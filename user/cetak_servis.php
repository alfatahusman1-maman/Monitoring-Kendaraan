<?php
require '../config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require '../vendor/autoload.php';

if (!isset($_GET['id'])) {
    die("ID Servis tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data servis
$q = mysqli_query($conn, "
    SELECT s.*, u.nama, k.no_polisi, k.merk, k.tipe
    FROM servis s
    JOIN users u ON s.id_user = u.id
    JOIN kendaraan k ON s.id_kendaraan = k.id
    WHERE s.id = $id

");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Data tidak ditemukan!");
}

// prepare values safely
$biaya = isset($data['biaya']) ? floatval($data['biaya']) : 0;
$tanggal = !empty($data['tanggal']) ? date('d-m-Y', strtotime($data['tanggal'])) : '-';

// status mapping
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

// optionally embed struk image if exists and is an image
$struk_html = '-';
if (!empty($data['foto_struk'])) {
    $path = __DIR__ . '/../uploads/struk_servis/' . $data['foto_struk'];
    if (file_exists($path)) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $img = base64_encode(file_get_contents($path));
            $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/'.$ext;
            $struk_html = "<img src=\"data:$mime;base64,$img\" style=\"max-width:300px;\"/>";
        } else {
            $struk_html = '<a href="/uploads/struk_servis/'.htmlspecialchars($data['foto_struk']).'">Lihat Struk</a>';
        }
    }
}

$html = '<style>
body{font-family:Arial; font-size:12px}
.header{text-align:center;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:15px}
table{width:100%;border-collapse:collapse;margin-top:15px}
td{padding:6px}
.table-bordered td,.table-bordered th{border:1px solid #000;padding:6px}
.title{text-align:center;margin-top:10px;font-size:16px;font-weight:bold}
</style>';

// header
$html .= '<div class="header"><h2>PEMERINTAH PROVINSI JAWA TENGAH</h2><h3>DINAS PERHUBUNGAN</h3><small>Jl. Pemuda No.127, Kota Semarang</small></div>';

$html .= '<div class="title">FORM PENGAJUAN SERVIS KENDARAAN</div>';

$html .= '<table>';
$html .= '<tr><td><b>Tanggal Pengajuan</b></td><td>: '.htmlspecialchars($tanggal).'</td></tr>';
$html .= '<tr><td><b>Nama Pengaju</b></td><td>: '.htmlspecialchars($data['nama']).'</td></tr>';
$html .= '<tr><td><b>No Polisi</b></td><td>: '.htmlspecialchars($data['no_polisi']).'</td></tr>';
$html .= '<tr><td><b>Merk / Type Kendaraan</b></td><td>: '.htmlspecialchars($data['merk']).' / '.htmlspecialchars($data['tipe']).'</td></tr>';
$html .= '</table>';

$html .= '<h4 style="margin-top:20px;">Detail Pengajuan:</h4>';
$html .= '<table class="table-bordered"><tr><th>Jenis Servis</th><th>Biaya</th><th>Status</th></tr>';
$html .= '<tr><td>'.htmlspecialchars($data['jenis_servis'] ?? '').'</td><td>Rp '.number_format($biaya, 0, ",", ".").'</td><td><b>'.htmlspecialchars($status_label).'</b></td></tr>';
$html .= '</table>';

$html .= '<h4 style="margin-top:20px;">Struk:</h4>';
$html .= $struk_html;

$html .= '<div style="margin-top:40px;width:100%"><table width="100%"><tr><td style="text-align:left;">Mengetahui,<br>Kepala Dinas Perhubungan<br><br><br>___________________________</td><td style="text-align:right;">Pemohon,<br><br><br><b>'.htmlspecialchars($data['nama']).'</b></td></tr></table></div>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Servis_$id.pdf", ["Attachment" => false]);
exit;

?>
