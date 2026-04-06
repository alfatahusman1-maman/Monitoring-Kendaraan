<?php
// Script untuk menambahkan kolom foto ke tabel kendaraan
session_start();
require 'config.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    die('Akses ditolak! Hanya admin yang dapat menjalankan migration.');
}

try {
    // Cek apakah kolom foto sudah ada
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM kendaraan LIKE 'foto'");
    
    if (mysqli_num_rows($checkColumn) === 0) {
        // Kolom belum ada, tambahkan
        $alter = mysqli_query($conn, "ALTER TABLE `kendaraan` ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL AFTER `status`");
        
        if ($alter) {
            echo "<script>
                alert('✅ Migration berhasil! Kolom foto telah ditambahkan ke tabel kendaraan.');
                window.location.href = 'admin/kendaraan.php';
            </script>";
        } else {
            echo "<script>
                alert('❌ Migration gagal: ' + '" . mysqli_error($conn) . "');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('ℹ️ Kolom foto sudah ada di tabel kendaraan.');
            window.location.href = 'admin/kendaraan.php';
        </script>";
    }
} catch (Exception $e) {
    echo "<script>
        alert('❌ Error: ' + '" . $e->getMessage() . "');
        window.history.back();
    </script>";
}
?>
