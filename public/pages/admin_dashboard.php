<?php
/**
 * Admin Dashboard Overview
 * Provides quick access to all admin management functions
 */

require_once '../src/utils/SessionManager.php';

// Require admin access
SessionManager::requireRole('admin');

// Get current user
$currentUser = SessionManager::getCurrentUser();

// Load models for data
require_once '../src/models/BaseModel.php';
require_once '../src/models/Complaint.php';
require_once '../src/models/User.php';

$db = Database::getInstance();
$connection = $db->getConnection();

// Get quick statistics
$stats = [];

// Total complaints
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM complaints");
$stmt->execute();
$stats['total_complaints'] = $stmt->fetch()['count'];

// Pending complaints
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'");
$stmt->execute();
$stats['pending_complaints'] = $stmt->fetch()['count'];

// Total users
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['count'];

// Recent complaints (last 7 days)
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM complaints WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute();
$stats['recent_7_days'] = $stmt->fetch()['count'];

// Complaints by status
$stmt = $connection->prepare("SELECT status, COUNT(*) as count FROM complaints GROUP BY status");
$stmt->execute();
$stats['by_status'] = $stmt->fetchAll();

// Users by role
$stmt = $connection->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt->execute();
$stats['users_by_role'] = $stmt->fetchAll();

// Set page title for header
$pageTitle = 'Admin Dashboard';

// Include header
require_once '../src/views/header.php';
?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-tachometer-alt text-primary"></i> Admin Dashboard
                    </h1>
                    <p class="text-muted mb-0">
                        Welcome back, <?php echo htmlspecialchars($currentUser['name']); ?>! 
                        <span class="badge bg-primary">System Administrator</span>
                    </p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Complaints
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_complaints']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Complaints
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['pending_complaints']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Recent (7 Days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['recent_7_days']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Management Grid -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3"><i class="fas fa-cogs text-primary"></i> System Management</h4>
        </div>
    </div>

    <div class="row mb-4">
        <!-- User Management -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">User Management</h5>
                    <p class="card-text text-muted">Manage system users, roles, and permissions</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-primary btn-sm">
                            <i class="fas fa-cog"></i> Manage Users
                        </a>
                        <a href="<?php echo BASE_URL; ?>register" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-plus"></i> Add User
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complaint Categories -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-tags fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Categories</h5>
                    <p class="card-text text-muted">Manage complaint categories and types</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/categories" class="btn btn-success btn-sm">
                            <i class="fas fa-cog"></i> Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- News & Announcements -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-newspaper fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">News & Announcements</h5>
                    <p class="card-text text-muted">Manage system announcements and news</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/news" class="btn btn-info btn-sm">
                            <i class="fas fa-cog"></i> Manage News
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-external-link-alt fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">Quick Links</h5>
                    <p class="card-text text-muted">Manage system quick links and shortcuts</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/quicklinks" class="btn btn-warning btn-sm">
                            <i class="fas fa-cog"></i> Manage Links
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Analytics & Reports -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title">Analytics & Reports</h5>
                    <p class="card-text text-muted">View system analytics and generate reports</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/reports" class="btn btn-danger btn-sm">
                            <i class="fas fa-chart-bar"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Management -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-envelope fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Email Management</h5>
                    <p class="card-text text-muted">Manage email templates and bulk emails</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/email-templates" class="btn btn-secondary btn-sm">
                            <i class="fas fa-envelope-open-text"></i> Templates
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/bulk-email" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-paper-plane"></i> Bulk Email
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Management -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-tie fa-3x text-dark"></i>
                    </div>
                    <h5 class="card-title">Customer Management</h5>
                    <p class="card-text text-muted">Manage customer accounts and information</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>customer/add" class="btn btn-dark btn-sm">
                            <i class="fas fa-user-plus"></i> Add Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Logs -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-history fa-3x text-muted"></i>
                    </div>
                    <h5 class="card-title">System Logs</h5>
                    <p class="card-text text-muted">View system activity and error logs</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>admin/logs" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-list"></i> View Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt text-warning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo BASE_URL; ?>grievances/hub" class="btn btn-outline-primary w-100">
                                <i class="fas fa-comments"></i> View Complaints Hub
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo BASE_URL; ?>reports" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo BASE_URL; ?>admin/notifications" class="btn btn-outline-info w-100">
                                <i class="fas fa-bell"></i> Manage Notifications
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo BASE_URL; ?>help" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-question-circle"></i> System Help
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Complaint Status Overview</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['by_status'] as $status): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $status['status'] === 'Pending' ? 'warning' : 
                                                ($status['status'] === 'Closed' ? 'success' : 
                                                ($status['status'] === 'Replied' ? 'info' : 'secondary')); 
                                        ?>">
                                            <?php echo $status['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $status['count']; ?></td>
                                    <td><?php echo round(($status['count'] / $stats['total_complaints']) * 100, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['users_by_role'] as $role): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $role['role'] === 'admin' ? 'danger' : 
                                                ($role['role'] === 'controller' ? 'primary' : 
                                                ($role['role'] === 'customer' ? 'success' : 'secondary')); 
                                        ?>">
                                            <?php echo ucfirst($role['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $role['count']; ?></td>
                                    <td><?php echo round(($role['count'] / $stats['total_users']) * 100, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-xs {
    font-size: 0.7rem;
}
.font-weight-bold {
    font-weight: 700 !important;
}
.text-uppercase {
    text-transform: uppercase !important;
}
</style>

<?php require_once '../src/views/footer.php'; ?>
