<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ExamAttempt;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    /**
     * Store a newly created complaint in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attempt_id' => 'required|exists:exam_attempts,id',
            'reason'     => 'required|string|min:10',
        ]);

        $studentId = $request->user()->id;

        // Ensure user owns this attempt
        $attempt = ExamAttempt::with(['schedule.exam', 'schedule.courseSection.lecturer'])
            ->where('id', $validated['attempt_id'])
            ->where('user_id', $studentId)
            ->firstOrFail();

        // 1. Must be completed
        // Compare with name since status is Enum (enum string value)
        if ($attempt->status->value !== 'completed') {
            return response()->json([
                'message' => 'Bạn chỉ có thể khiếu nại đối với bài thi đã hoàn thành.'
            ], 422);
        }

        // 2. Exam must allow showing score after submit
        if (!$attempt->schedule->exam->show_score_after_submit) {
            return response()->json([
                'message' => 'Kỳ thi này không cho phép xem điểm, do đó không thể khiếu nại.'
            ], 422);
        }

        // 3. Prevent duplicate complaints
        $existing = Complaint::where('student_id', $studentId)
            ->where('exam_attempt_id', $attempt->id)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Bạn đã gửi khiếu nại cho bài thi này rồi.'
            ], 422);
        }

        // Create complaint and send notification within a transaction
        $complaint = DB::transaction(function () use ($attempt, $studentId, $validated, $request) {
            $complaint = Complaint::create([
                'student_id'        => $studentId,
                'exam_attempt_id'   => $attempt->id,
                'exam_schedule_id'  => $attempt->exam_schedule_id,
                'course_section_id' => $attempt->schedule->course_section_id,
                'reason'            => $validated['reason'],
                'current_score'     => $attempt->total_score,
                'status'            => 'pending',
            ]);

            // Notify Lecturer
            $lecturer = $attempt->schedule->courseSection->lecturer;
            if ($lecturer) {
                UserNotification::create([
                    'user_id' => $lecturer->id,
                    'type'    => 'complaint_created',
                    'title'   => 'Có khiếu nại điểm mới',
                    'message' => "Sinh viên {$request->user()->name} vừa gửi khiếu nại cho bài thi '{$attempt->schedule->exam->title}' thuộc lớp {$attempt->schedule->courseSection->code}.",
                    'data'    => [
                        'complaint_id' => $complaint->id,
                        'url'          => route('lecturer.complaints.index')
                    ]
                ]);
            }

            return $complaint;
        });

        return response()->json([
            'message'   => 'Đã gửi khiếu nại thành công.',
            'complaint' => $complaint
        ], 201);
    }
}
