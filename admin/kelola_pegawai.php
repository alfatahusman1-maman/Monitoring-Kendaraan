<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}
require '../config.php';

$user = $_SESSION['user'];

// Folder upload
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Hapus pegawai (Admin saja)
if ($user['role'] == 'Admin' && isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    mysqli_query($conn, "DELETE FROM pegawai WHERE id='$id'");
    mysqli_query($conn, "DELETE FROM pegawai_detail WHERE id_pegawai='$id'");
    header("Location: kelola_pegawai.php");
    exit;
}

// Edit pegawai (Admin saja)
if ($user['role'] == 'Admin' && isset($_POST['edit'])) {
    $id     = $_POST['id'];
    $nama   = $_POST['nama'];
    $nip    = $_POST['nip'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email  = $_POST['email'] ?? '';
    $no_hp  = $_POST['no_hp'] ?? '';
    $kendaraan = $_POST['kendaraan'] ?? '';

    if (!empty($_FILES['foto']['name'])) {
        $foto = time() . "_" . basename($_FILES['foto']['name']);
        $targetFile = $uploadDir . $foto;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            mysqli_query($conn, "UPDATE pegawai_detail SET foto='$foto' WHERE id_pegawai='$id'");
        }
    }

    mysqli_query($conn, "UPDATE pegawai SET nama='$nama' WHERE id='$id'");
    mysqli_query($conn, "UPDATE users SET nama='$nama' WHERE id='$id'");
    mysqli_query($conn, "UPDATE pegawai_detail 
                         SET nip='$nip', alamat='$alamat', email='$email', no_hp='$no_hp', kendaraan='$kendaraan' 
                         WHERE id_pegawai='$id'");

    header("Location: kelola_pegawai.php");
    exit;
}

// Update data User
if ($user['role'] == 'User' && isset($_POST['update'])) {
    $id     = $user['id'];
    $nip    = $_POST['nip'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email  = $_POST['email'] ?? '';
    $no_hp  = $_POST['no_hp'] ?? '';
    $kendaraan = $_POST['kendaraan'] ?? '';

    if (!empty($_FILES['foto']['name'])) {
        $foto = time() . "_" . basename($_FILES['foto']['name']);
        $targetFile = $uploadDir . $foto;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            mysqli_query($conn, "UPDATE pegawai_detail SET foto='$foto' WHERE id_pegawai='$id'");
        }
    }

    mysqli_query($conn, "UPDATE pegawai_detail 
                         SET nip='$nip', alamat='$alamat', email='$email', no_hp='$no_hp', kendaraan='$kendaraan' 
                         WHERE id_pegawai='$id'");

    header("Location: kelola_pegawai.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pegawai</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: #f4f6f9;
        }
        .container {
            display: flex;
        }
        .main-content {
            flex: 1;
            margin-left: 220px; /* sidebar width */
            padding: 20px;
        }
        h2 {
            margin-top: 10px;
            margin-bottom: 25px;
            color: #333;
            font-weight: 700;
        }
        .card {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #007bff;
            color: #fff;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-edit {
            background: #ffc107;
            color: #fff;
        }
        .btn-del {
            background: #dc3545;
            color: #fff;
        }
        .btn-primary {
            background: #007bff;
            color: #fff;
        }
        .form-group {
            margin-bottom: 10px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }
        button {
            background: #28a745;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        img {
            border-radius: 5px;
        }
        /* Modal */
        .modal {
            display: none;
            position: fixed; 
            z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
        }
        .modal-content {
            margin: 5% auto;
            display: block;
            width: 60%;
            max-width: 600px;
            border-radius: 10px;
        }
        .close {
            position: absolute;
            top: 20px; right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'layout/sidebar.php'; ?>
    <div class="main-content">
        <h2> Kelola Pegawai</h2>

        <?php if ($user['role'] == 'Admin'): ?>
        <div class="card">
            <h3>Data Pegawai</h3>
            <table>
                <tr>
                    <th>No</th><th>Nama</th><th>NIP</th><th>Alamat</th><th>Email</th>
                    <th>No HP</th><th>Kendaraan</th><th>Foto</th><th>Aksi</th>
                </tr>
                <?php
                $q = mysqli_query($conn, "SELECT p.*, d.nip, d.alamat, d.email, d.no_hp, d.kendaraan, d.foto 
                                          FROM pegawai p 
                                          LEFT JOIN pegawai_detail d ON p.id=d.id_pegawai");
                $no=1;
                while ($r=mysqli_fetch_assoc($q)):
                    $fotoPath = (!empty($r['foto']) && file_exists($uploadDir.$r['foto'])) 
                                ? "../uploads/".htmlspecialchars($r['foto']) : "";
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nama'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['nip'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['alamat'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['no_hp'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['kendaraan'] ?? '') ?></td>
                    <td><?= $fotoPath ? "<img src='$fotoPath' width='50' style='cursor:pointer' onclick=\"openModal('$fotoPath')\">" : '' ?></td>
                    <td>
                        <a href='kelola_pegawai.php?edit=<?= $r['id'] ?>' class="btn btn-edit">Edit</a>
                        <a href='kelola_pegawai.php?hapus=<?= $r['id'] ?>' class="btn btn-del" onclick="return confirm('Yakin hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <?php if (isset($_GET['edit'])): 
            $id=$_GET['edit'];
            $editQ=mysqli_query($conn,"SELECT p.*,d.nip,d.alamat,d.email,d.no_hp,d.kendaraan,d.foto
                                       FROM pegawai p 
                                       LEFT JOIN pegawai_detail d ON p.id=d.id_pegawai
                                       WHERE p.id='$id'");
            $editUserData=mysqli_fetch_assoc($editQ);
        ?>
        <div class="card">
            <h3>Edit Pegawai</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editUserData['id'] ?>">
                <div class="form-group">Nama: <input type="text" name="nama" value="<?= htmlspecialchars($editUserData['nama'] ?? '') ?>" required></div>
                <div class="form-group">NIP: <input type="text" name="nip" value="<?= htmlspecialchars($editUserData['nip'] ?? '') ?>"></div>
                <div class="form-group">Alamat: <input type="text" name="alamat" value="<?= htmlspecialchars($editUserData['alamat'] ?? '') ?>"></div>
                <div class="form-group">Email: <input type="email" name="email" value="<?= htmlspecialchars($editUserData['email'] ?? '') ?>"></div>
                <div class="form-group">No HP: <input type="text" name="no_hp" value="<?= htmlspecialchars($editUserData['no_hp'] ?? '') ?>"></div>
                <div class="form-group">Kendaraan:
                    <select name="kendaraan">
                        <option value="">-- Pilih --</option>
                        <option value="Mobil" <?= $editUserData['kendaraan']=="Mobil"?"selected":"" ?>>Mobil</option>
                        <option value="Motor" <?= $editUserData['kendaraan']=="Motor"?"selected":"" ?>>Motor</option>
                    </select>
                </div>
                <div class="form-group">Foto: <input type="file" name="foto"></div>
                <?php if (!empty($editUserData['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($editUserData['foto']) ?>" width="60">
                <?php endif; ?>
                <br><br>
                <button type="submit" name="edit">Update</button>
            </form>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="card">
            <h3>Update Data Saya</h3>
            <?php
            $idUser=$user['id'];
            $detailQ=mysqli_query($conn,"SELECT * FROM pegawai_detail WHERE id_pegawai='$idUser'");
            $detail=mysqli_fetch_assoc($detailQ);
            ?>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">NIP: <input type="text" name="nip" value="<?= htmlspecialchars($detail['nip']??'') ?>"></div>
                <div class="form-group">Alamat: <input type="text" name="alamat" value="<?= htmlspecialchars($detail['alamat']??'') ?>"></div>
                <div class="form-group">Email: <input type="email" name="email" value="<?= htmlspecialchars($detail['email']??'') ?>"></div>
                <div class="form-group">No HP: <input type="text" name="no_hp" value="<?= htmlspecialchars($detail['no_hp']??'') ?>"></div>
                <div class="form-group">Kendaraan:
                    <select name="kendaraan">
                        <option value="">-- Pilih --</option>
                        <option value="Mobil" <?= ($detail['kendaraan']??'')=="Mobil"?"selected":"" ?>>Mobil</option>
                        <option value="Motor" <?= ($detail['kendaraan']??'')=="Motor"?"selected":"" ?>>Motor</option>
                    </select>
                </div>
                <div class="form-group">Foto: <input type="file" name="foto"></div>
                <?php if (!empty($detail['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($detail['foto']) ?>" width="60">
                <?php endif; ?>
                <br><br>
                <button type="submit" name="update">Update</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="imgPreview">
</div>

<script>
function openModal(src) {
    document.getElementById("myModal").style.display = "block";
    document.getElementById("imgPreview").src = src;
}
function closeModal() {
    document.getElementById("myModal").style.display = "none";
}
</script>
</body>
</html>
