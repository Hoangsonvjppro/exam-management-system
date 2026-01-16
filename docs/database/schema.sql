-- ============================================
-- HỆ THỐNG QUẢN LÝ THI TRẮC NGHIỆM
-- Database Schema - MySQL 8.0+
-- ============================================
-- Ngày cập nhật: 2026-01-16
-- Phiên bản: 1.1.0 (Đồng bộ theo ERD tiếng Việt)
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. BẢNG PHÂN QUYỀN VÀ TỔ CHỨC
-- ============================================

-- Bảng vai trò (vai_tro)
CREATE TABLE IF NOT EXISTS `vai_tro` (
    `ma_vai_tro` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ten_vai_tro` VARCHAR(100) NOT NULL COMMENT 'Tên vai trò (admin, lecturer, student)',
    `mo_ta` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_vai_tro`),
    UNIQUE KEY `uk_vai_tro_ten` (`ten_vai_tro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng vai trò người dùng';

-- Bảng quyền (quyen)
CREATE TABLE IF NOT EXISTS `quyen` (
    `ma_quyen` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ten_quyen` VARCHAR(100) NOT NULL COMMENT 'Tên quyền',
    `module` VARCHAR(100) NOT NULL COMMENT 'Module quản lý',
    `mo_ta` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_quyen`),
    UNIQUE KEY `uk_quyen_ten` (`ten_quyen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quyền hạn';

-- Bảng khoa phòng (khoa_phong)
CREATE TABLE IF NOT EXISTS `khoa_phong` (
    `ma_khoa` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_khoa_viet_tat` VARCHAR(50) NOT NULL COMMENT 'Mã viết tắt',
    `ten_khoa` VARCHAR(255) NOT NULL COMMENT 'Tên khoa',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_khoa`),
    UNIQUE KEY `uk_khoa_phong_ma_vt` (`ma_khoa_viet_tat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng khoa phòng';

-- Bảng liên kết vai trò - quyền (vai_tro_quyen)
CREATE TABLE IF NOT EXISTS `vai_tro_quyen` (
    `ma_vai_tro` BIGINT UNSIGNED NOT NULL,
    `ma_quyen` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`ma_vai_tro`, `ma_quyen`),
    CONSTRAINT `fk_vtq_vai_tro` FOREIGN KEY (`ma_vai_tro`) REFERENCES `vai_tro`(`ma_vai_tro`) ON DELETE CASCADE,
    CONSTRAINT `fk_vtq_quyen` FOREIGN KEY (`ma_quyen`) REFERENCES `quyen`(`ma_quyen`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Liên kết vai trò và quyền';

-- Bảng người dùng (nguoi_dung)
CREATE TABLE IF NOT EXISTS `nguoi_dung` (
    `ma_nguoi_dung` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_dinh_danh` VARCHAR(50) NOT NULL COMMENT 'MSSV/MSGV',
    `email` VARCHAR(255) NOT NULL,
    `mat_khau` VARCHAR(255) NOT NULL,
    `ho_ten` VARCHAR(255) NOT NULL,
    `so_dien_thoai` VARCHAR(20) NULL,
    `anh_dai_dien` VARCHAR(500) NULL,
    `gioi_tinh` ENUM('Nam', 'Nữ', 'Khác') NULL,
    `ngay_sinh` DATE NULL,
    `dia_chi` TEXT NULL,
    `ma_khoa` BIGINT UNSIGNED NULL,
    `ma_vai_tro` BIGINT UNSIGNED NOT NULL,
    `trang_thai` ENUM('Hoạt động', 'Khóa', 'Chờ xác nhận') DEFAULT 'Hoạt động',
    `xac_thuc_email` TIMESTAMP NULL,
    `dang_nhap_cuoi` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`ma_nguoi_dung`),
    UNIQUE KEY `uk_nd_ma_dinh_danh` (`ma_dinh_danh`),
    UNIQUE KEY `uk_nd_email` (`email`),
    CONSTRAINT `fk_nd_khoa` FOREIGN KEY (`ma_khoa`) REFERENCES `khoa_phong`(`ma_khoa`) ON DELETE SET NULL,
    CONSTRAINT `fk_nd_vai_tro` FOREIGN KEY (`ma_vai_tro`) REFERENCES `vai_tro`(`ma_vai_tro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng người dùng';

-- Bảng token truy cập (token_truy_cap)
CREATE TABLE IF NOT EXISTS `token_truy_cap` (
    `ma_token` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `loai_doi_tuong` VARCHAR(255) NOT NULL,
    `ma_doi_tuong` BIGINT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `quyen_han` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_token`),
    UNIQUE KEY `uk_ttc_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng token truy cập';

-- ============================================
-- 2. BẢNG MÔN HỌC VÀ TÀI LIỆU
-- ============================================

-- Bảng môn học (mon_hoc)
CREATE TABLE IF NOT EXISTS `mon_hoc` (
    `ma_mon` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_mon_viet_tat` VARCHAR(50) NOT NULL COMMENT 'Mã viết tắt',
    `ten_mon` VARCHAR(255) NOT NULL,
    `so_tin_chi` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `so_tiet_ly_thuyet` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `so_tiet_thuc_hanh` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `he_so` DECIMAL(3,2) NOT NULL DEFAULT 1.00,
    `mo_ta` TEXT NULL,
    `trang_thai` ENUM('Đang mở', 'Đã đóng') DEFAULT 'Đang mở',
    `nguoi_tao` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`ma_mon`),
    UNIQUE KEY `uk_mon_hoc_ma_vt` (`ma_mon_viet_tat`),
    CONSTRAINT `fk_mon_hoc_nguoi_tao` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng môn học';

-- Bảng chương (chuong)
CREATE TABLE IF NOT EXISTS `chuong` (
    `ma_chuong` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_mon` BIGINT UNSIGNED NOT NULL,
    `ma_chuong_viet_tat` VARCHAR(50) NULL,
    `ten_chuong` VARCHAR(255) NOT NULL,
    `thu_tu` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_chuong`),
    CONSTRAINT `fk_chuong_mon_hoc` FOREIGN KEY (`ma_mon`) REFERENCES `mon_hoc`(`ma_mon`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng chương môn học';

-- Bảng tài liệu (tai_lieu)
CREATE TABLE IF NOT EXISTS `tai_lieu` (
    `ma_tai_lieu` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_chuong` BIGINT UNSIGNED NOT NULL,
    `tieu_de` VARCHAR(255) NOT NULL,
    `loai_file` ENUM('PDF', 'DOC', 'PPT', 'Video', 'Link', 'Khác') NOT NULL,
    `duong_dan` VARCHAR(500) NULL,
    `url` VARCHAR(500) NULL,
    `kich_thuoc` BIGINT UNSIGNED NULL,
    `thu_tu` INT UNSIGNED NOT NULL DEFAULT 0,
    `nguoi_tao` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_tai_lieu`),
    CONSTRAINT `fk_tai_lieu_chuong` FOREIGN KEY (`ma_chuong`) REFERENCES `chuong`(`ma_chuong`) ON DELETE CASCADE,
    CONSTRAINT `fk_tai_lieu_nguoi_tao` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng tài liệu giảng dạy';

-- ============================================
-- 3. BẢNG PHÂN CÔNG VÀ LỚP HỌC
-- ============================================

-- Bảng phân công (phan_cong)
CREATE TABLE IF NOT EXISTS `phan_cong` (
    `ma_phan_cong` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_phan_cong_viet_tat` VARCHAR(50) NOT NULL,
    `ma_giang_vien` BIGINT UNSIGNED NOT NULL,
    `ma_mon` BIGINT UNSIGNED NOT NULL,
    `nam_hoc` VARCHAR(20) NOT NULL,
    `hoc_ky` TINYINT UNSIGNED NOT NULL,
    `trang_thai` ENUM('Đang thực hiện', 'Đã hoàn thành', 'Đã hủy') DEFAULT 'Đang thực hiện',
    `nguoi_phan_cong` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_phan_cong`),
    UNIQUE KEY `uk_phan_cong_ma_vt` (`ma_phan_cong_viet_tat`),
    CONSTRAINT `fk_pc_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_mon_hoc` FOREIGN KEY (`ma_mon`) REFERENCES `mon_hoc`(`ma_mon`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_nguoi_pc` FOREIGN KEY (`nguoi_phan_cong`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng phân công giảng dạy';

-- Bảng lớp học phần (lop_hoc_phan)
CREATE TABLE IF NOT EXISTS `lop_hoc_phan` (
    `ma_lop` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_lop_viet_tat` VARCHAR(50) NULL,
    `ten_lop` VARCHAR(255) NOT NULL,
    `ma_mon` BIGINT UNSIGNED NOT NULL,
    `ma_giang_vien` BIGINT UNSIGNED NOT NULL,
    `nam_hoc` VARCHAR(20) NOT NULL,
    `hoc_ky` TINYINT UNSIGNED NOT NULL,
    `so_sv_toi_da` INT UNSIGNED NULL,
    `trang_thai` ENUM('Đang mở', 'Đã đóng', 'Đã kết thúc') DEFAULT 'Đang mở',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_lop`),
    CONSTRAINT `fk_lhp_mon_hoc` FOREIGN KEY (`ma_mon`) REFERENCES `mon_hoc`(`ma_mon`) ON DELETE CASCADE,
    CONSTRAINT `fk_lhp_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lớp học phần';

-- Bảng sinh viên lớp (sinh_vien_lop)
CREATE TABLE IF NOT EXISTS `sinh_vien_lop` (
    `ma_dang_ky` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_lop` BIGINT UNSIGNED NOT NULL,
    `ma_sinh_vien` BIGINT UNSIGNED NOT NULL,
    `ngay_dang_ky` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `trang_thai` ENUM('Đang học', 'Đã nghỉ', 'Hoàn thành') DEFAULT 'Đang học',
    PRIMARY KEY (`ma_dang_ky`),
    UNIQUE KEY `uk_svl_lop_sv` (`ma_lop`, `ma_sinh_vien`),
    CONSTRAINT `fk_svl_lop` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc_phan`(`ma_lop`) ON DELETE CASCADE,
    CONSTRAINT `fk_svl_sinh_vien` FOREIGN KEY (`ma_sinh_vien`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sinh viên trong lớp học phần';

-- ============================================
-- 4. BẢNG ĐIỂM DANH
-- ============================================

-- Bảng buổi điểm danh (buoi_diem_danh)
CREATE TABLE IF NOT EXISTS `buoi_diem_danh` (
    `ma_buoi` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_lop` BIGINT UNSIGNED NOT NULL,
    `ten_buoi` VARCHAR(255) NULL,
    `ngay` DATE NOT NULL,
    `gio_bat_dau` TIME NULL,
    `gio_ket_thuc` TIME NULL,
    `ma_qr` VARCHAR(255) NULL,
    `het_han_qr` TIMESTAMP NULL,
    `trang_thai` ENUM('Chưa bắt đầu', 'Đang diễn ra', 'Đã kết thúc') DEFAULT 'Chưa bắt đầu',
    `nguoi_tao` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_buoi`),
    CONSTRAINT `fk_bdd_lop` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc_phan`(`ma_lop`) ON DELETE CASCADE,
    CONSTRAINT `fk_bdd_nguoi_tao` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiên điểm danh';

-- Bảng bản ghi điểm danh (ban_ghi_diem_danh)
CREATE TABLE IF NOT EXISTS `ban_ghi_diem_danh` (
    `ma_ban_ghi` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_buoi` BIGINT UNSIGNED NOT NULL,
    `ma_sinh_vien` BIGINT UNSIGNED NOT NULL,
    `thoi_gian_diem_danh` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `phuong_thuc` ENUM('QR Code', 'Thủ công') DEFAULT 'QR Code',
    `trang_thai` ENUM('Có mặt', 'Vắng mặt', 'Đi muộn', 'Có phép') DEFAULT 'Có mặt',
    `dia_chi_ip` VARCHAR(45) NULL,
    PRIMARY KEY (`ma_ban_ghi`),
    UNIQUE KEY `uk_bgdd_buoi_sv` (`ma_buoi`, `ma_sinh_vien`),
    CONSTRAINT `fk_bgdd_buoi` FOREIGN KEY (`ma_buoi`) REFERENCES `buoi_diem_danh`(`ma_buoi`) ON DELETE CASCADE,
    CONSTRAINT `fk_bgdd_sinh_vien` FOREIGN KEY (`ma_sinh_vien`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết điểm danh';

-- ============================================
-- 5. BẢNG NGÂN HÀNG CÂU HỎI
-- ============================================

-- Bảng độ khó (do_kho)
CREATE TABLE IF NOT EXISTS `do_kho` (
    `ma_do_kho` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ten` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_do_kho`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Độ khó câu hỏi';

-- Bảng câu hỏi (cau_hoi)
CREATE TABLE IF NOT EXISTS `cau_hoi` (
    `ma_cau_hoi` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_mon` BIGINT UNSIGNED NOT NULL,
    `ma_chuong` BIGINT UNSIGNED NULL,
    `ma_do_kho` BIGINT UNSIGNED NOT NULL,
    `loai_cau_hoi` ENUM('Một đáp án', 'Nhiều đáp án', 'Đúng/Sai') NOT NULL,
    `noi_dung` TEXT NOT NULL,
    `giai_thich` TEXT NULL,
    `hinh_anh` VARCHAR(500) NULL,
    `diem` DECIMAL(5,2) DEFAULT 1.00,
    `thoi_gian_gioi_han` INT UNSIGNED NULL COMMENT 'Giây',
    `trang_thai` ENUM('Hoạt động', 'Nháp', 'Đã ẩn') DEFAULT 'Hoạt động',
    `nguoi_tao` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`ma_cau_hoi`),
    CONSTRAINT `fk_ch_mon` FOREIGN KEY (`ma_mon`) REFERENCES `mon_hoc`(`ma_mon`) ON DELETE CASCADE,
    CONSTRAINT `fk_ch_chuong` FOREIGN KEY (`ma_chuong`) REFERENCES `chuong`(`ma_chuong`) ON DELETE SET NULL,
    CONSTRAINT `fk_ch_do_kho` FOREIGN KEY (`ma_do_kho`) REFERENCES `do_kho`(`ma_do_kho`),
    CONSTRAINT `fk_ch_nguoi_tao` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ngân hàng câu hỏi';

-- Bảng đáp án (dap_an)
CREATE TABLE IF NOT EXISTS `dap_an` (
    `ma_dap_an` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_cau_hoi` BIGINT UNSIGNED NOT NULL,
    `noi_dung` TEXT NOT NULL,
    `hinh_anh` VARCHAR(500) NULL,
    `dap_an_dung` BOOLEAN DEFAULT FALSE,
    `thu_tu` INT UNSIGNED DEFAULT 0,
    `giai_thich` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_dap_an`),
    CONSTRAINT `fk_da_cau_hoi` FOREIGN KEY (`ma_cau_hoi`) REFERENCES `cau_hoi`(`ma_cau_hoi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng đáp án';

-- ============================================
-- 6. BẢNG ĐỀ THI VÀ KẾT QUẢ
-- ============================================

-- Bảng đề thi (de_thi)
CREATE TABLE IF NOT EXISTS `de_thi` (
    `ma_de` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_de_viet_tat` VARCHAR(50) NULL,
    `ten_de` VARCHAR(255) NOT NULL,
    `ma_mon` BIGINT UNSIGNED NOT NULL,
    `ma_lop` BIGINT UNSIGNED NULL,
    `mo_ta` TEXT NULL,
    `thoi_gian_bat_dau` DATETIME NULL,
    `thoi_gian_ket_thuc` DATETIME NULL,
    `thoi_luong` INT UNSIGNED NOT NULL COMMENT 'Phút',
    `tong_so_cau` INT UNSIGNED NOT NULL,
    `tong_diem` DECIMAL(6,2) DEFAULT 10.00,
    `diem_dat` DECIMAL(5,2) NULL,
    `cau_hinh_do_kho` JSON NULL,
    `tu_dong_tao` BOOLEAN DEFAULT FALSE,
    `xao_tron_cau_hoi` BOOLEAN DEFAULT TRUE,
    `xao_tron_dap_an` BOOLEAN DEFAULT TRUE,
    `hien_ket_qua` BOOLEAN DEFAULT TRUE,
    `cho_xem_lai` BOOLEAN DEFAULT FALSE,
    `so_lan_thi_toi_da` INT UNSIGNED DEFAULT 1,
    `trang_thai` ENUM('Nháp', 'Đã công bố', 'Đang diễn ra', 'Đã đóng') DEFAULT 'Nháp',
    `nguoi_tao` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`ma_de`),
    CONSTRAINT `fk_dt_mon` FOREIGN KEY (`ma_mon`) REFERENCES `mon_hoc`(`ma_mon`) ON DELETE CASCADE,
    CONSTRAINT `fk_dt_lop` FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc_phan`(`ma_lop`) ON DELETE SET NULL,
    CONSTRAINT `fk_dt_nguoi_tao` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng đề thi';

-- Bảng câu hỏi đề thi (cau_hoi_de_thi)
CREATE TABLE IF NOT EXISTS `cau_hoi_de_thi` (
    `ma_cau_hoi_de` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_de` BIGINT UNSIGNED NOT NULL,
    `ma_cau_hoi` BIGINT UNSIGNED NOT NULL,
    `thu_tu` INT UNSIGNED DEFAULT 0,
    `diem` DECIMAL(5,2) NULL,
    PRIMARY KEY (`ma_cau_hoi_de`),
    UNIQUE KEY `uk_chdt_de_ch` (`ma_de`, `ma_cau_hoi`),
    CONSTRAINT `fk_chdt_de` FOREIGN KEY (`ma_de`) REFERENCES `de_thi`(`ma_de`) ON DELETE CASCADE,
    CONSTRAINT `fk_chdt_cau_hoi` FOREIGN KEY (`ma_cau_hoi`) REFERENCES `cau_hoi`(`ma_cau_hoi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Câu hỏi trong đề thi';

-- Bảng phiên thi (phien_thi)
CREATE TABLE IF NOT EXISTS `phien_thi` (
    `ma_phien` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_de` BIGINT UNSIGNED NOT NULL,
    `ma_sinh_vien` BIGINT UNSIGNED NOT NULL,
    `lan_thi_thu` INT UNSIGNED DEFAULT 1,
    `bat_dau` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `nop_bai` TIMESTAMP NULL,
    `thoi_gian_lam` INT UNSIGNED NULL COMMENT 'Giây',
    `thu_tu_cau_hoi` JSON NULL,
    `dia_chi_ip` VARCHAR(45) NULL,
    `trang_thai` ENUM('Đang thi', 'Đã nộp', 'Hết giờ', 'Đã hủy') DEFAULT 'Đang thi',
    PRIMARY KEY (`ma_phien`),
    CONSTRAINT `fk_pt_de` FOREIGN KEY (`ma_de`) REFERENCES `de_thi`(`ma_de`) ON DELETE CASCADE,
    CONSTRAINT `fk_pt_sinh_vien` FOREIGN KEY (`ma_sinh_vien`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiên thi sinh viên';

-- Bảng bài làm (bai_lam)
CREATE TABLE IF NOT EXISTS `bai_lam` (
    `ma_bai_lam` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_phien` BIGINT UNSIGNED NOT NULL,
    `ma_cau_hoi` BIGINT UNSIGNED NOT NULL,
    `dap_an_chon` JSON NULL,
    `dung` BOOLEAN NULL,
    `diem_dat_duoc` DECIMAL(5,2) NULL,
    `thoi_gian_tra_loi` TIMESTAMP NULL,
    `thoi_gian_lam` INT UNSIGNED NULL,
    PRIMARY KEY (`ma_bai_lam`),
    UNIQUE KEY `uk_bl_phien_ch` (`ma_phien`, `ma_cau_hoi`),
    CONSTRAINT `fk_bl_phien` FOREIGN KEY (`ma_phien`) REFERENCES `phien_thi`(`ma_phien`) ON DELETE CASCADE,
    CONSTRAINT `fk_bl_cau_hoi` FOREIGN KEY (`ma_cau_hoi`) REFERENCES `cau_hoi`(`ma_cau_hoi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết bài làm';

-- Bảng kết quả thi (ket_qua_thi)
CREATE TABLE IF NOT EXISTS `ket_qua_thi` (
    `ma_ket_qua` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_phien` BIGINT UNSIGNED NOT NULL,
    `ma_de` BIGINT UNSIGNED NOT NULL,
    `ma_sinh_vien` BIGINT UNSIGNED NOT NULL,
    `tong_so_cau` INT UNSIGNED NOT NULL,
    `so_cau_dung` INT UNSIGNED DEFAULT 0,
    `so_cau_sai` INT UNSIGNED DEFAULT 0,
    `chua_tra_loi` INT UNSIGNED DEFAULT 0,
    `diem` DECIMAL(5,2) NOT NULL,
    `diem_toi_da` DECIMAL(5,2) NOT NULL,
    `ty_le_phan_tram` DECIMAL(5,2) NOT NULL,
    `dat` BOOLEAN NULL,
    `xep_hang` INT UNSIGNED NULL,
    `thoi_gian_cham` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `nguoi_cham` BIGINT UNSIGNED NULL,
    PRIMARY KEY (`ma_ket_qua`),
    UNIQUE KEY `uk_kqt_phien` (`ma_phien`),
    CONSTRAINT `fk_kqt_phien` FOREIGN KEY (`ma_phien`) REFERENCES `phien_thi`(`ma_phien`) ON DELETE CASCADE,
    CONSTRAINT `fk_kqt_de` FOREIGN KEY (`ma_de`) REFERENCES `de_thi`(`ma_de`) ON DELETE CASCADE,
    CONSTRAINT `fk_kqt_sinh_vien` FOREIGN KEY (`ma_sinh_vien`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE,
    CONSTRAINT `fk_kqt_nguoi_cham` FOREIGN KEY (`nguoi_cham`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kết quả thi';

-- ============================================
-- 7. BẢNG HỆ THỐNG VÀ NHẬT KÝ
-- ============================================

-- Bảng nhật ký hoạt động (nhat_ky_hoat_dong)
CREATE TABLE IF NOT EXISTS `nhat_ky_hoat_dong` (
    `ma_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_nguoi_dung` BIGINT UNSIGNED NULL,
    `hanh_dong` VARCHAR(255) NOT NULL,
    `loai_doi_tuong` VARCHAR(100) NULL,
    `ma_doi_tuong` BIGINT UNSIGNED NULL,
    `mo_ta` TEXT NULL,
    `gia_tri_cu` JSON NULL,
    `gia_tri_moi` JSON NULL,
    `dia_chi_ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_log`),
    CONSTRAINT `fk_nkhd_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs hoạt động';

-- Bảng thông báo (thong_bao)
CREATE TABLE IF NOT EXISTS `thong_bao` (
    `ma_thong_bao` CHAR(36) NOT NULL,
    `loai` VARCHAR(255) NOT NULL,
    `loai_nguoi_nhan` VARCHAR(255) NOT NULL,
    `ma_nguoi_nhan` BIGINT UNSIGNED NOT NULL,
    `du_lieu` JSON NOT NULL,
    `thoi_gian_doc` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_thong_bao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông báo';

-- Bảng công việc nhập xuất (cong_viec_nhap_xuat)
CREATE TABLE IF NOT EXISTS `cong_viec_nhap_xuat` (
    `ma_cong_viec` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ma_nguoi_dung` BIGINT UNSIGNED NOT NULL,
    `loai` ENUM('Nhập', 'Xuất') NOT NULL,
    `doi_tuong` VARCHAR(100) NOT NULL,
    `duong_dan_file` VARCHAR(500) NULL,
    `trang_thai` ENUM('Chờ xử lý', 'Đang xử lý', 'Hoàn thành', 'Thất bại') DEFAULT 'Chờ xử lý',
    `tong_dong` INT UNSIGNED DEFAULT 0,
    `da_xu_ly` INT UNSIGNED DEFAULT 0,
    `thanh_cong` INT UNSIGNED DEFAULT 0,
    `that_bai` INT UNSIGNED DEFAULT 0,
    `loi` JSON NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ma_cong_viec`),
    CONSTRAINT `fk_cvnx_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung`(`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Jobs import/export';

-- ============================================
-- 8. DỮ LIỆU MẪU (SEEDERS)
-- ============================================

-- Chèn vai trò
INSERT INTO `vai_tro` (`ten_vai_tro`, `mo_ta`) VALUES
('Quản trị viên', 'Quản trị viên hệ thống với toàn quyền'),
('Giảng viên', 'Giảng viên có thể quản lý môn học, câu hỏi, đề thi'),
('Sinh viên', 'Sinh viên có thể làm bài thi và xem kết quả');

-- Chèn độ khó
INSERT INTO `do_kho` (`ten`) VALUES
('Dễ'),
('Trung bình'),
('Khó'),
('Rất khó');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- KẾT THÚC SCHEMA
-- ============================================
