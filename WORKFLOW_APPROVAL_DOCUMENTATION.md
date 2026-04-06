# 📋 Workflow Approval System - BBM & Servis

## 🔄 Alur Proses

```
USER (Input Struk)
    ↓
    └─→ Mengajukan BBM / Servis
        - Upload foto struk
        - Input detail (tanggal, liter/jenis, biaya)
        - Status: PENDING USER
    ↓
ADMIN (Review & Approve)
    ├─→ Melihat semua pengajuan dari user
    ├─→ Memeriksa struk dan detail
    └─→ Approve/Reject
        - Status: APPROVED ADMIN → lanjut ke keuangan
        - Status: REJECTED ADMIN → kembali ke user
    ↓
KEUANGAN (Final Validation & Payment)
    ├─→ Melihat pengajuan yang sudah di-approve admin
    ├─→ Validasi dokumen & biaya
    └─→ Approve/Reject
        - Status: VALIDATED KEUANGAN → Selesai
        - Status: REJECTED KEUANGAN → kembali ke admin
    ↓
COMPLETION (Tanda Terima)
    └─→ Generate tanda terima & arsip
```

---

## 📊 Status Flow

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  PENDING (User)  → APPROVED_ADMIN → VALIDATED_KEUANGAN │
│       ↓                  ↓                 ↓            │
│   (Tunggu)        (Checked Admin)   (Final OK)          │
│       │                  │                 │            │
│       └──→ REJECTED ←────┴────────→ REJECTED            │
│            (Ditolak)              (Ditolak Keuangan)    │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🗂️ Database Schema Updates

### Tabel BBM (Enhanced)
```sql
CREATE TABLE bbm (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_user INT NOT NULL,
  id_kendaraan INT NOT NULL,
  tanggal DATE NOT NULL,
  liter DECIMAL(8,2),
  biaya DECIMAL(12,2) DEFAULT 0,
  
  -- File Upload
  foto_struk VARCHAR(255),
  
  -- Admin Review
  status_admin ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  catatan_admin TEXT,
  admin_id INT,
  admin_review_date TIMESTAMP,
  
  -- Keuangan Validation
  status_keuangan ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING',
  catatan_keuangan TEXT,
  keuangan_id INT,
  keuangan_review_date TIMESTAMP,
  
  -- Timeline
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_user) REFERENCES users(id),
  FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id),
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (keuangan_id) REFERENCES users(id)
);
```

### Tabel Servis (Enhanced)
```sql
CREATE TABLE servis (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_user INT NOT NULL,
  id_kendaraan INT NOT NULL,
  tanggal DATE NOT NULL,
  jenis_servis VARCHAR(100),
  biaya DECIMAL(12,2),
  
  -- File Upload
  foto_struk VARCHAR(255),
  
  -- Admin Review
  status_admin ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  catatan_admin TEXT,
  admin_id INT,
  admin_review_date TIMESTAMP,
  
  -- Keuangan Validation
  status_keuangan ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING',
  catatan_keuangan TEXT,
  keuangan_id INT,
  keuangan_review_date TIMESTAMP,
  
  -- Timeline
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_user) REFERENCES users(id),
  FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id),
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (keuangan_id) REFERENCES users(id)
);
```

---

## 👥 User Roles & Permissions

### USER ROLE
**Can:**
- View own submissions (BBM & Servis)
- Create new submission
- Upload struk/receipt photo
- Track status (Pending → Approved → Validated)
- Resubmit if rejected

**Cannot:**
- Approve/Reject
- View other user submissions
- Access validation features

**Pages:**
- `user/dashboard.php` - Summary & quick stats
- `user/ajukan_bbm.php` - Submit BBM
- `user/ajukan_servis.php` - Submit Servis
- `user/riwayat.php` - View history & status

---

### ADMIN ROLE
**Can:**
- View all pending user submissions
- Review foto struk & details
- Approve/Reject submissions
- Add review notes
- Track approval timeline

**Cannot:**
- Validate payment (Keuangan's job)
- Edit user submissions
- Access keuangan features

**Pages:**
- `admin/dashboard.php` - Summary & analytics
- `admin/bbm_review.php` - Review BBM submissions
- `admin/servis_review.php` - Review Servis submissions
- `admin/approval_history.php` - View history

---

### KEUANGAN ROLE
**Can:**
- View approved submissions (from admin)
- Validate payment & dokumen
- Approve/Reject final validation
- Add validation notes
- Generate payment reports

**Cannot:**
- Approve at first stage
- Edit approved submissions
- Access user/admin features

**Pages:**
- `keuangan/dashboard.php` - Summary & validation queue
- `keuangan/validasi_bbm.php` - Validate BBM
- `keuangan/validasi_servis.php` - Validate Servis
- `keuangan/laporan_keuangan.php` - Payment reports

---

## 📝 Detailed Workflow

### Step 1: User Submit (Initial Creation)
```
USER ACTION:
├─ Select kendaraan
├─ Input tanggal
├─ Input liter/jenis servis
├─ Input biaya (estimasi)
├─ Upload foto struk
└─ Submit

DATABASE UPDATE:
├─ Insert to bbm/servis table
├─ status_admin = 'PENDING'
├─ status_keuangan = 'PENDING'
├─ Save foto file
└─ created_at = NOW()

NOTIFICATION:
└─ Send email to ADMIN: "BBM baru dari User: Sunardi"
```

---

### Step 2: Admin Review
```
ADMIN ACTION:
├─ View pending submissions
├─ Click to see details + foto struk
├─ Review accuracy
├─ Add notes/comments
└─ Choose: APPROVE or REJECT

IF APPROVED:
├─ Update status_admin = 'APPROVED'
├─ Update admin_id = current_admin_id
├─ Update admin_review_date = NOW()
├─ Status moves to Keuangan queue
├─ Send notification to KEUANGAN

IF REJECTED:
├─ Update status_admin = 'REJECTED'
├─ Add catatan_admin (reason)
├─ Update admin_review_date = NOW()
├─ Send notification to USER with reason
└─ User can resubmit after correction
```

---

### Step 3: Keuangan Validation
```
KEUANGAN ACTION:
├─ View approved submissions (status_admin = 'APPROVED')
├─ Check payment details
├─ Validate biaya vs struk
├─ Add validation notes
└─ Choose: VALIDATE or REJECT

IF VALIDATED:
├─ Update status_keuangan = 'VALIDATED'
├─ Update keuangan_id = current_keuangan_id
├─ Update keuangan_review_date = NOW()
├─ Generate tanda_terima record
├─ Mark as completed
├─ Send notification to USER & ADMIN

IF REJECTED:
├─ Update status_keuangan = 'REJECTED'
├─ Add catatan_keuangan (reason)
├─ Status goes back to admin for review
├─ Send notification to ADMIN with reason
└─ Admin can revise & resubmit to keuangan
```

---

## 🔔 Notification System

### Email Notifications
1. **To ADMIN** when user submits new BBM/Servis
   - Subject: "Pengajuan BBM Baru dari Sunardi"
   - Content: Details & link to review

2. **To KEUANGAN** when admin approves
   - Subject: "BBM Menunggu Validasi - Sunardi"
   - Content: Details & link to validate

3. **To USER** when rejected at any stage
   - Subject: "Pengajuan Anda Ditolak"
   - Content: Reason & link to resubmit

4. **To USER** when validated
   - Subject: "Pengajuan Anda Disetujui"
   - Content: Confirmation & tanda terima

---

## 📊 Dashboard Summaries

### User Dashboard
```
┌─────────────────────────────────────────┐
│  PENGAJUAN SAYA (Total)                 │
├─────────────────────────────────────────┤
│  ⏳ Pending User     : 3                 │
│  👁️  Under Review     : 2                 │
│  ✅ Approved          : 5                 │
│  ❌ Rejected          : 1                 │
│  ✔️  Validated        : 15                │
└─────────────────────────────────────────┘

Pending Actions:
- BBM #123 - Pending User Review (5 days)
- Servis #456 - Pending Keuangan (2 days)
```

### Admin Dashboard
```
┌─────────────────────────────────────────┐
│  REVIEW QUEUE                           │
├─────────────────────────────────────────┤
│  🔴 Urgent (>5 days pending)  : 2       │
│  🟡 Medium (2-5 days)         : 4       │
│  🟢 New (< 2 days)            : 8       │
│                                         │
│  Total Pending Review: 14               │
│  Approved This Month: 45                │
│  Rejected This Month: 2                 │
└─────────────────────────────────────────┘
```

### Keuangan Dashboard
```
┌─────────────────────────────────────────┐
│  VALIDATION QUEUE                       │
├─────────────────────────────────────────┤
│  📋 Pending Validation: 8                │
│  ✅ Validated This Month: 42             │
│  ❌ Rejected: 3                          │
│  💰 Total Value (Pending): Rp 15.500.000│
└─────────────────────────────────────────┘
```

---

## 📂 File Organization

```
monitoring_kendaraan/
├── user/
│   ├── dashboard.php           [Summary stats]
│   ├── ajukan_bbm.php          [Create BBM]
│   ├── ajukan_servis.php       [Create Servis]
│   ├── riwayat.php             [View history]
│   └── layout/sidebar.php
│
├── admin/
│   ├── dashboard.php           [Review queue]
│   ├── bbm_review.php          [Review BBM]
│   ├── servis_review.php       [Review Servis]
│   ├── approval_history.php    [View history]
│   └── layout/sidebar.php
│
├── keuangan/
│   ├── dashboard.php           [Validation queue]
│   ├── validasi_bbm.php        [Validate BBM]
│   ├── validasi_servis.php     [Validate Servis]
│   ├── laporan_keuangan.php    [Reports]
│   └── layout/sidebar.php
│
├── uploads/
│   ├── struk_bbm/              [BBM receipts]
│   └── struk_servis/           [Servis receipts]
│
└── css/
    └── buttons.css
```

---

## ⚙️ Implementation Checklist

Database:
- [ ] Create migration file to alter bbm/servis tables
- [ ] Add new columns (status_admin, status_keuangan, etc.)
- [ ] Add foreign keys

Backend:
- [ ] Helper functions for status management
- [ ] Notification system
- [ ] File upload handler

User Pages:
- [ ] Enhance ajukan_bbm.php
- [ ] Enhance ajukan_servis.php
- [ ] Create riwayat.php

Admin Pages:
- [ ] Create bbm_review.php
- [ ] Create servis_review.php
- [ ] Enhance dashboard.php
- [ ] Create approval_history.php

Keuangan Pages:
- [ ] Create validasi_bbm.php
- [ ] Create validasi_servis.php
- [ ] Enhance dashboard.php
- [ ] Create laporan_keuangan.php

---

## 🎯 Key Features

✅ **Multi-stage Approval:** User → Admin → Keuangan
✅ **File Upload:** Photo/receipt upload at submission
✅ **Status Tracking:** Real-time status update
✅ **Audit Trail:** Complete timeline of approvals
✅ **Notifications:** Email & dashboard alerts
✅ **Rejection Workflow:** Clear reason & resubmit option
✅ **Reports:** Finance reports & analytics
✅ **Role-based Access:** Different views for each role

---

**Next Step:** Create database migration & implement each module
