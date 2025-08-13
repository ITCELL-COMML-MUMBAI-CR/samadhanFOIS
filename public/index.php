<?php
/**
 * Front Controller for SAMPARK - Railway Grievance System
 */

// Include configuration and session management
require_once '../config/config.php';
require_once '../src/utils/SessionManager.php';
require_once '../src/controllers/BaseController.php';
require_once '../src/controllers/PageController.php';
require_once '../src/controllers/LoginController.php';
require_once '../src/controllers/DashboardController.php';
require_once '../src/controllers/ComplaintController.php';
require_once '../src/controllers/AdminController.php';
require_once '../src/controllers/CustomerController.php';


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
$controllerName = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$params = array_slice($segments, 2);

// Handle API requests first (before including header)
if ($controllerName === 'api') {
    // Handle API requests
    include '../src/api/index.php';
    exit;
}

// Basic routing logic
switch ($controllerName) {
    case 'home':
    case '':
        $controller = new PageController();
        $controller->home();
        break;
        
    case 'login':
        $controller = new LoginController();
        $controller->handleLoginRequest();
        
        // Load the login page using PageController
        $pageController = new PageController();
        $pageController->login();
        break;
        
    case 'customer':
        $controller = new CustomerController();
        if ($action === 'add') {
            $controller->add();
        }
        break;

    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case 'grievances':
        $controller = new ComplaintController();
        if ($action === 'new') {
            $controller->create();
        }
        // ... other complaint actions
        break;
        
    case 'admin':
        $controller = new AdminController();
        if ($action === 'categories') {
            $controller->categories();
        } elseif ($action === 'logs') {
            $controller->logs();
        }
        break;
        
    case 'policy':
        $controller = new PageController();
        $controller->policy();
        break;
        
    case 'logout':
        SessionManager::logout();
        header('Location: ' . BASE_URL . 'login');
        exit;
        
    default:
        $controller = new PageController();
        $controller->notFound();
        break;
}
