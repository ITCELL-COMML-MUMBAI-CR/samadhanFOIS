<?php
require_once 'BaseController.php';

class PageController extends BaseController {
    public function home() {
        $this->loadView('header', ['pageTitle' => 'Welcome']);
        $this->loadView('pages/home');
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
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        $this->loadView('header', ['pageTitle' => 'Login']);
        require_once __DIR__ . '/../../public/pages/login.php';
        $this->loadView('footer');
    }

    public function notFound() {
        http_response_code(404);
        $this->loadView('header', ['pageTitle' => '404 - Page Not Found']);
        require_once __DIR__ . '/../../public/pages/404.php';
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

    public function help() {
        $this->loadView('header', ['pageTitle' => 'Help']);
        $this->loadView('pages/help');
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
        $this->loadView('header', ['pageTitle' => 'Profile']);
        $this->loadView('pages/profile');
        $this->loadView('footer');
    }

    public function settings() {
        SessionManager::requireLogin();
        $this->loadView('header', ['pageTitle' => 'Settings']);
        $this->loadView('pages/settings');
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
}
