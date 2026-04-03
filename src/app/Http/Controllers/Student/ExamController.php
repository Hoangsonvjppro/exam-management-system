<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\SaveAnswerRequest;
use App\Http\Requests\Exam\SubmitExamRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use App\Services\StudentExamService;
use App\Services\StudentExamQueryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
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
    public function show(ExamSchedule $schedule): \Illuminate\View\View|RedirectResponse
    {
        $exam = $schedule->exam;
        if ($redirect = $this->authorizeStudentExamAccess('viewAsStudent', $schedule)) {
            return $redirect;
        }

        $showData = $this->studentExamQueryService->getShowData($schedule, (int) Auth::id());
        $inProgressAttempt = $showData['inProgressAttempt'];
        $pastAttempts = $showData['pastAttempts'];
        $canStartNew = $showData['canStartNew'];

        return view("student.exams.show", compact("exam", "schedule", "inProgressAttempt", "pastAttempts", "canStartNew"));
    }

    // Hiển thị bài thi khi thằng sinh viên nhấn vào nút bắt đầu
    public function start(ExamSchedule $schedule): \Illuminate\Http\RedirectResponse
    {
        $exam = $schedule->exam;
        if ($redirect = $this->authorizeStudentExamAccess('attemptExam', $schedule)) {
            return $redirect;
        }

        try {
            $this->studentExamService->startAttempt(
                $schedule,
                (int) Auth::id(),
                request()->ip(),
                request()->userAgent(),
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student.exams.room', $schedule->id);
    }

    //Vào được phòng thi rồi thì sẽ hiển thị câu hỏi và form nộp bài
    public function room(ExamSchedule $schedule): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $exam = $schedule->exam;
        if ($redirect = $this->authorizeStudentExamAccess('attemptExam', $schedule)) {
            return $redirect;
        }

        $attempt = $this->studentExamQueryService->getInProgressAttempt($schedule, (int) Auth::id());

        if (!$attempt) {
            return redirect()->route('student.exams.show', $schedule->id)
                ->with('error', 'Bạn không có bài thi nào đang diễn ra. Xin hãy dứt khoát ấn Bắt đầu.');
        }

        // Deadline = min(started_at + duration, schedule.end_time) hoặc duration tuỳ cấu hình
        $deadline = $schedule->getDeadlineFor($attempt);
        $timeLeftSeconds = $deadline->getTimestamp() - now()->getTimestamp();
        $minDurationSeconds = max(0, (int) ($exam->min_duration_before_submit ?? 0) * 60);
        $elapsedSeconds = (int) $attempt->started_at->diffInSeconds(now());
        $minSubmitRemainingSeconds = max(0, $minDurationSeconds - $elapsedSeconds);

        // Nếu thời gian đã hết thì tự động finalize (chấm điểm luôn)
        if ($timeLeftSeconds <= 0) {
            $this->examAttemptService->finalizeAttempt($attempt);
            return redirect()->route('student.exams.show', $schedule->id)
                ->with('info', 'Thời gian làm bài đã hết. Bài thi của bạn đã được nộp và chấm điểm tự động.');
        }

        $roomData = $this->studentExamQueryService->getRoomData($schedule, $attempt);
        $questions = $roomData['questions'];
        $savedAnswers = $roomData['savedAnswers'];

        return view('student.exams.room', compact(
            'exam',
            'schedule',
            'attempt',
            'questions',
            'timeLeftSeconds',
            'minSubmitRemainingSeconds',
            'savedAnswers'
        ));
    }

    // API lưu ngầm, không reload trang, mỗi lần sinh viên chọn đáp án nào đó thì sẽ gọi API này để lưu lại
    public function saveAnswer(SaveAnswerRequest $request, ExamSchedule $schedule): \Illuminate\Http\JsonResponse
    {
        $exam = $schedule->exam;
        try {
            $this->authorize('attemptExam', $schedule);
        } catch (AuthorizationException) {
            return response()->json(['error' => 'Bạn không có quyền truy cập ca thi này.'], 403);
        }

        $result = $this->studentExamService->saveAnswer(
            $schedule,
            (int) Auth::id(),
            $request->validated(),
            $request->has('tab_switch_count') ? (int) $request->input('tab_switch_count') : null,
        );

        if ($result['http_code'] !== 200) {
            return response()->json(['error' => $result['message']], $result['http_code']);
        }

        return response()->json(['success' => true]);
    }

    // Nộp bài thi, tính điểm và lưu kết quả dựa trên dữ liệu đã có trong DB
    public function submit(SubmitExamRequest $request, ExamSchedule $schedule): \Illuminate\Http\RedirectResponse
    {
        $exam = $schedule->exam;
        if ($redirect = $this->authorizeStudentExamAccess('attemptExam', $schedule)) {
            return $redirect;
        }

        $attempt = ExamAttempt::forSchedule($schedule->id)
            ->forUser(Auth::id())
            ->inProgress()
            ->first();

        if (!$attempt) {
            $hasCompletedAttempt = ExamAttempt::forSchedule($schedule->id)
                ->forUser(Auth::id())
                ->completed()
                ->exists();

            if ($hasCompletedAttempt) {
                return redirect()->route('student.exams.result', $schedule->id)
                    ->with('info', 'Bài thi đã được nộp.');
            }

            return redirect()->route('student.exams.show', $schedule->id)
                ->with('warning', 'Bạn chưa bắt đầu làm bài cho ca thi này.');
        }

        // 1. Kiểm tra thời gian làm bài tối thiểu (bỏ qua nếu nộp phạt vi phạm quy chế)
        if (!$request->has('is_forced_submit')) {
            $minDuration = $exam->min_duration_before_submit ?? 0;
            if ($minDuration > 0) {
                $requiredSeconds = (int) $minDuration * 60;
                $elapsedSeconds = (int) $attempt->started_at->diffInSeconds(now());
                if ($elapsedSeconds < $requiredSeconds) {
                    $remainingSeconds = max(0, $requiredSeconds - $elapsedSeconds);
                    $remainingMinutes = (int) floor($remainingSeconds / 60);
                    $remainingExtraSeconds = $remainingSeconds % 60;

                    $remainingText = [];
                    if ($remainingMinutes > 0) {
                        $remainingText[] = "{$remainingMinutes} phút";
                    }
                    if ($remainingExtraSeconds > 0) {
                        $remainingText[] = "{$remainingExtraSeconds} giây";
                    }

                    return back()->with('warning', 'Chưa đủ thời gian nộp bài. Bạn cần làm thêm ' . implode(' ', $remainingText) . '.');
                }
            }
        }

        // 2. Chặn submit sau deadline
        $deadline = $schedule->getDeadlineFor($attempt);
        if (now()->gt($deadline)) {
            // Nếu đã quá deadline, finalize với dữ liệu đã lưu trong DB
            $this->examAttemptService->finalizeAttempt($attempt);
            return redirect()->route('student.exams.show', $schedule->id)
                ->with('info', 'Đã hết thời gian. Bài thi được chấm với đáp án đã lưu.');
        }

        // 3. Chấm điểm dựa trên Single Source of Truth từ DB (các câu trả lời đã lưu qua AJAX)
        $this->examAttemptService->finalizeAttempt($attempt);

        return redirect()->route('student.exams.result', $schedule->id)
            ->with('success', 'Bài thi đã được nộp thành công!');
    }

    // Xem kết quả bài thi sau khi nộp
    public function result(ExamSchedule $schedule): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $exam = $schedule->exam;
        if ($redirect = $this->authorizeStudentExamAccess('viewAsStudent', $schedule)) {
            return $redirect;
        }

        try {
            $attempt = $this->studentExamQueryService->getCompletedAttempt($schedule, (int) Auth::id());
        } catch (ModelNotFoundException) {
            return redirect()->route('student.exams.show', $schedule->id)
                ->with('info', 'Bạn chưa có kết quả cho ca thi này.');
        }

        $resultData = $this->studentExamQueryService->getResultData($schedule, $attempt);
        $answers = $resultData['answers'];
        $correctCount = $resultData['correctCount'];
        $totalQuestions = $resultData['totalQuestions'];

        return view('student.exams.result', compact(
            'exam',
            'schedule',
            'attempt',
            'answers',
            'correctCount',
            'totalQuestions'
        ));
    }

    private function authorizeStudentExamAccess(string $ability, ExamSchedule $schedule): ?RedirectResponse
    {
        try {
            $this->authorize($ability, $schedule);
            return null;
        } catch (AuthorizationException) {
            return redirect()
                ->route('student.schedules.index')
                ->with('warning', 'Bạn không có quyền truy cập ca thi này hoặc chưa được phân ca.');
        }
    }
}
