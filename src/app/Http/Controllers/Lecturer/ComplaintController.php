<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\ExamAttempt;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the complaints for classes taught by the lecturer.
     */
    public function index(Request $request): View
    {
        $lecturerId = $request->user()->id;

        $complaints = Complaint::with(['student', 'attempt', 'schedule.exam', 'section'])
            ->whereHas('section', function ($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })
            ->latest()
            ->paginate(15);

        return view('lecturer.complaints.index', compact('complaints'));
    }

    /**
     * Update the specified complaint in storage (resolve or reject).
     */
    public function update(Request $request, Complaint $complaint): JsonResponse
    {
        // 1. Validate lecturer owns this section
        $lecturerId = $request->user()->id;
        if ($complaint->section->lecturer_id !== $lecturerId) {
            return response()->json([
                'message' => 'Bạn không có quyền xử lý khiếu nại của lớp học phần này.'
            ], 403);
        }

        // 2. Cannot update an already resolved/rejected complaint
        if ($complaint->status !== 'pending' && $complaint->status !== 'reviewing') {
            return response()->json([
                'message' => 'Khiếu nại này đã được xử lý từ trước.'
            ], 422);
        }

        // 3. Get total questions for validation
        $totalQuestions = $complaint->schedule->exam->questions()->count();

        $validated = $request->validate([
            'status'               => 'required|in:resolved,rejected',
            'reviewer_note'        => 'required|string|min:5',
            'updated_correct_count' => "nullable|integer|min:0|max:{$totalQuestions}",
        ]);

        // Process complaint inside transaction
        DB::transaction(function () use ($complaint, $validated, $lecturerId, $totalQuestions) {
            $status = $validated['status'];
            $note = $validated['reviewer_note'];
            $updatedCorrectCount = $validated['updated_correct_count'] ?? null;

            // Apply updates
            $complaint->status        = $status;
            $complaint->reviewer_id   = $lecturerId;
            $complaint->reviewer_note = $note;
            $complaint->resolved_at   = now();

            if ($status === 'resolved' && is_numeric($updatedCorrectCount)) {
                // Tính điểm hệ 10 từ số câu đúng mới
                $newScore = $totalQuestions > 0
                    ? round(($updatedCorrectCount / $totalQuestions) * 10, 1)
                    : 0;

                $complaint->updated_score = $newScore;

                // Sync to ExamAttempt
                $attempt = $complaint->attempt;
                $attempt->correct_count = $updatedCorrectCount;
                $attempt->total_score   = $newScore;
                $attempt->save();
            }

            $complaint->save();

            // Notify Student
            $message = $status === 'resolved' 
                ? "Giảng viên đã chấp nhận khiếu nại cho bài thi '{$complaint->schedule->exam->title}' và cập nhật điểm của bạn."
                : "Giảng viên đã từ chối khiếu nại cho bài thi '{$complaint->schedule->exam->title}'.";

            UserNotification::create([
                'user_id' => $complaint->student_id,
                'type'    => 'complaint_updated',
                'title'   => 'Kết quả khiếu nại điểm thi',
                'message' => $message,
                'data'    => [
                    'complaint_id' => $complaint->id,
                    'url'          => route('student.complaints.index')
                ]
            ]);
        });

        return response()->json([
            'message' => 'Đã lưu phản hồi trạng thái khiếu nại thành công.',
        ]);
    }
}
