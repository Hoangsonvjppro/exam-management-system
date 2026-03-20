<?php

namespace App\Http\Requests\CourseSection;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware (lecturer_role)
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'code'         => ['required', 'string', 'max:50', 'unique:course_sections,code'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Tên lớp học phần là bắt buộc.',
            'code.required'     => 'Mã lớp học phần là bắt buộc.',
            'code.unique'       => 'Mã lớp học phần đã tồn tại.',
            'max_students.min'  => 'Số sinh viên tối đa phải ít nhất 1.',
            'max_students.max'  => 'Số sinh viên tối đa không được vượt quá 500.',
        ];
    }
}
