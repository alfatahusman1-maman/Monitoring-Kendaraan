<?php
require 'config.php';
$res = mysqli_query($conn, "SELECT username, role FROM users");
while($row = mysqli_fetch_assoc($res)) {
    echo "User: " . $row['username'] . " | Role: " . $row['role'] . "\n";
}
?>
