-- Workflow Approval System - Database Migration
-- Menambahkan kolom approval workflow ke tabel bbm dan servis

-- ============================================
-- ALTER TABLE BBM
-- ============================================

ALTER TABLE `bbm` 
  DROP COLUMN `status`,
  ADD COLUMN `foto_struk` VARCHAR(255) AFTER `biaya`,
  ADD COLUMN `status_admin` ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING' AFTER `foto_struk`,
  ADD COLUMN `catatan_admin` TEXT AFTER `status_admin`,
  ADD COLUMN `admin_id` INT AFTER `catatan_admin`,
  ADD COLUMN `admin_review_date` TIMESTAMP NULL AFTER `admin_id`,
  ADD COLUMN `status_keuangan` ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING' AFTER `admin_review_date`,
  ADD COLUMN `catatan_keuangan` TEXT AFTER `status_keuangan`,
  ADD COLUMN `keuangan_id` INT AFTER `catatan_keuangan`,
  ADD COLUMN `keuangan_review_date` TIMESTAMP NULL AFTER `keuangan_id`,
  ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `keuangan_review_date`,
  ADD FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ============================================
-- ALTER TABLE SERVIS
-- ============================================

ALTER TABLE `servis`
  DROP COLUMN `status`,
  ADD COLUMN `foto_struk` VARCHAR(255) AFTER `biaya`,
  ADD COLUMN `status_admin` ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING' AFTER `foto_struk`,
  ADD COLUMN `catatan_admin` TEXT AFTER `status_admin`,
  ADD COLUMN `admin_id` INT AFTER `catatan_admin`,
  ADD COLUMN `admin_review_date` TIMESTAMP NULL AFTER `admin_id`,
  ADD COLUMN `status_keuangan` ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING' AFTER `admin_review_date`,
  ADD COLUMN `catatan_keuangan` TEXT AFTER `status_keuangan`,
  ADD COLUMN `keuangan_id` INT AFTER `catatan_keuangan`,
  ADD COLUMN `keuangan_review_date` TIMESTAMP NULL AFTER `keuangan_id`,
  ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `keuangan_review_date`,
  ADD FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ============================================
-- CREATE TABLE APPROVAL_LOGS (Audit Trail)
-- ============================================

CREATE TABLE IF NOT EXISTS `approval_logs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `jenis` ENUM('BBM','Servis') NOT NULL,
  `id_transaksi` INT NOT NULL,
  `stage` ENUM('Admin','Keuangan') NOT NULL,
  `action` ENUM('Approved','Rejected','Resubmitted') NOT NULL,
  `user_id` INT NOT NULL,
  `catatan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- CREATE TABLE NOTIFICATIONS
-- ============================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('Info','Warning','Success','Error') DEFAULT 'Info',
  `link` VARCHAR(255),
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create index for faster queries
CREATE INDEX idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX idx_approval_logs_transaksi ON approval_logs(jenis, id_transaksi);
