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
}
