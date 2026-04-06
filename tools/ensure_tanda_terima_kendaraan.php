<?php
require_once __DIR__ . '/../config.php';

// Add id_kendaraan column to tanda_terima if not exists
try {
    $check = $conn->query("SHOW COLUMNS FROM tanda_terima LIKE 'id_kendaraan'");
    if ($check && $check->num_rows == 0) {
        $ok = $conn->query("ALTER TABLE tanda_terima ADD COLUMN id_kendaraan INT NULL AFTER id_transaksi");
        if ($ok) {
            echo "Added column id_kendaraan to tanda_terima\n";
        } else {
            echo "Failed to add column: " . $conn->error . "\n";
        }
    } else {
        echo "Column id_kendaraan already exists, no change.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
