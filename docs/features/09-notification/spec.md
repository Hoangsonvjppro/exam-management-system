# Module 9: Thông báo (Notification)

## 1. Tổng quan

Module thông báo in-app cho người dùng: thông báo khi có bài thi mới, tài liệu mới, điểm thi, và nhắc nhở deadline. Sử dụng Laravel Notification system với database channel.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Gửi thông báo toàn hệ thống |
| **Giảng viên** | Nhận thông báo liên quan. Tự động gửi khi tạo đề/tài liệu |
| **Sinh viên** | Nhận thông báo: bài thi, điểm, tài liệu, deadline |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F9.1 | Hiển thị thông báo | Bell icon trên navbar + badge số chưa đọc. Dropdown danh sách thông báo mới nhất |
| F9.2 | Đánh dấu đã đọc | Click vào thông báo → mark read (read_at). Nút "Đánh dấu tất cả đã đọc" |
| F9.3 | Trang tất cả thông báo | Xem toàn bộ thông báo, phân trang, filter đã đọc/chưa đọc |
| F9.4 | Tự động tạo thông báo | Tự động gửi khi: đề thi mới được lên lịch, tài liệu mới upload, điểm thi được publish |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F9.5 | Nhắc nhở deadline | Trước 24h khi bài thi hết hạn → gửi notification nhắc SV |
| F9.6 | Notification preferences | User chọn loại thông báo muốn nhận (bài thi, tài liệu, điểm...) |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `notifications` | Thông báo (UUID, user_id, type, title, message, data JSON, read_at) |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Bell icon + dropdown (component) | (navbar component) | All |
| Trang tất cả thông báo | `GET /notifications` | All |
