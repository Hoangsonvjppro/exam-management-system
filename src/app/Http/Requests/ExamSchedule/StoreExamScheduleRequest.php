<?php

namespace App\Http\Requests\ExamSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_date'     => 'required|date|after_or_equal:today',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'max_students'  => 'nullable|integer|min:1',
            'notes'         => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_date.required'        => 'Ngày thi là bắt buộc.',
            'exam_date.after_or_equal'  => 'Ngày thi phải từ hôm nay trở đi.',
            'start_time.required'       => 'Giờ bắt đầu là bắt buộc.',
            'end_time.required'         => 'Giờ kết thúc là bắt buộc.',
            'end_time.after'            => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ];
    }
}
