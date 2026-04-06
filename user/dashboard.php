<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'User') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard User</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }
        .content {
            margin-left: 240px;
            padding: 20px;
        }
        .welcome-box {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.15);
        }
        .welcome-box h2 {
            margin: 0 0 10px;
        }
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .step-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .step-card:hover {
            transform: translateY(-5px);
        }
        .step-card h3 {
            margin: 0;
            font-size: 18px;
            color: #007bff;
        }
        .step-card p {
            margin-top: 8px;
            color: #333;
            font-size: 14px;
        }
        .tips {
            margin-top: 30px;
            padding: 15px;
            background: #eaf4ff;
            border-left: 5px solid #007bff;
            border-radius: 8px;
        }
        .tips h4 {
            margin: 0 0 10px;
            color: #007bff;
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <div class="welcome-box">
        <h2>Dashboard Pegawai</h2>
        <p>Selamat datang, <b><?php echo $_SESSION['user']['nama']; ?></b> 👋</p>
        <p>Gunakan sistem ini untuk mengelola kebutuhan kendaraan dinas Anda.</p>
    </div>

    <div class="steps">
        <div class="step-card">
            <h3> Ajukan Servis</h3>
            <p>Lakukan pengajuan servis kendaraan dengan mengisi formulir di menu <b>Ajukan Servis</b>.</p>
        </div>
        <div class="step-card">
            <h3> Ajukan BBM</h3>
            <p>Ajukan kebutuhan BBM kendaraan melalui menu <b>Ajukan BBM</b>.</p>
        </div>
        <div class="step-card">
            <h3> Cek Tanda Terima</h3>
            <p>Lihat dan unduh tanda terima pengajuan Anda di menu <b>Tanda Terima</b>.</p>
        </div>
        <div class="step-card">
            <h3> Pantau Status</h3>
            <p>Periksa status pengajuan apakah sedang divalidasi atau sudah disetujui.</p>
        </div>
    </div>

    <div class="tips">
        <h4> Tips Penggunaan</h4>
        <ul>
            <li>Pastikan data kendaraan sesuai sebelum mengajukan.</li>
            <li>Gunakan menu <b>Tanda Terima</b> sebagai bukti pengajuan.</li>
            <li>Hubungi admin bila ada kendala pada sistem.</li>
        </ul>
    </div>
</div>
</body>
</html>
