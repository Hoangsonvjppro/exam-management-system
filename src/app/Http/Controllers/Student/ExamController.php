<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\SaveAnswerRequest;
use App\Http\Requests\Exam\SubmitExamRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use App\Services\StudentExamService;
use App\Services\StudentExamQueryService;
use Illuminate\Support\Facades\Auth;
use DomainException;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamAttemptService $examAttemptService,
        private readonly StudentExamService $studentExamService,
        private readonly StudentExamQueryService $studentExamQueryService,
    ) {}

    // Danh sách bài thi của sinh viên
    public function index(): \Illuminate\View\View
    {
        $indexData = $this->studentExamQueryService->getIndexData((int) Auth::id());

        return view('student.exams.index', $indexData);
    }

    // Tạo sảnh chờ, hiện thông tin đề thi và nút bắt đầu
    public function show(Exam $exam): \Illuminate\View\View
    {
        $this->authorize('viewAsStudent', $exam);

        $showData = $this->studentExamQueryService->getShowData($exam, (int) Auth::id());
        $inProgressAttempt = $showData['inProgressAttempt'];
        $pastAttempts = $showData['pastAttempts'];
        $canStartNew = $showData['canStartNew'];

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

        $attempt = $this->studentExamQueryService->getInProgressAttempt($exam, (int) Auth::id());

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

        $roomData = $this->studentExamQueryService->getRoomData($exam, $attempt);
        $questions = $roomData['questions'];
        $savedAnswers = $roomData['savedAnswers'];

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

        $attempt = $this->studentExamQueryService->getCompletedAttempt($exam, (int) Auth::id());
        $resultData = $this->studentExamQueryService->getResultData($exam, $attempt);
        $answers = $resultData['answers'];
        $correctCount = $resultData['correctCount'];
        $totalQuestions = $resultData['totalQuestions'];
        $passed = $resultData['passed'];

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
