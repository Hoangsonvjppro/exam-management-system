<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
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
        return [
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
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
            'status' => ['required', 'string', Rule::in(['draft', 'approved', 'hidden'])],
            'explanation' => ['nullable', 'string'],
        ];
    }
}
