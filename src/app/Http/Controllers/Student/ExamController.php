<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\SaveAnswerRequest;
use App\Http\Requests\Exam\SubmitExamRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use App\Services\StudentExamService;
use Illuminate\Support\Facades\Auth;
use DomainException;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamAttemptService $examAttemptService,
        private readonly StudentExamService $studentExamService,
    ) {}

    // Danh sách bài thi của sinh viên
    public function index(): \Illuminate\View\View
    {
        $userId = Auth::id();

        // Lấy danh sách lớp học phần mà SV đã enrolled
        $enrolledSectionIds = \App\Models\CourseSection::whereHas('students', function ($q) use ($userId) {
            $q->where('users.id', $userId)->where('course_section_students.status', 'enrolled');
        })->pluck('id');

        // Lấy exams thuộc các course sections đã enrolled
        $exams = Exam::whereIn('course_section_id', $enrolledSectionIds)
            ->published()
            ->with('courseSection')
            ->orderBy('start_time', 'asc')
            ->get();

        // Gom nhóm theo trạng thái
        $upcoming = $exams->filter(fn($e) => !$e->start_time || $e->start_time->isFuture());
        $available = $exams->filter(fn($e) => $e->start_time && $e->start_time->isPast() && (!$e->end_time || $e->end_time->isFuture()));
        $ended = $exams->filter(fn($e) => $e->end_time && $e->end_time->isPast());

        return view('student.exams.index', compact('upcoming', 'available', 'ended'));
    }

    // Tạo sảnh chờ, hiện thông tin đề thi và nút bắt đầu
    public function show(Exam $exam): \Illuminate\View\View
    {
        $this->authorize('viewAsStudent', $exam);

        $userId = Auth::id();
        $inProgressAttempt = ExamAttempt::forExam($exam->id)->forUser($userId)->inProgress()->first();
        $pastAttempts = ExamAttempt::forExam($exam->id)->forUser($userId)->completed()->latestAttempt()->get();

        $canStartNew = true;
        if ($exam->isOfficial() && $pastAttempts->isNotEmpty()) {
            $canStartNew = false; // Official exam, already completed
        }
        if ($inProgressAttempt) {
            $canStartNew = false; // Currently taking the exam
        }

        return view("student.exams.show", compact("exam", "inProgressAttempt", "pastAttempts", "canStartNew"));
    }

    // Hiển thị bài thi khi thằng sinh viên nhấn vào nút bắt đầu
    public function start(Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('attemptExam', $exam);

        try {
            $this->studentExamService->startAttempt(
                $exam,
                (int) Auth::id(),
                request()->ip(),
                request()->userAgent(),
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student.exams.room', $exam->id);
    }

    //Vào được phòng thi rồi thì sẽ hiển thị câu hỏi và form nộp bài
    public function room(Exam $exam): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('attemptExam', $exam);

        $attempt = ExamAttempt::forExam($exam->id)->forUser(Auth::id())->inProgress()->first();

        if (!$attempt) {
            return redirect()->route('student.exams.show', $exam->id)
                ->with('error', 'Bạn không có bài thi nào đang diễn ra. Xin hãy dứt khoát ấn Bắt đầu.');
        }

        // Deadline = min(started_at + duration, exam.end_time)
        $deadline = $exam->getDeadlineFor($attempt);
        $timeLeftSeconds = $deadline->getTimestamp() - now()->getTimestamp();

        // Nếu thời gian đã hết thì tự động finalize (chấm điểm luôn)
        if ($timeLeftSeconds <= 0) {
            $this->examAttemptService->finalizeAttempt($attempt);
            return redirect()->route('student.exams.show', $exam->id)
                ->with('info', 'Thời gian làm bài đã hết. Bài thi của bạn đã được nộp và chấm điểm tự động.');
        }

        // Lấy câu hỏi kèm đáp án
        $questions = $exam->questions()->with('options')->get();

        // Cần lưu những đáp án đã chọn, vì chẳng may thằng sinh viên nhấn f5 thì còn cứu được
        $savedAnswers = $attempt->answers()->pluck('question_option_id', 'question_id')->toArray();

        return view('student.exams.room', compact(
            'exam',
            'attempt',
            'questions',
            'timeLeftSeconds',
            'savedAnswers'
        ));
    }

    // API lưu ngầm, không reload trang, mỗi lần sinh viên chọn đáp án nào đó thì sẽ gọi API này để lưu lại
    public function saveAnswer(SaveAnswerRequest $request, Exam $exam): \Illuminate\Http\JsonResponse
    {
        $this->authorize('attemptExam', $exam);

        $result = $this->studentExamService->saveAnswer(
            $exam,
            (int) Auth::id(),
            $request->validated(),
            $request->has('tab_switch_count') ? (int) $request->input('tab_switch_count') : null,
        );

        if ($result['http_code'] !== 200) {
            return response()->json(['error' => $result['message']], $result['http_code']);
        }

        return response()->json(['success' => true]);
    }

    // Nộp bài thi, tính điểm và lưu kết quả
    public function submit(SubmitExamRequest $request, Exam $exam): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('attemptExam', $exam);

        $attempt = ExamAttempt::forExam($exam->id)->forUser(Auth::id())->inProgress()->firstOrFail();

        // Chặn submit sau deadline
        $deadline = $exam->getDeadlineFor($attempt);
        if (now()->gt($deadline)) {
            // Nếu đã quá deadline, finalize với dữ liệu đã lưu
            $this->examAttemptService->finalizeAttempt($attempt);
            return redirect()->route('student.exams.show', $exam->id)
                ->with('info', 'Đã hết thời gian. Bài thi được chấm với đáp án đã lưu.');
        }

        // Upsert answers cuối cùng từ form trước khi chấm (Medium #19)
        $lastAnswers = $request->validated('answers', []);
        $this->examAttemptService->finalizeAttempt($attempt, $lastAnswers);

        return redirect()->route('student.exams.result', $exam->id)
            ->with('success', 'Bài thi đã được nộp thành công!');
    }

    // Xem kết quả bài thi sau khi nộp
    public function result(Exam $exam): \Illuminate\View\View
    {
        $this->authorize('viewAsStudent', $exam);

        $attempt = ExamAttempt::forExam($exam->id)->forUser(Auth::id())
            ->completed()->latestAttempt()->firstOrFail();

        // Load answers kèm option và question (để hiển thị chi tiết)
        $answers = $attempt->answers()
            ->with(['option', 'question.options'])
            ->get();

        // Tính số câu đúng
        $correctCount = $answers->where('is_correct', true)->count();
        $totalQuestions = $exam->questions()->count();

        // Kiểm tra đạt/không đạt
        $passed = $attempt->total_score >= $exam->pass_points;

        return view('student.exams.result', compact(
            'exam',
            'attempt',
            'answers',
            'correctCount',
            'totalQuestions',
            'passed'
        ));
    }
}
