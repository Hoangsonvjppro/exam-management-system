<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseSectionRequest;
use App\Models\CourseSection;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CourseSectionController extends Controller
{
    // private function formData(): array
    // {
    //     return [
    //         'subjects'  => Subject::orderBy('name')->get(),
    //         'semesters' => Semester::orderByDesc('year')->orderBy('term')->get(),
    //         'lecturers' => User::role(['lecturer'])->orderBy('name')->get(),
    //     ];
    // }
    
    public function index(Request $request)
    {
        $courseSections = CourseSection::all();
        return view('course-sections.index', compact('courseSections'));
    }

    public function create()
    {
        return view('course-sections.create');
        // return view('course-sections.create', $this->formData());
    }

    public function store(StoreCourseSectionRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $payload = $request->safe()->except('schedules');
                $payload['invite_code'] = $payload['invite_code'] ?? strtoupper(Str::random(8));

                $section = CourseSection::create($payload);
    
                if ($schedules = $request->input('schedules')) {
                    $section->classSchedules()->createMany($schedules);
                }
            });
        } catch (Throwable $e){
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi lưu dữ liệu! Vui lòng thử lại.');
        }

        return redirect()
            ->route('admin.course-sections.index')
            ->with('success', 'Tạo lớp học phần thành công!');
    }

    public function edit(CourseSection $courseSection)
    {
        // $courseSection->load('classSchedules');

        // return view('course-sections.edit', array_merge(
        //     ['courseSection' => $courseSection],
        //     $this->formData()
        // ));
        return view('course-sections.edit');
    }

    public function update(StoreCourseSectionRequest $request, CourseSection $courseSection)
    {
        try {
            DB::transaction(function () use ($request, $courseSection) {
                $payload = $request->safe()->except('schedules');
                if (empty($payload['invite_code'])) {
                    $payload['invite_code'] = $courseSection->invite_code ?: strtoupper(Str::random(8));
                }

                $courseSection->update($payload);
    
                if ($request->has('schedules')) {
                    $courseSection->classSchedules()->delete();
                    if ($schedules = $request->input('schedules')) {
                        $courseSection->classSchedules()->createMany($schedules);
                    }
                }
            });

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
        }

        return redirect()
            ->route('admin.course-sections.index')
            ->with('success', 'Cập nhật lớp học phần thành công!');
    }

    public function destroy(CourseSection $courseSection)
    {
        // classSchedules sẽ cascade delete theo migration
        // $courseSection->delete();

        // return redirect()
        //     ->route('admin.course-sections.index')
        //     ->with('success', 'Đã xóa lớp học phần.');
    }
}