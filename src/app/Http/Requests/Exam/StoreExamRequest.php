<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy in Controller
    }

    public function prepareForValidation()
    {
        $this->merge([
            'allow_late_entrance' => $this->boolean('allow_late_entrance'),
        ]);
    }

    public function rules(): array
    {
        $lecturerSubjectIds = \Illuminate\Support\Facades\Auth::user()->courseSections()->pluck('subject_id')->unique()->toArray();

        $rules = [
            'title'                       => 'required|string|max:255',
            'subject_id'                  => ['required', Rule::exists('subjects', 'id')->where(fn($q) => $q->whereIn('id', $lecturerSubjectIds))],
            'description'                 => 'nullable|string',
            'duration_minutes'            => 'required|integer|min:1',
            'exam_type'                   => 'required|in:official,practice',
            'show_score_after_submit'     => 'boolean',
            'show_answers_after_submit'   => 'boolean',
            'allow_late_entrance'         => 'boolean',
            'late_entrance_limit_minutes' => 'nullable|integer|min:1',
            'late_entrance_behavior'      => 'required|in:fixed_end,flexible_duration',
            'min_duration_before_submit'  => 'required|integer|min:0',
            'creation_mode'               => 'required|in:manual,matrix',
        ];

        if ($this->input('creation_mode') === 'matrix') {
            $rules['matrix']                    = 'required|array|min:1';
            $rules['matrix.*.chapter_id']       = 'nullable|exists:chapters,id';
            $rules['matrix.*.difficulty']        = 'required|in:remember,understand,apply,analyze';
            $rules['matrix.*.question_type_id']  = 'nullable|exists:question_types,id';
            $rules['matrix.*.question_count']    = 'required|integer|min:1';
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
            'subject_id.required'            => 'Môn học là bắt buộc.',
            'subject_id.exists'              => 'Môn học không tồn tại.',
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
