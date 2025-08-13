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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            'login_id' => sanitizeInput($_POST['login_id'] ?? ''),
            'name' => sanitizeInput($_POST['name'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'mobile' => sanitizeInput($_POST['mobile'] ?? ''),
            'company_name' => sanitizeInput($_POST['company_name'] ?? '')
        ];
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = $this->validateCustomerData($formData, $password, $confirmPassword);

        if (empty($errors)) {
            $customerModel = $this->loadModel('Customer');
            $userModel = $this->loadModel('User');

            if ($userModel->loginIdExists($formData['login_id'])) {
                return ['Login ID already exists. Please choose a different one.', ''];
            }
            if ($customerModel->isDuplicateCustomer($formData['name'], $formData['company_name'])) {
                return ['A customer with the same name and company already exists in the system.', ''];
            }

            $db = Database::getInstance();
            $connection = $db->getConnection();
            $connection->beginTransaction();

            try {
                $customerData = [
                    'Name' => $formData['name'],
                    'Email' => $formData['email'],
                    'MobileNumber' => $formData['mobile'],
                    'CompanyName' => $formData['company_name']
                ];
                $generatedCustomerId = $customerModel->createCustomer($customerData);

                if (!$generatedCustomerId) throw new Exception('Failed to create customer record');

                $userData = [
                    'login_id' => $formData['login_id'],
                    'name' => $formData['name'],
                    'email' => $formData['email'],
                    'mobile' => $formData['mobile'],
                    'password' => $password,
                    'role' => 'customer',
                    'department' => 'COMMERCIAL',
                    'customer_id' => $generatedCustomerId,
                    'status' => 'active'
                ];
                $userResult = $userModel->createUser($userData);

                if (!$userResult) throw new Exception('Failed to create user account');

                $connection->commit();

                $currentUser = SessionManager::getCurrentUser();
                Logger::logUserAction('NEW_CUSTOMER_CREATED', $currentUser['login_id'], [
                    'customer_id' => $generatedCustomerId,
                    'customer_name' => $formData['name']
                ]);

                return ['', "Customer created successfully!"];

            } catch (Exception $e) {
                $connection->rollBack();
                error_log('Add customer error: ' . $e->getMessage());
                return ['Customer creation failed: ' . $e->getMessage(), ''];
            }
        }
        return [implode('<br>', $errors), ''];
    }

    private function validateCustomerData($formData, $password, $confirmPassword) {
        $errors = [];
        if (empty($formData['login_id']) || strlen($formData['login_id']) < 3) $errors[] = 'Login ID must be at least 3 characters long';
        if (empty($formData['name'])) $errors[] = 'Customer name is required';
        if (empty($formData['company_name'])) $errors[] = 'Company name is required';
        if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
        if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
        if (!empty($formData['mobile']) && !preg_match('/^[0-9]{10}$/', $formData['mobile'])) $errors[] = 'Mobile number must be 10 digits';
        return $errors;
    }
}
