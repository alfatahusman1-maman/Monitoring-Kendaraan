<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Keuangan'){
    header("Location: ../index.php");
    exit;
}
require '../config.php';

// Proses verifikasi
if(isset($_GET['verifikasi'])){
    $id = intval($_GET['verifikasi']);
    mysqli_query($conn, "UPDATE tanda_terima SET status='Terverifikasi' WHERE id=$id");
    header("Location: validasi.php");
    exit;
}

// Ambil semua tanda terima
$riwayat = mysqli_query($conn, "SELECT t.*, u.nama, k.no_polisi
    FROM tanda_terima t
    JOIN users u ON t.id_user = u.id
    LEFT JOIN servis s ON t.jenis='Servis' AND t.id_transaksi=s.id
    LEFT JOIN bbm b ON t.jenis='BBM' AND t.id_transaksi=b.id
    LEFT JOIN kendaraan k ON (s.id_kendaraan=k.id OR b.id_kendaraan=k.id)
    ORDER BY t.tanggal_serah DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Validasi Tanda Terima</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }
        .main-content {
            margin-left: 240px; /* sesuai lebar sidebar.php */
            padding: 20px;
        }
        h2 {
            margin-bottom: 15px;
            color: #333;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td { 
            border-bottom: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th {
            background: #007bff;
            color: #fff;
        }
        tr:hover {
            background: #f9f9f9;
        }
        a { 
            text-decoration: none; 
            color: #3498db;
        }
        .btn {
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            color: #fff;
        }
        .btn-verify { background: #27ae60; }
        .btn-verify:hover { background: #219150; }
        .btn-disabled { background: #7f8c8d; cursor: not-allowed; }
    </style>
</head>
<body>
    <?php include 'layout/sidebar.php'; ?>

    <div class="main-content">
        <h2>Validasi Tanda Terima</h2>
        <a href="dashboard.php">Kembali Dashboard</a> | <a href="../logout.php">Logout</a>

        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Jenis</th>
                <th>No Polisi</th>
                <th>Tanggal Serah</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php while($r = mysqli_fetch_assoc($riwayat)): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo $r['nama']; ?></td>
                <td><?php echo $r['jenis']; ?></td>
                <td><?php echo $r['no_polisi']; ?></td>
                <td><?php echo $r['tanggal_serah']; ?></td>
                <td><?php echo $r['status']; ?></td>
                <td>
                    <?php if($r['status']=='Belum Diverifikasi'): ?>
                        <a class="btn btn-verify" href="?verifikasi=<?php echo $r['id']; ?>">Verifikasi</a>
                    <?php else: ?>
                        <span class="btn btn-disabled">Terverifikasi</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
