# Module 8: Tài liệu & Đề cương (Document & Syllabus)

## 1. Tổng quan

Module quản lý tài liệu học tập và đề cương môn học. Giảng viên upload file, phân loại theo môn/chương. Sinh viên tải tài liệu và xem đề cương. Mọi file được quản lý tập trung qua bảng `files`.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Xem toàn bộ tài liệu, xoá tài liệu |
| **Giảng viên** | Upload/sửa/xoá tài liệu + đề cương trong môn mình dạy |
| **Sinh viên** | Xem + tải tài liệu và đề cương của lớp đang học |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F8.1 | Upload tài liệu | GV upload file (PDF, DOCX, PPTX, ảnh): chọn môn + chương, đặt tiêu đề, mô tả. Giới hạn dung lượng |
| F8.2 | Danh sách tài liệu | Xem tài liệu theo môn → chương. Search theo tiêu đề |
| F8.3 | Tải tài liệu | SV download file. Tăng download_count |
| F8.4 | Sửa/Xoá tài liệu | GV sửa thông tin hoặc soft delete tài liệu |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F8.5 | Tạo/Upload đề cương | GV tạo đề cương (content + file đính kèm), gán vào môn. Status draft/published |
| F8.6 | SV xem đề cương | Xem nội dung đề cương online hoặc tải file |
| F8.7 | Thống kê lượt tải | Xem download_count cho từng tài liệu |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `documents` | Tài liệu (tiêu đề, mô tả, FK file, FK môn/chương, download_count) |
| `syllabi` | Đề cương môn học (nội dung, version, FK file, status) |
| `files` | Quản lý file tập trung (path, mime, size, checksum) |
| `subjects` | Môn học (parent) |
| `chapters` | Chương (phân loại) |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Danh sách tài liệu (theo môn) | `GET /subjects/{id}/documents` | GV, SV |
| Upload tài liệu | `GET /documents/create` | GV |
| Sửa tài liệu | `GET /documents/{id}/edit` | GV |
| Đề cương môn học | `GET /subjects/{id}/syllabus` | GV, SV |
| Tạo/Sửa đề cương | `GET /syllabi/create` | GV |
