<?php
// This file is now a view and should not contain business logic.
// The logic is handled by ComplaintController.php
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-plus-circle text-primary"></i> Submit New Grievance
                </h1>
                <div>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-railway-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            
            <!-- Customer Info Card -->
            <?php if ($customerDetails): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user"></i> Customer Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Customer ID:</strong> <?php echo htmlspecialchars($customerDetails['CustomerID']); ?></p>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($customerDetails['Name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Company:</strong> <?php echo htmlspecialchars($customerDetails['CompanyName']); ?></p>
                                <p class="mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($customerDetails['MobileNumber']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Grievance Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt"></i> Grievance Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="grievanceForm">
                        <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                        
                        <!-- Complaint Type and Subtype Selection -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="complaint_type" name="complaint_type" required>
                                        <option value="">Select Complaint Type</option>
                                        <?php foreach ($complaintTypes as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>" 
                                                    <?php echo ($_POST['complaint_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="complaint_type">
                                        <i class="fas fa-list"></i> Complaint Type *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="complaint_subtype" name="complaint_subtype" required disabled>
                                        <option value="">First select a complaint type</option>
                                    </select>
                                    <label for="complaint_subtype">
                                        <i class="fas fa-list-ul"></i> Complaint Subtype *
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mb-3">
                            Select the type and subtype of complaint/grievance you want to submit. The category will be determined automatically.
                        </div>
                        
                        <!-- FNR Number -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="fnr_no" name="fnr_no" required
                                   placeholder="FNR Number" value="<?php echo htmlspecialchars($_POST['fnr_no'] ?? ''); ?>">
                            <label for="fnr_no">
                                <i class="fas fa-receipt"></i> FNR Number *
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
                        
                        <!-- Description -->
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="description" name="description" 
                                      placeholder="Description" style="height: 120px" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <label for="description">
                                <i class="fas fa-comment-alt"></i> Detailed Description *
                            </label>
                            <div class="form-text">
                                Provide a detailed description of your grievance (minimum 20 characters)
                                <span class="float-end">
                                    <span id="charCount">0</span> / 20 minimum
                                </span>
                            </div>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-paperclip"></i> Evidence (Optional)
                                </h6>
                                <p class="card-text">Upload supporting images (Max 3 files, 2MB each)</p>
                                
                                <div class="file-upload-area" id="fileUploadArea" style="cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                    <p>Drag and drop files here or click to select</p>
                                    <input type="file" class="form-control" id="evidence" name="evidence[]" 
                                           multiple accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary" onclick="event.stopPropagation(); document.getElementById('evidence').click();">
                                        <i class="fas fa-upload"></i> Select Files
                                    </button>
                                </div>
                                
                                <div class="row mt-3" id="imagePreview"></div>
                                
                                <div class="form-text">
                                    Supported formats: JPG, JPEG, PNG, GIF. Maximum 3 images, 2MB each.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-railway-primary btn-lg w-100" id="submitBtn">
                                    <i class="fas fa-paper-plane"></i> Submit Grievance
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
                        <i class="fas fa-info-circle"></i> Grievance Submission Guidelines
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
                                <li>Attach supporting evidence if available</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">After Submission:</h6>
                            <ul class="small">
                                <li>You will receive a unique grievance ID</li>
                                <li>Track progress from your dashboard</li>
                                <li>Response within 24-48 hours</li>
                                <li>Updates via email notifications</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Type-Subtype mapping for cascading dropdowns
const typeSubtypeMapping = <?php echo json_encode($typeSubtypeMapping); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('complaint_type');
    const subtypeSelect = document.getElementById('complaint_subtype');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    const fileInput = document.getElementById('evidence');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const imagePreview = document.getElementById('imagePreview');
    
    // Character counter
    function updateCharCount() {
        const count = descriptionField.value.length;
        charCount.textContent = count;
        charCount.parentElement.className = count >= 20 ? 'form-text text-success' : 'form-text text-danger';
    }
    
    descriptionField.addEventListener('input', updateCharCount);
    updateCharCount();
    
    // Cascading dropdown: Type -> Subtype
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        updateSubtypeOptions(selectedType);
    });
    
    function updateSubtypeOptions(selectedType) {
        subtypeSelect.innerHTML = '<option value="">Select Complaint Subtype</option>';
        
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
            subtypeSelect.innerHTML = '<option value="">First select a complaint type</option>';
        }
    }
    
    if (typeSelect.value) {
        updateSubtypeOptions(typeSelect.value);
    }
    
    // File upload handling
    SAMPARKApp.fileUpload.setupDragDrop(fileUploadArea);
    
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
        
        const validation = SAMPARKApp.fileUpload.validate(this.files);
        
        if (!validation.valid) {
            SAMPARKApp.alerts.error(validation.errors.join('<br>'));
            this.value = '';
            imagePreview.innerHTML = '';
            return;
        }
        
        SAMPARKApp.fileUpload.previewImages(this.files, imagePreview);
    });
    
    const form = document.getElementById('grievanceForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    form.addEventListener('submit', function(e) {
        if (descriptionField.value.length < 20) {
            e.preventDefault();
            SAMPARKApp.alerts.error('Description must be at least 20 characters long.');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Submitting...';
        
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }, 10000);
    });
    
    form.addEventListener('reset', function() {
        imagePreview.innerHTML = '';
        updateCharCount();
        subtypeSelect.disabled = true;
        subtypeSelect.innerHTML = '<option value="">First select a complaint type</option>';
    });
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

<?php
// Include footer
require_once __DIR__ . '/../../src/views/footer.php';
?>
