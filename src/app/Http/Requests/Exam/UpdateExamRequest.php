<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function prepareForValidation()
    {
        $this->merge([
            'allow_late_entrance' => $this->boolean('allow_late_entrance'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title'                       => 'required|string|max:255',
            'subject_id'                  => 'required|exists:subjects,id',
            'description'                 => 'nullable|string',
            'duration_minutes'            => 'required|integer|min:1',
            'exam_type'                   => 'required|in:official,practice',
            'show_score_after_submit'     => 'boolean',
            'show_answers_after_submit'   => 'boolean',
            'allow_late_entrance'         => 'boolean',
            'late_entrance_limit_minutes' => 'nullable|integer|min:1',
            'late_entrance_behavior'      => 'required|in:fixed_end,flexible_duration',
            'min_duration_before_submit'  => 'required|integer|min:0',
            'question_ids'                => 'nullable|array',
            'question_ids.*'              => 'exists:questions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'Tiêu đề đề thi là bắt buộc.',
            'duration_minutes.required'=> 'Thời gian làm bài là bắt buộc.',
            'duration_minutes.min'     => 'Thời gian làm bài phải ít nhất 1 phút.',
            'subject_id.required'      => 'Môn học là bắt buộc.',
            'subject_id.exists'        => 'Môn học không tồn tại.',
            'exam_type.required'       => 'Loại đề thi là bắt buộc.',
            'exam_type.in'             => 'Loại đề thi không hợp lệ.',
            'question_ids.*.exists'    => 'Câu hỏi được chọn không tồn tại.',
        ];
    }
}
