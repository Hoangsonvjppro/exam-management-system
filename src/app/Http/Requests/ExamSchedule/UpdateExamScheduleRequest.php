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
            'start_time'    => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $examDate = $this->input('exam_date');
                    if ($examDate === date('Y-m-d')) {
                        if ($value <= date('H:i')) {
                            $fail('Giờ bắt đầu phải lớn hơn giờ hiện tại nếu thi trong hôm nay.');
                        }
                    }
                }
            ],
            'end_time'      => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) use ($examId, $schedule) {
                    $startTime = $this->input('start_time');
                    $examDate = $this->input('exam_date');
                    $exam = Exam::find($examId);
                    
                    if ($exam && $startTime) {
                        try {
                            $start = Carbon::createFromFormat('H:i', $startTime);
                            $end = Carbon::createFromFormat('H:i', $value);
                            $diff = $start->diffInMinutes($end);

                            if ($diff < $exam->duration_minutes) {
                                $fail("Thời gian thi ({$diff} phút) không đủ cho thời lượng đề thi ({$exam->duration_minutes} phút).");
                            }
                        } catch (\Exception $e) {}
                    }

                    // Kiểm tra trùng lịch cho lớp học phần
                    if ($examDate && $startTime && $value) {
                        $hasSectionConflict = ExamSchedule::where('course_section_id', $schedule->course_section_id)
                            ->where('id', '!=', $schedule->id)
                            ->where('exam_date', $examDate)
                            ->where(function ($query) use ($startTime, $value) {
                                $query->where('start_time', '<', $value)
                                      ->where('end_time', '>', $startTime);
                            })
                            ->exists();

                        if ($hasSectionConflict) {
                            $fail("Lớp học phần này đã có lịch thi khác trùng vào thời gian này.");
                        }

                        // Kiểm tra trùng lịch cho giảng viên
                        $lecturerId = Auth::id();
                        $hasLecturerConflict = ExamSchedule::whereHas('courseSection', function($q) use ($lecturerId) {
                                $q->where('lecturer_id', $lecturerId);
                            })
                            ->where('id', '!=', $schedule->id)
                            ->where('exam_date', $examDate)
                            ->where(function ($query) use ($startTime, $value) {
                                $query->where('start_time', '<', $value)
                                      ->where('end_time', '>', $startTime);
                            })
                            ->exists();

                        if ($hasLecturerConflict) {
                            $fail("Bạn đã có một lịch thi khác trùng vào thời gian này.");
                        }
                    }
                }
            ],
            'max_students'  => 'nullable|integer|min:1',
            'notes'         => 'nullable|string|max:1000',
            'status'        => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'link_grade_column' => 'nullable|boolean',
            'grade_column_id'   => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_date.required'         => 'Ngày thi là bắt buộc.',
            'exam_date.after_or_equal'   => 'Ngày thi phải từ hôm nay trở đi.',
            'start_time.required'        => 'Giờ bắt đầu là bắt buộc.',
            'end_time.required'          => 'Giờ kết thúc là bắt buộc.',
            'end_time.after'             => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ];
    }
}
