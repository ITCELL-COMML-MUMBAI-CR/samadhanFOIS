<?php
/**
 * Email Templates API
 * Handles email template CRUD operations for admin users
 */

// Check if user is logged in and is admin
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    sendError('Authentication required', 401);
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    sendError('Access denied - Admin privileges required', 403);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            handleCreateTemplate();
            break;
        case 'update':
            handleUpdateTemplate();
            break;
        case 'delete':
            handleDeleteTemplate();
            break;
        case 'get':
            handleGetTemplate();
            break;
        case 'list':
            handleListTemplates();
            break;
        default:
            sendError('Invalid action', 400);
            break;
    }
} else {
    sendError('Method not allowed', 405);
}

/**
 * Handle template creation
 */
function handleCreateTemplate() {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    $description = $_POST['description'] ?? '';
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($category) || empty($subject) || empty($content)) {
        sendError('Missing required fields');
    }
    
    // Load required models and services
    require_once __DIR__ . '/../models/EmailTemplate.php';
    require_once __DIR__ . '/../utils/Logger.php';
    require_once __DIR__ . '/../utils/SessionManager.php';
    
    try {
        $templateModel = new EmailTemplate();
        $currentUser = SessionManager::getCurrentUser();
        
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        // If setting as default, unset other defaults in the same category
        if ($isDefault) {
            $templateModel->unsetDefaultForCategory($category);
        }
        
        // Create template
        $templateId = $templateModel->create([
            'name' => $name,
            'category' => $category,
            'subject' => $subject,
            'content' => $content,
            'description' => $description,
            'is_default' => $isDefault,
            'created_by' => $currentUser['login_id']
        ]);
        
        if ($templateId) {
            // Log the action
            $logger = new Logger();
            $logger->log('email_template_created', [
                'admin_id' => $currentUser['login_id'],
                'admin_name' => $currentUser['name'] ?? $currentUser['login_id'],
                'template_id' => $templateId,
                'template_name' => $name,
                'category' => $category
            ]);
            
            sendSuccess(['template_id' => $templateId], 'Email template created successfully');
        } else {
            sendError('Failed to create template');
        }
        
    } catch (Exception $e) {
        error_log('Error creating email template: ' . $e->getMessage());
        sendError('Error creating template: ' . $e->getMessage());
    }
}

/**
 * Handle template update
 */
function handleUpdateTemplate() {
    $templateId = $_POST['template_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    $description = $_POST['description'] ?? '';
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate required fields
    if (empty($templateId) || empty($name) || empty($category) || empty($subject) || empty($content)) {
        sendError('Missing required fields');
    }
    
    // Load required models and services
    require_once __DIR__ . '/../models/EmailTemplate.php';
    require_once __DIR__ . '/../utils/Logger.php';
    require_once __DIR__ . '/../utils/SessionManager.php';
    
    try {
        $templateModel = new EmailTemplate();
        $currentUser = SessionManager::getCurrentUser();
        
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        // Check if template exists
        $existingTemplate = $templateModel->findById($templateId);
        if (!$existingTemplate) {
            sendError('Template not found');
        }
        
        // If setting as default, unset other defaults in the same category
        if ($isDefault) {
            $templateModel->unsetDefaultForCategory($category, $templateId);
        }
        
        // Update template
        $result = $templateModel->update($templateId, [
            'name' => $name,
            'category' => $category,
            'subject' => $subject,
            'content' => $content,
            'description' => $description,
            'is_default' => $isDefault,
            'updated_by' => $currentUser['login_id']
        ]);
        
        if ($result) {
            // Log the action
            $logger = new Logger();
            $logger->log('email_template_updated', [
                'admin_id' => $currentUser['login_id'],
                'admin_name' => $currentUser['name'] ?? $currentUser['login_id'],
                'template_id' => $templateId,
                'template_name' => $name,
                'category' => $category
            ]);
            
            sendSuccess([], 'Email template updated successfully');
        } else {
            sendError('Failed to update template');
        }
        
    } catch (Exception $e) {
        error_log('Error updating email template: ' . $e->getMessage());
        sendError('Error updating template: ' . $e->getMessage());
    }
}

/**
 * Handle template deletion
 */
function handleDeleteTemplate() {
    $templateId = $_POST['template_id'] ?? '';
    
    if (empty($templateId)) {
        sendError('Template ID is required');
    }
    
    // Load required models and services
    require_once __DIR__ . '/../models/EmailTemplate.php';
    require_once __DIR__ . '/../utils/Logger.php';
    require_once __DIR__ . '/../utils/SessionManager.php';
    
    try {
        $templateModel = new EmailTemplate();
        $currentUser = SessionManager::getCurrentUser();
        
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        // Check if template exists
        $template = $templateModel->findById($templateId);
        if (!$template) {
            sendError('Template not found');
        }
        
        // Check if it's a default template
        if ($template['is_default']) {
            sendError('Cannot delete default template');
        }
        
        // Delete template
        $result = $templateModel->delete($templateId);
        
        if ($result) {
            // Log the action
            $logger = new Logger();
            $logger->log('email_template_deleted', [
                'admin_id' => $currentUser['login_id'],
                'admin_name' => $currentUser['name'] ?? $currentUser['login_id'],
                'template_id' => $templateId,
                'template_name' => $template['name']
            ]);
            
            sendSuccess([], 'Email template deleted successfully');
        } else {
            sendError('Failed to delete template');
        }
        
    } catch (Exception $e) {
        error_log('Error deleting email template: ' . $e->getMessage());
        sendError('Error deleting template: ' . $e->getMessage());
    }
}

/**
 * Handle getting a single template
 */
function handleGetTemplate() {
    $templateId = $_POST['template_id'] ?? '';
    
    if (empty($templateId)) {
        sendError('Template ID is required');
    }
    
    // Load required models
    require_once __DIR__ . '/../models/EmailTemplate.php';
    require_once __DIR__ . '/../utils/SessionManager.php';
    
    try {
        $templateModel = new EmailTemplate();
        $currentUser = SessionManager::getCurrentUser();
        
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        $template = $templateModel->findById($templateId);
        
        if (!$template) {
            sendError('Template not found');
        }
        
        sendSuccess($template, 'Template retrieved successfully');
        
    } catch (Exception $e) {
        error_log('Error getting email template: ' . $e->getMessage());
        sendError('Error retrieving template: ' . $e->getMessage());
    }
}

/**
 * Handle listing all templates
 */
function handleListTemplates() {
    // Load required models
    require_once __DIR__ . '/../models/EmailTemplate.php';
    require_once __DIR__ . '/../utils/SessionManager.php';
    
    try {
        $templateModel = new EmailTemplate();
        $currentUser = SessionManager::getCurrentUser();
        
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        $templates = $templateModel->getAll();
        
        sendSuccess($templates, 'Templates retrieved successfully');
        
    } catch (Exception $e) {
        error_log('Error listing email templates: ' . $e->getMessage());
        sendError('Error retrieving templates: ' . $e->getMessage());
    }
}
