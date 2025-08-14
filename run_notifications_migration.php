<?php
/**
 * SAMADHAN FOIS - Notifications Table Migration Runner
 * 
 * This script creates the notifications table for the enhanced notification system.
 * Run this file from the project root directory.
 * 
 * Usage: php run_notifications_migration.php
 */

echo "🚀 SAMADHAN FOIS - Notifications Migration\n";
echo "==========================================\n\n";

// Include database configuration
if (!file_exists('config/config.php')) {
    die("❌ Error: config/config.php not found. Please run this script from the project root directory.\n");
}

require_once 'config/config.php';

try {
    // Create database connection
    echo "📡 Connecting to database...\n";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Database connection successful!\n\n";
    
    // Read the SQL migration file
    $sqlFile = 'notifications_table_migration.sql';
    if (!file_exists($sqlFile)) {
        die("❌ Error: Migration file '$sqlFile' not found.\n");
    }
    
    echo "📄 Reading migration file...\n";
    $sql = file_get_contents($sqlFile);
    
    // Execute the migration
    echo "⚡ Executing migration...\n";
    $pdo->exec($sql);
    
    // Verify the table was created
    echo "🔍 Verifying table creation...\n";
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'notifications'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        // Get table info
        $stmt = $pdo->prepare("DESCRIBE notifications");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "✅ Notifications table created successfully!\n";
        echo "📊 Table structure:\n";
        echo "   - Columns: " . count($columns) . "\n";
        echo "   - Primary Key: id (auto-increment)\n";
        echo "   - Unique Key: notification_id\n";
        echo "   - Foreign Key: user_id -> users(login_id)\n\n";
        
        // Check if sample data was inserted
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications");
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            echo "📝 Sample notifications inserted: $count\n";
        } else {
            echo "ℹ️  No sample data inserted (admin user not found)\n";
        }
        
        echo "\n🎉 Migration completed successfully!\n";
        echo "🔔 The notification system is now ready to use.\n\n";
        
        echo "🚀 Next steps:\n";
        echo "   1. Test the notification system by logging in\n";
        echo "   2. Visit /notifications to view the notification page\n";
        echo "   3. Admins can visit /admin/notifications to send notifications\n";
        echo "   4. The notification bell in the navbar will show live counts\n\n";
        
    } else {
        echo "❌ Error: Table creation verification failed.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\n💡 Common solutions:\n";
    echo "   - Check your database credentials in config/config.php\n";
    echo "   - Ensure MySQL/MariaDB server is running\n";
    echo "   - Verify the database exists\n";
    echo "   - Check user permissions for table creation\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Migration script completed.\n";
?>
