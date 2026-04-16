<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../helpers/approval_workflow.php';

// find a test user
$res = $conn->query("SELECT id FROM users LIMIT 1");
if (!$res || $res->num_rows == 0) {
    echo "No users found in users table.\n";
    exit(1);
}
$user = $res->fetch_assoc();
$id_user = $user['id'];

// find an active vehicle
$res2 = $conn->query("SELECT id FROM kendaraan WHERE status = 'Aktif' LIMIT 1");
if (!$res2 || $res2->num_rows == 0) {
    echo "No active kendaraan found.\n";
    exit(1);
}
$kend = $res2->fetch_assoc();
$id_kend = $kend['id'];

$test = createBBMSubmission($id_user, $id_kend, date('Y-m-d'), 'Pertalite', 5.5, 150000, null);
if ($test) {
    echo "createBBMSubmission succeeded, id: $test\n";
} else {
    echo "createBBMSubmission failed\n";
}
