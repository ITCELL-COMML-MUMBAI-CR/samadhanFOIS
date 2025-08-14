<?php
require_once 'BaseController.php';

class PageController extends BaseController {
    public function home() {
        $this->loadView('header', ['pageTitle' => 'Welcome']);
        $this->loadView('pages/home');
        $this->loadView('footer');
    }

    public function customerHome() {
        SessionManager::requireLogin();
        
        // Load models and get data for customer home page
        $newsModel = $this->loadModel('News');
        $quickLinkModel = $this->loadModel('QuickLink');
        
        $data = [
            'pageTitle' => 'Home - SAMPARK',
            'marqueeItems' => $newsModel->getMarqueeItems(),
            'featuredNews' => $newsModel->getFeaturedItems(6),
            'newsItems' => $newsModel->getNewsByType('news', 4),
            'announcements' => $newsModel->getNewsByType('announcement', 4),
            'advertisements' => $newsModel->getNewsByType('advertisement', 3),
            'quickLinks' => $quickLinkModel->getActiveLinks()
        ];

        $this->loadView('header', $data);
        $this->loadView('pages/customer_home', $data);
        $this->loadView('footer');
    }

    public function policy() {
        $this->loadView('header', ['pageTitle' => 'General Policy']);
        $this->loadView('pages/policy');
        $this->loadView('footer');
    }

    public function login() {
        // Check if user is already logged in before loading header
        if (SessionManager::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'customer-home');
            exit;
        }
        
        // Load standalone login page without header/footer
        require_once __DIR__ . '/../../public/pages/login.php';
    }

    public function notFound() {
        http_response_code(404);
        $this->loadView('header', ['pageTitle' => '404 - Page Not Found']);
        $this->loadView('pages/404');
        $this->loadView('footer');
    }

    // Simple static pages
    public function about() {
        $this->loadView('header', ['pageTitle' => 'About']);
        $this->loadView('pages/about');
        $this->loadView('footer');
    }

    public function contact() {
        $this->loadView('header', ['pageTitle' => 'Contact']);
        $this->loadView('pages/contact');
        $this->loadView('footer');
    }

    public function faq() {
        $this->loadView('header', ['pageTitle' => 'FAQ']);
        $this->loadView('pages/faq');
        $this->loadView('footer');
    }

    public function guidelines() {
        $this->loadView('header', ['pageTitle' => 'Guidelines']);
        $this->loadView('pages/guidelines');
        $this->loadView('footer');
    }

    public function profile() {
        SessionManager::requireLogin();
        
        $currentUser = SessionManager::getCurrentUser();
        $userModel = $this->loadModel('User');
        $customerModel = $this->loadModel('Customer');
        
        // Get complete user data from users table
        $userDetails = $userModel->findByLoginId($currentUser['login_id']);
        
        // Get customer data if user has customer_id
        $customerDetails = null;
        if (!empty($userDetails['customer_id'])) {
            $customerDetails = $customerModel->findById($userDetails['customer_id']);
        }
        
        $data = [
            'pageTitle' => 'Profile',
            'userDetails' => $userDetails,
            'customerDetails' => $customerDetails
        ];
        
        $this->loadView('header', $data);
        $this->loadView('pages/profile', $data);
        $this->loadView('footer');
    }

    public function track() {
        $this->loadView('header', ['pageTitle' => 'Track Status']);
        $this->loadView('pages/track');
        $this->loadView('footer');
    }

    public function reports() {
        SessionManager::requireAnyRole(['admin', 'controller']);
        $this->loadView('header', ['pageTitle' => 'Reports']);
        $this->loadView('pages/reports');
        $this->loadView('footer');
    }

    public function register() {
        SessionManager::requireAnyRole(['admin']);
        $this->loadView('header', ['pageTitle' => 'Register']);
        $this->loadView('pages/register');
        $this->loadView('footer');
    }
}
