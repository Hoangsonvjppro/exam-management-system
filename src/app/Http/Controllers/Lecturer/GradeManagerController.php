<?php

namespace App\Http\Controllers\Lecturer;

use App\Exports\ProcessGradesExport;
use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\GradeColumn;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GradeManagerController extends Controller
{
    public function export(CourseSection $section): BinaryFileResponse
    {
        Gate::authorize('manage', $section);

        $section->load([
            'subject:id,code,name',
            'semester:id,name',
            'students:id,name,student_code',
            'gradeColumns' => fn($query) => $query->orderBy('order'),
            'gradeColumns.studentGrades:id,grade_column_id,student_id,score',
        ]);

        $fileName = sprintf(
            'diem-qua-trinh-%s-%s.xlsx',
            str($section->code)->slug('-'),
            now()->format('Ymd-His')
        );

        return Excel::download(new ProcessGradesExport($section), $fileName);
    }

    /**
     * Tạo cột điểm mới cho lớp
     */
    public function storeColumn(Request $request, CourseSection $section)
    {
        Gate::authorize('manage', $section);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        $column = $section->gradeColumns()->create([
            'name' => $validated['name'],
            'weight' => $validated['weight'],
            'order' => $section->gradeColumns()->max('order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo cột điểm thành công',
            'column' => $column
        ]);
    }

    /**
     * Cập nhật thông tin cột điểm
     */
    public function updateColumn(Request $request, CourseSection $section, GradeColumn $column)
    {
        Gate::authorize('manage', $section);

        if ($column->course_section_id !== $section->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        $column->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật cột điểm thành công',
            'column' => $column
        ]);
    }

    /**
     * Xóa cột điểm
     */
    public function destroyColumn(CourseSection $section, GradeColumn $column)
    {
        Gate::authorize('manage', $section);

        if ($column->course_section_id !== $section->id) {
            abort(404);
        }

        $column->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa cột điểm'
        ]);
    }

    /**
     * Lưu điểm chi tiết cho sinh viên vào 1 cột điểm
     */
    public function saveGrades(Request $request, CourseSection $section, GradeColumn $column)
    {
        Gate::authorize('manage', $section);

        if ($column->course_section_id !== $section->id) {
            abort(404);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'score' => 'nullable|numeric|min:0|max:10',
            'note' => 'nullable|string|max:500'
        ]);

        // Kiểm tra xem sinh viên có trong lớp không
        $isEnrolled = $section->students()->where('student_id', $validated['student_id'])->exists();
        if (!$isEnrolled) {
            return response()->json(['error' => 'Sinh viên không thuộc lớp này'], 403);
        }

        $updateData = ['updated_by' => Auth::id()];
        if (array_key_exists('score', $validated)) {
            $updateData['score'] = $validated['score'];
        }
        if (array_key_exists('note', $validated)) {
            $updateData['note'] = $validated['note'];
        }

        $grade = StudentGrade::updateOrCreate(
            [
                'grade_column_id' => $column->id,
                'student_id' => $validated['student_id']
            ],
            $updateData
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu điểm',
            'grade' => $grade
        ]);
    }
}
