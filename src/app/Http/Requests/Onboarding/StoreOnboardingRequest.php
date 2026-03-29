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
            'student_code' => ['required', 'string', 'max:20', 'unique:users,student_code'],
            'class_name'   => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
            'student_code.unique'   => 'Mã số sinh viên đã tồn tại trong hệ thống.',
            'student_code.max'      => 'Mã số sinh viên không được vượt quá 20 ký tự.',
            'class_name.required'   => 'Vui lòng nhập tên lớp.',
            'class_name.max'        => 'Tên lớp không được vượt quá 100 ký tự.',
        ];
    }
}
