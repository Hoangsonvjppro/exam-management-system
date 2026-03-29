<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Không yêu cầu mảng answers nữa, hệ thống sử dụng Single Source of Truth từ DB
        ];
    }
}
