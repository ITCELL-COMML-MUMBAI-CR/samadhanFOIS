<?php
require_once __DIR__ . '/../utils/CSRF.php';

class BaseController {
    protected function loadView($view, $data = []) {
        // Store the view name in a local variable to avoid conflicts
        $viewName = $view;
        
        // First try to load from src/views
        $filePath = __DIR__ . '/../views/' . $viewName . '.php';
        
        // If the view is a page and doesn't exist in src/views, try public/pages
        if (!file_exists($filePath) && strpos($viewName, 'pages/') === 0) {
            $pageView = substr($viewName, 6); // Remove 'pages/' prefix
            $filePath = __DIR__ . '/../../public/pages/' . $pageView . '.php';
        }
        
        if (file_exists($filePath)) {
            // Extract data into a local scope to avoid variable conflicts
            extract($data);
            require_once $filePath;
        } else {
            // Handle view not found error
            die("View not found: " . $filePath);
        }
    }

    protected function loadModel($model) {
        $filePath = __DIR__ . '/../models/' . $model . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            return new $model();
        } else {
            // Handle model not found error
            die("Model not found: " . $filePath);
        }
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function validateCsrfToken() {
        if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }
    }
}