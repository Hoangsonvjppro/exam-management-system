# 📖 API Endpoints Documentation

## 🔐 Authentication

### POST /api/auth/login
Đăng nhập người dùng.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "code": "GV001",
      "email": "user@example.com",
      "full_name": "Nguyễn Văn A",
      "role": "lecturer"
    }
  }
}
```

### POST /api/auth/logout
Đăng xuất (yêu cầu token).

### GET /api/auth/me
Lấy thông tin người dùng hiện tại.

---

## 👥 Users

### GET /api/users
Danh sách người dùng (Admin only).

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| page | int | Trang hiện tại |
| per_page | int | Số item/trang |
| role | string | Filter theo role |
| search | string | Tìm kiếm |

### POST /api/users
Tạo người dùng mới (Admin only).

### GET /api/users/{id}
Chi tiết người dùng.

### PUT /api/users/{id}
Cập nhật người dùng.

### DELETE /api/users/{id}
Xóa người dùng (soft delete).

---

## 📖 Subjects

### GET /api/subjects
Danh sách môn học.

### POST /api/subjects
Tạo môn học.

```json
{
  "code": "INT2204",
  "name": "Lập trình OOP",
  "credits": 3,
  "theory_hours": 30,
  "practice_hours": 15,
  "coefficient": 1.0
}
```

### GET /api/subjects/{id}
Chi tiết môn học (bao gồm chapters).

### PUT /api/subjects/{id}
Cập nhật môn học.

### DELETE /api/subjects/{id}
Xóa môn học.

### GET /api/subjects/{id}/chapters
Danh sách chương của môn học.

### POST /api/subjects/{id}/chapters
Thêm chương mới.

---

## ❓ Questions

### GET /api/questions
Danh sách câu hỏi với filtering.

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| subject_id | int | Lọc theo môn học |
| chapter_id | int | Lọc theo chương |
| difficulty_id | int | Lọc theo độ khó |
| question_type | string | single_choice, multiple_choice, true_false |
| search | string | Tìm kiếm nội dung |

### POST /api/questions
Tạo câu hỏi.

```json
{
  "subject_id": 15,
  "chapter_id": 42,
  "difficulty_id": 1,
  "question_type": "single_choice",
  "content": "Nội dung câu hỏi?",
  "points": 1.0,
  "answers": [
    { "content": "Đáp án A", "is_correct": false },
    { "content": "Đáp án B", "is_correct": true },
    { "content": "Đáp án C", "is_correct": false }
  ]
}
```

### POST /api/questions/import
Import câu hỏi từ Excel.

### GET /api/questions/export
Export câu hỏi ra Excel.

---

## 📝 Exams

### GET /api/exams
Danh sách đề thi.

### POST /api/exams
Tạo đề thi.

```json
{
  "name": "Kiểm tra giữa kỳ",
  "subject_id": 15,
  "start_date": "2026-03-15T08:00:00Z",
  "end_date": "2026-03-15T10:00:00Z",
  "duration": 60,
  "total_questions": 30,
  "difficulty_config": { "easy": 10, "medium": 15, "hard": 5 },
  "shuffle_questions": true,
  "shuffle_answers": true
}
```

### POST /api/exams/{id}/auto-generate
Tự động lấy câu hỏi từ ngân hàng.

### POST /api/exams/{id}/publish
Công bố đề thi.

---

## 📚 Course Groups

### GET /api/course-groups
Danh sách nhóm học phần.

### POST /api/course-groups
Tạo nhóm học phần.

### GET /api/course-groups/{id}/students
Danh sách sinh viên trong nhóm.

### POST /api/course-groups/{id}/students/import
Import sinh viên từ Excel.

### POST /api/course-groups/{id}/attendance
Tạo phiên điểm danh (trả về QR code).

---

## 🎓 Student Endpoints

### GET /api/student/dashboard
Tổng quan cho sinh viên.

### GET /api/student/exams
Danh sách bài thi có thể làm.

### POST /api/student/exams/{id}/start
Bắt đầu làm bài (trả về câu hỏi).

### POST /api/student/exams/{id}/answer
Nộp câu trả lời.

```json
{
  "question_id": 123,
  "selected_answers": [456]
}
```

### POST /api/student/exams/{id}/submit
Nộp bài thi.

### POST /api/student/attendance/check-in
Điểm danh bằng QR.

```json
{
  "qr_code": "ATT-20260115-123-456-ABCD"
}
```

---

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
```

### Pagination
```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "total_pages": 7
    }
  }
}
```

---

## 🔑 Authentication

Tất cả API (trừ login/register) yêu cầu Bearer token:

```
Authorization: Bearer <access_token>
```

---

*Xem OpenAPI spec: [openapi.yaml](openapi.yaml)*
