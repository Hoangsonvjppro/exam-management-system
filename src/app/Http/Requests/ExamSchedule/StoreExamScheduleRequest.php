<?php

namespace App\Http\Requests\ExamSchedule;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Exam;
use App\Models\CourseSection;
use App\Services\SemesterGovernanceService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class StoreExamScheduleRequest extends FormRequest
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
        return [
            'exam_id'              => 'required|exists:exams,id',
            'schedule_mode'        => 'required|in:within_day,in_range',
            'disable_attempt_timer' => 'boolean',
            'course_section_ids'   => 'required|array|min:1',
            'course_section_ids.*' => [
                'required',
                'distinct',
                'exists:course_sections,id',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    $window = $this->parseScheduleWindow();

                    if ($examId) {
                        $exam = Exam::find($examId);
                        $section = CourseSection::find($value);
                        if ($exam && $section && $exam->subject_id !== $section->subject_id) {
                            $fail("Lớp học phần {$section->name} không thuộc môn học của đề thi này.");
                        }

                        if ($section) {
                            try {
                                app(SemesterGovernanceService::class)->assertSectionAllowsExamScheduling($section);

                                if ($window) {
                                    [$startAt, $endAt] = $window;
                                    app(SemesterGovernanceService::class)->assertScheduleWindowInsideSemester($section, $startAt, $endAt);
                                }
                            } catch (ValidationException $e) {
                                $message = collect($e->errors())->flatten()->first() ?? 'Không thể tạo lịch thi cho học kỳ này.';
                                $fail((string) $message);
                            }
                        }
                    }
                },
            ],
            'exam_date'         => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after_or_equal:exam_date',
            'start_time'        => [
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

                    // Nới lỏng 10 phút để tránh lỗi khi GV thao tác chậm 
                    // (Ví dụ: GV chọn 08:30 nhưng lúc bấm Lưu đã là 08:31)
                    if ($startAt->lessThanOrEqualTo(now()->subMinutes(10))) {
                        $fail('Thời gian bắt đầu phải lớn hơn thời điểm hiện tại (cho phép trễ đến 10 phút).');
                    }
                },
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
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

                    if ($examId) {
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
                    }
                },
            ],
            'notes'         => 'nullable|string|max:1000',
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
        });
    }

    public function messages(): array
    {
        return [
            'exam_id.required'           => 'Đề thi là bắt buộc.',
            'exam_id.exists'             => 'Đề thi không tồn tại.',
            'course_section_ids.required' => 'Phải chọn ít nhất một lớp học phần.',
            'course_section_ids.array'   => 'Dữ liệu lớp học phần không hợp lệ.',
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
