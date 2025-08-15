<?php
/**
 * Bulk Email API
 * Handles bulk email sending for admin users
 */

require_once '../init.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
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
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
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
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    if (!EmailService::isValidEmail($testEmail)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
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
        echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send test email']);
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
    
    // Validate required fields
    if (empty($subject) || empty($content)) {
        header('Location: ' . BASE_URL . 'admin_bulk_email?error=Missing required fields');
        exit;
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
        header('Location: ' . BASE_URL . 'admin_bulk_email?error=No valid recipients found');
        exit;
    }
    
    // Process template variables if needed
    $templateVars = [];
    if ($template === 'system_maintenance') {
        $templateVars['maintenance_date'] = $_POST['maintenance_date'] ?? '';
        $templateVars['maintenance_time'] = $_POST['maintenance_time'] ?? '';
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
    $logger->log('bulk_email', [
        'admin_id' => $_SESSION['user_id'],
        'admin_name' => $_SESSION['name'],
        'recipient_count' => count($recipients),
        'success_count' => $successCount,
        'error_count' => $errorCount,
        'subject' => $subject,
        'template' => $template
    ]);
    
    // Redirect with results
    $message = "Bulk email completed. Successfully sent: $successCount, Failed: $errorCount";
    if ($errorCount > 0) {
        $message .= ". Some emails failed to send.";
    }
    
    header('Location: ' . BASE_URL . 'admin_bulk_email?success=' . urlencode($message));
    exit;
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
