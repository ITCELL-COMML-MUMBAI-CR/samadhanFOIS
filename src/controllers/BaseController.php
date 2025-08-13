<?php

class BaseController {
    protected function loadView($view, $data = []) {
        extract($data);
        
        // First try to load from src/views
        $filePath = __DIR__ . '/../views/' . $view . '.php';
        
        // If the view is a page and doesn't exist in src/views, try public/pages
        if (!file_exists($filePath) && strpos($view, 'pages/') === 0) {
            $pageView = substr($view, 6); // Remove 'pages/' prefix
            $filePath = __DIR__ . '/../../public/pages/' . $pageView . '.php';
        }
        
        if (file_exists($filePath)) {
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
}
