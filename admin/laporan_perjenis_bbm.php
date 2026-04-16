<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

$tahun = intval($_GET['tahun']);
$bulan_awal = intval($_GET['bulan_awal']);
$bulan_akhir = intval($_GET['bulan_akhir']);

// Cek kolom untuk jenis BBM
$kolom_jenis_bbm = '';
$columns = mysqli_query($conn, "SHOW COLUMNS FROM bbm");
while($col = mysqli_fetch_assoc($columns)){
    $col_name = strtolower($col['Field']);
    if(in_array($col_name, ['jenis_bbm','nama_bbm','tipe_bbm'])){
        $kolom_jenis_bbm = $col['Field'];
        break;
    }
}

if($kolom_jenis_bbm == ''){
    die("Kolom jenis BBM tidak ditemukan di tabel bbm!");
}

// Query total per jenis BBM
$query = "SELECT $kolom_jenis_bbm as jenis_bbm, SUM(liter) as total_liter, SUM(biaya) as total_biaya 
          FROM bbm 
          WHERE YEAR(tanggal) = '$tahun' 
          AND MONTH(tanggal) BETWEEN '$bulan_awal' AND '$bulan_akhir'
          GROUP BY $kolom_jenis_bbm";
$result = mysqli_query($conn, $query);

$html = "
<h3 style='text-align:center;'>Laporan Perjenis BBM</h3>
<p style='text-align:center;'>Periode: $tahun (Bulan $bulan_awal s/d $bulan_akhir)</p>
<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<thead>
<tr>
<th>No</th>
<th>Jenis BBM</th>
<th>Total Liter</th>
<th>Total Biaya</th>
</tr>
</thead>
<tbody>";

$no = 1;
$total = 0;
if(mysqli_num_rows($result) > 0){
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<tr>
            <td>{$no}</td>
            <td>{$row['jenis_bbm']}</td>
            <td>".number_format($row['total_liter'],2)." Liter</td>
            <td>Rp " . number_format($row['total_biaya'], 0, ',', '.') . "</td>
        </tr>";
        $total += $row['total_biaya'];
        $no++;
    }

    $html .= "<tr>
    <td colspan='3'><b>Total Keseluruhan</b></td>
    <td><b>Rp " . number_format($total, 0, ',', '.') . "</b></td>
    </tr>";
} else {
    $html .= "<tr><td colspan='4' style='text-align:center;'>Tidak ada data BBM untuk periode ini</td></tr>";
}

$html .= "</tbody></table>";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_perjenis_bbm.pdf", ["Attachment" => false]);
exit;
