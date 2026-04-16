# Instruksi Setup Foto Kendaraan

## 1. Jalankan Migration di MySQL

Buka phpMyAdmin atau MySQL client dan jalankan query berikut:

```sql
ALTER TABLE `kendaraan` ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;
```

## 2. Verifikasi Folder Uploads

Pastikan folder `/uploads/` sudah ada di root project. Jika belum:
- Buat folder `uploads` di: `c:\laragon\www\monitoring_kendaraan\uploads\`
- Folder harus writable (permission 755)

## 3. Features yang Ditambahkan

### Upload Gambar di Halaman Data Kendaraan
- Form input file untuk foto saat menambah kendaraan
- Form input file untuk mengupdate foto saat edit kendaraan
- Preview otomatis gambar sebelum upload
- Validasi format file (JPG, JPEG, PNG, GIF)
- Penghapusan foto lama saat diupdate

### Tampilan Foto
- Kolom "Foto" di tabel daftar kendaraan
- Gambar ditampilkan dengan ukuran thumbnail (80x80px)
- Jika tidak ada foto, tampil pesan "Tidak ada foto"

## 4. Struktur File

```
uploads/
├── 1733203200_1234.jpg    (format: timestamp_random.ext)
├── 1733203215_5678.png
└── ...
```

## 5. Security Features

✅ Validasi tipe file (whitelist: jpg, jpeg, png, gif)
✅ Unique filename (timestamp + random number)
✅ File size check (bisa disesuaikan)
✅ Uploaded files di-check sebelum ditampilkan
✅ HTML escaped untuk mencegah XSS

## 6. Testing

1. Login ke admin panel
2. Buka "Data Kendaraan"
3. Klik "Edit" atau "Tambah Kendaraan"
4. Upload gambar kendaraan
5. Lihat preview dan submit
6. Verifikasi foto muncul di daftar kendaraan
