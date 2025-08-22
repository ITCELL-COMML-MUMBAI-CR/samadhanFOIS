<?php
/**
 * New Support Ticket Form with Customer Authentication
 * First authenticates customer, then shows the support ticket form
 * 
 * Authentication Logic:
 * - Customers can be authenticated through regular user login (user_logged_in + user_customer_id)
 * - Or through customer-specific authentication (customer_logged_in)
 * - If either method is active, customer is considered authenticated
 */

require_once __DIR__ . '/../../src/utils/SessionManager.php';

// Set page title
$pageTitle = 'Create New Support Ticket';

// Include header
require_once __DIR__ . '/../../src/views/header.php';

// Check if customer is already authenticated
// Customers can be authenticated either through regular login or customer-specific login
$customerAuthenticated = (
    (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && 
     isset($_SESSION['user_customer_id']) && !empty($_SESSION['user_customer_id']))
);

// Debug: Log authentication status
error_log("Customer authentication status: " . ($customerAuthenticated ? 'AUTHENTICATED' : 'NOT AUTHENTICATED'));
if (isset($_SESSION['user_logged_in'])) {
    error_log("Session user_logged_in: " . ($_SESSION['user_logged_in'] ? 'TRUE' : 'FALSE'));
}
if (isset($_SESSION['user_customer_id'])) {
    error_log("Session user_customer_id: " . $_SESSION['user_customer_id']);
}

    // Load necessary data for the support ticket form if customer is authenticated
    if ($customerAuthenticated) {
        require_once __DIR__ . '/../../src/models/ComplaintCategory.php';
        require_once __DIR__ . '/../../src/models/Shed.php';
        require_once __DIR__ . '/../../src/models/Department.php';
        require_once __DIR__ . '/../../src/models/Wagon.php';
        
        $complaintCategoryModel = new ComplaintCategory();
        $shedModel = new Shed();
        $departmentModel = new Department();
        $wagonModel = new Wagon();
        
        // Get complaint types and subtypes
        $complaintTypes = $complaintCategoryModel->getComplaintTypes();
        $typeSubtypeMapping = $complaintCategoryModel->getTypeSubtypeMapping();
        
        // Get sheds for location selection
        $sheds = $shedModel->getAllSheds();
        
        // Get wagons
        $wagons = $wagonModel->getAllWagons();
    }
?>

<!-- Load necessary CSS and JS files -->
<meta name="base-url" content="<?php echo BASE_URL; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/new_support_ticket_with_auth.css">
<script src="<?php echo BASE_URL; ?>js/new_support_ticket_with_auth.js"></script>

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

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-plus-circle text-primary"></i> Create New Support Ticket
                </h1>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>

            <?php if (!$customerAuthenticated): ?>
                <!-- Customer Authentication Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-lock"></i> Customer Authentication Required
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Authentication Required:</strong> You must be a registered customer to submit support tickets. 
                            Please enter your email or mobile number and password to continue.
                        </div>
                        
                        <form id="customerAuthForm" method="POST" action="<?php echo BASE_URL; ?>support/new" class="login-form">
                            <div class="form-group">
                                <label for="login_identifier" class="form-label">
                                    <i class="fas fa-user"></i> Email or Mobile Number
                                </label>
                                <input type="text" class="form-control" id="login_identifier" name="login_identifier" 
                                       placeholder="Enter your email or mobile number" required>
                                <div class="form-text">Enter your registered email address or mobile number</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" class="form-control" id="customer_password" name="password" 
                                           placeholder="Enter your password" required>
                                    <button type="button" class="password-toggle-btn" id="passwordToggle">
                                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-railway-primary btn-lg w-100" id="authSubmitBtn">
                                <i class="fas fa-sign-in-alt"></i> Authenticate & Continue
                            </button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-question-circle"></i> 
                                Not registered? Contact your administrator to create a customer account.
                            </small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Customer Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user"></i> Customer Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Customer ID:</strong> <?php echo htmlspecialchars(isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : $_SESSION['user_customer_id']); ?></p>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars(isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : $_SESSION['user_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Company:</strong> <?php echo htmlspecialchars(isset($_SESSION['customer_company']) ? $_SESSION['customer_company'] : $_SESSION['user_company']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars(isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : $_SESSION['user_email']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support Ticket Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-headset"></i> Support Ticket Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="supportTicketForm" action="<?php echo BASE_URL; ?>api/complaints">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                            
                            <!-- Complaint Type and Subtype Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="complaint_type" name="complaint_type" required>
                                            <option value="">Select Issue Type</option>
                                            <?php foreach ($complaintTypes as $type): ?>
                                                <option value="<?php echo htmlspecialchars($type); ?>" 
                                                        <?php echo ($_POST['complaint_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($type); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="complaint_type">
                                            <i class="fas fa-list"></i> Issue Type *
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="complaint_subtype" name="complaint_subtype" required disabled>
                                            <option value="">First select an issue type</option>
                                        </select>
                                        <label for="complaint_subtype">
                                            <i class="fas fa-list-ul"></i> Issue Subtype *
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text mb-3">
                                Select the type and subtype of issue you need assistance with. The category will be determined automatically.
                            </div>
                            
                            <!-- FNR Number -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="fnr_no" name="fnr_no" 
                                       placeholder="FNR Number" value="<?php echo htmlspecialchars($_POST['fnr_no'] ?? ''); ?>">
                                <label for="fnr_no">
                                    <i class="fas fa-receipt"></i> FNR Number / GSTN IN / eIndent*
                                </label>
                                <div class="form-text">
                                    Enter the Freight Note Receipt (FNR) number if applicable
                                </div>
                            </div>
                            
                            <!-- Location -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="shed_id" name="shed_id" required>
                                    <option value="">Select Location</option>
                                    <?php foreach ($sheds as $shed): ?>
                                        <option value="<?php echo htmlspecialchars($shed['ShedID']); ?>" 
                                                <?php echo ($_POST['shed_id'] ?? '') == $shed['ShedID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($shed['Terminal'] . ' - ' . $shed['Name'] . ' - ' . $shed['Division'] . ' - ' . $shed['Zone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="shed_id">
                                    <i class="fas fa-map-marker-alt"></i> Location *
                                </label>
                                <div class="form-text">
                                    Select the terminal, depot, or location where the issue occurred
                                </div>
                            </div>
                            
                            <!-- Wagon Selection (Optional) -->
                            <div class="form-floating mb-3">
                                <select class="form-select" id="wagon_id" name="wagon_id">
                                    <option value="">Select Wagon (Optional)</option>
                                    <?php if (isset($wagons) && is_array($wagons)): ?>
                                        <?php foreach ($wagons as $wagon): ?>
                                            <option value="<?php echo htmlspecialchars($wagon['WagonID']); ?>" 
                                                    <?php echo ($_POST['wagon_id'] ?? '') == $wagon['WagonID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($wagon['WagonCode'] ? $wagon['WagonCode'] . ' - ' . $wagon['Type'] : $wagon['Type']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="wagon_id">
                                    <i class="fas fa-train"></i> Wagon (Optional)
                                </label>
                                <div class="form-text">
                                    Select a specific wagon if your issue is related to a particular wagon
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="description" name="description" 
                                          placeholder="Description" style="height: 120px" required 
                                          data-bs-toggle="tooltip" data-bs-placement="top" 
                                          title="Provide detailed information about your issue. Include specific details like when it occurred, what happened, and any error messages."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                <label for="description">
                                    <i class="fas fa-comment-alt"></i> Detailed Description *
                                </label>
                                <div class="form-text">
                                    <i class="fas fa-info-circle text-info"></i> Provide a detailed description of your issue (minimum 20 characters)
                                </div>
                            </div>
                            
                            <!-- File Upload -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-paperclip"></i> Supporting Documents (Optional)
                                    </h6>
                                    <p class="card-text">Upload supporting images or documents (Max 3 files, 2MB each)</p>
                                    
                                    <div class="file-upload-area" id="fileUploadArea" style="cursor: pointer;">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                        <p>Drag and drop files here or click to select (Max 3 files)</p>
                                        <input type="file" class="form-control" id="evidence" name="evidence[]" 
                                               accept="image/*,.pdf,.doc,.docx" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary" onclick="event.stopPropagation(); document.getElementById('evidence').click();">
                                            <i class="fas fa-upload"></i> Select Files
                                        </button>
                                    </div>
                                    
                                    <div class="row mt-3" id="imagePreview"></div>
                                    
                                    <div class="form-text">
                                        Supported formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX. Maximum 3 files, 2MB each.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-railway-primary btn-lg w-100" id="submitBtn">
                                        <i class="fas fa-paper-plane"></i> Submit Support Ticket
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="reset" class="btn btn-railway-secondary btn-lg w-100">
                                        <i class="fas fa-eraser"></i> Clear Form
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Guidelines -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Support Ticket Guidelines
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Before Submitting:</h6>
                                <ul class="small">
                                    <li>Ensure all required fields are completed</li>
                                    <li>Provide specific location details</li>
                                    <li>Include relevant dates and times</li>
                                    <li>Attach supporting documents if available</li>
                                    <li>Select wagon if issue is wagon-specific</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">After Submission:</h6>
                                <ul class="small">
                                    <li>You will receive a unique support ticket ID</li>
                                    <li>Track progress from Support & Assistance page</li>
                                    <li>Response within 24-48 hours</li>
                                    <li>Updates via email and dashboard</li>
                                    <li>You can add responses to ongoing tickets</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Type-Subtype mapping for cascading dropdowns
window.typeSubtypeMapping = <?php echo json_encode($typeSubtypeMapping ?? []); ?>;
</script>

<?php
// Include footer
require_once __DIR__ . '/../../src/views/footer.php';
?>