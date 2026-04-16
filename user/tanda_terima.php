<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'User'){
    header("Location: ../index.php");
    exit;
}
require '../config.php';

$id_user = $_SESSION['user']['id'];

// Periksa apakah kolom id_kendaraan ada di tabel tanda_terima
$has_kendaraan_col = false;
$col_check = $conn->query("SHOW COLUMNS FROM tanda_terima LIKE 'id_kendaraan'");
if($col_check && $col_check->num_rows > 0){
    $has_kendaraan_col = true;
}

/* =========================
   PROSES HAPUS SEMUA RIWAYAT
   ========================= */
if(isset($_POST['hapus_semua'])){
    mysqli_query($conn, "DELETE FROM tanda_terima WHERE id_user = $id_user");
    header("Location: tanda_terima.php");
    exit;
}

/* =========================
   PROSES BUAT TANDA TERIMA
   ========================= */
if(isset($_POST['buat'])){
    $jenis = $_POST['jenis'];
    $id_transaksi = intval($_POST['id_transaksi']);
    $tanggal_serah = $_POST['tanggal_serah'];

    $id_kendaraan = $has_kendaraan_col ? intval($_POST['id_kendaraan'] ?? 0) : 0;

    if($jenis == 'Servis'){
        $cek = mysqli_query($conn, "SELECT * FROM servis WHERE id=$id_transaksi AND status_admin='APPROVED'");
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM bbm WHERE id=$id_transaksi AND status_admin='APPROVED'");
    }

    if(mysqli_num_rows($cek) > 0){
        $status = 'Belum Diverifikasi';

        if($has_kendaraan_col){
            $stmt = mysqli_prepare($conn, "INSERT INTO tanda_terima (id_user, jenis, id_transaksi, id_kendaraan, tanggal_serah, status) 
                                           VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "isiiss", $id_user, $jenis, $id_transaksi, $id_kendaraan, $tanggal_serah, $status);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO tanda_terima (id_user, jenis, id_transaksi, tanggal_serah, status) 
                                           VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "isiss", $id_user, $jenis, $id_transaksi, $tanggal_serah, $status);
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: tanda_terima.php");
        exit;

    } else {
        $error = "⚠️ Transaksi belum disetujui admin.";
    }
}

/* =========================
   AMBIL DATA
   ========================= */
$servis_disetujui = mysqli_query($conn, 
    "SELECT s.*, k.no_polisi, s.id_kendaraan 
     FROM servis s 
     JOIN kendaraan k ON s.id_kendaraan=k.id 
     WHERE s.id_user=$id_user AND s.status_admin='APPROVED'");

$bbm_disetujui = mysqli_query($conn, 
    "SELECT b.*, k.no_polisi, b.id_kendaraan 
     FROM bbm b 
     JOIN kendaraan k ON b.id_kendaraan=k.id 
     WHERE b.id_user=$id_user AND b.status_admin='APPROVED'");

$kendaraan_list = $conn->query("SELECT id, no_polisi FROM kendaraan WHERE status='Aktif' ORDER BY no_polisi")
                       ->fetch_all(MYSQLI_ASSOC);

if($has_kendaraan_col){
    $riwayat = mysqli_query($conn,
        "SELECT t.*, COALESCE(k1.no_polisi, k2.no_polisi) AS no_polisi
         FROM tanda_terima t
         LEFT JOIN kendaraan k1 ON t.id_kendaraan=k1.id
         LEFT JOIN servis s ON t.jenis='Servis' AND t.id_transaksi=s.id
         LEFT JOIN bbm b ON t.jenis='BBM' AND t.id_transaksi=b.id
         LEFT JOIN kendaraan k2 ON (s.id_kendaraan=k2.id OR b.id_kendaraan=k2.id)
         WHERE t.id_user=$id_user
         ORDER BY t.tanggal_serah DESC");
} else {
    $riwayat = mysqli_query($conn,
        "SELECT t.*, k.no_polisi 
         FROM tanda_terima t
         LEFT JOIN servis s ON t.jenis='Servis' AND t.id_transaksi=s.id
         LEFT JOIN bbm b ON t.jenis='BBM' AND t.id_transaksi=b.id
         LEFT JOIN kendaraan k ON (s.id_kendaraan=k.id OR b.id_kendaraan=k.id)
         WHERE t.id_user=$id_user
         ORDER BY t.tanggal_serah DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
        .main-content { margin-left: 240px; padding: 20px; }
        .card { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php include 'layout/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">

        <h2 class="mb-4"> Buat Tanda Terima</h2>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- FORM -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Form Tanda Terima</div>
            <div class="card-body">

                <form method="POST" class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" id="jenis" class="form-select" required onchange="showTransaksi(this.value)">
                            <option value="">Pilih</option>
                            <option value="Servis">Servis</option>
                            <option value="BBM">BBM</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Transaksi</label>
                        <select name="id_transaksi" id="id_transaksi" class="form-select" required>
                            <option value="">Pilih jenis dahulu</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kendaraan</label>
                        <select name="id_kendaraan" id="id_kendaraan" class="form-select" required>
                            <option value="">-- Pilih Kendaraan --</option>
                            <?php foreach($kendaraan_list as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= $k['no_polisi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Serah</label>
                        <input type="date" name="tanggal_serah" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-md-12 text-end">
                        <button class="btn btn-success px-4" name="buat">Buat Tanda Terima</button>
                    </div>

                </form>

            </div>
        </div>

        <!-- RIWAYAT -->
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <span>Riwayat Tanda Terima</span>

    
                </form>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jenis</th>
                            <th>No Polisi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while($r = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= $r['jenis'] ?></td>
                            <td><?= $r['no_polisi'] ?></td>
                            <td><?= date("d-m-Y", strtotime($r['tanggal_serah'])) ?></td>
                            <td>
                                <?php if($r['status'] == 'Belum Diverifikasi'): ?>
                                    <span class="badge bg-warning text-dark">Belum Diverifikasi</span>
                                <?php elseif($r['status']=='Diverifikasi' || $r['status']=='Disetujui'): ?>
                                    <span class="badge bg-success">Disetujui</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $r['status'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</div>

<script>
const servis = <?php
$arr=[]; while($s=mysqli_fetch_assoc($servis_disetujui)) $arr[]=$s; echo json_encode($arr);
?>;
const bbm = <?php
$arr=[]; while($b=mysqli_fetch_assoc($bbm_disetujui)) $arr[]=$b; echo json_encode($arr);
?>;

function showTransaksi(jenis){
    let data = jenis === 'Servis' ? servis : bbm;
    let select = document.getElementById('id_transaksi');
    select.innerHTML = '<option value="">Pilih Transaksi</option>';

    data.forEach(d=>{
        let opt = document.createElement('option');
        opt.value = d.id;
        opt.text = 'ID ' + d.id + ' | ' + d.no_polisi;
        opt.dataset.kendaraan = d.id_kendaraan;
        select.add(opt);
    });
}
</script>

</body>
</html>
