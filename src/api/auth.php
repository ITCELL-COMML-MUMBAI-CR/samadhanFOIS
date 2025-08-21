<?php
/**
 * Authentication API Endpoints
 * Handles login, logout, and session management
 */

require_once dirname(__DIR__) . '/utils/SessionManager.php';
require_once dirname(__DIR__) . '/models/User.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'login':
                handleLogin();
                break;
                
            case 'logout':
                handleLogout();
                break;
                
            case 'check_session':
                checkSession();
                break;
                
            default:
                sendError('Invalid action', 400);
                break;
        }
        break;
        
    case 'GET':
        checkSession();
        break;
        
    default:
        sendError('Method not allowed', 405);
        break;
}

function handleLogin() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        sendError('Username and password are required', 400);
    }
    
    try {
        $userModel = new User();
        $user = $userModel->authenticate($username, $password);
        
        if ($user) {
            SessionManager::login($user);
            sendSuccess([
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'department' => $user['department'] ?? null,
                    'name' => $user['name'] ?? ''
                ]
            ], 'Login successful');
        } else {
            sendError('Invalid username or password', 401);
        }
    } catch (Exception $e) {
        sendError('Login failed: ' . $e->getMessage(), 500);
    }
}

function handleLogout() {
    SessionManager::logout();
    sendSuccess([], 'Logout successful');
}

function checkSession() {
    if (SessionManager::isLoggedIn()) {
        $currentUser = SessionManager::getCurrentUser();
        sendSuccess([
            'user' => [
                'id' => $currentUser['id'],
                'username' => $currentUser['username'],
                'role' => $currentUser['role'],
                'department' => $currentUser['department'] ?? null,
                'name' => $currentUser['name'] ?? ''
            ]
        ], 'Session valid');
    } else {
        sendError('Not authenticated', 401);
    }
}
?>
