# 📖 Quản lý Môn học

## 📋 Tổng quan

Chức năng quản lý môn học cho phép giảng viên tạo và duy trì thông tin các môn học. Mỗi môn học có thể có nhiều chương và tài liệu giảng dạy đính kèm.

---

## 🔌 API Endpoints

### Subjects

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/subjects` | Danh sách môn học |
| `POST` | `/api/subjects` | Tạo môn học mới |
| `GET` | `/api/subjects/{id}` | Chi tiết môn học |
| `PUT` | `/api/subjects/{id}` | Cập nhật môn học |
| `DELETE` | `/api/subjects/{id}` | Xóa môn học |

### Chapters

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/subjects/{id}/chapters` | Danh sách chương |
| `POST` | `/api/subjects/{id}/chapters` | Thêm chương |
| `PUT` | `/api/chapters/{id}` | Cập nhật chương |
| `DELETE` | `/api/chapters/{id}` | Xóa chương |

### Teaching Materials

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/chapters/{id}/materials` | Danh sách tài liệu |
| `POST` | `/api/chapters/{id}/materials` | Upload tài liệu |
| `DELETE` | `/api/materials/{id}` | Xóa tài liệu |

---

## 📝 Form Thêm Môn học

| Field | Kiểu | Bắt buộc | Mô tả |
|-------|------|----------|-------|
| `code` | string | ✅ | Mã môn học (VD: INT2204) |
| `name` | string | ✅ | Tên môn học |
| `credits` | int | ✅ | Số tín chỉ |
| `theory_hours` | int | ✅ | Số tiết lý thuyết |
| `practice_hours` | int | ✅ | Số tiết thực hành |
| `coefficient` | decimal | ✅ | Hệ số môn học (mặc định: 1.0) |
| `description` | text | ❌ | Mô tả môn học |

---

## 📝 Request Example

```json
POST /api/subjects
{
  "code": "INT2204",
  "name": "Lập trình hướng đối tượng",
  "credits": 3,
  "theory_hours": 30,
  "practice_hours": 15,
  "coefficient": 1.0,
  "description": "Môn học giới thiệu các khái niệm OOP..."
}
```

---

## 📊 Business Rules

1. **Mã môn học:** Duy nhất trong hệ thống
2. **Xóa môn học:** Soft delete, không xóa vĩnh viễn
3. **Chương:** Mỗi môn học có thể có nhiều chương, sắp xếp theo thứ tự
4. **Tài liệu:** Hỗ trợ PDF, DOC, DOCX, PPT, PPTX, Video, Link

---

## 🔒 Phân quyền

| Hành động | Giảng viên | Admin |
|-----------|------------|-------|
| Xem môn học | ✅ | ✅ |
| Tạo môn học | ✅ | ✅ |
| Sửa môn học | ✅ (của mình) | ✅ |
| Xóa môn học | ✅ (của mình) | ✅ |
| CRUD chương | ✅ | ✅ |
| Upload tài liệu | ✅ | ✅ |

---

## 📁 Database Tables

- `subjects` - Thông tin môn học
- `chapters` - Chương của môn học
- `teaching_materials` - Tài liệu giảng dạy

---

*Cập nhật: 01/2026*
