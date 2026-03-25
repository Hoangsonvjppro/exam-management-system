<?php

namespace App\Services;

use App\Models\ExamSchedule;
use App\Models\Exam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamScheduleService
{
    /**
     * Tạo lịch thi mới cho 1 đề thi và áp dụng cho nhiều lớp.
     */
    public function createSchedules(Exam $exam, array $data): Collection
    {
        $courseSectionIds = $data['course_section_ids'] ?? [];
        $schedules = new Collection();

        DB::transaction(function () use ($exam, $data, $courseSectionIds, &$schedules) {
            foreach ($courseSectionIds as $sectionId) {
                $scheduleData = $data;
                unset($scheduleData['course_section_ids']);
                $scheduleData['course_section_id'] = $sectionId;

                $schedule = $exam->schedules()->create($scheduleData);
                
                // Tự động phân sinh viên sau khi tạo
                $this->autoAssignStudents($schedule);
                
                $schedules->push($schedule);
            }
        });

        return $schedules;
    }

    /**
     * Tạo lịch thi mới cho 1 đề thi.
     */
    public function createSchedule(Exam $exam, array $data): ExamSchedule
    {
        return $exam->schedules()->create($data);
    }

    /**
     * Cập nhật lịch thi.
     */
    public function updateSchedule(ExamSchedule $schedule, array $data): ExamSchedule
    {
        $schedule->update($data);
        return $schedule;
    }

    public function deleteSchedule(ExamSchedule $schedule): void
    {
        $schedule->delete();
    }

    /**
     * Tự động phân sinh viên enrolled vào ca thi.
     * Lấy SV enrolled trong course_section, assign theo capacity.
     *
     * @return int Số SV đã assign
     */
    public function autoAssignStudents(ExamSchedule $schedule): int
    {
        $courseSection = $schedule->courseSection;

        if (! $courseSection) {
            return 0;
        }

        // Lấy danh sách SV enrolled chưa được assign vào ca thi này
        $enrolledStudentIds = $courseSection->students()
            ->wherePivot('status', 'enrolled')
            ->pluck('users.id');

        $alreadyAssignedIds = $schedule->scheduleStudents()
            ->pluck('student_id');

        $toAssign = $enrolledStudentIds->diff($alreadyAssignedIds);

        // Giới hạn theo max_students nếu có
        if ($schedule->max_students) {
            $currentCount = $schedule->scheduleStudents()->count();
            $remaining = max(0, $schedule->max_students - $currentCount);
            $toAssign = $toAssign->take($remaining);
        }

        DB::transaction(function () use ($schedule, $toAssign) {
            foreach ($toAssign as $studentId) {
                $schedule->scheduleStudents()->create([
                    'student_id' => $studentId,
                    'attendance_status' => 'pending',
                ]);
            }
        });

        return $toAssign->count();
    }

    /**
     * Lấy danh sách lịch thi của giảng viên.
     */
    public function getSchedulesForLecturer(int $lecturerId, ?int $semesterId = null): Collection
    {
        $query = ExamSchedule::whereHas('courseSection', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })->with(['exam.subject', 'courseSection']);

        if ($semesterId) {
            $query->whereHas('courseSection', fn($q) => $q->where('semester_id', $semesterId));
        }

        return $query->orderBy('exam_date')->orderBy('start_time')->get();
    }

    /**
     * Lấy danh sách lịch thi của sinh viên (dựa trên lớp đã enrolled).
     */
    public function getSchedulesForStudent(int $studentId): Collection
    {
        return ExamSchedule::whereHas('courseSection.students', function ($q) use ($studentId) {
            $q->where('users.id', $studentId)
              ->where('course_section_students.status', 'enrolled');
        })
        ->with(['exam.subject', 'courseSection'])
        ->orderBy('exam_date', 'desc')
        ->orderBy('start_time')
        ->get();
    }
}
