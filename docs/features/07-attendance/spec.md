# Module 7: Điểm danh (Attendance)

## 1. Tổng quan

Module điểm danh cho phép giảng viên tạo buổi điểm danh, sinh viên xác nhận có mặt qua nhiều phương thức (thủ công, QR code, mã PIN). Hỗ trợ GPS validation và thống kê chuyên cần.

## 2. Actors & Quyền hạn

| Actor | Quyền hạn |
|---|---|
| **Admin** | Xem thống kê điểm danh tổng |
| **Giảng viên** | Tạo buổi điểm danh, chọn phương thức, chỉnh sửa kết quả, xem thống kê |
| **Sinh viên** | Điểm danh (quét QR / nhập PIN), xem lịch sử điểm danh cá nhân |

## 3. Danh sách chức năng

### Must-have (MVP)

| ID | Chức năng | Mô tả |
|---|---|---|
| F7.1 | Tạo buổi điểm danh | GV tạo session: chọn lớp HP, ngày, tiêu đề, phương thức (manual/qr/pin), thời hạn |
| F7.2 | Điểm danh thủ công | GV check mark từng SV trong DSSV lớp HP. Trạng thái: present/absent_excused/absent_unexcused/late |
| F7.3 | Điểm danh bằng mã PIN | GV tạo mã PIN → SV nhập PIN trên giao diện → hệ thống ghi nhận |
| F7.4 | Chỉnh sửa điểm danh | GV sửa trạng thái SV sau buổi học (VD: chuyển absent → late, thêm ghi chú) |
| F7.5 | SV xem lịch sử điểm danh | SV xem danh sách buổi điểm danh + trạng thái của mình theo từng lớp HP |
| F7.6 | Thống kê chuyên cần | Tỷ lệ % có mặt theo SV, theo lớp HP. Bảng tổng hợp |

### Nice-to-have

| ID | Chức năng | Mô tả |
|---|---|---|
| F7.7 | Điểm danh bằng QR Code | GV tạo QR → sinh viên quét QR bằng camera → điểm danh. Dùng simplesoftwareio/simple-qrcode |
| F7.8 | GPS validation | So sánh vị trí SV với vị trí GV. Reject nếu khoảng cách > ngưỡng (settings: attendance_geo_radius_m) |
| F7.9 | Cảnh báo vắng quá mức | Tự động cảnh báo khi SV vắng quá số buổi cho phép (settings: max_absent_allowed) |

## 4. Database Tables liên quan

| Bảng | Vai trò |
|---|---|
| `attendance_sessions` | Buổi điểm danh (ngày, phương thức, QR/PIN, GPS GV, thời hạn) |
| `attendance_records` | Bản ghi từng SV (trạng thái, GPS SV, khoảng cách) |
| `course_sections` | Lớp HP (parent) |
| `course_section_students` | DSSV để tạo danh sách điểm danh |
| `settings` | Cấu hình (max_absent_allowed, attendance_geo_radius_m) |

## 5. Giao diện (UI Screens)

| Màn hình | Route gợi ý | Actor |
|---|---|---|
| Danh sách buổi điểm danh | `GET /course-sections/{id}/attendance` | GV |
| Tạo buổi điểm danh | `GET /attendance/create?section={id}` | GV |
| Điểm danh thủ công | `GET /attendance/{id}/manual` | GV |
| Hiển thị mã QR/PIN | `GET /attendance/{id}/code` | GV |
| SV nhập PIN | `GET /attendance/check-in` | SV |
| Lịch sử điểm danh SV | `GET /my-attendance` | SV |
| Thống kê chuyên cần | `GET /course-sections/{id}/attendance/stats` | GV |
