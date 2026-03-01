<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Spatie: chỉ admin mới được thực hiện
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        // Khi UPDATE, $this->route('course_section') trả về model nhờ Route Model Binding
        $currentId = $this->route('course_section')?->id;

        return [
            // ── Lớp học phần ──────────────────────────────────
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('course_sections', 'code')->ignore($currentId),
            ],
            'subject_id'   => ['required', 'integer', 'exists:subjects,id'],
            'semester_id'  => ['required', 'integer', 'exists:semesters,id'],
            'lecturer_id'  => ['required', 'integer', 'exists:users,id'],
            'max_students' => ['required', 'integer', 'min:1', 'max:999'],
            'status'       => ['required', Rule::in(['active', 'archived', 'cancelled'])],

            // ── Lịch học (tuỳ chọn khi tạo lớp) ──────────────
            'schedules'                => ['nullable', 'array', 'max:7'], // Tối đa 7 buổi/tuần
            'schedules.*.day_of_week'  => ['required', 'integer', 'between:2,8'],
            'schedules.*.start_period' => ['required', 'integer', 'between:1,16'],
            'schedules.*.end_period'   => [
                'required', 'integer', 'between:1,16',
                'gte:schedules.*.start_period', // end >= start (nghiệp vụ từ schema CHECK constraint)
            ],
            'schedules.*.room' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique'                    => 'Mã lớp học phần này đã tồn tại.',
            'subject_id.exists'              => 'Môn học không tồn tại trong hệ thống.',
            'semester_id.exists'             => 'Học kỳ không tồn tại trong hệ thống.',
            'lecturer_id.exists'             => 'Giảng viên không tồn tại trong hệ thống.',
            'schedules.*.day_of_week.between'=> 'Thứ trong tuần phải từ 2 (Thứ Hai) đến 8 (Chủ Nhật).',
            'schedules.*.end_period.gte'     => 'Tiết kết thúc phải ≥ tiết bắt đầu.',
        ];
    }
}