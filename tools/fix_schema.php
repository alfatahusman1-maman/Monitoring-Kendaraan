<?php
require_once dirname(__DIR__) . '/config.php';

echo "<h3>Memperbaiki Skema Database...</h3>";

function addColumnIfMissing($table, $column, $definition) {
    global $conn;
    $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (mysqli_num_rows($check) == 0) {
        $q = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (mysqli_query($conn, $q)) {
            echo "<p>✅ Kolom `$column` ditambahkan ke tabel `$table`.</p>";
        } else {
            echo "<p>❌ Gagal menambahkan kolom `$column` ke `$table`: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>ℹ️ Kolom `$column` sudah ada di tabel `$table`.</p>";
    }
}

// 1. Fix SERVIS table
addColumnIfMissing('servis', 'status_admin', "ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING' AFTER status");
addColumnIfMissing('servis', 'status_keuangan', "ENUM('PENDING', 'VALIDATED', 'REJECTED') DEFAULT 'PENDING' AFTER status_admin");
addColumnIfMissing('servis', 'admin_id', "INT NULL AFTER status_keuangan");
addColumnIfMissing('servis', 'admin_review_date', "DATETIME NULL AFTER admin_id");
addColumnIfMissing('servis', 'catatan_admin', "TEXT NULL AFTER admin_review_date");
addColumnIfMissing('servis', 'keuangan_id', "INT NULL AFTER catatan_admin");
addColumnIfMissing('servis', 'keuangan_review_date', "DATETIME NULL AFTER keuangan_id");
addColumnIfMissing('servis', 'catatan_keuangan', "TEXT NULL AFTER keuangan_review_date");
addColumnIfMissing('servis', 'foto_struk', "VARCHAR(255) NULL AFTER catatan_keuangan");

// 2. Fix BBM table
addColumnIfMissing('bbm', 'jenis_bbm', "VARCHAR(50) NULL AFTER tanggal");
addColumnIfMissing('bbm', 'biaya', "DECIMAL(12,2) NULL AFTER liter");
addColumnIfMissing('bbm', 'foto_struk', "VARCHAR(255) NULL AFTER biaya");
addColumnIfMissing('bbm', 'status_admin', "ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING' AFTER foto_struk");
addColumnIfMissing('bbm', 'status_keuangan', "ENUM('PENDING', 'VALIDATED', 'REJECTED') DEFAULT 'PENDING' AFTER status_admin");
addColumnIfMissing('bbm', 'admin_id', "INT NULL AFTER status_keuangan");
addColumnIfMissing('bbm', 'admin_review_date', "DATETIME NULL AFTER admin_id");
addColumnIfMissing('bbm', 'catatan_admin', "TEXT NULL AFTER admin_review_date");
addColumnIfMissing('bbm', 'keuangan_id', "INT NULL AFTER catatan_admin");
addColumnIfMissing('bbm', 'keuangan_review_date', "DATETIME NULL AFTER keuangan_id");
addColumnIfMissing('bbm', 'catatan_keuangan', "TEXT NULL AFTER keuangan_review_date");

// 3. Create APPROVAL_LOGS table
$qApprovalLogs = "CREATE TABLE IF NOT EXISTS approval_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('BBM', 'Servis') NOT NULL,
    id_transaksi INT NOT NULL,
    stage ENUM('User', 'Admin', 'Keuangan') NOT NULL,
    action VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $qApprovalLogs)) {
    echo "<p>✅ Tabel `approval_logs` berhasil disiapkan.</p>";
}

// 4. Create NOTIFICATIONS table
$qNotifications = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('INFO', 'SUCCESS', 'WARNING', 'ERROR') DEFAULT 'INFO',
    link VARCHAR(255) NULL,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $qNotifications)) {
    echo "<p>✅ Tabel `notifications` berhasil disiapkan.</p>";
}

echo "<h4>🚀 Skema Database Selesai Diperbaiki!</h4>";
?>
