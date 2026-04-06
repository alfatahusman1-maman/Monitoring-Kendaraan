<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Keuangan') {
    header("Location: ../index.php");
    exit;
}
require '../config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Keuangan</title>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>

<div class="content">
    <h2>Dashboard Keuangan</h2>
    <p>Selamat datang, <?php echo $_SESSION['user']['nama']; ?>!</p>
</div>
</body>
</html>
