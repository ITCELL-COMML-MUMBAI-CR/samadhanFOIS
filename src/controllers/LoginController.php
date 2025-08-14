<?php

require_once __DIR__ . '/../utils/SessionManager.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Logger.php';

class LoginController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function handleLoginRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $loginId = sanitizeInput($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginId) || empty($password)) {
            SessionManager::setAlert('Please enter both login ID and password.', 'danger');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $user = $this->userModel->authenticate($loginId, $password);

            if ($user) {
                Logger::logAuth('LOGIN', $loginId, true, ['user_role' => $user['role']]);
                SessionManager::login($user);
                header('Location: ' . BASE_URL . 'customer-home');
                exit;
            } else {
                Logger::logAuth('LOGIN', $loginId, false, ['error' => 'Invalid credentials']);
                SessionManager::setAlert('Invalid login credentials. Please try again.', 'danger');
            }
        } catch (Exception $e) {
            Logger::logAuth('LOGIN', $loginId, false, ['error' => $e->getMessage()]);
            SessionManager::setAlert('Login failed. Please try again later.', 'danger');
        }

        // Store loginId in session to repopulate the form
        $_SESSION['form_data'] = ['login_id' => $loginId];

        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}
