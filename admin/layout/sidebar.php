<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    return;
}
?>
<link rel="stylesheet" href="../css/admin-style.css">

<!-- Overlay for Mobile -->
<div class="sidebar-overlay" id="overlay"></div>

<!-- Top Nav for Mobile Toggle -->
<div class="top-nav">
    <button class="menu-toggle" id="toggleMenu">
        <span>☰ Menu</span>
    </button>
    <div style="font-weight: bold;">Monitoring Kendaraan</div>
</div>

<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="logo-container">
        <img src="../lg dishub.png" alt="Logo Admin">
    </div>
    <h2>Admin</h2>

    <!-- Menu Navigasi -->
    <a href="dashboard.php" class="nav-link">Dashboard</a>
    <a href="kendaraan.php" class="nav-link">Data Kendaraan</a>
    <a href="servis.php" class="nav-link">Data Servis</a>
    <a href="bbm.php" class="nav-link">Data BBM</a>
    <a href="laporan.php" class="nav-link">Laporan</a>
    <a href="tanda_terima.php" class="nav-link">Tanda Terima</a>
    <a href="kelola_admin.php" class="nav-link">Kelola Admin & Keuangan</a>
    <a href="pegawai.php" class="nav-link">Tambah Pegawai</a>
    <a href="kelola_pegawai.php" class="nav-link">Data Pegawai</a>
    <a href="../logout.php" onclick="return confirm('Yakin logout?')" class="nav-link">Logout</a>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleMenu');
    const overlay = document.getElementById('overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    // Mark active menu item
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.style.backgroundColor = 'var(--hover-color)';
            link.style.borderLeft = '4px solid var(--text-white)';
        }
    });
</script>
