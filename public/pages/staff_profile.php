<?php
/**
 * Staff Profile Page
 * Allows staff users (admin, controller, viewer) to view and edit their profile information including password
 */

// Ensure user is logged in and is a staff member
if (!SessionManager::isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

$currentUser = SessionManager::getCurrentUser();

// Only allow staff users to access this page
if ($currentUser['role'] === 'customer') {
    header('Location: ' . BASE_URL . 'profile');
    exit;
}

$userDetails = $userDetails ?? null;

// Handle form submission for profile update
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../src/models/User.php';
        
        $userModel = new User();
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            // Update basic profile information
            $updateData = [];
            
            // Validate and sanitize input
            $name = sanitizeInput($_POST['name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $mobile = sanitizeInput($_POST['mobile'] ?? '');
            $department = sanitizeInput($_POST['department'] ?? '');
            $division = sanitizeInput($_POST['division'] ?? '');
            $zone = sanitizeInput($_POST['zone'] ?? '');
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            if (!empty($mobile)) {
                $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
                if (strlen($cleanMobile) !== 10) {
                    $errors[] = 'Mobile number must be exactly 10 digits';
                }
            }
            
            if (empty($errors)) {
                // Update user table
                $userUpdateData = [];
                if (!empty($name)) $userUpdateData['name'] = $name;
                if (!empty($email)) $userUpdateData['email'] = $email;
                if (!empty($mobile)) $userUpdateData['mobile'] = $mobile;
                if (!empty($department)) $userUpdateData['department'] = $department;
                if (!empty($division)) $userUpdateData['Division'] = $division;
                if (!empty($zone)) $userUpdateData['Zone'] = $zone;
                
                if (!empty($userUpdateData)) {
                    $userModel->updateProfile($currentUser['login_id'], $userUpdateData);
                }
                
                $success = 'Profile updated successfully!';
                
                // Refresh user data
                $userDetails = $userModel->findByLoginId($currentUser['login_id']);
                
                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_department'] = $department;
            } else {
                $error = implode(', ', $errors);
            }
        } elseif ($action === 'change_password') {
            // Change password
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            $errors = [];
            
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'New password is required';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters long';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match';
            }
            
            if (empty($errors)) {
                // Verify current password
                $isValidPassword = false;
                
                if ($userDetails) {
                    $isValidPassword = password_verify($currentPassword, $userDetails['password']);
                }
                
                if ($isValidPassword) {
                    // Update password
                    $userModel->updatePassword($currentUser['login_id'], $newPassword);
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Current password is incorrect';
                }
            } else {
                $error = implode(', ', $errors);
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred: ' . $e->getMessage();
    }
}

// Get current data for form
$currentName = $userDetails['name'] ?? $currentUser['name'] ?? '';
$currentEmail = $userDetails['email'] ?? $currentUser['email'] ?? '';
$currentMobile = $userDetails['mobile'] ?? '';
$currentDepartment = $userDetails['department'] ?? $currentUser['department'] ?? '';
$currentDivision = $userDetails['Division'] ?? '';
$currentZone = $userDetails['Zone'] ?? '';
$currentRole = $currentUser['role'] ?? '';
$currentLoginId = $currentUser['login_id'] ?? '';

// Get role display name
$roleDisplayNames = [
    'admin' => 'Administrator',
    'controller' => 'Controller',
    'viewer' => 'Viewer'
];
$roleDisplayName = $roleDisplayNames[$currentRole] ?? $currentRole;
?>

<!-- Alert Messages -->
<?php if (!empty($error)): ?>
    <div class="alert-container">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert-container">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<div class="container-fluid profile-page">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <!-- Page Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="profile-info">
                    <h1 class="profile-title">
                        <i class="fas fa-user-edit"></i>
                        Staff Profile
                    </h1>
                    <p class="profile-subtitle">Manage your account information and security settings</p>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="profile-content">
                <div class="row">
                    <!-- Profile Information Section -->
                    <div class="col-lg-8">
                        <div class="profile-section">
                            <div class="section-header">
                                <h3 class="section-title">
                                    <i class="fas fa-user"></i>
                                    Personal Information
                                </h3>
                                <p class="section-description">Update your personal and contact details</p>
                            </div>
                            
                            <form method="POST" class="profile-form" id="profileForm">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   placeholder="Full Name" value="<?php echo htmlspecialchars($currentName); ?>" required>
                                            <label for="name">
                                                <i class="fas fa-user"></i> Full Name
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Email Address" value="<?php echo htmlspecialchars($currentEmail); ?>">
                                            <label for="email">
                                                <i class="fas fa-envelope"></i> Email Address
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                                   placeholder="Mobile Number" value="<?php echo htmlspecialchars($currentMobile); ?>">
                                            <label for="mobile">
                                                <i class="fas fa-phone"></i> Mobile Number
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="department" name="department" 
                                                   placeholder="Department" value="<?php echo htmlspecialchars($currentDepartment); ?>" readonly>
                                            <label for="department">
                                                <i class="fas fa-building"></i> Department
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="division" name="division" 
                                                   placeholder="Division" value="<?php echo htmlspecialchars($currentDivision); ?>" readonly>
                                            <label for="division">
                                                <i class="fas fa-map-marker-alt"></i> Division
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="zone" name="zone" 
                                                   placeholder="Zone" value="<?php echo htmlspecialchars($currentZone); ?>" readonly>
                                            <label for="zone">
                                                <i class="fas fa-globe"></i> Zone
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="login_id" 
                                                   placeholder="Login ID" value="<?php echo htmlspecialchars($currentLoginId); ?>" readonly>
                                            <label for="login_id">
                                                <i class="fas fa-id-card"></i> Login ID
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="role" 
                                                   placeholder="Role" value="<?php echo htmlspecialchars($roleDisplayName); ?>" readonly>
                                            <label for="role">
                                                <i class="fas fa-user-tag"></i> Role
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i>
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Password Change Section -->
                        <div class="profile-section">
                            <div class="section-header">
                                <h3 class="section-title">
                                    <i class="fas fa-lock"></i>
                                    Change Password
                                </h3>
                                <p class="section-description">Update your password to keep your account secure</p>
                            </div>
                            
                            <form method="POST" class="password-form" id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                                   placeholder="Current Password" required>
                                            <label for="current_password">
                                                <i class="fas fa-key"></i> Current Password
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                                   placeholder="New Password" required>
                                            <label for="new_password">
                                                <i class="fas fa-lock"></i> New Password
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Confirm New Password" required>
                                            <label for="confirm_password">
                                                <i class="fas fa-lock"></i> Confirm New Password
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="password-requirements">
                                    <h6><i class="fas fa-info-circle"></i> Password Requirements:</h6>
                                    <ul class="requirements-list">
                                        <li id="length-check">At least 6 characters long</li>
                                        <li id="match-check">Passwords must match</li>
                                    </ul>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-key"></i>
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Profile Summary Sidebar -->
                    <div class="col-lg-4">
                        <div class="profile-sidebar">
                            <div class="sidebar-section">
                                <h4 class="sidebar-title">
                                    <i class="fas fa-info-circle"></i>
                                    Account Summary
                                </h4>
                                <div class="account-info">
                                    <div class="info-item">
                                        <span class="info-label">Account Type:</span>
                                        <span class="info-value">Staff</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Role:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($roleDisplayName); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Department:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($currentDepartment ?: 'N/A'); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Last Updated:</span>
                                        <span class="info-value">
                                            <?php 
                                            if ($userDetails && !empty($userDetails['updated_at'])) {
                                                echo date('M d, Y H:i', strtotime($userDetails['updated_at']));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="sidebar-section">
                                <h4 class="sidebar-title">
                                    <i class="fas fa-shield-alt"></i>
                                    Security Tips
                                </h4>
                                <div class="security-tips">
                                    <div class="tip-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Use a strong, unique password</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Never share your login credentials</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Keep your contact information updated</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Log out when using shared devices</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="sidebar-section">
                                <h4 class="sidebar-title">
                                    <i class="fas fa-question-circle"></i>
                                    Need Help?
                                </h4>
                                <div class="help-links">
                                    <a href="<?php echo BASE_URL; ?>help" class="help-link">
                                        <i class="fas fa-life-ring"></i>
                                        User Manual
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>contact" class="help-link">
                                        <i class="fas fa-envelope"></i>
                                        Contact Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/profile.css">
<script src="<?php echo BASE_URL; ?>js/profile.js"></script>
