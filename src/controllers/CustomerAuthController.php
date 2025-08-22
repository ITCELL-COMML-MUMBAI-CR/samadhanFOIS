<?php

require_once __DIR__ . '/../utils/SessionManager.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/Helpers.php';

class CustomerAuthController
{
    private $customerModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
    }

    public function handleAuthRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("CustomerAuthController: Not a POST request");
            return false;
        }

        $loginIdentifier = sanitizeInput($_POST['login_identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        error_log("CustomerAuthController: login_identifier = " . $loginIdentifier);
        error_log("CustomerAuthController: password provided = " . (!empty($password) ? 'YES' : 'NO'));

        if (empty($loginIdentifier) || empty($password)) {
            error_log("CustomerAuthController: Empty login_identifier or password");
            SessionManager::setAlert('Please enter both email/mobile and password.', 'danger');
            return false;
        }

        try {
            error_log("CustomerAuthController: Attempting authentication for: " . $loginIdentifier);
            $customer = $this->customerModel->authenticateCustomerByEmailOrMobile($loginIdentifier, $password);

            if ($customer) {
                error_log("CustomerAuthController: Authentication SUCCESS for customer: " . $customer['CustomerID']);
                Logger::logAuth('CUSTOMER_AUTH', $customer['CustomerID'], true, ['customer_name' => $customer['Name']]);
                SessionManager::loginCustomer($customer);
                return true;
            } else {
                error_log("CustomerAuthController: Authentication FAILED - Invalid credentials");
                Logger::logAuth('CUSTOMER_AUTH', $loginIdentifier, false, ['error' => 'Invalid credentials']);
                SessionManager::setAlert('Invalid email/mobile or password. Please try again.', 'danger');
                return false;
            }
        } catch (Exception $e) {
            error_log("CustomerAuthController: Authentication EXCEPTION: " . $e->getMessage());
            Logger::logAuth('CUSTOMER_AUTH', $loginIdentifier, false, ['error' => $e->getMessage()]);
            SessionManager::setAlert('Authentication failed. Please try again later.', 'danger');
            return false;
        }
    }
}
