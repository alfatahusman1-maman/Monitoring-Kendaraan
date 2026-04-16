<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek apakah ekstensi mysqli tersedia
if (!function_exists('mysqli_connect')) {
    die("❌ Error: Ekstensi 'mysqli' tidak terinstal di server ini. Silakan aktifkan di konfigurasi PHP.");
}

// Ambil variabel environment dari Railway (jika ada)
$host = getenv('MYSQLHOST') ?: "nozomi.proxy.rlwy.net";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "jTjXWFcHxwTmoEdZIphkSZbeYTzLvMVh";
$db   = getenv('MYSQLDATABASE') ?: "railway";
$port = getenv('MYSQLPORT') ?: 16632;

// Koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    // Fallback jika koneksi ke Railway gagal (misal di localhost)
    $conn = mysqli_connect("localhost", "root", "", "db_monitoring");
    if (!$conn) {
        die("❌ Koneksi database gagal: " . mysqli_connect_error());
    }
}
?>
