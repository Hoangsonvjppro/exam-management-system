<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\SaveAnswerRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function __construct(private readonly ExamAttemptService $examAttemptService)
    {
    }

    // Danh sách bài thi của sinh viên
    public function index()
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
    public function show(Exam $exam)
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
    public function start(Exam $exam)
    {
        $this->authorize('attemptExam', $exam);

        $now = now();
        if ($exam->start_time && $now->lt($exam->start_time)) {
            return back()->with('error', 'Bài thi chưa bắt đầu.');
        }
        if ($exam->end_time && $now->gt($exam->end_time)) {
            return back()->with('error', 'Bài thi đã kết thúc.');
        }

        $userId = Auth::id();

        // 1. Phục hồi session đang thi nếu có
        $attempt = ExamAttempt::forExam($exam->id)->forUser($userId)->inProgress()->first();

        // 2. Nếu không có session đang thi, tạo mới
        if (!$attempt) {
            $hasCompleted = ExamAttempt::forExam($exam->id)->forUser($userId)->completed()->exists();

            // Chặn thi lại nếu là bài thi chính thức
            if ($hasCompleted && $exam->isOfficial()) {
                return back()->with('error', 'Bạn đã hoàn thành bài thi chính thức này. Không thể thi lại.');
            }

            $latestAttempt = ExamAttempt::forExam($exam->id)->forUser($userId)->latestAttempt()->first();
            $nextNumber = $latestAttempt ? $latestAttempt->attempt_number + 1 : 1;

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'user_id' => $userId,
                'attempt_number' => $nextNumber,
                'started_at' => now(),
                'status' => 'in_progress',
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            ]);
        }

        return redirect()->route('student.exams.room', $exam->id);
    }

    //Vào được phòng thi rồi thì sẽ hiển thị câu hỏi và form nộp bài
    public function room(Exam $exam)
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
    public function saveAnswer(SaveAnswerRequest $request, Exam $exam)
    {
        $this->authorize('attemptExam', $exam);

        $validated = $request->validated();

        $attempt = ExamAttempt::forExam($exam->id)->forUser(Auth::id())->inProgress()->first();

        if (!$attempt || $attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Không thể lưu đáp án.'], 403);
        }

        // Chặn lưu đáp án sau deadline
        $deadline = $exam->getDeadlineFor($attempt);
        if (now()->gt($deadline)) {
            return response()->json(['error' => 'Đã hết thời gian làm bài.'], 403);
        }

        // Kiểm tra câu hỏi có thuộc đề thi này không
        $questionBelongstoExam = $exam->questions()
            ->where('questions.id', $validated['question_id'])
            ->exists();

        if (!$questionBelongstoExam) {
            return response()->json(['error' => 'Câu hỏi không thuộc đề thi này.'], 422);
        }

        // Kiểm tra option có thuộc question không (Critical #3)
        $optionBelongsToQuestion = QuestionOption::where('id', $validated['question_option_id'])
            ->where('question_id', $validated['question_id'])
            ->exists();

        if (!$optionBelongsToQuestion) {
            return response()->json(['error' => 'Đáp án không thuộc câu hỏi này.'], 422);
        }

        StudentAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'question_option_id' => $validated['question_option_id'],
            ]
        );

        // Cập nhật tab_switch_count nếu frontend gửi kèm
        if ($request->has('tab_switch_count')) {
            $attempt->update([
                'tab_switch_count' => (int) $request->input('tab_switch_count'),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Nộp bài thi, tính điểm và lưu kết quả
    public function submit(Request $request, Exam $exam)
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
        $lastAnswers = $request->input('answers', []);
        $this->examAttemptService->finalizeAttempt($attempt, $lastAnswers);

        return redirect()->route('student.exams.result', $exam->id)
            ->with('success', 'Bài thi đã được nộp thành công!');
    }

    // Xem kết quả bài thi sau khi nộp
    public function result(Exam $exam)
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
