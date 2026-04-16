<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';

// Proses Tambah Pegawai
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $passwordHash = md5($password);
    $role     = 'User'; // otomatis pegawai

    // Cek username sudah ada atau belum
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('❌ Username sudah digunakan!');</script>";
    } else {
        // simpan ke tabel users
        mysqli_query($conn, "INSERT INTO users (nama, username, password, password_text, role) 
                             VALUES ('$nama','$username','$passwordHash','$password','$role')");
        $idUser = mysqli_insert_id($conn);

        // otomatis buat data pegawai di tabel pegawai
        mysqli_query($conn, "INSERT INTO pegawai (id, nama) VALUES ('$idUser', '$nama')");

        // otomatis buat record kosong di tabel pegawai_detail
        mysqli_query($conn, "INSERT INTO pegawai_detail (id_pegawai) VALUES ('$idUser')");

        header("Location: kelola_pegawai.php");
        exit;
    }
}

// Ambil data semua pegawai
$dataPegawai = mysqli_query($conn, "SELECT * FROM users WHERE role='User'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .main-content {
            margin-left: 250px; /* lebar sidebar */
            padding: 20px;
            transition: all 0.3s;
        }
        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary"> Kelola Pegawai</h3>
            <span class="text-muted"><?= date('d F Y'); ?></span>
        </div>

        <!-- Form Tambah Pegawai -->
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-info text-white fw-semibold">
                ➕ Tambah Pegawai Baru
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Pegawai</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" name="tambah" class="btn btn-success px-4">
                            <i class="bi bi-plus-circle"></i> Tambah Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Pegawai -->
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white fw-semibold">
                 Daftar Pegawai
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="pegawaiTable" class="table table-striped table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Password Asli</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = mysqli_fetch_assoc($dataPegawai)): ?>
                            <tr>
                                <td><?= $p['id']; ?></td>
                                <td><?= htmlspecialchars($p['nama']); ?></td>
                                <td><?= htmlspecialchars($p['username']); ?></td>
                                <td><?= htmlspecialchars($p['password_text']); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= $p['role']; ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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
        $('#pegawaiTable').DataTable({
            "pageLength": 5,
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
            }
        });
    });
</script>
</body>
</html>
