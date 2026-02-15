# Module 4: Ngân hàng Câu hỏi (Question Bank)

## 1. Tổng quan

Module cho phép giảng viên tạo, quản lý và phân loại câu hỏi trắc nghiệm theo cấu trúc **Môn học → Chương → Mức độ (Bloom)**. Hỗ trợ nhiều loại câu hỏi (MCQ, True/False, Fill-blank, Matching, Essay) với hệ thống versioning và snapshot khi publish đề thi.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Xem toàn bộ ngân hàng câu hỏi, duyệt câu hỏi |
| **Giảng viên** | CRUD câu hỏi trong môn mình dạy, tag, import từ Excel |
| **Sinh viên** | Không truy cập trực tiếp module này |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F4.1 | Tạo câu hỏi trắc nghiệm (MCQ) | Form tạo câu hỏi: nội dung, 4 đáp án (A/B/C/D), chọn đáp án đúng. Chọn môn + chương + mức độ |
| F4.2 | Tạo câu hỏi Đúng/Sai | Câu hỏi chỉ 2 lựa chọn. Dùng question_type = true_false |
| F4.3 | Danh sách câu hỏi + Filter | Xem theo môn → chương → mức độ. Search theo nội dung, lọc theo status (draft/approved/hidden) |
| F4.4 | Sửa/Xoá câu hỏi | Sửa nội dung → version tăng. Xoá = soft delete |
| F4.5 | Trạng thái câu hỏi | Chuyển giữa Draft → Approved → Hidden. Chỉ câu Approved mới dùng được trong đề thi |
| F4.6 | Phân loại theo Bloom | Mức độ: remember (Nhận biết), understand (Thông hiểu), apply (Vận dụng), analyze (Phân tích) |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F4.7 | Tag/gắn nhãn câu hỏi | Thêm tags tự do (VD: "thi giữa kỳ", "khó", "chương 3"). Dùng bảng `question_tags` |
| F4.8 | Import câu hỏi từ Excel | Upload file Excel → parse thành câu hỏi. Format template chuẩn |
| F4.9 | Hỗ trợ hình ảnh | Upload ảnh minh hoạ cho câu hỏi và đáp án (image_file_id → files) |
| F4.10 | Thống kê câu hỏi | Tỷ lệ đúng (correct_rate), số lần sử dụng (usage_count) — cập nhật sau mỗi kỳ thi |
| F4.11 | Hỗ trợ MathJax/KaTeX | Render công thức toán trong nội dung câu hỏi |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `questions` | Câu hỏi chính (nội dung, mức độ, loại, version, status) |
| `question_options` | Đáp án A/B/C/D cho MCQ/TF |
| `question_types` | Bảng tham chiếu loại câu hỏi (extensible) |
| `question_tags` | Nhãn/tag cho câu hỏi |
| `subjects` | Môn học (parent phân loại) |
| `chapters` | Chương (phân loại chi tiết hơn) |
| `files` | Hình ảnh minh hoạ |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Danh sách câu hỏi (filter theo môn) | `GET /questions` | Admin, GV |
| Tạo câu hỏi | `GET /questions/create` | GV |
| Sửa câu hỏi | `GET /questions/{id}/edit` | GV |
| Chi tiết câu hỏi | `GET /questions/{id}` | Admin, GV |
| Import câu hỏi | `GET /questions/import` | GV |
