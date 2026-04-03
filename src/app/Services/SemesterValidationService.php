<?php

namespace App\Services;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SemesterValidationService
{
    private const MAX_FUTURE_YEAR_OFFSET = 10;

    /**
     * @param array<string, mixed> $data
     */
    public function validateForUpsert(array $data, ?int $ignoreId = null): void
    {
        Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:' . now()->year, 'max:' . (now()->year + self::MAX_FUTURE_YEAR_OFFSET)],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ], [
            'year.required' => 'Vui lòng chọn năm học bắt đầu.',
            'year.min' => 'Năm học bắt đầu chỉ được chọn từ năm hiện tại trở đi.',
            'year.max' => 'Năm học bắt đầu vượt quá phạm vi cho phép (tối đa +10 năm).',
            'term.required' => 'Vui lòng chọn học kỳ.',
            'term.in' => 'Học kỳ được chọn không hợp lệ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc trùng ngày bắt đầu.',
        ])->validate();

        $startDate = Carbon::parse((string) $data['start_date'])->startOfDay();
        $endDate = Carbon::parse((string) $data['end_date'])->endOfDay();
        $today = now()->startOfDay();

        if ($endDate->lt($today)) {
            throw ValidationException::withMessages([
                'end_date' => 'Không thể cấu hình học kỳ đã kết thúc trong quá khứ.',
            ]);
        }

        if ((int) $data['year'] !== $startDate->year) {
            throw ValidationException::withMessages([
                'year' => 'Năm học bắt đầu phải trùng với năm của ngày bắt đầu học kỳ.',
            ]);
        }

        if (! in_array($endDate->year, [$startDate->year, $startDate->year + 1], true)) {
            throw ValidationException::withMessages([
                'end_date' => 'Ngày kết thúc phải nằm trong cùng năm bắt đầu hoặc năm kế tiếp.',
            ]);
        }

        if ($startDate->diffInDays($endDate) > 230) {
            throw ValidationException::withMessages([
                'end_date' => 'Thời lượng học kỳ vượt quá giới hạn cho phép (230 ngày).',
            ]);
        }

        $duplicateQuery = Semester::query()
            ->where('year', (int) $data['year'])
            ->where('term', (int) $data['term']);

        if ($ignoreId !== null) {
            $duplicateQuery->whereKeyNot($ignoreId);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'term' => 'Học kỳ này đã tồn tại trong năm học đã chọn.',
            ]);
        }
    }
}
