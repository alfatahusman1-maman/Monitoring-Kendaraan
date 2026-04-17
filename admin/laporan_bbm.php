<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

$tahun = $_GET['tahun'];
$bulan_awal = $_GET['bulan_awal'];
$bulan_akhir = $_GET['bulan_akhir'];

// Query data BBM kendaraan
$query = "SELECT b.*, CONCAT(k.merk, ' ', k.tipe, ' (', k.no_polisi, ')') as nama_kendaraan 
          FROM bbm b
          JOIN kendaraan k ON b.id_kendaraan = k.id
          WHERE YEAR(b.tanggal) = '$tahun' 
          AND MONTH(b.tanggal) BETWEEN '$bulan_awal' AND '$bulan_akhir'";
$result = mysqli_query($conn, $query);

$html = "
<h3 style='text-align:center;'>Laporan Kendaraan Perjenis-BBM</h3>
<p style='text-align:center;'>Periode: $tahun (Bulan $bulan_awal s/d $bulan_akhir)</p>
<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<thead>
<tr>
<th>No</th>
<th>Tanggal</th>
<th>Nama Kendaraan</th>
<th>Jenis BBM</th>
<th>Jumlah (Liter)</th>
<th>Biaya</th>
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
        <td>{$row['jenis_bbm']}</td>
        <td>{$row['liter']}</td>
        <td>Rp " . number_format($row['biaya'], 0, ',', '.') . "</td>
    </tr>";
    $total += $row['biaya'];
    $no++;
}

$html .= "<tr>
<td colspan='5'><b>Total</b></td>
<td><b>Rp " . number_format($total, 0, ',', '.') . "</b></td>
</tr>";

$html .= "</tbody></table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_bbm.pdf", ["Attachment" => false]);
exit;
