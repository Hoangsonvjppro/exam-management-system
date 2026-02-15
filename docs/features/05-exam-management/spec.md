# Module 5: Quản lý Đề thi & Bài thi (Exam Management)

## 1. Tổng quan

Module cho phép giảng viên tạo đề thi từ ngân hàng câu hỏi, cấu hình bài thi (thời gian, số lần thi, trộn đề...), gán cho lớp học phần và quản lý kết quả. Kiến trúc DB tách biệt **ExamPaper** (đề thi gốc) và **ExamSchedule** (lịch thi gán cho lớp).

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Xem toàn bộ đề thi, thống kê kết quả |
| **Giảng viên** | Tạo/sửa đề thi, cấu hình, gán cho lớp, xem kết quả, chấm điểm, xuất Excel |
| **Sinh viên** | Không truy cập trực tiếp (thi qua Module 6) |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F5.1 | Tạo đề thi (chọn câu hỏi thủ công) | GV tạo đề: chọn môn, đặt tiêu đề, chọn câu hỏi từ ngân hàng, đặt điểm cho từng câu |
| F5.2 | Cấu hình đề thi | Trộn câu hỏi (shuffle_questions), trộn đáp án (shuffle_options), hiển thị kết quả (show_result), cho phép quay lại (allow_review), chế độ thi (official/practice) |
| F5.3 | Publish đề thi + Snapshot | Khi chuyển status → published: tạo content_snapshot JSON cho từng câu hỏi. Snapshot đóng băng nội dung |
| F5.4 | Lên lịch thi (Exam Schedule) | Gán đề thi cho lớp HP: thời gian mở/đóng, thời lượng, số lần thi tối đa, mật khẩu phòng thi, điểm đạt |
| F5.5 | Xem danh sách SV đã/chưa thi | GV xem trạng thái thi của từng SV trong lớp HP |
| F5.6 | Chấm điểm tự động | Sau khi SV nộp bài → so sánh đáp án với snapshot → tính điểm tự động |
| F5.7 | Xem kết quả thi | GV xem điểm, chi tiết bài làm từng SV |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F5.8 | Tạo đề tự động theo ma trận | Tự động chọn câu hỏi theo số lượng/chương/mức độ (ma trận đề) |
| F5.9 | Trộn đề tạo nhiều mã đề | Tạo N phiên bản đề với thứ tự câu/đáp án khác nhau |
| F5.10 | Xuất kết quả ra Excel | Export điểm thi của lớp HP ra file Excel |
| F5.11 | Thống kê phân bổ điểm | Biểu đồ phân phối điểm (histogram), trung bình, cao nhất, thấp nhất |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `exam_papers` | Đề thi gốc (tiêu đề, cấu hình, status) |
| `exam_paper_questions` | Câu hỏi trong đề + content_snapshot JSON |
| `exam_schedules` | Lịch thi (gán đề → lớp HP, thời gian, mật khẩu) |
| `exam_attempts` | Lượt thi của SV (điểm, status, thời gian) |
| `exam_answers` | Câu trả lời chi tiết từng câu |
| `questions` | Ngân hàng câu hỏi (truy nguyên) |
| `question_options` | Đáp án gốc |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Danh sách đề thi | `GET /exam-papers` | Admin, GV |
| Tạo đề thi | `GET /exam-papers/create` | GV |
| Sửa đề thi | `GET /exam-papers/{id}/edit` | GV |
| Chi tiết đề thi (preview) | `GET /exam-papers/{id}` | GV |
| Chọn câu hỏi cho đề | `GET /exam-papers/{id}/questions` | GV |
| Lên lịch thi | `GET /exam-papers/{id}/schedule` | GV |
| Kết quả thi theo lớp | `GET /exam-schedules/{id}/results` | GV |
| Chi tiết bài thi SV | `GET /exam-attempts/{id}` | GV |
