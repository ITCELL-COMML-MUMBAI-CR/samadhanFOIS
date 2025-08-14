<?php
/**
 * Admin Quick Links Management Page
 * Allows administrators to manage customer home page quick links with icon upload functionality
 */
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-external-link-alt text-primary"></i> Quick Links Management</h1>
            <button type="button" class="btn btn-railway-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuickLinkModal">
                <i class="fas fa-plus"></i> Add Quick Link
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-primary text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['total']; ?></h3>
                    <p class="mb-0">Total Links</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-success text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['active']; ?></h3>
                    <p class="mb-0">Active</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-warning text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['by_category']['railway'] ?? 0; ?></h3>
                    <p class="mb-0">Railway Links</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-info text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['by_category']['grievance'] ?? 0; ?></h3>
                    <p class="mb-0">Grievance Links</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Search by title, description, or URL" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="railway" <?php echo ($filters['category'] ?? '') === 'railway' ? 'selected' : ''; ?>>Railway</option>
                        <option value="system" <?php echo ($filters['category'] ?? '') === 'system' ? 'selected' : ''; ?>>System</option>
                        <option value="external" <?php echo ($filters['category'] ?? '') === 'external' ? 'selected' : ''; ?>>External</option>
                        <option value="grievance" <?php echo ($filters['category'] ?? '') === 'grievance' ? 'selected' : ''; ?>>Grievance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-railway-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Links List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Quick Links List</h5>
        </div>
        <div class="card-body">
            <?php if (empty($quickLinksList)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-external-link-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No quick links found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Position</th>
                                <th>Target</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="quickLinkTableBody">
                            <?php foreach ($quickLinksList as $link): ?>
                                <tr data-id="<?php echo $link['id']; ?>">
                                    <td>
                                        <div class="link-icon">
                                            <?php if ($link['icon_type'] === 'upload' && !empty($link['icon_path'])): ?>
                                                <img src="<?php echo BASE_URL . $link['icon_path']; ?>" alt="Icon" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <i class="<?php echo htmlspecialchars($link['icon_class'] ?? 'fas fa-link'); ?>" style="font-size: 1.5rem; color: #3498db;"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($link['title']); ?></strong>
                                            <div class="text-muted small">
                                                <?php echo htmlspecialchars(substr($link['description'] ?? '', 0, 50)); ?>
                                                <?php if (strlen($link['description'] ?? '') > 50) echo '...'; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-link"></i> 
                                                <?php echo htmlspecialchars($link['url']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $link['category'] === 'railway' ? 'primary' : 
                                                ($link['category'] === 'grievance' ? 'warning' : 
                                                ($link['category'] === 'external' ? 'success' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($link['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $link['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($link['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="position-badge"><?php echo $link['position']; ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo $link['target'] === '_blank' ? 'New Tab' : 'Same Tab'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($link['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editQuickLinkModal"
                                                    onclick="loadQuickLinkForEdit(<?php echo htmlspecialchars(json_encode($link)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewQuickLinkModal"
                                                    onclick="showQuickLinkDetails(<?php echo htmlspecialchars(json_encode($link)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this quick link?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Quick Link Modal -->
<div class="modal fade" id="addQuickLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add Quick Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="add_title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="add_title" name="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="add_category" class="form-label">Category *</label>
                            <select class="form-select" id="add_category" name="category" required>
                                <option value="railway">Railway</option>
                                <option value="system">System</option>
                                <option value="external">External</option>
                                <option value="grievance">Grievance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_url" class="form-label">URL *</label>
                        <input type="url" class="form-control" id="add_url" name="url" required>
                    </div>
                    
                    <!-- Icon Selection -->
                    <div class="mb-3">
                        <label class="form-label">Icon Type</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="icon_type" id="add_icon_fontawesome" value="fontawesome" checked>
                                    <label class="form-check-label" for="add_icon_fontawesome">
                                        FontAwesome Icon
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="icon_type" id="add_icon_upload" value="upload">
                                    <label class="form-check-label" for="add_icon_upload">
                                        Upload Custom Icon
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FontAwesome Icon Selection -->
                    <div id="add_fontawesome_section" class="mb-3">
                        <label for="add_icon_class" class="form-label">FontAwesome Icon Class</label>
                        <input type="text" class="form-control" id="add_icon_class" name="icon_class" placeholder="e.g., fas fa-train">
                        
                        <!-- Icon Suggestions -->
                        <div class="mt-3">
                            <label class="form-label">Suggested Icons:</label>
                            <div id="add_icon_suggestions" class="icon-suggestions">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Icon Section -->
                    <div id="add_upload_section" class="mb-3" style="display: none;">
                        <label for="add_icon_upload_file" class="form-label">Upload Icon (JPG, PNG, GIF, SVG - Max 2MB)</label>
                        <input type="file" class="form-control" id="add_icon_upload_file" name="icon_upload" accept="image/*">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="add_position" class="form-label">Position</label>
                            <input type="number" class="form-control" id="add_position" name="position" value="0" min="0">
                            <small class="text-muted">0 = Auto assign</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="add_target" class="form-label">Target</label>
                            <select class="form-select" id="add_target" name="target">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="add_status" class="form-label">Status</label>
                            <select class="form-select" id="add_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="add_author" class="form-label">Author Name</label>
                            <input type="text" class="form-control" id="add_author" name="author_name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-railway-primary">Add Quick Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Quick Link Modal -->
<div class="modal fade" id="editQuickLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Quick Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editQuickLinkForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_category" class="form-label">Category *</label>
                            <select class="form-select" id="edit_category" name="category" required>
                                <option value="railway">Railway</option>
                                <option value="system">System</option>
                                <option value="external">External</option>
                                <option value="grievance">Grievance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_url" class="form-label">URL *</label>
                        <input type="url" class="form-control" id="edit_url" name="url" required>
                    </div>
                    
                    <!-- Icon Selection -->
                    <div class="mb-3">
                        <label class="form-label">Icon Type</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="icon_type" id="edit_icon_fontawesome" value="fontawesome">
                                    <label class="form-check-label" for="edit_icon_fontawesome">
                                        FontAwesome Icon
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="icon_type" id="edit_icon_upload" value="upload">
                                    <label class="form-check-label" for="edit_icon_upload">
                                        Upload Custom Icon
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Icon Display -->
                    <div id="edit_current_icon" class="mb-3">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    
                    <!-- FontAwesome Icon Selection -->
                    <div id="edit_fontawesome_section" class="mb-3">
                        <label for="edit_icon_class" class="form-label">FontAwesome Icon Class</label>
                        <input type="text" class="form-control" id="edit_icon_class" name="icon_class" placeholder="e.g., fas fa-train">
                        
                        <!-- Icon Suggestions -->
                        <div class="mt-3">
                            <label class="form-label">Suggested Icons:</label>
                            <div id="edit_icon_suggestions" class="icon-suggestions">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Icon Section -->
                    <div id="edit_upload_section" class="mb-3" style="display: none;">
                        <label for="edit_icon_upload_file" class="form-label">Upload New Icon (JPG, PNG, GIF, SVG - Max 2MB)</label>
                        <input type="file" class="form-control" id="edit_icon_upload_file" name="icon_upload" accept="image/*">
                        <small class="text-muted">Leave empty to keep current icon</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="edit_position" class="form-label">Position</label>
                            <input type="number" class="form-control" id="edit_position" name="position" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_target" class="form-label">Target</label>
                            <select class="form-select" id="edit_target" name="target">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_author" class="form-label">Author Name</label>
                            <input type="text" class="form-control" id="edit_author" name="author_name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-railway-primary">Update Quick Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Quick Link Modal -->
<div class="modal fade" id="viewQuickLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Quick Link Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickLinkDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.icon-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
}

.icon-suggestion {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 60px;
}

.icon-suggestion:hover {
    background: #e9ecef;
    border-color: #3498db;
}

.icon-suggestion i {
    font-size: 1.2rem;
    margin-bottom: 4px;
    color: #3498db;
}

.icon-suggestion small {
    font-size: 0.7rem;
    text-align: center;
    line-height: 1.2;
}

.link-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.position-badge {
    display: inline-block;
    background: #f8f9fa;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}
</style>

<script>
// Icon suggestions data
const iconSuggestions = <?php echo json_encode($iconSuggestions); ?>;

// Handle icon type selection for add modal
document.addEventListener('DOMContentLoaded', function() {
    // Add modal icon type handling
    const addIconTypeRadios = document.querySelectorAll('input[name="icon_type"]');
    const addFontawesomeSection = document.getElementById('add_fontawesome_section');
    const addUploadSection = document.getElementById('add_upload_section');
    const addCategorySelect = document.getElementById('add_category');
    
    addIconTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'fontawesome') {
                addFontawesomeSection.style.display = 'block';
                addUploadSection.style.display = 'none';
            } else {
                addFontawesomeSection.style.display = 'none';
                addUploadSection.style.display = 'block';
            }
        });
    });
    
    // Category change handler for add modal
    addCategorySelect.addEventListener('change', function() {
        updateIconSuggestions('add', this.value);
    });
    
    // Initialize icon suggestions for add modal
    updateIconSuggestions('add', addCategorySelect.value);
});

function updateIconSuggestions(modalType, category) {
    const container = document.getElementById(modalType + '_icon_suggestions');
    const iconClass = document.getElementById(modalType + '_icon_class');
    
    if (!container || !iconClass) return;
    
    container.innerHTML = '';
    
    const categoryIcons = iconSuggestions[category] || iconSuggestions['general'] || {};
    
    Object.entries(categoryIcons).forEach(([iconClass, iconName]) => {
        const suggestion = document.createElement('div');
        suggestion.className = 'icon-suggestion';
        suggestion.innerHTML = `
            <i class="${iconClass}"></i>
            <small>${iconName}</small>
        `;
        suggestion.addEventListener('click', function() {
            document.getElementById(modalType + '_icon_class').value = iconClass;
        });
        container.appendChild(suggestion);
    });
}

function loadQuickLinkForEdit(quickLink) {
    document.getElementById('edit_id').value = quickLink.id;
    document.getElementById('edit_title').value = quickLink.title;
    document.getElementById('edit_description').value = quickLink.description || '';
    document.getElementById('edit_url').value = quickLink.url;
    document.getElementById('edit_category').value = quickLink.category;
    document.getElementById('edit_icon_class').value = quickLink.icon_class || '';
    document.getElementById('edit_position').value = quickLink.position;
    document.getElementById('edit_target').value = quickLink.target;
    document.getElementById('edit_status').value = quickLink.status;
    document.getElementById('edit_author').value = quickLink.author_name || '';
    
    // Set icon type
    if (quickLink.icon_type === 'upload') {
        document.getElementById('edit_icon_upload').checked = true;
        document.getElementById('edit_fontawesome_section').style.display = 'none';
        document.getElementById('edit_upload_section').style.display = 'block';
    } else {
        document.getElementById('edit_icon_fontawesome').checked = true;
        document.getElementById('edit_fontawesome_section').style.display = 'block';
        document.getElementById('edit_upload_section').style.display = 'none';
    }
    
    // Show current icon
    const currentIconDiv = document.getElementById('edit_current_icon');
    if (quickLink.icon_type === 'upload' && quickLink.icon_path) {
        currentIconDiv.innerHTML = `
            <label class="form-label">Current Icon:</label>
            <div><img src="<?php echo BASE_URL; ?>${quickLink.icon_path}" alt="Current Icon" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;"></div>
        `;
    } else if (quickLink.icon_class) {
        currentIconDiv.innerHTML = `
            <label class="form-label">Current Icon:</label>
            <div><i class="${quickLink.icon_class}" style="font-size: 2rem; color: #3498db;"></i></div>
        `;
    } else {
        currentIconDiv.innerHTML = '';
    }
    
    // Update icon suggestions
    updateIconSuggestions('edit', quickLink.category);
    
    // Handle edit modal icon type changes
    const editIconTypeRadios = document.querySelectorAll('#editQuickLinkModal input[name="icon_type"]');
    editIconTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'fontawesome') {
                document.getElementById('edit_fontawesome_section').style.display = 'block';
                document.getElementById('edit_upload_section').style.display = 'none';
            } else {
                document.getElementById('edit_fontawesome_section').style.display = 'none';
                document.getElementById('edit_upload_section').style.display = 'block';
            }
        });
    });
    
    // Category change handler for edit modal
    document.getElementById('edit_category').addEventListener('change', function() {
        updateIconSuggestions('edit', this.value);
    });
}

function showQuickLinkDetails(quickLink) {
    const content = `
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        ${quickLink.icon_type === 'upload' && quickLink.icon_path ? 
                            `<img src="<?php echo BASE_URL; ?>${quickLink.icon_path}" alt="Icon" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">` :
                            `<i class="${quickLink.icon_class || 'fas fa-link'}" style="font-size: 2.5rem; color: #3498db;"></i>`
                        }
                    </div>
                    <div>
                        <h4>${quickLink.title}</h4>
                        <div class="mb-2">
                            <span class="badge bg-${quickLink.category === 'railway' ? 'primary' : (quickLink.category === 'grievance' ? 'warning' : (quickLink.category === 'external' ? 'success' : 'info'))} me-2">${quickLink.category.charAt(0).toUpperCase() + quickLink.category.slice(1)}</span>
                            <span class="badge bg-${quickLink.status === 'active' ? 'success' : 'secondary'} me-2">${quickLink.status.charAt(0).toUpperCase() + quickLink.status.slice(1)}</span>
                            <span class="badge bg-light text-dark">Position: ${quickLink.position}</span>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="mt-2">${quickLink.description || 'No description provided'}</p>
                </div>
                
                <div class="mb-3">
                    <strong>URL:</strong>
                    <p class="mt-2"><a href="${quickLink.url}" target="${quickLink.target}">${quickLink.url}</a></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Author:</strong> ${quickLink.author_name || 'Unknown'}<br>
                        <strong>Target:</strong> ${quickLink.target === '_blank' ? 'New Tab' : 'Same Tab'}<br>
                        <strong>Icon Type:</strong> ${quickLink.icon_type === 'upload' ? 'Custom Upload' : 'FontAwesome'}
                    </div>
                    <div class="col-md-6">
                        <strong>Created:</strong> ${new Date(quickLink.created_at).toLocaleString()}<br>
                        <strong>Updated:</strong> ${new Date(quickLink.updated_at).toLocaleString()}
                        ${quickLink.icon_class ? '<br><strong>Icon Class:</strong> ' + quickLink.icon_class : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('quickLinkDetailsContent').innerHTML = content;
}
</script>
