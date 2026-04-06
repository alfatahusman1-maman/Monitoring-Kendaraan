<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';

$id = $_GET['id'];
$pegawai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));

// Update data
if (isset($_POST['update'])) {
    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $role     = $_POST['role'];

    // Jika password diisi, update juga
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username', password='$password', role='$role' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id=$id");
    }
    header("Location: kelola_pegawai.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Pegawai</title>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<h2>Edit Pegawai</h2>
<a href="kelola_pegawai.php">Kembali</a> | <a href="../logout.php">Logout</a>

<form method="POST">
    Nama: <input type="text" name="nama" value="<?php echo $pegawai['nama']; ?>" required><br>
    Username: <input type="text" name="username" value="<?php echo $pegawai['username']; ?>" required><br>
    Password (kosongkan jika tidak ganti): <input type="password" name="password"><br>
    Role:
    <select name="role" required>
        <option value="User" <?php if($pegawai['role']=='User') echo 'selected'; ?>>Pegawai (User)</option>
        <option value="Keuangan" <?php if($pegawai['role']=='Keuangan') echo 'selected'; ?>>Keuangan</option>
        <option value="Admin" <?php if($pegawai['role']=='Admin') echo 'selected'; ?>>Admin</option>
    </select><br>
    <button type="submit" name="update">Simpan Perubahan</button>
</form>
</body>
</html>
