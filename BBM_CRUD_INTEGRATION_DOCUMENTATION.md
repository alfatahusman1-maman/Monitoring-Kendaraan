# Dokumentasi Integrasi CRUD Pengajuan BBM

**Last Updated:** December 23, 2025  
**Status:** ✅ Integrated & Improved

---

## 📋 Daftar Isi
1. [Ringkasan Implementasi](#ringkasan-implementasi)
2. [Alur Approval BBM](#alur-approval-bbm)
3. [CRUD Operations](#crud-operations)
4. [Status Management](#status-management)
5. [File-File Terkait](#file-file-terkait)
6. [Integrasi Sistem](#integrasi-sistem)

---

## 🎯 Ringkasan Implementasi

Sistem CRUD untuk Konfirmasi Pengajuan BBM telah diintegrasikan dengan workflow approval multi-tahap:

```
User Ajukan BBM → Admin Review (PENDING → APPROVED/REJECTED) → Keuangan Validasi (VALIDATED/REJECTED)
```

### Status Flow:

```
┌─────────────────────────────────────────────────────────────────┐
│                  PENGAJUAN BBM (CREATE)                         │
│                 User mengajukan melalui form                    │
│              (status_admin=PENDING, status_keuangan=PENDING)    │
└─────────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│               REVIEW ADMIN (READ + UPDATE)                      │
│                  Admin melihat daftar pending                   │
│         Approve → status_admin=APPROVED                         │
│         Reject → status_admin=REJECTED + catatan               │
└─────────────────────────────────────────────────────────────────┘
                    ↓ (Jika APPROVED)
┌─────────────────────────────────────────────────────────────────┐
│             VALIDASI KEUANGAN (READ + UPDATE)                   │
│               Keuangan melihat yang sudah approved              │
│         Validate → status_keuangan=VALIDATED                    │
│         Reject → status_keuangan=REJECTED + catatan            │
└─────────────────────────────────────────────────────────────────┘
                           ↓
                    ✅ FINAL (VALIDATED)
```

---

## 📌 Alur Approval BBM

### 1. **User Mengajukan BBM (CREATE)**

**File:** `user/ajukan_bbm.php`

#### Form Data:
- `id_kendaraan` - Kendaraan yang akan diisi BBM
- `tanggal` - Tanggal pengajuan
- `jenis_bbm` - Jenis BBM (Pertalite, Pertamax, Solar)
- `liter` - Jumlah liter
- `biaya` - Biaya BBM (Rp)
- `foto_struk` - Upload struk BBM (opsional)

#### Proses:
```php
function createBBMSubmission($id_user, $id_kendaraan, $tanggal, $jenis_bbm, $liter, $biaya, $foto_struk = null) {
    // INSERT INTO bbm dengan:
    // status_admin = 'PENDING'
    // status_keuangan = 'PENDING'
    // created_at = NOW()
}
```

**Database Columns Yang Terisi:**
```sql
INSERT INTO bbm (
    id_user, id_kendaraan, tanggal, jenis_bbm, liter, biaya, foto_struk,
    status_admin, status_keuangan, created_at
) VALUES (...)
```

---

### 2. **Admin Review (READ + UPDATE)**

**File:** `admin/bbm.php` & `admin/bbm_review.php`

#### A. READ - Lihat Daftar Pending

```php
// File: admin/bbm.php
// Query dengan filter status
$filter_sql = "WHERE (b.status_admin='PENDING' OR (b.status_admin IS NULL AND b.status='Pending'))";

SELECT b.*, u.nama, k.no_polisi
FROM bbm b
JOIN users u ON b.id_user = u.id
JOIN kendaraan k ON b.id_kendaraan = k.id
WHERE b.status_admin='PENDING'
ORDER BY created_at ASC
```

**Filter Options:**
- **ALL** - Semua data
- **PENDING** - Menunggu review admin (status_admin='PENDING')
- **APPROVED** - Sudah disetujui admin (status_admin='APPROVED')
- **REJECTED** - Ditolak admin (status_admin='REJECTED')

#### B. UPDATE - Approve

```php
function adminApproveSubmission($jenis, $id_transaksi, $admin_id) {
    UPDATE bbm 
    SET status_admin = 'APPROVED', 
        admin_id = ?, 
        admin_review_date = NOW()
    WHERE id = ?
}
```

**Aksi yang Terjadi:**
- ✅ `status_admin` diubah menjadi `APPROVED`
- ✅ `admin_id` dicatat siapa yang approve
- ✅ `admin_review_date` dicatat waktu approval
- ✅ Notifikasi dikirim ke user: "Pengajuan disetujui, menunggu validasi keuangan"
- ✅ Notifikasi dikirim ke keuangan: "Ada pengajuan untuk divalidasi"

#### C. UPDATE - Reject

```php
function adminRejectSubmission($jenis, $id_transaksi, $admin_id, $catatan) {
    UPDATE bbm 
    SET status_admin = 'REJECTED', 
        catatan_admin = ?, 
        admin_id = ?, 
        admin_review_date = NOW()
    WHERE id = ?
}
```

**Aksi yang Terjadi:**
- ❌ `status_admin` diubah menjadi `REJECTED`
- ❌ `catatan_admin` berisi alasan penolakan
- ✅ `admin_id` dicatat siapa yang reject
- ✅ `admin_review_date` dicatat waktu rejection
- ✅ Notifikasi dikirim ke user: "Pengajuan ditolak. Alasan: [catatan]"

---

### 3. **Keuangan Validasi (READ + UPDATE)**

**File:** `keuangan/validasi_bbm.php`

#### A. READ - Lihat Daftar APPROVED

```php
function getKeuanganPendingSubmissions($type = 'bbm') {
    SELECT b.*, u.nama as nama_user, k.no_polisi
    FROM bbm b
    JOIN users u ON b.id_user = u.id
    JOIN kendaraan k ON b.id_kendaraan = k.id
    LEFT JOIN users admin ON b.admin_id = admin.id
    WHERE b.status_admin = 'APPROVED' 
    AND b.status_keuangan = 'PENDING'
}
```

#### B. UPDATE - Validate

```php
function keuanganValidateSubmission($jenis, $id_transaksi, $keuangan_id) {
    UPDATE bbm 
    SET status_keuangan = 'VALIDATED', 
        keuangan_id = ?, 
        keuangan_review_date = NOW()
    WHERE id = ?
}
```

#### C. UPDATE - Reject

```php
function keuanganRejectSubmission($jenis, $id_transaksi, $keuangan_id, $catatan) {
    UPDATE bbm 
    SET status_keuangan = 'REJECTED', 
        catatan_keuangan = ?, 
        keuangan_id = ?, 
        keuangan_review_date = NOW()
    WHERE id = ?
}
```

---

### 4. **User Melihat Status (READ)**

**File:** `user/ajukan_bbm.php`, `user/dashboard.php`, `user/tanda_terima.php`

```php
SELECT b.*, k.no_polisi
FROM bbm b
JOIN kendaraan k ON b.id_kendaraan = k.id
WHERE b.id_user = $id_user
ORDER BY b.created_at DESC
```

**Status Ditampilkan:**
- **Status Admin:** 
  - ⏳ PENDING (Menunggu Review Admin)
  - ✅ APPROVED (Disetujui Admin)
  - ❌ REJECTED (Ditolak Admin)

- **Status Keuangan:**
  - 📋 PENDING (Menunggu Validasi Keuangan)
  - ✔️ VALIDATED (Tervalidasi)
  - ❌ REJECTED (Ditolak Keuangan)

- **Catatan:** Menampilkan catatan dari admin dan keuangan jika ada

---

### 5. **Delete BBM (DELETE)**

**File:** `admin/bbm.php` & `user/ajukan_bbm.php`

#### A. User Hapus (Status PENDING)

```php
// user/ajukan_bbm.php
if (isset($_GET["hapus"])) {
    // DELETE hanya jika status_admin = PENDING
    DELETE FROM bbm 
    WHERE id = $id 
    AND id_user = $id_user
    AND status_admin = 'PENDING'
}
```

#### B. Admin Hapus (Selalu bisa)

```php
// admin/bbm.php
if(isset($_GET['hapus'])){
    // Hapus file struk jika ada
    // DELETE FROM bbm WHERE id = $id
}
```

---

## 🔄 CRUD Operations Summary

| Operation | User | Admin | Keuangan | Notes |
|-----------|------|-------|----------|-------|
| **CREATE** | ✅ Ajukan | ❌ | ❌ | Melalui form ajukan_bbm.php |
| **READ** | ✅ Lihat riwayat | ✅ Lihat pending/semua | ✅ Lihat approved pending | Filter by status |
| **UPDATE - Approve** | ❌ | ✅ (PENDING→APPROVED) | ✅ (APPROVED→VALIDATED) | Dengan notifikasi |
| **UPDATE - Reject** | ❌ | ✅ (PENDING→REJECTED) | ✅ (APPROVED→REJECTED) | Dengan catatan |
| **DELETE** | ✅ (jika PENDING) | ✅ (semua) | ❌ | Hapus file struk |

---

## 📊 Status Management

### Database Columns

```sql
-- Admin Review Status
status_admin ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING'
admin_id INT                              -- ID admin yang approve/reject
catatan_admin TEXT                        -- Catatan penolakan dari admin
admin_review_date TIMESTAMP NULL          -- Waktu review admin

-- Keuangan Validation Status
status_keuangan ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING'
keuangan_id INT                           -- ID keuangan yang validate/reject
catatan_keuangan TEXT                     -- Catatan penolakan dari keuangan
keuangan_review_date TIMESTAMP NULL       -- Waktu review keuangan

-- Metadata
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

### Status Flow Chart

```
PENDING (Admin) 
    ├─→ APPROVED (Admin)
    │       └─→ PENDING (Keuangan)
    │           ├─→ VALIDATED ✅
    │           └─→ REJECTED (Keuangan) ❌
    │
    └─→ REJECTED (Admin) ❌
```

---

## 📁 File-File Terkait

### User Side
- **`user/ajukan_bbm.php`** - Form ajukan + riwayat dengan status terintegrasi
- **`user/dashboard.php`** - Dashboard dengan statistik pengajuan
- **`user/tanda_terima.php`** - Lihat tanda terima pengajuan

### Admin Side
- **`admin/bbm.php`** - Management panel dengan filter & aksi approve/reject
- **`admin/bbm_review.php`** - Queue pengajuan dengan form tolak modal
- **`admin/dashboard.php`** - Dashboard admin dengan statistik pending

### Keuangan Side
- **`keuangan/validasi_bbm.php`** - Validasi pengajuan yang approved
- **`keuangan/dashboard.php`** - Dashboard keuangan

### Helper & Configuration
- **`helpers/approval_workflow.php`** - Core CRUD functions
  - `createBBMSubmission()`
  - `getUserSubmissions()`
  - `getAdminPendingSubmissions()`
  - `adminApproveSubmission()`
  - `adminRejectSubmission()`
  - `getKeuanganPendingSubmissions()`
  - `keuanganValidateSubmission()`
  - `keuanganRejectSubmission()`

- **`config.php`** - Database connection

### Uploads
- **`uploads/struk_bbm/`** - Tempat simpan file struk BBM

---

## 🔗 Integrasi Sistem

### Notification System
Setiap aksi approval/rejection mengirim notifikasi ke:

```php
// Saat User Submit
notifyAdmins('BBM baru', 'Ada pengajuan BBM untuk direview', 'info')

// Saat Admin Approve
notifyUser($user_id, 'BBM Disetujui Admin', '...menunggu validasi keuangan...', 'success')
notifyKeuangans('BBM Menunggu Validasi', '...untuk divalidasi', 'info')

// Saat Admin Reject
notifyUser($user_id, 'BBM Ditolak', "Alasan: $catatan", 'error')

// Saat Keuangan Validate
notifyUser($user_id, 'BBM Tervalidasi', 'Pengajuan BBM Anda telah tervalidasi', 'success')

// Saat Keuangan Reject
notifyUser($user_id, 'BBM Ditolak Keuangan', "Alasan: $catatan", 'error')
```

### Approval Workflow Logging

Setiap aksi dicatat di audit trail:

```php
logApprovalAction('BBM', $id, 'User', 'Submitted', $user_id, 'User mengajukan BBM')
logApprovalAction('BBM', $id, 'Admin', 'Approved', $admin_id, 'Admin menyetujui')
logApprovalAction('BBM', $id, 'Keuangan', 'Validated', $keuangan_id, 'Keuangan memvalidasi')
```

---

## 🔐 Security & Validation

### Access Control
```php
// User: Hanya bisa lihat data sendiri
$riwayat = getUserSubmissions($id_user, 'bbm');

// Admin: Lihat semua pending, approved, rejected
$pending = getAdminPendingSubmissions('bbm');

// Keuangan: Lihat yang approved & pending keuangan
$pending = getKeuanganPendingSubmissions('bbm');
```

### Input Validation
- ✅ User ID validation
- ✅ Kendaraan ID validation (owner check)
- ✅ File upload validation (format, size)
- ✅ Amount validation (liter, biaya > 0)
- ✅ SQL injection prevention (prepared statements)

### File Security
- ✅ Unique filename for uploaded struk
- ✅ Allowed file extensions only (jpg, jpeg, png, gif, pdf)
- ✅ File size limits
- ✅ Stored outside web root (uploads/struk_bbm/)

---

## ✅ Checklist Integrasi

- [x] **CREATE** - User bisa submit BBM dengan form
- [x] **READ** - Admin/Keuangan bisa lihat pending
- [x] **READ** - User bisa lihat riwayat dengan status terintegrasi
- [x] **UPDATE - Approve** - Admin bisa approve pending → keuangan review
- [x] **UPDATE - Reject** - Admin/Keuangan bisa reject dengan catatan
- [x] **DELETE** - Admin bisa hapus, User bisa hapus jika pending
- [x] **Status Filter** - Filter by PENDING/APPROVED/REJECTED/ALL
- [x] **Notifications** - Notifikasi ke user & admin
- [x] **Audit Trail** - Log setiap aksi approval
- [x] **Modal Forms** - Reject form dengan modal Bootstrap
- [x] **Error Handling** - Try-catch & user-friendly messages
- [x] **UI/UX Improvements** - Better status display dengan badges & icons

---

## 🚀 Testing Checklist

### User Flow
- [ ] User bisa ajukan BBM baru
- [ ] User bisa lihat riwayat dengan status terintegrasi
- [ ] User bisa lihat catatan admin/keuangan
- [ ] User bisa hapus pengajuan jika status PENDING
- [ ] File struk bisa diupload dan terlihat

### Admin Flow
- [ ] Admin bisa lihat daftar pending BBM
- [ ] Admin bisa approve (status → APPROVED)
- [ ] Admin bisa reject dengan catatan
- [ ] Admin bisa filter by status (PENDING/APPROVED/REJECTED/ALL)
- [ ] Admin bisa hapus BBM
- [ ] Admin bisa lihat catatan keuangan

### Keuangan Flow
- [ ] Keuangan bisa lihat yang approved & pending
- [ ] Keuangan bisa validate (status → VALIDATED)
- [ ] Keuangan bisa reject dengan catatan
- [ ] Notifikasi diterima user saat validate/reject

### Notifications
- [ ] User dapat notifikasi saat submit
- [ ] Admin dapat notifikasi ada pengajuan baru
- [ ] Keuangan dapat notifikasi ada yang diapprove
- [ ] User dapat notifikasi saat approve/reject

---

**End of Documentation**
