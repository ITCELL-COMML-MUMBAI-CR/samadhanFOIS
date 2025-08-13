<?php
/**
 * Login Page
 * Authentication page for the grievance system
 */

require_once __DIR__ . '/../../src/controllers/LoginController.php';
require_once __DIR__ . '/../../src/utils/SessionManager.php';

// If already logged in, redirect to dashboard
if (SessionManager::isLoggedIn()) {
    header('Location: ' . BASE_URL . 'dashboard');
    exit;
}

$loginController = new LoginController();
$loginController->handleLoginRequest();

$loginId = $_SESSION['form_data']['login_id'] ?? '';
unset($_SESSION['form_data']);

// Check for timeout message
if (isset($_GET['timeout'])) {
    SessionManager::setAlert('Your session has expired. Please login again.', 'danger');
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <div class="text-center">
                        <i class="fas fa-train fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold text-primary">SAMPARK</h3>
                        <p class="text-muted">Grievance Management System</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>login" id="loginForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="login_id" name="login_id" 
                                   placeholder="Login ID" value="<?php echo htmlspecialchars($loginId); ?>" required>
                            <label for="login_id">
                                <i class="fas fa-user"></i> Login ID
                            </label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Use default credentials: admin/admin123 or commercial_controller/commercial123
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me">
                                Remember me on this device
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-railway-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">
                        <p class="mb-1">
                            <i class="fas fa-question-circle"></i> 
                            Need help? Contact: 
                            <a href="mailto:support@cr.railnet.gov.in">support@cr.railnet.gov.in</a>
                        </p>
                        <p class="mb-0 text-muted">
                            For new user registration, contact your administrator
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Quick Access Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">For Customers:</h6>
                            <ul class="small">
                                <li>Submit new grievances</li>
                                <li>Track grievance status</li>
                                <li>Provide feedback</li>
                                <li>View grievance history</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">For Railway Staff:</h6>
                            <ul class="small">
                                <li>Handle assigned grievances</li>
                                <li>Forward to departments</li>
                                <li>Update status & remarks</li>
                                <li>Generate reports</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt text-success"></i> 
                    System Status: Online | 
                    <i class="fas fa-clock"></i> 
                    Server Time: <?php echo date('d-M-Y H:i:s'); ?> IST
                </small>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/login.css">
<script src="<?php echo BASE_URL; ?>js/login.js"></script>
