<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role"] != "User") {
    header("Location: ../index.php");
    exit;
}

require "../config.php";
require "../helpers/approval_workflow.php";

$success = false;
$error = "";
$id_user = $_SESSION["user"]["id"];

/* ============================================================
   HANDLE DELETE SERVIS
   ============================================================ */
if (isset($_GET["hapus"])) {

    $id_hapus = intval($_GET["hapus"]);
    
    // Pastikan data milik user
    $cek = $conn->query("SELECT * FROM servis WHERE id = $id_hapus AND id_user = $id_user")->fetch_assoc();

    if ($cek) {

        // Hapus file struk jika ada
        if (!empty($cek["foto_struk"])) {
            $file = "../uploads/struk_servis/" . $cek["foto_struk"];
            if (file_exists($file)) unlink($file);
        }

        // Hapus data
        $conn->query("DELETE FROM servis WHERE id = $id_hapus");

        $success = true;
        $message_delete = "Riwayat servis berhasil dihapus.";
    } else {
        $error = "Data tidak ditemukan atau Anda tidak memiliki akses.";
    }
}

/* ============================================================
   HANDLE FORM SUBMIT
   ============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_servis"])) {

    $id_kendaraan = intval($_POST["id_kendaraan"] ?? 0);
    $tanggal = $_POST["tanggal"] ?? date("Y-m-d");
    $jenis_servis = trim($_POST["jenis_servis"] ?? "");
    $biaya = floatval($_POST["biaya"] ?? 0);

    if ($id_kendaraan <= 0 || empty($tanggal) || empty($jenis_servis) || $biaya <= 0) {
        $error = "Semua field harus diisi.";
    } else {

        /* Upload Struk */
        $foto_struk = null;
        if (!empty($_FILES["foto_struk"]["name"])) {

            $target_dir = "../uploads/struk_servis/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            $ext = strtolower(pathinfo($_FILES["foto_struk"]["name"], PATHINFO_EXTENSION));
            $allow = ["jpg","jpeg","png","gif","pdf"];

            if (!in_array($ext, $allow)) {
                $error = "Format file tidak didukung.";
            } else {
                $newname = time() . "_" . rand(1000,9999) . "." . $ext;
                if (move_uploaded_file($_FILES["foto_struk"]["tmp_name"], $target_dir . $newname)) {
                    $foto_struk = $newname;
                }
            }
        }

        if (empty($error)) {
            $servis_id = createServisSubmission(
                $id_user,
                $id_kendaraan,
                $tanggal,
                $jenis_servis,
                $biaya,
                $foto_struk
            );

            if ($servis_id) {
                $success = true;
            } else {
                $error = "Gagal menyimpan pengajuan servis.";
            }
        }
    }
}

/* ============================================================
   LIST KENDARAAN
   ============================================================ */
$kendaraan = $conn->query("
    SELECT * FROM kendaraan 
    WHERE status = 'Aktif'
    ORDER BY no_polisi
")->fetch_all(MYSQLI_ASSOC);

/* ============================================================
   RIWAYAT SERVIS
   ============================================================ */
$riwayat = $conn->query("
    SELECT s.*, k.no_polisi 
    FROM servis s 
    JOIN kendaraan k ON s.id_kendaraan = k.id
    WHERE s.id_user = $id_user
    ORDER BY s.tanggal DESC
")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Servis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/buttons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa;
        }

        .main-content {
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

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 5px solid #007bff;
        }

        .card-body {
            padding: 20px;
        }

        .card h5 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .alert {
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

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .btn {
            font-weight: 600;
            border-radius: 8px;
            font-size: 13px;
            padding: 10px 16px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
    </style>

    <script>
        function confirmDelete(id){
            if (confirm("Yakin ingin menghapus riwayat ini?")) {
                window.location = "ajukan_servis.php?hapus=" + id;
            }
        }
    </script>
</head>
<body>

<?php include "layout/sidebar.php"; ?>

<div class="main-content">
    <div class="header">
        <h2>Ajukan Servis Baru</h2>
    </div>

    <?php if ($success && isset($message_delete)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ⚠️ <?= $message_delete; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php elseif ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ Pengajuan servis berhasil dikirim.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ <?= $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FORM PENGAJUAN -->
    <form method="POST" enctype="multipart/form-data">

        <div class="card">
            <div class="card-body">
                <h5>Pilih Kendaraan</h5>
                <select class="form-select" name="id_kendaraan" required>
                    <option value="">-- Pilih Kendaraan --</option>
                    <?php foreach ($kendaraan as $k): ?>
                        <option value="<?= $k['id']; ?>"><?= $k['no_polisi']; ?> - <?= $k['merk']; ?> <?= $k['tipe']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Detail Servis</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Servis <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Servis <span class="text-danger">*</span></label>
                        <select class="form-select" name="jenis_servis" required>
                            <option value="">-- Pilih --</option>
                            <option value="Ganti Oli">Ganti Oli</option>
                            <option value="Servis Berkala">Servis Berkala</option>
                            <option value="Perbaikan Mesin">Perbaikan Mesin</option>
                            <option value="Perbaikan Elektronik">Perbaikan Elektronik</option>
                            <option value="Servis AC">Servis AC</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Biaya (Rp) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="biaya" min="1" required>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Upload Struk</h5>
                <input type="file" class="form-control" name="foto_struk" accept=".jpg,.jpeg,.png,.gif,.pdf">
                <small class="text-muted d-block mt-2">Format yang didukung: JPG, PNG, GIF, PDF</small>
            </div>
        </div>

        <button class="btn btn-primary" name="submit_servis">Ajukan Servis</button>
    </form>

    <hr class="my-4">

    <!-- RIWAYAT SERVIS -->
    <h3 style="font-size: 20px; color: #1a1a1a; margin-bottom: 20px; font-weight: 600;"> Riwayat Pengajuan Servis</h3>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>No Polisi</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Biaya</th>
            <th>Struk</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <tbody>
        <?php if (empty($riwayat)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding: 40px;">📭 Tidak ada riwayat servis</td></tr>

        <?php else: ?>
            <?php foreach ($riwayat as $r): ?>
                <tr>
                    <td><strong><?= $r['no_polisi']; ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($r['tanggal'])); ?></td>
                    <td><?= $r['jenis_servis']; ?></td>
                    <td>Rp <?= number_format($r['biaya']); ?></td>

                    <!-- STRUK -->
                    <td>
                        <?php if ($r['foto_struk']): ?>
                            <a href="../uploads/struk_servis/<?= $r['foto_struk']; ?>" target="_blank" class="btn btn-sm btn-info"> Lihat</a>
                        <?php else: ?> 
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>

                    <!-- STATUS -->
                    <td>
<?php if ($r['status_admin'] == 'PENDING'): ?>
    <span class="badge bg-warning text-dark">⏳ Pending</span>

<?php elseif ($r['status_admin'] == 'APPROVED'): ?>
    <span class="badge bg-success">✅ Disetujui</span>

<?php else: ?>
    <span class="badge bg-danger">❌ Ditolak</span>
<?php endif; ?>
</td>


                    <td>
                        <a href="cetak_servis.php?id=<?= $r['id']; ?>" target="_blank" class="btn btn-sm btn-success">🖨️ Cetak</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
