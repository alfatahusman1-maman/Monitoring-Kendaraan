<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';

// Handle query errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Hitung data keseluruhan
$jmlKendaraan = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kendaraan"))[0];
$jmlPegawai   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='User'"))[0];
$jmlServis    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM servis"))[0];
$jmlBBM       = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bbm"))[0];
$jmlAdmin     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role IN ('Admin','Keuangan')"))[0];

// Data untuk chart lebih detail - Servis per bulan
try {
    $servisPerBulan = mysqli_query($conn, "SELECT YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, COUNT(*) as total FROM servis GROUP BY YEAR(tanggal), MONTH(tanggal) ORDER BY tahun DESC, bulan ASC LIMIT 12");
} catch (Exception $e) {
    $servisPerBulan = mysqli_query($conn, "SELECT COUNT(*) as total FROM servis");
}
$dataBulan = [];
$dataServis = [];
while ($row = mysqli_fetch_assoc($servisPerBulan)) {
    $namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $dataBulan[] = $namaBulan[(int)$row['bulan'] ?? 1];
    $dataServis[] = $row['total'];
}

// Data status kendaraan (aktif/tidak aktif)
$kendaraanAktif = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM kendaraan WHERE status='Aktif'"))[0] ?? 0;
$kendaraanNonaktif = $jmlKendaraan - $kendaraanAktif;

// Data tipe kendaraan
try {
    $tipeKendaraan = mysqli_query($conn, "SELECT tipe, COUNT(*) as total FROM kendaraan WHERE tipe IS NOT NULL GROUP BY tipe ORDER BY total DESC");
} catch (Exception $e) {
    $tipeKendaraan = mysqli_query($conn, "SELECT tipe, COUNT(*) as total FROM kendaraan GROUP BY tipe HAVING tipe IS NOT NULL ORDER BY total DESC");
}
$tipeName = [];
$tipeCount = [];
while ($row = mysqli_fetch_assoc($tipeKendaraan)) {
    $tipeName[] = $row['tipe'];
    $tipeCount[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .content {
            padding: 20px;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 8px 16px rgba(0, 123, 255, 0.2);
        }
        .header h2 {
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 5px solid;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        .stat-card h6 {
            font-size: 13px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        .stat-card i {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 40px;
            opacity: 0.1;
        }
        .stat-primary { border-left-color: #007bff; }
        .stat-success { border-left-color: #28a745; }
        .stat-warning { border-left-color: #ffc107; }
        .stat-danger { border-left-color: #dc3545; }
        .stat-info { border-left-color: #17a2b8; }
        
        .chart-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 992px) {
            .chart-section {
                grid-template-columns: 1fr;
            }
        }
        .chart-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .chart-box h5 {
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .chart-box canvas {
            max-height: 300px;
        }
        .flowchart-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
        .flowchart-box h5 {
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .flow-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .flow-item {
            flex: 1;
            min-width: 140px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
            position: relative;
        }
        .flow-item i {
            display: block;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .flow-arrow {
            flex: 0 0 30px;
            text-align: center;
            font-size: 20px;
            color: #007bff;
            font-weight: bold;
        }
        .info-badge {
            display: inline-block;
            background: #e7f3ff;
            color: #007bff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <!-- Header -->
    <div class="header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h2>
        <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['user']['nama']); ?></strong>! Kelola semua data kendaraan Anda dengan mudah.</p>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-primary">
                <h6><i class="fas fa-car"></i> Kendaraan</h6>
                <h3><?php echo $jmlKendaraan; ?></h3>
                <i class="fas fa-car"></i>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-success">
                <h6><i class="fas fa-users"></i> Pegawai</h6>
                <h3><?php echo $jmlPegawai; ?></h3>
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-warning">
                <h6><i class="fas fa-cog"></i> Servis</h6>
                <h3><?php echo $jmlServis; ?></h3>
                <i class="fas fa-cog"></i>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-danger">
                <h6><i class="fas fa-gas-pump"></i> BBM</h6>
                <h3><?php echo $jmlBBM; ?></h3>
                <i class="fas fa-gas-pump"></i>
            </div>
        </div>
    </div>

    <!-- Flowchart Sistem -->
    <div class="flowchart-box">
        <h5><i class="fas fa-sitemap"></i> Alur Kerja Sistem Monitoring Kendaraan</h5>
        <div class="flow-container">
            <div class="flow-item">
                <i class="fas fa-car"></i>
                <div>Kendaraan</div>
                <span class="info-badge"><?php echo $jmlKendaraan; ?> Unit</span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="flow-item" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                <i class="fas fa-users"></i>
                <div>Pegawai</div>
                <span class="info-badge" style="background: #e8f5e9; color: #28a745;"><?php echo $jmlPegawai; ?> Orang</span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="flow-item" style="background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);">
                <i class="fas fa-cog"></i>
                <div>Servis & BBM</div>
                <span class="info-badge" style="background: #ffebee; color: #dc3545;">S:<?php echo $jmlServis; ?> B:<?php echo $jmlBBM; ?></span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="flow-item" style="background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%);">
                <i class="fas fa-check-circle"></i>
                <div>Laporan</div>
                <span class="info-badge" style="background: #e0f2f1; color: #17a2b8;">Real-time</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="chart-section">
        <div class="chart-box">
            <h5><i class="fas fa-chart-bar"></i> Statistik Data Keseluruhan</h5>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-box">
            <h5><i class="fas fa-chart-pie"></i> Distribusi Tipe Kendaraan</h5>
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <div class="chart-section">
        <div class="chart-box">
            <h5><i class="fas fa-chart-line"></i> Tren Servis Per Bulan</h5>
            <canvas id="lineChart"></canvas>
        </div>
        <div class="chart-box">
            <h5><i class="fas fa-chart-doughnut"></i> Status Kendaraan</h5>
            <canvas id="doughnutChart"></canvas>
        </div>
    </div>
</div>

<script>
    // Bar Chart - Statistik Data Keseluruhan
    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Kendaraan', 'Pegawai', 'Servis', 'BBM'],
            datasets: [{
                label: 'Jumlah Data',
                data: [
                    <?php echo $jmlKendaraan; ?>, 
                    <?php echo $jmlPegawai; ?>, 
                    <?php echo $jmlServis; ?>, 
                    <?php echo $jmlBBM; ?>
                ],
                backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107'],
                borderColor: ['#0056b3', '#1e7e34', '#a71d2a', '#e0a800'],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: { 
                legend: { 
                    display: true,
                    labels: { font: { size: 12, weight: 'bold' } }
                },
                tooltip: { 
                    enabled: true,
                    backgroundColor: 'rgba(0, 123, 255, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' }
                }
            }
        }
    });

    // Pie Chart - Distribusi Tipe Kendaraan
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($tipeName); ?>,
            datasets: [{
                data: <?php echo json_encode($tipeCount); ?>,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'],
                borderColor: '#fff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: { font: { size: 12, weight: 'bold' }, padding: 15 }
                },
                tooltip: { 
                    backgroundColor: 'rgba(0, 123, 255, 0.8)',
                    padding: 12
                }
            }
        }
    });

    // Line Chart - Tren Servis Per Bulan
    const ctxLine = document.getElementById('lineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(empty($dataBulan) ? ['Jan', 'Feb', 'Mar'] : $dataBulan); ?>,
            datasets: [{
                label: 'Jumlah Servis',
                data: <?php echo json_encode(empty($dataServis) ? [0, 0, 0] : $dataServis); ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#dc3545',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { 
                    display: true,
                    labels: { font: { size: 12, weight: 'bold' } }
                },
                tooltip: { 
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' }
                }
            }
        }
    });

    // Doughnut Chart - Status Kendaraan
    const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Tidak Aktif'],
            datasets: [{
                data: [<?php echo $kendaraanAktif; ?>, <?php echo $kendaraanNonaktif; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderColor: '#fff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: { font: { size: 12, weight: 'bold' }, padding: 15 }
                },
                tooltip: { 
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                }
            }
        }
    });
</script>

</body>
</html>
