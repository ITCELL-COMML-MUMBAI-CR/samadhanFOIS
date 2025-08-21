<?php
/**
 * Session Manager
 * Handles secure session management with timeout
 */

class SessionManager {
    
    /**
     * Start secure session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            
            session_start();
            
            // Check for session timeout
            self::checkTimeout();
            
            // Regenerate session ID periodically for security
            self::regenerateIfNeeded();
        }
    }
    
    /**
     * Check session timeout
     */
    private static function checkTimeout() {
        $timeout = SESSION_LIFETIME; // From config
        
        if (isset($_SESSION['last_activity'])) {
            $timeSinceLastActivity = time() - $_SESSION['last_activity'];
            
            if ($timeSinceLastActivity > $timeout) {
                self::destroy();
                // Redirect to login with timeout message
                if (!headers_sent()) {
                    header('Location: ' . BASE_URL . 'login?timeout=1');
                    exit;
                }
            }
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Regenerate session ID if needed
     */
    private static function regenerateIfNeeded() {
        $regenerateInterval = 1800; // 30 minutes
        
        if (!isset($_SESSION['created_at'])) {
            $_SESSION['created_at'] = time();
        } else if (time() - $_SESSION['created_at'] > $regenerateInterval) {
            session_regenerate_id(true);
            $_SESSION['created_at'] = time();
        }
    }
    
    /**
     * Login user
     */
    public static function login($user) {
        self::start();
        
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_login_id'] = $user['login_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_department'] = $user['department'];
        $_SESSION['user_customer_id'] = $user['customer_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Set alert message
        self::setAlert('Welcome, ' . $user['name'] . '!', 'success');
    }
    
    /**
     * Login customer
     */
    public static function loginCustomer($customer) {
        self::start();
        
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_type'] = 'customer';
        $_SESSION['user_customer_id'] = $customer['CustomerID'];
        $_SESSION['user_name'] = $customer['Name'];
        $_SESSION['user_email'] = $customer['Email'];
        $_SESSION['user_company'] = $customer['CompanyName'];
        $_SESSION['user_designation'] = $customer['Designation'];
        $_SESSION['user_role'] = 'customer';
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Set alert message
        self::setAlert('Welcome, ' . $customer['Name'] . '!', 'success');
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        self::start();
        
        $userId = $_SESSION['user_login_id'] ?? $_SESSION['user_customer_id'] ?? 'unknown';
        $isCustomer = self::isCustomer();
        
        // Log the logout action
        if (class_exists('Logger')) {
            Logger::logAuth('LOGOUT', $userId, true);
        }
        
        // Clear all session data
        session_unset();
        session_destroy();
        
        // Start new session for guest
        session_start();
        self::setAlert('You have been logged out successfully.', 'info');
        
        // Redirect based on user type
        if ($isCustomer) {
            header('Location: ' . BASE_URL . 'customer-login');
        } else {
            header('Location: ' . BASE_URL . 'login');
        }
        exit;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }
    
    /**
     * Get current user data
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'login_id' => $_SESSION['user_login_id'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role'],
            'department' => $_SESSION['user_department'],
            'customer_id' => $_SESSION['user_customer_id'],
            'email' => $_SESSION['user_email']
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_role'] === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole($roles) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        return in_array($_SESSION['user_role'], $roles);
    }
    
    /**
     * Check if user belongs to specific department
     */
    public static function hasDepartment($department) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_department'] === $department;
    }
    
    /**
     * Check if current user is a customer
     */
    public static function isCustomer() {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
    }
    
    /**
     * Get current customer data
     */
    public static function getCurrentCustomer() {
        if (!self::isCustomer()) {
            return null;
        }
        
        return [
            'customer_id' => $_SESSION['user_customer_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'company' => $_SESSION['user_company'],
            'designation' => $_SESSION['user_designation'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        }
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role) {
        self::requireLogin();
        
        if (!self::hasRole($role)) {
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'dashboard?error=access_denied');
                exit;
            }
        }
    }
    
    /**
     * Require any of the specified roles
     */
    public static function requireAnyRole($roles) {
        self::requireLogin();
        
        if (!self::hasAnyRole($roles)) {
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'dashboard?error=access_denied');
                exit;
            }
        }
    }
    
    /**
     * Set alert message
     */
    public static function setAlert($message, $type = 'info') {
        self::start();
        $_SESSION['alert_message'] = $message;
        $_SESSION['alert_type'] = $type;
    }
    
    /**
     * Get and clear alert message
     */
    public static function getAlert() {
        self::start();
        
        $alert = null;
        if (isset($_SESSION['alert_message'])) {
            $alert = [
                'message' => $_SESSION['alert_message'],
                'type' => $_SESSION['alert_type'] ?? 'info'
            ];
            
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
        }
        
        return $alert;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        self::start();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        self::start();
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
    
    /**
     * Get session info for debugging
     */
    public static function getSessionInfo() {
        self::start();
        
        return [
            'logged_in' => self::isLoggedIn(),
            'session_id' => session_id(),
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'created_at' => $_SESSION['created_at'] ?? null,
            'time_remaining' => isset($_SESSION['last_activity']) ? 
                (SESSION_LIFETIME - (time() - $_SESSION['last_activity'])) : null,
            'user_role' => $_SESSION['user_role'] ?? null,
            'user_department' => $_SESSION['user_department'] ?? null
        ];
    }
    
    /**
     * Extend session (reset timeout)
     */
    public static function extendSession() {
        self::start();
        $_SESSION['last_activity'] = time();
    }
}
?>
