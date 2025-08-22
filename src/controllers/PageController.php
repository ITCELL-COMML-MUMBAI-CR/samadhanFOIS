<?php
require_once 'BaseController.php';

class PageController extends BaseController {
    public function home() {
        // Load models and get data for customer home page (same as customerHome)
        $newsModel = $this->loadModel('News');
        $quickLinkModel = $this->loadModel('QuickLink');
        
        $data = [
            'pageTitle' => 'Home - SAMPARK',
            'marqueeItems' => $newsModel->getMarquee(),
            'featuredNews' => $newsModel->getFeatured(6),
            'newsItems' => $newsModel->findByType('news', 4),
            'announcements' => $newsModel->findByType('announcement', 4),
            'advertisements' => $newsModel->findByType('advertisement', 3),
            'quickLinks' => $quickLinkModel->getActive()
        ];

        $this->loadView('header', $data);
        $this->loadView('pages/customer_home', $data);
        $this->loadView('footer');
    }

    public function customerHome() {
        SessionManager::requireLogin();
        
        // Load models and get data for customer home page
        $newsModel = $this->loadModel('News');
        $quickLinkModel = $this->loadModel('QuickLink');
        
        $data = [
            'pageTitle' => 'Home - SAMPARK',
            'marqueeItems' => $newsModel->getMarquee(),
            'featuredNews' => $newsModel->getFeatured(6),
            'newsItems' => $newsModel->findByType('news', 4),
            'announcements' => $newsModel->findByType('announcement', 4),
            'advertisements' => $newsModel->findByType('advertisement', 3),
            'quickLinks' => $quickLinkModel->getActive()
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

    public function help() {
        $this->loadView('header', ['pageTitle' => 'Help & User Manual']);
        $this->loadView('pages/help');
        $this->loadView('footer');
    }

    public function helpStandalone() {
        require_once __DIR__ . '/../../public/pages/help_standalone.php';
    }

    public function profile() {
        SessionManager::requireLogin();
        
        $currentUser = SessionManager::getCurrentUser();
        $customerModel = $this->loadModel('Customer');
        
        // Get customer data
        $customerDetails = null;
        if (!empty($currentUser['customer_id'])) {
            $customerDetails = $customerModel->findById($currentUser['customer_id']);
        }
        
        $data = [
            'pageTitle' => 'Customer Profile',
            'customerDetails' => $customerDetails
        ];
        
        $this->loadView('header', $data);
        $this->loadView('pages/profile', $data);
        $this->loadView('footer');
    }

    public function staffProfile() {
        SessionManager::requireLogin();
        
        $currentUser = SessionManager::getCurrentUser();
        $userModel = $this->loadModel('User');
        
        // Get complete user data from users table
        $userDetails = $userModel->findByLoginId($currentUser['login_id']);
        
        $data = [
            'pageTitle' => 'Staff Profile',
            'userDetails' => $userDetails
        ];
        
        $this->loadView('header', $data);
        $this->loadView('pages/staff_profile', $data);
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
