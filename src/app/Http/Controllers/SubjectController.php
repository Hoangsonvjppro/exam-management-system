<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::orderBy('code')->get();
        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(StoreSubjectRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Subject::create($request->validated());
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu dữ liệu! Vui lòng thử lại.');
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Tạo môn học thành công!');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(StoreSubjectRequest $request, Subject $subject)
    {
        try {
            DB::transaction(function () use ($request, $subject) {
                $subject->update($request->validated());
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Cập nhật môn học thành công!');
    }

    public function destroy(Subject $subject)
    {
        try {
            $subject->delete();
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Không thể xoá môn học này. Có thể dữ liệu đang được sử dụng.');
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Đã xóa môn học.');
    }
}
