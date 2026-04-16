<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <h4> Data Laporan</h4>
    <p><?= date('d F Y'); ?></p>

    <!-- Laporan Biaya Service -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">Laporan Pengeluaran Biaya Service</div>
        <div class="card-body">
            <form action="laporan_service.php" method="get" target="_blank" class="row g-2">
                <div class="col-md-3">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control">
                        <?php for ($i = date('Y'); $i >= 2020; $i--) { ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bulan Awal</label>
                    <select name="bulan_awal" class="form-control">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bulan Akhir</label>
                    <select name="bulan_akhir" class="form-control">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">📄 Cetak PDF</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Laporan Kendaraan Perjenis-BBM -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">Laporan Kendaraan Perjenis-BBM</div>
        <div class="card-body">
            <form action="laporan_bbm.php" method="get" target="_blank" class="row g-2">
                <div class="col-md-3">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control">
                        <option value="">Pilih Tahun</option>
                        <?php for ($i = date('Y'); $i >= 2020; $i--) { ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bulan Awal</label>
                    <select name="bulan_awal" class="form-control">
                        <option value="">Bulan Awal</option>
                        <?php for ($b = 1; $b <= 12; $b++) { ?>
                            <option value="<?= $b ?>"><?= date("F", mktime(0, 0, 0, $b, 10)); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bulan Akhir</label>
                    <select name="bulan_akhir" class="form-control">
                        <option value="">Bulan Akhir</option>
                        <?php for ($b = 1; $b <= 12; $b++) { ?>
                            <option value="<?= $b ?>"><?= date("F", mktime(0, 0, 0, $b, 10)); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">📄 Cetak PDF</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Laporan Perjenis-BBM -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">Laporan Perjenis-BBM</div>
        <div class="card-body">
            <form action="laporan_perjenis_bbm.php" method="get" target="_blank" class="row g-2">
                <div class="col-md-3">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control">
                        <option value="">Pilih Tahun</option>
                        <?php for ($i = date('Y'); $i >= 2020; $i--) { ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bulan Awal</label>
                    <select name="bulan_awal" class="form-control">
                        <option value="">Bulan Awal</option>
                        <?php for ($b = 1; $b <= 12; $b++) { ?>
                            <option value="<?= $b ?>"><?= date("F", mktime(0, 0, 0, $b, 10)); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bulan Akhir</label>
                    <select name="bulan_akhir" class="form-control">
                        <option value="">Bulan Akhir</option>
                        <?php for ($b = 1; $b <= 12; $b++) { ?>
                            <option value="<?= $b ?>"><?= date("F", mktime(0, 0, 0, $b, 10)); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">📄 Cetak PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
