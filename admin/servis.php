<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin'){
    header("Location: ../index.php");
    exit;
}

require '../config.php';
require_once '../helpers/approval_workflow.php';

/* =====================================================
   AKSI APPROVE / REJECT
   ===================================================== */
if(isset($_GET['setuju'])){
    $id = intval($_GET['setuju']);
    $admin_id = $_SESSION['user']['id'];
    adminApproveSubmission('Servis', $id, $admin_id);
    header("Location: servis.php?status=ALL");
    exit;
}

if(isset($_GET['tolak'])){
    $id = intval($_GET['tolak']);
    $admin_id = $_SESSION['user']['id'];
    adminRejectSubmission('Servis', $id, $admin_id, 'Ditolak oleh admin');
    header("Location: servis.php?status=ALL");
    exit;
}

/* =====================================================
   HAPUS DATA
   ===================================================== */
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);

    $cek = mysqli_query($conn, "SELECT foto_struk FROM servis WHERE id=$id");
    $row = mysqli_fetch_assoc($cek);

    if($row && !empty($row['foto_struk'])){
        $file = "../uploads/struk_servis/" . $row['foto_struk'];
        if(file_exists($file)) unlink($file);
    }

    mysqli_query($conn, "DELETE FROM servis WHERE id=$id");
    header("Location: servis.php?status=ALL");
    exit;
}

/* =====================================================
   FILTER STATUS
   ===================================================== */
$status_filter = $_GET['status'] ?? 'PENDING';

$filter_sql = "";
if ($status_filter == "PENDING") {
    $filter_sql = "WHERE s.status_admin = 'PENDING'";
} elseif ($status_filter == "APPROVED") {
    $filter_sql = "WHERE s.status_admin = 'APPROVED'";
} elseif ($status_filter == "REJECTED") {
    $filter_sql = "WHERE s.status_admin = 'REJECTED'";
}

/* =====================================================
   AMBIL DATA
   ===================================================== */
$data = mysqli_query($conn, "
    SELECT s.*, u.nama, k.no_polisi
    FROM servis s
    JOIN users u ON s.id_user = u.id
    JOIN kendaraan k ON s.id_kendaraan = k.id
    $filter_sql
    ORDER BY s.tanggal DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfirmasi Servis</title>

<style>
    table { border-collapse: collapse; width:100%; margin-top:15px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:center; }
    th { background:#0056b3; color:white; }
    tr:nth-child(even) { background:#f9f9f9; }

    .btn { 
        display: inline-block; 
        padding:6px 10px; 
        border-radius:4px; 
        text-decoration:none; 
        color:white; 
        font-size:13px; 
        margin: 2px;
    }
    .setuju { background:green; }
    .tolak { background:#d9534f; }
    .hapus { background:#444; }
    .filter-btn { padding:8px 12px; background:#EEE; border-radius:4px; margin-right:5px; display: inline-block; margin-bottom: 5px; }
    .active-filter { background:#007BFF; color:white; }
</style>
</head>
<body>

<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <h2> Konfirmasi Pengajuan Servis</h2>
    <p><a href="dashboard.php">⬅ Kembali Dashboard</a></p>

    <!-- FILTER STATUS -->
    <div style="margin-bottom:15px;">
        <a href="servis.php?status=ALL" class="filter-btn <?= ($status_filter=='ALL'?'active-filter':''); ?>">Semua</a>
        <a href="servis.php?status=PENDING" class="filter-btn <?= ($status_filter=='PENDING'?'active-filter':''); ?>">Pending</a>
        <a href="servis.php?status=APPROVED" class="filter-btn <?= ($status_filter=='APPROVED'?'active-filter':''); ?>">Disetujui</a>
        <a href="servis.php?status=REJECTED" class="filter-btn <?= ($status_filter=='REJECTED'?'active-filter':''); ?>">Ditolak</a>
    </div>

    <div class="table-responsive">
        <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>No Polisi</th>
            <th>Tanggal</th>
            <th>Jenis Servis</th>
            <th>Biaya</th>
            <th>Struk</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php while($s = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $s['id']; ?></td>
            <td><?= $s['nama']; ?></td>
            <td><?= $s['no_polisi']; ?></td>
            <td><?= $s['tanggal']; ?></td>
            <td><?= $s['jenis_servis']; ?></td>
            <td>Rp <?= number_format($s['biaya']); ?></td>

            <!-- STRUK SERVIS -->
            <td>
                <?php if(!empty($s['foto_struk'])): ?>
                    <a href="../uploads/struk_servis/<?= $s['foto_struk']; ?>" 
                       target="_blank" class="btn setuju">Lihat</a>
                <?php else: ?>
                        <span style="color:#ccc;">-</span>
                <?php endif; ?>
            </td>

            <td>
                <?php
                    if($s['status_admin'] == "APPROVED"){
                        echo "<span style='color:green;'>Disetujui Admin</span>";
                    } elseif($s['status_admin'] == "REJECTED"){
                        echo "<span style='color:red;'>Ditolak Admin</span>";
                    } else {
                        echo "<span style='color:orange;'>Pending</span>";
                    }
                ?>
            </td>

            <td>
                <?php if ($s['status_admin'] == "PENDING"): ?>
                    <a href="?setuju=<?= $s['id']; ?>" class="btn setuju"> Setuju</a>
                    <a href="?tolak=<?= $s['id']; ?>" class="btn tolak"> Tolak</a>
                <?php else: ?>
                    <span style="font-size:13px; color:#666;">(Sudah Diproses)</span>
                <?php endif; ?>

                <a href="?hapus=<?= $s['id']; ?>"
                   class="btn hapus"
                   onclick="return confirm('Hapus data ini?')">🗑</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
