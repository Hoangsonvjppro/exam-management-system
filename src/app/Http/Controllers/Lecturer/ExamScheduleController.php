<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamSchedule\StoreExamScheduleRequest;
use App\Http\Requests\ExamSchedule\UpdateExamScheduleRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Services\ExamScheduleService;
use Illuminate\Http\JsonResponse;
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
    public function index(\Illuminate\Http\Request $request): View
    {
        $search = $request->query('search');
        $subjectId = $request->query('subject_id');

        $schedules = $this->scheduleService->getSchedulesForLecturer(Auth::id(), null, $search, $subjectId);

        // Load data cho slide-over form tạo lịch thi mới
        $exams = Exam::where('created_by', Auth::id())->with('subject')->get();
        $courseSections = \App\Models\CourseSection::where('lecturer_id', Auth::id())->get();

        // Subjects for filter dropdown
        $filterSubjects = $courseSections->pluck('subject_id')->filter()->unique()->values();
        $filterSubjects = \App\Models\Subject::whereIn('id', $filterSubjects)->orderBy('name')->get(['id', 'name', 'code']);

        return view('lecturer.schedules.index', compact('schedules', 'exams', 'courseSections', 'filterSubjects'));
    }

    /**
     * Form tạo lịch thi.
     */
    public function create(\Illuminate\Http\Request $request): View
    {
        $exams = Exam::where('created_by', Auth::id())->with('subject')->get();
        $courseSections = \App\Models\CourseSection::where('lecturer_id', Auth::id())->get();
        
        $preSelectedSection = null;
        if ($request->query('course_section_id')) {
            $preSelectedSection = \App\Models\CourseSection::find($request->query('course_section_id'));
        }

        return view('lecturer.schedules.create', compact('exams', 'courseSections', 'preSelectedSection'));
    }

    /*
     * Lưu lịch thi mới.
     */
    public function store(StoreExamScheduleRequest $request): RedirectResponse|JsonResponse
    {
        $exam = Exam::findOrFail($request->validated('exam_id'));
        Gate::authorize('manageLecturer', $exam);

        $schedules = $this->scheduleService->createSchedules($exam, $request->validated());

        foreach ($schedules as $schedule) {
            if ($schedule->courseSection) {
                $this->notificationService->sendToSection($schedule->courseSection, [
                    'title' => 'Lịch thi mới',
                    'message' => 'Bạn có một lịch thi mới cho môn học ' . ($schedule->exam->subject->name ?? 'Không xác định') . '. Ngày thi: ' . $schedule->exam_date->format('d/m/Y'),
                ]);
            }
        }

        if ($request->wantsJson()) {
            $htmlRows = '';
            foreach ($schedules as $schedule) {
                $schedule->load(['exam.subject', 'courseSection']);
                $htmlRows .= view('lecturer.schedules.partials._schedule_row', compact('schedule'))->render();
            }

            return response()->json([
                'success' => true,
                'message' => 'Lịch thi đã được tạo thành công cho ' . $schedules->count() . ' lớp.',
                'html'    => $htmlRows,
            ]);
        }

        return redirect()->route('lecturer.schedules.index')
            ->with('success', 'Lịch thi đã được tạo thành công cho ' . $schedules->count() . ' lớp.');
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
