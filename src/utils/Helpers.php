<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Get current date in Y-m-d format
 */
function getCurrentDate() {
    return date('Y-m-d');
}

/**
 * Get current time in H:i:s format
 */
function getCurrentTime() {
    return date('H:i:s');
}

/**
 * Get current date and time in Y-m-d H:i:s format
 */
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate mobile number (Indian format)
 */
function validateMobile($mobile) {
    return preg_match('/^[6-9]\d{9}$/', $mobile);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Generate complaint ID
 */
function generateComplaintId() {
    $prefix = 'CMP';
    $date = date('Ymd');
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $random;
}

/**
 * Generate transaction ID
 */
function generateTransactionId() {
    $prefix = 'TXN';
    $date = date('Ymd');
    $time = date('His');
    $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . $date . $time . $random;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format date and time for display
 */
function formatDateTime($datetime, $format = 'd M Y H:i') {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get time ago string
 */
function timeAgo($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Get priority color class
 */
function getPriorityColor($priority) {
    switch (strtolower($priority)) {
        case 'critical':
            return 'danger';
        case 'high':
            return 'warning';
        case 'medium':
            return 'info';
        case 'low':
            return 'success';
        default:
            return 'secondary';
    }
}

/**
 * Get status color class
 */
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'warning';
        case 'replied':
            return 'info';
        case 'closed':
            return 'success';
        case 'reverted':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Get rating color class
 */
function getRatingColor($rating) {
    switch (strtolower($rating)) {
        case 'excellent':
            return 'success';
        case 'satisfactory':
            return 'info';
        case 'unsatisfactory':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    $errors = [];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $errors[] = 'Invalid file parameter';
        return $errors;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File size exceeds limit';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'File was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'No file was uploaded';
                break;
            default:
                $errors[] = 'Unknown upload error';
        }
        return $errors;
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum allowed size';
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
    }
    
    return $errors;
}

/**
 * Upload file
 */
function uploadFile($file, $destination, $filename = null) {
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    if ($filename === null) {
        $filename = uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    }
    
    $filepath = $destination . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get file size in human readable format
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Check if string contains only numbers
 */
function isNumeric($string) {
    return ctype_digit($string);
}

/**
 * Check if string contains only letters
 */
function isAlpha($string) {
    return ctype_alpha($string);
}

/**
 * Check if string contains only letters and numbers
 */
function isAlphanumeric($string) {
    return ctype_alnum($string);
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Convert bytes to human readable format
 */
function bytesToSize($bytes) {
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if ($bytes == 0) return '0 Bytes';
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Get client IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
    header('Location: ' . $url);
    exit;
}

/**
 * Get alert message and clear it
 */
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

/**
 * Set alert message
 */
function setAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token field for forms
 */
function getCSRFTokenField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Get current page identifier for navbar highlighting
 * @return string
 */
function getCurrentPage() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = trim($uri, '/');
    
    // Remove base path if exists
    $basePath = trim(BASE_URL, '/');
    if (!empty($basePath) && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
        $uri = trim($uri, '/');
    }
    
    // Default route
    if (empty($uri)) {
        return 'home';
    }
    
    // Extract controller and action
    $segments = explode('/', $uri);
    $controllerName = $segments[0] ?? 'home';
    $action = $segments[1] ?? 'index';
    
    // Handle API requests
    if ($controllerName === 'api') {
        return 'api';
    }
    
    // Map routes to page identifiers
    $pageMap = [
        'home' => 'home',
        'customer-home' => 'customer-home',
        'dashboard' => 'dashboard',
        'login' => 'login',
        'register' => 'register',
        'about' => 'about',
        'contact' => 'contact',
        'help' => 'help',
        'profile' => 'profile',
        'support' => 'support',
        'grievances' => 'grievances',
        'complaints' => 'complaints',
        'reports' => 'reports',
        'admin' => 'admin',
        'customer' => 'customer'
    ];
    
    // Check for specific admin pages
    if ($controllerName === 'admin') {
        $adminPages = [
            'users' => 'admin-users',
            'categories' => 'admin-categories',
            'news' => 'admin-news',
            'quicklinks' => 'admin-quicklinks',
            'reports' => 'admin-reports',
            'bulk-email' => 'admin-bulk-email',
            'email-templates' => 'admin-email-templates',
            'logs' => 'admin-logs'
        ];
        
        if (isset($adminPages[$action])) {
            return $adminPages[$action];
        }
        return 'admin';
    }
    
    // Check for specific customer pages
    if ($controllerName === 'customer') {
        if ($action === 'add') {
            return 'customer-add';
        }
        return 'customer';
    }
    
    // Check for specific support/grievance pages
    if ($controllerName === 'support') {
        if ($action === 'assistance') {
            return 'support-assistance';
        } elseif ($action === 'new') {
            return 'support-new';
        }
        return 'support';
    }
    
    if ($controllerName === 'grievances') {
        if ($action === 'hub') {
            return 'grievances-hub';
        } elseif ($action === 'tome') {
            return 'grievances-tome';
        } elseif ($action === 'approvals') {
            return 'grievances-approvals';
        }
        return 'grievances';
    }
    
    // Return mapped page or controller name
    return $pageMap[$controllerName] ?? $controllerName;
}

/**
 * Check if current page matches the given page identifier
 * @param string $pageIdentifier
 * @return bool
 */
function isCurrentPage($pageIdentifier) {
    return getCurrentPage() === $pageIdentifier;
}
?>
