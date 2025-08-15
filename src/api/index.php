<?php
/**
 * API Index - Routes API requests
 * Handles AJAX requests for the complaint system
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/config.php';

// Parse the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Remove base path and 'api' from URI
$basePath = trim(BASE_URL, '/');
if (!empty($basePath) && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    $uri = trim($uri, '/');
}

if (strpos($uri, 'api/') === 0) {
    $uri = substr($uri, 4);
}

// Extract endpoint and method
$segments = explode('/', $uri);
$endpoint = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? '';

$method = $_SERVER['REQUEST_METHOD'];

// Response helper function
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Error response helper
function sendError($message, $statusCode = 400) {
    sendResponse(['error' => true, 'message' => $message], $statusCode);
}

// Success response helper
function sendSuccess($data = [], $message = 'Success') {
    sendResponse(['error' => false, 'message' => $message, 'data' => $data]);
}

// Check if user is authenticated for protected endpoints
function requireAuth() {
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        sendError('Authentication required', 401);
    }
}

// Get JSON input
function getJsonInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

try {
    // Route API requests
    switch ($endpoint) {
        case 'auth':
            include 'auth.php';
            break;
            
        case 'complaints':
            requireAuth();
            include 'complaints.php';
            break;
            
        case 'users':
            requireAuth();
            include 'users.php';
            break;
            
        case 'transactions':
            requireAuth();
            include 'transactions.php';
            break;
            
        case 'evidence':
            requireAuth();
            include 'evidence.php';
            break;
            

            
        case 'dashboard':
            requireAuth();
            include 'dashboard.php';
            break;
            
        case 'reports':
            requireAuth();
            include 'reports.php';
            break;
            
        case 'bulk_email':
            requireAuth();
            include 'bulk_email.php';
            break;
            
        case 'email_templates':
            requireAuth();
            include 'email_templates.php';
            break;
            
        default:
            sendError('API endpoint not found', 404);
            break;
    }
    
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendError('Internal server error', 500);
}
?>
