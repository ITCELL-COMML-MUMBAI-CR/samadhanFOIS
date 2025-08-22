<?php
require_once 'BaseController.php';

class CustomerAuthController extends BaseController {
    
    /**
     * Handle customer authentication for support ticket form
     */
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            return;
        }
        
        $customerModel = $this->loadModel('Customer');
        $customer = $customerModel->authenticateCustomerByEmailOrMobile($email, $password);
        
        if ($customer) {
            // Store customer session
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $customer['CustomerID'];
            $_SESSION['customer_name'] = $customer['Name'];
            $_SESSION['customer_email'] = $customer['Email'];
            $_SESSION['customer_company'] = $customer['CompanyName'];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Authentication successful',
                'customer' => [
                    'id' => $customer['CustomerID'],
                    'name' => $customer['Name'],
                    'email' => $customer['Email'],
                    'company' => $customer['CompanyName']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    }
    
    /**
     * Logout customer
     */
    public function logout() {
        unset($_SESSION['customer_logged_in']);
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_company']);
        
        header('Location: ' . BASE_URL);
        exit;
    }
}
?>
