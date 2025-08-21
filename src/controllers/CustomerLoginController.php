<?php

require_once __DIR__ . '/../utils/SessionManager.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../utils/Logger.php';

class CustomerLoginController
{
    private $customerModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
    }

    public function handleLoginRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $customerId = sanitizeInput($_POST['customer_id'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($customerId) || empty($password)) {
            SessionManager::setAlert('Please enter both Customer ID and password.', 'danger');
            header('Location: ' . BASE_URL . 'customer-login');
            exit;
        }

        try {
            $customer = $this->customerModel->authenticateCustomer($customerId, $password);

            if ($customer) {
                Logger::logAuth('CUSTOMER_LOGIN', $customerId, true, ['customer_name' => $customer['Name']]);
                SessionManager::loginCustomer($customer);
                header('Location: ' . BASE_URL . 'customer-home');
                exit;
            } else {
                Logger::logAuth('CUSTOMER_LOGIN', $customerId, false, ['error' => 'Invalid credentials']);
                SessionManager::setAlert('Invalid Customer ID or password. Please try again.', 'danger');
            }
        } catch (Exception $e) {
            Logger::logAuth('CUSTOMER_LOGIN', $customerId, false, ['error' => $e->getMessage()]);
            SessionManager::setAlert('Login failed. Please try again later.', 'danger');
        }

        // Store customerId in session to repopulate the form
        $_SESSION['form_data'] = ['customer_id' => $customerId];

        header('Location: ' . BASE_URL . 'customer-login');
        exit;
    }
}
