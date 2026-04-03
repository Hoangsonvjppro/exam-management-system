<?php

namespace App\Http\Requests\CourseSection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware (lecturer_role)
    }

    public function rules(): array
    {
        $assignedSubjectIds = $this->user()
            ? $this->user()->subjects()->pluck('subjects.id')->map(fn($id) => (int) $id)->all()
            : [];

        return [
            'name'         => ['required', 'string', 'max:255'],
            'subject_id'   => ['required', Rule::exists('subjects', 'id')->where(fn($query) => $query->whereIn('id', $assignedSubjectIds))],
            'semester_id'  => ['required', 'exists:semesters,id'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Tên lớp học phần là bắt buộc.',
            'subject_id.required'  => 'Vui lòng chọn môn học.',
            'subject_id.exists'    => 'Bạn chỉ có thể tạo lớp học phần cho các môn được phân công.',
            'semester_id.required' => 'Vui lòng chọn học kỳ.',
            'max_students.min'  => 'Số sinh viên tối đa phải ít nhất 1.',
            'max_students.max'  => 'Số sinh viên tối đa không được vượt quá 500.',
        ];
    }
}
