<?php

namespace App\Http\Requests\CourseSection;

use App\Models\Semester;
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
            'semester_id'  => [
                'required',
                Rule::exists('semesters', 'id')->where(function ($query): void {
                    $query->where('status', '!=', Semester::STATUS_ARCHIVED)
                        ->whereDate('end_date', '>=', now()->toDateString());
                }),
            ],
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
            'semester_id.exists'   => 'Chỉ có thể tạo lớp học phần cho học kỳ hiện tại hoặc sắp tới.',
            'max_students.min'  => 'Số sinh viên tối đa phải ít nhất 1.',
            'max_students.max'  => 'Số sinh viên tối đa không được vượt quá 500.',
        ];
    }
}
