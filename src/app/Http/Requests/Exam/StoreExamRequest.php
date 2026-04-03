<?php

namespace App\Http\Requests\Exam;

use App\Models\User;
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
            'show_score_after_submit' => $this->boolean('show_score_after_submit'),
            'show_answers_after_submit' => $this->boolean('show_answers_after_submit'),
            'multiple_choice_scoring_method' => $this->input('multiple_choice_scoring_method', 'all_or_nothing'),
        ]);
    }

    public function rules(): array
    {
        $authUser = \Illuminate\Support\Facades\Auth::user();
        $assignedSubjectIds = [];

        if ($authUser instanceof User) {
            $assignedSubjectIds = $authUser
                ->subjects()
                ->pluck('subjects.id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->toArray();
        }

        $subjectId = (int) $this->input('subject_id');

        $rules = [
            'title'                       => 'required|string|max:255',
            'subject_id'                  => ['required', Rule::exists('subjects', 'id')->where(fn($q) => $q->whereIn('id', $assignedSubjectIds))],
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
            'multiple_choice_scoring_method' => ['required', 'string', Rule::in(['all_or_nothing', 'partial_credit'])],
        ];

        if ($this->input('creation_mode') === 'matrix') {
            $rules['matrix']                    = 'required|array|min:1';
            $rules['matrix.*.chapter_id']       = [
                'nullable',
                Rule::exists('chapters', 'id')->where(function ($query) use ($subjectId): void {
                    if ($subjectId > 0) {
                        $query->where('subject_id', $subjectId);
                    }
                }),
            ];
            $rules['matrix.*.difficulty']        = 'required|in:remember,understand,apply,analyze';
            $rules['matrix.*.question_type_id']  = 'nullable|exists:question_types,id';
            $rules['matrix.*.question_count']    = 'required|integer|min:1';
        } else {
            $rules['question_ids']   = 'required|array|min:1';
            $rules['question_ids.*'] = [
                Rule::exists('questions', 'id')->where(function ($query) use ($subjectId): void {
                    if ($subjectId > 0) {
                        $query->where('subject_id', $subjectId);
                    }
                }),
            ];
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
            'subject_id.exists'              => 'Bạn chỉ có thể tạo đề cho các môn được phân công.',
            'exam_type.required'             => 'Loại đề thi là bắt buộc.',
            'exam_type.in'                   => 'Loại đề thi không hợp lệ.',
            'creation_mode.required'         => 'Chế độ tạo đề là bắt buộc.',
            'creation_mode.in'               => 'Chế độ tạo đề không hợp lệ.',
            'question_ids.required'          => 'Vui lòng chọn ít nhất một câu hỏi.',
            'question_ids.min'               => 'Vui lòng chọn ít nhất một câu hỏi.',
            'question_ids.*.exists'          => 'Câu hỏi được chọn không tồn tại.',
            'matrix.required'                => 'Vui lòng cấu hình ít nhất một hàng ma trận.',
            'matrix.*.difficulty.required'   => 'Vui lòng chọn mức độ khó.',
            'matrix.*.question_count.required' => 'Vui lòng nhập số lượng câu hỏi.',
            'matrix.*.question_count.min'    => 'Số lượng câu hỏi phải ít nhất 1.',
        ];
    }
}
