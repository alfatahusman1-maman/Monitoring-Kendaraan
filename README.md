# 🚗 Sistem Monitoring Kendaraan

Sistem Monitoring Kendaraan adalah aplikasi berbasis web yang dirancang untuk mengelola data kendaraan, pemeliharaan (servis), dan pengajuan serta approval BBM (Bahan Bakar Minyak) secara digital dan terintegrasi.

---

## 🚀 Fitur Utama

1.  **Manajemen Kendaraan**: Pencatatan data kendaraan (No. Polisi, Merk, Tipe, Tahun, Jenis, Kondisi).
2.  **Tracking BBM**: Pengajuan BBM oleh User dengan sistem approval berjenjang.
3.  **Sistem Approval BBM**:
    *   **User (Pegawai)**: Mengajukan BBM.
    *   **Admin**: Meninjau dan menyetujui/menolak pengajuan (Status: PENDING → APPROVED/REJECTED).
    *   **Keuangan**: Memvalidasi pengajuan yang sudah disetujui Admin (Status: PENDING → VALIDATED/REJECTED).
4.  **Manajemen Servis**: Pencatatan riwayat servis kendaraan.
5.  **Sistem Button Modern**: Tampilan tombol yang konsisten dan responsive di seluruh modul.

---

## 🛠️ Teknologi yang Digunakan

*   **Core**: PHP 8.x
*   **Database**: MySQL / MariaDB
*   **Frontend**: Bootstrap 5, Custom CSS (Buttons), JavaScript
*   **Dependencies**: Composer (untuk library pendukung)

---

## 📂 Struktur Project (Penting)

*   `/admin`: Modul untuk administrator.
*   `/keuangan`: Modul untuk bagian keuangan.
*   `/user`: Modul untuk user/pegawai.
*   `/css`: File styling, termasuk `buttons.css`.
*   `/config.php`: Konfigurasi database.
*   `/db_monitoring .sql`: Database schema utama.

---

## ⚙️ Cara Menjalankan Sistem

### 1. Persiapan Environment
*   Gunakan web server lokal seperti **XAMPP**, **Laragon**, atau **WAMP**.
*   Pastikan PHP versi 8.0 ke atas aktif.
*   Pastikan MySQL service sudah berjalan.

### 2. Setup Database
1.  Buka **PhpMyAdmin** (biasanya di `http://localhost/phpmyadmin`).
2.  Buat database baru dengan nama `db_monitoring`.
3.  Import file `db_monitoring .sql` yang ada di root folder ke dalam database tersebut.
4.  Jika ada error terkait kolom `foto` di tabel kendaraan, jalankan SQL di folder `migrations/`.

### 3. Konfigurasi Koneksi
*   Buka file `config.php`.
*   Sesuaikan parameter database jika berbeda:
    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_monitoring";
    ```

### 4. Menjalankan Aplikasi
*   Pindahkan folder project ke direktori `htdocs` (untuk XAMPP).
*   Akses aplikasi melalui browser di: `http://localhost/Monitoring-Kendaraan-main`

---

## 📖 Istilah-Istilah Program (Glossary)

### Status Approval BBM
Dalam alur pengajuan BBM, terdapat beberapa status penting:

| Istilah | Keterangan |
| :--- | :--- |
| **PENDING** | Pengajuan baru yang belum diproses oleh Admin/Keuangan. |
| **APPROVED** | Pengajuan telah disetujui oleh level Admin. |
| **VALIDATED** | Pengajuan telah divalidasi dan diselesaikan oleh level Keuangan. |
| **REJECTED** | Pengajuan ditolak (biasanya disertai catatan alasan penolakan). |

### Tipe Button (Styling)
Aplikasi ini menggunakan sistem button class yang konsisten:
*   `.btn-primary`: Untuk aksi utama (Simpan, Tambah).
*   `.btn-success`: Untuk konfirmasi atau persetujuan.
*   `.btn-danger`: Untuk aksi hapus atau penolakan.
*   `.btn-warning`: Untuk peringatan.
*   `.btn-info`: Untuk informasi tambahan.
*   `.btn-cancel`: Untuk membatalkan aksi.

---

## 🔼 Git & Deployment

### Cara Push ke GitHub (Manual)
1.  Buka terminal/command prompt di folder project.
2.  Tambahkan perubahan:
    ```bash
    git add .
    ```
3.  Lakukan commit:
    ```bash
    git commit -m "Pesan perubahan Anda"
    ```
4.  Kirim ke GitHub:
    ```bash
    git push origin main
    ```

### Cara Push Otomatis (Windows)
Telah disediakan file `auto-push.bat` untuk mempermudah proses:
1.  Double-click file `auto-push.bat`.
2.  Masukkan pesan commit saat diminta (atau tekan Enter untuk pesan default).
3.  Tunggu hingga proses selesai.

---

## 📞 Support
Jika terjadi kendala teknis, pastikan ekstransi `mysqli` sudah aktif di file `php.ini` Anda.
