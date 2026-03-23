-- ============================================================
-- EMS - Examination Management System
-- Database Schema v3.1 for MySQL 8+
-- Created:  2026-02-14
-- Revised:  2026-02-15 (v2.0 → v3.0 → v3.1)
--
-- ═══════════════════════════════════════════════════════════
-- REVISION HISTORY
-- ═══════════════════════════════════════════════════════════
--
-- v2.0 (2026-02-15):
--   [1] Question Versioning + Snapshot
--   [2] Tách Exam → ExamPaper + ExamSchedule
--   [3] Question Types → bảng tham chiếu + answer_data JSON
--   [4] Shuffle Audit Trail (questions_order, displayed_options_order)
--
-- v3.0 (2026-02-15):
--   [5] Snapshot Consistency: Loại bỏ FK selected_option_id
--       trong exam_answers, thay bằng selected_snapshot_index
--       (vị trí trong mảng options của snapshot). Ly hôn hoàn
--       toàn với bảng sống, tránh dữ liệu không nhất quán khi
--       GV sửa/xoá options gốc. (#1)
--   [6] RBAC N-N: Thay cột role ENUM cứng trong users bằng
--       bảng roles + user_roles (N-N). Một người có thể vừa là
--       SV vừa là Trợ giảng, Admin khoa có thể dạy. (#2)
--   [7] Structured Schedule: Thay cột schedule VARCHAR bằng
--       bảng class_schedules (day_of_week, start_period,
--       end_period, room). Cho phép query trùng lịch/phòng. (#3)
--   [8] Student GPS Attendance: Thêm student_latitude,
--       student_longitude vào attendance_records để đối chiếu
--       khoảng cách với vị trí giảng viên. (#4)
--   [9] Centralized File Management: Bảng files quản lý tập
--       trung mọi upload. Các bảng khác tham chiếu file_id
--       thay vì lưu path rời rạc. Dễ scale sang S3/MinIO. (#5)
--  [10] JSON Performance: Thêm Generated Columns + Index cho
--       các trường JSON cần thống kê. (#6)
--  [11] Exam Proctoring Logs: Bảng exam_attempt_events ghi
--       log realtime từ client (tab-switch, copy-paste, resize,
--       disconnect) để phát hiện gian lận. (#7)
--
-- v3.1 (2026-02-15) — Review & Polish:
--  [12] Enrollment Status: Thêm status ENUM vào
--       course_section_students (enrolled/dropped/completed).
--  [13] Exam Flag: Thêm is_flagged vào exam_answers —
--       SV đánh dấu câu hỏi để xem lại.
--  [14] Duration Tracking: Thêm duration_seconds vào
--       exam_attempts — thời gian làm bài thực tế.
--  [15] Reserved Keyword Fix: Đổi settings.key → key_name
--       tránh xung đột MySQL reserved keyword.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

-- ============================================================
-- 1. USERS - Người dùng
--    [FIX #6] Bỏ cột role ENUM. Phân quyền qua roles + user_roles.
-- ============================================================
CREATE TABLE `users` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(255)        NOT NULL COMMENT 'Họ và tên',
    `email`             VARCHAR(255)        NOT NULL COMMENT 'Email đăng nhập',
    `password`          VARCHAR(255)        NOT NULL,
    `phone`             VARCHAR(20)         NULL COMMENT 'Số điện thoại',
    `avatar_file_id`    BIGINT UNSIGNED     NULL COMMENT '[FIX #9] FK → files. Ảnh đại diện',
    `student_code`      VARCHAR(20)         NULL COMMENT 'Mã số sinh viên (MSSV)',
    `lecturer_code`     VARCHAR(20)         NULL COMMENT 'Mã giảng viên',
    `class_name`        VARCHAR(100)        NULL COMMENT 'Lớp sinh hoạt (dành cho SV)',
    `department`        VARCHAR(255)        NULL COMMENT 'Khoa / Bộ môn',
    `is_active`         TINYINT(1)          NOT NULL DEFAULT 1 COMMENT '1=Hoạt động, 0=Bị khoá',
    `email_verified_at` TIMESTAMP           NULL,
    `remember_token`    VARCHAR(100)        NULL,
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP           NULL COMMENT 'Soft delete',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    UNIQUE KEY `uk_users_student_code` (`student_code`),
    UNIQUE KEY `uk_users_lecturer_code` (`lecturer_code`),
    INDEX `idx_users_department` (`department`)
    -- FK avatar_file_id được thêm SAU khi bảng files được tạo (cuối file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #6] Bảng người dùng - role tách ra bảng roles/user_roles';

-- ============================================================
-- 1b. ROLES - Vai trò (thay thế ENUM cứng)
--     [FIX #6] Bảng tham chiếu roles
-- ============================================================
CREATE TABLE `roles` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(50)         NOT NULL COMMENT 'Mã vai trò: admin, lecturer, student, teaching_assistant...',
    `name`          VARCHAR(100)        NOT NULL COMMENT 'Tên hiển thị: Quản trị viên, Giảng viên...',
    `description`   TEXT                NULL,
    `is_active`     TINYINT(1)          NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #6] Bảng vai trò - thay thế ENUM, hỗ trợ 1 user nhiều role';

-- ============================================================
-- 1c. USER_ROLES - Gán vai trò cho người dùng (N-N)
--     [FIX #6] 1 người có thể vừa là SV vừa là Trợ giảng
-- ============================================================
CREATE TABLE `user_roles` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       BIGINT UNSIGNED     NOT NULL,
    `role_id`       BIGINT UNSIGNED     NOT NULL,
    `assigned_at`   TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm gán',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_roles` (`user_id`, `role_id`),
    INDEX `idx_ur_role` (`role_id`),

    CONSTRAINT `fk_ur_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ur_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #6] Pivot N-N: user ↔ role. 1 user có thể có nhiều role';

-- ============================================================
-- 2. SEMESTERS - Học kỳ
-- ============================================================
CREATE TABLE `semesters` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)        NOT NULL COMMENT 'VD: HK1 2025-2026',
    `year`          SMALLINT UNSIGNED   NOT NULL COMMENT 'Năm học bắt đầu, VD: 2025',
    `term`          TINYINT UNSIGNED    NOT NULL COMMENT '1=HK1, 2=HK2, 3=HK Hè',
    `start_date`    DATE                NOT NULL,
    `end_date`      DATE                NOT NULL,
    `is_current`    TINYINT(1)          NOT NULL DEFAULT 0 COMMENT 'Đánh dấu học kỳ hiện tại',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_semesters_year_term` (`year`, `term`),
    INDEX `idx_semesters_is_current` (`is_current`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bảng học kỳ';

-- ============================================================
-- 3. SUBJECTS - Môn học
-- ============================================================
CREATE TABLE `subjects` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(20)         NOT NULL COMMENT 'Mã môn học, VD: CS101',
    `name`          VARCHAR(255)        NOT NULL COMMENT 'Tên môn học',
    `credits`       TINYINT UNSIGNED    NOT NULL DEFAULT 3 COMMENT 'Số tín chỉ',
    `department`    VARCHAR(255)        NULL COMMENT 'Khoa quản lý',
    `description`   TEXT                NULL,
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP           NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_subjects_code` (`code`),
    INDEX `idx_subjects_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bảng môn học';

-- ============================================================
-- 4. CHAPTERS - Chương (thuộc môn học)
-- ============================================================
CREATE TABLE `chapters` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `subject_id`    BIGINT UNSIGNED     NOT NULL,
    `name`          VARCHAR(255)        NOT NULL COMMENT 'Tên chương',
    `order`         INT UNSIGNED        NOT NULL DEFAULT 0 COMMENT 'Thứ tự sắp xếp',
    `description`   TEXT                NULL,
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_chapters_subject` (`subject_id`),
    INDEX `idx_chapters_order` (`subject_id`, `order`),

    CONSTRAINT `fk_chapters_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bảng chương của môn học - dùng để phân loại câu hỏi và tài liệu';

-- ============================================================
-- 5. COURSE_SECTIONS - Lớp học phần
-- ============================================================
CREATE TABLE `course_sections` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(50)         NOT NULL COMMENT 'Mã lớp học phần, VD: CS101-01-HK1-2526',
    `subject_id`    BIGINT UNSIGNED     NOT NULL,
    `semester_id`   BIGINT UNSIGNED     NOT NULL,
    `lecturer_id`   BIGINT UNSIGNED     NOT NULL COMMENT 'Giảng viên phụ trách',
    `max_students`  INT UNSIGNED        NOT NULL DEFAULT 50 COMMENT 'Sĩ số tối đa',
    `status`        ENUM('active','archived','cancelled') NOT NULL DEFAULT 'active',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_course_sections_code` (`code`),
    INDEX `idx_cs_subject` (`subject_id`),
    INDEX `idx_cs_semester` (`semester_id`),
    INDEX `idx_cs_lecturer` (`lecturer_id`),
    INDEX `idx_cs_status` (`status`),

    CONSTRAINT `fk_cs_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_semester`
        FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_lecturer`
        FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #7] Bảng lớp học phần - bỏ schedule/room VARCHAR, dùng class_schedules thay thế';

-- ============================================================
-- 5b. CLASS_SCHEDULES - Thời khóa biểu chi tiết
--     [FIX #7] Thay thế cột schedule VARCHAR. Cho phép query
--     trùng lịch, trùng phòng chính xác bằng SQL thuần.
-- ============================================================
CREATE TABLE `class_schedules` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_section_id` BIGINT UNSIGNED     NOT NULL,
    `day_of_week`       TINYINT UNSIGNED    NOT NULL COMMENT '2=Thứ Hai, 3=Thứ Ba, ..., 8=Chủ Nhật',
    `start_period`      TINYINT UNSIGNED    NOT NULL COMMENT 'Tiết bắt đầu (1-16)',
    `end_period`        TINYINT UNSIGNED    NOT NULL COMMENT 'Tiết kết thúc (1-16)',
    `room`              VARCHAR(50)         NULL COMMENT 'Phòng học',
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_clsch_section` (`course_section_id`),
    -- Index phục vụ query: "phòng X có trống vào Thứ 2 tiết 3 không?"
    INDEX `idx_clsch_room_time` (`room`, `day_of_week`, `start_period`, `end_period`),
    INDEX `idx_clsch_day` (`day_of_week`, `start_period`),

    CONSTRAINT `fk_clsch_section`
        FOREIGN KEY (`course_section_id`) REFERENCES `course_sections`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    -- Đảm bảo start <= end
    CONSTRAINT `chk_clsch_period` CHECK (`start_period` <= `end_period`),
    CONSTRAINT `chk_clsch_day` CHECK (`day_of_week` BETWEEN 2 AND 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #7] Thời khóa biểu cấu trúc - thay VARCHAR, cho phép query trùng lịch/phòng chính xác';

-- ============================================================
-- 6. COURSE_SECTION_STUDENTS - Sinh viên đăng ký lớp học phần
-- ============================================================
CREATE TABLE `course_section_students` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_section_id` BIGINT UNSIGNED     NOT NULL,
    `student_id`        BIGINT UNSIGNED     NOT NULL,
    `status`            ENUM('enrolled','dropped','completed')
                                            NOT NULL DEFAULT 'enrolled'
                                            COMMENT 'enrolled=đang học, dropped=đã rút, completed=hoàn thành',
    `enrolled_at`       TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày đăng ký',
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_css_section_student` (`course_section_id`, `student_id`),
    INDEX `idx_css_student` (`student_id`),
    INDEX `idx_css_status` (`status`),

    CONSTRAINT `fk_css_section`
        FOREIGN KEY (`course_section_id`) REFERENCES `course_sections`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_css_student`
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bảng trung gian N-N giữa sinh viên và lớp học phần. Có status để quản lý rút môn/hoàn thành.';

-- ============================================================
-- 1. DIFFICULTIES - Mức độ tư duy (Bloom Taxonomy)
-- ============================================================
CREATE TABLE `difficulties` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(50)         NOT NULL UNIQUE, -- remember, understand, apply...
    `name`          VARCHAR(100)        NOT NULL,        -- Nhận biết, Thông hiểu...
    `score_weight`  DECIMAL(3,2)        NOT NULL DEFAULT 1.0, -- Hệ số điểm
    `display_order` TINYINT UNSIGNED    NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. QUESTION_TYPES - Loại câu hỏi
-- ============================================================
CREATE TABLE `question_types` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`          VARCHAR(50)         NOT NULL UNIQUE,
    `name`          VARCHAR(100)        NOT NULL,
    `answer_schema` JSON                NULL COMMENT 'Mô tả cấu trúc đáp án (Ví dụ: số lượng option tối thiểu)',
    `is_auto_grade` TINYINT(1)          NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. QUESTIONS - Ngân hàng câu hỏi
-- ============================================================
CREATE TABLE `questions` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `subject_id`        BIGINT UNSIGNED     NOT NULL,
    `chapter_id`        BIGINT UNSIGNED     NULL,
    `question_type_id`  BIGINT UNSIGNED     NOT NULL,
    `difficulty_id`     BIGINT UNSIGNED     NOT NULL, -- [FIX] Dùng FK thay vì ENUM
    `created_by`        BIGINT UNSIGNED     NOT NULL,
    `content`           TEXT                NOT NULL,
    `image_file_id`     BIGINT UNSIGNED     NULL,
    `explanation`       TEXT                NULL,
    `correct_option_id` BIGINT UNSIGNED     NULL COMMENT '[FIX] ID của đáp án đúng duy nhất - Chấm bài cực nhanh',
    `answer_data`       JSON                NULL COMMENT 'Dùng cho loại câu phức tạp (matching, fill_blank)',
    `status`            ENUM('draft','approved','hidden') NOT NULL DEFAULT 'draft',
    `version`           INT UNSIGNED        NOT NULL DEFAULT 1,
    
    -- Các cột phục vụ tính tỉ lệ đúng
    `total_attempts`    INT UNSIGNED        NOT NULL DEFAULT 0,
    `total_correct`     INT UNSIGNED        NOT NULL DEFAULT 0,
    `correct_rate`      DECIMAL(5,2)        AS ((total_correct / NULLIF(total_attempts, 0)) * 100) VIRTUAL, 

    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP           NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_q_subject_chapter` (`subject_id`, `chapter_id`),
    INDEX `idx_q_type_diff` (`question_type_id`, `difficulty_id`),
    INDEX `idx_q_status` (`status`),
    
    CONSTRAINT `fk_q_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
    CONSTRAINT `fk_q_difficulty` FOREIGN KEY (`difficulty_id`) REFERENCES `difficulties` (`id`),
    CONSTRAINT `fk_q_type` FOREIGN KEY (`question_type_id`) REFERENCES `question_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. QUESTION_OPTIONS - Các lựa chọn đáp án
-- ============================================================
CREATE TABLE `question_options` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `question_id`   BIGINT UNSIGNED     NOT NULL,
    `label`         CHAR(1)             NULL COMMENT 'A, B, C, D',
    `content`       TEXT                NOT NULL,
    `image_file_id` BIGINT UNSIGNED     NULL,
    `is_correct`    TINYINT(1)          NOT NULL DEFAULT 0 COMMENT 'Vẫn giữ để dự phòng/hiển thị UI Admin',
    `display_order` TINYINT UNSIGNED    NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_qo_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [QUAN TRỌNG] Thêm FK cho đáp án đúng sau khi đã có bảng options
ALTER TABLE `questions` 
ADD CONSTRAINT `fk_q_correct_option` 
FOREIGN KEY (`correct_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL;

-- ============================================================
-- 5. TAGS & MAPPING - Quản lý nhãn (Chuẩn hóa)
-- ============================================================
CREATE TABLE `tags` (
    `id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100)    NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `question_tag_map` (
    `question_id` BIGINT UNSIGNED NOT NULL,
    `tag_id`      BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`question_id`, `tag_id`),
    CONSTRAINT `fk_map_q` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_map_t` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ============================================================
-- 11. EXAM_PAPERS - Đề thi gốc (tách khỏi lớp học phần)
--     [FIX #2] Đề thi là thực thể độc lập, gắn với MÔN HỌC
--     chứ không gắn với lớp. Có thể tái sử dụng cho N lớp.
-- ============================================================
CREATE TABLE `exam_papers` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `subject_id`        BIGINT UNSIGNED     NOT NULL COMMENT 'Đề thuộc môn nào (không gắn lớp)',
    `created_by`        BIGINT UNSIGNED     NOT NULL COMMENT 'Giảng viên tạo đề',
    `title`             VARCHAR(255)        NOT NULL COMMENT 'Tiêu đề đề thi',
    `description`       TEXT                NULL,
    `total_questions`   INT UNSIGNED        NOT NULL DEFAULT 0,
    `total_marks`       DECIMAL(5,2)        NOT NULL DEFAULT 10.00 COMMENT 'Tổng điểm (thang 10)',
    `shuffle_questions` TINYINT(1)          NOT NULL DEFAULT 1 COMMENT 'Trộn thứ tự câu hỏi',
    `shuffle_options`   TINYINT(1)          NOT NULL DEFAULT 1 COMMENT 'Trộn thứ tự đáp án',
    `show_result`       ENUM('immediately','after_end','never')
                                            NOT NULL DEFAULT 'after_end'
                                            COMMENT 'Khi nào hiển thị kết quả cho SV',
    `show_answer`       TINYINT(1)          NOT NULL DEFAULT 0 COMMENT 'Cho SV xem đáp án đúng sau khi thi',
    `allow_review`      TINYINT(1)          NOT NULL DEFAULT 1 COMMENT 'Cho phép quay lại câu trước khi làm bài',
    `mode`              ENUM('official','practice') NOT NULL DEFAULT 'official'
                                            COMMENT 'Chế độ: chính thức / luyện tập',
    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'
                                            COMMENT 'draft=đang soạn, published=đã duyệt (snapshot đã tạo), archived=lưu trữ',
    `published_at`      DATETIME            NULL COMMENT 'Thời điểm duyệt đề (snapshot được tạo lúc này)',
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP           NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_ep_subject` (`subject_id`),
    INDEX `idx_ep_created_by` (`created_by`),
    INDEX `idx_ep_status` (`status`),
    INDEX `idx_ep_mode` (`mode`),

    CONSTRAINT `fk_ep_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ep_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #2] Đề thi gốc - độc lập với lớp học phần, có thể gán cho nhiều lớp qua exam_schedules';

-- ============================================================
-- 12. EXAM_PAPER_QUESTIONS - Câu hỏi trong đề thi + SNAPSHOT
--     [FIX #1] content_snapshot JSON lưu trữ nội dung đóng băng
--     của câu hỏi + đáp án tại thời điểm publish đề thi.
-- ============================================================
CREATE TABLE `exam_paper_questions` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `exam_paper_id`     BIGINT UNSIGNED     NOT NULL,
    `question_id`       BIGINT UNSIGNED     NOT NULL COMMENT 'FK truy nguyên về câu hỏi gốc (traceability)',
    `order`             INT UNSIGNED        NOT NULL DEFAULT 0 COMMENT 'Thứ tự câu hỏi gốc trong đề',
    `marks`             DECIMAL(4,2)        NOT NULL DEFAULT 0.25 COMMENT 'Điểm cho câu này',

    -- ═══════════════════════════════════════════════════════════
    -- [FIX #1] SNAPSHOT - Nội dung đóng băng tại thời điểm PUBLISH
    -- Được populate bởi application khi exam_papers.status → published
    -- Format JSON:
    -- {
    --   "question_version": 3,
    --   "question_type_code": "multiple_choice",
    --   "content": "Nội dung câu hỏi...",
    --   "image": "path/to/image.png",
    --   "explanation": "Giải thích...",
    --   "difficulty": "understand",
    --   "options": [
    --     {"id": 101, "label": "A", "content": "...", "is_correct": false},
    --     {"id": 102, "label": "B", "content": "...", "is_correct": true},
    --     {"id": 103, "label": "C", "content": "...", "is_correct": false},
    --     {"id": 104, "label": "D", "content": "...", "is_correct": false}
    --   ],
    --   "answer_data": null,
    --   "snapshotted_at": "2026-02-15 10:30:00"
    -- }
    -- ═══════════════════════════════════════════════════════════
    `content_snapshot`  JSON                NULL COMMENT '[FIX #1] Snapshot đóng băng câu hỏi + đáp án tại thời điểm publish',

    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_epq_paper_question` (`exam_paper_id`, `question_id`),
    INDEX `idx_epq_question` (`question_id`),

    CONSTRAINT `fk_epq_paper`
        FOREIGN KEY (`exam_paper_id`) REFERENCES `exam_papers`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_epq_question`
        FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #1] Câu hỏi trong đề thi + snapshot nội dung. Snapshot tạo 1 lần khi publish, không thay đổi.';

-- ============================================================
-- 13. EXAM_SCHEDULES - Lịch thi (gán đề thi cho lớp học phần)
--     [FIX #2] Bảng cầu nối giữa ExamPaper và CourseSection.
--     Cho phép 1 đề thi gán cho nhiều lớp với thời gian khác nhau.
-- ============================================================
CREATE TABLE `exam_schedules` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `exam_paper_id`     BIGINT UNSIGNED     NOT NULL COMMENT 'Đề thi nào',
    `course_section_id` BIGINT UNSIGNED     NOT NULL COMMENT 'Lớp học phần nào',
    `scheduled_by`      BIGINT UNSIGNED     NOT NULL COMMENT 'Giảng viên lên lịch',
    `duration_minutes`  INT UNSIGNED        NOT NULL COMMENT 'Thời gian làm bài (phút) - có thể khác nhau mỗi lớp',
    `max_attempts`      TINYINT UNSIGNED    NOT NULL DEFAULT 1 COMMENT 'Số lần thi tối đa',
    `start_time`        DATETIME            NOT NULL COMMENT 'Thời gian mở thi',
    `end_time`          DATETIME            NOT NULL COMMENT 'Thời gian đóng thi',
    `pass_score`        DECIMAL(5,2)        NULL COMMENT 'Điểm đạt (NULL = không áp dụng)',
    `password`          VARCHAR(100)        NULL COMMENT 'Mật khẩu vào phòng thi (tuỳ chọn)',
    `status`            ENUM('scheduled','active','closed','cancelled')
                                            NOT NULL DEFAULT 'scheduled'
                                            COMMENT 'scheduled=chờ, active=đang mở, closed=đã đóng, cancelled=huỷ',
    `notes`             TEXT                NULL COMMENT 'Ghi chú cho lịch thi này',
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    -- Cho phép 1 đề gán nhiều lớp, nhưng 1 đề chỉ gán 1 lần cho 1 lớp
    UNIQUE KEY `uk_es_paper_section` (`exam_paper_id`, `course_section_id`),
    INDEX `idx_es_paper` (`exam_paper_id`),
    INDEX `idx_es_section` (`course_section_id`),
    INDEX `idx_es_scheduled_by` (`scheduled_by`),
    INDEX `idx_es_status` (`status`),
    INDEX `idx_es_time` (`start_time`, `end_time`),

    CONSTRAINT `fk_es_paper`
        FOREIGN KEY (`exam_paper_id`) REFERENCES `exam_papers`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_es_section`
        FOREIGN KEY (`course_section_id`) REFERENCES `course_sections`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_es_scheduled_by`
        FOREIGN KEY (`scheduled_by`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #2] Lịch thi - gán 1 đề cho N lớp with different time windows. SV thi qua bảng này.';

-- ============================================================
-- 14. EXAM_ATTEMPTS - Lượt thi của sinh viên
--     [FIX #4] Thêm questions_order JSON để tái hiện thứ tự
--     câu hỏi đã hiển thị cho sinh viên.
-- ============================================================
CREATE TABLE `exam_attempts` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `exam_schedule_id`  BIGINT UNSIGNED     NOT NULL COMMENT '[FIX #2] FK tới lịch thi, không tới đề gốc',
    `student_id`        BIGINT UNSIGNED     NOT NULL,
    `attempt_number`    TINYINT UNSIGNED    NOT NULL DEFAULT 1 COMMENT 'Lần thi thứ mấy',
    `started_at`        DATETIME            NOT NULL COMMENT 'Thời điểm bắt đầu làm bài',
    `submitted_at`      DATETIME            NULL COMMENT 'Thời điểm nộp bài',
    `duration_seconds`  INT UNSIGNED        NULL COMMENT 'Thời gian làm bài thực tế (giây), tính bởi server khi nộp bài',
    `score`             DECIMAL(5,2)        NULL COMMENT 'Điểm đạt được',
    `total_correct`     INT UNSIGNED        NULL COMMENT 'Số câu đúng',
    `total_answered`    INT UNSIGNED        NULL COMMENT 'Số câu đã trả lời',
    `is_passed`         TINYINT(1)          NULL COMMENT '1=Đạt, 0=Không đạt, NULL=Chưa chấm',
    `ip_address`        VARCHAR(45)         NULL COMMENT 'Địa chỉ IP khi thi',
    `user_agent`        VARCHAR(500)        NULL COMMENT 'Trình duyệt / thiết bị',

    -- ═══════════════════════════════════════════════════════════
    -- [FIX #4] SHUFFLE AUDIT TRAIL
    -- Lưu trữ thứ tự hiển thị câu hỏi cho lượt thi này.
    -- Dùng để tái hiện chính xác giao diện SV đã thấy.
    -- VD: [45, 12, 38, 7, 23] = câu EPQ#45 hiện đầu, EPQ#12 thứ 2...
    -- ═══════════════════════════════════════════════════════════
    `questions_order`   JSON                NULL COMMENT '[FIX #4] Mảng JSON thứ tự exam_paper_question IDs đã hiển thị',

    `status`            ENUM('in_progress','submitted','auto_submitted','graded')
                                            NOT NULL DEFAULT 'in_progress',
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ea_schedule_student_attempt` (`exam_schedule_id`, `student_id`, `attempt_number`),
    INDEX `idx_ea_student` (`student_id`),
    INDEX `idx_ea_status` (`status`),
    INDEX `idx_ea_score` (`exam_schedule_id`, `score`),

    CONSTRAINT `fk_ea_schedule`
        FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedules`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ea_student`
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #2,#4] Lượt thi - liên kết tới exam_schedules, lưu thứ tự shuffle';

-- ============================================================
-- 15. EXAM_ANSWERS - Câu trả lời chi tiết
--     [FIX #1] Tham chiếu exam_paper_questions (có snapshot)
--              thay vì tham chiếu trực tiếp questions.
--     [FIX #3] Hỗ trợ answer_text + answer_data cho đa loại.
--     [FIX #4] Thêm displayed_options_order JSON.
-- ============================================================
CREATE TABLE `exam_answers` (
    `id`                        BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `exam_attempt_id`           BIGINT UNSIGNED     NOT NULL,
    `exam_paper_question_id`    BIGINT UNSIGNED     NOT NULL COMMENT '[FIX #1] FK tới câu hỏi trong đề (có snapshot), không tới câu hỏi gốc',

    -- ═══════════════════════════════════════════════════════════
    -- [FIX #5] SNAPSHOT CONSISTENCY
    -- KHÔNG dùng selected_option_id FK nữa. Lưu vị trí/nhãn
    -- trong snapshot thay vì ID sống. "Ly hôn cho trót."
    --
    -- selected_snapshot_index: vị trí trong mảng options của
    --   content_snapshot (0=option đầu tiên, 1=thứ 2...)
    -- selected_snapshot_label: nhãn hiển thị cho SV ("A","B"...)
    --   tính theo displayed_options_order, KHÔNG phải label gốc.
    -- ═══════════════════════════════════════════════════════════
    `selected_snapshot_index`   TINYINT UNSIGNED    NULL COMMENT '[FIX #5] Index trong mảng options của snapshot (0-based). NULL=chưa trả lời hoặc không phải MCQ',
    `selected_snapshot_label`   CHAR(1)             NULL COMMENT '[FIX #5] Nhãn hiển thị SV đã thấy: A, B, C, D (theo displayed_options_order)',

    `is_correct`                TINYINT(1)          NULL COMMENT '1=Đúng, 0=Sai, NULL=Chưa chấm hoặc câu tự luận',
    `is_flagged`                TINYINT(1)          NOT NULL DEFAULT 0 COMMENT 'SV đánh dấu câu hỏi để xem lại khi làm bài',
    `answer_text`               TEXT                NULL COMMENT '[FIX #3] Câu trả lời dạng text cho fill_blank/essay',
    `answer_data`               JSON                NULL COMMENT '[FIX #3] Dữ liệu trả lời linh hoạt cho matching, ordering...',
    `answered_at`               DATETIME            NULL COMMENT 'Thời điểm trả lời / thay đổi cuối',

    -- ═══════════════════════════════════════════════════════════
    -- [FIX #4] SHUFFLE AUDIT TRAIL cho đáp án
    -- Lưu thứ tự hiển thị các option snapshot cho câu hỏi này.
    -- Đây là INDEX trong mảng snapshot options, KHÔNG phải option ID.
    -- VD: [2, 0, 3, 1] = snapshot_options[2] hiện ở vị trí A,
    --     snapshot_options[0] ở B, snapshot_options[3] ở C...
    -- ═══════════════════════════════════════════════════════════
    `displayed_options_order`   JSON                NULL COMMENT '[FIX #4+#5] Mảng JSON snapshot option indexes theo thứ tự hiển thị. Hoàn toàn tự chứa, không FK.',

    `created_at`                TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_eans_attempt_epq` (`exam_attempt_id`, `exam_paper_question_id`),
    INDEX `idx_eans_epq` (`exam_paper_question_id`),
    INDEX `idx_eans_correct` (`exam_paper_question_id`, `is_correct`),

    CONSTRAINT `fk_eans_attempt`
        FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_eans_epq`
        FOREIGN KEY (`exam_paper_question_id`) REFERENCES `exam_paper_questions`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
    -- KHÔNG CÓ FK tới question_options. Snapshot là nguồn sự thật duy nhất.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #1,#3,#4,#5] Câu trả lời - hoàn toàn tự chứa, không FK tới bảng sống. Snapshot is the single source of truth.';

-- ============================================================
-- 16. ATTENDANCE_SESSIONS - Buổi điểm danh
-- ============================================================
CREATE TABLE `attendance_sessions` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_section_id` BIGINT UNSIGNED     NOT NULL,
    `created_by`        BIGINT UNSIGNED     NOT NULL COMMENT 'Giảng viên tạo buổi điểm danh',
    `session_date`      DATE                NOT NULL COMMENT 'Ngày điểm danh',
    `title`             VARCHAR(255)        NULL COMMENT 'VD: Buổi 1 - Giới thiệu môn học',
    `method`            ENUM('manual','qr_code','pin_code')
                                            NOT NULL DEFAULT 'manual'
                                            COMMENT 'Phương thức điểm danh',
    `qr_code`           VARCHAR(255)        NULL COMMENT 'Mã QR (nếu dùng QR)',
    `pin_code`          VARCHAR(10)         NULL COMMENT 'Mã PIN (nếu dùng PIN)',
    `expires_at`        DATETIME            NULL COMMENT 'Thời hạn hết hiệu lực điểm danh',
    `latitude`          DECIMAL(10,8)       NULL COMMENT 'Vĩ độ GPS (tuỳ chọn)',
    `longitude`         DECIMAL(11,8)       NULL COMMENT 'Kinh độ GPS (tuỳ chọn)',
    `notes`             TEXT                NULL,
    `status`            ENUM('open','closed') NOT NULL DEFAULT 'open',
    `created_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_as_cs` (`course_section_id`),
    INDEX `idx_as_created_by` (`created_by`),
    INDEX `idx_as_date` (`session_date`),
    INDEX `idx_as_status` (`status`),

    CONSTRAINT `fk_as_cs`
        FOREIGN KEY (`course_section_id`) REFERENCES `course_sections`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_as_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mỗi buổi điểm danh của lớp học phần';

-- ============================================================
-- 17. ATTENDANCE_RECORDS - Bản ghi điểm danh từng SV
-- ============================================================
CREATE TABLE `attendance_records` (
    `id`                    BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `attendance_session_id` BIGINT UNSIGNED     NOT NULL,
    `student_id`            BIGINT UNSIGNED     NOT NULL,
    `status`                ENUM('present','absent_excused','absent_unexcused','late')
                                                NOT NULL DEFAULT 'present'
                                                COMMENT 'Có mặt / Vắng CP / Vắng KP / Đi muộn',
    `checked_at`            DATETIME            NULL COMMENT 'Thời điểm điểm danh',

    -- ═══════════════════════════════════════════════════════════
    -- [FIX #8] STUDENT GPS - Vị trí SV lúc điểm danh
    -- Server so sánh với latitude/longitude trong attendance_sessions
    -- Nếu khoảng cách > ngưỡng (VD: 100m) → reject hoặc flag.
    -- ═══════════════════════════════════════════════════════════
    `student_latitude`      DECIMAL(10,8)       NULL COMMENT '[FIX #8] Vĩ độ GPS của SV khi điểm danh',
    `student_longitude`     DECIMAL(11,8)       NULL COMMENT '[FIX #8] Kinh độ GPS của SV khi điểm danh',
    `distance_meters`       DECIMAL(10,2)       NULL COMMENT '[FIX #8] Khoảng cách (m) giữa SV và GV, tính bởi server',

    `note`                  VARCHAR(255)        NULL COMMENT 'Ghi chú (lý do vắng...)',
    `created_at`            TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ar_session_student` (`attendance_session_id`, `student_id`),
    INDEX `idx_ar_student` (`student_id`),
    INDEX `idx_ar_status` (`status`),

    CONSTRAINT `fk_ar_session`
        FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ar_student`
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #8] Trạng thái điểm danh + vị trí GPS sinh viên để chống gian lận từ xa';

-- ============================================================
-- 18. SYLLABI - Đề cương môn học
-- ============================================================
CREATE TABLE `syllabi` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `subject_id`    BIGINT UNSIGNED     NOT NULL,
    `created_by`    BIGINT UNSIGNED     NOT NULL COMMENT 'Giảng viên tạo đề cương',
    `title`         VARCHAR(255)        NOT NULL,
    `content`       LONGTEXT            NULL COMMENT 'Nội dung đề cương (HTML/Markdown)',
    `file_id`       BIGINT UNSIGNED     NULL COMMENT '[FIX #9] FK → files. File đề cương đính kèm',
    `version`       VARCHAR(20)         NULL COMMENT 'Phiên bản, VD: 2025.1',
    `status`        ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_syllabi_subject` (`subject_id`),
    INDEX `idx_syllabi_created_by` (`created_by`),
    INDEX `idx_syllabi_status` (`status`),
    INDEX `idx_syllabi_file` (`file_id`),

    CONSTRAINT `fk_syllabi_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_syllabi_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_syllabi_file`
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #9] Đề cương môn học - file tham chiếu qua files table';

-- ============================================================
-- 19. DOCUMENTS - Tài liệu học tập
-- ============================================================
CREATE TABLE `documents` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `subject_id`    BIGINT UNSIGNED     NOT NULL,
    `chapter_id`    BIGINT UNSIGNED     NULL COMMENT 'Thuộc chương nào (tuỳ chọn)',
    `uploaded_by`   BIGINT UNSIGNED     NOT NULL COMMENT 'Người upload',
    `file_id`       BIGINT UNSIGNED     NOT NULL COMMENT '[FIX #9] FK → files. File tài liệu',
    `title`         VARCHAR(255)        NOT NULL,
    `description`   TEXT                NULL,
    `download_count` INT UNSIGNED       NOT NULL DEFAULT 0 COMMENT 'Số lượt tải',
    `status`        ENUM('active','hidden') NOT NULL DEFAULT 'active',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP           NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_docs_subject` (`subject_id`),
    INDEX `idx_docs_chapter` (`chapter_id`),
    INDEX `idx_docs_uploaded_by` (`uploaded_by`),
    INDEX `idx_docs_file` (`file_id`),
    INDEX `idx_docs_status` (`status`),

    CONSTRAINT `fk_docs_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_docs_chapter`
        FOREIGN KEY (`chapter_id`) REFERENCES `chapters`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_docs_uploaded_by`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_docs_file`
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #9] Tài liệu học tập - file quản lý tập trung qua files table';

-- ============================================================
-- 19b. FILES - Quản lý file tập trung
--      [FIX #9] Mọi upload đều đi qua bảng này.
--      Các bảng khác tham chiếu file_id thay vì lưu path rời rạc.
--      Khi cần migrate sang S3/MinIO, chỉ cần sửa disk + path.
-- ============================================================
CREATE TABLE `files` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `uploaded_by`   BIGINT UNSIGNED     NULL COMMENT 'Người upload (NULL=hệ thống)',
    `disk`          VARCHAR(20)         NOT NULL DEFAULT 'local' COMMENT 'Storage disk: local, s3, minio...',
    `path`          VARCHAR(500)        NOT NULL COMMENT 'Đường dẫn trên disk',
    `original_name` VARCHAR(255)        NOT NULL COMMENT 'Tên file gốc người dùng upload',
    `mime_type`     VARCHAR(100)        NOT NULL COMMENT 'MIME type: application/pdf, image/png...',
    `extension`     VARCHAR(20)         NOT NULL COMMENT 'Phần mở rộng: pdf, docx, png...',
    `size`          BIGINT UNSIGNED     NOT NULL DEFAULT 0 COMMENT 'Dung lượng (bytes)',
    `checksum`      VARCHAR(64)         NULL COMMENT 'SHA-256 hash để phát hiện file trùng',
    `is_public`     TINYINT(1)          NOT NULL DEFAULT 0 COMMENT '1=Public URL, 0=Cần signed URL',
    `used_by_type`  VARCHAR(100)        NULL COMMENT 'Polymorphic: App\\Models\\Document, App\\Models\\Question...',
    `used_by_id`    BIGINT UNSIGNED     NULL COMMENT 'ID bản ghi sử dụng file này',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP           NULL COMMENT 'Soft delete - chờ dọn rác',

    PRIMARY KEY (`id`),
    INDEX `idx_files_uploaded_by` (`uploaded_by`),
    INDEX `idx_files_disk` (`disk`),
    INDEX `idx_files_mime` (`mime_type`),
    INDEX `idx_files_checksum` (`checksum`),
    INDEX `idx_files_used_by` (`used_by_type`, `used_by_id`),
    INDEX `idx_files_orphan` (`used_by_type`, `deleted_at`),

    CONSTRAINT `fk_files_uploaded_by`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #9] Quản lý file tập trung. Dọn orphan dễ dàng. Scale sang S3/MinIO chỉ cần đổi disk.';

-- ============================================================
-- 20. NOTIFICATIONS - Thông báo
-- ============================================================
CREATE TABLE `notifications` (
    `id`            CHAR(36)            NOT NULL COMMENT 'UUID',
    `user_id`       BIGINT UNSIGNED     NOT NULL COMMENT 'Người nhận thông báo',
    `type`          VARCHAR(100)        NOT NULL COMMENT 'Loại: exam_created, score_published, document_uploaded...',
    `title`         VARCHAR(255)        NOT NULL,
    `message`       TEXT                NOT NULL,
    `data`          JSON                NULL COMMENT 'Dữ liệu bổ sung (link, ID liên quan...)',
    `read_at`       DATETIME            NULL COMMENT 'Thời điểm đã đọc (NULL=chưa đọc)',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_user_read` (`user_id`, `read_at`),
    INDEX `idx_notif_type` (`type`),
    INDEX `idx_notif_created` (`created_at`),

    CONSTRAINT `fk_notif_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Thông báo in-app cho người dùng';

-- ============================================================
-- 21. ACTIVITY_LOGS - Nhật ký hoạt động (Audit Trail)
-- ============================================================
CREATE TABLE `activity_logs` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       BIGINT UNSIGNED     NULL COMMENT 'Người thực hiện (NULL=hệ thống)',
    `action`        VARCHAR(100)        NOT NULL COMMENT 'VD: created, updated, deleted, login, logout',
    `model_type`    VARCHAR(255)        NULL COMMENT 'Tên model: App\\Models\\ExamPaper',
    `model_id`      BIGINT UNSIGNED     NULL COMMENT 'ID của bản ghi bị tác động',
    `description`   TEXT                NULL COMMENT 'Mô tả hành động',
    `old_values`    JSON                NULL COMMENT 'Giá trị cũ (trước khi thay đổi)',
    `new_values`    JSON                NULL COMMENT 'Giá trị mới (sau khi thay đổi)',
    `ip_address`    VARCHAR(45)         NULL,
    `user_agent`    VARCHAR(500)        NULL,
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_al_user` (`user_id`),
    INDEX `idx_al_action` (`action`),
    INDEX `idx_al_model` (`model_type`, `model_id`),
    INDEX `idx_al_created` (`created_at`),

    CONSTRAINT `fk_al_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Nhật ký hoạt động - ghi lại mọi thao tác quan trọng để truy vết';

-- ============================================================
-- 22. EXAM_ATTEMPT_EVENTS - Log sự kiện trong khi thi (Proctoring)
--     [FIX #11] Ghi log realtime từ client-side:
--     rời tab, resize, mất kết nối, copy/paste, DevTools...
-- ============================================================
CREATE TABLE `exam_attempt_events` (
    `id`                BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `exam_attempt_id`   BIGINT UNSIGNED     NOT NULL,
    `event_type`        VARCHAR(50)         NOT NULL COMMENT 'Loại sự kiện: tab_switch, window_blur, copy, paste, resize, fullscreen_exit, devtools_open, disconnect, reconnect, screenshot_attempt',
    `event_data`        JSON                NULL COMMENT 'Dữ liệu bổ sung: {"from_tab": "...", "duration_seconds": 5}',
    `ip_address`        VARCHAR(45)         NULL,
    `occurred_at`       DATETIME            NOT NULL COMMENT 'Thời điểm sự kiện xảy ra (client-side timestamp)',
    `received_at`       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm server nhận được',

    PRIMARY KEY (`id`),
    INDEX `idx_eae_attempt` (`exam_attempt_id`),
    INDEX `idx_eae_type` (`event_type`),
    INDEX `idx_eae_attempt_type` (`exam_attempt_id`, `event_type`),
    INDEX `idx_eae_occurred` (`occurred_at`),

    CONSTRAINT `fk_eae_attempt`
        FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='[FIX #11] Log sự kiện khi thi online. Client gửi realtime để phát hiện gian lận.';

-- ============================================================
-- 23. SETTINGS - Cấu hình hệ thống
-- ============================================================
CREATE TABLE `settings` (
    `id`            BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `key_name`      VARCHAR(100)        NOT NULL COMMENT 'Khoá cấu hình',
    `value`         TEXT                NULL COMMENT 'Giá trị',
    `description`   VARCHAR(255)        NULL COMMENT 'Mô tả cấu hình',
    `created_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cấu hình hệ thống (max file size, tên trường, logo...)';

-- ============================================================
-- DEFERRED FOREIGN KEYS
-- Các FK không thể tạo inline do thứ tự bảng (circular dependency)
-- ============================================================

-- users.avatar_file_id → files
ALTER TABLE `users`
    ADD CONSTRAINT `fk_users_avatar`
    FOREIGN KEY (`avatar_file_id`) REFERENCES `files`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- questions.image_file_id → files
ALTER TABLE `questions`
    ADD CONSTRAINT `fk_questions_image`
    FOREIGN KEY (`image_file_id`) REFERENCES `files`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- question_options.image_file_id → files
ALTER TABLE `question_options`
    ADD CONSTRAINT `fk_qo_image`
    FOREIGN KEY (`image_file_id`) REFERENCES `files`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- GENERATED COLUMNS + INDEXES FOR JSON PERFORMANCE
-- [FIX #10] Virtual columns cho phép đánh index trên dữ liệu
-- JSON mà không tốn disk (VIRTUAL = tính on-the-fly).
-- ============================================================

-- Thống kê nhanh: số câu đúng theo exam_paper_question
ALTER TABLE `exam_answers`
    ADD COLUMN `_epq_id_correct` BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE WHEN `is_correct` = 1 THEN `exam_paper_question_id` ELSE NULL END
        ) VIRTUAL
        COMMENT '[FIX #10] Virtual column cho index thống kê câu đúng',
    ADD INDEX `idx_eans_vcol_correct` (`_epq_id_correct`);

-- Thống kê nhanh: answer cho fill_blank (trích từ JSON)
ALTER TABLE `exam_answers`
    ADD COLUMN `_answer_text_short` VARCHAR(255)
        GENERATED ALWAYS AS (
            LEFT(COALESCE(`answer_text`, ''), 255)
        ) VIRTUAL
        COMMENT '[FIX #10] Virtual column - 255 ký tự đầu của answer_text, indexable',
    ADD INDEX `idx_eans_vcol_answer_text` (`_answer_text_short`);

-- ============================================================
-- INITIAL DATA
-- ============================================================

-- [FIX #6] Tạo roles trước, users sau, rồi gán role

-- Vai trò mặc định
INSERT INTO `roles` (`id`, `code`, `name`, `description`) VALUES
(1, 'admin',               'Quản trị viên',     'Toàn quyền hệ thống'),
(2, 'lecturer',            'Giảng viên',         'Tạo đề thi, điểm danh, upload tài liệu'),
(3, 'student',             'Sinh viên',          'Làm bài thi, xem điểm, tải tài liệu'),
(4, 'teaching_assistant',  'Trợ giảng',          'Hỗ trợ giảng viên, có quyền hạn giới hạn'),
(5, 'department_admin',    'Admin khoa',         'Quản lý môn học và giảng viên trong khoa');

-- Tài khoản Admin mặc định (password: password)
INSERT INTO `users` (`name`, `email`, `password`, `is_active`, `email_verified_at`)
VALUES ('Administrator', 'admin@ems.local', '$2y$12$aB3xYz000000000000000uExampleHashPleaseChangeInProduction00', 1, NOW());

-- Gán role admin cho user #1
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 1);

-- [FIX #3] Loại câu hỏi mặc định
INSERT INTO `question_types` (`code`, `name`, `description`, `is_auto_grade`, `display_order`, `answer_schema`) VALUES
('multiple_choice', 'Trắc nghiệm nhiều lựa chọn',
    'Câu hỏi có 2-6 lựa chọn, chỉ 1 đáp án đúng. Đáp án lưu trong question_options.',
    1, 1,
    '{"type": "single_select", "source": "question_options"}'),
('multiple_answer', 'Trắc nghiệm nhiều đáp án đúng',
    'Câu hỏi có nhiều lựa chọn, có thể có nhiều đáp án đúng. Đáp án lưu trong question_options.',
    1, 2,
    '{"type": "multi_select", "source": "question_options"}'),
('true_false', 'Đúng / Sai',
    'Câu hỏi chỉ có 2 lựa chọn: Đúng hoặc Sai. Đáp án lưu trong question_options.',
    1, 3,
    '{"type": "single_select", "source": "question_options", "fixed_options": ["Đúng", "Sai"]}'),
('fill_blank', 'Điền vào chỗ trống',
    'Sinh viên nhập câu trả lời ngắn. Đáp án đúng lưu trong questions.answer_data.',
    1, 4,
    '{"type": "text_input", "source": "answer_data", "schema": {"accepted_answers": ["string"], "case_sensitive": false}}'),
('matching', 'Ghép cặp',
    'Ghép các mục bên trái với bên phải. Dữ liệu cặp lưu trong questions.answer_data.',
    1, 5,
    '{"type": "matching_pairs", "source": "answer_data", "schema": {"pairs": [{"left": "string", "right": "string"}]}}'),
('essay', 'Tự luận',
    'Sinh viên viết câu trả lời dạng văn bản dài. Cần giảng viên chấm tay.',
    0, 6,
    '{"type": "long_text", "source": "answer_text", "schema": {"max_words": null, "rubric": "answer_data"}}');

-- Cấu hình mặc định
INSERT INTO `settings` (`key_name`, `value`, `description`) VALUES
('app_name', 'EMS - Examination Management System', 'Tên hệ thống'),
('max_upload_size_mb', '20', 'Dung lượng upload tối đa (MB)'),
('allowed_file_types', 'pdf,docx,doc,pptx,ppt,xlsx,xls,jpg,jpeg,png,gif', 'Các loại file được phép upload'),
('max_absent_allowed', '3', 'Số buổi vắng tối đa trước khi cảnh báo'),
('timezone', 'Asia/Ho_Chi_Minh', 'Múi giờ hệ thống'),
('exam_auto_submit', '1', 'Tự động nộp bài khi hết giờ (1=Bật, 0=Tắt)'),
('attendance_geo_radius_m', '100', '[FIX #8] Bán kính GPS tối đa cho điểm danh (mét)'),
('file_storage_disk', 'local', '[FIX #9] Disk mặc định cho file uploads: local, s3, minio');

SET FOREIGN_KEY_CHECKS = 1;