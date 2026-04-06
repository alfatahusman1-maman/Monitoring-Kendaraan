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
   DELETE PENGAJUAN BBM
   ============================================================ */
if (isset($_GET["hapus"])) {
    $hapus_id = intval($_GET["hapus"]);

    // Cek apakah data milik user ini
    $cek = $conn->query("SELECT foto_struk FROM bbm WHERE id = $hapus_id AND id_user = $id_user");

    if ($cek && $cek->num_rows > 0) {
        $data = $cek->fetch_assoc();

        // Hapus file struk
        if (!empty($data["foto_struk"])) {
            $file_path = "../uploads/struk_bbm/" . $data["foto_struk"];
            if (file_exists($file_path)) unlink($file_path);
        }

        // Hapus data database
        $conn->query("DELETE FROM bbm WHERE id = $hapus_id");

        $success = "Pengajuan BBM berhasil dihapus.";
    } else {
        $error = "Data tidak ditemukan atau bukan milik Anda.";
    }
}

/* ============================================================
   HANDLE SUBMIT BBM
   ============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_bbm"])) {

    $id_kendaraan = intval($_POST["id_kendaraan"] ?? 0);
    $tanggal = $_POST["tanggal"] ?? "";
    $jenis_bbm = $_POST["jenis_bbm"] ?? "";
    $liter = floatval($_POST["liter"] ?? 0);
    $biaya = floatval($_POST["biaya"] ?? 0);

    if (empty($tanggal) || empty($jenis_bbm) || $liter <= 0 || $biaya <= 0 || $id_kendaraan <= 0) {
        $error = "Semua field harus diisi dengan benar.";
    } else {

        /* ============================================================
           UPLOAD STRUK
           ============================================================ */
        $foto_struk = null;

        if (!empty($_FILES["foto_struk"]["name"])) {
            $target_dir = "../uploads/struk_bbm/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            $file_ext = strtolower(pathinfo($_FILES["foto_struk"]["name"], PATHINFO_EXTENSION));
            $allowed_ext = ["jpg", "jpeg", "png", "gif", "pdf"];

            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Format file tidak didukung.";
            } else {
                $unique_name = time() . "_" . rand(1000, 9999) . "." . $file_ext;
                $target_file = $target_dir . $unique_name;

                if (move_uploaded_file($_FILES["foto_struk"]["tmp_name"], $target_file)) {
                    $foto_struk = $unique_name;
                } else {
                    $error = "Gagal upload file.";
                }
            }
        }

        /* ============================================================
           SIMPAN KE DATABASE
           ============================================================ */
        if (empty($error)) {
            $bbm_id = createBBMSubmission($id_user, $id_kendaraan, $tanggal, $jenis_bbm, $liter, $biaya, $foto_struk);

            if ($bbm_id) {
                $success = "Pengajuan BBM berhasil dikirim.";
            } else {
                $error = "Gagal menyimpan pengajuan.";
            }
        }
    }
}

/* ============================================================
   DATA KENDARAAN
   ============================================================ */
$kendaraan = $conn->query("SELECT * FROM kendaraan WHERE status='Aktif' ORDER BY no_polisi")
                  ->fetch_all(MYSQLI_ASSOC);

/* ============================================================
   RIWAYAT USER
   ============================================================ */
$riwayat = $conn->query("
    SELECT b.*, k.no_polisi
    FROM bbm b
    JOIN kendaraan k ON b.id_kendaraan = k.id
    WHERE b.id_user = $id_user
    ORDER BY b.tanggal DESC
")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan BBM</title>
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
</head>
<body>

<?php include "layout/sidebar.php"; ?>

<div class="main-content">
    <div class="header">
        <h2>Ajukan BBM Baru</h2>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" enctype="multipart/form-data">
        <div class="card">
            <div class="card-body">
                <h5>Pilih Kendaraan</h5>

                <select class="form-select" name="id_kendaraan" required>
                    <option value="">-- Pilih Kendaraan --</option>
                    <?php foreach ($kendaraan as $k): ?>
                        <option value="<?= $k['id'] ?>">
                            <?= $k['no_polisi'] ?> - <?= $k['merk'] ?> <?= $k['tipe'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Detail BBM</h5>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis BBM</label>
                        <select class="form-select" name="jenis_bbm" required>
                            <option value="">-- Pilih --</option>
                            <option value="Pertalite">Pertalite</option>
                            <option value="Pertamax">Pertamax</option>
                            <option value="Solar">Solar</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Liter</label>
                        <input type="number" class="form-control" name="liter" step="0.01" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Biaya (Rp)</label>
                        <input type="number" class="form-control" name="biaya" step="0.01" required>
                    </div>
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

        <button type="submit" name="submit_bbm" class="btn btn-primary">Ajukan BBM</button>
    </form>

    <hr class="my-4">

    <!-- RIWAYAT -->
    <h3 style="font-size: 20px; color: #1a1a1a; margin-bottom: 20px; font-weight: 600;"> Riwayat Pengajuan BBM</h3>

    <table class="table table-hover">
        <thead>
        <tr>
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
        <?php if (empty($riwayat)): ?>
            <tr><td colspan="9" class="text-center text-muted" style="padding: 40px;">📭 Belum ada pengajuan BBM</td></tr>
        <?php else: ?>
            <?php foreach ($riwayat as $r): ?>
            <tr>
                <td><strong><?= htmlspecialchars($r['no_polisi']); ?></strong></td>
                <td><?= date('d/m/Y', strtotime($r['tanggal'])); ?></td>
                <td><?= htmlspecialchars($r['jenis_bbm']); ?></td>
                <td><?= $r['liter']; ?> L</td>
                <td>Rp <?= number_format($r['biaya']); ?></td>

                <td>
                    <?php if ($r['foto_struk']): ?>
                        <a href="../uploads/struk_bbm/<?= htmlspecialchars($r['foto_struk']); ?>" 
                           target="_blank" class="btn btn-sm btn-info"> Lihat</a>
                    <?php else: ?> 
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php 
                        $status_admin = $r['status_admin'] ?? ($r['status'] ?? 'PENDING');
                        
                        if($status_admin == "PENDING" || $status_admin == "Pending"){
                            echo "<span class='badge bg-warning text-dark'>⏳ Pending</span>";
                        } elseif($status_admin == "APPROVED" || $status_admin == "Disetujui"){
                            echo "<span class='badge bg-success'>✅ Disetujui</span>";
                        } else {
                            echo "<span class='badge bg-danger'>❌ Ditolak</span>";
                        }
                    ?>
                </td>

                

                <td>
                    <a href="cetak_bbm.php?id=<?= $r['id']; ?>" 
                       class="btn btn-sm btn-success" target="_blank">🖨️ Cetak</a>

                    <?php if($r['status_admin'] == "PENDING" || $r['status'] == "Pending"): ?>
                        
                    <?php endif; ?>
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
