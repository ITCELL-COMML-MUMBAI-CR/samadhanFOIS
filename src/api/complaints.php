<?php
/**
 * Complaints API
 * Handles complaint-related requests
 */

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
        
    default:
        sendError('Invalid complaint action', 400);
        break;
}

function handleComplaintList() {
    try {
        require_once __DIR__ . '/../models/Complaint.php';
        $complaintModel = new Complaint();
        
        $userRole = $_SESSION['user_role'];
        $userId = $_SESSION['user_login_id'];
        
        $complaints = [];
        
        if ($userRole === 'customer') {
            // Customers see only their own complaints
            $complaints = $complaintModel->findByCustomer($userId);
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
        $complaintModel = new Complaint();
        
        $complaint = $complaintModel->findByComplaintId($complaintId);
        
        if (!$complaint) {
            sendError('Complaint not found', 404);
        }
        
        sendSuccess($complaint);
        
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

        $complaintModel = new Complaint();
        $transactionModel = new Transaction();
        $emailService = new EmailService();

        $complaint = $complaintModel->findByComplaintId($complaintId);
        if (!$complaint) {
            sendError('Complaint not found', 404);
        }

        // Update status to replied
        $complaintModel->updateStatus($complaintId, 'replied', $actionTaken);
        $transactionModel->logStatusUpdate($complaintId, ($remarks ?: 'Replied to customer'), $_SESSION['user_login_id']);

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
?>
