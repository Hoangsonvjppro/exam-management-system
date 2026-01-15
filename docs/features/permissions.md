# 🔐 Phân quyền (Permissions)

## 📋 Tổng quan

Hệ thống sử dụng mô hình RBAC (Role-Based Access Control) với 3 vai trò chính: Admin, Giảng viên, Sinh viên.

---

## 👥 Vai trò (Roles)

| Role | Code | Mô tả |
|------|------|-------|
| **Quản trị viên** | `admin` | Toàn quyền quản lý hệ thống |
| **Giảng viên** | `lecturer` | Quản lý môn học, câu hỏi, đề thi |
| **Sinh viên** | `student` | Làm bài thi, điểm danh, xem kết quả |

---

## 🔑 Danh sách Quyền

### Module Users
| Permission | Admin | Lecturer | Student |
|------------|-------|----------|---------|
| `users.view` | ✅ | ❌ | ❌ |
| `users.create` | ✅ | ❌ | ❌ |
| `users.update` | ✅ | ❌ | ❌ |
| `users.delete` | ✅ | ❌ | ❌ |

### Module Subjects
| Permission | Admin | Lecturer | Student |
|------------|-------|----------|---------|
| `subjects.view` | ✅ | ✅ | ✅ |
| `subjects.create` | ✅ | ✅ | ❌ |
| `subjects.update` | ✅ | ✅* | ❌ |
| `subjects.delete` | ✅ | ✅* | ❌ |

### Module Questions
| Permission | Admin | Lecturer | Student |
|------------|-------|----------|---------|
| `questions.view` | ✅ | ✅* | ❌ |
| `questions.create` | ✅ | ✅ | ❌ |
| `questions.update` | ✅ | ✅* | ❌ |
| `questions.delete` | ✅ | ✅* | ❌ |

### Module Exams
| Permission | Admin | Lecturer | Student |
|------------|-------|----------|---------|
| `exams.view` | ✅ | ✅* | ✅* |
| `exams.create` | ✅ | ✅ | ❌ |
| `exams.update` | ✅ | ✅* | ❌ |
| `exams.delete` | ✅ | ✅* | ❌ |

### Module Course Groups
| Permission | Admin | Lecturer | Student |
|------------|-------|----------|---------|
| `course_groups.view` | ✅ | ✅* | ✅* |
| `course_groups.create` | ✅ | ✅ | ❌ |
| `course_groups.update` | ✅ | ✅* | ❌ |
| `course_groups.delete` | ✅ | ✅* | ❌ |

### Module Assignments
| Permission | Admin | Lecturer | Student |
|------------|-------|----------|---------|
| `assignments.view` | ✅ | ✅* | ❌ |
| `assignments.create` | ✅ | ❌ | ❌ |
| `assignments.update` | ✅ | ❌ | ❌ |
| `assignments.delete` | ✅ | ❌ | ❌ |

> **\*** = Chỉ với dữ liệu của mình

---

## 🔌 API Middleware

```php
// Laravel Middleware
Route::middleware(['auth:sanctum', 'permission:questions.create'])
    ->post('/questions', [QuestionController::class, 'store']);
```

---

## 📊 Database

```sql
-- Bảng roles
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,
    display_name VARCHAR(100)
);

-- Bảng permissions
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100) UNIQUE,
    module VARCHAR(50)
);

-- Bảng liên kết
CREATE TABLE role_permissions (
    role_id BIGINT,
    permission_id BIGINT,
    PRIMARY KEY (role_id, permission_id)
);
```

---

*Cập nhật: 01/2026*
