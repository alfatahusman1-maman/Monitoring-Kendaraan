<?php
require 'config.php';
// Use md5 or password_hash depending on what the app uses. 
// Let's check index.php first.
$login_code = file_get_contents('index.php');
if (strpos($login_code, 'md5') !== false) {
    $pass = md5('admin123');
} else {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
}

$sql = "UPDATE users SET password='$pass' WHERE username='admin'";
if(mysqli_query($conn, $sql)) {
    echo "Password admin reset to: admin123\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
