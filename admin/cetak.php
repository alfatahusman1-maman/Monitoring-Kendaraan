<?php
require 'vendor/autoload.php'; // kalau pakai composer
// require 'dompdf/autoload.inc.php'; // kalau install manual

use Dompdf\Dompdf;

// Inisialisasi Dompdf
$dompdf = new Dompdf();

// HTML yang akan dikonversi ke PDF
$html = '
<h2 style="text-align:center;">Laporan Data Pegawai</h2>
<table border="1" width="100%" cellspacing="0" cellpadding="5">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NIP</th>
        <th>Jabatan</th>
    </tr>
    <tr>
        <td>1</td>
        <td>Andi</td>
        <td>123456</td>
        <td>Staff</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Budi</td>
        <td>789012</td>
        <td>Manager</td>
    </tr>
</table>
';

// Load HTML ke Dompdf
$dompdf->loadHtml($html);

// (Opsional) Atur ukuran dan orientasi kertas
$dompdf->setPaper('A4', 'portrait');

// Render PDF
$dompdf->render();

// Output ke browser
$dompdf->stream("laporan.pdf", array("Attachment" => false)); 
// kalau mau auto download ubah ke true
