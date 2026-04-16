<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';

// Tambah Admin/Keuangan
if (isset($_POST['tambah'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $role     = $_POST['role']; // Admin atau Keuangan

    // Password default sesuai role
    $plain_password = ($role == "Admin") ? "admin123" : "pegawai123";
    $password       = md5($plain_password);

    // Cek username sudah ada atau belum
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('❌ Username sudah dipakai!');</script>";
    } else {
        mysqli_query($conn, "INSERT INTO users (nama, username, password, role, plain_password) 
                             VALUES ('$nama','$username','$password','$role','$plain_password')");
        header("Location: kelola_admin.php");
        exit;
    }
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: kelola_admin.php");
    exit;
}

// Ambil data admin & keuangan
$data = mysqli_query($conn, "SELECT * FROM users WHERE role IN ('Admin','Keuangan') ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin & Keuangan</title>
    <style>
        body { margin:0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f9fafc; }
        .main-content { margin-left:240px; padding:20px; }
        h2,h3 { color:#2c3e50; }
        a { text-decoration:none; color:#3498db; }
        a:hover { color:#1d6fa5; }

        .card { background:#fff; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow:0 4px 8px rgba(0,0,0,0.08); }
        form input, form select, form button { width:100%; padding:10px; margin:6px 0 15px 0; border-radius:8px; border:1px solid #ddd; font-size:14px; }
        form button { background:#3498db; color:#fff; font-weight:bold; border:none; cursor:pointer; transition:0.3s; }
        form button:hover { background:#1d6fa5; }

        table { border-collapse:collapse; width:100%; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.05); }
        table th, table td { padding:12px 15px; text-align:left; }
        table th { background:#3498db; color:#fff; }
        table tr:nth-child(even) { background:#f9f9f9; }
        table tr:hover { background:#eef6fc; }

        .action-links a { margin-right:8px; padding:6px 10px; border-radius:6px; font-size:13px; color:#fff; }
        .action-links a.delete { background:#e74c3c; }
        .action-links a.delete:hover { background:#c0392b; }

        @media(max-width:768px){
            .main-content{ margin-left:0; }
            table,thead,tbody,th,td,tr{ display:block; width:100%; }
            table tr{ margin-bottom:12px; background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
            table td{ padding:10px; text-align:right; position:relative; }
            table td::before{ content:attr(data-label); position:absolute; left:10px; font-weight:bold; color:#2c3e50; }
            table th{ display:none; }
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="main-content">
    <h2> Kelola Admin & Keuangan</h2>
    <p>
        <a href="dashboard.php">⬅ Kembali Dashboard</a> | 
        <a href="../logout.php">Logout</a>
    </p>

    <div class="card">
        <h3>Tambah Admin/Keuangan</h3>
        <form method="POST">
            <label>Nama</label>
            <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>

            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>

            <label>Password</label>
            <input type="text" name="password" value="(Otomatis sesuai role)" readonly>

            <label>Role</label>
            <select name="role" required>
                <option value="Admin">Admin</option>
                <option value="Keuangan">Keuangan</option>
            </select>

            <button type="submit" name="tambah">➕ Tambah</button>
        </form>
    </div>

    <h3> Daftar Admin & Keuangan</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Password (Asli)</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($u = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td data-label="ID"><?php echo $u['id']; ?></td>
                <td data-label="Nama"><?php echo $u['nama']; ?></td>
                <td data-label="Username"><?php echo $u['username']; ?></td>
                <td data-label="Password"><?php echo $u['plain_password']; ?></td>
                <td data-label="Role"><?php echo $u['role']; ?></td>
                <td class="action-links">
                    <a href="?hapus=<?php echo $u['id']; ?>" class="delete" onclick="return confirm('Hapus akun ini?')">🗑 Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
