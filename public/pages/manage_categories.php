<?php
/**
 * Manage Categories Page
 * Admin page to manage grievance categories, types, and subtypes
 */

require_once '../src/utils/SessionManager.php';

// Require admin access
SessionManager::requireRole('admin');

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';

require_once '../src/models/ComplaintCategory.php';
$categoryModel = new ComplaintCategory();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    try {
        switch ($postAction) {
            case 'add':
                $category = sanitizeInput($_POST['category'] ?? '');
                $type = sanitizeInput($_POST['type'] ?? '');
                $subtype = sanitizeInput($_POST['subtype'] ?? '');
                
                if (empty($category) || empty($type) || empty($subtype)) {
                    $error = 'All fields are required.';
                } else {
                    $result = $categoryModel->addCategory($category, $type, $subtype);
                    if ($result !== false) {
                        $success = 'Category added successfully!';
                    } else {
                        $error = 'Category combination already exists.';
                    }
                }
                break;
                
            case 'edit':
                $id = (int)($_POST['id'] ?? 0);
                $category = sanitizeInput($_POST['category'] ?? '');
                $type = sanitizeInput($_POST['type'] ?? '');
                $subtype = sanitizeInput($_POST['subtype'] ?? '');
                
                if (empty($category) || empty($type) || empty($subtype)) {
                    $error = 'All fields are required.';
                } else {
                    $result = $categoryModel->updateCategory($id, $category, $type, $subtype);
                    if ($result) {
                        $success = 'Category updated successfully!';
                    } else {
                        $error = 'Failed to update category.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                $result = $categoryModel->deleteCategory($id);
                if ($result) {
                    $success = 'Category deleted successfully!';
                } else {
                    $error = 'Failed to delete category.';
                }
                break;
        }
    } catch (Exception $e) {
        error_log('Category management error: ' . $e->getMessage());
        $error = 'An error occurred. Please try again.';
    }
}

// Get categories for listing
$categories = [];
$searchTerm = $_GET['search'] ?? '';

if (!empty($searchTerm)) {
    $categories = $categoryModel->searchCategories($searchTerm);
} else {
    $categories = $categoryModel->getAllForManagement();
}

// Get hierarchical data for dropdowns
$hierarchicalData = $categoryModel->getHierarchicalData();

// Get statistics
$stats = $categoryModel->getStatistics();

// Get category for editing
$editCategory = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $allCategories = $categoryModel->getAllForManagement();
    foreach ($allCategories as $cat) {
        if ($cat['CategoryID'] == $editId) {
            $editCategory = $cat;
            break;
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-tags"></i> Manage Grievance Categories
                </h1>
                <div>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-railway-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card card-complaints text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['total_categories']; ?></h3>
                    <p class="mb-0">Categories</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card card-pending text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['total_types']; ?></h3>
                    <p class="mb-0">Types</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card card-resolved text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['total_subtypes']; ?></h3>
                    <p class="mb-0">Subtypes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card card-users text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo count($categories); ?></h3>
                    <p class="mb-0">Total Entries</p>
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
    
    <div class="row">
        <!-- Add/Edit Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>"></i>
                        <?php echo $action === 'edit' ? 'Edit' : 'Add New'; ?> Category
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="categoryForm">
                        <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                        <?php if ($action === 'edit' && $editCategory): ?>
                            <input type="hidden" name="id" value="<?php echo $editCategory['CategoryID']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <input type="text" class="form-control" id="category" name="category" 
                                   value="<?php echo $editCategory ? htmlspecialchars($editCategory['Category']) : ''; ?>" 
                                   list="categoryList" required>
                            <datalist id="categoryList">
                                <?php foreach (array_keys($hierarchicalData) as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Type *</label>
                            <input type="text" class="form-control" id="type" name="type" 
                                   value="<?php echo $editCategory ? htmlspecialchars($editCategory['Type']) : ''; ?>" 
                                   list="typeList" required>
                            <datalist id="typeList">
                                <!-- Populated dynamically -->
                            </datalist>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subtype" class="form-label">Subtype *</label>
                            <input type="text" class="form-control" id="subtype" name="subtype" 
                                   value="<?php echo $editCategory ? htmlspecialchars($editCategory['SubType']) : ''; ?>" 
                                   list="subtypeList" required>
                            <datalist id="subtypeList">
                                <!-- Populated dynamically -->
                            </datalist>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-railway-primary">
                                <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?>"></i>
                                <?php echo $action === 'edit' ? 'Update' : 'Add'; ?> Category
                            </button>
                            <?php if ($action === 'edit'): ?>
                                <a href="<?php echo BASE_URL; ?>admin/categories" class="btn btn-railway-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportCategories()">
                            <i class="fas fa-download"></i> Export Categories
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearForm()">
                            <i class="fas fa-eraser"></i> Clear Form
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="showStatistics()">
                            <i class="fas fa-chart-bar"></i> View Statistics
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Categories List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-list"></i> Categories List
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="action" value="list">
                                <input type="text" class="form-control form-control-sm me-2" 
                                       name="search" placeholder="Search categories..." 
                                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                                <button type="submit" class="btn btn-railway-primary btn-sm">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (!empty($searchTerm)): ?>
                                    <a href="<?php echo BASE_URL; ?>admin/categories" class="btn btn-secondary btn-sm ms-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Subtype</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <br>
                                            <?php echo empty($searchTerm) ? 'No categories found.' : 'No categories match your search.'; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['CategoryID']; ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($category['Category']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($category['Type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($category['SubType']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo BASE_URL; ?>admin/categories?action=edit&id=<?php echo $category['CategoryID']; ?>" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteCategory(<?php echo $category['CategoryID']; ?>, '<?php echo htmlspecialchars(addslashes($category['Category'] . ' - ' . $category['Type'] . ' - ' . $category['SubType'])); ?>')" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category?</p>
                <p><strong id="deleteItemName"></strong></p>
                <p class="text-danger">
                    <i class="fas fa-warning"></i> This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteItemId">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Hierarchical data for dynamic dropdowns
const hierarchicalData = <?php echo json_encode($hierarchicalData); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const categoryInput = document.getElementById('category');
    const typeInput = document.getElementById('type');
    const subtypeInput = document.getElementById('subtype');
    const typeList = document.getElementById('typeList');
    const subtypeList = document.getElementById('subtypeList');
    
    // Update type dropdown when category changes
    categoryInput.addEventListener('input', function() {
        const category = this.value;
        updateTypeOptions(category);
        clearSubtypeOptions();
    });
    
    // Update subtype dropdown when type changes
    typeInput.addEventListener('input', function() {
        const category = categoryInput.value;
        const type = this.value;
        updateSubtypeOptions(category, type);
    });
    
    function updateTypeOptions(category) {
        typeList.innerHTML = '';
        if (hierarchicalData[category]) {
            Object.keys(hierarchicalData[category]).forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                typeList.appendChild(option);
            });
        }
    }
    
    function updateSubtypeOptions(category, type) {
        subtypeList.innerHTML = '';
        if (hierarchicalData[category] && hierarchicalData[category][type]) {
            hierarchicalData[category][type].forEach(subtype => {
                const option = document.createElement('option');
                option.value = subtype;
                subtypeList.appendChild(option);
            });
        }
    }
    
    function clearSubtypeOptions() {
        subtypeList.innerHTML = '';
        subtypeInput.value = '';
    }
    
    // Initialize dropdowns if editing
    if (categoryInput.value) {
        updateTypeOptions(categoryInput.value);
        if (typeInput.value) {
            updateSubtypeOptions(categoryInput.value, typeInput.value);
        }
    }
});

function deleteCategory(id, name) {
    document.getElementById('deleteItemId').value = id;
    document.getElementById('deleteItemName').textContent = name;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function clearForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('typeList').innerHTML = '';
    document.getElementById('subtypeList').innerHTML = '';
}

function exportCategories() {
    window.open('<?php echo BASE_URL; ?>api/categories/export', '_blank');
}

function showStatistics() {
    SamadhanApp.alerts.info('Categories: <?php echo $stats['total_categories']; ?>, Types: <?php echo $stats['total_types']; ?>, Subtypes: <?php echo $stats['total_subtypes']; ?>');
}
</script>
