<?php
/**
 * Workflow Approval Helper Functions
 * Functions for managing approval workflow (User → Admin → Keuangan)
 */

// Include database connection
require_once __DIR__ . '/../config.php';

// ============================================
// STATUS & STATUS COLORS
// ============================================

/**
 * Get status display info (color, label, icon)
 */
function getStatusInfo($status, $stage = 'admin') {
    $statusMap = [
        'admin' => [
            'PENDING' => [
                'label' => 'Menunggu Review',
                'color' => 'warning',
                'bg' => '#fff3cd',
                'icon' => '⏳',
                'class' => 'badge bg-warning'
            ],
            'APPROVED' => [
                'label' => 'Disetujui',
                'color' => 'success',
                'bg' => '#d4edda',
                'icon' => '✅',
                'class' => 'badge bg-success'
            ],
            'REJECTED' => [
                'label' => 'Ditolak',
                'color' => 'danger',
                'bg' => '#f8d7da',
                'icon' => '❌',
                'class' => 'badge bg-danger'
            ]
        ],
        'keuangan' => [
            'PENDING' => [
                'label' => 'Menunggu Validasi',
                'color' => 'info',
                'bg' => '#cfe2ff',
                'icon' => '📋',
                'class' => 'badge bg-info'
            ],
            'VALIDATED' => [
                'label' => 'Tervalidasi',
                'color' => 'success',
                'bg' => '#d4edda',
                'icon' => '✔️',
                'class' => 'badge bg-success'
            ],
            'REJECTED' => [
                'label' => 'Ditolak',
                'color' => 'danger',
                'bg' => '#f8d7da',
                'icon' => '❌',
                'class' => 'badge bg-danger'
            ]
        ]
    ];
    
    return $statusMap[$stage][$status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
}

/**
 * Get overall submission status
 */
function getOverallStatus($status_admin, $status_keuangan) {
    if ($status_admin === 'REJECTED') {
        return ['label' => 'Ditolak Admin', 'color' => 'danger', 'icon' => '❌'];
    }
    if ($status_admin === 'PENDING') {
        return ['label' => 'Menunggu Review Admin', 'color' => 'warning', 'icon' => '⏳'];
    }
    if ($status_keuangan === 'REJECTED') {
        return ['label' => 'Ditolak Keuangan', 'color' => 'danger', 'icon' => '❌'];
    }
    if ($status_keuangan === 'PENDING') {
        return ['label' => 'Menunggu Validasi Keuangan', 'color' => 'info', 'icon' => '📋'];
    }
    if ($status_keuangan === 'VALIDATED') {
        return ['label' => 'Selesai & Tervalidasi', 'color' => 'success', 'icon' => '✅'];
    }
    return ['label' => 'Unknown', 'color' => 'secondary', 'icon' => '❓'];
}

// ============================================
// USER SUBMISSION FUNCTIONS
// ============================================

/**
 * Create BBM submission by user
 */
function createBBMSubmission($id_user, $id_kendaraan, $tanggal, $jenis_bbm, $liter, $biaya, $foto_struk = null) {
    global $conn;

    $id_user = intval($id_user);
    $id_kendaraan = intval($id_kendaraan);
    $jenis_bbm = $conn->real_escape_string(trim($jenis_bbm));
    $liter = floatval($liter);
    $biaya = floatval($biaya);

    $stmt = $conn->prepare("
        INSERT INTO bbm (id_user, id_kendaraan, tanggal, jenis_bbm, liter, biaya, foto_struk, status_admin, status_keuangan, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING', 'PENDING', NOW())
    ");

    if (!$stmt) {
        return false;
    }

    // bind types: i i s s d d s
    $stmt->bind_param("iissdds", $id_user, $id_kendaraan, $tanggal, $jenis_bbm, $liter, $biaya, $foto_struk);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        $bbm_id = $conn->insert_id;
        logApprovalAction('BBM', $bbm_id, 'User', 'Submitted', $id_user, 'User mengajukan BBM baru');
        notifyAdmins('BBM baru dari ' . getUserName($id_user), "Ada pengajuan BBM baru yang memerlukan review", 'info', "/admin/bbm_review.php?id=$bbm_id");
        return $bbm_id;
    }
    return false;
}

/**
 * Create Servis submission by user
 */
function createServisSubmission($id_user, $id_kendaraan, $tanggal, $jenis_servis, $biaya, $foto_struk = null) {
    global $conn;
    
    $id_user = intval($id_user);
    $id_kendaraan = intval($id_kendaraan);
    $biaya = floatval($biaya);

    // sanitize inputs
    $tanggal = $conn->real_escape_string(trim($tanggal));
    $jenis_servis = $conn->real_escape_string(trim($jenis_servis));

    $sql = "INSERT INTO servis (id_user, id_kendaraan, tanggal, jenis_servis, biaya, foto_struk, status_admin, status_keuangan, created_at) VALUES (?, ?, ?, ?, ?, ?, 'PENDING', 'PENDING', NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    // bind types: id_user(i), id_kendaraan(i), tanggal(s), jenis_servis(s), biaya(d), foto_struk(s)
    $stmt->bind_param("iissds", $id_user, $id_kendaraan, $tanggal, $jenis_servis, $biaya, $foto_struk);
    $result = $stmt->execute();
    $stmt->close();
    if ($result) {
        $servis_id = $conn->insert_id;
        logApprovalAction('Servis', $servis_id, 'User', 'Submitted', $id_user, 'User mengajukan Servis baru');
        notifyAdmins('Servis baru dari ' . getUserName($id_user), "Ada pengajuan Servis baru yang memerlukan review", 'info', "/admin/servis_review.php?id=$servis_id");
        return $servis_id;
    }
    return false;
}

/**
 * Get user's submissions (BBM & Servis)
 */
function getUserSubmissions($id_user, $type = 'all') {
    global $conn;
    $id_user = intval($id_user);
    
    if ($type === 'bbm' || $type === 'all') {
        $bbm = $conn->query("
            SELECT 'BBM' as tipe, b.* FROM bbm b
            WHERE b.id_user = $id_user
            ORDER BY b.created_at DESC
        ");
        $bbm_data = $bbm->fetch_all(MYSQLI_ASSOC);
    } else {
        $bbm_data = [];
    }
    
    if ($type === 'servis' || $type === 'all') {
        $servis = $conn->query("
            SELECT 'Servis' as tipe, s.* FROM servis s
            WHERE s.id_user = $id_user
            ORDER BY s.created_at DESC
        ");
        $servis_data = $servis->fetch_all(MYSQLI_ASSOC);
    } else {
        $servis_data = [];
    }
    
    if ($type === 'all') {
        return array_merge($bbm_data, $servis_data);
    } elseif ($type === 'bbm') {
        return $bbm_data;
    } else {
        return $servis_data;
    }
}

/**
 * Get submission count by status for user
 */
function getUserSubmissionStats($id_user) {
    global $conn;
    $id_user = intval($id_user);
    
    $stats = [];
    
    // Pending Admin Review
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE id_user = $id_user AND status_admin = 'PENDING'");
    $stats['pending_admin'] = $result->fetch_assoc()['count'];
    
    // Approved by Admin, Pending Keuangan
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE id_user = $id_user AND status_admin = 'APPROVED' AND status_keuangan = 'PENDING'");
    $stats['under_review'] = $result->fetch_assoc()['count'];
    
    // Validated by Keuangan
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE id_user = $id_user AND status_keuangan = 'VALIDATED'");
    $stats['validated'] = $result->fetch_assoc()['count'];
    
    // Rejected
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE id_user = $id_user AND (status_admin = 'REJECTED' OR status_keuangan = 'REJECTED')");
    $stats['rejected'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// ============================================
// ADMIN APPROVAL FUNCTIONS
// ============================================

/**
 * Get pending submissions for admin review
 */
function getAdminPendingSubmissions($type = 'all', $limit = 50) {
    global $conn;
    
    if ($type === 'bbm' || $type === 'all') {
        $bbm = $conn->query("
            SELECT 'BBM' as tipe, b.*, u.nama as nama_user, k.no_polisi
            FROM bbm b
            JOIN users u ON b.id_user = u.id
            JOIN kendaraan k ON b.id_kendaraan = k.id
            WHERE b.status_admin = 'PENDING'
            ORDER BY b.created_at ASC
            LIMIT $limit
        ");
        $bbm_data = $bbm->fetch_all(MYSQLI_ASSOC);
    } else {
        $bbm_data = [];
    }
    
    if ($type === 'servis' || $type === 'all') {
        $servis = $conn->query("
            SELECT 'Servis' as tipe, s.*, u.nama as nama_user, k.no_polisi
            FROM servis s
            JOIN users u ON s.id_user = u.id
            JOIN kendaraan k ON s.id_kendaraan = k.id
            WHERE s.status_admin = 'PENDING'
            ORDER BY s.created_at ASC
            LIMIT $limit
        ");
        $servis_data = $servis->fetch_all(MYSQLI_ASSOC);
    } else {
        $servis_data = [];
    }
    
    if ($type === 'all') {
        return array_merge($bbm_data, $servis_data);
    } elseif ($type === 'bbm') {
        return $bbm_data;
    } else {
        return $servis_data;
    }
}

/**
 * Admin approve submission
 */
function adminApproveSubmission($jenis, $id_transaksi, $admin_id) {
    global $conn;
    $jenis = strtoupper($jenis);
    $id_transaksi = intval($id_transaksi);
    $admin_id = intval($admin_id);
    
    $table = ($jenis === 'BBM') ? 'bbm' : 'servis';
    
    $stmt = $conn->prepare("
        UPDATE $table 
        SET status_admin = 'APPROVED', admin_id = ?, admin_review_date = NOW()
        WHERE id = ?
    ");
    
    $stmt->bind_param("ii", $admin_id, $id_transaksi);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        logApprovalAction($jenis, $id_transaksi, 'Admin', 'Approved', $admin_id, 'Admin menyetujui pengajuan');
        
        // Get user ID for notification
        $user_result = $conn->query("SELECT id_user FROM $table WHERE id = $id_transaksi");
        $user_data = $user_result->fetch_assoc();
        $user_id = $user_data['id_user'];
        
        notifyUser($user_id, "$jenis Disetujui Admin", "Pengajuan $jenis Anda telah disetujui admin. Menunggu validasi keuangan.", 'success', "/user/riwayat.php");
        notifyKeuangans("$jenis Menunggu Validasi", "Ada pengajuan $jenis yang disetujui admin, menunggu validasi Anda", 'info', "/keuangan/validasi_" . strtolower($jenis) . ".php?id=$id_transaksi");
        
        return true;
    }
    return false;
}

/**
 * Admin reject submission
 */
function adminRejectSubmission($jenis, $id_transaksi, $admin_id, $catatan) {
    global $conn;
    $jenis = strtoupper($jenis);
    $id_transaksi = intval($id_transaksi);
    $admin_id = intval($admin_id);
    $catatan = $conn->real_escape_string($catatan);
    
    $table = ($jenis === 'BBM') ? 'bbm' : 'servis';
    
    $stmt = $conn->prepare("
        UPDATE $table 
        SET status_admin = 'REJECTED', catatan_admin = ?, admin_id = ?, admin_review_date = NOW()
        WHERE id = ?
    ");
    
    $stmt->bind_param("sii", $catatan, $admin_id, $id_transaksi);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        logApprovalAction($jenis, $id_transaksi, 'Admin', 'Rejected', $admin_id, $catatan);
        
        // Get user ID for notification
        $user_result = $conn->query("SELECT id_user FROM $table WHERE id = $id_transaksi");
        $user_data = $user_result->fetch_assoc();
        $user_id = $user_data['id_user'];
        
        notifyUser($user_id, "$jenis Ditolak", "Pengajuan $jenis Anda telah ditolak. Alasan: $catatan", 'error', "/user/riwayat.php");
        
        return true;
    }
    return false;
}

/**
 * Get admin review statistics
 */
function getAdminStats() {
    global $conn;
    
    $stats = [];
    
    // Pending review count
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE status_admin = 'PENDING'");
    $stats['pending_bbm'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM servis WHERE status_admin = 'PENDING'");
    $stats['pending_servis'] = $result->fetch_assoc()['count'];
    
    // Urgent (> 5 days)
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE status_admin = 'PENDING' AND created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)");
    $stats['urgent'] = $result->fetch_assoc()['count'];
    
    // Approved this month
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE status_admin = 'APPROVED' AND MONTH(admin_review_date) = MONTH(NOW())");
    $stats['approved_month'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// ============================================
// KEUANGAN VALIDATION FUNCTIONS
// ============================================

/**
 * Get pending submissions for keuangan validation
 */
function getKeuanganPendingSubmissions($type = 'all', $limit = 50) {
    global $conn;
    
    if ($type === 'bbm' || $type === 'all') {
        $bbm = $conn->query("
            SELECT 'BBM' as tipe, b.*, u.nama as nama_user, k.no_polisi, admin.nama as nama_admin
            FROM bbm b
            JOIN users u ON b.id_user = u.id
            JOIN kendaraan k ON b.id_kendaraan = k.id
            LEFT JOIN users admin ON b.admin_id = admin.id
            WHERE b.status_admin = 'APPROVED' AND b.status_keuangan = 'PENDING'
            ORDER BY b.admin_review_date ASC
            LIMIT $limit
        ");
        $bbm_data = $bbm->fetch_all(MYSQLI_ASSOC);
    } else {
        $bbm_data = [];
    }
    
    if ($type === 'servis' || $type === 'all') {
        $servis = $conn->query("
            SELECT 'Servis' as tipe, s.*, u.nama as nama_user, k.no_polisi, admin.nama as nama_admin
            FROM servis s
            JOIN users u ON s.id_user = u.id
            JOIN kendaraan k ON s.id_kendaraan = k.id
            LEFT JOIN users admin ON s.admin_id = admin.id
            WHERE s.status_admin = 'APPROVED' AND s.status_keuangan = 'PENDING'
            ORDER BY s.admin_review_date ASC
            LIMIT $limit
        ");
        $servis_data = $servis->fetch_all(MYSQLI_ASSOC);
    } else {
        $servis_data = [];
    }
    
    if ($type === 'all') {
        return array_merge($bbm_data, $servis_data);
    } elseif ($type === 'bbm') {
        return $bbm_data;
    } else {
        return $servis_data;
    }
}

/**
 * Keuangan validate submission
 */
function keuanganValidateSubmission($jenis, $id_transaksi, $keuangan_id) {
    global $conn;
    $jenis = strtoupper($jenis);
    $id_transaksi = intval($id_transaksi);
    $keuangan_id = intval($keuangan_id);
    
    $table = ($jenis === 'BBM') ? 'bbm' : 'servis';
    
    $stmt = $conn->prepare("
        UPDATE $table 
        SET status_keuangan = 'VALIDATED', keuangan_id = ?, keuangan_review_date = NOW()
        WHERE id = ?
    ");
    
    $stmt->bind_param("ii", $keuangan_id, $id_transaksi);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        logApprovalAction($jenis, $id_transaksi, 'Keuangan', 'Validated', $keuangan_id, 'Keuangan memvalidasi pengajuan');
        
        // Get user ID for notification
        $user_result = $conn->query("SELECT id_user FROM $table WHERE id = $id_transaksi");
        $user_data = $user_result->fetch_assoc();
        $user_id = $user_data['id_user'];
        
        notifyUser($user_id, "$jenis Selesai", "Pengajuan $jenis Anda telah divalidasi dan disetujui.", 'success', "/user/riwayat.php");
        
        // Jika ada tanda terima terkait transaksi ini, set statusnya menjadi 'Disetujui'
        try {
            $stmt2 = $conn->prepare("UPDATE tanda_terima SET status = ? WHERE jenis = ? AND id_transaksi = ?");
            $newStatus = 'Disetujui';
            $jenisParam = $jenis;
            $stmt2->bind_param('ssi', $newStatus, $jenisParam, $id_transaksi);
            $stmt2->execute();
            $stmt2->close();
        } catch (Exception $e) {
            // Non-blocking: log or ignore
        }

        return true;
    }
    return false;
}

/**
 * Keuangan reject submission (send back to admin)
 */
function keuanganRejectSubmission($jenis, $id_transaksi, $keuangan_id, $catatan) {
    global $conn;
    $jenis = strtoupper($jenis);
    $id_transaksi = intval($id_transaksi);
    $keuangan_id = intval($keuangan_id);
    $catatan = $conn->real_escape_string($catatan);
    
    $table = ($jenis === 'BBM') ? 'bbm' : 'servis';
    
    $stmt = $conn->prepare("
        UPDATE $table 
        SET status_keuangan = 'REJECTED', catatan_keuangan = ?, keuangan_id = ?, keuangan_review_date = NOW()
        WHERE id = ?
    ");
    
    $stmt->bind_param("sii", $catatan, $keuangan_id, $id_transaksi);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        logApprovalAction($jenis, $id_transaksi, 'Keuangan', 'Rejected', $keuangan_id, $catatan);
        
        // Notify admin to revise
        $admin_result = $conn->query("SELECT admin_id FROM $table WHERE id = $id_transaksi");
        $admin_data = $admin_result->fetch_assoc();
        $admin_id = $admin_data['admin_id'];
        
        if ($admin_id) {
            notifyUser($admin_id, "$jenis Ditolak Keuangan", "Pengajuan $jenis yang Anda setujui ditolak keuangan. Alasan: $catatan", 'error', "/admin/approval_history.php");
        }

        // Jika ada tanda terima terkait transaksi ini, set statusnya menjadi 'Ditolak'
        try {
            $stmt2 = $conn->prepare("UPDATE tanda_terima SET status = ? WHERE jenis = ? AND id_transaksi = ?");
            $newStatus = 'Ditolak';
            $jenisParam = $jenis;
            $stmt2->bind_param('ssi', $newStatus, $jenisParam, $id_transaksi);
            $stmt2->execute();
            $stmt2->close();
        } catch (Exception $e) {
            // Non-blocking
        }
        
        return true;
    }
    return false;
}

/**
 * Get keuangan validation statistics
 */
function getKeuanganStats() {
    global $conn;
    
    $stats = [];
    
    // Pending validation
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE status_admin = 'APPROVED' AND status_keuangan = 'PENDING'");
    $stats['pending'] = $result->fetch_assoc()['count'];
    
    // Validated this month
    $result = $conn->query("SELECT COUNT(*) as count FROM bbm WHERE status_keuangan = 'VALIDATED' AND MONTH(keuangan_review_date) = MONTH(NOW())");
    $stats['validated_month'] = $result->fetch_assoc()['count'];
    
    // Total pending value
    $result = $conn->query("SELECT SUM(biaya) as total FROM bbm WHERE status_admin = 'APPROVED' AND status_keuangan = 'PENDING'");
    $row = $result->fetch_assoc();
    $stats['pending_value'] = $row['total'] ?? 0;
    
    return $stats;
}

// ============================================
// LOGGING & NOTIFICATIONS
// ============================================

/**
 * Log approval action to audit trail
 */
function logApprovalAction($jenis, $id_transaksi, $stage, $action, $user_id, $catatan = '') {
    global $conn;
    
    $jenis = strtoupper($jenis);
    $stage = strtoupper($stage);
    $id_transaksi = intval($id_transaksi);
    $user_id = intval($user_id);
    $catatan = $conn->real_escape_string($catatan);
    
    $stmt = $conn->prepare("
        INSERT INTO approval_logs (jenis, id_transaksi, stage, action, user_id, catatan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    // types: jenis (s), id_transaksi (i), stage (s), action (s), user_id (i), catatan (s)
    $stmt->bind_param("sissis", $jenis, $id_transaksi, $stage, $action, $user_id, $catatan);
    $stmt->execute();
    $stmt->close();
}

/**
 * Send notification to user
 */
function notifyUser($user_id, $title, $message, $type = 'info', $link = '') {
    global $conn;
    $user_id = intval($user_id);
    $title = $conn->real_escape_string($title);
    $message = $conn->real_escape_string($message);
    $type = strtoupper($type);
    
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message, type, link)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("issss", $user_id, $title, $message, $type, $link);
    $stmt->execute();
    $stmt->close();
    
    // TODO: Send email notification
}

/**
 * Send notification to all admins
 */
function notifyAdmins($title, $message, $type = 'info', $link = '') {
    global $conn;
    
    $admins = $conn->query("SELECT id FROM users WHERE role = 'Admin'");
    while ($admin = $admins->fetch_assoc()) {
        notifyUser($admin['id'], $title, $message, $type, $link);
    }
}

/**
 * Send notification to all keuangan staff
 */
function notifyKeuangans($title, $message, $type = 'info', $link = '') {
    global $conn;
    
    $keuangans = $conn->query("SELECT id FROM users WHERE role = 'Keuangan'");
    while ($keuangan = $keuangans->fetch_assoc()) {
        notifyUser($keuangan['id'], $title, $message, $type, $link);
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get user name by ID
 */
function getUserName($user_id) {
    global $conn;
    $user_id = intval($user_id);
    $result = $conn->query("SELECT nama FROM users WHERE id = $user_id");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['nama'];
    }
    return 'Unknown User';
}

/**
 * Get kendaraan info
 */
function getKendaraanInfo($id_kendaraan) {
    global $conn;
    $id_kendaraan = intval($id_kendaraan);
    $result = $conn->query("SELECT * FROM kendaraan WHERE id = $id_kendaraan");
    return $result ? $result->fetch_assoc() : null;
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format date in Indonesian
 */
function formatDateID($date) {
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($date);
    $month = $months[date('n', $timestamp) - 1];
    return date('j', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp);
}

/**
 * Calculate days pending
 */
function daysPending($created_at) {
    $from = new DateTime($created_at);
    $to = new DateTime('now');
    return $from->diff($to)->days;
}



?>
