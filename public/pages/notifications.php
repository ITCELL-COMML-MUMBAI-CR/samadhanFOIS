<?php
/**
 * Notifications Management Page
 * Allows users to view and manage all their notifications
 */

require_once '../src/utils/SessionManager.php';

// Require login
SessionManager::requireLogin();

$currentUser = SessionManager::getCurrentUser();
$userRole = $_SESSION['user_role'] ?? '';

// Set page title
$pageTitle = 'Notifications';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token');
        }

        require_once '../src/models/Notification.php';
        $notificationModel = new Notification();
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'mark_read':
                $notificationId = $_POST['notification_id'] ?? '';
                if (!empty($notificationId)) {
                    if ($notificationId === 'all') {
                        $notificationModel->markAllAsRead($_SESSION['user_login_id']);
                        $_SESSION['alert_message'] = 'All notifications marked as read.';
                    } else {
                        $notificationModel->markAsRead($notificationId);
                        $_SESSION['alert_message'] = 'Notification marked as read.';
                    }
                    $_SESSION['alert_type'] = 'success';
                }
                break;
                
            case 'delete':
                // Future implementation for deleting notifications
                break;
        }
        
        header('Location: ' . BASE_URL . 'notifications');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['alert_message'] = $e->getMessage();
        $_SESSION['alert_type'] = 'error';
    }
}

// Get filters
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$filter = $_GET['filter'] ?? 'all'; // all, unread, read

// Fetch notifications
require_once '../src/models/Notification.php';
$notificationModel = new Notification();

$notifications = [];
$totalCount = 0;

try {
    if ($filter === 'unread') {
        $notifications = $notificationModel->findByUserId($_SESSION['user_login_id'], $limit, true);
        $totalCount = $notificationModel->countUnread($_SESSION['user_login_id']);
    } else {
        $notifications = $notificationModel->findByUserId($_SESSION['user_login_id'], $limit);
        // Get total count (we'll implement this method)
        $allNotifications = $notificationModel->findByUserId($_SESSION['user_login_id']);
        $totalCount = count($allNotifications);
        
        if ($filter === 'read') {
            $notifications = array_filter($notifications, function($n) { return $n['is_read']; });
        }
    }
} catch (Exception $e) {
    $error = 'Failed to load notifications: ' . $e->getMessage();
}

$totalPages = ceil($totalCount / $limit);

require_once '../src/views/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0">
                        <i class="fas fa-bell text-primary"></i>
                        Notifications
                    </h2>
                    <p class="text-muted mb-0">Manage your notifications and alerts</p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="notification_id" value="all">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </form>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="card mb-4">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                               href="?filter=all">
                                <i class="fas fa-list"></i> All Notifications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $filter === 'unread' ? 'active' : ''; ?>" 
                               href="?filter=unread">
                                <i class="fas fa-envelope"></i> Unread
                                <?php 
                                $unreadCount = $notificationModel->countUnread($_SESSION['user_login_id']);
                                if ($unreadCount > 0): 
                                ?>
                                    <span class="badge bg-danger ms-1"><?php echo $unreadCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $filter === 'read' ? 'active' : ''; ?>" 
                               href="?filter=read">
                                <i class="fas fa-envelope-open"></i> Read
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No notifications found</h5>
                            <p class="text-muted">
                                <?php if ($filter === 'unread'): ?>
                                    You have no unread notifications.
                                <?php elseif ($filter === 'read'): ?>
                                    You have no read notifications.
                                <?php else: ?>
                                    You don't have any notifications yet.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item list-group-item-action notification-list-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php
                                                $iconMap = [
                                                    'complaint_assigned' => 'fa-clipboard-list text-primary',
                                                    'approval_needed' => 'fa-check-circle text-warning',
                                                    'forward' => 'fa-share text-info',
                                                    'assignment' => 'fa-user-tag text-purple',
                                                    'reply_received' => 'fa-reply text-success',
                                                    'more_info_required' => 'fa-info-circle text-danger',
                                                    'complaint_resolved' => 'fa-check-double text-success',
                                                    'system' => 'fa-cog text-secondary'
                                                ];
                                                $icon = $iconMap[$notification['type']] ?? 'fa-bell text-primary';
                                                ?>
                                                <i class="fas <?php echo $icon; ?> me-2"></i>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                
                                                <?php if ($notification['priority'] !== 'normal'): ?>
                                                    <span class="badge bg-<?php 
                                                        echo $notification['priority'] === 'critical' ? 'danger' : 
                                                            ($notification['priority'] === 'high' ? 'warning' : 'info'); 
                                                    ?> ms-2">
                                                        <?php echo strtoupper($notification['priority']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge bg-primary ms-2">NEW</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="mb-2 text-muted"><?php echo htmlspecialchars($notification['message']); ?></p>
                                            
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <?php if (!$notification['is_read']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                                                    <input type="hidden" name="action" value="mark_read">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($notification['action_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-external-link-alt"></i> View
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.notification-list-item.unread {
    background-color: rgba(13, 110, 253, 0.05);
    border-left: 4px solid #0d6efd;
}

.notification-list-item.read {
    opacity: 0.8;
}

.notification-list-item:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.text-purple {
    color: #6f42c1 !important;
}
</style>

<?php require_once '../src/views/footer.php'; ?>
