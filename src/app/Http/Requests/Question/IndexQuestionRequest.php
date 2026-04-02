<?php

namespace App\Http\Requests\Question;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;

class IndexQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('viewAny', Question::class);
    }

    public function rules(): array
    {
        return [
            'sub-sel-ques' => ['nullable', 'string', 'exists:subjects,code'],
            'diff-sel-ques' => ['nullable', 'string', 'exists:difficulties,code'],
            'chap-sel-ques' => ['nullable', 'integer', 'exists:chapters,id'],
            'status-sel-ques' => ['nullable', 'string', 'in:approved,draft,hidden'],
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}
