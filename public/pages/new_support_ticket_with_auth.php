<?php
/**
 * New Support Ticket Form with Customer Authentication
 * First authenticates customer, then shows the support ticket form
 */

// Check if customer is already authenticated
$customerAuthenticated = isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'];
?>

<!-- Load necessary CSS and JS files -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/new_support_ticket_with_auth.css">
<script src="<?php echo BASE_URL; ?>js/new_support_ticket_with_auth.js"></script>

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
                            Please enter your email and password to continue.
                        </div>
                        
                        <form id="customerAuthForm" method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="customer_email" name="email" 
                                               placeholder="Email" required>
                                        <label for="customer_email">
                                            <i class="fas fa-envelope"></i> Email Address *
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="customer_password" name="password" 
                                               placeholder="Password" required>
                                        <label for="customer_password">
                                            <i class="fas fa-lock"></i> Password *
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-railway-primary btn-lg" id="authSubmitBtn">
                                    <i class="fas fa-sign-in-alt"></i> Authenticate & Continue
                                </button>
                            </div>
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
                                <p class="mb-1"><strong>Customer ID:</strong> <?php echo htmlspecialchars($_SESSION['customer_id']); ?></p>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['customer_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Company:</strong> <?php echo htmlspecialchars($_SESSION['customer_company']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['customer_email']); ?></p>
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
                        <form method="POST" enctype="multipart/form-data" id="supportTicketForm">
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
                                <input type="text" class="form-control" id="fnr_no" name="fnr_no" required
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
                                            <?php echo htmlspecialchars($shed['Terminal'] . ' - ' . $shed['Type']); ?>
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
                                        <p>Drag and drop files here or click to select</p>
                                        <input type="file" class="form-control" id="evidence" name="evidence[]" 
                                               multiple accept="image/*,.pdf,.doc,.docx" style="display: none;">
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
const typeSubtypeMapping = <?php echo json_encode($typeSubtypeMapping ?? []); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    <?php if (!$customerAuthenticated): ?>
    // Customer Authentication Form Handler
    const authForm = document.getElementById('customerAuthForm');
    const authSubmitBtn = document.getElementById('authSubmitBtn');
    
    authForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        authSubmitBtn.disabled = true;
        authSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Authenticating...';
        
        const formData = new FormData(authForm);
        
        // Submit authentication request
        fetch('<?php echo BASE_URL; ?>customer-auth/authenticate', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Authentication successful - reload page to show form
                Swal.fire({
                    icon: 'success',
                    title: 'Authentication Successful!',
                    text: 'Welcome back, ' + data.customer.name + '!',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Continue'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                // Authentication failed
                Swal.fire({
                    icon: 'error',
                    title: 'Authentication Failed',
                    text: data.message,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
                
                // Reset button state
                authSubmitBtn.disabled = false;
                authSubmitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Authenticate & Continue';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'There was a network error. Please try again.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
            
            // Reset button state
            authSubmitBtn.disabled = false;
            authSubmitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Authenticate & Continue';
        });
    });
    <?php else: ?>
    // Support Ticket Form Handler (only if authenticated)
    const typeSelect = document.getElementById('complaint_type');
    const subtypeSelect = document.getElementById('complaint_subtype');
    const descriptionField = document.getElementById('description');
    const fileInput = document.getElementById('evidence');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const imagePreview = document.getElementById('imagePreview');
    
    // Cascading dropdown: Type -> Subtype
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        updateSubtypeOptions(selectedType);
    });
    
    function updateSubtypeOptions(selectedType) {
        subtypeSelect.innerHTML = '<option value="">Select Issue Subtype</option>';
        
        if (selectedType && typeSubtypeMapping[selectedType]) {
            subtypeSelect.disabled = false;
            
            typeSubtypeMapping[selectedType].forEach(function(subtype) {
                const option = document.createElement('option');
                option.value = subtype;
                option.textContent = subtype;
                if ('<?php echo htmlspecialchars($_POST['complaint_subtype'] ?? ''); ?>' === subtype) {
                    option.selected = true;
                }
                subtypeSelect.appendChild(option);
            });
        } else {
            subtypeSelect.disabled = true;
            subtypeSelect.innerHTML = '<option value="">First select an issue type</option>';
        }
    }
    
    if (typeSelect.value) {
        updateSubtypeOptions(typeSelect.value);
    }
    
    // File upload handling
    if (typeof SAMPARKApp !== 'undefined' && SAMPARKApp.fileUpload) {
        SAMPARKApp.fileUpload.setupDragDrop(fileUploadArea);
    }
    
    fileUploadArea.addEventListener('click', function(e) {
        if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
            fileInput.click();
        }
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files.length === 0) {
            imagePreview.innerHTML = '';
            return;
        }
        
        if (typeof SAMPARKApp !== 'undefined' && SAMPARKApp.fileUpload) {
            const validation = SAMPARKApp.fileUpload.validate(this.files);
            
            if (!validation.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Upload Error',
                    text: validation.errors.join('\n'),
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                imagePreview.innerHTML = '';
                return;
            }
            
            SAMPARKApp.fileUpload.previewImages(this.files, imagePreview);
        }
    });
    
    const form = document.getElementById('supportTicketForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate description length
        if (descriptionField.value.length < 20) {
            descriptionField.focus();
            descriptionField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            Swal.fire({
                icon: 'error',
                title: 'Description Too Short',
                html: `
                    <div class="text-start">
                        <p>Your description is only <strong>${descriptionField.value.length}</strong> characters long.</p>
                        <p>Please provide at least <strong>20 characters</strong> with more details about your issue.</p>
                        <div class="alert alert-info">
                            <small><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Include specific details like:</small>
                            <ul class="small mb-0 mt-2">
                                <li>When did the issue occur?</li>
                                <li>What exactly happened?</li>
                                <li>What was the impact?</li>
                                <li>Any error messages or codes?</li>
                            </ul>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'I Understand',
                width: '500px'
            });
            
            return;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Submitting Support Ticket...',
            html: 'Please wait while we process your request.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Submitting...';
        
        const formData = new FormData(form);
        
        // Submit form via AJAX
        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text();
            }
        })
        .then(data => {
            let isSuccess = false;
            let message = '';
            
            if (typeof data === 'object' && data.success) {
                isSuccess = true;
                message = data.message;
            } else if (typeof data === 'string' && (data.includes('Support ticket submitted successfully') || data.includes('alert-success'))) {
                isSuccess = true;
                message = 'Support ticket submitted successfully!';
            }
            
            if (isSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Support Ticket Submitted Successfully!',
                    text: message || 'Your complaint has been submitted and will be reviewed by our team.',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'View My Tickets',
                    showCancelButton: true,
                    cancelButtonText: 'Submit Another',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '<?php echo BASE_URL; ?>support/assistance';
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        form.reset();
                        imagePreview.innerHTML = '';
                        subtypeSelect.disabled = true;
                        subtypeSelect.innerHTML = '<option value="">First select an issue type</option>';
                        
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            } else {
                let errorMessage = 'There was an error submitting your support ticket. Please try again.';
                
                if (typeof data === 'object' && data.message) {
                    errorMessage = data.message;
                } else if (typeof data === 'string' && data.includes('alert-danger')) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    const errorElement = tempDiv.querySelector('.alert-danger');
                    if (errorElement) {
                        errorMessage = errorElement.textContent.trim();
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'There was a network error. Please check your connection and try again.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
    
    form.addEventListener('reset', function() {
        imagePreview.innerHTML = '';
        subtypeSelect.disabled = true;
        subtypeSelect.innerHTML = '<option value="">First select an issue type</option>';
    });
    <?php endif; ?>
});
</script>

<style>
.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    padding: 3rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-upload-area:hover,
.file-upload-area.dragover {
    border-color: var(--railway-blue);
    background-color: #f8f9fa;
}

/* Enhanced form validation styles */
.form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-valid:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Character counter animations */
.form-text {
    transition: all 0.3s ease;
}

.form-text.text-success {
    font-weight: 600;
}

.form-text.text-danger {
    font-weight: 600;
}

/* Smooth focus transition */
#description {
    transition: all 0.3s ease;
}

#description:focus {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* SweetAlert Customization */
.swal2-popup {
    border-radius: 0.75rem;
    font-family: inherit;
}

.swal2-title {
    color: #2c3e50;
    font-weight: 600;
}

.swal2-content {
    color: #5a6c7d;
}

.swal2-confirm {
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
}

.swal2-cancel {
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
}

.swal2-icon {
    border-width: 3px;
}

.swal2-icon.swal2-success {
    border-color: #28a745;
    color: #28a745;
}

.swal2-icon.swal2-error {
    border-color: #dc3545;
    color: #dc3545;
}

.swal2-icon.swal2-info {
    border-color: #17a2b8;
    color: #17a2b8;
}

@media (max-width: 768px) {
    .file-upload-area {
        padding: 2rem 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .form-floating {
        margin-bottom: 1rem;
    }
    
    .btn-lg {
        margin-bottom: 0.5rem;
    }
}
</style>
