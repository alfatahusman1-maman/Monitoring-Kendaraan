<?php
require __DIR__ . '/..//config.php';

function runSql($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "OK: " . substr($sql,0,50) . "...\n";
        return true;
    } else {
        echo "ERR: " . $conn->error . " for: " . substr($sql,0,80) . "...\n";
        return false;
    }
}

// Check approval_logs
$res = $conn->query("SHOW TABLES LIKE 'approval_logs'");
if ($res && $res->num_rows > 0) {
    echo "Table approval_logs already exists\n";
} else {
    echo "Creating table approval_logs...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `approval_logs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    runSql($conn, $sql);
}

// Check notifications
$res = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($res && $res->num_rows > 0) {
    echo "Table notifications already exists\n";
} else {
    echo "Creating table notifications...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    runSql($conn, $sql);
}

echo "Done.\n";
?>