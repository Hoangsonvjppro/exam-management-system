<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'department_admin', 'lecturer']);
    }

    public function rules(): array
    {
        $currentId = $this->route('semester')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'term' => [
                'required',
                'integer',
                'in:1,2,3',
                Rule::unique('semesters')->where(function ($query) {
                    return $query->where('year', $this->year)->where('term', $this->term);
                })->ignore($currentId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'term.unique' => 'Học kỳ và năm học này đã tồn tại.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_current' => $this->has('is_current') ? 1 : 0,
        ]);
    }
}
