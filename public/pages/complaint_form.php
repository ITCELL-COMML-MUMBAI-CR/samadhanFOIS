<?php
/**
 * Grievance Submission Form
 * Page for customers to submit new grievances
 */

require_once '../src/utils/SessionManager.php';

// Require customer access
SessionManager::requireRole('customer');

$error = '';
$success = '';
$currentUser = SessionManager::getCurrentUser();

// Get customer details
$customerDetails = null;
try {
    require_once '../src/models/BaseModel.php';
    $db = Database::getInstance();
    $connection = $db->getConnection();
    $stmt = $connection->prepare("SELECT * FROM customers WHERE CustomerID = ?");
    $stmt->execute([$currentUser['customer_id']]);
    $customerDetails = $stmt->fetch();
} catch (Exception $e) {
    $error = 'Unable to load customer details.';
}

// Get categories for dropdowns
$categories = [];
$types = [];
$subtypes = [];
try {
    require_once '../src/models/ComplaintCategory.php';
    $categoryModel = new ComplaintCategory();
    $hierarchicalData = $categoryModel->getHierarchicalData();
    $categories = array_keys($hierarchicalData);
} catch (Exception $e) {
    $error = 'Unable to load grievance categories.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token');
        }
        
        $formData = [
            'complaint_type' => sanitizeInput($_POST['complaint_type'] ?? ''),
            'complaint_subtype' => sanitizeInput($_POST['complaint_subtype'] ?? ''),
            'category' => sanitizeInput($_POST['category'] ?? ''),
            'location' => sanitizeInput($_POST['location'] ?? ''),
            'description' => sanitizeInput($_POST['description'] ?? ''),
            'rating' => (int)($_POST['rating'] ?? 0),
            'rating_remarks' => sanitizeInput($_POST['rating_remarks'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($formData['complaint_type'])) {
            $errors[] = 'Grievance type is required';
        }
        
        if (empty($formData['complaint_subtype'])) {
            $errors[] = 'Grievance subtype is required';
        }
        
        if (empty($formData['category'])) {
            $errors[] = 'Category is required';
        }
        
        if (empty($formData['location'])) {
            $errors[] = 'Location is required';
        }
        
        if (empty($formData['description'])) {
            $errors[] = 'Description is required';
        } elseif (strlen($formData['description']) < 20) {
            $errors[] = 'Description must be at least 20 characters long';
        }
        
        if ($formData['rating'] > 0 && $formData['rating'] < 3 && empty($formData['rating_remarks'])) {
            $errors[] = 'Rating remarks are required for ratings below 3';
        }
        
        // Validate category combination
        if (!$categoryModel->validateCombination($formData['category'], $formData['complaint_type'], $formData['complaint_subtype'])) {
            $errors[] = 'Invalid category combination selected';
        }
        
        if (empty($errors)) {
            require_once '../src/models/Complaint.php';
            require_once '../src/models/Evidence.php';
            require_once '../src/models/Transaction.php';
            
            $complaintModel = new Complaint();
            $evidenceModel = new Evidence();
            $transactionModel = new Transaction();
            
            // Prepare complaint data
            $complaintData = [
                'complaint_type' => $formData['complaint_type'],
                'complaint_subtype' => $formData['complaint_subtype'],
                'category' => $formData['category'],
                'location' => $formData['location'],
                'description' => $formData['description'],
                'rating' => $formData['rating'] > 0 ? $formData['rating'] : null,
                'rating_remarks' => !empty($formData['rating_remarks']) ? $formData['rating_remarks'] : null,
                'customer_id' => $currentUser['customer_id'],
                'department' => 'COMMERCIAL', // Default to Commercial as per workflow
                'priority' => 'medium'
            ];
            
            // Create complaint
            $complaintId = $complaintModel->createComplaint($complaintData);
            
            if ($complaintId) {
                // Handle file uploads if present
                if (!empty($_FILES['evidence']['tmp_name'][0])) {
                    $uploadResult = $evidenceModel->handleFileUpload($_FILES['evidence'], $complaintId);
                    
                    if (!$uploadResult['success'] && !empty($uploadResult['errors'])) {
                        $error = 'Grievance submitted but some files failed to upload: ' . implode(', ', $uploadResult['errors']);
                    }
                }
                
                // Log initial transaction
                $transactionModel->logStatusUpdate(
                    $complaintId,
                    'Grievance submitted by customer. Assigned to Commercial Department for review.',
                    $currentUser['login_id']
                );
                
                $success = "Grievance submitted successfully! Your grievance ID is: <strong>$complaintId</strong>";
                
                // Clear form
                $_POST = [];
                
            } else {
                $error = 'Failed to submit grievance. Please try again.';
            }
        } else {
            $error = implode('<br>', $errors);
        }
        
    } catch (Exception $e) {
        error_log('Grievance submission error: ' . $e->getMessage());
        $error = 'An error occurred while submitting your grievance. Please try again.';
    }
}
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
                        
                        <!-- Category Selection -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>" 
                                                    <?php echo ($_POST['category'] ?? '') === $category ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="category">
                                        <i class="fas fa-tags"></i> Category *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="complaint_type" name="complaint_type" required>
                                        <option value="">Select Type</option>
                                    </select>
                                    <label for="complaint_type">
                                        <i class="fas fa-list"></i> Type *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="complaint_subtype" name="complaint_subtype" required>
                                        <option value="">Select Subtype</option>
                                    </select>
                                    <label for="complaint_subtype">
                                        <i class="fas fa-list-ul"></i> Subtype *
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="Location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
                            <label for="location">
                                <i class="fas fa-map-marker-alt"></i> Location *
                            </label>
                            <div class="form-text">
                                Specify the station, depot, or location where the issue occurred
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
                        
                        <!-- Rating Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-star"></i> Service Rating (Optional)
                                </h6>
                                <p class="card-text">Rate the service quality related to this grievance:</p>
                                
                                <div class="rating mb-3" id="serviceRating">
                                    <input type="hidden" name="rating" id="ratingValue" value="<?php echo $_POST['rating'] ?? '0'; ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-rating="<?php echo $i; ?>">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="form-floating" id="ratingRemarksField" style="display: none;">
                                    <textarea class="form-control" id="rating_remarks" name="rating_remarks" 
                                              placeholder="Rating Remarks"><?php echo htmlspecialchars($_POST['rating_remarks'] ?? ''); ?></textarea>
                                    <label for="rating_remarks">
                                        <i class="fas fa-comment"></i> Please explain your rating
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-paperclip"></i> Evidence (Optional)
                                </h6>
                                <p class="card-text">Upload supporting images (Max 3 files, 5MB each)</p>
                                
                                <div class="file-upload-area" id="fileUploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                    <p>Drag and drop files here or click to select</p>
                                    <input type="file" class="form-control" id="evidence" name="evidence[]" 
                                           multiple accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('evidence').click();">
                                        <i class="fas fa-upload"></i> Select Files
                                    </button>
                                </div>
                                
                                <div class="row mt-3" id="imagePreview"></div>
                                
                                <div class="form-text">
                                    Supported formats: JPG, JPEG, PNG, GIF. Maximum 3 images, 5MB each.
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
// Hierarchical data for dynamic dropdowns
const hierarchicalData = <?php echo json_encode($hierarchicalData ?? []); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const typeSelect = document.getElementById('complaint_type');
    const subtypeSelect = document.getElementById('complaint_subtype');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    const ratingStars = document.querySelectorAll('.star');
    const ratingValue = document.getElementById('ratingValue');
    const ratingRemarksField = document.getElementById('ratingRemarksField');
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
    
    // Category cascade
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        updateTypeOptions(category);
        clearSubtypeOptions();
    });
    
    typeSelect.addEventListener('change', function() {
        const category = categorySelect.value;
        const type = this.value;
        updateSubtypeOptions(category, type);
    });
    
    function updateTypeOptions(category) {
        typeSelect.innerHTML = '<option value="">Select Type</option>';
        if (hierarchicalData[category]) {
            Object.keys(hierarchicalData[category]).forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                if ('<?php echo $_POST['complaint_type'] ?? ''; ?>' === type) {
                    option.selected = true;
                }
                typeSelect.appendChild(option);
            });
        }
    }
    
    function updateSubtypeOptions(category, type) {
        subtypeSelect.innerHTML = '<option value="">Select Subtype</option>';
        if (hierarchicalData[category] && hierarchicalData[category][type]) {
            hierarchicalData[category][type].forEach(subtype => {
                const option = document.createElement('option');
                option.value = subtype;
                option.textContent = subtype;
                if ('<?php echo $_POST['complaint_subtype'] ?? ''; ?>' === subtype) {
                    option.selected = true;
                }
                subtypeSelect.appendChild(option);
            });
        }
    }
    
    function clearSubtypeOptions() {
        subtypeSelect.innerHTML = '<option value="">Select Subtype</option>';
    }
    
    // Initialize on page load
    if (categorySelect.value) {
        updateTypeOptions(categorySelect.value);
        if (typeSelect.value) {
            updateSubtypeOptions(categorySelect.value, typeSelect.value);
        }
    }
    
    // Rating system
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingValue.value = rating;
            updateStarDisplay(rating);
            toggleRatingRemarks(rating);
        });
        
        star.addEventListener('mouseover', function() {
            const rating = parseInt(this.dataset.rating);
            updateStarDisplay(rating);
        });
    });
    
    document.getElementById('serviceRating').addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingValue.value) || 0;
        updateStarDisplay(currentRating);
    });
    
    function updateStarDisplay(rating) {
        ratingStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
                star.style.color = '#fbbf24';
            } else {
                star.classList.remove('active');
                star.style.color = '#e2e8f0';
            }
        });
    }
    
    function toggleRatingRemarks(rating) {
        if (rating > 0 && rating < 3) {
            ratingRemarksField.style.display = 'block';
            document.getElementById('rating_remarks').required = true;
        } else {
            ratingRemarksField.style.display = 'none';
            document.getElementById('rating_remarks').required = false;
        }
    }
    
    // Initialize rating display
    const initialRating = parseInt('<?php echo $_POST['rating'] ?? '0'; ?>');
    if (initialRating > 0) {
        updateStarDisplay(initialRating);
        toggleRatingRemarks(initialRating);
    }
    
    // File upload handling
    SamadhanApp.fileUpload.setupDragDrop(fileUploadArea);
    
    fileInput.addEventListener('change', function() {
        const validation = SamadhanApp.fileUpload.validate(this.files);
        
        if (!validation.valid) {
            SamadhanApp.alerts.error(validation.errors.join('<br>'));
            this.value = '';
            imagePreview.innerHTML = '';
            return;
        }
        
        SamadhanApp.fileUpload.previewImages(this.files, imagePreview);
    });
    
    // Form submission
    const form = document.getElementById('grievanceForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    form.addEventListener('submit', function(e) {
        // Validate description length
        if (descriptionField.value.length < 20) {
            e.preventDefault();
            SamadhanApp.alerts.error('Description must be at least 20 characters long.');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Submitting...';
        
        // Re-enable after 10 seconds in case of issues
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }, 10000);
    });
    
    // Form reset
    form.addEventListener('reset', function() {
        imagePreview.innerHTML = '';
        updateStarDisplay(0);
        ratingValue.value = '0';
        ratingRemarksField.style.display = 'none';
        updateCharCount();
        clearSubtypeOptions();
        typeSelect.innerHTML = '<option value="">Select Type</option>';
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

.rating {
    display: flex;
    gap: 0.5rem;
    font-size: 1.5rem;
}

.rating .star {
    cursor: pointer;
    transition: color 0.2s ease;
    color: #e2e8f0;
}

.rating .star:hover,
.rating .star.active {
    color: #fbbf24;
}

@media (max-width: 768px) {
    .file-upload-area {
        padding: 2rem 1rem;
    }
    
    .rating {
        font-size: 1.25rem;
        justify-content: center;
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
