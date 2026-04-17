<?php
// PHP Runner to apply April 2026 dummy data
require_once __DIR__ . '/../config.php';

$sqlFile = __DIR__ . '/populate_april_data.sql';

if (!file_exists($sqlFile)) {
    die("❌ Error: File SQL tidak ditemukan!\n");
}

$sql = file_get_contents($sqlFile);

// Execute multi-query
if (mysqli_multi_query($conn, $sql)) {
    do {
        // Store first result set
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));
    echo "✅ Berhasil memasukkan data dummy April 2026 ke database!\n";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "\n";
}
