<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamSchedule\StoreExamScheduleRequest;
use App\Http\Requests\ExamSchedule\UpdateExamScheduleRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Services\ExamScheduleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ExamScheduleController extends Controller
{
    public function __construct(private readonly ExamScheduleService $scheduleService)
    {
    }

    /**
     * Danh sách lịch thi của giảng viên.
     */
    public function index()
    {
        $schedules = $this->scheduleService->getSchedulesForLecturer(Auth::id());

        return view('lecturer.schedules.index', compact('schedules'));
    }

    /**
     * Form tạo lịch thi cho 1 đề thi.
     */
    public function create(Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        return view('lecturer.schedules.create', compact('exam'));
    }

    /**
     * Lưu lịch thi mới.
     */
    public function store(StoreExamScheduleRequest $request, Exam $exam)
    {
        Gate::authorize('manageLecturer', $exam);

        $this->scheduleService->createSchedule($exam, $request->validated());

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được tạo thành công.');
    }

    /**
     * Form sửa lịch thi.
     */
    public function edit(ExamSchedule $schedule)
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        return view('lecturer.schedules.edit', compact('schedule'));
    }

    /**
     * Cập nhật lịch thi.
     */
    public function update(UpdateExamScheduleRequest $request, ExamSchedule $schedule)
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $this->scheduleService->updateSchedule($schedule, $request->validated());

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được cập nhật.');
    }

    /**
     * Xóa lịch thi.
     */
    public function destroy(ExamSchedule $schedule)
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $schedule->delete();

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được xoá.');
    }

    /**
     * Tự động phân sinh viên vào ca thi.
     */
    public function assignStudents(ExamSchedule $schedule)
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $count = $this->scheduleService->autoAssignStudents($schedule);

        return back()->with('success', "Đã phân {$count} sinh viên vào ca thi.");
    }
}
