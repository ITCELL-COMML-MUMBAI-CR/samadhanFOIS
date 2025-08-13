<?php
/**
 * Front Controller for Samadhan FOIS - Railway Grievance System
 */

// Include configuration and session management
require_once '../config/config.php';
require_once '../src/utils/SessionManager.php';

// Start session management
SessionManager::start();

// Simple routing system
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
    $uri = 'home';
}

// Extract controller and action
$segments = explode('/', $uri);
$controller = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$params = array_slice($segments, 2);

// Define page title
$pageTitle = 'Home';

// Include header
include '../src/views/header.php';

// Basic routing logic
switch ($controller) {
    case 'home':
    case '':
        $pageTitle = 'Welcome';
        include 'pages/home.php';
        break;
        
    case 'login':
        $pageTitle = 'Login';
        include 'pages/login.php';
        break;
        
    case 'register':
        $pageTitle = 'Register';
        include 'pages/register.php';
        break;
        
    case 'dashboard':
        $pageTitle = 'Dashboard';
        SessionManager::requireLogin();
        include 'pages/dashboard.php';
        break;
        
    case 'grievances':
    case 'complaints':
        $pageTitle = 'Grievances';
        SessionManager::requireLogin();
        
        switch ($action) {
            case 'new':
                $pageTitle = 'New Grievance';
                SessionManager::requireAnyRole(['customer']);
                include 'pages/complaint_form.php';
                break;
            case 'my':
                $pageTitle = 'My Grievances';
                SessionManager::requireAnyRole(['customer']);
                include 'pages/my_complaints.php';
                break;
            case 'tome':
                $pageTitle = 'Grievances to Me';
                SessionManager::requireAnyRole(['controller']);
                include 'pages/complaints_to_me.php';
                break;
            case 'view':
                $pageTitle = 'View Grievance';
                include 'pages/complaint_view.php';
                break;
            default:
                include 'pages/complaints_list.php';
                break;
        }
        break;
        
    case 'admin':
        SessionManager::requireRole('admin');
        
        switch ($action) {
            case 'categories':
                $pageTitle = 'Manage Categories';
                include 'pages/manage_categories.php';
                break;
            case 'users':
                $pageTitle = 'Manage Users';
                include 'pages/manage_users.php';
                break;
            case 'reports':
                $pageTitle = 'Reports';
                include 'pages/reports.php';
                break;
            case 'logs':
                $pageTitle = 'System Logs';
                include 'pages/admin_logs.php';
                break;
            default:
                header('Location: ' . BASE_URL . 'dashboard');
                exit;
        }
        break;
        
    case 'policy':
        $pageTitle = 'General Policy';
        include 'pages/policy.php';
        break;
        
    case 'api':
        // Handle API requests
        include '../src/api/index.php';
        exit;
        
    case 'logout':
        SessionManager::logout();
        header('Location: ' . BASE_URL . 'login');
        exit;
        
    default:
        // 404 page
        http_response_code(404);
        $pageTitle = '404 - Page Not Found';
        include 'pages/404.php';
        break;
}

// Include footer
include '../src/views/footer.php';
?>
