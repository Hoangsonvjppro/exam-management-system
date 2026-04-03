<?php

namespace App\Http\Requests\ExamSchedule;

use App\Models\CourseSection;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Exam;
use App\Services\SemesterGovernanceService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateExamScheduleRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $scheduleMode = (string) ($this->input('schedule_mode') ?: 'within_day');

        $merge = [
            'schedule_mode' => $scheduleMode,
            'disable_attempt_timer' => $this->boolean('disable_attempt_timer'),
        ];

        if (! $this->filled('end_date') && $this->filled('exam_date')) {
            $merge['end_date'] = $this->input('exam_date');
        }

        if ($scheduleMode === 'in_range') {
            $merge['start_time'] = '00:00';
            $merge['end_time'] = '23:59';
        }

        $this->merge($merge);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schedule = $this->route('schedule');
        $examId = $schedule->exam_id;

        return [
            'schedule_mode' => 'required|in:within_day,in_range',
            'disable_attempt_timer' => 'boolean',
            'exam_date'     => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:exam_date',
            'start_time'    => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($this->input('schedule_mode') !== 'within_day') {
                        return;
                    }

                    $window = $this->parseScheduleWindow();

                    if (! $window) {
                        return;
                    }

                    [$startAt] = $window;
                    if ($startAt->lessThanOrEqualTo(now())) {
                        $fail('Thời gian bắt đầu phải lớn hơn thời điểm hiện tại.');
                    }
                },
            ],
            'end_time'      => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($examId, $schedule) {
                    $scheduleMode = (string) $this->input('schedule_mode', 'within_day');
                    $disableAttemptTimer = (bool) $this->boolean('disable_attempt_timer');
                    $window = $this->parseScheduleWindow();

                    if (! $window) {
                        return;
                    }

                    [$startAt, $endAt] = $window;

                    if ($endAt->lessThanOrEqualTo($startAt)) {
                        $fail('Thời điểm kết thúc phải sau thời điểm bắt đầu.');
                        return;
                    }

                    $exam = Exam::find($examId);

                    if ($exam) {
                        try {
                            $diff = $startAt->diffInMinutes($endAt);

                            if (! $disableAttemptTimer && $scheduleMode === 'within_day' && $diff < $exam->duration_minutes) {
                                $fail("Thời gian thi ({$diff} phút) không đủ cho thời lượng đề thi ({$exam->duration_minutes} phút).");
                            }
                        } catch (\Exception $e) {
                        }
                    }
                },
            ],
            'notes'         => 'nullable|string|max:1000',
            'status'        => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'link_grade_column' => 'nullable|boolean',
            'grade_column_id'   => 'nullable|integer',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->input('schedule_mode') === 'within_day') {
                $startDate = (string) $this->input('exam_date');
                $endDate = (string) $this->input('end_date');

                if ($startDate !== '' && $endDate !== '' && $startDate !== $endDate) {
                    $validator->errors()->add('end_date', 'Chế độ kiểm tra trong ngày yêu cầu ngày bắt đầu và kết thúc phải trùng nhau.');
                }
            }

            $schedule = $this->route('schedule');
            $section = $schedule?->courseSection;
            $window = $this->parseScheduleWindow();

            if (! $section instanceof CourseSection || ! $window) {
                return;
            }

            try {
                app(SemesterGovernanceService::class)->assertSectionAllowsExamScheduling($section);
                [$startAt, $endAt] = $window;
                app(SemesterGovernanceService::class)->assertScheduleWindowInsideSemester($section, $startAt, $endAt);
            } catch (ValidationException $e) {
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add((string) $field, (string) $message);
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'exam_date.required'         => 'Ngày bắt đầu là bắt buộc.',
            'exam_date.after_or_equal'   => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_date.required'          => 'Ngày kết thúc là bắt buộc.',
            'end_date.after_or_equal'    => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'start_time.required'        => 'Giờ bắt đầu là bắt buộc.',
            'end_time.required'          => 'Giờ kết thúc là bắt buộc.',
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function parseScheduleWindow(): ?array
    {
        $startDate = $this->input('exam_date');
        $endDate = $this->input('end_date') ?: $startDate;
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');

        if (! $startDate || ! $startTime || ! $endDate || ! $endTime) {
            return null;
        }

        try {
            $startAt = Carbon::createFromFormat('Y-m-d H:i', $startDate . ' ' . $startTime);
            $endAt = Carbon::createFromFormat('Y-m-d H:i', $endDate . ' ' . $endTime);
        } catch (\Throwable $e) {
            return null;
        }

        return [$startAt, $endAt];
    }
}
