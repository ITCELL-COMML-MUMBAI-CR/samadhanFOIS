<?php
/**
 * Users API Endpoints
 * Handles user management operations
 */

require_once dirname(__DIR__) . '/utils/SessionManager.php';
require_once dirname(__DIR__) . '/models/User.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!empty($id)) {
            getUser($id);
        } else {
            getUsers();
        }
        break;
        
    case 'POST':
        createUser();
        break;
        
    case 'PUT':
        updateUser($id);
        break;
        
    case 'DELETE':
        deleteUser($id);
        break;
        
    default:
        sendError('Method not allowed', 405);
        break;
}

function getUsers() {
    try {
        $userModel = new User();
        $users = $userModel->getAllUsers();
        sendSuccess($users, 'Users retrieved successfully');
    } catch (Exception $e) {
        sendError('Failed to retrieve users: ' . $e->getMessage(), 500);
    }
}

function getUser($id) {
    if (empty($id)) {
        sendError('User ID is required', 400);
    }
    
    try {
        $userModel = new User();
        $user = $userModel->getUserById($id);
        
        if ($user) {
            sendSuccess($user, 'User retrieved successfully');
        } else {
            sendError('User not found', 404);
        }
    } catch (Exception $e) {
        sendError('Failed to retrieve user: ' . $e->getMessage(), 500);
    }
}

function createUser() {
    $input = getJsonInput();
    
    $requiredFields = ['username', 'password', 'role', 'name'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            sendError("Field '$field' is required", 400);
        }
    }
    
    try {
        $userModel = new User();
        $userId = $userModel->createUser($input);
        
        if ($userId) {
            sendSuccess(['id' => $userId], 'User created successfully');
        } else {
            sendError('Failed to create user', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to create user: ' . $e->getMessage(), 500);
    }
}

function updateUser($id) {
    if (empty($id)) {
        sendError('User ID is required', 400);
    }
    
    $input = getJsonInput();
    
    try {
        $userModel = new User();
        $success = $userModel->updateUser($id, $input);
        
        if ($success) {
            sendSuccess([], 'User updated successfully');
        } else {
            sendError('Failed to update user', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to update user: ' . $e->getMessage(), 500);
    }
}

function deleteUser($id) {
    if (empty($id)) {
        sendError('User ID is required', 400);
    }
    
    try {
        $userModel = new User();
        $success = $userModel->deleteUser($id);
        
        if ($success) {
            sendSuccess([], 'User deleted successfully');
        } else {
            sendError('Failed to delete user', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to delete user: ' . $e->getMessage(), 500);
    }
}
?>
