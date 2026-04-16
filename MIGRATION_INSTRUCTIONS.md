# 🔧 Cara Menjalankan Database Migration

Error yang terjadi: `Unknown column 'foto' in 'field list'`

Ini berarti kolom `foto` belum ada di tabel `kendaraan`. Berikut cara memperbaikinya:

## **CARA 1: Otomatis (Recommended) ✅**

1. Akses file migration melalui browser:
   ```
   http://localhost/monitoring_kendaraan/setup-migration.html
   ```

2. Klik tombol **"Jalankan Migration"**

3. Tunggu hingga muncul pesan sukses

4. Sistem akan redirect ke halaman `Data Kendaraan`

---

## **CARA 2: Manual via PhpMyAdmin**

1. Buka **PhpMyAdmin** (http://localhost/phpmyadmin)

2. Pilih database **`db_monitoring`**

3. Buka tab **SQL**

4. Copy dan paste query berikut:
   ```sql
   ALTER TABLE `kendaraan` ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;
   ```

5. Klik **Execute** (atau tekan Ctrl+Enter)

6. Tunggu hingga muncul pesan "Query executed successfully"

---

## **CARA 3: Manual via Command Line**

```bash
mysql -u root -p db_monitoring < migrations/add_foto_to_kendaraan.sql
```

---

## ✅ Verifikasi Sukses

Setelah migration selesai, coba akses:
- **Data Kendaraan**: `http://localhost/monitoring_kendaraan/admin/kendaraan.php`
- Tidak boleh ada error lagi

---

## 📝 Struktur Database Setelah Migration

```
kendaraan
├── id (INT, Primary Key)
├── no_polisi (VARCHAR)
├── merk (VARCHAR)
├── tipe (VARCHAR)
├── tahun (INT)
├── jenis (ENUM)
├── kondisi (ENUM)
├── status (ENUM)
└── foto (VARCHAR) ← BARU ✨
```

---

## 🎯 Selanjutnya

Setelah migration berhasil, Anda bisa:
1. ✅ Upload foto untuk kendaraan baru
2. ✅ Update foto kendaraan yang sudah ada
3. ✅ Lihat preview foto sebelum submit
4. ✅ Lihat daftar kendaraan dengan thumbnail foto

---

## ⚠️ Troubleshooting

**Jika masih error "Unknown column 'foto'":**
- Pastikan migration sudah dijalankan
- Refresh halaman (F5)
- Clear cache browser (Ctrl+Shift+Delete)
- Logout dan login ulang

**Jika migration gagal:**
- Pastikan user MySQL memiliki privilege ALTER TABLE
- Cek koneksi database di `config.php`
- Lihat error message di console browser (F12)
