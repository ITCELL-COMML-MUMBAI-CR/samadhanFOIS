<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class ComplaintController extends BaseController {

    public function __construct() {
        SessionManager::requireLogin();
    }

    /**
     * List all grievances (admin/controller)
     */
    public function index() {
        SessionManager::requireAnyRole(['admin', 'controller']);

        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $department = $_GET['department'] ?? '';
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';

        $filters = [];
        if (!empty($status)) $filters['status'] = $status;
        if (!empty($priority)) $filters['priority'] = $priority;
        if (!empty($department)) $filters['department'] = $department;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;

        $complaintModel = $this->loadModel('Complaint');
        
        // Update auto-priorities before displaying
        $complaintModel->updateAutoPriorities();
        
        $grievances = !empty($search) ? $complaintModel->search($search, $filters) : $complaintModel->search('', $filters);

        // Calculate auto-priority for each grievance for display
        foreach ($grievances as &$grievance) {
            $grievance['display_priority'] = $complaintModel->calculateAutoPriority($grievance['created_at']);
        }

        $data = compact('grievances', 'status', 'priority', 'department', 'search', 'dateFrom', 'dateTo');
        $this->loadView('header', ['pageTitle' => 'All Grievances']);
        $this->loadView('pages/grievances', $data);
        $this->loadView('footer');
    }

    /**
     * List grievances for the logged-in customer
     */
    public function my() {
        SessionManager::requireRole('customer');
        $currentUser = SessionManager::getCurrentUser();

        $complaintModel = $this->loadModel('Complaint');
        $grievances = $complaintModel->findByCustomer($currentUser['customer_id']);

        $data = compact('grievances', 'currentUser');
        $this->loadView('header', ['pageTitle' => 'My Grievances']);
        $this->loadView('pages/my_grievances', $data);
        $this->loadView('footer');
    }

    /**
     * Grievances assigned to me (controller)
     */
    public function assignedToMe() {
        SessionManager::requireRole('controller');
        
        $currentUser = SessionManager::getCurrentUser();
        $complaintModel = $this->loadModel('Complaint');
        $userModel = $this->loadModel('User');
        
        // Get filters
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $search = $_GET['search'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $action = $_POST['action'] ?? '';
                $complaintId = $_POST['complaint_id'] ?? '';
                
                // Validate CSRF token
                if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                
                $transactionModel = $this->loadModel('Transaction');
                $rejectionModel = $this->loadModel('ComplaintRejection');
                
                switch ($action) {
                    case 'close':
                        // Close complaint -> send for Commercial approval
                        $actionTaken = sanitizeInput($_POST['action_taken'] ?? '');
                        $remarks = sanitizeInput($_POST['remarks'] ?? '');
                        if (empty($actionTaken) || empty($remarks)) {
                            throw new Exception('Action taken and internal remarks are required');
                        }
                        // Set status to awaiting_approval and assign to commercial controller
                        $result = $complaintModel->updateStatus($complaintId, 'awaiting_approval', $actionTaken);
                        // Assign to commercial controller for approval
                        $assignResult = $complaintModel->assignTo($complaintId, 'commercial_controller');
                        if ($result) {
                            $transactionModel->logStatusUpdate($complaintId, 'Closed by controller. Awaiting Commercial approval. Remarks: ' . $remarks, $currentUser['login_id']);
                            $_SESSION['alert_message'] = "Grievance sent for Commercial approval.";
                            $_SESSION['alert_type'] = 'success';
                        } else {
                            $_SESSION['alert_message'] = 'Failed to process close request.';
                            $_SESSION['alert_type'] = 'error';
                        }
                        // Redirect to prevent resubmission
                        $this->redirect('grievances/tome');
                        return;
                        
                    case 'forward':
                        $toDepartment = $_POST['to_department'] ?? '';
                        $toUser = $_POST['to_user'] ?? '';
                        $forwardRemarks = sanitizeInput($_POST['forward_remarks'] ?? '');
                        
                        if (empty($toDepartment) || empty($forwardRemarks)) {
                            throw new Exception('Department and remarks are required for forwarding');
                        }
                        
                        // Update complaint assignment
                        if (!empty($toUser)) {
                            $complaintModel->assignTo($complaintId, $toUser);
                        }
                        
                        // Log forward transaction
                        $transactionModel->logForward(
                            $complaintId,
                            $currentUser['login_id'],
                            $toUser,
                            $currentUser['department'],
                            $toDepartment,
                            $forwardRemarks,
                            $currentUser['login_id']
                        );
                        
                        $_SESSION['alert_message'] = 'Grievance forwarded successfully!';
                        $_SESSION['alert_type'] = 'success';
                        $this->redirect('grievances/tome');
                        return;
                        
                    case 'revert':
                        // Only Commercial can revert back to customer
                        if (strtoupper($currentUser['department'] ?? '') !== 'COMMERCIAL') {
                            throw new Exception('Only Commercial Controller can revert to customer');
                        }
                        $rejectionReason = sanitizeInput($_POST['rejection_reason'] ?? '');
                        if (empty($rejectionReason)) {
                            throw new Exception('Remarks are required for revert');
                        }
                        
                        // Fetch complaint and customer user
                        $complaint = $complaintModel->findByComplaintId($complaintId);
                        $customerId = $complaint['customer_id'] ?? null;
                        $customerUser = $customerId ? $userModel->findByCustomerId($customerId) : null;
                        $customerLoginId = $customerUser['login_id'] ?? null;
                        
                        // Log rejection to customer, asking for more information
                        $rejectionModel->logCommercialToCustomer($complaintId, $currentUser['login_id'], null, $rejectionReason);
                        
                        // Update complaint status to reverted and reassign to customer
                        $complaintModel->updateStatus($complaintId, 'reverted');
                        if ($customerLoginId) {
                            $complaintModel->assignTo($complaintId, $customerLoginId);
                        }
                        
                        // Log transaction
                        $transactionModel->logStatusUpdate($complaintId, 'Reverted to customer for more information: ' . $rejectionReason, $currentUser['login_id']);
                        
                        // Email to customer
                        require_once __DIR__ . '/../utils/EmailService.php';
                        $emailService = new EmailService();
                        $customerEmail = $complaint['customer_email'] ?? '';
                        $customerName = $complaint['customer_name'] ?? 'Valued Customer';
                        if ($customerEmail && EmailService::isValidEmail($customerEmail)) {
                            $emailService->sendStatusUpdate($customerEmail, $customerName, $complaintId, ($complaint['status'] ?? 'pending'), 'reverted', $rejectionReason);
                        }
                        
                        $_SESSION['alert_message'] = 'Grievance reverted to customer with remarks.';
                        $_SESSION['alert_type'] = 'success';
                        $this->redirect('grievances/tome');
                        return;
                        
                    case 'assign_priority':
                        $newPriority = $_POST['new_priority'] ?? '';
                        
                        if (empty($newPriority)) {
                            throw new Exception('Priority is required');
                        }
                        
                        $result = $complaintModel->updatePriority($complaintId, $newPriority);
                        
                        if ($result) {
                            $transactionModel->logStatusUpdate($complaintId, "Priority updated to: $newPriority", $currentUser['login_id']);
                            $_SESSION['alert_message'] = 'Priority updated successfully!';
                            $_SESSION['alert_type'] = 'success';
                        } else {
                            $_SESSION['alert_message'] = 'Failed to update priority.';
                            $_SESSION['alert_type'] = 'error';
                        }
                        $this->redirect('grievances/tome');
                        return;
                }
                
            } catch (Exception $e) {
                error_log('Controller action error: ' . $e->getMessage());
                $_SESSION['alert_message'] = $e->getMessage();
                $_SESSION['alert_type'] = 'error';
                $this->redirect('grievances/tome');
                return;
            }
        }
        
        // Get grievances assigned to current user
        $grievances = [];
        $totalCount = 0;
        
        try {
            // Update auto-priorities before displaying
            $complaintModel->updateAutoPriorities();
            
            // Build filter conditions
            $filters = [
                'assigned_to' => $currentUser['login_id']
            ];
            
            if (!empty($status)) {
                $filters['status'] = $status;
            }
            
            if (!empty($priority)) {
                $filters['priority'] = $priority;
            }
            
            // Get filtered grievances
            if (!empty($search)) {
                $grievances = $complaintModel->search($search, $filters);
            } else {
                $grievances = $complaintModel->findAssignedTo($currentUser['login_id']);
                
                // Exclude forwarded away or reverted to customer from "Assigned to Me"
                $grievances = array_filter($grievances, function($g) use ($currentUser) {
                    // If complaint is awaiting approval but not with commercial controller, hide
                    if (($g['status'] ?? '') === 'awaiting_approval' && ($currentUser['department'] ?? '') !== 'COMMERCIAL') {
                        return false;
                    }
                                    // If complaint has been reverted to customer (status reverted) and assigned to a customer login, hide
                if (($g['status'] ?? '') === 'reverted') {
                        if (($g['assigned_to'] ?? '') !== ($currentUser['login_id'] ?? '')) {
                            return false;
                        }
                    }
                    return true;
                });

                // Apply additional filters
                if (!empty($status) || !empty($priority)) {
                    $grievances = array_filter($grievances, function($g) use ($status, $priority) {
                        if (!empty($status) && $g['status'] !== $status) return false;
                        if (!empty($priority) && $g['priority'] !== $priority) return false;
                        return true;
                    });
                }
            }
            
            $totalCount = count($grievances);
            
            // Apply pagination
            $grievances = array_slice($grievances, $offset, $limit);
            
            // Calculate auto-priority for each grievance
            foreach ($grievances as &$grievance) {
                $grievance['display_priority'] = $complaintModel->calculateAutoPriority($grievance['created_at']);
            }
            
        } catch (Exception $e) {
            $error = 'Unable to load grievances.';
        }
        
        // Get department users for forwarding
        $departmentUsers = [];
        $departments = ['COMMERCIAL', 'OPERATING', 'MECHANICAL', 'ELECTRICAL', 'ENGINEERING', 'SECURITY', 'MEDICAL', 'ACCOUNTS', 'PERSONNEL'];
        
        try {
            foreach ($departments as $dept) {
                $departmentUsers[$dept] = $userModel->findByDepartment($dept);
            }
        } catch (Exception $e) {
            // Handle silently
        }
        
        // Calculate pagination
        $totalPages = ceil($totalCount / $limit);
        
        // Check for session alerts
        $error = '';
        $success = '';
        if (isset($_SESSION['alert_message'])) {
            if ($_SESSION['alert_type'] === 'success') {
                $success = $_SESSION['alert_message'];
            } else {
                $error = $_SESSION['alert_message'];
            }
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        }
        
        $data = compact(
            'grievances', 'totalCount', 'currentUser', 'error', 'success',
            'status', 'priority', 'search', 'page', 'totalPages',
            'departmentUsers', 'departments'
        );
        
        $this->loadView('header', ['pageTitle' => 'Assigned to Me']);
        $this->loadView('pages/complaints_to_me', $data);
        $this->loadView('footer');
    }

    /**
     * Approvals queue for Commercial Controller
     */
    public function approvals() {
        SessionManager::requireRole('controller');
        $currentUser = SessionManager::getCurrentUser();
        if (strtoupper($currentUser['department'] ?? '') !== 'COMMERCIAL') {
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'dashboard?error=access_denied');
            }
            return;
        }

        $error = '';
        $success = '';

        // Check for session alerts
        if (isset($_SESSION['alert_message'])) {
            if ($_SESSION['alert_type'] === 'success') {
                $success = $_SESSION['alert_message'];
            } else {
                $error = $_SESSION['alert_message'];
            }
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        }

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }

                $action = $_POST['action'] ?? '';
                $complaintId = $_POST['complaint_id'] ?? '';
                $remarks = sanitizeInput($_POST['remarks'] ?? '');

                $complaintModel = $this->loadModel('Complaint');
                $transactionModel = $this->loadModel('Transaction');
                $userModel = $this->loadModel('User');

                if ($action === 'approve') {
                    $complaint = $complaintModel->findByComplaintId($complaintId);
                    if (!$complaint) {
                        throw new Exception('Complaint not found');
                    }
                    $customerId = $complaint['customer_id'] ?? null;
                    $customerUser = $customerId ? $userModel->findByCustomerId($customerId) : null;
                    $customerLoginId = $customerUser['login_id'] ?? null;

                    // Update status to replied and assign to customer
                    $complaintModel->updateStatus($complaintId, 'replied');
                    if ($customerLoginId) {
                        $complaintModel->assignTo($complaintId, $customerLoginId);
                    }

                    // Log approval
                    $transactionModel->logStatusUpdate($complaintId, 'Commercial approval granted. ' . ($remarks ? ('Remarks: ' . $remarks) : ''), $currentUser['login_id']);

                    // Email customer about replied status
                    require_once __DIR__ . '/../utils/EmailService.php';
                    $emailService = new EmailService();
                    $customerEmail = $complaint['customer_email'] ?? '';
                    $customerName = $complaint['customer_name'] ?? 'Valued Customer';
                    if ($customerEmail && EmailService::isValidEmail($customerEmail)) {
                        $emailService->sendStatusUpdate($customerEmail, $customerName, $complaintId, 'awaiting_approval', 'replied', $remarks);
                    }

                    $_SESSION['alert_message'] = 'Action taken approved and sent to customer for feedback.';
                    $_SESSION['alert_type'] = 'success';
                    $this->redirect('grievances/approvals');
                    return;
                }
            } catch (Exception $e) {
                $_SESSION['alert_message'] = $e->getMessage();
                $_SESSION['alert_type'] = 'error';
                $this->redirect('grievances/approvals');
                return;
            }
        }

        // Fetch approvals list
        $complaintModel = $this->loadModel('Complaint');
        $approvals = $complaintModel->findByStatus('awaiting_approval');

        $data = compact('approvals', 'currentUser', 'error', 'success');
        
        $this->loadView('header', ['pageTitle' => 'Approvals']);
        $this->loadView('pages/approvals', $data);
        $this->loadView('footer');
    }

    /**
     * Detailed complaint view
     */
    public function view($complaintId) {
        $complaintModel = $this->loadModel('Complaint');
        $evidenceModel = $this->loadModel('Evidence');
        $transactionModel = $this->loadModel('Transaction');
        $rejectionModel = $this->loadModel('ComplaintRejection');

        $complaint = $complaintModel->findByComplaintId($complaintId);
        if (!$complaint) {
            $this->loadView('header', ['pageTitle' => 'Complaint Not Found']);
            $this->loadView('pages/404');
            $this->loadView('footer');
            return;
        }

        // Access control: admin/controller/viewer, or complaint owner (customer)
        $currentUser = SessionManager::getCurrentUser();
        $role = $currentUser['role'] ?? '';
        $isOwner = isset($currentUser['customer_id']) && $currentUser['customer_id'] === ($complaint['customer_id'] ?? null);
        $allowedRoles = ['admin', 'controller', 'viewer'];
        if (!$isOwner && !in_array($role, $allowedRoles, true)) {
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'dashboard?error=access_denied');
            }
            return;
        }

        // Handle customer actions: feedback and more information
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token');
                }
                $action = $_POST['action'] ?? '';
                if ($role === 'customer' && $isOwner) {
                    switch ($action) {
                        case 'submit_feedback':
                            $feedback = trim($_POST['feedback_text'] ?? '');
                            $rating = $_POST['feedback_rating'] ?? '';
                            
                            if (strlen($feedback) < 3) {
                                throw new Exception('Please provide brief feedback');
                            }
                            
                            if (empty($rating) || !in_array($rating, ['Excellent', 'Satisfactory', 'Unsatisfactory'])) {
                                throw new Exception('Please select a rating');
                            }
                            
                            // Update complaint with rating and close it
                            $complaintModel->updateComplaint($complaintId, [
                                'rating' => $rating,
                                'rating_remarks' => $feedback,
                                'status' => 'closed'
                            ]);
                            $transactionModel->logStatusUpdate($complaintId, 'Customer feedback: ' . $feedback . ' (Rating: ' . $rating . ')', $currentUser['login_id']);
                            break;
                        case 'submit_more_info':
                            $moreInfo = trim($_POST['more_info_text'] ?? '');
                            if (strlen($moreInfo) < 3) {
                                throw new Exception('Please provide additional information');
                            }
                            
                            // Handle image deletion if requested
                            $deleteImages = $_POST['delete_images'] ?? [];
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
                                    throw new Exception('Failed to upload some files: ' . implode(', ', $uploadResult['errors']));
                                }
                            }
                            
                            // Move back to pending and assign to commercial controller for review
                            $complaintModel->updateStatus($complaintId, 'pending');
                            $complaintModel->assignTo($complaintId, 'commercial_controller');
                            $transactionModel->logStatusUpdate($complaintId, 'Customer provided more information: ' . $moreInfo, $currentUser['login_id']);
                            break;
                    }
                    // Set success message and redirect to prevent resubmission
                    $_SESSION['alert_message'] = 'Action submitted successfully!';
                    $_SESSION['alert_type'] = 'success';
                    $this->redirect("grievances/view/$complaintId");
                }
            } catch (Exception $e) {
                $_SESSION['alert_message'] = $e->getMessage();
                $_SESSION['alert_type'] = 'error';
                $this->redirect("grievances/view/$complaintId");
            }
        }

        $images = $evidenceModel->getImages($complaintId);
        $history = $transactionModel->getComplaintHistory($complaintId);
        $rejections = $rejectionModel->findByComplaintId($complaintId);

        // Handle session alerts
        $alert_message = '';
        $alert_type = '';
        if (isset($_SESSION['alert_message'])) {
            $alert_message = $_SESSION['alert_message'];
            $alert_type = $_SESSION['alert_type'];
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        }

        $data = compact('complaint', 'images', 'history', 'rejections', 'currentUser', 'alert_message', 'alert_type');
        $this->loadView('header', ['pageTitle' => 'Complaint Details']);
        $this->loadView('pages/complaint_details', $data);
        $this->loadView('footer');
    }
    public function create() {
        SessionManager::requireRole('customer');

        $error = '';
        $success = '';
        $currentUser = SessionManager::getCurrentUser();
        $customerDetails = null;
        $complaintTypes = [];
        $typeSubtypeMapping = [];
        $sheds = [];

        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();

            // Get customer details
            $stmt = $connection->prepare("SELECT * FROM customers WHERE CustomerID = ?");
            $stmt->execute([$currentUser['customer_id']]);
            $customerDetails = $stmt->fetch();

            // Get complaint types and their related subtypes
            $stmt = $connection->prepare("SELECT DISTINCT Type FROM complaint_categories WHERE Type IS NOT NULL AND Type != '' ORDER BY Type ASC");
            $stmt->execute();
            $complaintTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $connection->prepare("SELECT Type, SubType FROM complaint_categories WHERE Type IS NOT NULL AND Type != '' AND SubType IS NOT NULL AND SubType != '' ORDER BY Type ASC, SubType ASC");
            $stmt->execute();
            $mappings = $stmt->fetchAll();
            
            foreach ($mappings as $mapping) {
                if (!isset($typeSubtypeMapping[$mapping['Type']])) {
                    $typeSubtypeMapping[$mapping['Type']] = [];
                }
                if (!in_array($mapping['SubType'], $typeSubtypeMapping[$mapping['Type']])) {
                    $typeSubtypeMapping[$mapping['Type']][] = $mapping['SubType'];
                }
            }

            // Get shed locations
            $stmt = $connection->prepare("SELECT ShedID, Terminal, Type FROM shed ORDER BY Terminal ASC");
            $stmt->execute();
            $sheds = $stmt->fetchAll();

        } catch (Exception $e) {
            $error = 'Unable to load page data.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission
            list($error, $success) = $this->handleComplaintSubmission();
        }

        $data = compact('error', 'success', 'currentUser', 'customerDetails', 'complaintTypes', 'typeSubtypeMapping', 'sheds');
        $this->loadView('header', ['pageTitle' => 'New Grievance']);
        $this->loadView('pages/complaint_form', $data);
        $this->loadView('footer');
    }

    private function handleComplaintSubmission() {
        try {
            if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Invalid security token');
            }
            
            $formData = [
                'complaint_type' => sanitizeInput($_POST['complaint_type'] ?? ''),
                'complaint_subtype' => sanitizeInput($_POST['complaint_subtype'] ?? ''),
                'shed_id' => (int)($_POST['shed_id'] ?? 0),
                'fnr_no' => sanitizeInput($_POST['fnr_no'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? '')
            ];
            
            // Validation
            $errors = [];
            if (empty($formData['complaint_type'])) $errors[] = 'Grievance type is required';
            if (empty($formData['complaint_subtype'])) $errors[] = 'Grievance subtype is required';
            if (empty($formData['fnr_no'])) $errors[] = 'FNR Number is required';
            if (empty($formData['shed_id'])) $errors[] = 'Location (Shed) is required';
            if (empty($formData['description'])) {
                $errors[] = 'Description is required';
            } elseif (strlen($formData['description']) < 20) {
                $errors[] = 'Description must be at least 20 characters long';
            }
            
            $db = Database::getInstance();
            $connection = $db->getConnection();
            $categoryMapping = $this->determineCategoryFromTypeAndSubtype($formData['complaint_type'], $formData['complaint_subtype'], $connection);
            if (!$categoryMapping) {
                $errors[] = 'Invalid complaint type and subtype combination selected';
            }
            
            if (empty($errors)) {
                $complaintModel = $this->loadModel('Complaint');
                $evidenceModel = $this->loadModel('Evidence');
                $transactionModel = $this->loadModel('Transaction');
                
                $shedStmt = $connection->prepare("SELECT Terminal, Type FROM shed WHERE ShedID = ?");
                $shedStmt->execute([$formData['shed_id']]);
                $shedDetails = $shedStmt->fetch();
                $location = $shedDetails ? $shedDetails['Terminal'] . ' (' . $shedDetails['Type'] . ')' : '';
                
                $currentUser = SessionManager::getCurrentUser();
                $complaintData = [
                    'complaint_type' => $categoryMapping['Type'],
                    'complaint_subtype' => $categoryMapping['SubType'],
                    'category' => $categoryMapping['Category'],
                    'location' => $location,
                    'shed_id' => $formData['shed_id'],
                    'fnr_no' => $formData['fnr_no'],
                    'description' => $formData['description'],
                    'customer_id' => $currentUser['customer_id'],
                    'department' => 'COMMERCIAL'
                    // priority will be set to 'normal' by default in createComplaint()
                ];
                
                $complaintId = $complaintModel->createComplaint($complaintData);
                
                if ($complaintId) {
                    if (!empty($_FILES['evidence']['tmp_name'][0])) {
                        $uploadResult = $evidenceModel->handleFileUpload($_FILES['evidence'], $complaintId);
                        if (!$uploadResult['success'] && !empty($uploadResult['errors'])) {
                             return ['Grievance submitted but some files failed to upload: ' . implode(', ', $uploadResult['errors']), ''];
                        }
                    }
                    
                    $transactionModel->logStatusUpdate($complaintId, 'Grievance submitted by customer. Assigned to Commercial Controller for review.', $currentUser['login_id']);
                    
                    $this->sendConfirmationEmail($currentUser, $complaintId, $complaintData);
                    
                    $_SESSION['alert_message'] = "Grievance submitted successfully! Your grievance ID is: $complaintId";
                    $_SESSION['alert_type'] = 'success';
                    $this->redirect('complaints/new');
                } else {
                    return ['Failed to submit grievance. Please try again.', ''];
                }
            } else {
                return [implode('<br>', $errors), ''];
            }
        } catch (Exception $e) {
            error_log('Grievance submission error: ' . $e->getMessage());
            return ['An error occurred while submitting your grievance. Please try again or contact support if the issue persists.', ''];
        }
        return ['', ''];
    }

    private function determineCategoryFromTypeAndSubtype($complaintType, $complaintSubtype, $connection) {
        try {
            $stmt = $connection->prepare("SELECT Category, Type, SubType FROM complaint_categories WHERE Type = ? AND SubType = ? LIMIT 1");
            $stmt->execute([$complaintType, $complaintSubtype]);
            $result = $stmt->fetch();
            if ($result) return $result;
            
            $stmt = $connection->prepare("SELECT Category, Type, SubType FROM complaint_categories WHERE Type = ? OR SubType = ? LIMIT 1");
            $stmt->execute([$complaintType, $complaintSubtype]);
            $result = $stmt->fetch();
            if ($result) {
                return ['Category' => $result['Category'], 'Type' => $complaintType, 'SubType' => $complaintSubtype];
            }
            return false;
        } catch (Exception $e) {
            error_log('Error determining category: ' . $e->getMessage());
            return false;
        }
    }

    private function sendConfirmationEmail($currentUser, $complaintId, $complaintData) {
        try {
            require_once __DIR__ . '/../utils/EmailService.php';
            $emailService = new EmailService();
            $customerEmail = $currentUser['email'] ?? '';
            $customerName = $currentUser['name'] ?? 'Valued Customer';
            
            if ($customerEmail && EmailService::isValidEmail($customerEmail)) {
                $emailSent = $emailService->sendComplaintConfirmation($customerEmail, $customerName, $complaintId, $complaintData);
                
                if (!$emailSent) {
                    error_log("Failed to send confirmation email for complaint ID: $complaintId to: $customerEmail");
                    // Don't fail the entire process if email fails
                }
            }
        } catch (Exception $e) {
            error_log('Email sending error: ' . $e->getMessage());
            // Don't fail the entire process if email fails
        }
    }
}
