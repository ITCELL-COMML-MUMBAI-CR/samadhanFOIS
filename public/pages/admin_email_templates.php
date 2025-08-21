<?php
/**
 * Admin Email Templates Management Page
 * Provides email template management for administrators
 */

require_once '../src/utils/SessionManager.php';

// Require admin access
SessionManager::requireRole('admin');

// Get current user
$currentUser = SessionManager::getCurrentUser();

// Load models for data
require_once '../src/models/BaseModel.php';
require_once '../src/models/EmailTemplate.php';
require_once '../src/utils/Helpers.php';
require_once '../src/utils/Database.php';

$db = Database::getInstance();
$connection = $db->getConnection();

// Get templates
$emailTemplateModel = new EmailTemplate();
$templates = $emailTemplateModel->getAllTemplates();

// Set page title for header
$pageTitle = 'Email Template Management';

// Include header
require_once '../src/views/header.php';
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-envelope-open-text text-primary"></i> Email Template Management</h1>
            <div>
                <button type="button" class="btn btn-success btn-sm" onclick="showAddTemplateModal()">
                    <i class="fas fa-plus"></i> Add New Template
                </button>
                <a href="<?php echo BASE_URL; ?>admin/bulk-email" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Bulk Email
                </a>
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

    <!-- Templates List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list"></i> Email Templates</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($templates)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No email templates found</h5>
                            <p class="text-muted">Create your first email template to get started.</p>
                            <button type="button" class="btn btn-primary" onclick="showAddTemplateModal()">
                                <i class="fas fa-plus"></i> Create First Template
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Template Name</th>
                                        <th>Subject</th>
                                        <th>Category</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $template): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($template['name']); ?></strong>
                                                <?php if ($template['is_default']): ?>
                                                    <span class="badge bg-primary ms-2">Default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($template['subject']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getCategoryColor($template['category']); ?>">
                                                    <?php echo htmlspecialchars($template['category']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y H:i', strtotime($template['updated_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="previewTemplate(<?php echo $template['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="editTemplate(<?php echo $template['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if (!$template['is_default']): ?>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteTemplate(<?php echo $template['id']; ?>, '<?php echo htmlspecialchars($template['name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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
    </div>
</div>

<!-- Add/Edit Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalTitle">Add New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="templateForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="template_id" name="template_id">
                    <input type="hidden" id="action" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template_name" class="form-label">Template Name *</label>
                                <input type="text" class="form-control" id="template_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template_category" class="form-label">Category *</label>
                                <select class="form-select" id="template_category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="invitation">Invitation</option>
                                    <option value="notification">Notification</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="policy">Policy Update</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="template_subject" class="form-label">Email Subject *</label>
                        <input type="text" class="form-control" id="template_subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="template_content" class="form-label">Email Content *</label>
                        <textarea class="form-control" id="template_content" name="content" rows="15" required></textarea>
                        <div class="form-text">
                            <strong>Available placeholders:</strong> {name}, {login_id}, {email}, {department}, {role}, {portal_url}
                            <br><small class="text-muted">These will be automatically replaced with actual user data when sending.</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template_description" class="form-label">Description</label>
                                <textarea class="form-control" id="template_description" name="description" rows="3" 
                                          placeholder="Brief description of when to use this template"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="template_is_default" name="is_default">
                                    <label class="form-check-label" for="template_is_default">
                                        Set as default template for this category
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-info" onclick="previewTemplateContent()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveTemplateBtn">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Template Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="templatePreview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Include CSS and JS files -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/email_templates.css">
<script src="<?php echo BASE_URL; ?>js/email_templates.js"></script>

<?php
function getCategoryColor($category) {
    $colors = [
        'invitation' => 'success',
        'notification' => 'info',
        'maintenance' => 'warning',
        'policy' => 'primary',
        'custom' => 'secondary'
    ];
    return $colors[$category] ?? 'secondary';
}
?>

<?php require_once '../src/views/footer.php'; ?>
