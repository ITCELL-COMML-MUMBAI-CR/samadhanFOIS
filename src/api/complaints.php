<?php
/**
 * Complaints API
 * Handles complaint-related requests
 */

// Handle both URL-based actions and form-based actions
$action = $action ?: ($_POST['action'] ?? $_GET['action'] ?? '');

switch ($action) {
    case 'list':
        if ($method === 'GET') {
            handleComplaintList();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'create':
        if ($method === 'POST') {
            handleCreateComplaint();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'view':
        if ($method === 'GET') {
            handleViewComplaint($id);
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'update':
        if ($method === 'POST') {
            handleUpdateComplaint($id);
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'assign':
        if ($method === 'POST') {
            handleAssignComplaint($id);
        } else {
            sendError('Method not allowed', 405);
        }
        break;
    
    case 'reply':
        if ($method === 'POST') {
            handleReplyToComplaint($id);
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'submit_feedback':
        if ($method === 'POST') {
            handleSubmitFeedback();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'submit_more_info':
        if ($method === 'POST') {
            handleSubmitMoreInfo();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    default:
        sendError('Invalid complaint action', 400);
        break;
}

function handleComplaintList() {
    try {
        require_once __DIR__ . '/../models/Complaint.php';
        require_once __DIR__ . '/../utils/SessionManager.php';
        
        $complaintModel = new Complaint();
        
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        $userRole = $currentUser['role'];
        $userId = $currentUser['login_id'];
        
        $complaints = [];
        
        if ($userRole === 'customer') {
            // Customers see only their own complaints
            $customerId = $currentUser['customer_id'] ?? null;
            if (!$customerId) {
                sendError('Customer ID not found in session', 403);
            }
            $complaints = $complaintModel->findByCustomer($customerId);
            
            // Filter out internal details for customers (but keep category)
            foreach ($complaints as &$complaint) {
                unset($complaint['assigned_to'], $complaint['assigned_to_name'], $complaint['department'], $complaint['priority']);
                // category is kept for customers
            }
        } else {
            // Staff see all complaints or assigned complaints based on role
            if ($userRole === 'controller') {
                $complaints = $complaintModel->findAssignedTo($userId);
            } else {
                // Admin sees all complaints
                $complaints = $complaintModel->findAll();
            }
        }
        
        sendSuccess($complaints);
        
    } catch (Exception $e) {
        sendError('Failed to get complaints: ' . $e->getMessage());
    }
}

function handleCreateComplaint() {
    try {
        $input = getJsonInput();
        
        require_once __DIR__ . '/../models/Complaint.php';
        $complaintModel = new Complaint();
        
        // Validate required fields
        $requiredFields = ['customer_id', 'subject', 'description', 'category'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                sendError("Field '$field' is required");
            }
        }
        
        // Ensure default assignment to commercial controller
        if (empty($input['assigned_to'])) {
            $input['assigned_to'] = 'commercial_controller';
        }
        $complaintId = $complaintModel->createComplaint($input);
        
        if ($complaintId) {
            sendSuccess(['complaint_id' => $complaintId], 'Complaint created successfully');
        } else {
            sendError('Failed to create complaint');
        }
        
    } catch (Exception $e) {
        sendError('Failed to create complaint: ' . $e->getMessage());
    }
}

function handleViewComplaint($complaintId) {
    try {
        if (empty($complaintId)) {
            sendError('Complaint ID is required');
        }
        
        require_once __DIR__ . '/../models/Complaint.php';
        require_once __DIR__ . '/../utils/SessionManager.php';
        
        $complaintModel = new Complaint();
        $currentUser = SessionManager::getCurrentUser();
        
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        // For customers, we need to get complaint with wagon details
        if ($currentUser['role'] === 'customer') {
            $customerId = $currentUser['customer_id'] ?? null;
            if (!$customerId) {
                sendError('Customer ID not found in session', 403);
            }
            
            // Get complaint with wagon details for customers
            $complaints = $complaintModel->findByCustomerWithFilters($customerId, [], '', 1, 0);
            $complaint = null;
            foreach ($complaints as $comp) {
                if ($comp['complaint_id'] === $complaintId) {
                    $complaint = $comp;
                    break;
                }
            }
        } else {
            // For admins/controllers, use the regular method
            $complaint = $complaintModel->findByComplaintId($complaintId);
        }
        
        if (!$complaint) {
            sendError('Complaint not found', 404);
        }
        
        $userRole = $currentUser['role'];
        $customerId = $currentUser['customer_id'] ?? null;
        
        // Access control: customers can only view their own complaints
        if ($userRole === 'customer') {
            if ($complaint['customer_id'] !== $customerId) {
                sendError('Access denied', 403);
            }
            
            // Get evidence data
            require_once __DIR__ . '/../models/Evidence.php';
            $evidenceModel = new Evidence();
            $evidence = $evidenceModel->findByComplaintId($complaintId);
            
            // Prepare evidence images for response
            $evidenceImages = [];
            if ($evidence) {
                for ($i = 1; $i <= 3; $i++) {
                    $imageField = "image_$i";
                    if (!empty($evidence[$imageField])) {
                        $evidenceImages[] = [
                            'filename' => $evidence[$imageField],
                            'url' => BASE_URL . 'uploads/evidences/' . $evidence[$imageField]
                        ];
                    }
                }
            }
            
            // Filter out internal details for customers
            $customerViewComplaint = [
                'complaint_id' => $complaint['complaint_id'],
                'category' => $complaint['category'],
                'complaint_type' => $complaint['Type'],
                'complaint_subtype' => $complaint['Subtype'],
                'location' => $complaint['Location'],
                'wagon_type' => $complaint['wagon_type'] ?? null,
                'wagon_code' => $complaint['wagon_code'] ?? null,
                'fnr_no' => $complaint['FNR_Number'],
                'description' => $complaint['description'],
                'action_taken' => $complaint['action_taken'],
                'status' => $complaint['status'],
                'date' => $complaint['date'],
                'time' => $complaint['time'],
                'created_at' => $complaint['created_at'],
                'customer_name' => $complaint['customer_name'],
                'customer_id' => $complaint['customer_id'],
                'evidence' => $evidenceImages
            ];
            sendSuccess($customerViewComplaint);
        } else {
            // Admin/controllers see full details including auto-priority
            $complaint['display_priority'] = $complaintModel->calculateAutoPriority($complaint['created_at']);
            
            // Get transaction history
            require_once __DIR__ . '/../models/Transaction.php';
            $transactionModel = new Transaction();
            $transactions = $transactionModel->findByComplaintId($complaintId);
            
            // Add transactions to complaint data
            $complaint['transactions'] = $transactions;
            
            sendSuccess($complaint);
        }
        
    } catch (Exception $e) {
        sendError('Failed to get complaint: ' . $e->getMessage());
    }
}

function handleUpdateComplaint($complaintId) {
    try {
        if (empty($complaintId)) {
            sendError('Complaint ID is required');
        }
        
        $input = getJsonInput();
        
        require_once __DIR__ . '/../models/Complaint.php';
        $complaintModel = new Complaint();
        
        $result = $complaintModel->updateComplaint($complaintId, $input);
        
        if ($result) {
            sendSuccess([], 'Complaint updated successfully');
        } else {
            sendError('Failed to update complaint');
        }
        
    } catch (Exception $e) {
        sendError('Failed to update complaint: ' . $e->getMessage());
    }
}

function handleAssignComplaint($complaintId) {
    try {
        if (empty($complaintId)) {
            sendError('Complaint ID is required');
        }
        
        $input = getJsonInput();
        $assignedTo = $input['assigned_to'] ?? '';
        
        if (empty($assignedTo)) {
            sendError('Assigned user is required');
        }
        
        require_once __DIR__ . '/../models/Complaint.php';
        $complaintModel = new Complaint();
        
        $result = $complaintModel->assignComplaint($complaintId, $assignedTo);
        
        if ($result) {
            sendSuccess([], 'Complaint assigned successfully');
        } else {
            sendError('Failed to assign complaint');
        }
        
    } catch (Exception $e) {
        sendError('Failed to assign complaint: ' . $e->getMessage());
    }
}

/**
 * Controller sets complaint as replied to customer.
 * Moves status to 'replied', logs transaction, and emails customer.
 */
function handleReplyToComplaint($complaintId) {
    try {
        if (empty($complaintId)) {
            sendError('Complaint ID is required');
        }
        $input = getJsonInput();
        $actionTaken = $input['action_taken'] ?? '';
        $remarks = $input['remarks'] ?? '';

        require_once __DIR__ . '/../models/Complaint.php';
        require_once __DIR__ . '/../models/Transaction.php';
        require_once __DIR__ . '/../utils/EmailService.php';
        require_once __DIR__ . '/../utils/SessionManager.php';

        $complaintModel = new Complaint();
        $transactionModel = new Transaction();
        $emailService = new EmailService();
        
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }

        $complaint = $complaintModel->findByComplaintId($complaintId);
        if (!$complaint) {
            sendError('Complaint not found', 404);
        }

        // Update status to replied
        $complaintModel->updateStatus($complaintId, 'replied', $actionTaken);
        $transactionModel->logStatusUpdate($complaintId, ($remarks ?: 'Replied to customer'), $currentUser['login_id']);

        // Email customer
        $customerEmail = $complaint['customer_email'] ?? '';
        $customerName = $complaint['customer_name'] ?? 'Valued Customer';
        if ($customerEmail && EmailService::isValidEmail($customerEmail)) {
            $emailService->sendStatusUpdate($customerEmail, $customerName, $complaintId, $complaint['status'], 'replied', $remarks);
        }

        sendSuccess([], 'Reply sent to customer and status updated to replied');
    } catch (Exception $e) {
        sendError('Failed to reply to complaint: ' . $e->getMessage());
    }
}

/**
 * Customer submits feedback for replied complaint
 */
function handleSubmitFeedback() {
    try {
        require_once __DIR__ . '/../utils/SessionManager.php';
        
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        // Validate user is customer
        if ($currentUser['role'] !== 'customer') {
            sendError('Only customers can submit feedback', 403);
        }
        
        // Handle both form data and JSON input
        $input = $_SERVER['CONTENT_TYPE'] === 'application/json' ? getJsonInput() : $_POST;
        
        // Validate CSRF token
        if (!SessionManager::validateCSRFToken($input['csrf_token'] ?? '')) {
            sendError('Invalid CSRF token', 403);
        }
        
        $complaintId = $input['complaint_id'] ?? '';
        $feedback = trim($input['feedback_text'] ?? '');
        $rating = $input['feedback_rating'] ?? '';
        
        if (empty($complaintId)) {
            sendError('Complaint ID is required');
        }
        
        if (empty($feedback)) {
            sendError('Please provide feedback');
        }
        
        if (empty($rating) || !in_array($rating, ['Excellent', 'Satisfactory', 'Unsatisfactory'])) {
            sendError('Please select a rating');
        }
        
        require_once __DIR__ . '/../models/Complaint.php';
        require_once __DIR__ . '/../models/Transaction.php';
        
        $complaintModel = new Complaint();
        $transactionModel = new Transaction();
        
        // Verify complaint belongs to customer
        $complaint = $complaintModel->findByComplaintId($complaintId);
        if (!$complaint) {
            sendError('Complaint not found', 404);
        }
        
        if ($complaint['customer_id'] !== $currentUser['customer_id']) {
            sendError('Access denied', 403);
        }
        
        // Update complaint with rating and close it
        $complaintModel->updateComplaint($complaintId, [
            'rating' => $rating,
            'rating_remarks' => $feedback,
            'status' => 'closed'
        ]);
        
        $transactionModel->logStatusUpdate($complaintId, 'Customer feedback: ' . $feedback . ' (Rating: ' . $rating . ')', $currentUser['login_id']);
        
        sendSuccess([], 'Feedback submitted successfully');
        
    } catch (Exception $e) {
        sendError('Failed to submit feedback: ' . $e->getMessage());
    }
}

/**
 * Customer submits more information for reverted complaint
 */
function handleSubmitMoreInfo() {
    try {
        require_once __DIR__ . '/../utils/SessionManager.php';
        
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            sendError('User not authenticated', 401);
        }
        
        // Validate user is customer
        if ($currentUser['role'] !== 'customer') {
            sendError('Only customers can submit additional information', 403);
        }
        
        // Handle both form data and JSON input
        $input = $_SERVER['CONTENT_TYPE'] === 'application/json' ? getJsonInput() : $_POST;
        
        // Validate CSRF token
        if (!SessionManager::validateCSRFToken($input['csrf_token'] ?? '')) {
            sendError('Invalid CSRF token', 403);
        }
        
        $complaintId = $input['complaint_id'] ?? '';
        $moreInfo = trim($input['more_info_text'] ?? '');
        
        if (empty($complaintId)) {
            sendError('Complaint ID is required');
        }
        
        if (strlen($moreInfo) < 3) {
            sendError('Please provide additional information');
        }
        
        require_once __DIR__ . '/../models/Complaint.php';
        require_once __DIR__ . '/../models/Transaction.php';
        require_once __DIR__ . '/../models/Evidence.php';
        
        $complaintModel = new Complaint();
        $transactionModel = new Transaction();
        $evidenceModel = new Evidence();
        
        // Verify complaint belongs to customer
        $complaint = $complaintModel->findByComplaintId($complaintId);
        if (!$complaint) {
            sendError('Complaint not found', 404);
        }
        
        if ($complaint['customer_id'] !== $currentUser['customer_id']) {
            sendError('Access denied', 403);
        }
        
        // Handle image deletion if requested
        $deleteImages = $input['delete_images'] ?? [];
        if (!empty($deleteImages)) {
            // Get current evidence to map filenames to indices
            $currentEvidence = $evidenceModel->findByComplaintId($complaintId);
            if ($currentEvidence) {
                foreach ($deleteImages as $filename) {
                    // Find which image field contains this filename
                    for ($i = 1; $i <= 3; $i++) {
                        $imageField = "image_$i";
                        if ($currentEvidence[$imageField] === $filename) {
                            $evidenceModel->deleteImage($complaintId, $i);
                            break;
                        }
                    }
                }
            }
        }
        
        // Handle additional evidence upload if provided
        if (!empty($_FILES['additional_evidence']['tmp_name'][0])) {
            $uploadResult = $evidenceModel->handleFileUpload($_FILES['additional_evidence'], $complaintId);
            if (!$uploadResult['success'] && !empty($uploadResult['errors'])) {
                sendError('Failed to upload some files: ' . implode(', ', $uploadResult['errors']));
            }
        }
        
        // Move back to pending and assign to commercial controller for review
        $complaintModel->updateStatus($complaintId, 'pending');
        $complaintModel->assignTo($complaintId, 'commercial_controller');
        $transactionModel->logStatusUpdate($complaintId, 'Customer provided more information: ' . $moreInfo, $currentUser['login_id']);
        
        sendSuccess([], 'Additional information submitted successfully');
        
    } catch (Exception $e) {
        sendError('Failed to submit additional information: ' . $e->getMessage());
    }
}
?>
