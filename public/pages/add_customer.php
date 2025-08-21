<?php
// This file is now a view and should not contain business logic.
// The logic is handled by CustomerController.php

$formData = [
    'Name' => $_POST['Name'] ?? '',
    'Email' => $_POST['Email'] ?? '',
    'MobileNumber' => $_POST['MobileNumber'] ?? '',
    'CompanyName' => $_POST['CompanyName'] ?? '',
    'Designation' => $_POST['Designation'] ?? ''
];
?>
<!-- Include customer-specific styles -->
<link href="<?php echo BASE_URL; ?>public/css/add_customer.css" rel="stylesheet">

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1 text-primary">
                            <i class="fas fa-building"></i> Create New Customer
                        </h1>
                        <p class="text-muted mb-0">Add new customers to the grievance system</p>
                    </div>
                    <div>
                        <?php if (in_array($_SESSION['user_role'], ['controller', 'viewer', 'admin'])): ?>
                            <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-railway-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>home" class="btn btn-railway-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Home
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card shadow-sm border-0 rounded-lg customer-form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> New Customer Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong><?php echo htmlspecialchars($success); ?></strong>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>customer/add" id="customerForm">
                        <?php echo CSRF::getInput(); ?>
                        <div class="row">
                            <!-- Customer Name -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="Name" name="Name" 
                                           placeholder="Full Name" value="<?php echo htmlspecialchars($formData['Name']); ?>" required>
                                    <label for="Name">
                                        <i class="fas fa-user"></i> Customer Full Name *
                                    </label>
                                    <div class="form-text">Enter the customer's full name</div>
                                </div>
                            </div>
                            
                            <!-- Designation -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="Designation" name="Designation" 
                                           placeholder="Designation" value="<?php echo htmlspecialchars($formData['Designation']); ?>" required>
                                    <label for="Designation">
                                        <i class="fas fa-id-badge"></i> Designation *
                                    </label>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Company Name -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="CompanyName" name="CompanyName" 
                                   placeholder="Company Name" value="<?php echo htmlspecialchars($formData['CompanyName']); ?>" required>
                            <label for="CompanyName">
                                <i class="fas fa-building"></i> Company Name *
                            </label>
                            <div class="form-text">Enter the full company/organization name</div>
                        </div>
                        
                        <div class="row">
                            <!-- Email Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="Email" name="Email" 
                                           placeholder="Email" value="<?php echo htmlspecialchars($formData['Email']); ?>" required>
                                    <label for="Email">
                                        <i class="fas fa-envelope"></i> Email Address *
                                    </label>
                                    <div class="form-text">Required for login and notifications</div>
                                </div>
                            </div>
                            
                            <!-- Mobile Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control" id="MobileNumber" name="MobileNumber" 
                                           placeholder="Mobile" value="<?php echo htmlspecialchars($formData['MobileNumber']); ?>" 
                                           pattern="[0-9]{10}" maxlength="10" required>
                                    <label for="MobileNumber">
                                        <i class="fas fa-phone"></i> Mobile Number *
                                    </label>
                                    <div class="form-text">10 digit mobile number (required for login)</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Password Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="Password" name="Password" 
                                           placeholder="Password" required>
                                    <label for="Password">
                                        <i class="fas fa-lock"></i> Password *
                                    </label>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                                <div id="passwordStrength" class="password-strength mb-2"></div>
                            </div>
                            
                            <!-- Confirm Password Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm Password" required>
                                    <label for="confirm_password">
                                        <i class="fas fa-lock"></i> Confirm Password *
                                    </label>
                                </div>
                                <div id="passwordMatch" class="password-match mb-2"></div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-railway-primary btn-lg w-100" id="submitBtn">
                                    <i class="fas fa-building-user"></i> Create Customer Account
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="reset" class="btn btn-outline-secondary btn-lg w-100" id="resetBtn">
                                    <i class="fas fa-undo"></i> Clear Form
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include customer-specific JavaScript -->
<script src="<?php echo BASE_URL; ?>public/js/add_customer.js"></script>