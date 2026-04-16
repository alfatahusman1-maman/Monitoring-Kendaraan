<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin'){
    header("Location: ../index.php");
    exit;
}

require '../config.php';
require_once '../helpers/approval_workflow.php';

$message = '';
$error = '';

/* =====================================================
   AKSI APPROVE / REJECT (via POST for better security)
   ===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['setuju'])){
        $id = intval($_POST['setuju']);
        $admin_id = $_SESSION['user']['id'];
        if(adminApproveSubmission('BBM', $id, $admin_id)){
            $message = 'Pengajuan BBM disetujui.';
        } else {
            $error = 'Gagal menyetujui pengajuan.';
        }
    }

    if(isset($_POST['tolak'])){
        $id = intval($_POST['tolak']);
        $admin_id = $_SESSION['user']['id'];
        $catatan = $_POST['catatan_tolak'] ?? 'Ditolak oleh admin';
        if(adminRejectSubmission('BBM', $id, $admin_id, $catatan)){
            $message = 'Pengajuan BBM ditolak.';
        } else {
            $error = 'Gagal menolak pengajuan.';
        }
    }
}

// Handle GET parameters for quick approve (backward compatibility)
if(isset($_GET['setuju'])){
    $id = intval($_GET['setuju']);
    $admin_id = $_SESSION['user']['id'];
    if(adminApproveSubmission('BBM', $id, $admin_id)){
        header("Location: bbm.php?status=ALL&msg=approved");
    }
    exit;
}

if(isset($_GET['tolak'])){
    $id = intval($_GET['tolak']);
    $admin_id = $_SESSION['user']['id'];
    if(adminRejectSubmission('BBM', $id, $admin_id, 'Ditolak oleh admin')){
        header("Location: bbm.php?status=ALL&msg=rejected");
    }
    exit;
}

/* =====================================================
   HAPUS DATA BBM
   ===================================================== */
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);

    $cek = mysqli_query($conn, "SELECT foto_struk FROM bbm WHERE id=$id");
    $row = mysqli_fetch_assoc($cek);

    if($row && !empty($row['foto_struk'])){
        $file = "../uploads/struk_bbm/".$row['foto_struk'];
        if(file_exists($file)) unlink($file);
    }

    if(mysqli_query($conn, "DELETE FROM bbm WHERE id=$id")){
        $message = 'Data BBM berhasil dihapus.';
    } else {
        $error = 'Gagal menghapus data BBM.';
    }
    exit;
}

/* =====================================================
   FILTER STATUS - Mendukung kedua field status_admin dan status lama
   ===================================================== */
$status_filter = $_GET['status'] ?? 'PENDING';

$filter_sql = "";
if ($status_filter == "PENDING") {
    // Prioritas: cek status_admin dulu, jika tidak ada gunakan status field lama
    $filter_sql = "WHERE (b.status_admin='PENDING' OR (b.status_admin IS NULL AND b.status='Pending'))";
} elseif ($status_filter == "APPROVED") {
    $filter_sql = "WHERE (b.status_admin='APPROVED' OR (b.status_admin IS NULL AND b.status='Disetujui'))";
} elseif ($status_filter == "REJECTED") {
    $filter_sql = "WHERE (b.status_admin='REJECTED' OR (b.status_admin IS NULL AND b.status='Ditolak'))";
} elseif ($status_filter == "ALL") {
    $filter_sql = ""; // Semua data
}

/* =====================================================
   AMBIL DATA BBM (READ Operation)
   ===================================================== */
$data = mysqli_query($conn, "
    SELECT b.*, u.nama, k.no_polisi
    FROM bbm b
    JOIN users u ON b.id_user = u.id
    JOIN kendaraan k ON b.id_kendaraan = k.id
    $filter_sql
    ORDER BY 
        CASE 
            WHEN COALESCE(b.status_admin, 'PENDING') = 'PENDING' THEN 1
            WHEN COALESCE(b.status_admin, 'APPROVED') = 'APPROVED' THEN 2
            WHEN COALESCE(b.status_admin, 'REJECTED') = 'REJECTED' THEN 3
            ELSE 4
        END,
        COALESCE(b.admin_review_date, b.created_at) DESC
");

// Get message from URL parameter
$msg = $_GET['msg'] ?? '';
if($msg == 'approved') $message = 'Pengajuan BBM berhasil disetujui.';
if($msg == 'rejected') $message = 'Pengajuan BBM berhasil ditolak.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi BBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/buttons.css" rel="stylesheet">

    <style>
        body { 
            margin: 0; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa; 
        }
        .content { 
            margin-left: 220px; 
            padding: 20px; 
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 5px solid #007bff;
        }

        .header h2 {
            margin: 0;
            font-size: 28px;
            color: #1a1a1a;
        }

        .header p {
            margin: 10px 0 0;
            color: #666;
            font-size: 14px;
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th { 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            padding: 14px 12px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td { 
            border-bottom: 1px solid #e9ecef;
            padding: 12px;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover { 
            background: #f8f9fa;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter { 
            padding: 10px 16px; 
            background: white; 
            border-radius: 8px;
            border: 1px solid #dee2e6;
            text-decoration: none; 
            color: #495057;
            display: inline-block;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter:hover {
            background: #f8f9fa;
            border-color: #007bff;
            color: #007bff;
        }

        .active-filter { 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-color: #007bff;
        }

        .catatan {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            max-width: 200px;
            word-wrap: break-word;
            padding: 8px;
            background: #f8f9fa;
            border-left: 3px solid #ffc107;
            border-radius: 4px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            margin: 2px;
        }

        .action-cell {
            white-space: nowrap;
        }
    </style>
</head>

<body>

<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <div class="header">
        <h2> Konfirmasi Pengajuan BBM</h2>
        <p><a href="dashboard.php" style="color:#007bff; text-decoration:none;">⬅ Kembali Dashboard</a></p>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FILTER STATUS -->
    <div class="filter-container">
        <strong style="align-self: center; color: #495057;">Filter Status:</strong>
        <a href="bbm.php?status=ALL" class="filter <?= ($status_filter=='ALL'?'active-filter':''); ?>">📋 Semua</a>
        <a href="bbm.php?status=PENDING" class="filter <?= ($status_filter=='PENDING'?'active-filter':''); ?>">⏳ Pending</a>
        <a href="bbm.php?status=APPROVED" class="filter <?= ($status_filter=='APPROVED'?'active-filter':''); ?>">✅ Disetujui</a>
        <a href="bbm.php?status=REJECTED" class="filter <?= ($status_filter=='REJECTED'?'active-filter':''); ?>">❌ Ditolak</a>
    </div>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>No Polisi</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Liter</th>
            <th>Biaya</th>
            <th>Struk</th>
            <th>Status Admin</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if(!mysqli_num_rows($data)): ?>
            <tr>
                <td colspan="11" style="text-align: center; padding: 40px; color: #999;">
                    📭 Tidak ada data BBM untuk filter ini
                </td>
            </tr>
        <?php else: ?>
            <?php while($b = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><strong><?= $b['id']; ?></strong></td>
                <td><?= htmlspecialchars($b['nama']); ?></td>
                <td><strong><?= htmlspecialchars($b['no_polisi']); ?></strong></td>
                <td><?= date('d/m/Y', strtotime($b['tanggal'])); ?></td>
                <td><?= htmlspecialchars($b['jenis_bbm'] ?? ''); ?></td>
                <td><?= $b['liter']; ?> L</td>
                <td>Rp <?= number_format($b['biaya'] ?? 0, 0, ',', '.'); ?></td>

                <td>
                    <?php if($b['foto_struk']): ?>
                        <a href="../uploads/struk_bbm/<?= htmlspecialchars($b['foto_struk']); ?>" 
                           target="_blank" class="btn btn-sm btn-info"> Lihat</a>
                    <?php else: ?>
                        <span style="color:#ccc;">-</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php 
                        $status_admin = $b['status_admin'] ?? ($b['status'] ?? 'PENDING');
                        if($status_admin == "PENDING"){
                            echo "<span class='badge bg-warning text-dark'>⏳ Pending</span>";
                        } elseif($status_admin == "APPROVED" || $status_admin == "Disetujui"){
                            echo "<span class='badge bg-success'>✅ Disetujui</span>";
                        } else {
                            echo "<span class='badge bg-danger'>❌ Ditolak</span>";
                        }
                    ?>
                    <?php if($b['catatan_admin']): ?>
                        <div class="catatan">📝 <?= htmlspecialchars($b['catatan_admin']); ?></div>
                    <?php endif; ?>
                </td>



                <td class="action-cell">
                    <?php if($b['status_admin'] != "APPROVED" && $b['status_admin'] != "REJECTED"): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="setuju" value="<?= $b['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-success" 
                                    onclick="return confirm('Setujui pengajuan ini?')"> Setuju</button>
                        </form>
                        <button class="btn btn-sm btn-danger" 
                                onclick="showRejectModal(<?= $b['id']; ?>, '<?= htmlspecialchars($b['nama']); ?>')"> Tolak</button>
                    <?php else: ?>
                        <small style="color:#999;">Sudah Diproses</small>
                    <?php endif; ?>
                    
                    <a href="?hapus=<?= $b['id']; ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Hapus data ini? Tindakan ini tidak dapat dibatalkan.')">🗑️ Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Modal Tolak -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">❌ Tolak Pengajuan BBM</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <p>Pengajuan dari: <strong id="nama-user"></strong></p>
          <div class="mb-3">
            <label class="form-label fw-500">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="catatan_tolak" required rows="4" 
                      placeholder="Jelaskan secara detail alasan penolakan pengajuan ini..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Kirim Penolakan</button>
        </div>
        <input type="hidden" name="tolak" id="reject-id">
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showRejectModal(id, nama) {
    document.getElementById('reject-id').value = id;
    document.getElementById('nama-user').textContent = nama;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
</body>
</html>