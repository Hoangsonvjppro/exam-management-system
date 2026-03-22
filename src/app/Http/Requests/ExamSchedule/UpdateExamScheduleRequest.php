<?php

namespace App\Http\Requests\ExamSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_date'     => 'required|date',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'max_students'  => 'nullable|integer|min:1',
            'notes'         => 'nullable|string|max:1000',
            'status'        => 'sometimes|in:scheduled,in_progress,completed,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_date.required'  => 'Ngày thi là bắt buộc.',
            'start_time.required' => 'Giờ bắt đầu là bắt buộc.',
            'end_time.required'   => 'Giờ kết thúc là bắt buộc.',
            'end_time.after'      => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ];
    }
}
