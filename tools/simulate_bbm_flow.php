<?php
require __DIR__ . '/..//config.php';
require __DIR__ . '/..//helpers/approval_workflow.php';

echo "Simulate BBM end-to-end flow\n";

// Pick a test user
$userRes = $conn->query("SELECT id FROM users WHERE role = 'User' LIMIT 1");
if (!$userRes || $userRes->num_rows == 0) {
    echo "No User found in users table. Aborting.\n";
    exit;
}
$user = $userRes->fetch_assoc();
$id_user = $user['id'];

// Pick a kendaraan
$kRes = $conn->query("SELECT id FROM kendaraan LIMIT 1");
if (!$kRes || $kRes->num_rows == 0) {
    echo "No kendaraan found. Aborting.\n";
    exit;
}
$k = $kRes->fetch_assoc();
$id_kend = $k['id'];

// Submit BBM
$jenis = 'Pertalite';
$liter = 3.5;
$biaya = 50000;

$bbm_id = createBBMSubmission($id_user, $id_kend, date('Y-m-d'), $jenis, $liter, $biaya, null);
if (!$bbm_id) {
    echo "Failed to create BBM submission.\n";
    exit;
}
echo "Created BBM id: $bbm_id\n";

// Find an admin user
$admRes = $conn->query("SELECT id FROM users WHERE role = 'Admin' LIMIT 1");
if (!$admRes || $admRes->num_rows == 0) {
    echo "No Admin found. Skipping admin approve.\n";
    exit;
}
$adm = $admRes->fetch_assoc();
$admin_id = $adm['id'];

$ok = adminApproveSubmission('BBM', $bbm_id, $admin_id);
if ($ok) echo "Admin approved BBM $bbm_id\n";
else { echo "Admin approve failed\n"; exit; }

// Check Keuangan pending
$pending = getKeuanganPendingSubmissions('bbm', 10);
$found = false;
foreach ($pending as $p) {
    if ($p['id'] == $bbm_id) { $found = true; break; }
}
if ($found) echo "BBM appears in Keuangan queue.\n";
else echo "BBM NOT found in Keuangan queue.\n";

// Find a keuangan user
$kRes = $conn->query("SELECT id FROM users WHERE role = 'Keuangan' LIMIT 1");
if ($kRes && $kRes->num_rows > 0) {
    $krow = $kRes->fetch_assoc();
    $keuangan_id = $krow['id'];
    $ok2 = keuanganValidateSubmission('BBM', $bbm_id, $keuangan_id);
    if ($ok2) echo "Keuangan validated BBM $bbm_id\n";
    else echo "Keuangan validate failed\n";
} else {
    echo "No Keuangan user found; cannot auto-validate.\n";
}

// Show final status
$r = $conn->query("SELECT id, status_admin, status_keuangan FROM bbm WHERE id = $bbm_id");
if ($r && $r->num_rows > 0) {
    $row = $r->fetch_assoc();
    echo "Final status: admin={$row['status_admin']}, keuangan={$row['status_keuangan']}\n";
}

echo "Done.\n";
?>