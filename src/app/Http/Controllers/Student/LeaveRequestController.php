<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Submit a new leave request for a specific date.
     */
    public function store(Request $request, CourseSection $section): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|min:5|max:1000',
            'proof_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = request()->user();

        // Ensure user is enrolled
        if (!$user->enrolledSections()->where('course_sections.id', $section->id)->exists()) {
            abort(403);
        }

        // Check if a request already exists for this date
        $exists = $section->leaveRequests()
            ->where('student_id', $user->id)
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã gửi đơn xin nghỉ phép cho ngày này rồi.'
            ], 422);
        }

        $proofImagePath = null;
        if ($request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('leave-proofs', 'public');
        }

        $section->leaveRequests()->create([
            'student_id' => $user->id,
            'date' => $validated['date'],
            'reason' => $validated['reason'],
            'proof_image_path' => $proofImagePath,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi đơn xin nghỉ phép thành công. Vui lòng chờ Giảng viên duyệt.'
        ]);
    }
}
