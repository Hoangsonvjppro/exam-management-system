# EMS — Examination Management System

> **Hệ thống Quản lý Thi trắc nghiệm Online** — Đồ án môn học, PHP Laravel 12 Fullstack

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-4-06B6D4?style=flat&logo=tailwindcss)](https://tailwindcss.com)

---

## 📖 Giới Thiệu

EMS là hệ thống quản lý thi trắc nghiệm và tài liệu học tập được xây dựng bằng **Laravel 12 fullstack** (Blade + Livewire). Hệ thống hỗ trợ 3 vai trò chính:

| Vai trò | Quyền hạn |
|---|---|
| **Admin** | Quản trị hệ thống, người dùng, cấu hình |
| **Giảng viên** | Tạo đề thi, quản lý lớp học phần, điểm danh, upload tài liệu |
| **Sinh viên** | Làm bài thi, xem điểm, tải tài liệu, xem lịch sử điểm danh |

**Tính năng chính:**
- 🔐 Xác thực & phân quyền RBAC (Spatie Laravel Permission)
- 📝 Ngân hàng câu hỏi với nhiều loại câu hỏi (MCQ, đúng/sai, điền vào, tự luận)
- 📋 Tạo đề thi tự động, trộn câu hỏi / đáp án, hệ thống snapshot chống gian lận
- ✅ Điểm danh đa phương thức (thủ công, QR code, PIN code)
- 📁 Quản lý tài liệu học tập tập trung
- 🔔 Thông báo in-app

---

## 🗂️ Cấu Trúc Thư Mục

```
EMS-exam-management-system/
│
├── docs/                           # Tài liệu dự án
│   ├── analysis/
│   │   └── analysis.md             # Phân tích nghiệp vụ
│   ├── database-design/
│   │   ├── schema.sql              # Schema MySQL đầy đủ (23 bảng)
│   │   ├── schema.png              # Sơ đồ ERD
│   │   └── schema-description.md  # Mô tả chi tiết từng bảng
│   ├── features/                   # Đặc tả tính năng theo module
│   │   ├── 01-user-management/
│   │   ├── 02-course-management/
│   │   ├── 03-student-management/
│   │   ├── 04-question-bank/
│   │   ├── 05-exam-management/
│   │   ├── 06-exam-taking/
│   │   ├── 07-attendance/
│   │   ├── 08-document-syllabus/
│   │   ├── 09-notification/
│   │   └── 10-report-analytics/
│   └── team-plan.md                # Kế hoạch phân chia công việc nhóm
│
└── src/                            # Source code Laravel
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   │   └── Auth/           # Auth controllers (Breeze)
    │   │   ├── Middleware/
    │   │   │   └── EnsureUserIsActive.php  # Chặn tài khoản bị khoá
    │   │   └── Requests/           # Form Requests
    │   └── Models/
    │       ├── User.php            # HasRoles, SoftDeletes, scopeActive
    │       ├── File.php            # Upload file tập trung
    │       ├── Setting.php         # Cấu hình hệ thống (get/set)
    │       ├── ActivityLog.php     # Audit trail
    │       └── Notification.php    # Thông báo in-app (UUID)
    │
    ├── database/
    │   ├── migrations/
    │   │   ├── 0001_01_01_000000_create_users_table.php
    │   │   ├── 0001_01_01_000001_create_cache_table.php
    │   │   ├── 0001_01_01_000002_create_jobs_table.php
    │   │   ├── 2026_02_14_190056_create_permission_tables.php  # Spatie
    │   │   ├── 2026_02_28_000001_create_files_table.php
    │   │   └── 2026_02_28_000002_create_system_tables.php
    │   └── seeders/
    │       ├── DatabaseSeeder.php
    │       ├── RoleAndPermissionSeeder.php  # 5 roles + permissions
    │       ├── AdminUserSeeder.php          # Tài khoản demo
    │       └── SettingSeeder.php            # Cấu hình mặc định
    │
    ├── resources/views/
    │   ├── layouts/
    │   │   ├── app.blade.php       # Layout chính (sidebar + navbar)
    │   │   └── guest.blade.php     # Layout trang khách
    │   ├── components/
    │   │   ├── sidebar-section.blade.php
    │   │   └── sidebar-link.blade.php
    │   ├── auth/
    │   │   └── login.blade.php     # Trang đăng nhập custom
    │   ├── admin/
    │   │   └── dashboard.blade.php
    │   ├── lecturer/
    │   │   └── dashboard.blade.php
    │   └── student/
    │       └── dashboard.blade.php
    │
    ├── routes/
    │   ├── web.php                 # Routes phân theo role
    │   └── auth.php                # Routes xác thực (Breeze)
    │
    ├── bootstrap/app.php           # Đăng ký middleware
    ├── compose.yaml                # Podman/Docker: MySQL 8 + phpMyAdmin
    └── .env                        # Biến môi trường
```

---

## ⚙️ Yêu Cầu Hệ Thống

| Công nghệ | Phiên bản |
|---|---|
| PHP | ≥ 8.3 |
| Composer | ≥ 2.x |
| Node.js | ≥ 20.x |
| Podman hoặc Docker | Bất kỳ |
| MySQL | 8.0 (chạy qua container) |

---

## 🚀 Hướng Dẫn Cài Đặt & Chạy

### Bước 1 — Clone & cài dependencies

```bash
git clone <repo-url>
cd EMS-exam-management-system/src

# Cài PHP dependencies
composer install

# Cài Node dependencies
npm install
```

### Bước 2 — Cấu hình môi trường

```bash
# Copy file cấu hình mẫu (nếu chưa có .env)
cp .env.example .env

# Tạo app key
php artisan key:generate
```

> File `.env` trong repo đã được cấu hình sẵn để kết nối MySQL container. Không cần sửa gì thêm.

### Bước 3 — Khởi động Database (Podman/Docker)

```bash
# Khởi động MySQL 8 + phpMyAdmin
podman compose up -d
# hoặc: docker compose up -d

# Chờ khoảng 30 giây để MySQL khởi động xong
# Kiểm tra container đang chạy:
podman ps
```

| Service | URL |
|---|---|
| MySQL | `localhost:3306` |
| phpMyAdmin | `http://localhost:8080` |

**Thông tin kết nối DB:**
- Host: `127.0.0.1:3306`
- Database: `ems`
- Username: `ems_user` / Password: `ems_password`
- Root password: `root_password`

### Bước 4 — Migrate & Seed Database

```bash
# Chạy migrations và seed dữ liệu mẫu
php artisan migrate:fresh --seed
```

**Tài khoản demo được tạo sẵn:**

| Role | Email | Mật khẩu |
|---|---|---|
| Admin | `admin@ems.local` | `password` |
| Giảng viên | `lecturer@ems.local` | `password` |
| Sinh viên | `student@ems.local` | `password` |

### Bước 5 — Chạy ứng dụng

Mở **2 terminal riêng biệt**:

```bash
# Terminal 1 — Build assets (Vite dev server)
npm run dev

# Terminal 2 — Laravel dev server
php artisan serve
```

Truy cập: **`http://localhost:8000`**

---

## 👥 Phân Chia Công Việc Nhóm

| Thành viên | Phụ trách |
|---|---|
| **P1** (Team Lead) | Setup, Auth, Phân quyền, Layout chung |
| **P2** | Học kỳ, Môn học, Chương, Ngân hàng câu hỏi |
| **P3** | Lớp học phần, Đề thi, Giao diện làm bài |
| **P4** | UI components, Dashboard, Thống kê |
| **P5** | Sinh viên, Điểm danh, Tài liệu, Seeder |

> Xem chi tiết tại [`docs/team-plan.md`](./docs/team-plan.md)

---

## 🔧 Lệnh Artisan Thường Dùng

```bash
# Reset và seed lại toàn bộ DB (khi có migration mới từ team)
php artisan migrate:fresh --seed

# Chỉ seed lại data mà không reset migrations
php artisan db:seed

# Xem danh sách routes
php artisan route:list

# Clear cache
php artisan optimize:clear
```

---

## 📝 Quy Tắc Git

```bash
# Mỗi người làm trên nhánh riêng
git checkout -b feature/ten-tinh-nang

# Merge vào develop qua Pull Request
# KHÔNG push thẳng lên main/develop
```

---

## 📄 Tài Liệu Thêm

- [Phân tích nghiệp vụ](./docs/analysis/analysis.md)
- [Thiết kế Database](./docs/database-design/schema-description.md)
- [Kế hoạch nhóm](./docs/team-plan.md)
