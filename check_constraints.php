<?php
/**
 * Foreign Key Constraint Checker
 * Identifies foreign key constraints on complaint_rejections table
 */

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Checking foreign key constraints on complaint_rejections table...\n\n";
    
    // Get table creation SQL to see constraints
    $stmt = $pdo->query("SHOW CREATE TABLE complaint_rejections");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "Table structure:\n";
        echo $result['Create Table'] . "\n\n";
    }
    
    // Check for foreign key constraints
    $stmt = $pdo->query("SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'complaint_rejections' 
    AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "✅ No foreign key constraints found on complaint_rejections table\n";
    } else {
        echo "⚠️  Foreign key constraints found:\n";
        foreach ($constraints as $constraint) {
            echo "- {$constraint['CONSTRAINT_NAME']}: {$constraint['COLUMN_NAME']} -> {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}\n";
        }
        echo "\nThese constraints may need to be dropped before running the migration.\n";
    }
    
    // Check current columns
    echo "\nCurrent columns in complaint_rejections table:\n";
    $stmt = $pdo->query("DESCRIBE complaint_rejections");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
