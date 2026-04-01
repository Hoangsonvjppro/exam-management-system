<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Services\AttendanceGradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttendanceSessionController extends Controller
{
    public function __construct(
        private readonly AttendanceGradeService $attendanceGradeService
    ) {}

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

        $wasOpen = (bool) $session->is_open;
        $nextState = !$wasOpen;
        $penaltyAppliedNow = false;

        DB::transaction(function () use ($section, $session, $wasOpen, $nextState, &$penaltyAppliedNow) {
            $session->update([
                'is_open' => $nextState,
            ]);

            // Apply attendance-score penalty only once at the first closing action.
            if ($wasOpen && !$nextState && is_null($session->penalty_applied_at)) {
                $this->applyAttendancePenaltyForSession($section, $session);
                $session->update(['penalty_applied_at' => now()]);
                $penaltyAppliedNow = true;
            }
        });

        $message = $session->is_open
            ? 'Đã mở điểm danh'
            : ($penaltyAppliedNow ? 'Đã đóng điểm danh và cập nhật điểm chuyên cần' : 'Đã đóng điểm danh');

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_open' => $session->is_open,
            'secret_code' => $session->secret_code
        ]);
    }

    private function applyAttendancePenaltyForSession(CourseSection $section, AttendanceSession $session): void
    {
        $enrolledStudentIds = $section->students()
            ->wherePivot('status', 'enrolled')
            ->pluck('users.id');

        if ($enrolledStudentIds->isEmpty()) {
            return;
        }

        $approvedLeaveStudentIds = $section->leaveRequests()
            ->where('status', 'approved')
            ->whereDate('date', $session->date)
            ->pluck('student_id')
            ->flip();

        $records = $session->records()
            ->whereIn('student_id', $enrolledStudentIds)
            ->get(['student_id', 'status']);

        foreach ($records as $record) {
            if ($record->status === 'present') {
                continue;
            }

            $hasApprovedLeave = $record->status === 'excused' || $approvedLeaveStudentIds->has($record->student_id);
            $penalty = $hasApprovedLeave
                ? AttendanceGradeService::APPROVED_LEAVE_PENALTY
                : AttendanceGradeService::ABSENT_PENALTY;

            $this->attendanceGradeService->deductScore(
                $section,
                (int) $record->student_id,
                $penalty,
                auth()->id(),
                'Tự động trừ điểm chuyên cần sau khi đóng buổi điểm danh'
            );
        }
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
