<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\LeaveRequest;
use App\Services\AttendanceGradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly AttendanceGradeService $attendanceGradeService
    ) {}

    /**
     * Process a leave request.
     */
    public function update(Request $request, CourseSection $section, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('manage', $section);

        if ($leaveRequest->course_section_id !== $section->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ]);

        $session = $section->attendanceSessions()->whereDate('date', $leaveRequest->date)->first();
        $recordBeforeStatus = $session
            ? $session->records()->where('student_id', $leaveRequest->student_id)->value('status')
            : null;

        $leaveRequest->update([
            'status' => $validated['status']
        ]);

        // If approved, optionally mirror the excused state to the attendance session for the same date 
        // if there is one already existing.
        if ($validated['status'] === 'approved') {
            if ($session) {
                // Update or create attendance record with 'excused' status
                $session->records()->updateOrCreate(
                    ['student_id' => $leaveRequest->student_id],
                    ['status' => 'excused']
                );

                // If attendance was already closed and absent penalty (-1) was applied,
                // refund +0.5 so approved leave becomes net -0.5.
                if (!$session->is_open && $session->penalty_applied_at && $recordBeforeStatus === 'absent') {
                    $this->attendanceGradeService->applyScoreDelta(
                        $section,
                        (int) $leaveRequest->student_id,
                        AttendanceGradeService::APPROVED_LEAVE_PENALTY,
                        auth()->id(),
                        'Hoàn điểm chuyên cần do duyệt đơn xin phép'
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái đơn xin nghỉ phép.',
            'status' => $leaveRequest->status
        ]);
    }
}
