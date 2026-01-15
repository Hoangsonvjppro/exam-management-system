# ❓ Quản lý Câu hỏi (Ngân hàng Đề thi)

## 📋 Tổng quan

Chức năng quản lý câu hỏi cho phép giảng viên xây dựng và duy trì ngân hàng câu hỏi trắc nghiệm. Các câu hỏi được phân loại theo môn học, chương và độ khó.

---

## 🎨 Loại Câu hỏi

| Loại | Mã | Mô tả |
|------|-----|-------|
| **Một đáp án đúng** | `single_choice` | Chỉ có duy nhất 1 đáp án đúng |
| **Nhiều đáp án đúng** | `multiple_choice` | Có thể có 2+ đáp án đúng |
| **Đúng/Sai** | `true_false` | Chỉ có 2 lựa chọn: Đúng hoặc Sai |

---

## 🔌 API Endpoints

### Questions CRUD

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/questions` | Danh sách câu hỏi với filters |
| `POST` | `/api/questions` | Tạo câu hỏi mới |
| `GET` | `/api/questions/{id}` | Chi tiết câu hỏi |
| `PUT` | `/api/questions/{id}` | Cập nhật câu hỏi |
| `DELETE` | `/api/questions/{id}` | Xóa câu hỏi |
| `POST` | `/api/questions/{id}/duplicate` | Nhân bản câu hỏi |

### Import/Export

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `POST` | `/api/questions/import` | Import từ Excel/Word |
| `GET` | `/api/questions/export` | Export ra Excel |
| `GET` | `/api/questions/template` | Tải template import |

---

## 📝 Tạo Câu hỏi (Request)

```json
{
  "subject_id": 15,
  "chapter_id": 42,
  "difficulty_id": 1,
  "question_type": "single_choice",
  "content": "Trong lập trình OOP, tính chất nào cho phép đa hình?",
  "explanation": "Đa hình (Polymorphism) cho phép...",
  "points": 1.0,
  "answers": [
    { "content": "Đóng gói", "is_correct": false },
    { "content": "Kế thừa", "is_correct": false },
    { "content": "Đa hình", "is_correct": true },
    { "content": "Trừu tượng", "is_correct": false }
  ]
}
```

---

## 📊 Business Rules

### Quy tắc Đáp án

1. **Số lượng:** Tối thiểu 2, tối đa 10 đáp án
2. **Đáp án đúng:**
   - `single_choice`: Chính xác 1 đáp án đúng
   - `multiple_choice`: Ít nhất 1 đáp án đúng
   - `true_false`: Tự động 2 đáp án "Đúng/Sai"

### Quy tắc Tính điểm

| Loại | Đúng | Sai |
|------|------|-----|
| single_choice | +100% | 0 |
| multiple_choice | Theo tỷ lệ | 0 |
| true_false | +100% | 0 |

---

## 🔒 Phân quyền

| Hành động | Giảng viên | Admin |
|-----------|------------|-------|
| Xem câu hỏi của mình | ✅ | ✅ |
| Tạo câu hỏi | ✅ | ✅ |
| Import/Export | ✅ | ✅ |

---

## 📁 Database Tables

- `questions` - Thông tin câu hỏi
- `answers` - Các đáp án
- `difficulty_levels` - Độ khó

Xem chi tiết: [Database Design](../database/database-design.md)

---

*Cập nhật: 01/2026*
