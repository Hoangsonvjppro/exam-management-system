<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSemesterRequest;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SemesterController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::orderByDesc('year')->orderBy('term')->get();
        return view('semesters.index', compact('semesters'));
    }

    public function create()
    {
        return view('semesters.create');
    }

    public function store(StoreSemesterRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if ($request->input('is_current')) {
                    Semester::where('is_current', 1)->update(['is_current' => 0]);
                }

                Semester::create($request->validated());
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu dữ liệu! Vui lòng thử lại.');
        }

        return redirect()->route('admin.semesters.index')->with('success', 'Tạo học kỳ thành công!');
    }

    public function edit(Semester $semester)
    {
        return view('semesters.edit', compact('semester'));
    }

    public function update(StoreSemesterRequest $request, Semester $semester)
    {
        try {
            DB::transaction(function () use ($request, $semester) {
                if ($request->input('is_current')) {
                    Semester::where('id', '!=', $semester->id)->where('is_current', 1)->update(['is_current' => 0]);
                }
                $semester->update($request->validated());
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
        }

        return redirect()->route('admin.semesters.index')->with('success', 'Cập nhật học kỳ thành công!');
    }

    public function destroy(Semester $semester)
    {
        try {
            $semester->delete();
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Không thể xoá học kỳ này. Có thể dữ liệu đang được sử dụng.');
        }

        return redirect()->route('admin.semesters.index')->with('success', 'Đã xóa học kỳ.');
    }
}
