<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class CustomerController extends BaseController {

    public function __construct() {
        SessionManager::requireRole('admin');
    }

    public function add() {
        $error = '';
        $success = '';

        // Check for session alerts (from redirect after successful submission)
        if (isset($_SESSION['alert_message'])) {
            if ($_SESSION['alert_type'] === 'success') {
                $success = $_SESSION['alert_message'];
            } else {
                $error = $_SESSION['alert_message'];
            }
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            list($error, $success) = $this->handleAddCustomerRequest();
        }

        $data = [
            'pageTitle' => 'Add Customer',
            'error' => $error,
            'success' => $success
        ];

        $this->loadView('header', $data);
        $this->loadView('pages/add_customer', $data);
        $this->loadView('footer');
    }

    private function handleAddCustomerRequest() {
        $formData = [
            'Name' => sanitizeInput($_POST['Name'] ?? ''),
            'Email' => sanitizeInput($_POST['Email'] ?? ''),
            'MobileNumber' => sanitizeInput($_POST['MobileNumber'] ?? ''),
            'CompanyName' => sanitizeInput($_POST['CompanyName'] ?? ''),
            'Designation' => sanitizeInput($_POST['Designation'] ?? '')
        ];
        $password = $_POST['Password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = $this->validateCustomerData($formData, $password, $confirmPassword);

        if (empty($errors)) {
            $customerModel = $this->loadModel('Customer');

            // Check for duplicate email or mobile
            if ($customerModel->findByEmail($formData['Email'])) {
                return ['A customer with this email address already exists.', ''];
            }
            
            if ($customerModel->findByMobile($formData['MobileNumber'])) {
                return ['A customer with this mobile number already exists.', ''];
            }
            
            if ($customerModel->isDuplicateCustomer($formData['Name'], $formData['CompanyName'])) {
                return ['A customer with the same name and company already exists in the system.', ''];
            }

            try {
                // Add password to customer data
                $customerData = $formData;
                $customerData['Password'] = $password;
                
                $generatedCustomerId = $customerModel->createCustomer($customerData);

                if (!$generatedCustomerId) {
                    throw new Exception('Failed to create customer record');
                }

                $currentUser = SessionManager::getCurrentUser();
                Logger::logUserAction('NEW_CUSTOMER_CREATED', $currentUser['login_id'], [
                    'customer_id' => $generatedCustomerId,
                    'customer_name' => $formData['Name']
                ]);

                // Set success message in session and redirect to prevent resubmission
                $_SESSION['alert_message'] = "Customer created successfully! Customer ID: " . $generatedCustomerId;
                $_SESSION['alert_type'] = 'success';
                $this->redirect('customer/add');

            } catch (Exception $e) {
                error_log('Add customer error: ' . $e->getMessage());
                return ['Customer creation failed: ' . $e->getMessage(), ''];
            }
        }
        return [implode('<br>', $errors), ''];
    }

    private function validateCustomerData($formData, $password, $confirmPassword) {
        $errors = [];
        
        if (empty($formData['Name'])) {
            $errors[] = 'Customer name is required';
        }
        
        if (empty($formData['CompanyName'])) {
            $errors[] = 'Company name is required';
        }
        
        if (empty($formData['Email'])) {
            $errors[] = 'Email address is required';
        } elseif (!filter_var($formData['Email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($formData['MobileNumber'])) {
            $errors[] = 'Mobile number is required';
        } elseif (!preg_match('/^[0-9]{10}$/', $formData['MobileNumber'])) {
            $errors[] = 'Mobile number must be exactly 10 digits';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        return $errors;
    }
}