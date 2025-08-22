<?php
/**
 * Admin Reports & Analytics Page
 * Provides comprehensive reporting and analytics for administrators
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

// Get statistics
$stats = [];

// Total complaints
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM complaints");
$stmt->execute();
$stats['total_complaints'] = $stmt->fetch()['count'];

// Complaints by status
$stmt = $connection->prepare("
    SELECT status, COUNT(*) as count 
    FROM complaints 
    GROUP BY status
");
$stmt->execute();
$stats['by_status'] = $stmt->fetchAll();

// Complaints by department
$stmt = $connection->prepare("
    SELECT department, COUNT(*) as count 
    FROM complaints 
    GROUP BY department
");
$stmt->execute();
$stats['by_department'] = $stmt->fetchAll();

// Complaints by type
$stmt = $connection->prepare("
    SELECT Type, COUNT(*) as count 
    FROM complaints 
    GROUP BY Type
");
$stmt->execute();
$stats['by_type'] = $stmt->fetchAll();

// Recent complaints (last 30 days)
$stmt = $connection->prepare("
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stmt->execute();
$stats['recent_30_days'] = $stmt->fetch()['count'];

// Average resolution time
$stmt = $connection->prepare("
    SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days
    FROM complaints 
    WHERE status IN ('Closed', 'Replied') 
    AND updated_at > created_at
");
$stmt->execute();
$stats['avg_resolution_days'] = round($stmt->fetch()['avg_days'] ?? 0, 1);

// User statistics
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['count'];

// Users by role
$stmt = $connection->prepare("
    SELECT role, COUNT(*) as count 
    FROM users 
    GROUP BY role
");
$stmt->execute();
$stats['users_by_role'] = $stmt->fetchAll();

// Monthly trend (last 6 months)
$stmt = $connection->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM complaints 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");
$stmt->execute();
$stats['monthly_trend'] = $stmt->fetchAll();

// Top locations - removed as Location column is being dropped
$stats['top_locations'] = [];

// Customer satisfaction (ratings)
$stmt = $connection->prepare("
    SELECT rating, COUNT(*) as count 
    FROM complaints 
    WHERE rating IS NOT NULL
    GROUP BY rating
");
$stmt->execute();
$stats['ratings'] = $stmt->fetchAll();

// Set page title for header
$pageTitle = 'Analytics & Reports';

// Include header
require_once '../src/views/header.php';
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-chart-line text-primary"></i> Analytics & Reports</h1>
            <div class="btn-group" role="group">
                <button class="btn btn-outline-primary btn-sm" onclick="exportReport('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="exportReport('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Recent (30 Days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['recent_30_days']); ?></div>
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
                                Avg Resolution (Days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['avg_resolution_days']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Complaints by Status -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Complaints by Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Complaints by Department -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Complaints by Department</h6>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend -->
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Complaint Trend (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" width="400" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row mb-4">
        <!-- Top Locations - Removed as Location column is being dropped -->

        <!-- Customer Ratings -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Satisfaction Ratings</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['ratings'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Rating</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalRatings = array_sum(array_column($stats['ratings'], 'count'));
                                    foreach ($stats['ratings'] as $rating): 
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?php echo $rating['rating'] === 'Excellent' ? 'success' : ($rating['rating'] === 'Satisfactory' ? 'warning' : 'danger'); ?>">
                                                <?php echo $rating['rating']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $rating['count']; ?></td>
                                        <td><?php echo round(($rating['count'] / $totalRatings) * 100, 1); ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No ratings available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($stats['users_by_role'] as $role): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?php echo ucfirst($role['role']); ?>s
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $role['count']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($stats['by_status'], 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($stats['by_status'], 'count')); ?>,
            backgroundColor: [
                '#1cc88a', // Success
                '#f6c23e', // Warning
                '#e74a3b', // Danger
                '#36b9cc', // Info
                '#858796'  // Secondary
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Department Chart
const deptCtx = document.getElementById('departmentChart').getContext('2d');
const deptChart = new Chart(deptCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($stats['by_department'], 'department')); ?>,
        datasets: [{
            label: 'Complaints',
            data: <?php echo json_encode(array_column($stats['by_department'], 'count')); ?>,
            backgroundColor: '#4e73df'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($stats['monthly_trend'], 'month')); ?>,
        datasets: [{
            label: 'Complaints',
            data: <?php echo json_encode(array_column($stats['monthly_trend'], 'count')); ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Export functions
function exportReport(format) {
    // Implementation for PDF/Excel export
    alert('Export functionality will be implemented here for ' + format + ' format.');
}
</script>

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
