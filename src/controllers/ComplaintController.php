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
        $grievances = !empty($search) ? $complaintModel->search($search, $filters) : $complaintModel->search('', $filters);

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
        $this->loadView('header', ['pageTitle' => 'Assigned to Me']);
        // Legacy page with embedded logic
        require_once __DIR__ . '/../../public/pages/complaints_to_me.php';
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

        $images = $evidenceModel->getImages($complaintId);
        $history = $transactionModel->getComplaintHistory($complaintId);
        $rejections = $rejectionModel->findByComplaintId($complaintId);

        $data = compact('complaint', 'images', 'history', 'rejections', 'currentUser');
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
        require_once __DIR__ . '/../../public/pages/complaint_form.php';
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
                    'department' => 'COMMERCIAL',
                    'priority' => 'medium'
                ];
                
                $complaintId = $complaintModel->createComplaint($complaintData);
                
                if ($complaintId) {
                    if (!empty($_FILES['evidence']['tmp_name'][0])) {
                        $uploadResult = $evidenceModel->handleFileUpload($_FILES['evidence'], $complaintId);
                        if (!$uploadResult['success'] && !empty($uploadResult['errors'])) {
                             return ['Grievance submitted but some files failed to upload: ' . implode(', ', $uploadResult['errors']), ''];
                        }
                    }
                    
                    $transactionModel->logStatusUpdate($complaintId, 'Grievance submitted by customer. Assigned to Commercial Department for review.', $currentUser['login_id']);
                    
                    $this->sendConfirmationEmail($currentUser, $complaintId, $complaintData);
                    
                    $_SESSION['alert_message'] = "Grievance submitted successfully! Your grievance ID is: <strong>$complaintId</strong>";
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
