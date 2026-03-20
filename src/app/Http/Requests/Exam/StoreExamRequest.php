<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function rules(): array
    {
        return [
            'title'                    => 'required|string|max:255',
            'description'              => 'nullable|string',
            'duration_minutes'         => 'required|integer|min:1',
            'start_time'               => 'nullable|date',
            'end_time'                 => 'nullable|date|after_or_equal:start_time',
            'exam_type'                => 'required|in:official,practice',
            'show_score_after_submit'  => 'boolean',
            'show_answers_after_submit'=> 'boolean',
            'question_ids'             => 'required|array',
            'question_ids.*'           => 'exists:questions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'Tiêu đề đề thi là bắt buộc.',
            'duration_minutes.required'=> 'Thời gian làm bài là bắt buộc.',
            'duration_minutes.min'     => 'Thời gian làm bài phải ít nhất 1 phút.',
            'end_time.after_or_equal'  => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'exam_type.required'       => 'Loại đề thi là bắt buộc.',
            'exam_type.in'             => 'Loại đề thi không hợp lệ.',
            'question_ids.required'    => 'Vui lòng chọn ít nhất một câu hỏi.',
            'question_ids.*.exists'    => 'Câu hỏi được chọn không tồn tại.',
        ];
    }
}
