<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\ExamAttempt;
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

        $validated = $request->validate([
            'status'        => 'required|in:resolved,rejected',
            'reviewer_note' => 'required_if:status,resolved|required_if:status,rejected|string|min:5',
            'updated_score' => 'nullable|numeric|min:0',
        ]);

        // Process complaint inside transaction
        DB::transaction(function () use ($complaint, $validated, $lecturerId) {
            $status = $validated['status'];
            $note = $validated['reviewer_note'];
            $updatedScore = $validated['updated_score'] ?? null;

            // Apply updates
            $complaint->status        = $status;
            $complaint->reviewer_id   = $lecturerId;
            $complaint->reviewer_note = $note;
            $complaint->resolved_at   = now();

            if ($status === 'resolved' && is_numeric($updatedScore)) {
                $complaint->updated_score = $updatedScore;

                // Sync the total_score to ExamAttempt
                $attempt = $complaint->attempt;
                $attempt->total_score = $updatedScore;
                $attempt->save();
            }

            $complaint->save();

            // Notify Student
            $message = $status === 'resolved' 
                ? "Giảng viên đã chấp nhận khiếu nại cho bài thi '{$complaint->schedule->exam->title}' và cập nhật điểm của bạn."
                : "Giảng viên đã từ chối khiếu nại cho bài thi '{$complaint->schedule->exam->title}'.";

            Notification::create([
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
