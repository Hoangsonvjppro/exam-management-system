# 📝 Quản lý Đề Kiểm tra

## 📋 Tổng quan

Chức năng quản lý đề kiểm tra cho phép giảng viên tạo và quản lý các bài kiểm tra trắc nghiệm. Hỗ trợ tự động lấy câu hỏi từ ngân hàng đề, đảo câu hỏi và đáp án.

---

## 🔌 API Endpoints

### Exams CRUD

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/exams` | Danh sách đề thi |
| `POST` | `/api/exams` | Tạo đề thi mới |
| `GET` | `/api/exams/{id}` | Chi tiết đề thi |
| `PUT` | `/api/exams/{id}` | Cập nhật đề thi |
| `DELETE` | `/api/exams/{id}` | Xóa đề thi |
| `POST` | `/api/exams/{id}/publish` | Publish đề thi |
| `POST` | `/api/exams/{id}/close` | Đóng đề thi |

### Exam Questions

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/exams/{id}/questions` | Câu hỏi trong đề |
| `POST` | `/api/exams/{id}/questions` | Thêm câu hỏi |
| `DELETE` | `/api/exams/{id}/questions/{qId}` | Xóa câu hỏi khỏi đề |
| `POST` | `/api/exams/{id}/auto-generate` | Tự động lấy câu hỏi |

---

## 📝 Form Tạo Đề Kiểm tra

| Field | Kiểu | Bắt buộc | Mô tả |
|-------|------|----------|-------|
| `name` | string | ✅ | Tên bài kiểm tra |
| `subject_id` | int | ✅ | Môn học |
| `course_group_id` | int | ❌ | Nhóm học phần (nếu có) |
| `start_date` | datetime | ❌ | Thời gian bắt đầu |
| `end_date` | datetime | ❌ | Thời gian kết thúc |
| `duration` | int | ✅ | Thời gian làm bài (phút) |
| `total_questions` | int | ✅ | Số câu hỏi |
| `difficulty_config` | json | ❌ | Cấu hình độ khó |
| `auto_generate` | bool | ❌ | Tự động lấy từ ngân hàng |
| `shuffle_questions` | bool | ❌ | Đảo thứ tự câu hỏi |
| `shuffle_answers` | bool | ❌ | Đảo thứ tự đáp án |
| `max_attempts` | int | ❌ | Số lần làm tối đa |

---

## 📝 Request Example

```json
POST /api/exams
{
  "name": "Kiểm tra giữa kỳ - Lập trình OOP",
  "subject_id": 15,
  "course_group_id": 123,
  "start_date": "2026-03-15T08:00:00",
  "end_date": "2026-03-15T10:00:00",
  "duration": 60,
  "total_questions": 30,
  "total_points": 10.0,
  "difficulty_config": {
    "easy": 10,
    "medium": 15,
    "hard": 5
  },
  "auto_generate": true,
  "shuffle_questions": true,
  "shuffle_answers": true,
  "show_result": true,
  "max_attempts": 1
}
```

---

## 📊 Cấu hình Độ khó

```json
{
  "difficulty_config": {
    "easy": 10,      // 10 câu dễ
    "medium": 15,    // 15 câu trung bình
    "hard": 5        // 5 câu khó
  }
}
```

> Tổng câu trong `difficulty_config` phải bằng `total_questions`

---

## 📊 Trạng thái Đề thi

| Status | Mô tả |
|--------|-------|
| `draft` | Đang soạn, chưa công bố |
| `published` | Đã công bố, chờ thời gian |
| `active` | Đang diễn ra |
| `closed` | Đã kết thúc |
| `archived` | Đã lưu trữ |

---

## 📊 Tự động Lấy Câu hỏi

Khi `auto_generate = true`:
1. Hệ thống lọc câu hỏi theo `subject_id`
2. Phân bổ theo `difficulty_config`
3. Random trong mỗi nhóm độ khó
4. Đảm bảo không trùng lặp

---

## 🔒 Phân quyền

| Hành động | Giảng viên | Admin |
|-----------|------------|-------|
| Xem đề thi của mình | ✅ | ✅ |
| Tạo đề thi | ✅ | ✅ |
| Publish đề thi | ✅ | ✅ |
| Xem kết quả | ✅ | ✅ |

---

## 📁 Database Tables

- `exams` - Thông tin đề thi
- `exam_questions` - Câu hỏi trong đề
- `exam_sessions` - Phiên thi
- `exam_answers` - Câu trả lời
- `exam_results` - Kết quả

---

*Cập nhật: 01/2026*
