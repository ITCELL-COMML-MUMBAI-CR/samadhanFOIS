<?php
// This file is now a view and should not contain business logic.
// The logic is handled by CustomerController.php

$formData = [
    'login_id' => $_POST['login_id'] ?? '',
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'mobile' => $_POST['mobile'] ?? '',
    'company_name' => $_POST['company_name'] ?? ''
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
                        <p class="text-muted mb-0">Add new customers and their user accounts to the grievance system</p>
                    </div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-railway-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card shadow-sm border-0 rounded-lg customer-form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> New Customer & Account Information
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
                        <div class="row">
                            <!-- Customer Name -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name" 
                                           placeholder="Full Name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                                    <label for="name">
                                        <i class="fas fa-user"></i> Customer Full Name *
                                    </label>
                                    <div class="form-text">Enter the customer's full name</div>
                                </div>
                            </div>
                            
                            <!-- Login ID Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="login_id" name="login_id" 
                                           placeholder="Login ID" value="<?php echo htmlspecialchars($formData['login_id']); ?>" 
                                           required autocomplete="off">
                                    <label for="login_id">
                                        <i class="fas fa-key"></i> Login ID *
                                    </label>
                                    <div class="form-text">Unique identifier for customer login (min 3 characters)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Company Name -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                   placeholder="Company Name" value="<?php echo htmlspecialchars($formData['company_name']); ?>" required>
                            <label for="company_name">
                                <i class="fas fa-building"></i> Company Name *
                            </label>
                            <div class="form-text">Enter the full company/organization name</div>
                        </div>
                        
                        <div class="row">
                            <!-- Email Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Email" value="<?php echo htmlspecialchars($formData['email']); ?>">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Email Address
                                    </label>
                                    <div class="form-text">Optional - for password recovery</div>
                                </div>
                            </div>
                            
                            <!-- Mobile Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control" id="mobile" name="mobile" 
                                           placeholder="Mobile" value="<?php echo htmlspecialchars($formData['mobile']); ?>" 
                                           pattern="[0-9]{10}" maxlength="10">
                                    <label for="mobile">
                                        <i class="fas fa-phone"></i> Mobile Number
                                    </label>
                                    <div class="form-text">Optional - 10 digit mobile number</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Password Field -->
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Password" required>
                                    <label for="password">
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
                                    <i class="fas fa-building-user"></i> Create Customer & Account
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
