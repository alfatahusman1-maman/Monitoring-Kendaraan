<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin'){
    header("Location: ../index.php");
    exit;
}
require '../config.php';

// Ambil data tanda terima
$data = mysqli_query($conn, "SELECT t.*, u.nama 
    FROM tanda_terima t 
    JOIN users u ON t.id_user=u.id
    ORDER BY t.tanggal_serah DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Tanda Terima</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-content {
            margin-left: 250px; /* menyesuaikan lebar sidebar */
            padding: 20px;
            transition: all 0.3s;
        }
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary"> Data Tanda Terima</h3>
            <span class="text-muted"><?= date('d F Y'); ?></span>
        </div>

        <div class="card shadow-lg border-0">
            <div class="card-header bg-gradient bg-info text-white fw-semibold">
                Daftar Tanda Terima
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tandaTerimaTable" class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Jenis</th>
                                <th>ID Transaksi</th>
                                <th>Tanggal Serah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($data) > 0): ?>
                            <?php while($t = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['id']); ?></td>
                                <td><?= htmlspecialchars($t['nama']); ?></td>
                                <td><?= htmlspecialchars($t['jenis']); ?></td>
                                <td><?= htmlspecialchars($t['id_transaksi']); ?></td>
                                <td><?= htmlspecialchars(date('d-m-Y', strtotime($t['tanggal_serah']))); ?></td>
                                <td>
                                    <?php if($t['status'] == 'Selesai'): ?>
                                        <span class="badge bg-success px-3 py-2">✔ Selesai</span>
                                    <?php elseif($t['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark px-3 py-2">⏳ Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-3 py-2"><?= htmlspecialchars($t['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetail(<?= $t['id']; ?>)">👁 Lihat</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data tanda terima</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Suppress DataTables warnings
    $.fn.dataTable.ext.errMode = 'none';
    
    $(document).ready(function() {
        $('#tandaTerimaTable').DataTable({
            "pageLength": 10,
            "columnDefs": [
                { "orderable": false, "targets": 6 } // Disable sort pada kolom Aksi
            ],
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "›",
                    "previous": "‹"
                }
            },
            "bAutoWidth": false,
            "stripeClasses": []
        });
    });
    
    // Fungsi untuk melihat detail
    function viewDetail(id) {
        alert('Detail untuk ID: ' + id);
        // Bisa diganti dengan modal atau redirect
    }
</script>
</body>
</html>
