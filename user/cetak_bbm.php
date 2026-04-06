<?php
require '../config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require '../vendor/autoload.php';

if (!isset($_GET['id'])) {
    die("ID BBM tidak ditemukan.");
}

$id = intval($_GET['id']);

// AMBIL DATA BBM
$q = mysqli_query($conn, "
    SELECT b.*, u.nama, k.no_polisi, k.merk, k.tipe
    FROM bbm b
    JOIN users u ON b.id_user = u.id
    JOIN kendaraan k ON b.id_kendaraan = k.id
    WHERE b.id = $id

");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Data tidak ditemukan!");
}

// === TEMPLATE HTML UNTUK PDF ===

// compute harga_liter and total_biaya safely
$harga_liter = null;
$total_biaya = null;
if (isset($data['harga_liter']) && $data['harga_liter']) {
    $harga_liter = $data['harga_liter'];
} elseif (!empty($data['liter']) && !empty($data['biaya'])) {
    // biaya stored as total; compute per-liter if possible
    $harga_liter = $data['biaya'] / max(1, floatval($data['liter']));
}
if (isset($data['total_biaya']) && $data['total_biaya']) {
    $total_biaya = $data['total_biaya'];
} elseif (isset($data['biaya'])) {
    $total_biaya = $data['biaya'];
}

// determine status label
$status_label = 'Menunggu';
if (!empty($data['status_keuangan']) && $data['status_keuangan'] === 'VALIDATED') {
    $status_label = 'Disetujui';
} elseif (!empty($data['status_keuangan']) && $data['status_keuangan'] === 'REJECTED') {
    $status_label = 'Ditolak Keuangan';
} elseif (!empty($data['status_admin']) && $data['status_admin'] === 'APPROVED') {
    $status_label = 'Disetujui Admin';
} elseif (!empty($data['status_admin']) && $data['status_admin'] === 'REJECTED') {
    $status_label = 'Ditolak Admin';
}

$html = '
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
}
.header {
    text-align: center;
    border-bottom: 2px solid black;
    padding-bottom: 8px;
    margin-bottom: 15px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
td {
    padding: 6px;
}
.table-bordered td, .table-bordered th {
    border: 1px solid #000;
    padding: 6px;
}
.title {
    text-align: center;
    margin-top: 10px;
    font-size: 16px;
    font-weight: bold;
}
.footer {
    margin-top: 40px;
    width: 100%;
    font-size: 12px;
}
.signature {
    text-align: right;
    margin-top: 60px;
}
</style>

<div class="header">
    <h2>PEMERINTAH PROVINSI JAWA TENGAH</h2>
    <h3>DINAS PERHUBUNGAN</h3>
    <small>Jl. Pemuda No.127, Kota Semarang</small>
</div>

<div class="title">FORM PENGAJUAN BAHAN BAKAR MINYAK (BBM)</div>

<table>
    <tr>
        <td><b>Tanggal Pengajuan</b></td>
        <td>: '.date("d-m-Y", strtotime($data['tanggal'])).'</td>
    </tr>
    <tr>
        <td><b>Nama Pengaju</b></td>
        <td>: '.$data['nama'].'</td>
    </tr>
    <tr>
        <td><b>No Polisi</b></td>
        <td>: '.$data['no_polisi'].'</td>
    </tr>
    <tr>
        <td><b>Merk / Type Kendaraan</b></td>
        <td>: '.$data['merk'].' / '.$data['tipe'].'</td>

    </tr>
</table>

<h4 style="margin-top:20px;">Detail Pengajuan:</h4>

<table class="table-bordered">
    <tr>
        <th>Jenis BBM</th>
        <th>Liter</th>
        <th>Biaya / Liter</th>
        <th>Total</th>
        <th>Status</th>
    </tr>
    <tr>
        <td>'.$data['jenis_bbm'].'</td>
        <td>'.$data['liter'].' Liter</td>
    <td>Rp '.number_format($harga_liter ?? 0, 0, ",", ".").'</td>
    <td>Rp '.number_format($total_biaya ?? 0, 0, ",", ".").'</td>
    <td><b>'.htmlspecialchars($status_label).'</b></td>
    </tr>
</table>

<div class="footer">
    <table width="100%">
        <tr>
            <td style="text-align:left;">
                Mengetahui,<br>
                Kepala Dinas Perhubungan<br><br><br><br>
                ___________________________
            </td>
            <td style="text-align:right;">
                Pemohon,<br><br><br><br>
                <b>'.$data['nama'].'</b>
            </td>
        </tr>
    </table>
</div>
';

// === GENERATE PDF ===
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("BBM_$id.pdf", ["Attachment" => false]);
exit;

?>
