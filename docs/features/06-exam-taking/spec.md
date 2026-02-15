# Module 6: Làm bài thi (Exam Taking — Sinh viên)

## 1. Tổng quan

Module giao diện làm bài thi dành cho sinh viên: hiển thị câu hỏi, đếm ngược thời gian, đánh dấu câu để xem lại, nộp bài và xem kết quả. Đây là module hướng đến trải nghiệm người dùng (UX) nhiều nhất.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Sinh viên** | Vào phòng thi, làm bài, nộp bài, xem kết quả, xem lịch sử |
| **Giảng viên** | Không truy cập (quản lý kết quả qua Module 5) |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F6.1 | Vào phòng thi | SV chọn bài thi → nhập mật khẩu (nếu có) → tạo exam_attempt → bắt đầu làm bài |
| F6.2 | Giao diện làm bài | Hiển thị câu hỏi từ snapshot, đáp án đã được shuffle. Navigation sidebar hiển thị trạng thái từng câu |
| F6.3 | Đếm ngược thời gian | Timer countdown phía client (JavaScript). Hiển thị thời gian còn lại. Cảnh báo khi sắp hết |
| F6.4 | Chọn/thay đổi đáp án | SV click chọn đáp án → lưu vào exam_answers (AJAX/Livewire). Cho phép thay đổi nếu allow_review = true |
| F6.5 | Đánh dấu câu hỏi | SV flag câu hỏi để xem lại sau (is_flagged). Hiển thị icon flag trên sidebar |
| F6.6 | Nộp bài | SV chủ động nộp → confirm dialog → server chấm điểm tự động |
| F6.7 | Tự động nộp khi hết giờ | Client gửi auto-submit khi timer = 0. Server validate thời gian → chấm điểm |
| F6.8 | Xem kết quả | Sau khi nộp: hiển thị điểm, số câu đúng. Chi tiết đáp án nếu GV cho phép (show_answer) |
| F6.9 | Xem lịch sử bài thi | SV xem danh sách bài thi đã làm: đề, lớp, điểm, thời gian |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F6.10 | Anti-cheating: Full-screen mode | Bắt buộc full-screen khi làm bài. Log sự kiện rời tab (exam_attempt_events) |
| F6.11 | Lưu bài tự động (auto-save) | Định kỳ lưu đáp án đã chọn → tránh mất bài khi mất kết nối |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `exam_schedules` | Lịch thi (kiểm tra thời gian mở/đóng, mật khẩu) |
| `exam_attempts` | Lượt thi (started_at, submitted_at, score, status, questions_order) |
| `exam_answers` | Câu trả lời (selected_snapshot_index, is_flagged, displayed_options_order) |
| `exam_paper_questions` | Câu hỏi trong đề + content_snapshot (nguồn dữ liệu hiển thị) |
| `exam_attempt_events` | Log sự kiện anti-cheating |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Danh sách bài thi sắp tới | `GET /my-exams` | SV |
| Vào phòng thi (nhập password) | `GET /exams/{schedule_id}/enter` | SV |
| Giao diện làm bài | `GET /exams/{attempt_id}/take` | SV |
| Kết quả sau khi nộp | `GET /exams/{attempt_id}/result` | SV |
| Lịch sử bài thi | `GET /my-exams/history` | SV |
