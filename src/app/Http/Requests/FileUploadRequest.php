<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================
 * FileUploadRequest — Validation cho file upload
 * ============================================================
 * Validate:
 *   - File bắt buộc
 *   - Kích thước tối đa 20MB
 *   - Chỉ cho phép các MIME types an toàn
 *   - Messages tiếng Việt
 * ============================================================
 */
class FileUploadRequest extends FormRequest
{
    /**
     * Xác định user có quyền gửi request không.
     */
    public function authorize(): bool
    {
        return true; // Auth đã kiểm tra ở middleware
    }

    /**
     * Quy tắc validation.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480', // 20MB = 20 * 1024 KB
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif',
            ],

            // Tuỳ chọn: thư mục lưu trữ
            'directory' => ['nullable', 'string', 'max:255'],

            // Tuỳ chọn: file public hay private
            'is_public' => ['nullable', 'boolean'],

            // Tuỳ chọn: gán cho model nào (polymorphic)
            'used_by_type' => ['nullable', 'string', 'max:255'],
            'used_by_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom error messages (tiếng Việt).
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file để tải lên.',
            'file.file' => 'Dữ liệu gửi lên không phải là file hợp lệ.',
            'file.max' => 'File không được vượt quá 20MB.',
            'file.mimes' => 'Chỉ chấp nhận các loại file: PDF, Word, Excel, PowerPoint, JPG, PNG, GIF.',
        ];
    }
}
