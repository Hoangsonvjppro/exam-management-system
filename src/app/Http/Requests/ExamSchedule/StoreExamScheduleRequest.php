<?php

namespace App\Http\Requests\ExamSchedule;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Exam;
use App\Models\CourseSection;
use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;

class StoreExamScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id'              => 'required|exists:exams,id',
            'course_section_ids'   => 'required|array|min:1',
            'course_section_ids.*' => [
                'required',
                'distinct',
                'exists:course_sections,id',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    $examDate = $this->input('exam_date');
                    $startTime = $this->input('start_time');
                    $endTime = $this->input('end_time');

                    if ($examId) {
                        $exam = Exam::find($examId);
                        $section = CourseSection::find($value);
                        if ($exam && $section && $exam->subject_id !== $section->subject_id) {
                            $fail("Lớp học phần {$section->name} không thuộc môn học của đề thi này.");
                        }

                        // Kiểm tra trùng lịch thi cho cùng một lớp học phần
                        if ($examDate && $startTime && $endTime) {
                            $hasConflict = ExamSchedule::where('course_section_id', $value)
                                ->where('exam_date', $examDate)
                                ->where(function ($query) use ($startTime, $endTime) {
                                    $query->where(function ($q) use ($startTime, $endTime) {
                                        $q->where('start_time', '<', $endTime)
                                          ->where('end_time', '>', $startTime);
                                    });
                                })
                                ->exists();

                            if ($hasConflict) {
                                $fail("Lớp học phần {$section->name} đã có lịch thi khác trùng vào thời gian này.");
                            }
                        }
                    }
                },
            ],
            'exam_date'         => 'required|date|after_or_equal:today',
            'start_time'        => [
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
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    $startTime = $this->input('start_time');
                    $examDate = $this->input('exam_date');

                    if ($examId && $startTime) {
                        $exam = Exam::find($examId);
                        if ($exam) {
                            try {
                                $start = Carbon::createFromFormat('H:i', $startTime);
                                $end = Carbon::createFromFormat('H:i', $value);
                                $diff = $start->diffInMinutes($end);

                                if ($diff < $exam->duration_minutes) {
                                    $fail("Thời gian thi ({$diff} phút) không đủ cho thời lượng đề thi ({$exam->duration_minutes} phút).");
                                }
                            } catch (\Exception $e) {}
                        }

                        // Kiểm tra trùng lịch cho giảng viên (tránh 1 GV gác nhiều ca khác nhau cùng lúc)
                        // Chỉ kiểm tra với các lịch thi ĐÃ CÓ trong DB (không trùng với mảng đang tạo)
                        if ($examDate) {
                            $lecturerId = Auth::id();
                            $hasLecturerConflict = ExamSchedule::whereHas('courseSection', function($q) use ($lecturerId) {
                                    $q->where('lecturer_id', $lecturerId);
                                })
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
                },
            ],
            'max_students'  => 'nullable|integer|min:1',
            'notes'         => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_id.required'           => 'Đề thi là bắt buộc.',
            'exam_id.exists'             => 'Đề thi không tồn tại.',
            'course_section_ids.required'=> 'Phải chọn ít nhất một lớp học phần.',
            'course_section_ids.array'   => 'Dữ liệu lớp học phần không hợp lệ.',
            'exam_date.required'         => 'Ngày thi là bắt buộc.',
            'exam_date.after_or_equal'   => 'Ngày thi phải từ hôm nay trở đi.',
            'start_time.required'        => 'Giờ bắt đầu là bắt buộc.',
            'end_time.required'          => 'Giờ kết thúc là bắt buộc.',
            'end_time.after'             => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ];
    }
}
