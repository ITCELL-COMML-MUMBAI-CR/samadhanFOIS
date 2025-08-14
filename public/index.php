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
        } elseif ($action === 'my') {
            $controller->my();
        } elseif ($action === 'tome') {
            $controller->assignedToMe();
        } elseif ($action === 'approvals') {
            $controller->approvals();
        } elseif ($action === 'view' && !empty($params[0])) {
            $controller->view($params[0]);
        } else {
            $controller->index();
        }
        break;
    case 'complaints':
        // Alias routes to grievances for backward compatibility
        $controller = new ComplaintController();
        if ($action === 'new') {
            $controller->create();
        } elseif ($action === 'my') {
            $controller->my();
        } elseif ($action === 'tome') {
            $controller->assignedToMe();
        } elseif ($action === 'view' && !empty($params[0])) {
            $controller->view($params[0]);
        } else {
            $controller->index();
        }
        break;
        
    case 'admin':
        $controller = new AdminController();
        if ($action === 'categories') {
            $controller->categories();
        } elseif ($action === 'logs') {
            $controller->logs();
        } elseif ($action === 'users') {
            $controller->users();
        } elseif ($action === 'reports') {
            $controller->reports();
        } else {
            $controller->categories();
        }
        break;
        
    case 'policy':
        $controller = new PageController();
        $controller->policy();
        break;
    case 'about':
        $controller = new PageController();
        $controller->about();
        break;
    case 'contact':
        $controller = new PageController();
        $controller->contact();
        break;
    case 'help':
        $controller = new PageController();
        $controller->help();
        break;
    case 'faq':
        $controller = new PageController();
        $controller->faq();
        break;
    case 'guidelines':
        $controller = new PageController();
        $controller->guidelines();
        break;
    case 'profile':
        $controller = new PageController();
        $controller->profile();
        break;
    case 'settings':
        $controller = new PageController();
        $controller->settings();
        break;
    case 'track':
        $controller = new PageController();
        $controller->track();
        break;
    case 'reports':
        $controller = new PageController();
        $controller->reports();
        break;
        
    case 'register':
        $controller = new PageController();
        $controller->register();
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
