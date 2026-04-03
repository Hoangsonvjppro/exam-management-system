<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authentication handled by middleware
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:users,student_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Vui lòng nhập họ tên đầy đủ.',
            'name.max'              => 'Họ tên không được vượt quá 255 ký tự.',
            'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
            'student_code.unique'   => 'Mã số sinh viên đã tồn tại trong hệ thống.',
            'student_code.regex'    => 'MSSV phải gồm đúng 10 chữ số.',
        ];
    }
}
