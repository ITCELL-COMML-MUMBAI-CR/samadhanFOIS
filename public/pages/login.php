<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>css/login.css" rel="stylesheet">
</head>
<body>

<?php
/**
 * Standalone Login Page
 * Authentication page for the grievance system
 */

require_once __DIR__ . '/../../src/controllers/LoginController.php';
require_once __DIR__ . '/../../src/utils/SessionManager.php';

$loginController = new LoginController();
$loginController->handleLoginRequest();

$loginId = $_SESSION['form_data']['login_id'] ?? '';
unset($_SESSION['form_data']);

// Check for timeout message
if (isset($_GET['timeout'])) {
    SessionManager::setAlert('Your session has expired. Please login again.', 'danger');
}
?>

<!-- Alert Messages -->
<?php if (isset($_SESSION['alert_message'])): ?>
    <div class="alert-container">
        <div class="alert alert-<?php echo $_SESSION['alert_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['alert_message']);
            unset($_SESSION['alert_message'], $_SESSION['alert_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Animated Background Icons -->
<div class="freight-icons-background">
    <!-- Trucks -->
    <div class="freight-icon freight-icon-1">
        <i class="fas fa-truck"></i>
    </div>
    <div class="freight-icon freight-icon-2">
        <i class="fas fa-truck-loading"></i>
    </div>
    
    <!-- Trains & Railways -->
    <div class="freight-icon freight-icon-3">
        <i class="fas fa-train"></i>
    </div>
    <div class="freight-icon freight-icon-4">
        <i class="fas fa-subway"></i>
    </div>
    
    <!-- Containers & Boxes -->
    <div class="freight-icon freight-icon-5">
        <i class="fas fa-boxes"></i>
    </div>
    <div class="freight-icon freight-icon-6">
        <i class="fas fa-box"></i>
    </div>
    <div class="freight-icon freight-icon-7">
        <i class="fas fa-archive"></i>
    </div>
    
    <!-- Ships & Maritime -->
    <div class="freight-icon freight-icon-8">
        <i class="fas fa-ship"></i>
    </div>
    <div class="freight-icon freight-icon-9">
        <i class="fas fa-anchor"></i>
    </div>
    
    <!-- Warehouse & Storage -->
    <div class="freight-icon freight-icon-10">
        <i class="fas fa-warehouse"></i>
    </div>
    <div class="freight-icon freight-icon-11">
        <i class="fas fa-industry"></i>
    </div>
    
    <!-- Material Handling -->
    <div class="freight-icon freight-icon-12">
        <i class="fas fa-forklift"></i>
    </div>
    <div class="freight-icon freight-icon-13">
        <i class="fas fa-tools"></i>
    </div>
    
    <!-- Additional Freight Icons -->
    <div class="freight-icon freight-icon-14">
        <i class="fas fa-dolly"></i>
    </div>
    <div class="freight-icon freight-icon-15">
        <i class="fas fa-pallet"></i>
    </div>
    <div class="freight-icon freight-icon-16">
        <i class="fas fa-shipping-fast"></i>
    </div>
    <div class="freight-icon freight-icon-17">
        <i class="fas fa-route"></i>
    </div>
    <div class="freight-icon freight-icon-18">
        <i class="fas fa-map-marked-alt"></i>
    </div>
</div>

<!-- Main Login Container -->
<div class="login-container">
    <div class="login-split-screen">
        <!-- Left Side - Image with Glass Effect -->
        <div class="login-image-side">
            <div class="image-container">
                <img src="<?php echo BASE_URL; ?>images/image1.jpg" alt="Railway Freight Services" class="login-background-image">
                <div class="image-glass-overlay">
                    <div class="glass-content">
                        <div class="sampark-logo-container">
                            <img src="<?php echo BASE_URL; ?>images/Icon SAMPARK.png" alt="SAMPARK Logo" class="sampark-welcome-logo">
                        </div>
                        <h1 class="welcome-title">Welcome to</h1>
                        <h2 class="system-title">SAMPARK</h2>
                        <p class="system-subtitle">Support and Mediation Portal for All Rail Cargo</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="login-form-side">
            <div class="login-form-container">
                <div class="login-header">
                    <h3 class="login-title">Sign In</h3>
                    <p class="login-subtitle">Access your SAMPARK account</p>
                </div>
                
                <form method="POST" action="<?php echo BASE_URL; ?>login" id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="login_id" class="form-label">
                            <i class="fas fa-user"></i> Login ID
                        </label>
                        <input type="text" class="form-control" id="login_id" name="login_id" 
                               placeholder="Enter your login ID" value="<?php echo htmlspecialchars($loginId); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-input-container">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle-btn" id="passwordToggle">
                                <i class="fas fa-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <span class="btn-text">Sign In</span>
                        <i class="fas fa-arrow-right btn-icon"></i>
                    </button>
                </form>
                
                <div class="login-footer">
                    <p class="help-text">
                        <i class="fas fa-info-circle"></i>
                        For new user registration, contact your administrator
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Password Toggle Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordToggleIcon = document.getElementById('passwordToggleIcon');

    if (passwordToggle && passwordInput && passwordToggleIcon) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                passwordToggleIcon.classList.remove('fa-eye');
                passwordToggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordToggleIcon.classList.remove('fa-eye-slash');
                passwordToggleIcon.classList.add('fa-eye');
            }
        });
    }
});
</script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>js/login.js"></script>

</body>
</html>
