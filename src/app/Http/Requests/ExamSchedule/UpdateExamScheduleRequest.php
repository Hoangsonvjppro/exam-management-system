<?php

namespace App\Http\Requests\ExamSchedule;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UpdateExamScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schedule = $this->route('schedule');
        $examId = $schedule->exam_id;

        return [
            'exam_date'     => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:exam_date',
            'start_time'    => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
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

                            if ($diff < $exam->duration_minutes) {
                                $fail("Thời gian thi ({$diff} phút) không đủ cho thời lượng đề thi ({$exam->duration_minutes} phút).");
                            }
                        } catch (\Exception $e) {
                        }
                    }

                    // Kiểm tra trùng lịch cho lớp học phần
                    $hasSectionConflict = ExamSchedule::where('course_section_id', $schedule->course_section_id)
                        ->where('id', '!=', $schedule->id)
                        ->whereRaw('TIMESTAMP(exam_date, start_time) < ?', [$endAt->toDateTimeString()])
                        ->whereRaw('TIMESTAMP(COALESCE(end_date, exam_date), end_time) > ?', [$startAt->toDateTimeString()])
                        ->exists();

                    if ($hasSectionConflict) {
                        $fail('Lớp học phần này đã có lịch thi khác trùng vào thời gian này.');
                    }

                    // Kiểm tra trùng lịch cho giảng viên
                    $lecturerId = Auth::id();
                    $hasLecturerConflict = ExamSchedule::whereHas('courseSection', function ($q) use ($lecturerId) {
                        $q->where('lecturer_id', $lecturerId);
                    })
                        ->where('id', '!=', $schedule->id)
                        ->whereRaw('TIMESTAMP(exam_date, start_time) < ?', [$endAt->toDateTimeString()])
                        ->whereRaw('TIMESTAMP(COALESCE(end_date, exam_date), end_time) > ?', [$startAt->toDateTimeString()])
                        ->exists();

                    if ($hasLecturerConflict) {
                        $fail('Bạn đã có một lịch thi khác trùng vào thời gian này.');
                    }
                },
            ],
            'notes'         => 'nullable|string|max:1000',
            'status'        => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'link_grade_column' => 'nullable|boolean',
            'grade_column_id'   => 'nullable|integer',
        ];
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
