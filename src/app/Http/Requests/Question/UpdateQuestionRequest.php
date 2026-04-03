<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $assignedSubjectIds = $this->user()
            ? $this->user()->subjects()->pluck('subjects.id')->map(fn($id) => (int) $id)->all()
            : [];

        return [
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(fn($query) => $query->whereIn('id', $assignedSubjectIds)),
            ],
            'chapter_id' => [
                'nullable',
                'integer',
                Rule::exists('chapters', 'id')->where(function ($query): void {
                    if ($this->input('subject_id')) {
                        $query->where('subject_id', $this->integer('subject_id'));
                    }
                }),
            ],
            'question_type_id' => ['required', 'integer', Rule::exists('question_types', 'id')],
            'content' => ['required', 'string', 'min:5'],
            'difficulty' => ['required', 'string', Rule::in(['remember', 'understand', 'apply', 'analyze'])],

            // Validate options array structure coming from the form
            'options' => ['required', 'array', 'min:2'],
            'options.*.content' => ['required', 'string'],
            'correct_options' => ['required', 'array', 'min:1'],
            'correct_options.*' => ['integer', 'min:0'],
        ];
    }
}
