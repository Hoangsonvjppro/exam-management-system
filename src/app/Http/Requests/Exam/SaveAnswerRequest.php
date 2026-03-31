<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class SaveAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function rules(): array
    {
        return [
            'question_id'        => 'required|integer|exists:questions,id',
            'question_option_id' => 'nullable|integer|exists:question_options,id',
            'option_ids'         => 'nullable|array',
            'option_ids.*'       => 'integer|exists:question_options,id',
            'answer_text'        => 'nullable|string',
            'tab_switch_count'   => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.required'        => 'Câu hỏi là bắt buộc.',
            'question_id.exists'          => 'Câu hỏi không tồn tại.',
            'question_option_id.required' => 'Đáp án là bắt buộc.',
            'question_option_id.exists'   => 'Đáp án không tồn tại.',
        ];
    }
}
