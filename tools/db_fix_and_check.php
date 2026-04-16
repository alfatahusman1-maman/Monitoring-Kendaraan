<?php
require __DIR__ . '/..//config.php';
header_remove();
$checks = [];
$tables = ['bbm','servis'];
// Desired columns per table
$desired = [
    'bbm' => [
        'jenis_bbm' => "VARCHAR(100) DEFAULT NULL",
        'foto_struk' => "VARCHAR(255) DEFAULT NULL",
        'status_admin' => "ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING'",
        'catatan_admin' => "TEXT DEFAULT NULL",
        'admin_id' => "INT DEFAULT NULL",
        'admin_review_date' => "TIMESTAMP NULL",
        'status_keuangan' => "ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING'",
        'catatan_keuangan' => "TEXT DEFAULT NULL",
        'keuangan_id' => "INT DEFAULT NULL",
        'keuangan_review_date' => "TIMESTAMP NULL",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ],
    'servis' => [
        'foto_struk' => "VARCHAR(255) DEFAULT NULL",
        'status_admin' => "ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING'",
        'catatan_admin' => "TEXT DEFAULT NULL",
        'admin_id' => "INT DEFAULT NULL",
        'admin_review_date' => "TIMESTAMP NULL",
        'status_keuangan' => "ENUM('PENDING','VALIDATED','REJECTED') DEFAULT 'PENDING'",
        'catatan_keuangan' => "TEXT DEFAULT NULL",
        'keuangan_id' => "INT DEFAULT NULL",
        'keuangan_review_date' => "TIMESTAMP NULL",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ]
];

echo "Checking database schema...\n";
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $existing = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    if (!$res) {
        echo "  Error: could not read table $table - " . $conn->error . "\n";
        continue;
    }
    while ($row = $res->fetch_assoc()) {
        $existing[$row['Field']] = $row;
    }
    foreach ($desired[$table] as $col => $def) {
        if (!isset($existing[$col])) {
            echo "  -> Column missing: $col  (will add)\n";
            $query = "ALTER TABLE `$table` ADD COLUMN `$col` $def";
            if ($conn->query($query)) {
                echo "     + Added $col\n";
            } else {
                echo "     - Failed to add $col: " . $conn->error . "\n";
            }
        } else {
            echo "  - Column exists: $col\n";
        }
    }
}

// Try to add foreign keys if columns exist
echo "\nEnsuring foreign keys (if applicable)...\n";
$fk_queries = [
    "ALTER TABLE `bbm` ADD CONSTRAINT `fk_bbm_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL",
    "ALTER TABLE `bbm` ADD CONSTRAINT `fk_bbm_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL",
    "ALTER TABLE `servis` ADD CONSTRAINT `fk_servis_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL",
    "ALTER TABLE `servis` ADD CONSTRAINT `fk_servis_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `users`(`id`) ON DELETE SET NULL",
];
foreach ($fk_queries as $q) {
    if ($conn->query($q)) {
        echo "  + FK added.\n";
    } else {
        echo "  - FK skipped/failed: " . $conn->error . "\n";
    }
}

echo "\nDone.\n";

?>
