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
require_once '../src/controllers/CustomerLoginController.php';
require_once '../src/controllers/DashboardController.php';
require_once '../src/controllers/ComplaintController.php';
require_once '../src/controllers/AdminController.php';
require_once '../src/controllers/CustomerController.php';
require_once '../src/controllers/CustomerAuthController.php';


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
        
    case 'customer-home':
        $controller = new PageController();
        $controller->customerHome();
        break;
        
    case 'login':
        $controller = new LoginController();
        $controller->handleLoginRequest();
        
        // Load the login page using PageController
        $pageController = new PageController();
        $pageController->login();
        break;
        
    case 'customer-login':
        $controller = new CustomerLoginController();
        $controller->handleLoginRequest();
        
        // Load the customer login page
        include '../public/pages/customer_login.php';
        break;
        
    case 'customer':
        $controller = new CustomerController();
        if ($action === 'add') {
            $controller->add();
        } elseif ($action === 'logout') {
            $authController = new CustomerAuthController();
            $authController->logout();
        }
        break;
        
    case 'customer-tickets':
        require_once '../src/controllers/CustomerTicketsController.php';
        $controller = new CustomerTicketsController();
        if ($action === 'feedback') {
            $controller->submitFeedback();
        } elseif ($action === 'additional-info') {
            $controller->submitAdditionalInfo();
        } elseif ($action === 'details') {
            $controller->getTicketDetails($params[0] ?? '');
        } elseif ($action === 'history') {
            $controller->getTransactionHistory($params[0] ?? '');
        } else {
            $controller->index();
        }
        break;
        
    case 'customer-debug':
        // Temporary debug page for customer authentication
        include '../public/pages/customer_debug.php';
        break;

    case 'customer-auth':
        $controller = new CustomerAuthController();
        if ($action === 'authenticate') {
            $controller->authenticate();
        } elseif ($action === 'logout') {
            $controller->logout();
        }
        break;

    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case 'grievances':
        // Redirect old grievance routes to new support system
        if ($action === 'new') {
            header('Location: ' . BASE_URL . 'support/new');
            exit;
        } elseif ($action === 'my') {
                            header('Location: ' . BASE_URL . 'customer-tickets');
            exit;
        } else {
            // For other grievance routes, use the support system
            $controller = new ComplaintController();
            if ($action === 'tome') {
                $controller->assignedToMe();
            } elseif ($action === 'hub') {
                $controller->complaintsHub();
            } elseif ($action === 'approvals') {
                $controller->approvals();
            } elseif ($action === 'view' && !empty($params[0])) {
                $controller->view($params[0]);
            } else {
                $controller->index();
            }
        }
        break;
                case 'support':
                $controller = new ComplaintController();
                if ($action === 'assistance') {
                    $controller->supportAssistance();
                } elseif ($action === 'new') {
                    $controller->newSupportTicket();
                } else {
                    $controller->supportAssistance();
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
        } elseif ($action === 'news') {
            $controller->news();
        } elseif ($action === 'quicklinks') {
            $controller->quicklinks();
        } elseif ($action === 'bulk-email') {
            $controller->bulkEmail();
        } elseif ($action === 'email-templates') {
            $controller->emailTemplates();
        } elseif ($action === 'dashboard') {
            $controller->dashboard();
        } elseif ($action === 'customers') { // THIS IS THE NEW ROUTE
            $controller->customers();
        } else {
            $controller->dashboard();
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
    case 'faq':
        $controller = new PageController();
        $controller->faq();
        break;
    case 'guidelines':
        $controller = new PageController();
        $controller->guidelines();
        break;
    case 'help':
        $controller = new PageController();
        if ($action === 'share') {
            $controller->helpStandalone();
        } else {
            $controller->help();
        }
        break;
    case 'profile':
        $controller = new PageController();
        $controller->profile();
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
        // The logout method now handles redirection based on user type
        break;
        
    default:
        $controller = new PageController();
        $controller->notFound();
        break;
}
