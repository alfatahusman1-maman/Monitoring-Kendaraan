<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';

// Tambah Kendaraan
if (isset($_POST['tambah'])) {
    $no_polisi = $_POST['no_polisi'];
    $merk      = $_POST['merk'];
    $tipe      = $_POST['tipe'];
    $tahun     = $_POST['tahun'];
    $jenis     = $_POST['jenis'];
    $kondisi   = $_POST['kondisi'];
    $foto      = '';

    // Handle file upload
    if (!empty($_FILES['foto']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['foto']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $unique_name = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $target_file = $target_dir . $unique_name;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $unique_name;
            }
        }
    }

    mysqli_query($conn, "INSERT INTO kendaraan (no_polisi, merk, tipe, tahun, jenis, kondisi, foto) 
        VALUES ('$no_polisi','$merk','$tipe','$tahun','$jenis','$kondisi','$foto')");
    header("Location: kendaraan.php");
    exit;
}

// Update Kendaraan
if (isset($_POST['update'])) {
    $id        = $_POST['id'];
    $no_polisi = $_POST['no_polisi'];
    $merk      = $_POST['merk'];
    $tipe      = $_POST['tipe'];
    $tahun     = $_POST['tahun'];
    $jenis     = $_POST['jenis'];
    $kondisi   = $_POST['kondisi'];

    // Ambil data lama untuk foto
    $oldData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM kendaraan WHERE id=$id"));
    $foto = $oldData['foto'];

    // Handle file upload jika ada file baru
    if (!empty($_FILES['foto']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['foto']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Hapus foto lama jika ada
            if (!empty($oldData['foto']) && file_exists($target_dir . $oldData['foto'])) {
                unlink($target_dir . $oldData['foto']);
            }
            
            $unique_name = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $target_file = $target_dir . $unique_name;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $unique_name;
            }
        }
    }

    mysqli_query($conn, "UPDATE kendaraan SET 
                no_polisi='$no_polisi',
                merk='$merk',
                tipe='$tipe',
                tahun='$tahun',
                jenis='$jenis',
                kondisi='$kondisi',
                foto='$foto'
                WHERE id=$id");
    header("Location: kendaraan.php");
    exit;
}

// Hapus Kendaraan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kendaraan WHERE id=$id");
    header("Location: kendaraan.php");
    exit;
}

// Jika Edit Kendaraan
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $editData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kendaraan WHERE id=$id"));
}

// Ambil Data
$data = mysqli_query($conn, "SELECT * FROM kendaraan ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kendaraan</title>
    <link rel="stylesheet" href="../css/buttons.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f9fafc; }
        .main-content { margin-left: 240px; padding: 20px; }
        h2 { margin-bottom: 10px; }
        a { text-decoration: none; color: #007bff; }
        a:hover { color: #007bff; }

        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        form input, form select, form button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ddd; }
        form button { background: #007bff; color: #fff; font-weight: bold; border: none; cursor: pointer; }
        form button:hover { background: #0056b3; }
        .btn-cancel { background: #e74c3c; color: #fff; padding: 8px 14px; border-radius: 6px; }
        
        #previewContainer { 
            border: 2px dashed #007bff; 
            padding: 15px; 
            border-radius: 6px; 
            background: #f0f8ff;
        }
        #previewContainer img { 
            max-width: 100%; 
            max-height: 250px; 
            border-radius: 6px;
            margin-top: 10px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 8px; overflow: hidden; }
        table th, table td { padding: 12px; border-bottom: 1px solid #eee; }
        table th { background:  #007bff; color: #fff; }
        table tr:hover { background: #f3f9ff; }
        .action-links a { padding: 6px 10px; border-radius: 6px; font-size: 13px; color: #fff; margin-right: 5px; }
        .action-links a.edit { background: #27ae60; }
        .action-links a.delete { background: #e74c3c; }
        .photo-cell img { max-width: 80px; max-height: 80px; border-radius: 6px; object-fit: cover; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #007bff;
            font-size: 24px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #333;
        }

        .modal-body {
            text-align: center;
        }

        .modal-photo {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .modal-details {
            text-align: left;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            width: 120px;
        }

        .detail-value {
            color: #666;
            flex: 1;
            text-align: right;
        }
    </style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <h2> Data Kendaraan</h2>
    <p>
        <a href="dashboard.php">⬅ Kembali Dashboard</a> | 
        <a href="../logout.php">Logout</a>
    </p>

    <div class="card">
        <?php if ($editData): ?>
            <h3>Edit Kendaraan</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <label>No Polisi</label>
                <input type="text" name="no_polisi" value="<?php echo htmlspecialchars($editData['no_polisi']); ?>" required>
                <label>Merk</label>
                <input type="text" name="merk" value="<?php echo htmlspecialchars($editData['merk']); ?>" required>
                <label>Tipe</label>
                <input type="text" name="tipe" value="<?php echo htmlspecialchars($editData['tipe']); ?>" required>
                <label>Tahun</label>
                <input type="number" name="tahun" value="<?php echo $editData['tahun']; ?>" required>
                <label>Jenis</label>
                <select name="jenis" required>
                    <option value="Roda Dua" <?php if ($editData['jenis']=="Roda Dua") echo "selected"; ?>>Roda Dua</option>
                    <option value="Roda Empat" <?php if ($editData['jenis']=="Roda Empat") echo "selected"; ?>>Roda Empat</option>
                </select>
                <label>Kondisi</label>
                <select name="kondisi" required>
                    <option value="Baik" <?php if ($editData['kondisi']=="Baik") echo "selected"; ?>>Baik</option>
                    <option value="Kurang Baik" <?php if ($editData['kondisi']=="Kurang Baik") echo "selected"; ?>>Kurang Baik</option>
                </select>
                <label>📷 Foto Kendaraan</label>
                <?php if (!empty($editData['foto'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../uploads/<?php echo htmlspecialchars($editData['foto']); ?>" alt="Foto Kendaraan" style="max-width: 200px; max-height: 200px; border-radius: 6px;">
                        <p style="margin-top: 5px; font-size: 12px; color: #666;">Foto saat ini</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*" id="fotoInput">
                <small style="color: #666; margin-top: -5px; display: block;">Format: JPG, JPEG, PNG, GIF (Biarkan kosong jika tidak ingin mengganti)</small>
                <div class="btn-group">
                    <button type="submit" name="update" class="btn-primary">Perbarui</button>
                    <a href="kendaraan.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        <?php else: ?>
            <h3>Tambah Kendaraan</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>No Polisi</label>
                <input type="text" name="no_polisi" placeholder="Masukkan No Polisi" required>
                <label>Merk</label>
                <input type="text" name="merk" placeholder="Masukkan Merk" required>
                <label>Tipe</label>
                <input type="text" name="tipe" placeholder="Masukkan Tipe" required>
                <label>Tahun</label>
                <input type="number" name="tahun" placeholder="Masukkan Tahun" required>
                <label>Jenis</label>
                <select name="jenis" required>
                    <option value="Roda Dua">Roda Dua</option>
                    <option value="Roda Empat">Roda Empat</option>
                </select>
                <label>Kondisi</label>
                <select name="kondisi" required>
                    <option value="Baik">Baik</option>
                    <option value="Rusak Ringan">Rusak Ringan</option>
                </select>
                <label>📷 Foto Kendaraan</label>
                <input type="file" name="foto" accept="image/*" id="fotoInput">
                <small style="color: #666; margin-top: -5px; display: block;">Format: JPG, JPEG, PNG, GIF (Maks 5MB)</small>
                <div id="previewContainer" style="margin-top: 10px; text-align: center;"></div>
                <div class="btn-group">
                    <button type="submit" name="tambah" class="btn-primary">Tambah Kendaraan</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <h3>📋 Daftar Kendaraan</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>No Polisi</th>
                <th>Merk</th>
                <th>Tipe</th>
                <th>Tahun</th>
                <th>Jenis</th>
                <th>Kondisi</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($k = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?php echo htmlspecialchars($k['id']); ?></td>
                <td><?php echo htmlspecialchars($k['no_polisi']); ?></td>
                <td><?php echo htmlspecialchars($k['merk']); ?></td>
                <td><?php echo htmlspecialchars($k['tipe']); ?></td>
                <td><?php echo htmlspecialchars($k['tahun']); ?></td>
                <td><?php echo htmlspecialchars($k['jenis']); ?></td>
                <td><?php echo htmlspecialchars($k['kondisi']); ?></td>
                <td class="photo-cell">
                    <?php if (!empty($k['foto']) && file_exists("../uploads/" . $k['foto'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($k['foto']); ?>" 
                             alt="Foto <?php echo htmlspecialchars($k['no_polisi']); ?>"
                             class="photo-thumbnail"
                             data-id="<?php echo $k['id']; ?>"
                             data-no-polisi="<?php echo htmlspecialchars($k['no_polisi']); ?>"
                             data-merk="<?php echo htmlspecialchars($k['merk']); ?>"
                             data-tipe="<?php echo htmlspecialchars($k['tipe']); ?>"
                             data-tahun="<?php echo $k['tahun']; ?>"
                             data-jenis="<?php echo htmlspecialchars($k['jenis']); ?>"
                             data-kondisi="<?php echo htmlspecialchars($k['kondisi']); ?>"
                             data-foto="<?php echo htmlspecialchars($k['foto']); ?>"
                             style="cursor: pointer;">
                    <?php else: ?>
                        <span style="color: #999; font-size: 12px;">Tidak ada foto</span>
                    <?php endif; ?>
                </td>
                <td class="action-links">
                    <a href="?edit=<?php echo $k['id']; ?>" class="edit btn-sm">Edit</a>
                    <a href="?hapus=<?php echo $k['id']; ?>" class="delete btn-sm" onclick="return confirm('Hapus data kendaraan ini?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal untuk Preview Foto Kendaraan -->
<div id="photoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>📷 Detail Kendaraan</h2>
            <button class="modal-close" onclick="closePhotoModal()">&times;</button>
        </div>
        <div class="modal-body">
            <img id="modalPhoto" src="" alt="Foto Kendaraan" class="modal-photo">
            <div class="modal-details">
                <div class="detail-row">
                    <span class="detail-label"><strong>ID</strong></span>
                    <span class="detail-value" id="modalId">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>No Polisi</strong></span>
                    <span class="detail-value" id="modalNoPolisi">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Merk</strong></span>
                    <span class="detail-value" id="modalMerk">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Tipe</strong></span>
                    <span class="detail-value" id="modalTipe">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Tahun</strong></span>
                    <span class="detail-value" id="modalTahun">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Jenis</strong></span>
                    <span class="detail-value" id="modalJenis">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Kondisi</strong></span>
                    <span class="detail-value" id="modalKondisi">-</span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script>
// Preview gambar saat dipilih
document.getElementById('fotoInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('previewContainer');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            previewContainer.innerHTML = '<p><strong>Preview Foto:</strong></p><img src="' + event.target.result + '" alt="Preview">';
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.innerHTML = '';
    }
});

// Modal untuk Preview Foto Kendaraan
function showPhotoModal(photo) {
    const modal = document.getElementById('photoModal');
    
    // Set foto
    document.getElementById('modalPhoto').src = '../uploads/' + photo.dataset.foto;
    
    // Set data dari data attributes
    document.getElementById('modalId').textContent = photo.dataset.id;
    document.getElementById('modalNoPolisi').textContent = photo.dataset.noPolisi;
    document.getElementById('modalMerk').textContent = photo.dataset.merk;
    document.getElementById('modalTipe').textContent = photo.dataset.tipe;
    document.getElementById('modalTahun').textContent = photo.dataset.tahun;
    document.getElementById('modalJenis').textContent = photo.dataset.jenis;
    document.getElementById('modalKondisi').textContent = photo.dataset.kondisi;
    
    // Show modal
    modal.classList.add('show');
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.remove('show');
}

// Close modal saat click di luar modal
window.onclick = function(event) {
    const modal = document.getElementById('photoModal');
    if (event.target == modal) {
        modal.classList.remove('show');
    }
}

// Event listener untuk semua foto di tabel
document.addEventListener('DOMContentLoaded', function() {
    const photos = document.querySelectorAll('.photo-thumbnail');
    photos.forEach(photo => {
        photo.addEventListener('click', function() {
            showPhotoModal(this);
        });
    });
});
</script>