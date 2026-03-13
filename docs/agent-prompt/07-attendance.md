# Agent Prompt: Module 07 — Điểm Danh (Attendance)

> **Module ID:** 07-attendance  
> **Priority:** Phase 2 
> **Assignee:** P5  
> **Branch:** `features/attendence`  
> **Dependencies:** Module 02 (Course Sections), Module 03 (Student Enrollment)  
> **Workspace root:** `/home/sonle/Projects/EMS-exam-management-system/src/`

---

## 1. BỐI CẢNH DỰ ÁN

Đây là hệ thống **EMS (Examination Management System)** xây bằng **Laravel 12 + Blade + Tailwind CSS + Alpine.js**. Không dùng Livewire (dù đã cài). Kiến trúc **MVC + Service Layer** với Spatie Permission cho RBAC.

### Stack kỹ thuật
- **Backend:** Laravel 12, PHP 8.4, MySQL 8+
- **Frontend:** Blade templates, Tailwind CSS, Alpine.js, Vite
- **Auth:** Laravel Breeze + Spatie Permission (HasRoles)
- **File:** FileUploadService (centralized upload, SHA-256 checksum)
- **QR Code:** `simplesoftwareio/simple-qrcode` (đã có trong composer.json)

### Conventions bắt buộc
- **Controller:** RESTful CRUD, tất cả write operations bọc trong `DB::transaction()` + `try-catch`
- **Model:** Dùng `$fillable`, `casts()`, relationships, `scopeXxx()` query scopes
- **Route:** Prefix theo role (`lecturer.`, `student.`, `admin.`), middleware `['auth', 'active', 'role:xxx']`
- **View:** Dùng Blade components (`<x-table>`, `<x-modal>`, `<x-text-input>`, `<x-primary-button>`, etc.)
- **Error messages:** Tiếng Việt, redirect with `->with('success|error', '...')`
- **Sidebar:** Thêm link bằng `<x-sidebar-link route="..." icon="...">` trong `<x-sidebar-section>`

---

## 2. DATABASE SCHEMA (đã thiết kế, cần tạo migration)

### Bảng `attendance_sessions`
```sql
CREATE TABLE `attendance_sessions` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_section_id` BIGINT UNSIGNED NOT NULL,
    `created_by`        BIGINT UNSIGNED NOT NULL,       -- GV tạo buổi điểm danh
    `session_date`      DATE            NOT NULL,
    `title`             VARCHAR(255)    NULL,            -- VD: "Buổi 1 - Giới thiệu"
    `method`            ENUM('manual','qr_code','pin_code') NOT NULL DEFAULT 'manual',
    `qr_code`           VARCHAR(255)    NULL,
    `pin_code`          VARCHAR(10)     NULL,
    `expires_at`        DATETIME        NULL,            -- Hết hạn điểm danh
    `latitude`          DECIMAL(10,8)   NULL,            -- GPS giảng viên
    `longitude`         DECIMAL(11,8)   NULL,
    `notes`             TEXT            NULL,
    `status`            ENUM('open','closed') NOT NULL DEFAULT 'open',
    `created_at`        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX (`course_section_id`),
    INDEX (`created_by`),
    INDEX (`session_date`),
    INDEX (`status`),
    FOREIGN KEY (`course_section_id`) REFERENCES `course_sections`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
);
```

### Bảng `attendance_records`
```sql
CREATE TABLE `attendance_records` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `attendance_session_id` BIGINT UNSIGNED NOT NULL,
    `student_id`            BIGINT UNSIGNED NOT NULL,
    `status`                ENUM('present','absent_excused','absent_unexcused','late') NOT NULL DEFAULT 'present',
    `checked_at`            DATETIME NULL,
    `student_latitude`      DECIMAL(10,8)  NULL,       -- GPS sinh viên
    `student_longitude`     DECIMAL(11,8)  NULL,
    `distance_meters`       DECIMAL(10,2)  NULL,       -- Khoảng cách SV-GV
    `note`                  VARCHAR(255)   NULL,
    `created_at`            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`attendance_session_id`, `student_id`),
    INDEX (`student_id`),
    INDEX (`status`),
    FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
```

### Bảng liên quan (đã tồn tại)
- `course_sections` — lớp học phần (đã có model + migration)
- `course_section_students` — DSSV đăng ký lớp HP (đã có, có `status` ENUM enrolled/dropped/completed)
- `settings` — cấu hình `max_absent_allowed`, `attendance_geo_radius_m` (đã có migration)

---

## 3. KẾ HOẠCH TRIỂN KHAI CHI TIẾT

### Phase A: Foundation (Models + Migrations + Routes)

#### Task A1: Migration `attendance_sessions`
- **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_attendance_sessions_table.php`
- Tạo bảng theo schema ở mục 2
- Các cột: id, course_section_id, created_by, session_date, title, method, qr_code, pin_code, expires_at, latitude, longitude, notes, status, timestamps

#### Task A2: Migration `attendance_records`
- **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_attendance_records_table.php`
- Tạo bảng theo schema ở mục 2
- Unique constraint trên `(attendance_session_id, student_id)`

#### Task A3: Model `AttendanceSession`
- **File:** `app/Models/AttendanceSession.php`
- **Fillable:** course_section_id, created_by, session_date, title, method, qr_code, pin_code, expires_at, latitude, longitude, notes, status
- **Casts:**
  ```php
  'session_date' => 'date',
  'expires_at' => 'datetime',
  'latitude' => 'decimal:8',
  'longitude' => 'decimal:8',
  ```
- **Relationships:**
  - `courseSection()` → belongsTo(CourseSection)
  - `creator()` → belongsTo(User, 'created_by')
  - `records()` → hasMany(AttendanceRecord)
- **Scopes:**
  - `scopeOpen($query)` → where('status', 'open')
  - `scopeClosed($query)` → where('status', 'closed')
  - `scopeForSection($query, $sectionId)` → where('course_section_id', $sectionId)
- **Methods:**
  - `isExpired(): bool` → kiểm tra expires_at < now
  - `close(): void` → update status = closed
  - `generatePinCode(): string` → tạo mã PIN 6 số ngẫu nhiên (random_int, không dùng rand)
  - `presentCount(): int` → đếm records có status = present
  - `totalStudents(): int` → đếm tổng SV qua courseSection->students enrolled

#### Task A4: Model `AttendanceRecord`
- **File:** `app/Models/AttendanceRecord.php`
- **Fillable:** attendance_session_id, student_id, status, checked_at, student_latitude, student_longitude, distance_meters, note
- **Casts:**
  ```php
  'checked_at' => 'datetime',
  'student_latitude' => 'decimal:8',
  'student_longitude' => 'decimal:8',
  'distance_meters' => 'decimal:2',
  ```
- **Relationships:**
  - `session()` → belongsTo(AttendanceSession, 'attendance_session_id')
  - `student()` → belongsTo(User, 'student_id')
- **Constants (ENUM):**
  ```php
  const STATUS_PRESENT = 'present';
  const STATUS_ABSENT_EXCUSED = 'absent_excused';
  const STATUS_ABSENT_UNEXCUSED = 'absent_unexcused';
  const STATUS_LATE = 'late';
  ```

#### Task A5: Thêm relationship vào models sẵn có
- **CourseSection model** — thêm:
  ```php
  public function attendanceSessions(): HasMany
  {
      return $this->hasMany(AttendanceSession::class);
  }
  ```
- **User model** — thêm:
  ```php
  public function attendanceRecords(): HasMany
  {
      return $this->hasMany(AttendanceRecord::class, 'student_id');
  }
  ```

#### Task A6: Routes
- **File:** `routes/web.php`
- **Lecturer routes** (trong group `lecturer.`, middleware `['auth', 'active', 'role:lecturer|teaching_assistant']`):
  ```php
  // Attendance — danh sách buổi điểm danh theo lớp HP
  Route::get('course-sections/{courseSection}/attendance', [AttendanceSessionController::class, 'index'])
      ->name('attendance.index');
  Route::get('course-sections/{courseSection}/attendance/create', [AttendanceSessionController::class, 'create'])
      ->name('attendance.create');
  Route::post('course-sections/{courseSection}/attendance', [AttendanceSessionController::class, 'store'])
      ->name('attendance.store');
  Route::get('attendance/{attendanceSession}', [AttendanceSessionController::class, 'show'])
      ->name('attendance.show');
  Route::get('attendance/{attendanceSession}/edit', [AttendanceSessionController::class, 'edit'])
      ->name('attendance.edit');
  Route::put('attendance/{attendanceSession}', [AttendanceSessionController::class, 'update'])
      ->name('attendance.update');
  Route::delete('attendance/{attendanceSession}', [AttendanceSessionController::class, 'destroy'])
      ->name('attendance.destroy');

  // Điểm danh thủ công
  Route::get('attendance/{attendanceSession}/manual', [AttendanceSessionController::class, 'manual'])
      ->name('attendance.manual');
  Route::post('attendance/{attendanceSession}/manual', [AttendanceSessionController::class, 'storeManual'])
      ->name('attendance.storeManual');

  // Hiển thị mã PIN/QR
  Route::get('attendance/{attendanceSession}/code', [AttendanceSessionController::class, 'showCode'])
      ->name('attendance.showCode');

  // Đóng buổi điểm danh
  Route::post('attendance/{attendanceSession}/close', [AttendanceSessionController::class, 'close'])
      ->name('attendance.close');

  // Thống kê chuyên cần
  Route::get('course-sections/{courseSection}/attendance/stats', [AttendanceSessionController::class, 'stats'])
      ->name('attendance.stats');
  ```
- **Student routes** (trong group `student.`, middleware `['auth', 'active', 'role:student']`):
  ```php
  // SV nhập PIN điểm danh
  Route::get('attendance/check-in', [StudentAttendanceController::class, 'checkInForm'])
      ->name('attendance.checkInForm');
  Route::post('attendance/check-in', [StudentAttendanceController::class, 'checkIn'])
      ->name('attendance.checkIn');

  // Lịch sử điểm danh cá nhân
  Route::get('my-attendance', [StudentAttendanceController::class, 'history'])
      ->name('attendance.history');
  Route::get('my-attendance/{courseSection}', [StudentAttendanceController::class, 'sectionHistory'])
      ->name('attendance.sectionHistory');
  ```

---

### Phase B: Feature F7.1 — Tạo buổi điểm danh

#### Task B1: AttendanceSessionController — index, create, store
- **File:** `app/Http/Controllers/AttendanceSessionController.php`
- **Method `index($courseSection)`:**
  - Kiểm tra: user là lecturer_id của courseSection HOẶC admin
  - Eager load: `attendanceSessions` with count records
  - Trả về view `lecturer/attendance/index.blade.php`
  - Truyền: `$courseSection`, `$sessions` (paginated, order by session_date DESC)
- **Method `create($courseSection)`:**
  - Kiểm tra quyền
  - Trả về view `lecturer/attendance/create.blade.php`
  - Truyền: `$courseSection`
- **Method `store(StoreAttendanceSessionRequest $request, $courseSection)`:**
  - Validate qua FormRequest
  - `DB::transaction()`:
    - Tạo AttendanceSession
    - Nếu method = 'pin_code': auto-generate PIN 6 số (`str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)`)
    - Nếu method = 'manual': tạo sẵn AttendanceRecord cho TẤT CẢ SV enrolled (status = absent_unexcused mặc định)
  - Redirect về `attendance.show` hoặc `attendance.manual` tùy method

#### Task B2: StoreAttendanceSessionRequest
- **File:** `app/Http/Requests/StoreAttendanceSessionRequest.php`
- **authorize():** User phải là lecturer của courseSection
- **rules():**
  ```php
  'session_date' => ['required', 'date'],
  'title'        => ['nullable', 'string', 'max:255'],
  'method'       => ['required', Rule::in(['manual', 'qr_code', 'pin_code'])],
  'expires_at'   => ['nullable', 'date', 'after:now'],
  'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
  'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
  'notes'        => ['nullable', 'string', 'max:1000'],
  ```
- **messages():** Tiếng Việt

#### Task B3: View — Danh sách buổi điểm danh
- **File:** `resources/views/lecturer/attendance/index.blade.php`
- **Layout:** `<x-app-layout>`
- **Nội dung:**
  - Header: Tên lớp HP + nút "Tạo buổi điểm danh"
  - Bảng `<x-table>`:
    - Cột: STT, Ngày, Tiêu đề, Phương thức, Trạng thái (open/closed), Có mặt/Tổng, Thao tác
    - Phương thức hiển thị bằng `<x-badge>` (manual=gray, pin=blue, qr=green)
    - Thao tác: Xem, Sửa, Xóa (confirm modal)
  - Empty state khi chưa có buổi nào

#### Task B4: View — Form tạo buổi điểm danh
- **File:** `resources/views/lecturer/attendance/create.blade.php`
- **Form fields:**
  - `session_date` — date input (default = today)
  - `title` — text input
  - `method` — select dropdown (Thủ công / Mã PIN / QR Code)
  - `expires_at` — datetime-local input (hiện khi method ≠ manual, dùng Alpine.js x-show)
  - `latitude`, `longitude` — ẩn, auto-fill bằng Geolocation API (optional, dùng Alpine.js)
  - `notes` — textarea
  - Nút: Tạo buổi điểm danh / Hủy

---

### Phase C: Feature F7.2 — Điểm danh thủ công

#### Task C1: AttendanceSessionController — manual, storeManual
- **Method `manual($attendanceSession)`:**
  - Kiểm tra quyền: creator hoặc admin
  - Eager load: session + records + students (qua courseSection enrolled students)
  - Nếu chưa có records: tạo records cho tất cả SV enrolled (absent_unexcused)
  - Trả về view `lecturer/attendance/manual.blade.php`
  - Truyền: `$attendanceSession`, `$students` (list SV + current status)
- **Method `storeManual(Request $request, $attendanceSession)`:**
  - Validate: `records` là array, mỗi item có `student_id` + `status` hợp lệ + optional `note`
  - `DB::transaction()`:
    - Duyệt từng SV → `updateOrCreate` AttendanceRecord
    - Set `checked_at` = now() cho các SV present/late
  - Redirect về `attendance.show` + success message

#### Task C2: View — Điểm danh thủ công
- **File:** `resources/views/lecturer/attendance/manual.blade.php`
- **Giao diện:**
  - Header: Tên buổi + ngày + lớp HP
  - Thống kê nhanh: Có mặt / Muộn / Vắng CP / Vắng KP (cập nhật realtime bằng Alpine.js)
  - Bảng danh sách SV:
    - Cột: STT, MSSV, Họ tên, Trạng thái (radio buttons 4 options), Ghi chú
    - Radio group cho mỗi SV: Present ✅ / Late ⏰ / Excused 📋 / Unexcused ❌
    - Alpine.js x-data để track selection, hiện counter realtime
  - Nút "Đánh dấu tất cả: Có mặt" (quick action)
  - Nút "Lưu điểm danh"

---

### Phase D: Feature F7.3 — Điểm danh bằng mã PIN

#### Task D1: AttendanceSessionController — showCode
- **Method `showCode($attendanceSession)`:**
  - Chỉ cho creator/admin
  - Nếu chưa có pin_code → generate và save
  - Trả về view `lecturer/attendance/code.blade.php`
  - Truyền: `$attendanceSession` (có pin_code, qr_code, expires_at)
- **View `lecturer/attendance/code.blade.php`:**
  - Hiển thị mã PIN lớn (font-size 4rem, font-mono, text-center)
  - Timer countdown đến expires_at (Alpine.js)
  - Hiện trạng thái: Đang mở / Đã hết hạn
  - Nút "Đóng buổi điểm danh" + "Tạo mã mới"
  - **(Nice-to-have):** QR code image bằng `simplesoftwareio/simple-qrcode`

#### Task D2: StudentAttendanceController — checkIn
- **File:** `app/Http/Controllers/StudentAttendanceController.php`
- **Method `checkInForm()`:**
  - View `student/attendance/check-in.blade.php`
  - Form nhập mã PIN (6 ký tự)
- **Method `checkIn(Request $request)`:**
  - Validate: `pin_code` required, string, size:6
  - Tìm AttendanceSession: `where('pin_code', $pinCode)->where('status', 'open')`
  - Kiểm tra:
    1. Session tồn tại và đang open
    2. Chưa hết hạn (expires_at > now HOẶC expires_at IS NULL)
    3. Student enrolled trong courseSection của session (status = 'enrolled')
    4. Student chưa điểm danh buổi này
  - Nếu pass → tạo AttendanceRecord (status = present, checked_at = now)
  - **(Nice-to-have):** Nếu có GPS → tính khoảng cách Haversine, lưu distance_meters
  - Redirect với success/error message

#### Task D3: View — SV nhập PIN
- **File:** `resources/views/student/attendance/check-in.blade.php`
- **Giao diện:**
  - Card centered, form đơn giản
  - Input PIN (6 số, autofocus, pattern="\d{6}")
  - Nút "Điểm danh"
  - Hiện success/error message
  - **(Nice-to-have):** Nút "Quét QR" mở camera

---

### Phase E: Feature F7.4 — Chỉnh sửa điểm danh

#### Task E1: AttendanceSessionController — show, edit, update, destroy
- **Method `show($attendanceSession)`:**
  - Eager load records + students
  - View `lecturer/attendance/show.blade.php`
  - Hiển thị chi tiết buổi + bảng kết quả điểm danh (read-only)
- **Method `edit($attendanceSession)`:**
  - Giống `manual()` nhưng cho phép sửa tất cả trạng thái + ghi chú
  - View `lecturer/attendance/edit.blade.php`
- **Method `update($request, $attendanceSession)`:**
  - Validate session info + records array
  - `DB::transaction()`: update session info + updateOrCreate records
- **Method `destroy($attendanceSession)`:**
  - Xóa session + cascade records
  - Redirect về index + success message

#### Task E2: Views — Show & Edit
- **`lecturer/attendance/show.blade.php`:**
  - Header: Thông tin buổi (ngày, lớp, phương thức, trạng thái)
  - Bảng kết quả: STT, MSSV, Họ tên, Trạng thái (badge color), Giờ điểm danh, Ghi chú
  - Badge colors: present=green, late=yellow, absent_excused=blue, absent_unexcused=red
  - Nút: Sửa, Đóng buổi, Quay lại
- **`lecturer/attendance/edit.blade.php`:**
  - Tương tự form manual nhưng pre-fill dữ liệu cũ
  - Cho phép edit title, notes, và status từng SV

---

### Phase F: Feature F7.5 — SV xem lịch sử điểm danh

#### Task F1: StudentAttendanceController — history, sectionHistory
- **Method `history()`:**
  - Lấy tất cả course_sections SV đang enrolled
  - Mỗi section: tính tổng buổi, số buổi có mặt, tỷ lệ %
  - View `student/attendance/history.blade.php`
- **Method `sectionHistory($courseSection)`:**
  - Kiểm tra SV enrolled trong section
  - Lấy danh sách attendance_records của SV trong section
  - View `student/attendance/section-history.blade.php`

#### Task F2: Views — Lịch sử điểm danh SV
- **`student/attendance/history.blade.php`:**
  - Bảng: Lớp HP, Môn học, Tổng buổi, Có mặt, Muộn, Vắng, Tỷ lệ %
  - Tỷ lệ < 80%: highlight đỏ (cảnh báo)
  - Click vào lớp → xem chi tiết
- **`student/attendance/section-history.blade.php`:**
  - Bảng: STT, Ngày, Tiêu đề buổi, Trạng thái, Giờ điểm danh, Ghi chú
  - Badge colors theo status

---

### Phase G: Feature F7.6 — Thống kê chuyên cần

#### Task G1: AttendanceSessionController — stats
- **Method `stats($courseSection)`:**
  - Kiểm tra quyền (lecturer/admin)
  - Query: Tổng hợp attendance_records theo student_id
  - Tính: tổng buổi, present_count, late_count, absent_excused_count, absent_unexcused_count, attendance_rate %
  - View `lecturer/attendance/stats.blade.php`

#### Task G2: View — Thống kê chuyên cần
- **File:** `resources/views/lecturer/attendance/stats.blade.php`
- **Giao diện:**
  - Header: Tên lớp HP + tổng số buổi
  - Summary cards: Tổng buổi, TB có mặt %, SV vắng nhiều nhất
  - Bảng chi tiết: STT, MSSV, Họ tên, Có mặt, Muộn, Vắng CP, Vắng KP, Tỷ lệ %
  - Highlight đỏ nếu tỷ lệ < 80% hoặc vắng > max_absent_allowed (lấy từ settings)
  - **(Nice-to-have):** Export Excel button

---

### Phase H: Nice-to-have Features

#### Task H1: F7.7 — Điểm danh QR Code
- Khi tạo session method='qr_code':
  - Generate UUID unique → lưu vào qr_code field
  - Dùng `SimpleSoftwareIo\QrCode\Facades\QrCode` tạo QR image
  - QR encode URL: `{APP_URL}/student/attendance/check-in?code={uuid}`
  - SV quét → auto-fill PIN hoặc redirect check-in với code
- **View:** Thêm QR image SVG vào `code.blade.php`

#### Task H2: F7.8 — GPS Validation
- **Khi SV check-in:**
  - Frontend: dùng `navigator.geolocation.getCurrentPosition()` lấy lat/lng
  - Gửi kèm request: `student_latitude`, `student_longitude`
  - Backend: tính khoảng cách Haversine giữa SV và GV
  - Nếu distance > `settings('attendance_geo_radius_m')` (default 200m) → reject
  - Lưu distance_meters vào attendance_records
- **Haversine formula (helper hoặc trong model):**
  ```php
  public static function haversineDistance($lat1, $lng1, $lat2, $lng2): float
  {
      $earthRadius = 6371000; // meters
      $dLat = deg2rad($lat2 - $lat1);
      $dLng = deg2rad($lng2 - $lng1);
      $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
      $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
      return $earthRadius * $c;
  }
  ```

#### Task H3: F7.9 — Cảnh báo vắng quá mức
- Trong stats view: kiểm tra `absent_unexcused_count > settings('max_absent_allowed')`
- Hiện banner cảnh báo cho SV bị vượt ngưỡng
- **(Optional):** Tạo notification tự động

---

### Phase I: Sidebar Integration

#### Task I1: Thêm vào sidebar
- **File:** `resources/views/layouts/sidebar.blade.php` (hoặc nơi sidebar được define)
- **Lecturer section:**
  ```blade
  <x-sidebar-section label="Lớp học">
      <x-sidebar-link route="lecturer.attendance.index" icon="check-circle"
          :params="['courseSection' => request()->route('courseSection')]">
          Điểm danh
      </x-sidebar-link>
  </x-sidebar-section>
  ```
  > Lưu ý: Route `attendance.index` cần `courseSection` param nên sidebar link có thể cần xử lý đặc biệt — dẫn về trang chọn lớp HP trước, hoặc hiển thị dropdown lớp HP.

  **Phương án khuyến nghị:** Tạo thêm route `lecturer.attendance.sections` (GET /lecturer/attendance) → view liệt kê các lớp HP của GV, mỗi lớp có link vào attendance index. Sidebar link trỏ vào route này.

- **Student section:**
  ```blade
  <x-sidebar-link route="student.attendance.history" icon="clipboard-check">
      Điểm danh
  </x-sidebar-link>
  ```

---

## 4. CÂY THƯ MỤC FILE CẦN TẠO/SỬA

### Files mới (tạo):
```
database/migrations/
├── YYYY_MM_DD_000001_create_attendance_sessions_table.php
├── YYYY_MM_DD_000002_create_attendance_records_table.php

app/Models/
├── AttendanceSession.php
├── AttendanceRecord.php

app/Http/Controllers/
├── AttendanceSessionController.php        (Lecturer)
├── StudentAttendanceController.php        (Student)

app/Http/Requests/
├── StoreAttendanceSessionRequest.php
├── UpdateAttendanceSessionRequest.php

resources/views/lecturer/attendance/
├── sections.blade.php                     (Chọn lớp HP)
├── index.blade.php                        (Danh sách buổi điểm danh)
├── create.blade.php                       (Tạo buổi mới)
├── show.blade.php                         (Chi tiết buổi)
├── edit.blade.php                         (Sửa buổi + records)
├── manual.blade.php                       (Điểm danh thủ công)
├── code.blade.php                         (Hiển thị PIN/QR)
├── stats.blade.php                        (Thống kê chuyên cần)

resources/views/student/attendance/
├── check-in.blade.php                     (Nhập PIN)
├── history.blade.php                      (Tổng hợp lịch sử)
├── section-history.blade.php              (Chi tiết theo lớp HP)
```

### Files sửa:
```
app/Models/CourseSection.php               (thêm attendanceSessions relationship)
app/Models/User.php                        (thêm attendanceRecords relationship)
routes/web.php                             (thêm attendance routes)
resources/views/layouts/sidebar.blade.php  (thêm sidebar links)
```

---

## 5. THỨ TỰ TRIỂN KHAI GỢI Ý

```
Step 1: Migrations (A1, A2)                     → chạy migrate
Step 2: Models (A3, A4, A5)                      → relationships + scopes
Step 3: Routes + Sidebar (A6, I1)                → wire up URL structure
Step 4: F7.1 — Tạo buổi (B1, B2, B3, B4)        → Controller + Request + Views
Step 5: F7.2 — Manual (C1, C2)                   → Điểm danh thủ công
Step 6: F7.3 — PIN (D1, D2, D3)                  → PIN generation + student check-in
Step 7: F7.4 — Edit (E1, E2)                     → Show + Edit buổi
Step 8: F7.5 — SV history (F1, F2)               → Student views
Step 9: F7.6 — Stats (G1, G2)                    → Thống kê
Step 10: Nice-to-have (H1, H2, H3)              → QR, GPS, warnings
```

---

## 6. QUY TẮC CODE & BẢO MẬT

### Authorization
- Mọi route lecturer phải kiểm tra: `auth()->user()->id === $courseSection->lecturer_id` hoặc admin
- SV chỉ xem được dữ liệu của chính mình, chỉ check-in vào lớp đã enrolled
- PIN validation: kiểm tra session open + chưa hết hạn + SV enrolled + chưa điểm danh

### Validation
- PIN code: `str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)` (cryptographically secure)
- QR code: UUID v4 (unique, unpredictable)
- GPS coordinates: validate range (-90→90 latitude, -180→180 longitude)
- Session date: required, must be a valid date
- Expires_at: must be in the future nếu có

### Performance
- Eager load relationships: `with('records.student', 'courseSection.subject')`
- Paginate danh sách sessions (15/page)
- Index trên `(course_section_id, session_date)` cho sorting

### Error Handling
- Tất cả write operations trong `DB::transaction()` + `try-catch(Throwable $e)`
- `report($e)` để ghi log
- Redirect back with user-friendly Vietnamese error message
- PIN not found → generic "Mã PIN không hợp lệ hoặc đã hết hạn" (không leak info)

---

## 7. TESTING CHECKLIST

| Test Case | Type | Mô tả |
|-----------|------|--------|
| GV tạo buổi điểm danh manual | Feature | POST store → session created + records pre-populated |
| GV tạo buổi PIN | Feature | POST store → session created + pin_code generated |
| GV điểm danh thủ công | Feature | POST storeManual → records updated correctly |
| SV nhập PIN đúng | Feature | POST checkIn → record created, status = present |
| SV nhập PIN sai | Feature | POST checkIn → error message, no record |
| SV nhập PIN hết hạn | Feature | POST checkIn → error: hết hạn |
| SV không enrolled | Feature | POST checkIn → error: không thuộc lớp HP |
| SV điểm danh trùng | Feature | POST checkIn → error: đã điểm danh |
| GV sửa điểm danh | Feature | PUT update → records changed |
| GV đóng buổi | Feature | POST close → status = closed |
| SV xem lịch sử | Feature | GET history → đúng dữ liệu |
| Thống kê chuyên cần | Feature | GET stats → tính đúng tỷ lệ % |
| Unauthorized access | Feature | non-lecturer → 403 |
| AttendanceSession model | Unit | relationships, scopes, isExpired(), generatePinCode() |
| AttendanceRecord model | Unit | relationships, constants |
