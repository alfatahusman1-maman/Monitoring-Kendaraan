-- Workflow Approval System - Safe Migration
-- Hanya menambahkan kolom yang belum ada

-- ============================================
-- ALTER TABLE BBM - Tambah kolom jika belum ada
-- ============================================

ALTER TABLE `bbm` 
  ADD COLUMN IF NOT EXISTS `jenis_bbm` VARCHAR(100) AFTER `biaya`,
  ADD COLUMN IF NOT EXISTS `foto_struk` VARCHAR(255) AFTER `jenis_bbm`,
  ADD COLUMN IF NOT EXISTS `status_admin` ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING' AFTER `foto_struk`,
  ADD COLUMN IF NOT EXISTS `catatan_admin` TEXT AFTER `status_admin`,
  ADD COLUMN IF NOT EXISTS `admin_id` INT AFTER `catatan_admin`,
  ADD COLUMN IF NOT EXISTS `admin_review_date` TIMESTAMP NULL AFTER `admin_id`,
  ADD COLUMN IF NOT EXISTS `status_keuangan` ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING' AFTER `admin_review_date`,
  ADD COLUMN IF NOT EXISTS `catatan_keuangan` TEXT AFTER `status_keuangan`,
  ADD COLUMN IF NOT EXISTS `keuangan_id` INT AFTER `catatan_keuangan`,
  ADD COLUMN IF NOT EXISTS `keuangan_review_date` TIMESTAMP NULL AFTER `keuangan_id`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `keuangan_review_date`;

-- Add Foreign Keys jika belum ada
ALTER TABLE `bbm` 
  ADD CONSTRAINT IF NOT EXISTS `fk_bbm_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_bbm_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ============================================
-- ALTER TABLE SERVIS - Tambah kolom jika belum ada
-- ============================================

ALTER TABLE `servis`
  ADD COLUMN IF NOT EXISTS `foto_struk` VARCHAR(255) AFTER `biaya`,
  ADD COLUMN IF NOT EXISTS `status_admin` ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING' AFTER `foto_struk`,
  ADD COLUMN IF NOT EXISTS `catatan_admin` TEXT AFTER `status_admin`,
  ADD COLUMN IF NOT EXISTS `admin_id` INT AFTER `catatan_admin`,
  ADD COLUMN IF NOT EXISTS `admin_review_date` TIMESTAMP NULL AFTER `admin_id`,
  ADD COLUMN IF NOT EXISTS `status_keuangan` ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING' AFTER `admin_review_date`,
  ADD COLUMN IF NOT EXISTS `catatan_keuangan` TEXT AFTER `status_keuangan`,
  ADD COLUMN IF NOT EXISTS `keuangan_id` INT AFTER `catatan_keuangan`,
  ADD COLUMN IF NOT EXISTS `keuangan_review_date` TIMESTAMP NULL AFTER `keuangan_id`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `keuangan_review_date`;

-- Add Foreign Keys jika belum ada
ALTER TABLE `servis`
  ADD CONSTRAINT IF NOT EXISTS `fk_servis_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_servis_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ============================================
-- CREATE TABLE APPROVAL_LOGS (Audit Trail)
-- ============================================

CREATE TABLE IF NOT EXISTS `approval_logs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `jenis` ENUM('BBM','Servis') NOT NULL,
  `id_transaksi` INT NOT NULL,
  `stage` ENUM('User','Admin','Keuangan') NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `user_id` INT NOT NULL,
  `catatan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_transaksi` (`jenis`, `id_transaksi`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_created` (`created_at`)
);

-- ============================================
-- CREATE TABLE NOTIFICATIONS
-- ============================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','danger') DEFAULT 'info',
  `link` VARCHAR(255),
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_read` (`user_id`, `is_read`),
  INDEX `idx_created` (`created_at`)
);
