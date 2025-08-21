<?php
/**
 * Database Initialization Script
 * Complaint Handling System for Central Railway Freight Customers
 */

require_once 'config/config.php';

class DatabaseInitializer {
    private $db;
    private $connection;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    /**
     * Initialize the database by creating tables and setting up initial data
     */
    public function initialize() {
        try {
            $this->connection->beginTransaction();
            
            echo "Starting database initialization...\n";
            
            // Read and execute the SQL file
            $sqlFile = 'u473452443_sampark.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                
                // Split SQL into individual statements
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && !preg_match('/^(--|\/\*|\*\/|SET|START|\/\*!)/', $stmt);
                    }
                );
                
                foreach ($statements as $statement) {
                    if (!empty(trim($statement))) {
                        $this->connection->exec($statement);
                    }
                }
                
                echo "Database tables created successfully.\n";
            } else {
                throw new Exception("SQL file not found: " . $sqlFile);
            }
            
            // Create default admin user
            $this->createDefaultUsers();
            
            // Insert sample complaint types and categories
            $this->insertMasterData();
            
            // Initialize system settings
            $this->initializeSystemSettings();
            
            $this->connection->commit();
            echo "Database initialization completed successfully!\n";
            
        } catch (Exception $e) {
            $this->connection->rollBack();
            echo "Error during database initialization: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Create default system users
     */
    private function createDefaultUsers() {
        try {
            // Create admin user
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT IGNORE INTO users 
                (login_id, password, role, department, name, email, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'admin',
                $adminPassword,
                'admin',
                'SYSTEM',
                'System Administrator',
                'admin@railway.gov.in',
                'active'
            ]);
            
            // Create sample commercial controller
            $commercialPassword = password_hash('commercial123', PASSWORD_DEFAULT);
            $stmt->execute([
                'commercial_controller',
                $commercialPassword,
                'controller',
                'COMMERCIAL',
                'Commercial Controller',
                'commercial@railway.gov.in',
                'active'
            ]);
            
            // Create sample viewer
            $viewerPassword = password_hash('viewer123', PASSWORD_DEFAULT);
            $stmt->execute([
                'viewer_user',
                $viewerPassword,
                'viewer',
                'COMMERCIAL',
                'System Viewer',
                'viewer@railway.gov.in',
                'active'
            ]);
            
            echo "Default users created successfully.\n";
            
        } catch (PDOException $e) {
            echo "Warning: Could not create default users: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Insert master data for complaint types and categories
     */
    private function insertMasterData() {
        try {
            // This could be expanded to include predefined complaint types,
            // categories, and other master data if needed
            echo "Master data setup completed.\n";
            
        } catch (PDOException $e) {
            echo "Warning: Could not insert master data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Initialize system settings
     */
    private function initializeSystemSettings() {
        try {
            require_once 'src/models/SystemSettings.php';
            $settingsModel = new SystemSettings();
            $settingsModel->initializeDefaultSettings();
            echo "System settings initialized successfully.\n";
            
        } catch (Exception $e) {
            echo "Warning: Could not initialize system settings: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Check if tables exist and are properly set up
     */
    public function verifySetup() {
        $tables = [
            'customers', 'users', 'complaints', 'transactions', 'evidence',
            'complaint_categories', 'email_templates', 'news', 'quick_links',
            'system_settings', 'shed', 'wagon_details', 'departments'
        ];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $stmt = $this->connection->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            
            if ($stmt->rowCount() == 0) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "All required tables are present.\n";
            return true;
        } else {
            echo "Missing tables: " . implode(', ', $missingTables) . "\n";
            return false;
        }
    }
    
    /**
     * Get database statistics
     */
    public function getStatistics() {
        $stats = [];
        $tables = [
            'customers', 'users', 'complaints', 'transactions', 'evidence',
            'complaint_categories', 'email_templates', 'news', 'quick_links',
            'system_settings', 'shed', 'wagon_details', 'departments'
        ];
        
        foreach ($tables as $table) {
            try {
                $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM $table");
                $stmt->execute();
                $result = $stmt->fetch();
                $stats[$table] = $result['count'];
            } catch (PDOException $e) {
                $stats[$table] = 'Error: ' . $e->getMessage();
            }
        }
        
        return $stats;
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    try {
        $initializer = new DatabaseInitializer();
        
        if (isset($argv[1]) && $argv[1] === 'verify') {
            $initializer->verifySetup();
            $stats = $initializer->getStatistics();
            echo "\nDatabase Statistics:\n";
            foreach ($stats as $table => $count) {
                echo "- $table: $count records\n";
            }
        } else {
            $initializer->initialize();
        }
        
    } catch (Exception $e) {
        echo "Fatal error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
