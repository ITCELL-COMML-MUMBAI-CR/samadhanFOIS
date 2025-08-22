<?php
/**
 * Auto-Close Complaints Cron Job
 * This script should be run daily via cron job to auto-close complaints
 * that have been in 'replied' status for more than 3 days without feedback
 * 
 * Usage: php cron_auto_close.php
 * Cron: 0 2 * * * /usr/bin/php /path/to/cron_auto_close.php
 */

// Include configuration
require_once 'config/config.php';
require_once 'src/models/Complaint.php';
require_once 'src/models/Transaction.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/cron_auto_close.log');

// Start logging
$logFile = 'logs/cron_auto_close.log';
$timestamp = date('Y-m-d H:i:s');

function writeLog($message) {
    global $logFile, $timestamp;
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

try {
    writeLog("Starting auto-close complaints cron job");
    
    // Initialize models
    $complaintModel = new Complaint();
    
    // Run auto-close function
    $closedCount = $complaintModel->autoCloseOldComplaints();
    
    if ($closedCount === false) {
        writeLog("ERROR: Failed to auto-close complaints");
        exit(1);
    }
    
    writeLog("SUCCESS: Auto-closed $closedCount complaints");
    
    // Log summary
    if ($closedCount > 0) {
        writeLog("INFO: $closedCount complaints were auto-closed due to no feedback after 3 days");
    } else {
        writeLog("INFO: No complaints needed to be auto-closed");
    }
    
    writeLog("Auto-close complaints cron job completed successfully");
    
} catch (Exception $e) {
    writeLog("ERROR: Exception occurred: " . $e->getMessage());
    writeLog("ERROR: Stack trace: " . $e->getTraceAsString());
    exit(1);
}

writeLog("Cron job finished");
?>
