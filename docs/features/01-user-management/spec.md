# Module 1: Quản lý Người dùng (User Management)

## 1. Tổng quan

Module quản lý toàn bộ vòng đời người dùng: đăng ký, đăng nhập, phân quyền, quản lý hồ sơ cá nhân và import hàng loạt. Đây là nền tảng cho mọi module khác trong hệ thống.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | CRUD tất cả users, phân quyền, khoá/mở khoá, import Excel/CSV |
| **Giảng viên** | Xem/sửa hồ sơ cá nhân, đổi mật khẩu |
| **Sinh viên** | Xem/sửa hồ sơ cá nhân, đổi mật khẩu |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F1.1 | Đăng ký tài khoản | Form đăng ký với name, email, password. Laravel Breeze đã scaffold sẵn |
| F1.2 | Đăng nhập / Đăng xuất | Login bằng email + password. Breeze đã scaffold |
| F1.3 | Phân quyền RBAC | Gán role (Admin, Lecturer, Student) cho user qua Spatie Permission. Middleware kiểm tra quyền |
| F1.4 | Quản lý danh sách users (Admin) | Trang CRUD: xem danh sách, tạo mới, sửa, xoá (soft delete). Lọc theo role, tìm kiếm theo tên/email |
| F1.5 | Khoá / Mở khoá tài khoản | Admin toggle trường `is_active`. User bị khoá không thể đăng nhập |
| F1.6 | Quản lý hồ sơ cá nhân | User xem/sửa thông tin: name, phone, avatar, department, class_name |
| F1.7 | Đổi mật khẩu | Form đổi mật khẩu (yêu cầu nhập mật khẩu cũ) |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F1.8 | Quên mật khẩu | Gửi link reset password qua email. Breeze hỗ trợ sẵn |
| F1.9 | Import users từ Excel/CSV | Admin upload file → hệ thống tạo users hàng loạt (maatwebsite/excel) |
| F1.10 | Upload avatar | User upload ảnh đại diện → lưu qua bảng `files` |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `users` | Thông tin người dùng chính |
| `roles` | Danh sách vai trò (admin, lecturer, student...) |
| `user_roles` | Pivot N-N gán user ↔ role |
| `files` | Lưu avatar (avatar_file_id FK) |
| `activity_logs` | Ghi log hành động CRUD user |

> **Lưu ý:** Spatie Permission có bảng riêng (`permissions`, `roles`, `model_has_roles`...) nhưng schema EMS cũng tự thiết kế bảng `roles` + `user_roles`. Khi implement, nên **dùng Spatie Permission làm chính** và bỏ bảng `roles`/`user_roles` tự thiết kế để tránh trùng lặp.

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Đăng nhập | `GET /login` | Guest |
| Đăng ký | `GET /register` | Guest |
| Dashboard | `GET /dashboard` | All authenticated |
| Danh sách users | `GET /admin/users` | Admin |
| Tạo user | `GET /admin/users/create` | Admin |
| Sửa user | `GET /admin/users/{id}/edit` | Admin |
| Hồ sơ cá nhân | `GET /profile` | All authenticated |
| Đổi mật khẩu | `GET /profile/password` | All authenticated |
| Import users | `GET /admin/users/import` | Admin |
