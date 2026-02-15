 # Phân Tích Nghiệp Vụ - Hệ Thống Quản Lý Thi Trắc Nghiệm Online (EMS)

---

## 1. Tổng Quan Hệ Thống

**Tên dự án:** EMS - Examination Management System \
**Công nghệ:** PHP Laravel 12, MySQL/PostgreSQL\
**Mục tiêu:** Hỗ trợ giảng viên tạo/quản lý bài thi trắc nghiệm, điểm danh, quản lý sinh viên, học phần và tài liệu.

---

## 2. Các Vai Trò (Actors)

| Vai trò | Mô tả |
|---|---|
| **Admin** | Quản trị hệ thống, quản lý người dùng, cấu hình chung |
| **Giảng viên** | Tạo đề thi, quản lý lớp học phần, điểm danh, upload tài liệu |
| **Sinh viên** | Làm bài thi, xem điểm, tải tài liệu, xem lịch sử điểm danh |

---

## 3. Danh Sách Module & Chức Năng Chi Tiết

### Module 1: Quản lý Người dùng (User Management)
- Đăng ký / Đăng nhập (Email, SSO nếu cần)
- Phân quyền theo vai trò (RBAC): Admin, Giảng viên, Sinh viên
- Quản lý hồ sơ cá nhân (avatar, thông tin liên hệ)
- Đổi mật khẩu, quên mật khẩu
- Import danh sách người dùng từ Excel/CSV (Admin)
- Khoá / mở khoá tài khoản

### Module 2: Quản lý Môn học & Học phần (Course Management)
- **Môn học (Subject):** Tạo, sửa, xoá môn học (mã môn, tên, số tín chỉ, mô tả)
- **Học phần (Course Section):** Tạo lớp học phần theo từng học kỳ (mã lớp, giảng viên phụ trách, lịch học, phòng học)
- Gán giảng viên vào học phần
- Gán/import sinh viên vào học phần
- Quản lý học kỳ (Semester): năm học, kỳ, ngày bắt đầu/kết thúc

### Module 3: Quản lý Sinh viên (Student Management)
- Danh sách sinh viên theo học phần
- Import sinh viên từ Excel/CSV
- Xem thông tin chi tiết sinh viên (MSSV, họ tên, lớp, email, SĐT)
- Thống kê: điểm thi, số buổi điểm danh, tài liệu đã tải

### Module 4: Ngân hàng Câu hỏi (Question Bank)
- Tạo câu hỏi trắc nghiệm (nhiều lựa chọn, đúng/sai)
- Phân loại câu hỏi theo: **Môn học → Chương → Mức độ** (Nhận biết, Thông hiểu, Vận dụng, Vận dụng cao)
- Hỗ trợ chèn hình ảnh, công thức toán (MathJax/KaTeX)
- Import câu hỏi từ file Word/Excel
- Tag/gắn nhãn câu hỏi
- Trạng thái câu hỏi: Nháp, Đã duyệt, Đã ẩn
- Thống kê độ khó câu hỏi dựa trên kết quả thi thực tế

### Module 5: Quản lý Đề thi & Bài thi (Exam Management)
- **Tạo đề thi:**
  - Chọn câu hỏi thủ công từ ngân hàng
  - Tự động tạo đề theo ma trận (số câu/chương/mức độ)
  - Trộn đề (shuffle câu hỏi & đáp án) → tạo nhiều mã đề
- **Cấu hình bài thi:**
  - Thời gian làm bài
  - Số lần được phép thi
  - Thời gian mở/đóng bài thi
  - Cho phép quay lại câu trước hay không
  - Hiển thị kết quả ngay hay sau khi hết hạn
  - Điểm đạt (pass score)
  - Chế độ: Thi chính thức / Luyện tập
- **Quản lý bài thi:**
  - Gán bài thi cho học phần
  - Xem danh sách sinh viên đã/chưa thi
  - Chấm điểm tự động
  - Xuất kết quả ra Excel
  - Thống kê phân bổ điểm (biểu đồ)

### Module 6: Làm bài thi (Exam Taking - Sinh viên)
- Giao diện làm bài trực quan, đếm ngược thời gian
- Đánh dấu câu hỏi để xem lại
- Tự động nộp bài khi hết giờ
- Xem kết quả & đáp án (nếu GV cho phép)
- Xem lịch sử các bài thi đã làm

### Module 7: Điểm danh (Attendance)
- Giảng viên tạo buổi điểm danh theo học phần
- **Phương thức điểm danh:**
  - Điểm danh thủ công (check từng SV)
  - Điểm danh bằng mã QR (sinh viên quét QR trong thời gian giới hạn)
  - Điểm danh bằng mã code/PIN (GV tạo mã, SV nhập)
- Trạng thái: Có mặt / Vắng có phép / Vắng không phép / Đi muộn
- Giảng viên chỉnh sửa điểm danh sau buổi học
- Sinh viên xem lịch sử điểm danh của mình
- Thống kê tỷ lệ chuyên cần theo sinh viên / học phần
- Cảnh báo sinh viên vắng quá số buổi cho phép

### Module 8: Tài liệu & Đề cương (Document & Syllabus)
- **Đề cương môn học:**
  - Tạo/upload đề cương theo mẫu chuẩn
  - Gán đề cương vào môn học
  - Sinh viên xem/tải đề cương
- **Tài liệu học tập:**
  - Upload file (PDF, DOCX, PPTX, hình ảnh, video link)
  - Phân loại theo: Môn học → Chương/Tuần
  - Giới hạn dung lượng file
  - Sinh viên tải tài liệu
  - Thống kê lượt tải

### Module 9: Thông báo (Notification)
- Thông báo khi có bài thi mới
- Thông báo khi có tài liệu mới
- Nhắc nhở deadline bài thi sắp đến
- Thông báo điểm thi
- Kênh: In-app notification, Email (tuỳ chọn)

### Module 10: Báo cáo & Thống kê (Report & Analytics)
- Thống kê điểm thi theo học phần (trung bình, cao nhất, thấp nhất, phân phối)
- Thống kê chuyên cần theo học phần
- Báo cáo hoạt động giảng viên (số đề thi, số buổi điểm danh...)
- Dashboard tổng quan cho Admin
- Xuất báo cáo PDF/Excel

---

## 4. Phương Hướng Phát Triển

### Phase 1 - MVP (4-6 tuần)
| Ưu tiên | Module |
|---|---|
| 1 | Authentication & User Management (đăng nhập, phân quyền) |
| 2 | Quản lý Môn học, Học kỳ, Học phần |
| 3 | Quản lý Sinh viên (CRUD, import CSV) |
| 4 | Ngân hàng câu hỏi (CRUD cơ bản) |
| 5 | Tạo đề thi & Làm bài thi (core flow) |

### Phase 2 - Essential Features (3-4 tuần)
| Ưu tiên | Module |
|---|---|
| 6 | Chấm điểm tự động & Thống kê điểm |
| 7 | Điểm danh (thủ công + QR code) |
| 8 | Upload tài liệu & Đề cương |
| 9 | Thông báo in-app |

### Phase 3 - Enhancement (3-4 tuần)
| Ưu tiên | Module |
|---|---|
| 10 | Trộn đề tự động theo ma trận |
| 11 | Import câu hỏi từ Word/Excel |
| 12 | Báo cáo & Dashboard |
| 13 | Export Excel/PDF |
| 14 | Email notification |

### Phase 4 - Advanced (tuỳ chọn)
- API cho Mobile App
- Chống gian lận (full-screen mode, tab-switch detection)
- Tích hợp SSO trường đại học
- AI gợi ý câu hỏi / phân tích độ khó

---

## 5. Tech Stack Đề Xuất

> **Phương án: Laravel Fullstack (Blade + Livewire 3)** — Tối ưu cho tốc độ phát triển, một codebase duy nhất, không cần tách Frontend/Backend riêng.

| Layer | Công nghệ | Ghi chú |
|---|---|---|
| **Backend** | PHP 8.3+ / Laravel 12 | Framework chính |
| **Frontend** | Blade + Livewire 3 | Không cần viết API riêng, xử lý tương tác real-time trực tiếp |
| **UI Components** | Flux UI hoặc Mary UI | Component library cho Livewire, tiết kiệm thời gian dựng giao diện |
| **CSS** | Tailwind CSS 4 | Utility-first, dựng layout nhanh |
| **Database** | MySQL 8 (Podman container) | Quen thuộc, dễ setup |
| **Auth** | Laravel Breeze (Blade stack) | Scaffold đăng nhập/đăng ký sẵn trong 1 lệnh |
| **Phân quyền** | Spatie Laravel Permission | Gói RBAC phổ biến nhất cho Laravel |
| **File Storage** | Laravel Storage (local disk) | Đủ dùng cho đồ án, không cần S3 |
| **Queue** | Laravel Queue (database driver) | Dùng database driver thay Redis, bớt 1 service phải cài |
| **Export** | Maatwebsite/Excel | Import/export CSV, Excel |
| **QR Code** | Simple QrCode package | Tạo QR cho điểm danh |

### Những gì đã lược bỏ (không cần cho đồ án)

| Đã bỏ | Lý do |
|---|---|
| Redis (Cache & Queue) | Dùng `database` driver cho queue & cache là đủ, bớt 1 service phải cài đặt |
| Laravel Reverb (WebSocket) | Countdown timer dùng JavaScript thuần phía client là đủ, WebSocket là overkill |
| Email notification | Chỉ cần in-app notification, bỏ email để giảm config SMTP |
| S3 Storage | Lưu file local là đủ cho đồ án |
| DomPDF (export PDF) | Ưu tiên thấp, có thời gian thì bổ sung sau |

---

## 6. Một Số Lưu Ý Nghiệp Vụ Quan Trọng

1. **Anti-cheating:** Khi sinh viên làm bài thi, trộn thứ tự câu hỏi và đáp án để hạn chế gian lận. Cân nhắc thêm full-screen enforcement.
2. **Auto-submit:** Tự động nộp bài khi hết giờ (cần xử lý cả phía server, không chỉ client).
3. **Concurrent access:** Nhiều sinh viên thi cùng lúc → cần tối ưu database query và cân nhắc caching.
4. **Soft delete:** Nên dùng `SoftDeletes` cho các bảng quan trọng (users, questions, exams) để tránh mất dữ liệu.
5. **Audit log:** Ghi lại hành động quan trọng (tạo đề, sửa điểm, xoá câu hỏi) để truy vết.
6. **Timezone:** Thống nhất timezone (Asia/Ho_Chi_Minh) cho toàn bộ hệ thống.
7. **File size limit:** Giới hạn upload (ví dụ 20MB/file) và validate file type phía server.

---

Bạn muốn tôi bắt đầu triển khai code cho phase/module nào trước? Hoặc nếu cần điều chỉnh gì về chức năng hay database, hãy cho tôi biết.