# 🎓 Materi Magang & Istilah Sistem (Dishub) - Edisi Lengkap

Halo! Dokumen ini telah diperbarui dengan detail yang lebih mendalam untuk membantumu menghadapi dosen penguji atau pembimbing di Dishub.

---

## 🗣️ Skenario Jawaban untuk Dosen Penguji

Jika dosen bertanya hal-hal spesifik, kamu bisa menggunakan poin-poin di bawah ini:

### 1. Apa Masalah Utama di Dishub yang Ingin Kamu Selesaikan?
*   **Manual ke Digital**: "Sebelumnya, pencatatan biaya BBM dan Servis dilakukan secara manual di buku besar. Hal ini menyulitkan saat harus mengecek berapa total biaya yang dihabiskan satu kendaraan dalam setahun."
*   **Kurangnya Transparansi**: "Sulit untuk memvalidasi apakah struk fisik yang diserahkan pegawai sesuai dengan input data. Sistem ini meminimalisir manipulasi data karena ada proses validasi bertingkat."

### 2. Bagaimana Sistem Ini Meningkatkan Efisiensi Kerja?
*   **Integrasi Data**: "Hanya dengan sekali input oleh pengemudi, data langsung masuk ke dashboard Admin dan Keuangan secara real-time. Tidak perlu lagi menunggu rekapitulasi bulanan manual."
*   **Audit-Ready**: "Laporan PDF yang dihasilkan sistem ini sudah diformat sesuai kebutuhan dinas, sehingga saat ada audit internal, data pengeluaran kendaraan sudah siap disajikan dengan akurat."

### 3. Apa Tantangan Terbesar Saat Membangun Sistem Ini?
*   **Responsivitas**: "Memastikan aplikasi tetap mudah digunakan oleh pengemudi di lapangan melalui HP, sambil tetap menyediakan dashboard yang lengkap untuk admin di kantor."
*   **Integritas Data**: "Membangun relasi tabel yang kuat agar data BBM tidak tertukar antar kendaraan, serta memastikan alur persetujuan (approval) tidak bisa diloncati."

---

## 📘 Glosarium Istilah Teknis (Materi Teknis)

Berikut adalah istilah yang lebih mendalam yang sering ditanyakan dosen penguji sistem informasi:

| Kelompok | Istilah | Penjelasan Teknis untuk Dosen |
| :--- | :--- | :--- |
| **Arsitektur** | **Client-Server** | Arsitektur di mana Browser (Client) meminta data ke Server (PHP/MySQL) dan menampilkannya kepada pengguna. |
| | **Native PHP** | Pengembangan web menggunakan bahasa PHP murni tanpa framework, menunjukkan pemahaman dasar logika pemrograman yang kuat. |
| **Database** | **Relational Map (ERD)** | Pemetaan hubungan antar tabel. Contoh: Tabel `bbm` memiliki relasi `Many-to-One` dengan tabel `kendaraan`. |
| | **Foreign Key Constraint** | Aturan yang menjaga konsistensi data. Sistem tidak akan menghapus data kendaraan jika masih ada riwayat BBM-nya. |
| | **SQL Join** | Teknik menggabungkan data dari dua tabel atau lebih (misal: mengambil nama pegawai dari tabel `users` saat melihat data `bbm`). |
| **Keamanan** | **Input Sanitization** | Proses pembersihan data input (menggunakan `mysqli_real_escape_string`) untuk mencegah serangan **SQL Injection**. |
| | **One-Way Hashing** | Penggunaan `MD5` atau `password_hash` untuk memastikan password tidak bisa dibaca oleh siapa pun, termasuk admin database. |
| **UI/UX** | **Media Queries** | Kode CSS yang mendeteksi ukuran layar pengguna untuk merubah tata letak (Layout) agar tetap rapi di mobile. |
| | **Glassmorphism** | Efek desain transparan seperti kaca yang memberikan kesan modern dan premium pada antarmuka admin. |
| **Logika** | **Boolean Logic** | Logika benar/salah yang digunakan dalam status approval (Jika `APPROVED` maka tampilkan tombol cetak, jika tidak maka sembunyikan). |

---

## 🏛️ Penjelasan Alur Kerja (Workflow) untuk Dishub

Dosen sering bertanya tentang alur bisnis. Kamu bisa jelaskan seperti ini:
1.  **Pengajuan**: Pegawai (User) menginput data BBM/Servis + Upload Struk Foto melalui HP.
2.  **Verifikasi Admin**: Admin memeriksa kelengkapan data dan keaslian struk.
3.  **Validasi Keuangan**: Setelah Admin oke, bagian Keuangan memvalidasi biaya untuk pencairan dana (jika diperlukan).
4.  **Finalisasi**: Data masuk ke laporan bulanan dan grafik monitoring dashboard.

---

## 📌 Tips Presentasi
*   **Gunakan Istilah "Digital Transformation"**: Ini kata kunci yang sangat disukai di instansi pemerintahan saat ini.
*   **Pamerkan Fitur PDF**: Dompdf adalah nilai tambah yang besar karena dosen suka melihat *output* nyata berupa dokumen resmi.
*   **Tekankan pada Keamanan**: Sebutkan bahwa sistem ini mengamankan data rahasia pegawai dan riwayat keuangan dinas.

> [!IMPORTANT]
> Jika ditanya *"Kenapa pakai PHP Native bukan Framework (Laravel) ?"*
> Jawab: "Untuk proyek skala Dishub saat ini, PHP Native memberikan kecepatan akses yang lebih ringan dan kontrol penuh terhadap setiap baris kode, serta mendemonstrasikan pemahaman mendalam saya terhadap struktur dasar web sebelum beralih ke framework."

---
