<?php
require 'config.php';

// Check if foto column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM kendaraan LIKE 'foto'");

if (mysqli_num_rows($result) > 0) {
    echo "✅ SUCCESS! Kolom 'foto' sudah ada di tabel 'kendaraan'.\n\n";
    
    // Show table structure
    echo "Struktur Tabel Kendaraan:\n";
    echo str_repeat("=", 60) . "\n";
    
    $tableStructure = mysqli_query($conn, "DESCRIBE kendaraan");
    printf("%-15s %-20s %-15s\n", "Field", "Type", "Null");
    echo str_repeat("-", 60) . "\n";
    
    while ($row = mysqli_fetch_assoc($tableStructure)) {
        printf("%-15s %-20s %-15s\n", 
            $row['Field'], 
            $row['Type'], 
            ($row['Null'] == 'YES' ? 'Yes' : 'No')
        );
    }
    
    echo str_repeat("=", 60) . "\n\n";
    echo "✨ Sekarang Anda bisa menggunakan fitur upload foto di Data Kendaraan!\n";
} else {
    echo "❌ ERROR! Kolom 'foto' belum ada di tabel 'kendaraan'.\n";
    echo "Silakan jalankan migration terlebih dahulu.\n";
}
?>
