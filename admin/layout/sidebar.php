<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    return;
}
?>
<div class="sidebar">
    <!-- Logo -->
    <div class="logo-container">
        <img src="../lg dishub.png" alt="Logo Admin">
    </div>
    <h2>Admin</h2>

    <!-- Menu Navigasi -->
    <a href="dashboard.php">Dashboard</a>
    <a href="kendaraan.php">Data Kendaraan</a>
    <a href="servis.php">Data Servis</a>
    <a href="bbm.php">Data BBM</a>
    <a href="laporan.php">Laporan</a>
    <a href="tanda_terima.php">Tanda Terima</a>
    <a href="kelola_admin.php">Kelola Admin & Keuangan</a>
    <a href="pegawai.php">Tambah Pegawai</a>
    <a href="kelola_pegawai.php">Data Pegawai</a>
    <a href="../logout.php" onclick="return confirm('Yakin logout?')">Logout</a>
</div>

<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
    }
    .sidebar {
        height: 100%;
        width: 220px;
        position: fixed;
        top: 0;
        left: 0;
        background-color:  #007bff; 
        padding-top: 20px;
        color: #fff;
        text-align: center;
        overflow-y: auto;
    }
    .logo-container {
        text-align: center;
        margin-bottom: 10px;
    }
    .logo-container img {
        width: 80px;
        max-width: 100%;
        height: auto;
        border-radius: 50%;
    }
    .sidebar h2 {
        margin-bottom: 25px;
        font-size: 20px;
    }
    .sidebar a {
        display: block;
        padding: 14px 20px;
        color: #fff;
        text-decoration: none;
        margin: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        text-align: left;
        transition: background-color 0.3s ease;
    }
    .sidebar a:hover {
        background-color: #0056b3;
    }
    .content {
        margin-left: 240px;
        padding: 20px;
    }
</style>
