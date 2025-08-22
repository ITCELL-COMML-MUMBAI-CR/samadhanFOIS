<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../utils/SessionManager.php';
require_once __DIR__ . '/../utils/CSRF.php';

class CustomerTicketsController extends BaseController {
    
    public function __construct() {
        // Ensure user is logged in and is a customer
        SessionManager::requireLogin();
        $currentUser = SessionManager::getCurrentUser();
        
        if ($currentUser['role'] !== 'customer') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }
    
    public function index() {
        $currentUser = SessionManager::getCurrentUser();
        $customerId = $currentUser['customer_id'];
        
        // Load models
        $complaintModel = $this->loadModel('Complaint');
        
        // Auto-close old complaints (3 days old with no feedback)
        $complaintModel->autoCloseOldComplaints();
        
        // Get all tickets for customer (only pending, replied, reverted)
        $tickets = $complaintModel->findByCustomerWithFilters($customerId, [], '', 0, 0);
        
        // Filter out closed tickets
        $tickets = array_filter($tickets, function($ticket) {
            return strtolower($ticket['status']) !== 'closed';
        });
        
        // Prepare data for view
        $data = [
            'pageTitle' => 'My Support Tickets',
            'tickets' => $tickets,
            'currentUser' => $currentUser,
            'error' => '',
            'success' => '',
            'customJS' => ['js/customer_tickets.js']
        ];
        
        // Load view
        $this->loadView('header', $data);
        $this->loadView('pages/customer_tickets', $data);
        $this->loadView('footer', $data);
    }
    
    /**
     * Handle feedback submission
     */
    public function submitFeedback() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => true, 'message' => 'Method not allowed']);
            return;
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'Invalid security token']);
            return;
        }
        
        $currentUser = SessionManager::getCurrentUser();
        $customerId = $currentUser['customer_id'];
        
        // Validate input
        $ticketId = $_POST['ticket_id'] ?? 0;
        $rating = intval($_POST['rating'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');
        
        if (empty($ticketId) || $rating < 1 || $rating > 5) {
            echo json_encode(['error' => true, 'message' => 'Invalid input parameters']);
            return;
        }
        
        // Load models
        $complaintModel = $this->loadModel('Complaint');
        $transactionModel = $this->loadModel('Transaction');
        
        try {
            // Verify ticket belongs to customer and is in replied status
            $ticket = $complaintModel->findByComplaintId($ticketId);
            if (!$ticket || $ticket['customer_id'] != $customerId || strtolower($ticket['status']) !== 'replied') {
                echo json_encode(['error' => true, 'message' => 'Invalid ticket or ticket not in replied status']);
                return;
            }
            
            // Update complaint with feedback
            $updateData = [
                'rating' => $rating,
                'rating_remarks' => $remarks,
                'status' => 'Closed'
            ];
            
            if ($complaintModel->updateComplaint($ticketId, $updateData)) {
                // Log the feedback transaction
                $transactionModel->createTransaction([
                    'complaint_id' => $ticketId,
                    'transaction_type' => 'feedback_submitted',
                    'remarks' => "Customer feedback submitted. Rating: $rating stars" . ($remarks ? ". Remarks: $remarks" : ''),
                    'created_by' => $currentUser['login_id'] ?? 'customer'
                ]);
                
                echo json_encode(['error' => false, 'message' => 'Feedback submitted successfully']);
            } else {
                echo json_encode(['error' => true, 'message' => 'Failed to update ticket']);
            }
            
        } catch (Exception $e) {
            error_log("Error submitting feedback: " . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'An error occurred while submitting feedback']);
        }
    }
    
    /**
     * Handle additional information submission
     */
    public function submitAdditionalInfo() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => true, 'message' => 'Method not allowed']);
            return;
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'Invalid security token']);
            return;
        }
        
        $currentUser = SessionManager::getCurrentUser();
        $customerId = $currentUser['customer_id'];
        
        // Validate input
        $ticketId = $_POST['ticket_id'] ?? 0;
        $additionalInfo = trim($_POST['additional_info'] ?? '');
        
        if (empty($ticketId) || empty($additionalInfo)) {
            echo json_encode(['error' => true, 'message' => 'Invalid input parameters']);
            return;
        }
        
        // Load models
        $complaintModel = $this->loadModel('Complaint');
        $transactionModel = $this->loadModel('Transaction');
        
        try {
            // Verify ticket belongs to customer and is in reverted status
            $ticket = $complaintModel->findByComplaintId($ticketId);
            if (!$ticket || $ticket['customer_id'] != $customerId || strtolower($ticket['status']) !== 'reverted') {
                echo json_encode(['error' => true, 'message' => 'Invalid ticket or ticket not in reverted status']);
                return;
            }
            
            // Update complaint with additional information
            $updateData = [
                'additional_info' => $additionalInfo,
                'status' => 'Pending'
            ];
            
            if ($complaintModel->updateComplaint($ticketId, $updateData)) {
                // Log the additional information transaction
                $transactionModel->createTransaction([
                    'complaint_id' => $ticketId,
                    'transaction_type' => 'additional_info_provided',
                    'remarks' => "Customer provided additional information: $additionalInfo",
                    'created_by' => $currentUser['login_id'] ?? 'customer'
                ]);
                
                echo json_encode(['error' => false, 'message' => 'Additional information submitted successfully']);
            } else {
                echo json_encode(['error' => true, 'message' => 'Failed to update ticket']);
            }
            
        } catch (Exception $e) {
            error_log("Error submitting additional info: " . $e->getMessage());
            echo json_encode(['error' => true, 'message' => 'An error occurred while submitting additional information']);
        }
    }
    
    /**
     * Get ticket details for modal
     */
    public function getTicketDetails($ticketId) {
        $currentUser = SessionManager::getCurrentUser();
        $customerId = $currentUser['customer_id'];
        // Validate ticket ID
        if (empty($ticketId)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Invalid ticket ID']);
            return;
        }
        
        // Load models
        $complaintModel = $this->loadModel('Complaint');
        $evidenceModel = $this->loadModel('Evidence');
        
        try {
            // Get ticket details
            $ticket = $complaintModel->findByComplaintId($ticketId);
            
            // Verify ticket belongs to customer
            if (!$ticket || $ticket['customer_id'] != $customerId) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Access denied']);
                return;
            }
            
            // Get evidence
            $evidence = $evidenceModel->getImages($ticketId);
            $ticket['evidence'] = $evidence;
            
            echo json_encode(['error' => false, 'data' => $ticket]);
            
        } catch (Exception $e) {
            error_log("Error getting ticket details: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'An error occurred while fetching ticket details']);
        }
    }
    
    /**
     * Get transaction history for a ticket
     */
    public function getTransactionHistory($ticketId) {
        $currentUser = SessionManager::getCurrentUser();
        $customerId = $currentUser['customer_id'];
        
        // Validate ticket ID
        if (empty($ticketId)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Invalid ticket ID']);
            return;
        }
        
        // Load models
        $complaintModel = $this->loadModel('Complaint');
        $transactionModel = $this->loadModel('Transaction');
        
        try {
            // Verify ticket belongs to customer
            $ticket = $complaintModel->findByComplaintId($ticketId);
            if (!$ticket || $ticket['customer_id'] != $customerId) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'Access denied']);
                return;
            }
            
            // Get transaction history
            $transactions = $transactionModel->findByComplaintId($ticketId);
            
            // Filter out internal remarks
            $filteredTransactions = array_filter($transactions, function($transaction) {
                return $transaction['transaction_type'] !== 'internal_remark';
            });
            
            echo json_encode(['error' => false, 'data' => array_values($filteredTransactions)]);
            
        } catch (Exception $e) {
            error_log("Error getting transaction history: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'An error occurred while fetching transaction history']);
        }
    }
}