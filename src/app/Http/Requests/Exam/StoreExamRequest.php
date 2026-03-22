<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function rules(): array
    {
        $rules = [
            'title'                    => 'required|string|max:255',
            'description'              => 'nullable|string',
            'duration_minutes'         => 'required|integer|min:1',
            'start_time'               => 'nullable|date',
            'end_time'                 => 'nullable|date|after_or_equal:start_time',
            'exam_type'                => 'required|in:official,practice',
            'show_score_after_submit'  => 'boolean',
            'show_answers_after_submit'=> 'boolean',
            'creation_mode'            => 'required|in:manual,matrix',
        ];

        if ($this->input('creation_mode') === 'matrix') {
            $rules['matrix']                    = 'required|array|min:1';
            $rules['matrix.*.chapter_id']       = 'nullable|exists:chapters,id';
            $rules['matrix.*.difficulty']        = 'required|in:remember,understand,apply,analyze';
            $rules['matrix.*.question_type_id']  = 'nullable|exists:question_types,id';
            $rules['matrix.*.question_count']    = 'required|integer|min:1';
            $rules['matrix.*.points_each']       = 'nullable|numeric|min:0.01';
        } else {
            $rules['question_ids']   = 'required|array';
            $rules['question_ids.*'] = 'exists:questions,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required'                 => 'Tiêu đề đề thi là bắt buộc.',
            'duration_minutes.required'      => 'Thời gian làm bài là bắt buộc.',
            'duration_minutes.min'           => 'Thời gian làm bài phải ít nhất 1 phút.',
            'end_time.after_or_equal'        => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'exam_type.required'             => 'Loại đề thi là bắt buộc.',
            'exam_type.in'                   => 'Loại đề thi không hợp lệ.',
            'creation_mode.required'         => 'Chế độ tạo đề là bắt buộc.',
            'creation_mode.in'               => 'Chế độ tạo đề không hợp lệ.',
            'question_ids.required'          => 'Vui lòng chọn ít nhất một câu hỏi.',
            'question_ids.*.exists'          => 'Câu hỏi được chọn không tồn tại.',
            'matrix.required'                => 'Vui lòng cấu hình ít nhất một hàng ma trận.',
            'matrix.*.difficulty.required'   => 'Vui lòng chọn mức độ khó.',
            'matrix.*.question_count.required' => 'Vui lòng nhập số lượng câu hỏi.',
            'matrix.*.question_count.min'    => 'Số lượng câu hỏi phải ít nhất 1.',
        ];
    }
}
