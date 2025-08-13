<?php
/**
 * Registration Page (Admin Only)
 * User registration page - restricted to admin users
 */

require_once '../src/utils/SessionManager.php';

// Require admin access
SessionManager::requireRole('admin');

$error = '';
$success = '';
$formData = [
    'login_id' => '',
    'name' => '',
    'email' => '',
    'mobile' => '',
    'role' => 'customer',
    'department' => '',
    'customer_id' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formData['login_id'] = sanitizeInput($_POST['login_id'] ?? '');
        $formData['name'] = sanitizeInput($_POST['name'] ?? '');
        $formData['email'] = sanitizeInput($_POST['email'] ?? '');
        $formData['mobile'] = sanitizeInput($_POST['mobile'] ?? '');
        $formData['role'] = sanitizeInput($_POST['role'] ?? '');
        $formData['department'] = sanitizeInput($_POST['department'] ?? '');
        $formData['customer_id'] = sanitizeInput($_POST['customer_id'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($formData['login_id'])) {
            $errors[] = 'Login ID is required';
        }
        
        if (empty($formData['name'])) {
            $errors[] = 'Name is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if ($formData['role'] === 'customer' && empty($formData['customer_id'])) {
            $errors[] = 'Customer ID is required for customer role';
        }
        
        if (in_array($formData['role'], ['controller', 'viewer']) && empty($formData['department'])) {
            $errors[] = 'Department is required for controller and viewer roles';
        }
        
        if (empty($errors)) {
            require_once '../src/models/User.php';
            $userModel = new User();
            
            // Check if login ID already exists
            if ($userModel->loginIdExists($formData['login_id'])) {
                $error = 'Login ID already exists. Please choose a different one.';
            } else {
                // Prepare user data
                $userData = $formData;
                $userData['password'] = $password;
                $userData['status'] = 'active';
                
                if ($formData['role'] !== 'customer') {
                    $userData['customer_id'] = null;
                }
                
                if (!in_array($formData['role'], ['controller', 'viewer'])) {
                    $userData['department'] = $formData['role'] === 'admin' ? 'SYSTEM' : 'COMMERCIAL';
                }
                
                $result = $userModel->createUser($userData);
                
                if ($result) {
                    $success = 'User registered successfully!';
                    // Clear form data
                    $formData = [
                        'login_id' => '',
                        'name' => '',
                        'email' => '',
                        'mobile' => '',
                        'role' => 'customer',
                        'department' => '',
                        'customer_id' => ''
                    ];
                } else {
                    $error = 'Failed to register user. Please try again.';
                }
            }
        } else {
            $error = implode('<br>', $errors);
        }
        
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        $error = 'Registration failed. Please try again later.';
    }
}

// Get customers for dropdown
$customers = [];
try {
    require_once '../src/models/BaseModel.php';
    $db = Database::getInstance();
    $connection = $db->getConnection();
    $stmt = $connection->prepare("SELECT CustomerID, Name, CompanyName FROM customers ORDER BY Name ASC LIMIT 100");
    $stmt->execute();
    $customers = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error silently
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0 rounded-lg mt-4">
                <div class="card-header">
                    <div class="text-center">
                        <h3 class="fw-bold text-primary">
                            <i class="fas fa-user-plus"></i> User Registration
                        </h3>
                        <p class="text-muted">Add new users to the grievance system</p>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>register" id="registrationForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="login_id" name="login_id" 
                                           placeholder="Login ID" value="<?php echo htmlspecialchars($formData['login_id']); ?>" required>
                                    <label for="login_id">
                                        <i class="fas fa-user"></i> Login ID *
                                    </label>
                                    <div class="form-text">Unique identifier for user login</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name" 
                                           placeholder="Full Name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                                    <label for="name">
                                        <i class="fas fa-id-card"></i> Full Name *
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Email" value="<?php echo htmlspecialchars($formData['email']); ?>">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Email Address
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control" id="mobile" name="mobile" 
                                           placeholder="Mobile" value="<?php echo htmlspecialchars($formData['mobile']); ?>">
                                    <label for="mobile">
                                        <i class="fas fa-phone"></i> Mobile Number
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="customer" <?php echo $formData['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                        <option value="controller" <?php echo $formData['role'] === 'controller' ? 'selected' : ''; ?>>Controller</option>
                                        <option value="admin" <?php echo $formData['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="viewer" <?php echo $formData['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                                    </select>
                                    <label for="role">
                                        <i class="fas fa-user-tag"></i> Role *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3" id="department_field" style="display: none;">
                                    <select class="form-select" id="department" name="department">
                                        <option value="">Select Department</option>
                                        <option value="COMMERCIAL" <?php echo $formData['department'] === 'COMMERCIAL' ? 'selected' : ''; ?>>Commercial</option>
                                        <option value="OPERATING" <?php echo $formData['department'] === 'OPERATING' ? 'selected' : ''; ?>>Operating</option>
                                        <option value="MECHANICAL" <?php echo $formData['department'] === 'MECHANICAL' ? 'selected' : ''; ?>>Mechanical</option>
                                        <option value="ELECTRICAL" <?php echo $formData['department'] === 'ELECTRICAL' ? 'selected' : ''; ?>>Electrical</option>
                                        <option value="ENGINEERING" <?php echo $formData['department'] === 'ENGINEERING' ? 'selected' : ''; ?>>Engineering</option>
                                        <option value="SECURITY" <?php echo $formData['department'] === 'SECURITY' ? 'selected' : ''; ?>>Security</option>
                                        <option value="MEDICAL" <?php echo $formData['department'] === 'MEDICAL' ? 'selected' : ''; ?>>Medical</option>
                                        <option value="ACCOUNTS" <?php echo $formData['department'] === 'ACCOUNTS' ? 'selected' : ''; ?>>Accounts</option>
                                        <option value="PERSONNEL" <?php echo $formData['department'] === 'PERSONNEL' ? 'selected' : ''; ?>>Personnel</option>
                                    </select>
                                    <label for="department">
                                        <i class="fas fa-building"></i> Department
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3" id="customer_field" style="display: none;">
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo htmlspecialchars($customer['CustomerID']); ?>" 
                                            <?php echo $formData['customer_id'] === $customer['CustomerID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['CustomerID'] . ' - ' . $customer['Name'] . ' (' . $customer['CompanyName'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="customer_id">
                                <i class="fas fa-industry"></i> Customer *
                            </label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Password" required>
                                    <label for="password">
                                        <i class="fas fa-lock"></i> Password *
                                    </label>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm Password" required>
                                    <label for="confirm_password">
                                        <i class="fas fa-lock"></i> Confirm Password *
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-railway-primary btn-lg w-100">
                                    <i class="fas fa-user-plus"></i> Register User
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-railway-secondary btn-lg w-100">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Role Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Role Descriptions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Customer</h6>
                            <ul class="small">
                                <li>Submit grievances</li>
                                <li>Track grievance status</li>
                                <li>Provide feedback</li>
                                <li>View grievance history</li>
                            </ul>
                            
                            <h6 class="text-primary">Controller</h6>
                            <ul class="small">
                                <li>Solve grievances</li>
                                <li>Forward to departments</li>
                                <li>View department grievances</li>
                                <li>Add internal remarks</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Admin</h6>
                            <ul class="small">
                                <li>View grievances and remarks</li>
                                <li>Manage users</li>
                                <li>Modify categories/types</li>
                                <li>Generate reports</li>
                            </ul>
                            
                            <h6 class="text-primary">Viewer</h6>
                            <ul class="small">
                                <li>View grievances and remarks</li>
                                <li>Generate reports</li>
                                <li>Read-only access</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const departmentField = document.getElementById('department_field');
    const customerField = document.getElementById('customer_field');
    const departmentSelect = document.getElementById('department');
    const customerSelect = document.getElementById('customer_id');
    
    function toggleFields() {
        const role = roleSelect.value;
        
        if (role === 'customer') {
            customerField.style.display = 'block';
            departmentField.style.display = 'none';
            customerSelect.required = true;
            departmentSelect.required = false;
        } else if (role === 'controller' || role === 'viewer') {
            customerField.style.display = 'none';
            departmentField.style.display = 'block';
            customerSelect.required = false;
            departmentSelect.required = true;
        } else {
            customerField.style.display = 'none';
            departmentField.style.display = 'none';
            customerSelect.required = false;
            departmentSelect.required = false;
        }
    }
    
    roleSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial call
    
    // Password strength indicator
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    function validatePasswords() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        if (password !== confirmPassword && confirmPassword.length > 0) {
            confirmPasswordField.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    }
    
    passwordField.addEventListener('input', validatePasswords);
    confirmPasswordField.addEventListener('input', validatePasswords);
    
    // Form submission handling
    const registrationForm = document.getElementById('registrationForm');
    const submitButton = registrationForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    registrationForm.addEventListener('submit', function() {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Registering...';
        
        // Re-enable after 5 seconds in case of issues
        setTimeout(function() {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }, 5000);
    });
});
</script>
