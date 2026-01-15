# 👥 Phân công Giảng dạy

## 📋 Tổng quan

Chức năng phân công cho phép Admin chỉ định môn học cho giảng viên. Mỗi phân công có mã riêng, cho phép 1 giảng viên dạy nhiều môn và 1 môn được dạy bởi nhiều giảng viên.

---

## 🔌 API Endpoints

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/assignments` | Danh sách phân công |
| `POST` | `/api/assignments` | Tạo phân công mới |
| `GET` | `/api/assignments/{id}` | Chi tiết phân công |
| `PUT` | `/api/assignments/{id}` | Cập nhật phân công |
| `DELETE` | `/api/assignments/{id}` | Xóa phân công |
| `GET` | `/api/lecturers/{id}/subjects` | Môn học của giảng viên |
| `GET` | `/api/subjects/{id}/lecturers` | Giảng viên của môn học |

---

## 📝 Request Example

```json
POST /api/assignments
{
  "lecturer_id": 5,
  "subject_id": 15,
  "academic_year": "2025-2026",
  "semester": 1,
  "note": "Phân công chính thức"
}
```

---

## 📊 Response Example

```json
{
  "success": true,
  "data": {
    "id": 123,
    "code": "ASG-2025-001",
    "lecturer": {
      "id": 5,
      "code": "GV001",
      "full_name": "ThS. Nguyễn Văn A"
    },
    "subject": {
      "id": 15,
      "code": "INT2204",
      "name": "Lập trình OOP"
    },
    "academic_year": "2025-2026",
    "semester": 1,
    "status": "active"
  }
}
```

---

## 📊 Business Rules

1. **Mã phân công:** Tự động sinh, duy nhất
2. **Ràng buộc:** Không trùng lặp (lecturer + subject + year + semester)
3. **Trạng thái:** active/inactive
4. **Khi phân công:** Giảng viên có thể tạo nhóm học phần và câu hỏi cho môn đó

---

## 🔒 Phân quyền

| Hành động | Giảng viên | Admin |
|-----------|------------|-------|
| Xem phân công của mình | ✅ | ✅ |
| Xem tất cả phân công | ❌ | ✅ |
| Tạo/Sửa/Xóa phân công | ❌ | ✅ |

---

## 📁 Database Tables

- `assignments` - Thông tin phân công

---

*Cập nhật: 01/2026*
