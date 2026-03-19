<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\StudentAnswer;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    // Tạo sảnh chờ, hiện thông tin đề thi và nút bắt đầu
    public function show(Exam $exam)
    {
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->first();

        return view("student.exams.show", compact("exam", "attempt"));
    }

    // Hiển thị bài thi khi thằng sinh viên nhấn vào nút bắt đầu
    public function start(Exam $exam)
    {
        $attempt = ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'user_id' => Auth::id()],
            ['started_at' => now(), 'status' => 'in_progress']
        );

        return redirect()->route('student.exams.room', $exam->id);
    }

    //Vào được phòng thi rồi thì sẽ hiển thị câu hỏi và form nộp bài
    public function room(Exam $exam)
    {
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($attempt->status == 'completed') {
            return redirect()->route('student.exams.show', $exam->id)
                ->with('error', 'Bạn đã hoàn thành bài thi này.');
        }

        // Có lẽ nên tính thời gian còn lại để đẩy sang Javascript
        $endTime = $attempt->started_at->addMinutes($exam->duration_minutes);
        $timeLeftSeconds = $endTime->getTimestamp() - now()->getTimestamp();

        // Nếu thời gian đã hết thì tự động submit bài thi
        if ($timeLeftSeconds <= 0) {
            $attempt->update(['status' => 'completed', 'completed_at' => now()]);
            return redirect()->route('student.exams.show', $exam->id)
                ->with('info', 'Thời gian làm bài đã hết. Bài thi của mày đã được nộp tự động.');
        }

        // Lấy câu hỏi kèm đáp án (tùy ý mấy ae sau này muốn hiển thị đáp án hay không)
        $questions = $exam->questions()->with('options')->get();

        // Cần lưu những đáp án đã chọn, vì chẳng may thằng sinh viên nhấn f5 thì còn cứu được
        $saveAnswers = $attempt->answers()->pluck('question_option_id', 'question_id')->toArray();

        return view('student.exams.room', compact('exam', 'attempt', 'questions', 'timeLeftSeconds', 'saveAnswers'));
    }

    // API lưu ngầm, không reload trang, mỗi lần sinh viên chọn đáp án nào đó thì sẽ gọi API này để lưu lại
    public function saveAnswer(Request $request, Exam $exam)
    {
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$attempt || $attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Không thể lưu đáp án.'], 403);
        }

        StudentAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $request->question_id,
            ],
            [
                'question_option_id' => $request->question_option_id,
            ]
        );

        return response()->json(['success' => true]);
    }

    // Nộp bài thi, tính điểm và lưu kết quả
    public function submit(Request $request, Exam $exam)
    {
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', Auth::id())->firstOrFail();

        $attempt->update(['status' => 'completed', 'completed_at' => now()]);

        return redirect()->route('student.exams.show', $exam->id)
            ->with('success', 'Bài thi đã được nộp thành công. Điểm số sẽ được cập nhật sau khi chấm bài.');
    }
}
