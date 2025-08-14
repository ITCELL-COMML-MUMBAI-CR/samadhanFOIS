<?php
/**
 * Run Database Migration for Notifications Table
 */

require_once '../../config/config.php';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the SQL migration
    $sql = file_get_contents('create_notifications_table.sql');
    $pdo->exec($sql);
    
    echo "✅ Notifications table created successfully!" . PHP_EOL;
    echo "🔔 The notification system is now ready to use." . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}
?>
