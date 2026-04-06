# Panduan Integrasi Button Styles

## Daftar Halaman yang Perlu di-Update

### Admin Pages
- [ ] `admin/dashboard.php`
- [ ] `admin/kendaraan.php`
- [ ] `admin/servis.php`
- [ ] `admin/bbm.php`
- [ ] `admin/laporan.php`
- [ ] `admin/laporan_bbm.php`
- [ ] `admin/laporan_perjenis_bbm.php`
- [ ] `admin/laporan_service.php`
- [ ] `admin/tanda_terima.php`
- [ ] `admin/cetak.php`
- [ ] `admin/pegawai.php`
- [ ] `admin/kelola_pegawai.php`
- [ ] `admin/edit_pegawai.php`
- [ ] `admin/kelola_admin.php`

### Keuangan Pages
- [ ] `keuangan/dashboard.php`
- [ ] `keuangan/validasi.php`

### User Pages
- [ ] `user/dashboard.php`
- [ ] `user/ajukan_bbm.php`
- `user/ajukan_servis.php`
- [ ] `user/tanda_terima.php`

---

## Langkah-Langkah Integrasi

### 1. Tambahkan Link CSS di setiap halaman

Di bagian `<head>` setiap file PHP, tambahkan line ini sebelum `</head>`:

```html
<link rel="stylesheet" href="../css/buttons.css">
```

**Catatan:** Sesuaikan path `../` tergantung lokasi file:
- Untuk file di folder `admin/`: gunakan `../css/buttons.css`
- Untuk file di folder `keuangan/`: gunakan `../css/buttons.css`
- Untuk file di folder `user/`: gunakan `../css/buttons.css`
- Untuk file di root folder: gunakan `css/buttons.css`

### 2. Update HTML Buttons

Ganti semua button dengan class yang sesuai:

#### Sebelum:
```html
<button type="submit">Simpan</button>
<input type="submit" value="Tambah">
<a href="?delete=1">Hapus</a>
```

#### Sesudah:
```html
<button type="submit" class="btn-primary">Simpan</button>
<input type="submit" name="tambah" class="btn-primary" value="Tambah">
<a href="?delete=1" class="btn-danger" onclick="return confirm('Yakin ingin dihapus?')">Hapus</a>
```

### 3. Standardisasi Button Groups

Untuk form dengan multiple buttons, gunakan container `btn-group`:

```html
<div class="btn-group">
    <input type="submit" name="tambah" value="Simpan">
    <button type="reset" class="btn-secondary">Reset</button>
    <a href="?back" class="btn-cancel">Batal</a>
</div>
```

### 4. Update Table Action Buttons

Untuk tombol di tabel, gunakan class yang sesuai:

```html
<table>
    <tbody>
        <tr>
            <td>
                <a href="?edit=1" class="edit btn-sm">Edit</a>
                <a href="?delete=1" class="delete btn-sm" onclick="return confirm('Yakin?')">Hapus</a>
            </td>
        </tr>
    </tbody>
</table>
```

---

## Template Integrasi untuk Setiap Halaman

### Template untuk Admin Pages

```php
<?php
// ... kode PHP lainnya ...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Button Styles -->
    <link rel="stylesheet" href="../css/buttons.css">
    
    <!-- Custom CSS (jika ada) -->
    <style>
        /* Custom styles untuk halaman spesifik */
    </style>
</head>
<body>
    <!-- ... konten halaman ... -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Custom JavaScript
    </script>
</body>
</html>
```

### Template untuk Keuangan Pages

```php
<?php
// ... kode PHP lainnya ...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Button Styles -->
    <link rel="stylesheet" href="../css/buttons.css">
</head>
<body>
    <!-- ... konten halaman ... -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### Template untuk User Pages

```php
<?php
// ... kode PHP lainnya ...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Button Styles -->
    <link rel="stylesheet" href="../css/buttons.css">
</head>
<body>
    <!-- ... konten halaman ... -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## Button Class Quick Reference

| Aksi | Class | Warna |
|------|-------|-------|
| Simpan, Submit, Tambah | `.btn-primary` atau type="submit" | Biru |
| Konfirmasi, Setuju | `.btn-success` atau class="success" | Hijau |
| Hapus | `.btn-danger` atau class="delete" | Merah |
| Perhatian | `.btn-warning` | Kuning |
| Informasi | `.btn-info` | Cyan |
| Batal, Reset | `.btn-cancel` / `.btn-secondary` | Abu-abu |
| Edit | class="edit" | Hijau Tua |
| Lihat | class="view" | Cyan |

---

## Contoh HTML untuk Berbagai Skenario

### Form dengan Single Button
```html
<form method="POST">
    <input type="text" name="nama" required>
    <input type="submit" value="Simpan">
</form>
```

### Form dengan Multiple Buttons
```html
<form method="POST">
    <input type="text" name="nama" required>
    <div class="btn-group">
        <input type="submit" name="tambah" value="Simpan">
        <button type="reset" class="btn-secondary">Reset</button>
        <a href="?back" class="btn-cancel">Batal</a>
    </div>
</form>
```

### Modal Action Buttons
```html
<div style="margin-top: 20px; text-align: right;">
    <button class="btn-primary">Lanjutkan</button>
    <button class="btn-cancel" onclick="closeModal()">Tutup</button>
</div>
```

### Inline Action Buttons
```html
<td>
    <a href="?edit=1" class="edit btn-sm">Edit</a>
    <a href="?delete=1" class="delete btn-sm" onclick="return confirm('Yakin?')">Hapus</a>
    <a href="detail.php?id=1" class="view btn-sm">Lihat</a>
</td>
```

---

## Checklist Integrasi

- [ ] Buat folder `css` di root project (jika belum ada)
- [ ] Copy file `buttons.css` ke folder `css/`
- [ ] Review semua halaman admin, keuangan, dan user
- [ ] Tambahkan `<link rel="stylesheet" href="../css/buttons.css">` di setiap halaman
- [ ] Update semua button HTML untuk menggunakan class yang sesuai
- [ ] Test responsive design di mobile devices
- [ ] Test hover, active, dan disabled states
- [ ] Periksa warna button konsisten sesuai dokumentasi

---

## Testing Checklist

### Desktop Testing (Chrome, Firefox, Edge)
- [ ] Semua button tampil dengan warna yang benar
- [ ] Hover effect berfungsi (shadow bertambah, button naik)
- [ ] Active/click effect berfungsi (button turun)
- [ ] Disabled button terlihat muted dan tidak responsif
- [ ] Font size dan padding sesuai

### Mobile Testing (iPhone, Android)
- [ ] Button ukuran cukup besar untuk tap (min 44px)
- [ ] Spacing antar button minimal 10px
- [ ] Button group tampil vertical (full width)
- [ ] Touch states berfungsi (tidak double-tap)

### Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari
- [ ] Chrome Mobile

---

## Troubleshooting

### Button tidak berubah warna
**Solusi:**
1. Pastikan CSS sudah di-include: `<link rel="stylesheet" href="../css/buttons.css">`
2. Clear browser cache (Ctrl+Shift+Delete atau Cmd+Shift+Delete)
3. Reload halaman (Ctrl+R atau Cmd+R)
4. Check browser console (F12) untuk error messages

### Button tampil terlalu besar/kecil
**Solusi:**
1. Gunakan `.btn-sm` untuk button kecil
2. Gunakan `.btn-lg` untuk button besar
3. Adjust custom padding jika diperlukan

### Gradient tidak tampil
**Solusi:**
1. Semua modern browsers support CSS gradients
2. Jika tidak tampil, clear cache dan reload
3. Fallback color akan digunakan untuk browser very old

### Button tidak responsive di mobile
**Solusi:**
1. Pastikan sudah ada `<meta name="viewport">`
2. Gunakan `.btn-group` untuk grouping buttons
3. Test di real device atau Chrome DevTools mobile view

---

## Next Steps

1. **Integrasi CSS ke semua halaman** (gunakan checklist di atas)
2. **Test semua button** di berbagai browser dan device
3. **Gather feedback** dari user tentang styling
4. **Refine warna** jika diperlukan
5. **Document custom buttons** jika ada yang khusus

---

## Support

Untuk bantuan atau pertanyaan, silakan referensikan file:
- `BUTTON_STYLES_DOCUMENTATION.md` - Dokumentasi lengkap
- `button-styles-preview.html` - Preview visual semua button styles
- `css/buttons.css` - Source code CSS

