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
        //Fix: Có lẽ nên thêm phần kiểm tra xem status bài thi đã được mở hay chưa rồi mới cho phép bắt đầu, tránh trường hợp sinh viên vào sảnh chờ rồi nhưng thầy chưa mở bài thi
        if ($exam->status != 'published') {
            abort(403, 'Bài thi nãy đã được mở đâu!?');
        }

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

        //Kiểm tra câu hỏi có thuộc đề thi này không, tránh trường hợp sinh viên gửi request lạ để lưu đáp án cho câu hỏi không thuộc đề thi
        $questionBelongstoExam = $exam->questions()
            ->where('questions.id', $validated['question_id'])
            ->exists();

        if (!$questionBelongstoExam) {
            return response()->json(['error' => 'Câu hỏi không thuộc đề thi này.'], 422);
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
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->where('status', 'in_progress') // chỉ cho phép submit nếu đang trong trạng thái làm bài
            ->firstOrFail();

        $totalScore = 0;
        $answers = $attempt->answers()->with('option')->get();
        foreach ($exam->questions as $question) {
            $isCorrect = $answers->option?->is_correct ?? false;

            // Lấy điểm câu hỏi từ bảng trung gian exam_questions
            $point = $exam->questions()
                ->where('questions.id', $answers->question_id)
                ->first()?->pivot->points ?? 1.00;
            $awardedPoint = $isCorrect ? $point : 0;
            $totalScore += $awardedPoint;

            $answers->update([
                'is_correct' => $isCorrect,
                'points_awarded' => $awardedPoint
            ]);
        }

        $attempt->update(['status' => 'completed', 'completed_at' => now(), 'total_score' => $totalScore]);

        return redirect()->route('student.exams.show', $exam->id)
            ->with('success', 'Bài thi đã được nộp thành công. Điểm của bạn là: ' . $totalScore .'');
    }
}
