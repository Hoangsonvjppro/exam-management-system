# Kế Hoạch Phân Chia Công Việc — Nhóm 5 Người × 4 Tuần

## Phân vai

| Thành viên | Vai trò | Phụ trách chính |
|---|---|---|
| **P1** | Team Lead + Fullstack | Setup, Auth, Layout, Notification, Tích hợp |
| **P2** | Backend chính | Môn học, Câu hỏi, Snapshot, Chấm điểm |
| **P3** | Backend + Exam | Đề thi, Lịch thi, Giao diện làm bài (SV) |
| **P4** | Frontend chính | UI components, Dashboard, Thống kê, Polish |
| **P5** | Fullstack | Sinh viên, Điểm danh, Tài liệu, Import/Export |

---

## Chi Tiết Từng Tuần

### 🗓️ Tuần 1: Nền Tảng (17/02 – 23/02)

> **Mục tiêu:** Chạy được app, đăng nhập, CRUD cơ bản cho Môn học / Học kỳ / Lớp HP

| Người | Công việc | Feature IDs |
|---|---|---|
| **P1** | Setup project (đã xong). Tạo layout chung (sidebar, navbar, responsive). Middleware phân quyền. Seeder roles. Auth flow hoàn chỉnh | F1.1–F1.3 |
| **P2** | Models + Migrations cho: `semesters`, `subjects`, `chapters`. CRUD Semester, Subject, Chapter | F2.1–F2.3 |
| **P3** | Models + Migrations cho: `course_sections`, `class_schedules`, `course_section_students`. CRUD Course Section | F2.4–F2.7 |
| **P4** | Thiết kế UI system (color, typography, button styles). Tạo Blade components dùng chung (table, form, modal, card, pagination). Dashboard layout skeleton | — |
| **P5** | Models + Migration cho: `files`, `question_types`. File upload service. Seeder: question_types, settings, demo data | — |

**Deliverable cuối tuần 1:** Đăng nhập → Dashboard → Xem/Tạo/Sửa/Xoá Môn học, Học kỳ, Lớp HP.

---

### 🗓️ Tuần 2: Core Features (24/02 – 02/03)

> **Mục tiêu:** Quản lý Users, Ngân hàng câu hỏi, Tạo đề thi, Gán sinh viên

| Người | Công việc | Feature IDs |
|---|---|---|
| **P1** | CRUD Users (Admin). Khoá/mở khoá. Profile page. Đổi mật khẩu | F1.4–F1.7 |
| **P2** | Models câu hỏi (`questions`, `question_options`). Tạo/Sửa/Xoá câu hỏi MCQ + True/False. Danh sách + Filter | F4.1–F4.6 |
| **P3** | Models đề thi (`exam_papers`, `exam_paper_questions`). Tạo đề thi + chọn câu hỏi. Cấu hình đề | F5.1–F5.2 |
| **P4** | UI cho Question Bank (form tạo câu hỏi dynamic, bảng filter). UI cho Exam Paper (question selector) | F4.3, F5.1 |
| **P5** | DSSV theo lớp HP. Gán sinh viên vào lớp. Import SV từ CSV (Maatwebsite) | F2.6, F3.1–F3.3 |

**Deliverable cuối tuần 2:** Tạo câu hỏi → Tạo đề thi → Chọn câu vào đề. Admin quản lý users. Import SV.

---

### 🗓️ Tuần 3: Exam Flow + Điểm Danh (03/03 – 09/03)

> **Mục tiêu:** Sinh viên thi được. Giảng viên điểm danh được. Core flow hoàn chỉnh.

| Người | Công việc | Feature IDs |
|---|---|---|
| **P1** | Publish đề + Snapshot logic. Notification system (bell icon, tự động gửi khi có bài thi) | F5.3, F9.1–F9.4 |
| **P2** | Lên lịch thi (Exam Schedule). Chấm điểm tự động. Xem danh sách SV đã/chưa thi | F5.4–F5.7 |
| **P3** | **Giao diện làm bài thi SV:** Vào phòng thi, hiển thị câu hỏi, chọn đáp án, đánh dấu, sidebar navigation | F6.1–F6.5 |
| **P4** | **Timer countdown JS**, auto-submit. UI kết quả bài thi. Lịch sử bài thi SV | F6.3, F6.6–F6.9 |
| **P5** | Tạo buổi điểm danh. Điểm danh thủ công + PIN. SV xem lịch sử. Thống kê chuyên cần | F7.1–F7.6 |

**Deliverable cuối tuần 3:** E2E flow hoàn chỉnh: GV tạo đề → Publish → Lên lịch → SV thi → Xem điểm. Điểm danh hoạt động.

---

### 🗓️ Tuần 4: Polish + Bổ Sung (10/03 – 16/03)

> **Mục tiêu:** Fix bug, hoàn thiện UI, bổ sung tính năng nice-to-have, chuẩn bị demo.

| Người | Công việc | Feature IDs |
|---|---|---|
| **P1** | Tích hợp toàn bộ, fix bug liên module. Import users từ CSV. Chuẩn bị slides/demo | F1.9 |
| **P2** | Export kết quả Excel. Tag câu hỏi. Import câu hỏi Excel (nếu kịp) | F5.10, F4.7, F4.8 |
| **P3** | Auto-save bài thi. Anti-cheating (nếu kịp). Polish UX làm bài | F6.10–F6.11 |
| **P4** | Dashboard Admin + GV + SV. Biểu đồ điểm (Chart.js). Responsive polish | F10.1–F10.3 |
| **P5** | Upload tài liệu + Đề cương. QR điểm danh (nếu kịp). Fix bug | F8.1–F8.6, F7.7 |

**Deliverable cuối tuần 4:** Sản phẩm hoàn chỉnh, sẵn sàng demo.

---

## Dependency Map

```
Tuần 1                    Tuần 2                    Tuần 3                    Tuần 4
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐      ┌──────────────┐
│ P1: Auth+Layout │─────▶│ P1: User CRUD   │─────▶│ P1: Snapshot    │─────▶│ P1: Tích hợp │
│                 │      │                 │      │     Notification│      │     Bug fix   │
├─────────────────┤      ├─────────────────┤      ├─────────────────┤      ├──────────────┤
│ P2: Semester    │─────▶│ P2: Question    │─────▶│ P2: Schedule    │─────▶│ P2: Export   │
│     Subject     │      │     Bank CRUD   │      │     Auto-grade  │      │     Import   │
├─────────────────┤      ├─────────────────┤      ├─────────────────┤      ├──────────────┤
│ P3: Course      │─────▶│ P3: Exam Paper  │─────▶│ P3: Exam Taking │─────▶│ P3: Polish   │
│     Section     │      │     Create      │      │     UI (SV)     │      │     Anti-cheat│
├─────────────────┤      ├─────────────────┤      ├─────────────────┤      ├──────────────┤
│ P4: UI System   │─────▶│ P4: Question UI │─────▶│ P4: Timer,      │─────▶│ P4: Dashboard│
│     Components  │      │     Exam UI     │      │     Result UI   │      │     Charts   │
├─────────────────┤      ├─────────────────┤      ├─────────────────┤      ├──────────────┤
│ P5: Files,      │─────▶│ P5: Student     │─────▶│ P5: Attendance  │─────▶│ P5: Document │
│     Seeders     │      │     Enrollment  │      │     Manual+PIN  │      │     QR Code  │
└─────────────────┘      └─────────────────┘      └─────────────────┘      └──────────────┘
```

---

## Quy Tắc Làm Việc

1. **Git branching:** Mỗi người làm trên branch `feature/module-name`. Merge vào `develop` qua Pull Request
2. **Daily sync:** Mỗi ngày sync 15 phút (báo tiến độ, báo blocker)
3. **Ưu tiên must-have:** Tuần 1-3 chỉ làm must-have. Nice-to-have dồn tuần 4
4. **Xong sớm = giúp người khác:** Ai xong trước thì review PR hoặc giúp người bị chậm
5. **Cập nhật progress.json:** Khi hoàn thành task → update file `progress.json` tương ứng

---

## Rủi Ro & Phương Án Dự Phòng

| Rủi ro | Phương án |
|---|---|
| Exam Taking (Module 5+6) phức tạp, bị chậm | P3 + P4 pair programming. Đơn giản hoá snapshot nếu cần |
| Import Excel lỗi format | Chỉ hỗ trợ CSV format đơn giản trước |
| UI chưa đẹp | P4 focus Polish ở tuần 4. Dùng UI component library (Flux/Mary) |
| Thiếu thời gian | Cắt nice-to-have: Module 8 (Tài liệu), F4.8 (Import câu hỏi), F7.7 (QR), F10.3+ |
