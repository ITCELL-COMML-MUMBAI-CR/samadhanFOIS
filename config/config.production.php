<?php
/**
 * Production Configuration for Hostinger
 * Copy this to config.php when deploying to production
 */

// Database Configuration (keep your existing database settings)
define('DB_HOST', '193.203.184.199');
define('DB_USERNAME', 'u473452443_ravan');
define('DB_PASSWORD', '!8St+preFZc');
define('DB_NAME', 'u473452443_sampark');

// Set timezone to India
define('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(TIMEZONE);

// Application Configuration
define('APP_NAME', 'SAMPARK - Railway Complaint System');
define('APP_VERSION', '1.0');
define('BASE_URL', '/'); // Update this to your domain path

// Environment Configuration
define('ENVIRONMENT', 'production'); // Production environment

// File Upload Configuration
define('UPLOAD_DIR', '/uploads/evidences/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('MAX_IMAGES_PER_COMPLAINT', 3);

// Security Configuration
define('HASH_ALGO', 'sha256');
define('SESSION_LIFETIME', 3600); // 1 hour

// Error Reporting Configuration (Production - Hide errors)
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', false); // Hide errors from frontend
define('ERROR_LOG_FILE', dirname(__DIR__) . '/logs/error.log');

// Configure PHP error reporting
ini_set('display_errors', DISPLAY_ERRORS ? 1 : 0);
ini_set('log_errors', LOG_ERRORS ? 1 : 0);
ini_set('error_log', ERROR_LOG_FILE);

// Set error reporting level (report all errors but don't display them)
error_reporting(E_ALL);

// Custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    // Don't log if error reporting is turned off
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    // Include Logger if not already included
    if (!class_exists('Logger')) {
        require_once dirname(__DIR__) . '/src/utils/Logger.php';
    }
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED'
    ];
    
    $errorType = $errorTypes[$severity] ?? 'UNKNOWN';
    $context = [
        'file' => $file,
        'line' => $line,
        'type' => $errorType
    ];
    
    Logger::error("PHP Error: {$message}", $context);
    
    // Don't execute PHP internal error handler
    return true;
});

// Exception handler
set_exception_handler(function($exception) {
    if (!class_exists('Logger')) {
        require_once dirname(__DIR__) . '/src/utils/Logger.php';
    }
    
    Logger::logSystemError('Uncaught Exception: ' . $exception->getMessage(), $exception);
    
    // Show generic error page to user
    if (!headers_sent()) {
        http_response_code(500);
        if (file_exists(dirname(__DIR__) . '/public/pages/error.php')) {
            include dirname(__DIR__) . '/public/pages/error.php';
        } else {
            echo '<h1>System Error</h1><p>An unexpected error occurred. Please try again later.</p>';
        }
    }
});

// Shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        if (!class_exists('Logger')) {
            require_once dirname(__DIR__) . '/src/utils/Logger.php';
        }
        
        Logger::critical("Fatal Error: {$error['message']}", [
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type']
        ]);
    }
});

// Database Connection Class
class Database {
    private $connection;
    private static $instance;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USERNAME,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // Set MySQL timezone to match PHP timezone
            $this->connection->exec("SET time_zone = '+05:30'");
            
        } catch (PDOException $e) {
            // Log the database connection error
            if (class_exists('Logger')) {
                Logger::critical("Database connection failed: " . $e->getMessage());
            } else {
                error_log("Database connection failed: " . $e->getMessage());
            }
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Utility Functions
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

function getCurrentDate() {
    return date('Y-m-d');
}

function getCurrentTime() {
    return date('H:i:s');
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateComplaintId() {
    return 'CMP' . date('Y') . date('m') . date('d') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateTransactionId() {
    return 'TXN' . date('YmdHis') . mt_rand(100, 999);
}

function generateCustomerId() {
    $dateStr = date('Ymd'); // YYYYMMDD format
    $randomNumber = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT); // Two digit random number
    return 'ED' . $dateStr . $randomNumber;
}
