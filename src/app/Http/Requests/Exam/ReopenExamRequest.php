<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class ReopenExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function rules(): array
    {
        return [
            'reopen_reason' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reopen_reason.required' => 'Vui lòng nhập lý do mở lại đề thi.',
            'reopen_reason.max'      => 'Lý do không được vượt quá 1000 ký tự.',
        ];
    }
}
