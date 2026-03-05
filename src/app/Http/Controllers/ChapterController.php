<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterRequest;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $chapters = Chapter::with('subject')
            ->orderBy('subject_id')
            ->orderBy('order')
            ->get();
        return view('chapters.index', compact('chapters'));
    }

    public function create()
    {
        return view('chapters.create');
    }

    public function store(StoreChapterRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Chapter::create($request->validated());
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu dữ liệu! Vui lòng thử lại.');
        }

        return redirect()->route('admin.chapters.index')->with('success', 'Tạo chương thành công!');
    }

    public function edit(Chapter $chapter)
    {
        return view('chapters.edit', compact('chapter'));
    }

    public function update(StoreChapterRequest $request, Chapter $chapter)
    {
        try {
            DB::transaction(function () use ($request, $chapter) {
                $chapter->update($request->validated());
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
        }

        return redirect()->route('admin.chapters.index')->with('success', 'Cập nhật chương thành công!');
    }

    public function destroy(Chapter $chapter)
    {
        try {
            $chapter->delete();
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Không thể xoá chương này. Có thể dữ liệu đang được sử dụng.');
        }

        return redirect()->route('admin.chapters.index')->with('success', 'Đã xóa chương.');
    }
}
