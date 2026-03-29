<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function rules(): array
    {
        return [
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Tiêu đề thông báo là bắt buộc.',
            'title.max'        => 'Tiêu đề không được vượt quá 255 ký tự.',
            'message.required' => 'Nội dung thông báo là bắt buộc.',
        ];
    }
}
