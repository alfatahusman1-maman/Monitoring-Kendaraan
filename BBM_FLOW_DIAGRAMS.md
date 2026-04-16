# 📊 DIAGRAM & FLOW CHART CRUD BBM

**Tanggal:** December 23, 2025

---

## 1. OVERALL SYSTEM ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         MONITORING KENDARAAN BBM                         │
│                              SISTEM APPROVAL                            │
└─────────────────────────────────────────────────────────────────────────┘

                                 ┌─────────────┐
                                 │   DATABASE  │
                                 │   (bbm)     │
                                 └─────────────┘
                                       △
                                       │
                ┌──────────────────────┼──────────────────────┐
                │                      │                      │
         ┌──────▼──────┐        ┌──────▼──────┐       ┌──────▼──────┐
         │     USER    │        │    ADMIN    │       │  KEUANGAN   │
         │   (pegawai) │        │   (approver)│       │(validator)  │
         └─────────────┘        └─────────────┘       └─────────────┘
                │                      │                      │
                │                      │                      │
         ┌──────▼──────┐        ┌──────▼──────┐       ┌──────▼──────┐
         │  CREATE     │        │    READ     │       │    READ     │
         │  DELETE     │        │   UPDATE    │       │   UPDATE    │
         │ (ajukan_bbm)│        │  (bbm.php)  │       │(validasi_bbm)
         └─────────────┘        └─────────────┘       └─────────────┘
                │                      │                      │
                └──────────────────────┼──────────────────────┘
                                       │
                           ┌───────────▼───────────┐
                           │  APPROVAL WORKFLOW    │
                           │ (helpers/approval_    │
                           │   workflow.php)       │
                           └───────────────────────┘
                                       │
                ┌──────────────────────┼──────────────────────┐
                │                      │                      │
         ┌──────▼──────┐        ┌──────▼──────┐       ┌──────▼──────┐
         │NOTIFICATIONS│        │AUDIT LOGGING│       │  STATUSES   │
         │notifyUser() │        │logApprovalA-│       │  - PENDING  │
         │notifyAdmins │        │ction()      │       │  - APPROVED │
         │notifyKeuangan       │              │       │  - REJECTED │
         └─────────────┘        └──────────────┘       │ - VALIDATED │
                                                       └─────────────┘
```

---

## 2. COMPLETE STATUS FLOW DIAGRAM

```
                         ┌────────────────────┐
                         │   USER SUBMIT BBM  │
                         │  (CREATE operation)│
                         └────────┬───────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │ INSERT INTO bbm         │
                    │ status_admin='PENDING'  │
                    │ status_keuangan=PENDING │
                    │ created_at=NOW()        │
                    └─────────┬───────────────┘
                              │
                              │ NOTIFICATION: notifyAdmins()
                              ▼
        ┌─────────────────────────────────────────┐
        │        ADMIN REVIEW STAGE                │
        │   (admin/bbm.php - READ operation)       │
        └─────────────────────┬───────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
            ┌───────▼────────┐  ┌───────▼────────┐
            │     APPROVE    │  │     REJECT     │
            │ (Update to     │  │ (Update to     │
            │  APPROVED)     │  │  REJECTED)     │
            └───────┬────────┘  └───────┬────────┘
                    │                   │
         ┌──────────▼──────────┐        │
         │ UPDATE bbm          │        │
         │ status_admin=       │        │ UPDATE bbm
         │  'APPROVED'         │        │ status_admin=
         │ admin_id=SET        │        │  'REJECTED'
         │ admin_review_date=  │        │ catatan_admin=
         │  NOW()              │        │  [USER INPUT]
         │                     │        │ admin_id=SET
         │ NOTIFY:             │        │ admin_review_date=
         │ - User (APPROVED)   │        │  NOW()
         │ - Keuangan (REVIEW) │        │
         └──────────┬──────────┘        │ NOTIFY:
                    │                   │ - User (REJECTED)
                    │                   │
                    │                   ▼
                    │            ┌──────────────┐
                    │            │ FINAL STATE  │
                    │            │ REJECTED ❌   │
                    │            │ (no further) │
                    │            └──────────────┘
                    │
                    ▼
        ┌──────────────────────────────────┐
        │   KEUANGAN REVIEW STAGE           │
        │ (keuangan/validasi_bbm.php)       │
        │ status_keuangan='PENDING'         │
        └──────────────┬────────────────────┘
                       │
             ┌─────────┴─────────┐
             │                   │
       ┌─────▼───────┐    ┌─────▼───────┐
       │  VALIDATE   │    │  REJECT     │
       │ (Update to  │    │ (Update to  │
       │ VALIDATED)  │    │ REJECTED)   │
       └─────┬───────┘    └─────┬───────┘
             │                  │
     ┌───────▼────────┐         │
     │ UPDATE bbm     │         │
     │ status_keuangan│         │
     │= 'VALIDATED'   │         │ UPDATE bbm
     │ keuangan_id=   │         │ status_keuangan=
     │  SET           │         │  'REJECTED'
     │ keuangan_review│         │ catatan_keuangan=
     │_date=NOW()     │         │  [USER INPUT]
     │                │         │ keuangan_id=SET
     │ NOTIFY:        │         │ keuangan_review_
     │ - User         │         │  date=NOW()
     │   (VALIDATED)  │         │
     └───────┬────────┘         │ NOTIFY:
             │                  │ - User (REJECTED)
             │                  │
             │                  ▼
             │          ┌────────────────┐
             │          │ FINAL STATE    │
             │          │ REJECTED ❌     │
             │          │ (Keuangan)     │
             │          └────────────────┘
             │
             ▼
    ┌────────────────────┐
    │ FINAL STATE        │
    │ VALIDATED ✅        │
    │ COMPLETE           │
    └────────────────────┘
```

---

## 3. USER INTERACTION FLOW

```
USER (Pegawai)
    │
    ├─► LOGIN
    │    │
    │    ▼
    │   Dashboard
    │    │
    │    ├─► Ajukan BBM ◄─────────────┐
    │    │    │                        │
    │    │    ├─ Select Kendaraan      │
    │    │    ├─ Input Tanggal         │
    │    │    ├─ Input Jenis BBM       │
    │    │    ├─ Input Liter           │
    │    │    ├─ Input Biaya           │
    │    │    ├─ Upload Struk          │
    │    │    │                        │
    │    │    ▼                        │
    │    │   Submit (CREATE)           │
    │    │    │                        │
    │    │    ├─ Validation: Input     │
    │    │    ├─ Validation: File      │
    │    │    ├─ Insert to Database    │
    │    │    │  (status='PENDING')    │
    │    │    │                        │
    │    │    ▼                        │
    │    │   Success Message           │
    │    │    │                        │
    │    │    ├─► Riwayat BBM ◄────────┘
    │    │         │                    Back to edit
    │    │         │                    (if PENDING)
    │    │         │
    │    │         ├─ View All BBM Submissions
    │    │         ├─ Filter: Own submissions
    │    │         │  
    │    │         │ Columns:
    │    │         ├─ No. Polisi
    │    │         ├─ Tanggal
    │    │         ├─ Jenis BBM
    │    │         ├─ Liter
    │    │         ├─ Biaya
    │    │         ├─ Struk (link)
    │    │         ├─ Status Admin ◄─── Updated by Admin
    │    │         ├─ Status Keuangan ◄─ Updated by Keuangan
    │    │         ├─ Catatan Admin ◄─── If rejected
    │    │         ├─ Catatan Keuangan ◄─ If rejected
    │    │         │
    │    │         └─ Actions:
    │    │            ├─ Print (cetak_bbm.php)
    │    │            └─ Delete (only if PENDING)
    │    │
    │    └─► Tanda Terima
    │         └─ View receipt of approved BBM
```

---

## 4. ADMIN INTERACTION FLOW

```
ADMIN (Approver)
    │
    ├─► LOGIN
    │    │
    │    ▼
    │   Dashboard
    │    │
    │    ├─► Konfirmasi BBM ◄────────────────────────────────┐
    │    │    │                                              │
    │    │    ├─ Filter by Status:                           │
    │    │    │  ├─ ALL (Semua)                              │
    │    │    │  ├─ PENDING (Menunggu Review)                │
    │    │    │  ├─ APPROVED (Disetujui)                     │
    │    │    │  └─ REJECTED (Ditolak)                       │
    │    │    │                                              │
    │    │    ├─ Table Display:                              │
    │    │    │  ├─ ID                                       │
    │    │    │  ├─ User (Pegawai)                           │
    │    │    │  ├─ No. Polisi                               │
    │    │    │  ├─ Tanggal                                  │
    │    │    │  ├─ Jenis BBM                                │
    │    │    │  ├─ Liter                                    │
    │    │    │  ├─ Biaya                                    │
    │    │    │  ├─ Struk (link to upload)                   │
    │    │    │  ├─ Status Admin (badge)                     │
    │    │    │  ├─ Status Keuangan (badge)                  │
    │    │    │  │                                           │
    │    │    │  └─ Actions: ◄─── For PENDING only
    │    │    │     ├─ ✅ SETUJU (Approve) ─┐
    │    │    │     └─ ❌ TOLAK (Reject) ───┤
    │    │    │                             │
    │    │    └─ Always Available:          │
    │    │        └─ 🗑 HAPUS (Delete)      │
    │    │                                  │
    │    │        ┌──────────────────────────┤
    │    │        │                          │
    │    │        ▼ SETUJU (POST)            │
    │    │        │                          │
    │    │        └─ adminApproveSubmission()
    │    │           ├─ UPDATE status_admin='APPROVED'
    │    │           ├─ Set admin_id
    │    │           ├─ Set admin_review_date
    │    │           ├─ Notify User (approved)
    │    │           ├─ Notify Keuangan (to review)
    │    │           └─ Log action
    │    │              │
    │    │              ▼
    │    │           Success Message ◄─┐ Back to list
    │    │                              │
    │    │        ┌──────────────────────┤
    │    │        │                      │
    │    │        ▼ TOLAK (Modal Form)  │
    │    │        │                      │
    │    │        └─ Modal Dialog
    │    │           ├─ Show User Name
    │    │           ├─ Input Catatan (textarea)
    │    │           │  [Alasan Penolakan]
    │    │           │
    │    │           ▼
    │    │        Submit (POST)
    │    │           │
    │    │           ▼
    │    │        adminRejectSubmission()
    │    │           ├─ UPDATE status_admin='REJECTED'
    │    │           ├─ Set catatan_admin
    │    │           ├─ Set admin_id
    │    │           ├─ Set admin_review_date
    │    │           ├─ Notify User (rejected)
    │    │           └─ Log action
    │    │              │
    │    │              ▼
    │    │           Success Message
    │    │              │
    │    │              ▼
    │    │           Back to List ◄────┘
    │    │
    │    └─► HAPUS (Delete)
    │         │
    │         ├─ Confirm Dialog
    │         │
    │         ▼
    │         DELETE FROM bbm
    │         └─ Delete file struk
    │            │
    │            ▼
    │         Success/Error Message
```

---

## 5. KEUANGAN INTERACTION FLOW

```
KEUANGAN (Validator)
    │
    ├─► LOGIN
    │    │
    │    ▼
    │   Dashboard
    │    │
    │    └─► Validasi BBM
    │         │
    │         ├─ Fetch: status_admin='APPROVED' 
    │         │          AND status_keuangan='PENDING'
    │         │
    │         ├─ Table Display:
    │         │  ├─ ID
    │         │  ├─ User
    │         │  ├─ No. Polisi
    │         │  ├─ Tanggal
    │         │  ├─ Jenis BBM
    │         │  ├─ Liter
    │         │  ├─ Biaya
    │         │  ├─ Struk (link)
    │         │  ├─ Status Admin (APPROVED)
    │         │  ├─ Status Keuangan (PENDING)
    │         │  ├─ Catatan Admin (if any)
    │         │  │
    │         │  └─ Actions:
    │         │     ├─ ✔️ VALIDASI (Validate) ─┐
    │         │     └─ ❌ TOLAK (Reject) ────┤
    │         │                              │
    │         ├──────────────────────────────┤
    │         │                              │
    │         ▼ VALIDASI (POST)             │
    │         │                              │
    │         └─ keuanganValidateSubmission()
    │            ├─ UPDATE status_keuangan='VALIDATED'
    │            ├─ Set keuangan_id
    │            ├─ Set keuangan_review_date
    │            ├─ Notify User (validated)
    │            └─ Log action
    │               │
    │               ▼
    │            Success Message ◄──┐
    │                               │
    │         ┌──────────────────────┤
    │         │                      │
    │         ▼ TOLAK (Modal Form)  │
    │         │                      │
    │         └─ Modal Dialog
    │            ├─ Show User Name
    │            ├─ Show Admin Catatan (if rejected)
    │            ├─ Input Catatan (textarea)
    │            │  [Alasan Penolakan Keuangan]
    │            │
    │            ▼
    │         Submit (POST)
    │            │
    │            ▼
    │         keuanganRejectSubmission()
    │            ├─ UPDATE status_keuangan='REJECTED'
    │            ├─ Set catatan_keuangan
    │            ├─ Set keuangan_id
    │            ├─ Set keuangan_review_date
    │            ├─ Notify User (rejected)
    │            └─ Log action
    │               │
    │               ▼
    │            Success Message
    │               │
    │               ▼
    │            Back to List ◄─────┘
```

---

## 6. DATABASE STATE TRANSITIONS

```
Initial State (CREATE)
┌───────────────────────────────────────────────────────────────┐
│ INSERT INTO bbm (...)                                         │
│ status_admin      = 'PENDING'                                 │
│ status_keuangan   = 'PENDING'                                 │
│ admin_id          = NULL                                      │
│ keuangan_id       = NULL                                      │
│ catatan_admin     = NULL                                      │
│ catatan_keuangan  = NULL                                      │
│ created_at        = NOW()                                     │
└───────────────────────────────────────────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────┐
        │ ADMIN REVIEW STATE                   │
        │                                      │
        ├─ Option 1: APPROVED                  │
        │  ├─ status_admin = 'APPROVED'        │
        │  ├─ status_keuangan = 'PENDING'      │
        │  ├─ admin_id = [admin_id]            │
        │  ├─ admin_review_date = NOW()        │
        │  ├─ catatan_admin = NULL             │
        │  │                                   │
        │  └─► KEUANGAN REVIEW                 │
        │      │                               │
        │      ├─ Option 1a: VALIDATED         │
        │      │  ├─ status_keuangan=VALIDATED │
        │      │  ├─ keuangan_id=[id]          │
        │      │  └─ FINAL: COMPLETED ✅        │
        │      │                               │
        │      └─ Option 1b: REJECTED          │
        │         ├─ status_keuangan=REJECTED  │
        │         ├─ catatan_keuangan=[text]   │
        │         ├─ keuangan_id=[id]          │
        │         └─ FINAL: REJECTED ❌         │
        │                                      │
        └─ Option 2: REJECTED                  │
           ├─ status_admin = 'REJECTED'        │
           ├─ catatan_admin = [text]           │
           ├─ admin_id = [admin_id]            │
           ├─ admin_review_date = NOW()        │
           ├─ status_keuangan = 'PENDING'      │
           └─ FINAL: REJECTED ❌                │
                (tidak dilanjut ke keuangan)
```

---

## 7. NOTIFICATION FLOW

```
User Submit BBM
    │
    ├─► INSERT into database
    │    │
    │    ├─► notifyAdmins()
    │    │    │
    │    │    └─ Send: "BBM baru dari [User]"
    │    │       To: All Admin users
    │    │       Link: admin/bbm_review.php
    │    │
    │    └─► logApprovalAction()
    │         └─ Record: User, Submitted, [notes]
    │
    ▼
Admin Approve
    │
    ├─► UPDATE status_admin='APPROVED'
    │    │
    │    ├─► notifyUser()
    │    │    │
    │    │    └─ Send: "BBM Anda disetujui admin"
    │    │       To: User
    │    │       Type: success
    │    │
    │    ├─► notifyKeuangans()
    │    │    │
    │    │    └─ Send: "Ada BBM untuk divalidasi"
    │    │       To: All Keuangan users
    │    │       Link: keuangan/validasi_bbm.php
    │    │
    │    └─► logApprovalAction()
    │         └─ Record: Admin, Approved, [id]
    │
    ▼
Admin Reject
    │
    ├─► UPDATE status_admin='REJECTED'
    │    │
    │    ├─► notifyUser()
    │    │    │
    │    │    └─ Send: "BBM Anda ditolak"
    │    │       Content: "Alasan: [catatan]"
    │    │       Type: error
    │    │
    │    └─► logApprovalAction()
    │         └─ Record: Admin, Rejected, [catatan]
    │
    ▼
Keuangan Validate
    │
    ├─► UPDATE status_keuangan='VALIDATED'
    │    │
    │    ├─► notifyUser()
    │    │    │
    │    │    └─ Send: "BBM Anda tervalidasi"
    │    │       Type: success
    │    │
    │    └─► logApprovalAction()
    │         └─ Record: Keuangan, Validated, [id]
    │
    ▼
Keuangan Reject
    │
    └─► UPDATE status_keuangan='REJECTED'
        │
        ├─► notifyUser()
        │    │
        │    └─ Send: "BBM Anda ditolak keuangan"
        │       Content: "Alasan: [catatan]"
        │       Type: error
        │
        └─► logApprovalAction()
             └─ Record: Keuangan, Rejected, [catatan]
```

---

## 8. CRUD OPERATIONS MATRIX

```
┌─────────────────────────────────────────────────────────────────┐
│                    OPERATION: CREATE                            │
├─────────────────────────────────────────────────────────────────┤
│ Who:      USER (Pegawai)                                        │
│ Where:    user/ajukan_bbm.php                                  │
│ Function: createBBMSubmission()                                 │
│ SQL:      INSERT INTO bbm (...)                                │
│ Status:   status_admin='PENDING', status_keuangan='PENDING'     │
│ Result:   New BBM pending admin review                         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│              OPERATION: READ (User Own)                         │
├─────────────────────────────────────────────────────────────────┤
│ Who:      USER (Pegawai)                                        │
│ Where:    user/ajukan_bbm.php (Riwayat table)                  │
│ Function: getUserSubmissions()                                  │
│ SQL:      SELECT * FROM bbm WHERE id_user=$id                  │
│ Display:  - Status Admin + Keuangan                            │
│           - Catatan (if any)                                   │
│ Filter:   Own submissions only                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│            OPERATION: READ (Admin Pending)                      │
├─────────────────────────────────────────────────────────────────┤
│ Who:      ADMIN                                                 │
│ Where:    admin/bbm.php                                        │
│ Function: getAdminPendingSubmissions()                          │
│ SQL:      SELECT * FROM bbm                                    │
│           WHERE status_admin IN (PENDING, APPROVED, REJECTED)  │
│ Display:  Full details + statuses                              │
│ Filter:   PENDING, APPROVED, REJECTED, or ALL                  │
│ Action:   Click on row for approve/reject                      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│         OPERATION: READ (Keuangan Approved Pending)             │
├─────────────────────────────────────────────────────────────────┤
│ Who:      KEUANGAN                                              │
│ Where:    keuangan/validasi_bbm.php                            │
│ Function: getKeuanganPendingSubmissions()                       │
│ SQL:      SELECT * FROM bbm                                    │
│           WHERE status_admin='APPROVED'                         │
│           AND status_keuangan='PENDING'                         │
│ Display:  Full details + admin notes                           │
│ Filter:   Only approved pending validation                      │
│ Action:   Click to validate/reject                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│          OPERATION: UPDATE (Admin Approve)                      │
├─────────────────────────────────────────────────────────────────┤
│ Who:      ADMIN (for PENDING only)                             │
│ Where:    admin/bbm.php (button click)                         │
│ Function: adminApproveSubmission()                              │
│ SQL:      UPDATE bbm SET                                       │
│             status_admin='APPROVED',                            │
│             admin_id=?,                                         │
│             admin_review_date=NOW()                             │
│ Result:   - Status changed to APPROVED                         │
│           - Admin recorded                                      │
│           - Date/time recorded                                  │
│           - Notifications sent                                  │
│           - Action logged                                       │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│           OPERATION: UPDATE (Admin Reject)                      │
├─────────────────────────────────────────────────────────────────┤
│ Who:      ADMIN (for PENDING only)                             │
│ Where:    admin/bbm.php (modal form)                           │
│ Function: adminRejectSubmission()                               │
│ SQL:      UPDATE bbm SET                                       │
│             status_admin='REJECTED',                            │
│             catatan_admin=?,                                    │
│             admin_id=?,                                         │
│             admin_review_date=NOW()                             │
│ Input:    Catatan (textarea)                                   │
│ Result:   - Status changed to REJECTED                         │
│           - Reason recorded                                     │
│           - Admin recorded                                      │
│           - Notifications sent to user                          │
│           - Action logged                                       │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│         OPERATION: UPDATE (Keuangan Validate)                   │
├─────────────────────────────────────────────────────────────────┤
│ Who:      KEUANGAN (for APPROVED & PENDING keuangan)           │
│ Where:    keuangan/validasi_bbm.php                            │
│ Function: keuanganValidateSubmission()                          │
│ SQL:      UPDATE bbm SET                                       │
│             status_keuangan='VALIDATED',                        │
│             keuangan_id=?,                                      │
│             keuangan_review_date=NOW()                          │
│ Result:   - Status changed to VALIDATED                        │
│           - Keuangan recorded                                   │
│           - Notifications sent                                  │
│           - Action logged                                       │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│          OPERATION: UPDATE (Keuangan Reject)                    │
├─────────────────────────────────────────────────────────────────┤
│ Who:      KEUANGAN (for APPROVED & PENDING keuangan)           │
│ Where:    keuangan/validasi_bbm.php (modal form)               │
│ Function: keuanganRejectSubmission()                            │
│ SQL:      UPDATE bbm SET                                       │
│             status_keuangan='REJECTED',                         │
│             catatan_keuangan=?,                                 │
│             keuangan_id=?,                                      │
│             keuangan_review_date=NOW()                          │
│ Input:    Catatan (textarea)                                   │
│ Result:   - Status changed to REJECTED                         │
│           - Reason recorded                                     │
│           - Keuangan recorded                                   │
│           - Notifications sent to user                          │
│           - Action logged                                       │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                  OPERATION: DELETE (User)                       │
├─────────────────────────────────────────────────────────────────┤
│ Who:      USER (for own PENDING only)                          │
│ Where:    user/ajukan_bbm.php (delete button)                  │
│ Function: Direct DELETE                                        │
│ SQL:      DELETE FROM bbm WHERE id=? AND id_user=?             │
│           AND status_admin IN ('PENDING', 'Pending')            │
│ Result:   - BBM deleted from database                          │
│           - File struk deleted                                  │
│           - Message shown                                       │
│ Security: Only own submissions, only if PENDING                │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                  OPERATION: DELETE (Admin)                      │
├─────────────────────────────────────────────────────────────────┤
│ Who:      ADMIN (any status)                                    │
│ Where:    admin/bbm.php (delete button)                        │
│ Function: Direct DELETE                                        │
│ SQL:      DELETE FROM bbm WHERE id=?                           │
│ Confirm:  JS confirm dialog                                    │
│ Result:   - BBM deleted from database                          │
│           - File struk deleted                                  │
│           - Message shown                                       │
│ Note:     No role check - admin can delete anything            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 9. FEATURE SUMMARY TABLE

```
╔════════════════════════════════════════════════════════════════════╗
║            CRUD OPERATIONS - COMPLETE MATRIX                       ║
╠════════════════════════════════════════════════════════════════════╣
║ Operation │ User | Admin | Keuangan | File | Function            ║
╠════════════════════════════════════════════════════════════════════╣
║ CREATE    │  ✅  │  ❌   │    ❌    │ ajax │ createBBMSubmission ║
║ READ Own  │  ✅  │  ❌   │    ❌    │ ajax │ getUserSubmissions  ║
║ READ Pend │  ❌  │  ✅   │    ❌    │ ajax │ getAdminPendingS... ║
║ READ Appr │  ❌  │  ❌   │    ✅    │ ajax │ getKeuanganPending  ║
║ APPR(Ad)  │  ❌  │  ✅   │    ❌    │ ajax │ adminApproveSubmis  ║
║ REJ(Ad)   │  ❌  │  ✅   │    ❌    │ ajax │ adminRejectSubmis   ║
║ VAL(Keu)  │  ❌  │  ❌   │    ✅    │ ajax │ keuanganValidateSub ║
║ REJ(Keu)  │  ❌  │  ❌   │    ✅    │ ajax │ keuanganRejectSubm  ║
║ DELETE    │ ✅*  │  ✅   │    ❌    │ ajax │ Direct DELETE       ║
║           │Pend │ Any   │          │      │                     ║
╚════════════════════════════════════════════════════════════════════╝
```

---

**End of Diagram & Flow Chart**

*All diagrams are ASCII-based for easy reading in text editors.*
