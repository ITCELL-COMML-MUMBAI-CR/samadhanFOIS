<?php
/**
 * Admin Notifications Page
 * Allows admins to send notifications to users and manage system notifications
 */

require_once '../src/utils/SessionManager.php';

// Require admin access
SessionManager::requireRole('admin');

$currentUser = SessionManager::getCurrentUser();
$pageTitle = 'Notification Management';

$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token');
        }

        require_once '../src/models/Notification.php';
        require_once '../src/models/User.php';
        
        $notificationModel = new Notification();
        $userModel = new User();
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'send_notification':
                $recipient = $_POST['recipient'] ?? '';
                $title = sanitizeInput($_POST['title'] ?? '');
                $message = sanitizeInput($_POST['message'] ?? '');
                $priority = $_POST['priority'] ?? 'normal';
                $type = $_POST['type'] ?? 'system';
                
                if (empty($title) || empty($message)) {
                    throw new Exception('Title and message are required');
                }
                
                $successCount = 0;
                
                if ($recipient === 'all') {
                    // Send to all users
                    $allUsers = $userModel->findAll();
                    $userIds = array_column($allUsers, 'login_id');
                    $successCount = $notificationModel->createBulkNotifications($userIds, $type, $title, $message, $priority);
                    
                } elseif ($recipient === 'customers') {
                    // Send to all customers
                    $customers = $userModel->getUsersByRole('customer');
                    $userIds = array_column($customers, 'login_id');
                    $successCount = $notificationModel->createBulkNotifications($userIds, $type, $title, $message, $priority);
                    
                } elseif ($recipient === 'controllers') {
                    // Send to all controllers
                    $controllers = $userModel->getUsersByRole('controller');
                    $userIds = array_column($controllers, 'login_id');
                    $successCount = $notificationModel->createBulkNotifications($userIds, $type, $title, $message, $priority);
                    
                } else {
                    // Send to specific user
                    if ($notificationModel->createSystemNotification($recipient, $type, $title, $message, $priority)) {
                        $successCount = 1;
                    }
                }
                
                $success = "Notification sent successfully to $successCount user(s).";
                break;
                
            case 'cleanup':
                // Clean up old notifications
                $notificationModel->cleanupOldNotifications();
                $success = 'Old notifications cleaned up successfully.';
                break;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get notification statistics
require_once '../src/models/Notification.php';
require_once '../src/models/User.php';
$notificationModel = new Notification();
$userModel = new User();

$stats = [
    'total_users' => count($userModel->findAll()),
    'customers' => count($userModel->getUsersByRole('customer')),
    'controllers' => count($userModel->getUsersByRole('controller')),
    'admins' => count($userModel->getUsersByRole('admin'))
];

require_once '../src/views/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0">
                        <i class="fas fa-bullhorn text-primary"></i>
                        Notification Management
                    </h2>
                    <p class="text-muted mb-0">Send system notifications and manage user alerts</p>
                </div>
                <a href="<?php echo BASE_URL; ?>admin/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Admin
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Statistics Cards -->
                <div class="col-lg-8">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h4><?php echo $stats['total_users']; ?></h4>
                                    <small>Total Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-user fa-2x mb-2"></i>
                                    <h4><?php echo $stats['customers']; ?></h4>
                                    <small>Customers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-tie fa-2x mb-2"></i>
                                    <h4><?php echo $stats['controllers']; ?></h4>
                                    <small>Controllers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-shield fa-2x mb-2"></i>
                                    <h4><?php echo $stats['admins']; ?></h4>
                                    <small>Admins</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Send Notification Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-paper-plane"></i>
                                Send Notification
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="notificationForm">
                                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="send_notification">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" name="recipient" id="recipient" required>
                                                <option value="">Select Recipients</option>
                                                <option value="all">All Users</option>
                                                <option value="customers">All Customers</option>
                                                <option value="controllers">All Controllers</option>
                                                <optgroup label="Specific Users">
                                                    <?php
                                                    $allUsers = $userModel->findAll();
                                                    foreach ($allUsers as $user):
                                                    ?>
                                                        <option value="<?php echo htmlspecialchars($user['login_id']); ?>">
                                                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['role'] . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            </select>
                                            <label for="recipient">Recipients *</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" name="priority" id="priority">
                                                <option value="low">Low</option>
                                                <option value="normal" selected>Normal</option>
                                                <option value="high">High</option>
                                                <option value="critical">Critical</option>
                                            </select>
                                            <label for="priority">Priority</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" name="type" id="type">
                                                <option value="system">System</option>
                                                <option value="announcement">Announcement</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="update">Update</option>
                                                <option value="alert">Alert</option>
                                            </select>
                                            <label for="type">Type</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="title" id="title" 
                                           placeholder="Notification Title" required maxlength="255">
                                    <label for="title">Title *</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" name="message" id="message" 
                                              placeholder="Notification Message" style="height: 120px" required></textarea>
                                    <label for="message">Message *</label>
                                    <div class="form-text">
                                        Keep messages clear and concise. Maximum 500 characters.
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Notification
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="previewNotification()">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <button type="reset" class="btn btn-outline-danger">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tools"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="cleanup">
                                    <button type="submit" class="btn btn-outline-warning w-100" 
                                            onclick="return confirm('This will delete notifications older than 30 days. Continue?')">
                                        <i class="fas fa-broom"></i> Cleanup Old Notifications
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-outline-info w-100" onclick="sendMaintenanceAlert()">
                                    <i class="fas fa-wrench"></i> Send Maintenance Alert
                                </button>
                                
                                <button type="button" class="btn btn-outline-success w-100" onclick="sendSystemUpdate()">
                                    <i class="fas fa-sync"></i> Announce System Update
                                </button>
                                
                                <a href="<?php echo BASE_URL; ?>notifications" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-bell"></i> View My Notifications
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Templates -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt"></i>
                                Quick Templates
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <button type="button" class="list-group-item list-group-item-action" 
                                        onclick="loadTemplate('maintenance')">
                                    <i class="fas fa-wrench text-warning"></i>
                                    Maintenance Notice
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        onclick="loadTemplate('update')">
                                    <i class="fas fa-sync text-info"></i>
                                    System Update
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        onclick="loadTemplate('security')">
                                    <i class="fas fa-shield-alt text-danger"></i>
                                    Security Alert
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        onclick="loadTemplate('welcome')">
                                    <i class="fas fa-handshake text-success"></i>
                                    Welcome Message
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="notification-preview">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-bell text-primary me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1" id="previewTitle">Notification Title</h6>
                            <p class="mb-2" id="previewMessage">Notification message content will appear here.</p>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Just now • <span id="previewPriority">Normal</span> Priority
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="sendFromPreview()">Send Notification</button>
            </div>
        </div>
    </div>
</div>

<script>
// Template data
const templates = {
    maintenance: {
        title: 'Scheduled System Maintenance',
        message: 'The system will be under maintenance on [DATE] from [TIME]. Please save your work and expect temporary unavailability.',
        priority: 'high',
        type: 'maintenance'
    },
    update: {
        title: 'System Update Available',
        message: 'A new system update has been released with bug fixes and improvements. The update will be applied during the next maintenance window.',
        priority: 'normal',
        type: 'update'
    },
    security: {
        title: 'Security Alert',
        message: 'Please review and update your password if you haven\'t done so recently. Ensure you\'re using a strong, unique password.',
        priority: 'high',
        type: 'alert'
    },
    welcome: {
        title: 'Welcome to SAMADHAN FOIS',
        message: 'Welcome to the SAMADHAN FOIS grievance system. Please take a moment to review the guidelines and feel free to contact support if you need assistance.',
        priority: 'normal',
        type: 'announcement'
    }
};

function loadTemplate(templateName) {
    const template = templates[templateName];
    if (template) {
        document.getElementById('title').value = template.title;
        document.getElementById('message').value = template.message;
        document.getElementById('priority').value = template.priority;
        document.getElementById('type').value = template.type;
    }
}

function previewNotification() {
    const title = document.getElementById('title').value || 'Notification Title';
    const message = document.getElementById('message').value || 'Notification message content will appear here.';
    const priority = document.getElementById('priority').value;
    
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewMessage').textContent = message;
    document.getElementById('previewPriority').textContent = priority.charAt(0).toUpperCase() + priority.slice(1);
    
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

function sendFromPreview() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('previewModal'));
    modal.hide();
    document.getElementById('notificationForm').submit();
}

function sendMaintenanceAlert() {
    loadTemplate('maintenance');
    document.getElementById('recipient').value = 'all';
    previewNotification();
}

function sendSystemUpdate() {
    loadTemplate('update');
    document.getElementById('recipient').value = 'all';
    previewNotification();
}

// Character count for message
document.getElementById('message').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Update or create character count display
    let countDisplay = document.querySelector('.char-count');
    if (!countDisplay) {
        countDisplay = document.createElement('div');
        countDisplay.className = 'char-count form-text text-end';
        this.parentNode.appendChild(countDisplay);
    }
    
    countDisplay.textContent = `${currentLength}/${maxLength} characters`;
    countDisplay.className = `char-count form-text text-end ${remaining < 50 ? 'text-warning' : ''}`;
    
    if (currentLength > maxLength) {
        this.value = this.value.substring(0, maxLength);
    }
});
</script>

<style>
.notification-preview {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.list-group-item-action:hover {
    background-color: rgba(0, 0, 0, 0.05);
}
</style>

<?php require_once '../src/views/footer.php'; ?>
