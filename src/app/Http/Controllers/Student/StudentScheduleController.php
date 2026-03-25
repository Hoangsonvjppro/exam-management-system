<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ExamScheduleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentScheduleController extends Controller
{
    public function __construct(private readonly ExamScheduleService $scheduleService) {}

    /**
     * Danh sách lịch thi của sinh viên.
     */
    public function index(): View
    {
        $schedules = $this->scheduleService->getSchedulesForStudent((int) Auth::id());

        return view('student.schedules.index', compact('schedules'));
    }
}
