<?php
$host = "nozomi.proxy.rlwy.net";
$user = "root";
$pass = "jTjXWFcHxwTmoEdZIphkSZbeYTzLvMVh";
$db   = "railway";
$port = 16632;

$conn = mysqli_connect($host, $user, $pass, $db, $port);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
