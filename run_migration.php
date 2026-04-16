<?php
/**
 * Database Migration Runner
 * Jalankan file ini untuk setup database workflow approval system
 * 
 * Akses: http://localhost/monitoring_kendaraan/run_migration.php
 */

require 'config.php';

// Check if already migrated
$check_column = $conn->query("SHOW COLUMNS FROM bbm LIKE 'foto_struk'");

if ($check_column && $check_column->num_rows > 0) {
    echo '<div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 6px; margin: 20px; border: 1px solid #c3e6cb; font-family: Arial;">';
    echo '<h2>✅ Database Sudah Ter-Migrate!</h2>';
    echo '<p>Kolom <strong>foto_struk</strong> dan struktur workflow approval sudah ada di database.</p>';
    echo '<p><a href="index.php" style="color: #155724; text-decoration: underline;">Kembali ke Dashboard</a></p>';
    echo '</div>';
    exit;
}

// Array of migration queries
$queries = [
    // BBM Table
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `jenis_bbm` VARCHAR(100) AFTER `biaya`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `foto_struk` VARCHAR(255) AFTER `jenis_bbm`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `status_admin` ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING' AFTER `foto_struk`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `catatan_admin` TEXT AFTER `status_admin`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `admin_id` INT AFTER `catatan_admin`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `admin_review_date` TIMESTAMP NULL AFTER `admin_id`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `status_keuangan` ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING' AFTER `admin_review_date`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `catatan_keuangan` TEXT AFTER `status_keuangan`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `keuangan_id` INT AFTER `catatan_keuangan`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `keuangan_review_date` TIMESTAMP NULL AFTER `keuangan_id`",
    "ALTER TABLE `bbm` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `keuangan_review_date`",
    
    // Servis Table
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `foto_struk` VARCHAR(255) AFTER `biaya`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `status_admin` ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING' AFTER `foto_struk`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `catatan_admin` TEXT AFTER `status_admin`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `admin_id` INT AFTER `catatan_admin`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `admin_review_date` TIMESTAMP NULL AFTER `admin_id`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `status_keuangan` ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING' AFTER `admin_review_date`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `catatan_keuangan` TEXT AFTER `status_keuangan`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `keuangan_id` INT AFTER `catatan_keuangan`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `keuangan_review_date` TIMESTAMP NULL AFTER `keuangan_id`",
    "ALTER TABLE `servis` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `keuangan_review_date`",
    
    // Approval Logs Table
    "CREATE TABLE IF NOT EXISTS `approval_logs` (
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
    )",
    
    // Notifications Table
    "CREATE TABLE IF NOT EXISTS `notifications` (
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
    )"
];

$success = true;
$executed = 0;
$errors = [];

foreach ($queries as $query) {
    if (!empty(trim($query))) {
        if (!$conn->query($query)) {
            $success = false;
            $errors[] = [
                'query' => substr($query, 0, 100) . '...',
                'error' => $conn->error
            ];
        } else {
            $executed++;
        }
    }
}

// Add Foreign Keys (if not already exist)
if (!$conn->query("ALTER TABLE `bbm` ADD CONSTRAINT `fk_bbm_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL")) {
    // Foreign key might already exist, that's ok
}

if (!$conn->query("ALTER TABLE `bbm` ADD CONSTRAINT `fk_bbm_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL")) {
    // Foreign key might already exist, that's ok
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f9fafc; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 6px; border: 1px solid #c3e6cb; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 6px; border: 1px solid #f5c6cb; margin-bottom: 20px; }
        .info { background: #cfe2ff; color: #084298; padding: 15px; border-radius: 6px; border: 1px solid #b6d4fe; margin-bottom: 15px; }
        h2 { margin-top: 0; }
        a { color: #007bff; text-decoration: none; padding: 8px 16px; display: inline-block; background: #007bff; color: white; border-radius: 4px; margin-top: 10px; }
        a:hover { text-decoration: underline; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success && $executed > 0): ?>
            <div class="success">
                <h2>✅ Migration Berhasil!</h2>
                <p><strong><?php echo $executed; ?></strong> query telah dijalankan dengan sukses.</p>
                <p>Database workflow approval system sudah siap digunakan!</p>
                <p>
                    <a href="index.php" style="background: #28a745;">Kembali ke Dashboard</a>
                    <a href="user/ajukan_bbm.php" style="background: #007bff;">Ajukan BBM</a>
                </p>
            </div>
        <?php else: ?>
            <div class="error">
                <h2>⚠️ Warning atau Error</h2>
                <p><?php echo $executed > 0 ? 'Sebagian query berhasil, tapi ada yang error.' : 'Terjadi error saat menjalankan migration.'; ?></p>
                <?php if (!empty($errors)): ?>
                    <h3>Error Details:</h3>
                    <?php foreach ($errors as $err): ?>
                        <div class="info">
                            <strong>Query:</strong>
                            <pre><?php echo htmlspecialchars($err['query']); ?></pre>
                            <strong>Error:</strong>
                            <pre><?php echo htmlspecialchars($err['error']); ?></pre>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <p><a href="javascript:history.back()" style="background: #6c757d;">← Kembali</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
