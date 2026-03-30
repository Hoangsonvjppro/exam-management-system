<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
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

        $leaveRequest->update([
            'status' => $validated['status']
        ]);

        // If approved, optionally mirror the excused state to the attendance session for the same date 
        // if there is one already existing.
        if ($validated['status'] === 'approved') {
            $session = $section->attendanceSessions()->whereDate('date', $leaveRequest->date)->first();
            if ($session) {
                // Update or create attendance record with 'excused' status
                $session->records()->updateOrCreate(
                    ['student_id' => $leaveRequest->student_id],
                    ['status' => 'excused']
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái đơn xin nghỉ phép.',
            'status' => $leaveRequest->status
        ]);
    }
}
