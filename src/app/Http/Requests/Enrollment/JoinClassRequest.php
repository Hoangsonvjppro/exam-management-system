<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

class JoinClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in Controller
    }

    public function rules(): array
    {
        return [
            'invite_code' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'invite_code.required' => 'Vui lòng nhập mã tham gia lớp.',
        ];
    }
}
