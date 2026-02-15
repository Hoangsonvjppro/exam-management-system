# Module 3: Quản lý Sinh viên (Student Management)

## 1. Tổng quan

Module cung cấp giao diện quản lý sinh viên theo lớp học phần: xem danh sách, thông tin chi tiết, thống kê và import hàng loạt. Dữ liệu sinh viên thực chất nằm trong bảng `users` (với role = student) và bảng `course_section_students`.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Xem toàn bộ SV, import CSV, xem thống kê tổng |
| **Giảng viên** | Xem DSSV của lớp mình phụ trách, import SV vào lớp |
| **Sinh viên** | Xem thông tin cá nhân, xem lớp đang học |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F3.1 | Danh sách sinh viên theo học phần | GV/Admin xem DSSV của một lớp HP cụ thể. Lọc theo trạng thái (enrolled/dropped/completed) |
| F3.2 | Xem thông tin chi tiết sinh viên | Xem MSSV, họ tên, lớp sinh hoạt, email, SĐT, khoa |
| F3.3 | Import sinh viên từ Excel/CSV | Upload file → tạo user + gán vào lớp HP. Validate trùng email/MSSV |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F3.4 | Thống kê sinh viên | Tổng hợp: điểm thi trung bình, số buổi điểm danh, tài liệu đã tải |
| F3.5 | Export danh sách SV ra Excel | Xuất DSSV của lớp HP ra file Excel |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `users` | Thông tin SV (student_code, name, email, phone, class_name, department) |
| `course_section_students` | Gán SV vào lớp HP + trạng thái enrollment |
| `course_sections` | Lớp học phần (để filter theo lớp) |
| `exam_attempts` | Lịch sử thi (cho thống kê điểm) |
| `attendance_records` | Lịch sử điểm danh (cho thống kê chuyên cần) |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| DSSV theo lớp HP | `GET /course-sections/{id}/students` | Admin, GV |
| Chi tiết sinh viên | `GET /students/{id}` | Admin, GV |
| Import SV vào lớp | `GET /course-sections/{id}/students/import` | Admin, GV |
| Tổng hợp thống kê SV | `GET /students/{id}/stats` | Admin, GV |
