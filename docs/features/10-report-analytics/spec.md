# Module 10: Báo cáo & Thống kê (Report & Analytics)

## 1. Tổng quan

Module tổng hợp dữ liệu từ các module khác để hiển thị dashboard, biểu đồ, và xuất báo cáo. Tập trung vào thống kê điểm thi, chuyên cần và hoạt động hệ thống.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Dashboard tổng quan toàn hệ thống, báo cáo hoạt động GV |
| **Giảng viên** | Thống kê điểm thi + chuyên cần theo lớp mình phụ trách |
| **Sinh viên** | Xem thống kê cá nhân (điểm thi, chuyên cần) |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F10.1 | Dashboard Admin | Tổng quan: số users, số môn, số lớp HP, số đề thi. Cards thống kê |
| F10.2 | Thống kê điểm theo lớp HP | Trung bình, cao nhất, thấp nhất, tỷ lệ đạt. Cho từng bài thi |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F10.3 | Biểu đồ phân phối điểm | Histogram điểm thi (Chart.js). Phân bố theo khoảng điểm |
| F10.4 | Thống kê chuyên cần theo lớp | Tỷ lệ có mặt trung bình, SV hay vắng nhất |
| F10.5 | Báo cáo hoạt động GV | Số đề thi đã tạo, số buổi điểm danh, số tài liệu upload |
| F10.6 | Export báo cáo Excel | Xuất các bảng thống kê ra file Excel (maatwebsite/excel) |
| F10.7 | Dashboard sinh viên | SV xem tổng hợp: điểm thi các môn, tỷ lệ chuyên cần, bài thi sắp tới |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `users` | Đếm users theo role |
| `subjects`, `course_sections` | Đếm môn, lớp HP |
| `exam_attempts` | Thống kê điểm thi |
| `exam_schedules` | Đếm bài thi |
| `attendance_records` | Thống kê chuyên cần |
| `documents` | Đếm tài liệu |
| `activity_logs` | Báo cáo hoạt động |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Dashboard Admin | `GET /admin/dashboard` | Admin |
| Dashboard GV | `GET /dashboard` | GV |
| Dashboard SV | `GET /dashboard` | SV |
| Thống kê điểm thi | `GET /exam-schedules/{id}/stats` | GV |
| Thống kê chuyên cần | `GET /course-sections/{id}/attendance/stats` | GV |
| Export báo cáo | `GET /reports/export` | Admin, GV |
