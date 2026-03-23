<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamSchedule\StoreExamScheduleRequest;
use App\Http\Requests\ExamSchedule\UpdateExamScheduleRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Services\ExamScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExamScheduleController extends Controller
{
    public function __construct(
        private readonly ExamScheduleService $scheduleService,
        private readonly \App\Services\NotificationService $notificationService
    ) {}

    /**
     * Danh sách lịch thi của giảng viên.
     */
    public function index(): View
    {
        $schedules = $this->scheduleService->getSchedulesForLecturer(Auth::id());

        return view('lecturer.schedules.index', compact('schedules'));
    }

    /**
     * Form tạo lịch thi.
     */
    public function create(): View
    {
        $exams = \App\Models\Exam::where('created_by', Auth::id())->get();
        $courseSections = \App\Models\CourseSection::where('lecturer_id', Auth::id())->get();

        return view('lecturer.schedules.create', compact('exams', 'courseSections'));
    }

    /**
     * Lưu lịch thi mới.
     */
    public function store(StoreExamScheduleRequest $request): RedirectResponse
    {
        $exam = Exam::findOrFail($request->validated('exam_id'));
        Gate::authorize('manageLecturer', $exam);

        $schedule = $this->scheduleService->createSchedule($exam, $request->validated());

        if ($schedule->courseSection) {
            $this->notificationService->sendToSection($schedule->courseSection, [
                'title' => 'Lịch thi mới',
                'message' => 'Bạn có một lịch thi mới cho môn học ' . ($schedule->exam->subject->name ?? 'Không xác định'),
            ]);
        }

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được tạo thành công.');
    }

    /**
     * Form sửa lịch thi.
     */
    public function edit(ExamSchedule $schedule): View
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $exams = \App\Models\Exam::where('created_by', Auth::id())->get();
        $courseSections = \App\Models\CourseSection::where('lecturer_id', Auth::id())->get();

        return view('lecturer.schedules.edit', compact('schedule', 'exams', 'courseSections'));
    }

    /**
     * Cập nhật lịch thi.
     */
    public function update(UpdateExamScheduleRequest $request, ExamSchedule $schedule): RedirectResponse
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $this->scheduleService->updateSchedule($schedule, $request->validated());

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được cập nhật.');
    }

    /**
     * Xóa lịch thi.
     */
    public function destroy(ExamSchedule $schedule): RedirectResponse
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $this->scheduleService->deleteSchedule($schedule);

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được xoá.');
    }

    /**
     * Tự động phân sinh viên vào ca thi.
     */
    public function assignStudents(ExamSchedule $schedule): RedirectResponse
    {
        Gate::authorize('manageLecturer', $schedule->exam);

        $count = $this->scheduleService->autoAssignStudents($schedule);

        return back()->with('success', "Đã phân {$count} sinh viên vào ca thi.");
    }
}
