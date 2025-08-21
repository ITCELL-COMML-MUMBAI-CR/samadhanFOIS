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

        $loginIdentifier = sanitizeInput($_POST['login_identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($loginIdentifier) || empty($password)) {
            SessionManager::setAlert('Please enter both email/mobile and password.', 'danger');
            header('Location: ' . BASE_URL . 'customer-login');
            exit;
        }

        try {
            $customer = $this->customerModel->authenticateCustomerByEmailOrMobile($loginIdentifier, $password);

            if ($customer) {
                Logger::logAuth('CUSTOMER_LOGIN', $customer['CustomerID'], true, ['customer_name' => $customer['Name']]);
                SessionManager::loginCustomer($customer);
                header('Location: ' . BASE_URL . 'customer-home');
                exit;
            } else {
                Logger::logAuth('CUSTOMER_LOGIN', $loginIdentifier, false, ['error' => 'Invalid credentials']);
                SessionManager::setAlert('Invalid email/mobile or password. Please try again.', 'danger');
            }
        } catch (Exception $e) {
            Logger::logAuth('CUSTOMER_LOGIN', $loginIdentifier, false, ['error' => $e->getMessage()]);
            SessionManager::setAlert('Login failed. Please try again later.', 'danger');
        }

        // Store loginIdentifier in session to repopulate the form
        $_SESSION['form_data'] = ['login_identifier' => $loginIdentifier];

        header('Location: ' . BASE_URL . 'customer-login');
        exit;
    }
}
