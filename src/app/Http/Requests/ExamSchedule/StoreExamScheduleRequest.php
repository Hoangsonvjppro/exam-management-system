<?php

namespace App\Http\Requests\ExamSchedule;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Exam;
use App\Models\CourseSection;
use Carbon\Carbon;

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
                'exists:course_sections,id',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    if ($examId) {
                        $exam = Exam::find($examId);
                        $section = CourseSection::find($value);
                        if ($exam && $section && $exam->subject_id !== $section->subject_id) {
                            $fail("Lớp học phần {$section->name} không thuộc môn học của đề thi này.");
                        }
                    }
                },
            ],
            'exam_date'         => 'required|date|after_or_equal:today',
            'start_time'        => 'required|date_format:H:i',
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    $examId = $this->input('exam_id');
                    $startTime = $this->input('start_time');

                    if ($examId && $startTime) {
                        $exam = Exam::find($examId);
                        if ($exam) {
                            try {
                                $start = Carbon::createFromFormat('H:i', $startTime)->startOfMinute();
                                $end = Carbon::createFromFormat('H:i', $value)->startOfMinute();

                                // Tính khoảng cách phút
                                $diff = $start->diffInMinutes($end);

                                if ($diff < $exam->duration_minutes) {
                                    // 🔍 IN THẲNG DATA THỰC TẾ RA LỖI ĐỂ BẮT BỆNH
                                    $fail("🔍 Server nhận được: Bắt đầu [{$startTime}], Kết thúc [{$value}] => Cách nhau có {$diff} phút. Yêu cầu tối thiểu {$exam->duration_minutes} phút. Hãy check lại code HTML!");
                                }
                            } catch (\Exception $e) {
                                // Bỏ qua nếu lỗi format, rule date_format ở trên sẽ tự chặn
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
