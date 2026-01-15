# 📤 Import/Export

## 📋 Tổng quan

Chức năng Import/Export cho phép nhập xuất dữ liệu hàng loạt: câu hỏi, danh sách sinh viên, kết quả thi.

---

## 📥 Import

### 1. Import Câu hỏi

**Định dạng hỗ trợ:** `.xlsx`, `.xls`, `.docx`

**Template Excel:**

| question_content | question_type | difficulty | chapter | answer_a | answer_b | answer_c | answer_d | correct | points |
|------------------|---------------|------------|---------|----------|----------|----------|----------|---------|--------|
| Câu hỏi 1 | single_choice | easy | Chương 1 | A | B | C | D | C | 1.0 |
| Câu hỏi 2 | multiple_choice | medium | Chương 2 | A | B | C | | A,B | 2.0 |
| Câu hỏi 3 | true_false | hard | | | | | | TRUE | 1.0 |

**API:**
```
POST /api/questions/import
Content-Type: multipart/form-data

file: [Excel/Word file]
subject_id: 15
```

### 2. Import Sinh viên

**Template Excel:**

| student_code | full_name | email | phone |
|--------------|-----------|-------|-------|
| 21020001 | Nguyễn Văn A | a@email.com | 0901234567 |
| 21020002 | Trần Thị B | b@email.com | 0907654321 |

**API:**
```
POST /api/course-groups/{id}/students/import
Content-Type: multipart/form-data

file: [Excel file]
```

---

## 📤 Export

### 1. Export Câu hỏi

```
GET /api/questions/export?subject_id=15&chapter_id=42&format=xlsx
```

### 2. Export Danh sách Sinh viên

```
GET /api/course-groups/{id}/students/export?format=xlsx
```

### 3. Export Kết quả Thi

```
GET /api/exams/{id}/results/export?format=xlsx
```

---

## 📊 Xử lý Lỗi Import

```json
{
  "success": false,
  "data": {
    "total_rows": 100,
    "success_rows": 95,
    "failed_rows": 5,
    "errors": [
      { "row": 10, "error": "Missing question content" },
      { "row": 25, "error": "Invalid difficulty level" }
    ]
  }
}
```

---

## 🔌 API Endpoints

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/questions/template` | Tải template câu hỏi |
| `POST` | `/api/questions/import` | Import câu hỏi |
| `GET` | `/api/questions/export` | Export câu hỏi |
| `GET` | `/api/students/template` | Tải template sinh viên |
| `POST` | `/api/course-groups/{id}/students/import` | Import sinh viên |
| `GET` | `/api/course-groups/{id}/students/export` | Export sinh viên |
| `GET` | `/api/exams/{id}/results/export` | Export kết quả |

---

## 📁 Database Table

```sql
CREATE TABLE import_export_jobs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    type ENUM('import', 'export'),
    entity VARCHAR(50),  -- 'questions', 'students', 'results'
    file_path VARCHAR(500),
    status ENUM('pending', 'processing', 'completed', 'failed'),
    total_rows INT,
    success_rows INT,
    failed_rows INT,
    errors JSON
);
```

---

*Cập nhật: 01/2026*
