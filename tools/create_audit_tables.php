<?php
require __DIR__ . '/../config.php';

$queries = [];

$queries[] = "CREATE TABLE IF NOT EXISTS `approval_logs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$queries[] = "CREATE TABLE IF NOT EXISTS `notifications` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "OK: Created or exists.\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}

// Try to add foreign keys safely
$fk1 = "ALTER TABLE `bbm` ADD CONSTRAINT IF NOT EXISTS `fk_bbm_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL";
$fk2 = "ALTER TABLE `bbm` ADD CONSTRAINT IF NOT EXISTS `fk_bbm_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL";
$fk3 = "ALTER TABLE `servis` ADD CONSTRAINT IF NOT EXISTS `fk_servis_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL";
$fk4 = "ALTER TABLE `servis` ADD CONSTRAINT IF NOT EXISTS `fk_servis_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL";

foreach ([$fk1,$fk2,$fk3,$fk4] as $fk) {
    if ($conn->query($fk)) {
        echo "FK OK\n";
    } else {
        echo "FK skipped/err: " . $conn->error . "\n";
    }
}

echo "Done.\n";
