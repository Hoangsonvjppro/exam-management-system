# Module 2: Quản lý Môn học & Học phần (Course Management)

## 1. Tổng quan

Module quản lý cấu trúc học thuật: Môn học (Subject), Chương (Chapter), Học kỳ (Semester) và Lớp học phần (Course Section). Đây là module nền tảng để các module Câu hỏi, Đề thi, Điểm danh, Tài liệu hoạt động.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | CRUD môn học, học kỳ. Tạo lớp học phần, gán giảng viên |
| **Giảng viên** | Xem lớp mình phụ trách, quản lý chương trong môn, gán/import sinh viên |
| **Sinh viên** | Xem danh sách lớp mình đang học |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F2.1 | CRUD Học kỳ (Semester) | Admin tạo/sửa/xoá học kỳ: tên, năm, kỳ, ngày bắt đầu/kết thúc. Đánh dấu HK hiện tại |
| F2.2 | CRUD Môn học (Subject) | Admin/GV tạo/sửa/xoá môn: mã môn, tên, số tín chỉ, khoa, mô tả. Soft delete |
| F2.3 | CRUD Chương (Chapter) | GV tạo/sửa/xoá chương thuộc môn. Sắp xếp thứ tự (drag & drop hoặc input số) |
| F2.4 | CRUD Lớp học phần (Course Section) | Admin tạo lớp HP: mã lớp, chọn môn + HK + GV, sĩ số tối đa. Trạng thái active/archived/cancelled |
| F2.5 | Gán giảng viên vào học phần | Admin chọn GV phụ trách khi tạo/sửa lớp HP |
| F2.6 | Gán sinh viên vào học phần | GV/Admin thêm SV vào lớp HP (chọn từ danh sách hoặc search) |
| F2.7 | Quản lý thời khoá biểu | Thêm lịch học cho lớp HP: thứ, tiết bắt đầu/kết thúc, phòng |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F2.8 | Import sinh viên vào HP từ CSV | Upload file CSV → gán nhiều SV vào lớp HP cùng lúc |
| F2.9 | Kiểm tra trùng lịch/phòng | Cảnh báo khi thời khoá biểu bị trùng phòng hoặc trùng GV |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `semesters` | Học kỳ (năm, kỳ, ngày bắt đầu/kết thúc) |
| `subjects` | Môn học (mã, tên, tín chỉ, khoa) |
| `chapters` | Chương thuộc môn (tên, thứ tự) |
| `course_sections` | Lớp học phần (mã lớp, FK môn + HK + GV) |
| `class_schedules` | Thời khoá biểu (thứ, tiết, phòng) |
| `course_section_students` | Pivot SV ↔ Lớp HP (status: enrolled/dropped/completed) |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Danh sách học kỳ | `GET /admin/semesters` | Admin |
| Tạo/Sửa học kỳ | `GET /admin/semesters/create` | Admin |
| Danh sách môn học | `GET /admin/subjects` | Admin, GV |
| Tạo/Sửa môn học | `GET /admin/subjects/create` | Admin |
| Chi tiết môn học + Chương | `GET /subjects/{id}` | Admin, GV |
| Danh sách lớp học phần | `GET /course-sections` | Admin, GV, SV |
| Tạo/Sửa lớp HP | `GET /admin/course-sections/create` | Admin |
| Chi tiết lớp HP (DSSV + TKB) | `GET /course-sections/{id}` | Admin, GV |
| Gán sinh viên vào lớp | `GET /course-sections/{id}/students` | Admin, GV |
