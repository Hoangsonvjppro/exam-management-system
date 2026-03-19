<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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

    // Tạo sảnh chờ, hiện thông tin đề thi và nút bắt đầu
    public function show(Exam $exam)
    {
        $this->authorize('viewAsStudent', $exam);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->first();

        return view("student.exams.show", compact("exam", "attempt"));
    }

    // Hiển thị bài thi khi thằng sinh viên nhấn vào nút bắt đầu
    public function start(Exam $exam)
    {
        $this->authorize('attemptExam', $exam);

        $now = now();
        if ($exam->start_time  && $now->lt($exam->start_time)) {
            return back()->with('error', 'Lo ôn bài tiếp đi, vì bài thi  chưa bắt đầu.');
        }
        if ($exam->end_time && $now->gt($exam->end_time)) {
            return back()->with('error', 'Bài thi đã kết thúc.');
        }


        $attempt = ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'user_id' => Auth::id()],
            ['started_at' => now(), 'status' => 'in_progress']
        );

        return redirect()->route('student.exams.room', $exam->id);
    }

    //Vào được phòng thi rồi thì sẽ hiển thị câu hỏi và form nộp bài
    public function room(Exam $exam)
    {
        $this->authorize('attemptExam', $exam);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($attempt->status == 'completed') {
            return redirect()->route('student.exams.show', $exam->id)
                ->with('error', 'Bạn đã hoàn thành bài thi này.');
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
    public function saveAnswer(Request $request, Exam $exam)
    {
        $this->authorize('attemptExam', $exam);

        $validated = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'question_option_id' => 'required|integer|exists:question_options,id',
        ]);


        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->first();

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

        return response()->json(['success' => true]);
    }

    // Nộp bài thi, tính điểm và lưu kết quả
    public function submit(Request $request, Exam $exam)
    {
        $this->authorize('attemptExam', $exam);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

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

        return redirect()->route('student.exams.show', $exam->id)
            ->with('success', 'Bài thi đã được nộp thành công. Điểm của ní là: ' . $attempt->fresh()->total_score);
    }
}
