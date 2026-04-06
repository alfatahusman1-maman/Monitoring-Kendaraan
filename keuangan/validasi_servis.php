<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Keuangan'){
    header("Location: ../index.php");
    exit;
}
require '../config.php';
require_once '../helpers/approval_workflow.php';

$message = '';
$error = '';

// Handle validate/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['validate_id'])) {
        $id = intval($_POST['validate_id']);
        $keuangan_id = $_SESSION['user']['id'];
        $ok = keuanganValidateSubmission('Servis', $id, $keuangan_id);
        if ($ok) $message = 'Pengajuan Servis divalidasi oleh keuangan.';
        else $error = 'Gagal memvalidasi pengajuan.';
    }
    if (isset($_POST['reject_id'])) {
        $id = intval($_POST['reject_id']);
        $keuangan_id = $_SESSION['user']['id'];
        $catatan = $_POST['catatan'] ?? '';
        $ok = keuanganRejectSubmission('Servis', $id, $keuangan_id, $catatan);
        if ($ok) $message = 'Pengajuan Servis ditolak oleh keuangan.';
        else $error = 'Gagal menolak pengajuan.';
    }
}

// Ambil data yang menunggu validasi keuangan
$data = getKeuanganPendingSubmissions('servis', 200);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Validasi Servis - Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/buttons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa;
        }

        .sidebar-wrapper {
            margin-left: 240px;
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

        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            padding: 14px 12px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .table tbody td {
            padding: 12px;
            font-size: 13px;
            border-color: #e9ecef;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .btn {
            font-weight: 600;
            border-radius: 8px;
            font-size: 13px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>

<?php include 'layout/sidebar.php'; ?>

<div class="sidebar-wrapper">
    <div class="header">
        <h2>💰 Queue Validasi Servis</h2>
        <p style="margin: 10px 0 0; color: #666;">
            <a href="dashboard.php" style="color: #007bff; text-decoration: none;">⬅ Kembali Dashboard</a>
        </p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>No Polisi</th>
            <th>Tanggal</th>
            <th>Jenis Servis</th>
            <th>Biaya</th>
            <th>Struk</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
            <?php if(empty($data)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                        📭 Tidak ada pengajuan servis yang pending validasi
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($data as $row): ?>
                <tr>
                    <td><strong><?= $row['id']; ?></strong></td>
                    <td><?= htmlspecialchars($row['nama_user']); ?></td>
                    <td><strong><?= htmlspecialchars($row['no_polisi']); ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><?= htmlspecialchars($row['jenis_servis']); ?></td>
                    <td>Rp <?= number_format($row['biaya']); ?></td>
                    <td>
                        <?php if(!empty($row['foto_struk'])): ?>
                            <a href="../uploads/struk_servis/<?= htmlspecialchars($row['foto_struk']); ?>" target="_blank" class="btn btn-sm btn-info">👁️ Lihat</a>
                        <?php else: ?>
                            <span style="color: #ccc;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="validate_id" value="<?= $row['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-success">✅ Validasi</button>
                        </form>
                        <button class="btn btn-sm btn-danger" onclick="showRejectModal(<?= $row['id']; ?>)">❌ Tolak</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">❌ Tolak Pengajuan Servis</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="reject_id" id="reject_id">
        <div class="mb-3">
            <label for="catatan" class="form-label fw-500">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea name="catatan" id="catatan" class="form-control" rows="4" required placeholder="Jelaskan alasan penolakan..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger">Kirim Penolakan</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showRejectModal(id){
    document.getElementById('reject_id').value = id;
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
</body>
</html>
