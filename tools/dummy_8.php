<?php
require_once dirname(__DIR__) . '/config.php';

echo "<h3>Membuat 8 Data Dummy...</h3>";

// 1. Data Kendaraan (8 Unit)
$kendaraans = [
    ['B 1010 ABC', 'Toyota', 'Fortuner', 2022, 'Roda Empat', 'Baik', 'Aktif'],
    ['B 2020 DEF', 'Honda', 'Vario 160', 2023, 'Roda Dua', 'Baik', 'Aktif'],
    ['B 3030 GHI', 'Toyota', 'Innova Zenix', 2023, 'Roda Empat', 'Baik', 'Aktif'],
    ['B 4040 JKL', 'Yamaha', 'XMAX', 2022, 'Roda Dua', 'Baik', 'Aktif'],
    ['B 5050 MNO', 'Mitsubishi', 'Pajero Sport', 2021, 'Roda Empat', 'Kurang Baik', 'Aktif'],
    ['B 6060 PQR', 'Honda', 'PCX 160', 2023, 'Roda Dua', 'Baik', 'Aktif'],
    ['B 7070 STU', 'Suzuki', 'Ertiga', 2020, 'Roda Empat', 'Baik', 'Aktif'],
    ['B 8080 VWX', 'Honda', 'Beat Street', 2021, 'Roda Dua', 'Baik', 'Aktif']
];

foreach ($kendaraans as $k) {
    mysqli_query($conn, "INSERT INTO kendaraan (no_polisi, merk, tipe, tahun, jenis, kondisi, status) VALUES ('{$k[0]}', '{$k[1]}', '{$k[2]}', {$k[3]}, '{$k[4]}', '{$k[5]}', '{$k[6]}')");
}
echo "<p>✅ 8 Kendaraan baru berhasil ditambahkan.</p>";

// Ambil ID kendaraan terakhir
$resK = mysqli_query($conn, "SELECT id FROM kendaraan ORDER BY id DESC LIMIT 8");
$idsK = [];
while($row = mysqli_fetch_assoc($resK)) $idsK[] = $row['id'];

// Ambil 1 user (pegawai)
$resU = mysqli_query($conn, "SELECT id FROM users WHERE role='User' LIMIT 1");
$userRow = mysqli_fetch_assoc($resU);
$idU = $userRow ? $userRow['id'] : 1;

// 2. Data Servis (8 Record)
foreach ($idsK as $idK) {
    $tgl = date('Y-m-d');
    mysqli_query($conn, "INSERT INTO servis (id_user, id_kendaraan, tanggal, jenis_servis, biaya, status_admin, status_keuangan) 
                         VALUES ($idU, $idK, '$tgl', 'Service Rutin', 500000, 'PENDING', 'PENDING')");
}
echo "<p>✅ 8 Data Servis (Pending) berhasil ditambahkan.</p>";

// 3. Data BBM (8 Record)
foreach ($idsK as $idK) {
    $tgl = date('Y-m-d');
    mysqli_query($conn, "INSERT INTO bbm (id_user, id_kendaraan, tanggal, jenis_bbm, liter, biaya, status_admin, status_keuangan) 
                         VALUES ($idU, $idK, '$tgl', 'Pertalite', 10, 100000, 'PENDING', 'PENDING')");
}
echo "<p>✅ 8 Data BBM (Pending) berhasil ditambahkan.</p>";

echo "<h4>🚀 Selesai! Aplikasi Anda sudah memiliki 8 data baru yang siap direview.</h4>";
?>
