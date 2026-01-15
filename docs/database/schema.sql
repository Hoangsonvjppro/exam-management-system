-- ============================================
-- HỆ THỐNG QUẢN LÝ THI TRẮC NGHIỆM
-- Database Schema - MySQL 8.0+
-- ============================================
-- Ngày tạo: 2026-01-15
-- Phiên bản: 1.0.0
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. BẢNG VAI TRÒ VÀ NGƯỜI DÙNG
-- ============================================

-- Bảng vai trò (roles)
CREATE TABLE IF NOT EXISTS `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL COMMENT 'Tên vai trò: admin, lecturer, student',
    `display_name` VARCHAR(100) NOT NULL COMMENT 'Tên hiển thị',
    `description` TEXT NULL COMMENT 'Mô tả vai trò',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng vai trò người dùng';

-- Bảng quyền (permissions)
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên quyền: users.create, exams.delete',
    `display_name` VARCHAR(150) NOT NULL COMMENT 'Tên hiển thị',
    `module` VARCHAR(50) NOT NULL COMMENT 'Module: users, subjects, exams',
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_name` (`name`),
    KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quyền hạn';

-- Bảng liên kết vai trò - quyền
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Liên kết vai trò và quyền';

-- Bảng người dùng (users)
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL COMMENT 'Mã người dùng (mã giảng viên/sinh viên)',
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL COMMENT 'Họ và tên',
    `phone` VARCHAR(20) NULL COMMENT 'Số điện thoại',
    `avatar` VARCHAR(500) NULL COMMENT 'Đường dẫn ảnh đại diện',
    `gender` ENUM('male', 'female', 'other') NULL COMMENT 'Giới tính',
    `date_of_birth` DATE NULL COMMENT 'Ngày sinh',
    `address` TEXT NULL COMMENT 'Địa chỉ',
    `department` VARCHAR(200) NULL COMMENT 'Khoa/Phòng ban',
    `role_id` BIGINT UNSIGNED NOT NULL COMMENT 'Vai trò',
    `status` ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    `email_verified_at` TIMESTAMP NULL,
    `remember_token` VARCHAR(100) NULL,
    `last_login_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_code` (`code`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role_id`),
    KEY `idx_users_status` (`status`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng người dùng';

-- Bảng Personal Access Tokens (Laravel Sanctum)
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tokenable_type` VARCHAR(255) NOT NULL,
    `tokenable_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `abilities` TEXT NULL,
    `last_used_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_personal_access_tokens_token` (`token`),
    KEY `idx_pat_tokenable` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. BẢNG MÔN HỌC VÀ CHƯƠNG
-- ============================================

-- Bảng môn học (subjects)
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL COMMENT 'Mã môn học',
    `name` VARCHAR(200) NOT NULL COMMENT 'Tên môn học',
    `credits` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số tín chỉ',
    `theory_hours` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số tiết lý thuyết',
    `practice_hours` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số tiết thực hành',
    `coefficient` DECIMAL(3,2) NOT NULL DEFAULT 1.00 COMMENT 'Hệ số môn học',
    `description` TEXT NULL COMMENT 'Mô tả môn học',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_by` BIGINT UNSIGNED NULL COMMENT 'Người tạo',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_subjects_code` (`code`),
    KEY `idx_subjects_status` (`status`),
    CONSTRAINT `fk_subjects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng môn học';

-- Bảng chương (chapters)
CREATE TABLE IF NOT EXISTS `chapters` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subject_id` BIGINT UNSIGNED NOT NULL COMMENT 'Môn học',
    `code` VARCHAR(20) NULL COMMENT 'Mã chương',
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên chương',
    `description` TEXT NULL COMMENT 'Mô tả',
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Thứ tự sắp xếp',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_chapters_subject` (`subject_id`),
    KEY `idx_chapters_order` (`order_index`),
    CONSTRAINT `fk_chapters_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng chương của môn học';

-- Bảng tài liệu giảng dạy (teaching_materials)
CREATE TABLE IF NOT EXISTS `teaching_materials` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chapter_id` BIGINT UNSIGNED NOT NULL COMMENT 'Thuộc chương',
    `title` VARCHAR(255) NOT NULL COMMENT 'Tiêu đề tài liệu',
    `file_type` ENUM('pdf', 'doc', 'docx', 'ppt', 'pptx', 'video', 'link', 'other') NOT NULL,
    `file_path` VARCHAR(500) NULL COMMENT 'Đường dẫn file',
    `file_url` VARCHAR(500) NULL COMMENT 'URL (nếu là link)',
    `file_size` BIGINT UNSIGNED NULL COMMENT 'Kích thước file (bytes)',
    `description` TEXT NULL,
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_materials_chapter` (`chapter_id`),
    CONSTRAINT `fk_materials_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_materials_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng tài liệu giảng dạy';

-- ============================================
-- 3. BẢNG PHÂN CÔNG GIẢNG DẠY
-- ============================================

-- Bảng phân công (assignments)
CREATE TABLE IF NOT EXISTS `assignments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(30) NOT NULL COMMENT 'Mã phân công',
    `lecturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'Giảng viên',
    `subject_id` BIGINT UNSIGNED NOT NULL COMMENT 'Môn học',
    `academic_year` VARCHAR(20) NOT NULL COMMENT 'Năm học: 2025-2026',
    `semester` TINYINT UNSIGNED NOT NULL COMMENT 'Học kỳ: 1, 2, 3',
    `note` TEXT NULL COMMENT 'Ghi chú',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `assigned_by` BIGINT UNSIGNED NULL COMMENT 'Admin phân công',
    `assigned_at` TIMESTAMP NULL COMMENT 'Thời gian phân công',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_assignments_code` (`code`),
    KEY `idx_assignments_lecturer` (`lecturer_id`),
    KEY `idx_assignments_subject` (`subject_id`),
    KEY `idx_assignments_year_semester` (`academic_year`, `semester`),
    CONSTRAINT `fk_assignments_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_assignments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng phân công giảng dạy';

-- ============================================
-- 4. BẢNG NHÓM HỌC PHẦN
-- ============================================

-- Bảng nhóm học phần (course_groups)
CREATE TABLE IF NOT EXISTS `course_groups` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(30) NULL COMMENT 'Mã nhóm học phần',
    `name` VARCHAR(200) NOT NULL COMMENT 'Tên nhóm học phần',
    `subject_id` BIGINT UNSIGNED NOT NULL COMMENT 'Môn học',
    `lecturer_id` BIGINT UNSIGNED NOT NULL COMMENT 'Giảng viên phụ trách',
    `academic_year` VARCHAR(20) NOT NULL COMMENT 'Năm học: 2025-2026',
    `semester` TINYINT UNSIGNED NOT NULL COMMENT 'Học kỳ',
    `note` TEXT NULL COMMENT 'Ghi chú',
    `max_students` INT UNSIGNED NULL COMMENT 'Số sinh viên tối đa',
    `status` ENUM('active', 'hidden', 'archived') DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_cg_subject` (`subject_id`),
    KEY `idx_cg_lecturer` (`lecturer_id`),
    KEY `idx_cg_year_semester` (`academic_year`, `semester`),
    KEY `idx_cg_status` (`status`),
    CONSTRAINT `fk_cg_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cg_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng nhóm học phần';

-- Bảng sinh viên trong nhóm học phần (course_group_students)
CREATE TABLE IF NOT EXISTS `course_group_students` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_group_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `enrolled_at` TIMESTAMP NULL COMMENT 'Thời gian đăng ký',
    `status` ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cgs_group_student` (`course_group_id`, `student_id`),
    KEY `idx_cgs_student` (`student_id`),
    CONSTRAINT `fk_cgs_group` FOREIGN KEY (`course_group_id`) REFERENCES `course_groups`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cgs_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sinh viên trong nhóm học phần';

-- ============================================
-- 5. BẢNG ĐIỂM DANH
-- ============================================

-- Bảng phiên điểm danh (attendance_sessions)
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_group_id` BIGINT UNSIGNED NOT NULL,
    `session_name` VARCHAR(100) NULL COMMENT 'Tên buổi học',
    `session_date` DATE NOT NULL COMMENT 'Ngày điểm danh',
    `start_time` TIME NULL COMMENT 'Giờ bắt đầu',
    `end_time` TIME NULL COMMENT 'Giờ kết thúc',
    `qr_code` VARCHAR(255) NOT NULL COMMENT 'Mã QR duy nhất',
    `qr_expires_at` TIMESTAMP NULL COMMENT 'Thời gian hết hạn QR',
    `note` TEXT NULL,
    `status` ENUM('pending', 'active', 'closed') DEFAULT 'pending',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_as_course_group` (`course_group_id`),
    KEY `idx_as_qr_code` (`qr_code`),
    KEY `idx_as_date` (`session_date`),
    CONSTRAINT `fk_as_course_group` FOREIGN KEY (`course_group_id`) REFERENCES `course_groups`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_as_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiên điểm danh';

-- Bảng chi tiết điểm danh (attendance_records)
CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `checked_in_at` TIMESTAMP NOT NULL COMMENT 'Thời gian điểm danh',
    `check_in_method` ENUM('qr_code', 'manual') DEFAULT 'qr_code',
    `status` ENUM('present', 'late', 'absent', 'excused') DEFAULT 'present',
    `note` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `device_info` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ar_session_student` (`session_id`, `student_id`),
    KEY `idx_ar_student` (`student_id`),
    CONSTRAINT `fk_ar_session` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ar_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết điểm danh';

-- ============================================
-- 6. BẢNG CÂU HỎI VÀ ĐÁP ÁN
-- ============================================

-- Bảng độ khó (difficulty_levels)
CREATE TABLE IF NOT EXISTS `difficulty_levels` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL COMMENT 'Tên độ khó: Dễ, Trung bình, Khó',
    `code` VARCHAR(20) NOT NULL COMMENT 'Mã: easy, medium, hard',
    `level` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Cấp độ số: 1, 2, 3',
    `color` VARCHAR(20) NULL COMMENT 'Màu hiển thị',
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_difficulty_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng độ khó câu hỏi';

-- Bảng câu hỏi (questions)
CREATE TABLE IF NOT EXISTS `questions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(30) NULL COMMENT 'Mã câu hỏi',
    `subject_id` BIGINT UNSIGNED NOT NULL COMMENT 'Môn học',
    `chapter_id` BIGINT UNSIGNED NULL COMMENT 'Chương (tuỳ chọn)',
    `difficulty_id` BIGINT UNSIGNED NOT NULL COMMENT 'Độ khó',
    `question_type` ENUM('single_choice', 'multiple_choice', 'true_false') NOT NULL DEFAULT 'single_choice'
        COMMENT 'Loại câu hỏi: 1 đáp án, nhiều đáp án, đúng/sai',
    `content` TEXT NOT NULL COMMENT 'Nội dung câu hỏi',
    `explanation` TEXT NULL COMMENT 'Giải thích đáp án',
    `image` VARCHAR(500) NULL COMMENT 'Hình ảnh đính kèm',
    `audio` VARCHAR(500) NULL COMMENT 'Audio đính kèm',
    `points` DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Điểm câu hỏi',
    `time_limit` INT UNSIGNED NULL COMMENT 'Giới hạn thời gian (giây)',
    `tags` JSON NULL COMMENT 'Tags phân loại',
    `status` ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    `usage_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số lần sử dụng',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_questions_subject` (`subject_id`),
    KEY `idx_questions_chapter` (`chapter_id`),
    KEY `idx_questions_difficulty` (`difficulty_id`),
    KEY `idx_questions_type` (`question_type`),
    KEY `idx_questions_status` (`status`),
    FULLTEXT KEY `ft_questions_content` (`content`),
    CONSTRAINT `fk_questions_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_questions_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_questions_difficulty` FOREIGN KEY (`difficulty_id`) REFERENCES `difficulty_levels`(`id`),
    CONSTRAINT `fk_questions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ngân hàng câu hỏi';

-- Bảng đáp án (answers)
CREATE TABLE IF NOT EXISTS `answers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` BIGINT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL COMMENT 'Nội dung đáp án',
    `image` VARCHAR(500) NULL COMMENT 'Hình ảnh đáp án',
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Đáp án đúng: 1, sai: 0',
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    `explanation` TEXT NULL COMMENT 'Giải thích',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_answers_question` (`question_id`),
    KEY `idx_answers_correct` (`is_correct`),
    CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng đáp án';

-- ============================================
-- 7. BẢNG ĐỀ KIỂM TRA
-- ============================================

-- Bảng đề kiểm tra (exams)
CREATE TABLE IF NOT EXISTS `exams` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(30) NULL COMMENT 'Mã đề thi',
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên bài kiểm tra',
    `subject_id` BIGINT UNSIGNED NOT NULL COMMENT 'Môn học',
    `course_group_id` BIGINT UNSIGNED NULL COMMENT 'Nhóm học phần (tuỳ chọn)',
    `description` TEXT NULL,
    
    -- Cấu hình thời gian
    `start_date` DATETIME NULL COMMENT 'Ngày giờ bắt đầu',
    `end_date` DATETIME NULL COMMENT 'Ngày giờ kết thúc',
    `duration` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Thời gian làm bài (phút)',
    
    -- Cấu hình đề thi
    `total_questions` INT UNSIGNED NOT NULL COMMENT 'Tổng số câu hỏi',
    `total_points` DECIMAL(6,2) NOT NULL DEFAULT 10.00 COMMENT 'Tổng điểm',
    `passing_score` DECIMAL(5,2) NULL COMMENT 'Điểm đạt',
    
    -- Cấu hình độ khó
    `difficulty_config` JSON NULL COMMENT 'Cấu hình số câu theo độ khó: {"easy": 5, "medium": 10, "hard": 5}',
    
    -- Tùy chọn
    `auto_generate` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Tự động lấy câu hỏi từ ngân hàng',
    `shuffle_questions` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Đảo thứ tự câu hỏi',
    `shuffle_answers` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Đảo thứ tự đáp án',
    `show_result` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Hiển thị kết quả sau khi nộp',
    `allow_review` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Cho phép xem lại bài làm',
    `max_attempts` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Số lần làm bài tối đa',
    
    -- Trạng thái
    `status` ENUM('draft', 'published', 'active', 'closed', 'archived') DEFAULT 'draft',
    
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_exams_subject` (`subject_id`),
    KEY `idx_exams_course_group` (`course_group_id`),
    KEY `idx_exams_status` (`status`),
    KEY `idx_exams_dates` (`start_date`, `end_date`),
    CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exams_course_group` FOREIGN KEY (`course_group_id`) REFERENCES `course_groups`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_exams_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng đề kiểm tra';

-- Bảng câu hỏi trong đề thi (exam_questions)
CREATE TABLE IF NOT EXISTS `exam_questions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `exam_id` BIGINT UNSIGNED NOT NULL,
    `question_id` BIGINT UNSIGNED NOT NULL,
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Thứ tự câu hỏi',
    `points` DECIMAL(5,2) NULL COMMENT 'Điểm (ghi đè điểm mặc định)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_eq_exam_question` (`exam_id`, `question_id`),
    KEY `idx_eq_question` (`question_id`),
    CONSTRAINT `fk_eq_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_eq_question` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Câu hỏi trong đề thi';

-- ============================================
-- 8. BẢNG KẾT QUẢ THI
-- ============================================

-- Bảng phiên thi (exam_sessions)
CREATE TABLE IF NOT EXISTS `exam_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `exam_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `attempt_number` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Lần thi thứ mấy',
    `started_at` TIMESTAMP NOT NULL COMMENT 'Thời gian bắt đầu',
    `submitted_at` TIMESTAMP NULL COMMENT 'Thời gian nộp bài',
    `time_spent` INT UNSIGNED NULL COMMENT 'Thời gian làm bài (giây)',
    `question_order` JSON NULL COMMENT 'Thứ tự câu hỏi (sau khi đảo)',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `status` ENUM('in_progress', 'submitted', 'timeout', 'cancelled') DEFAULT 'in_progress',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_es_exam` (`exam_id`),
    KEY `idx_es_student` (`student_id`),
    KEY `idx_es_status` (`status`),
    CONSTRAINT `fk_es_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_es_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiên thi của sinh viên';

-- Bảng câu trả lời (exam_answers)
CREATE TABLE IF NOT EXISTS `exam_answers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` BIGINT UNSIGNED NOT NULL,
    `question_id` BIGINT UNSIGNED NOT NULL,
    `selected_answers` JSON NOT NULL COMMENT 'Mảng ID đáp án đã chọn',
    `is_correct` TINYINT(1) NULL COMMENT 'Đúng hay sai',
    `points_earned` DECIMAL(5,2) NULL COMMENT 'Điểm đạt được',
    `answered_at` TIMESTAMP NULL COMMENT 'Thời gian trả lời',
    `time_spent` INT UNSIGNED NULL COMMENT 'Thời gian làm câu này (giây)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ea_session_question` (`session_id`, `question_id`),
    KEY `idx_ea_question` (`question_id`),
    CONSTRAINT `fk_ea_session` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ea_question` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Câu trả lời của sinh viên';

-- Bảng kết quả thi (exam_results)
CREATE TABLE IF NOT EXISTS `exam_results` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` BIGINT UNSIGNED NOT NULL,
    `exam_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `total_questions` INT UNSIGNED NOT NULL,
    `correct_answers` INT UNSIGNED NOT NULL DEFAULT 0,
    `wrong_answers` INT UNSIGNED NOT NULL DEFAULT 0,
    `unanswered` INT UNSIGNED NOT NULL DEFAULT 0,
    `score` DECIMAL(5,2) NOT NULL COMMENT 'Điểm số',
    `max_score` DECIMAL(5,2) NOT NULL COMMENT 'Điểm tối đa',
    `percentage` DECIMAL(5,2) NOT NULL COMMENT 'Phần trăm',
    `is_passed` TINYINT(1) NULL COMMENT 'Đạt/Không đạt',
    `rank` INT UNSIGNED NULL COMMENT 'Xếp hạng trong lớp',
    `graded_at` TIMESTAMP NULL COMMENT 'Thời gian chấm',
    `graded_by` BIGINT UNSIGNED NULL COMMENT 'Người chấm (nếu manual)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_er_session` (`session_id`),
    KEY `idx_er_exam` (`exam_id`),
    KEY `idx_er_student` (`student_id`),
    KEY `idx_er_score` (`score`),
    CONSTRAINT `fk_er_session` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_er_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_er_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_er_graded_by` FOREIGN KEY (`graded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kết quả thi';

-- ============================================
-- 9. BẢNG HỆ THỐNG
-- ============================================

-- Bảng cài đặt hệ thống (settings)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    `type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `group` VARCHAR(50) NULL COMMENT 'Nhóm cài đặt',
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cài đặt hệ thống';

-- Bảng logs hoạt động (activity_logs)
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'login, create, update, delete',
    `model_type` VARCHAR(100) NULL COMMENT 'App\\Models\\User',
    `model_id` BIGINT UNSIGNED NULL,
    `description` TEXT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_al_user` (`user_id`),
    KEY `idx_al_action` (`action`),
    KEY `idx_al_model` (`model_type`, `model_id`),
    KEY `idx_al_created` (`created_at`),
    CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs hoạt động';

-- Bảng thông báo (notifications)
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` CHAR(36) NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `notifiable_type` VARCHAR(255) NOT NULL,
    `notifiable_id` BIGINT UNSIGNED NOT NULL,
    `data` JSON NOT NULL,
    `read_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_notifiable` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông báo';

-- Bảng import/export jobs
CREATE TABLE IF NOT EXISTS `import_export_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('import', 'export') NOT NULL,
    `entity` VARCHAR(50) NOT NULL COMMENT 'questions, students, results',
    `file_path` VARCHAR(500) NULL,
    `file_name` VARCHAR(255) NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `total_rows` INT UNSIGNED NULL,
    `processed_rows` INT UNSIGNED NULL DEFAULT 0,
    `success_rows` INT UNSIGNED NULL DEFAULT 0,
    `failed_rows` INT UNSIGNED NULL DEFAULT 0,
    `errors` JSON NULL COMMENT 'Chi tiết lỗi',
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_iej_user` (`user_id`),
    KEY `idx_iej_status` (`status`),
    CONSTRAINT `fk_iej_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Jobs import/export';

-- ============================================
-- 10. DỮ LIỆU MẪU (SEEDERS)
-- ============================================

-- Insert roles
INSERT INTO `roles` (`name`, `display_name`, `description`) VALUES
('admin', 'Quản trị viên', 'Quản trị viên hệ thống với toàn quyền'),
('lecturer', 'Giảng viên', 'Giảng viên có thể quản lý môn học, câu hỏi, đề thi'),
('student', 'Sinh viên', 'Sinh viên có thể làm bài thi và xem kết quả');

-- Insert difficulty levels
INSERT INTO `difficulty_levels` (`name`, `code`, `level`, `color`) VALUES
('Dễ', 'easy', 1, '#52c41a'),
('Trung bình', 'medium', 2, '#faad14'),
('Khó', 'hard', 3, '#f5222d'),
('Rất khó', 'very_hard', 4, '#722ed1');

-- Insert default permissions
INSERT INTO `permissions` (`name`, `display_name`, `module`) VALUES
-- Users module
('users.view', 'Xem người dùng', 'users'),
('users.create', 'Tạo người dùng', 'users'),
('users.update', 'Cập nhật người dùng', 'users'),
('users.delete', 'Xóa người dùng', 'users'),
-- Subjects module
('subjects.view', 'Xem môn học', 'subjects'),
('subjects.create', 'Tạo môn học', 'subjects'),
('subjects.update', 'Cập nhật môn học', 'subjects'),
('subjects.delete', 'Xóa môn học', 'subjects'),
-- Questions module
('questions.view', 'Xem câu hỏi', 'questions'),
('questions.create', 'Tạo câu hỏi', 'questions'),
('questions.update', 'Cập nhật câu hỏi', 'questions'),
('questions.delete', 'Xóa câu hỏi', 'questions'),
-- Exams module
('exams.view', 'Xem đề thi', 'exams'),
('exams.create', 'Tạo đề thi', 'exams'),
('exams.update', 'Cập nhật đề thi', 'exams'),
('exams.delete', 'Xóa đề thi', 'exams'),
-- Course groups module
('course_groups.view', 'Xem nhóm học phần', 'course_groups'),
('course_groups.create', 'Tạo nhóm học phần', 'course_groups'),
('course_groups.update', 'Cập nhật nhóm học phần', 'course_groups'),
('course_groups.delete', 'Xóa nhóm học phần', 'course_groups'),
-- Assignments module
('assignments.view', 'Xem phân công', 'assignments'),
('assignments.create', 'Tạo phân công', 'assignments'),
('assignments.update', 'Cập nhật phân công', 'assignments'),
('assignments.delete', 'Xóa phân công', 'assignments'),
-- Reports module
('reports.view', 'Xem báo cáo', 'reports'),
('reports.export', 'Xuất báo cáo', 'reports');

-- Assign all permissions to admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Assign limited permissions to lecturer role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` 
WHERE `name` IN (
    'subjects.view', 'subjects.create', 'subjects.update',
    'questions.view', 'questions.create', 'questions.update', 'questions.delete',
    'exams.view', 'exams.create', 'exams.update', 'exams.delete',
    'course_groups.view', 'course_groups.create', 'course_groups.update', 'course_groups.delete',
    'reports.view', 'reports.export'
);

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`) VALUES
('app_name', 'Hệ thống Quản lý Thi Trắc nghiệm', 'string', 'general', 'Tên ứng dụng'),
('default_exam_duration', '60', 'integer', 'exam', 'Thời gian thi mặc định (phút)'),
('max_attempts', '1', 'integer', 'exam', 'Số lần làm bài tối đa mặc định'),
('qr_code_expiry', '300', 'integer', 'attendance', 'Thời gian hết hạn QR code (giây)'),
('late_threshold', '15', 'integer', 'attendance', 'Ngưỡng đi muộn (phút)');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- END OF SCHEMA
-- ============================================
