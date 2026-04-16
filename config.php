<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fungsi pembantu untuk mengambil variabel env
function get_db_env($name, $default) {
    return $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: $default;
}

// Cek apakah ekstensi mysqli tersedia
if (!function_exists('mysqli_connect')) {
    die("❌ Error: Ekstensi 'mysqli' tidak terinstal di server ini.");
}

// Ambil variabel environment dari Railway
$host = get_db_env('MYSQLHOST', "nozomi.proxy.rlwy.net");
$user = get_db_env('MYSQLUSER', "root");
$pass = get_db_env('MYSQLPASSWORD', "jTjXWFcHxwTmoEdZIphkSZbeYTzLvMVh");
$db   = get_db_env('MYSQLDATABASE', "railway");
$port = get_db_env('MYSQLPORT', 16632);

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
