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
            'question_option_id' => 'required|integer|exists:question_options,id',
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
