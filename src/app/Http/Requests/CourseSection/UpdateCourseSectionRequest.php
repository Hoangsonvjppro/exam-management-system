<?php

namespace App\Http\Requests\CourseSection;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function rules(): array
    {
        $sectionId = $this->route('section')?->id;

        return [
            'name'         => ['required', 'string', 'max:255'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:500'],
            'status'       => ['required', 'in:active,archived,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Tên lớp học phần là bắt buộc.',
            'code.required'     => 'Mã lớp học phần là bắt buộc.',
            'code.unique'       => 'Mã lớp học phần đã tồn tại.',
            'status.required'   => 'Trạng thái là bắt buộc.',
            'status.in'         => 'Trạng thái không hợp lệ.',
            'max_students.min'  => 'Số sinh viên tối đa phải ít nhất 1.',
            'max_students.max'  => 'Số sinh viên tối đa không được vượt quá 500.',
        ];
    }
}
