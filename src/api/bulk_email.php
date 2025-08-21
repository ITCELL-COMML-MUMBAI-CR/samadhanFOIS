<?php
/**
 * Bulk Email API
 * Handles bulk email sending for admin users
 */

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    sendError('Authentication required', 401);
}

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    sendError('Access denied - Admin privileges required', 403);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'send';
    
    if ($action === 'test') {
        handleTestEmail();
    } else {
        handleBulkEmail();
    }
} else {
    sendError('Method not allowed', 405);
}

/**
 * Handle test email sending
 */
function handleTestEmail() {
    $testEmail = $_POST['test_email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    $cc = $_POST['cc'] ?? '';
    
    if (empty($testEmail) || empty($subject) || empty($content)) {
        sendError('Missing required fields');
    }
    
    // Load required models and services
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../utils/EmailService.php';
    
    if (!EmailService::isValidEmail($testEmail)) {
        sendError('Invalid email address');
    }
    
    // Process content with test data
    $processedContent = processEmailContent($content, [
        'name' => 'Test User',
        'login_id' => 'TEST001',
        'email' => $testEmail,
        'department' => 'TEST',
        'role' => 'test'
    ]);
    
    // Send test email
    $emailService = new EmailService();
    $result = $emailService->sendCustomEmail($testEmail, $subject, $processedContent, $cc);
    
    if ($result) {
        sendSuccess([], 'Test email sent successfully');
    } else {
        sendError('Failed to send test email');
    }
}

/**
 * Handle bulk email sending
 */
function handleBulkEmail() {
    $recipientType = $_POST['recipient_type'] ?? '';
    $selectedUsers = $_POST['selected_users'] ?? [];
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    $cc = $_POST['cc'] ?? '';
    $template = $_POST['template'] ?? '';
    
    // Load required models and services
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../utils/EmailService.php';
    require_once __DIR__ . '/../utils/Logger.php';
    
    // Validate required fields
    if (empty($subject) || empty($content)) {
        sendError('Missing required fields');
    }
    
    // Get recipients
    $recipients = [];
    if ($recipientType === 'all') {
        $userModel = new User();
        $users = $userModel->getAllUsers();
        foreach ($users as $user) {
            if (!empty($user['email'])) {
                $recipients[] = $user;
            }
        }
    } elseif ($recipientType === 'by_role') {
        // Get users by selected roles
        $selectedRoles = $_POST['selected_roles'] ?? [];
        if (!empty($selectedRoles)) {
            $userModel = new User();
            $users = $userModel->getAllUsers();
            foreach ($users as $user) {
                if (!empty($user['email']) && in_array($user['role'], $selectedRoles)) {
                    $recipients[] = $user;
                }
            }
        }
    } else {
        // Get selected users
        $userModel = new User();
        foreach ($selectedUsers as $email) {
            $user = $userModel->findByEmail($email);
            if ($user) {
                $recipients[] = $user;
            }
        }
    }
    
    if (empty($recipients)) {
        sendError('No valid recipients found');
    }
    
    // Process template variables if needed
    $templateVars = [];
    if (!empty($template)) {
        // Load template from database to check category
        require_once __DIR__ . '/../models/EmailTemplate.php';
        $templateModel = new EmailTemplate();
        $templateData = $templateModel->findById($template);
        
        if ($templateData && $templateData['category'] === 'maintenance') {
            $templateVars['maintenance_date'] = $_POST['maintenance_date'] ?? '';
            $templateVars['maintenance_time'] = $_POST['maintenance_time'] ?? '';
        }
    }
    
    // Send emails
    $emailService = new EmailService();
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($recipients as $user) {
        try {
            // Process content with user data
            $processedContent = processEmailContent($content, $user, $templateVars);
            
            // Send email
            $result = $emailService->sendCustomEmail($user['email'], $subject, $processedContent, $cc);
            
            if ($result) {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = "Failed to send to: " . $user['email'];
            }
            
            // Add small delay to prevent overwhelming the mail server
            usleep(100000); // 0.1 second delay
            
        } catch (Exception $e) {
            $errorCount++;
            $errors[] = "Error sending to " . $user['email'] . ": " . $e->getMessage();
        }
    }
    
    // Log the bulk email operation
    $logger = new Logger();
    $templateName = 'Custom';
    if (!empty($template) && isset($templateData)) {
        $templateName = $templateData['name'];
    }
    
    require_once __DIR__ . '/../utils/SessionManager.php';
    $currentUser = SessionManager::getCurrentUser();
    
    $logger->log('bulk_email', [
        'admin_id' => $currentUser['login_id'] ?? 'unknown',
        'admin_name' => $currentUser['name'] ?? $currentUser['login_id'] ?? 'unknown',
        'recipient_count' => count($recipients),
        'success_count' => $successCount,
        'error_count' => $errorCount,
        'subject' => $subject,
        'template' => $templateName,
        'template_id' => $template
    ]);
    
    // Return results
    $message = "Bulk email completed. Successfully sent: $successCount, Failed: $errorCount";
    if ($errorCount > 0) {
        $message .= ". Some emails failed to send.";
    }
    
    sendSuccess([
        'success_count' => $successCount,
        'error_count' => $errorCount,
        'total_recipients' => count($recipients),
        'errors' => $errors
    ], $message);
}

/**
 * Process email content with placeholders
 */
function processEmailContent($content, $user, $templateVars = []) {
    // Replace user placeholders
    $replacements = [
        '{name}' => $user['name'] ?? '',
        '{login_id}' => $user['login_id'] ?? '',
        '{email}' => $user['email'] ?? '',
        '{department}' => $user['department'] ?? '',
        '{role}' => $user['role'] ?? '',
        '{portal_url}' => BASE_URL
    ];
    
    // Add template-specific variables
    foreach ($templateVars as $key => $value) {
        $replacements['{' . $key . '}'] = $value;
    }
    
    return str_replace(array_keys($replacements), array_values($replacements), $content);
}
