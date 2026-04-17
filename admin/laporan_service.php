<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

// Ambil parameter
$tahun = $_GET['tahun'];
$bulan_awal = $_GET['bulan_awal'];
$bulan_akhir = $_GET['bulan_akhir'];

// Query data biaya service
$query = "SELECT s.*, CONCAT(k.merk, ' ', k.tipe, ' (', k.no_polisi, ')') as nama_kendaraan 
          FROM servis s
          JOIN kendaraan k ON s.id_kendaraan = k.id
          WHERE YEAR(s.tanggal) = '$tahun' 
          AND MONTH(s.tanggal) BETWEEN '$bulan_awal' AND '$bulan_akhir'";
$result = mysqli_query($conn, $query);

// Buat HTML laporan
$html = "
<h3 style='text-align:center;'>Laporan Pengeluaran Biaya Service</h3>
<p style='text-align:center;'>Periode: $tahun (Bulan $bulan_awal s/d $bulan_akhir)</p>
<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<thead>
<tr>
<th>No</th>
<th>Tanggal</th>
<th>Nama Kendaraan</th>
<th>Biaya</th>
<th>Keterangan</th>
</tr>
</thead>
<tbody>";

$no = 1;
$total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $html .= "<tr>
        <td>{$no}</td>
        <td>{$row['tanggal']}</td>
        <td>{$row['nama_kendaraan']}</td>
        <td>Rp " . number_format($row['biaya'], 0, ',', '.') . "</td>
        <td>{$row['keterangan']}</td>
    </tr>";
    $total += $row['biaya'];
    $no++;
}

$html .= "<tr>
<td colspan='3'><b>Total</b></td>
<td colspan='2'><b>Rp " . number_format($total, 0, ',', '.') . "</b></td>
</tr>";

$html .= "</tbody></table>";

// Cetak PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_service.pdf", ["Attachment" => false]);
exit;
