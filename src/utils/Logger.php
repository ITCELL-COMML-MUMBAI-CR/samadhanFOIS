<?php
/**
 * Logger Utility Class
 * Handles all application logging with different levels
 */

class Logger {
    private static $logFile;
    private static $instance;
    
    // Log levels
    const EMERGENCY = 'EMERGENCY';
    const ALERT = 'ALERT';
    const CRITICAL = 'CRITICAL';
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const NOTICE = 'NOTICE';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    
    private function __construct() {
        self::$logFile = dirname(__DIR__, 2) . '/logs/error.log';
        
        // Ensure logs directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Ensure log file exists and is writable
        if (!file_exists(self::$logFile)) {
            touch(self::$logFile);
            chmod(self::$logFile, 0644);
        }
    }
    
    /**
     * Get Logger instance (Singleton)
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }
    
    /**
     * Log a message with specified level
     */
    public static function log($level, $message, $context = []) {
        self::getInstance();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        // Write to log file
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // For critical errors, also log to PHP error log
        if (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR])) {
            error_log("[SAMPARK FOIS] {$message}");
        }
    }
    
    /**
     * Log emergency message
     */
    public static function emergency($message, $context = []) {
        self::log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * Log alert message
     */
    public static function alert($message, $context = []) {
        self::log(self::ALERT, $message, $context);
    }
    
    /**
     * Log critical message
     */
    public static function critical($message, $context = []) {
        self::log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log(self::WARNING, $message, $context);
    }
    
    /**
     * Log notice message
     */
    public static function notice($message, $context = []) {
        self::log(self::NOTICE, $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log database errors with query information
     */
    public static function logDatabaseError($error, $query = null, $params = []) {
        $context = [
            'query' => $query,
            'params' => $params,
            'file' => debug_backtrace()[1]['file'] ?? 'unknown',
            'line' => debug_backtrace()[1]['line'] ?? 'unknown'
        ];
        
        self::error("Database Error: {$error}", $context);
    }
    
    /**
     * Log authentication attempts
     */
    public static function logAuth($action, $userId = null, $success = true, $details = []) {
        $level = $success ? self::INFO : self::WARNING;
        $status = $success ? 'SUCCESS' : 'FAILED';
        $message = "Authentication {$action}: {$status}";
        
        $context = [
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        self::log($level, $message, $context);
    }
    
    /**
     * Log user actions for audit trail
     */
    public static function logUserAction($action, $userId, $details = []) {
        $context = [
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => time(),
            'details' => $details
        ];
        
        self::info("User Action: {$action}", $context);
    }
    
    /**
     * Log system errors with stack trace
     */
    public static function logSystemError($error, $exception = null) {
        $context = [
            'file' => $exception ? $exception->getFile() : (debug_backtrace()[1]['file'] ?? 'unknown'),
            'line' => $exception ? $exception->getLine() : (debug_backtrace()[1]['line'] ?? 'unknown'),
            'stack_trace' => $exception ? $exception->getTraceAsString() : null
        ];
        
        self::error("System Error: {$error}", $context);
    }
    
    /**
     * Clear old log entries (keep last 30 days)
     */
    public static function cleanup($days = 30) {
        self::getInstance();
        
        if (!file_exists(self::$logFile)) {
            return;
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES);
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        $filteredLines = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                if ($matches[1] >= $cutoffDate) {
                    $filteredLines[] = $line;
                }
            }
        }
        
        file_put_contents(self::$logFile, implode(PHP_EOL, $filteredLines) . PHP_EOL);
        
        self::info("Log cleanup completed. Kept entries from {$cutoffDate} onwards.");
    }
    
    /**
     * Get log file path
     */
    public static function getLogFile() {
        self::getInstance();
        return self::$logFile;
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecentLogs($lines = 100) {
        self::getInstance();
        
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $allLines = file(self::$logFile, FILE_IGNORE_NEW_LINES);
        return array_slice($allLines, -$lines);
    }
    
    /**
     * Check if logging is working
     */
    public static function test() {
        try {
            self::info("Logger test - system is working correctly");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
