<?php
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
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
}
?>
