<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttendanceSessionController extends Controller
{
    /**
     * Store a newly created attendance session and initialize records.
     */
    public function store(Request $request, CourseSection $section): JsonResponse
    {
        // Require the user to own the course section
        $this->authorize('manage', $section);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $session = $section->attendanceSessions()->create([
                'title' => $validated['title'],
                'date' => $validated['date'],
                'secret_code' => strtoupper(Str::random(6)),
                'is_open' => true,
            ]);

            $students = $section->students()->get();

            $records = [];
            $now = now();
            foreach ($students as $student) {
                // If student is enrolled (you could filter by 'status' if needed)
                $records[] = [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => 'absent',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                AttendanceRecord::insert($records);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã tạo phiên điểm danh thành công',
                'session' => $session
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo phiên điểm danh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the attendance session's open status.
     */
    public function toggleOpen(Request $request, CourseSection $section, AttendanceSession $session): JsonResponse
    {
        $this->authorize('manage', $section);

        if ($session->course_section_id !== $section->id) {
            abort(404);
        }

        $session->update([
            'is_open' => !$session->is_open
        ]);

        return response()->json([
            'success' => true,
            'message' => $session->is_open ? 'Đã mở điểm danh' : 'Đã đóng điểm danh',
            'is_open' => $session->is_open,
            'secret_code' => $session->secret_code
        ]);
    }

    /**
     * Update the attendance status of a specific record.
     */
    public function updateRecord(Request $request, CourseSection $section, AttendanceSession $session, AttendanceRecord $record): JsonResponse
    {
        $this->authorize('manage', $section);

        if ($session->course_section_id !== $section->id || $record->attendance_session_id !== $session->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['present', 'absent', 'excused'])],
        ]);

        $record->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật điểm danh thành công',
            'status' => $record->status
        ]);
    }
}
