<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Check in via Secret Code/PIN OR QR code string.
     */
    public function checkIn(Request $request, CourseSection $section): JsonResponse
    {
        $validated = $request->validate([
            'secret_code' => 'required|string',
        ]);

        $code = strtoupper(trim($validated['secret_code']));

        // Find the open session with this secret code in this section
        $session = $section->attendanceSessions()
            ->where('secret_code', $code)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Mã điểm danh không hợp lệ hoặc không thuộc lớp này.'
            ], 422);
        }

        if (!$session->is_open) {
            return response()->json([
                'success' => false,
                'message' => 'Buổi điểm danh này đã đóng. Bạn không thể điểm danh lúc này.'
            ], 422);
        }

        $user = request()->user();

        // Ensure user is enrolled
        if (!$user->enrolledSections()->where('course_sections.id', $section->id)->exists()) {
            abort(403);
        }

        // Check if student is already present
        $record = $session->records()->firstOrCreate(
            ['student_id' => $user->id],
            ['status' => 'absent']
        );

        if ($record->status === 'present') {
            return response()->json([
                'success' => true,
                'message' => 'Bạn đã hoàn thành điểm danh từ trước!'
            ]);
        }
        
        if ($record->status === 'excused') {
            // Already excused? Let them override to present? Yes.
        }

        $record->update(['status' => 'present']);

        return response()->json([
            'success' => true,
            'message' => 'Điểm danh thành công!'
        ]);
    }
}
