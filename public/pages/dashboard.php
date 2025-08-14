<?php
// This file is now a view and should not contain business logic.
// The logic is handled by DashboardController.php
?>
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-dashboard text-primary"></i> Dashboard
                    </h1>
                    <p class="text-muted mb-0">
                        Welcome back, <?php echo htmlspecialchars($currentUser['name']); ?>! 
                        <span class="badge bg-secondary"><?php echo ucfirst($currentUser['role']); ?></span>
                        <?php if (!empty($currentUser['department'])): ?>
                            <span class="badge bg-info"><?php echo $currentUser['department']; ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-complaints">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="display-6 fw-bold"><?php echo $statistics['total'] ?? 0; ?></h3>
                            <p class="mb-0">Total Grievances</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-pending">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="display-6 fw-bold"><?php echo $statistics['by_status']['pending'] ?? 0; ?></h3>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-resolved">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="display-6 fw-bold"><?php echo ($statistics['by_status']['resolved'] ?? 0) + ($statistics['by_status']['closed'] ?? 0); ?></h3>
                            <p class="mb-0">Resolved</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-users">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="display-6 fw-bold"><?php echo $statistics['by_status']['in_progress'] ?? 0; ?></h3>
                            <p class="mb-0">In Progress</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-spinner fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Status Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Priority Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Priority Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Grievances -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Recent Grievances
                        </h5>
                        <a href="<?php echo BASE_URL; ?>complaints" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recentGrievances)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No recent grievances found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <?php if ($userRole !== 'customer'): ?>
                                            <th>Customer</th>
                                        <?php endif; ?>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentGrievances as $grievance): ?>
                                        <tr onclick="viewGrievance('<?php echo $grievance['complaint_id']; ?>')" style="cursor: pointer;">
                                            <td><small><?php echo htmlspecialchars($grievance['complaint_id']); ?></small></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($grievance['complaint_type']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($grievance['complaint_subtype']); ?></small>
                                            </td>
                                            <?php if ($userRole !== 'customer'): ?>
                                                <td><?php echo htmlspecialchars($grievance['customer_name'] ?? 'Unknown'); ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <span class="badge status-<?php echo str_replace('_', '-', $grievance['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $grievance['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge priority-<?php echo $grievance['priority']; ?>">
                                                    <?php echo ucfirst($grievance['priority']); ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo date('d-M-Y', strtotime($grievance['date'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions & Recent Transactions -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($userRole === 'customer'): ?>
                            <a href="<?php echo BASE_URL; ?>complaints/new" class="btn btn-railway-primary btn-sm">
                                <i class="fas fa-plus-circle"></i> Submit New Grievance
                            </a>
                            <a href="<?php echo BASE_URL; ?>complaints/my" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list"></i> View My Grievances
                            </a>
                        <?php elseif ($userRole === 'controller'): ?>
                            <a href="<?php echo BASE_URL; ?>complaints/tome" class="btn btn-railway-primary btn-sm">
                                <i class="fas fa-tasks"></i> Grievances to Me
                            </a>
                            <a href="<?php echo BASE_URL; ?>complaints" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list"></i> All Grievances
                            </a>
                        <?php elseif ($userRole === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>admin/categories" class="btn btn-railway-primary btn-sm">
                                <i class="fas fa-tags"></i> Manage Categories
                            </a>
                            <a href="<?php echo BASE_URL; ?>customer/add" class="btn btn-success btn-sm">
                                <i class="fas fa-user-plus"></i> Add Customer
                            </a>
                            <a href="<?php echo BASE_URL; ?>register" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-users"></i> Add User
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/reports" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>complaints" class="btn btn-railway-primary btn-sm">
                                <i class="fas fa-list"></i> View Grievances
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/reports" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history"></i> Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recentTransactions)): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-clock text-muted"></i>
                            <p class="text-muted mb-0">No recent activity</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($recentTransactions as $transaction): ?>
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <?php echo ucfirst(str_replace('_', ' ', $transaction['transaction_type'])); ?>
                                        </small>
                                        <small class="text-muted">
                                            <?php echo date('d-M H:i', strtotime($transaction['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 small">
                                        <?php echo htmlspecialchars(substr($transaction['remarks'], 0, 100)); ?>
                                        <?php if (strlen($transaction['remarks']) > 100): ?>...<?php endif; ?>
                                    </p>
                                    <?php if (!empty($transaction['complaint_type'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($transaction['complaint_type']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Information (Admin Only) -->
    <?php if ($userRole === 'admin' && !empty($dashboardData['user_stats'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> System Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary"><?php echo $dashboardData['user_stats']['total']; ?></h4>
                                    <p class="mb-0">Total Users</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success"><?php echo $dashboardData['user_stats']['active']; ?></h4>
                                    <p class="mb-0">Active Users</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info"><?php echo $dashboardData['user_stats']['by_role']['controller'] ?? 0; ?></h4>
                                    <p class="mb-0">Controllers</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning"><?php echo $dashboardData['user_stats']['by_role']['customer'] ?? 0; ?></h4>
                                    <p class="mb-0">Customers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart data
const statusData = <?php echo json_encode($statusChartData); ?>;
const priorityData = <?php echo json_encode($priorityChartData); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    if (statusData.length > 0) {
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.label),
                datasets: [{
                    data: statusData.map(item => item.value),
                    backgroundColor: statusData.map(item => item.color),
                    borderWidth: 2,
                    borderColor: '#fff'
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
    }
    
    // Priority Distribution Chart
    if (priorityData.length > 0) {
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: priorityData.map(item => item.label),
                datasets: [{
                    label: 'Count',
                    data: priorityData.map(item => item.value),
                    backgroundColor: priorityData.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});

function viewGrievance(complaintId) {
    window.open('<?php echo BASE_URL; ?>complaints/view/' + complaintId, '_blank');
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        window.location.reload();
    }
}, 300000);
</script>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    border-left: 2px solid #e2e8f0;
    padding-left: 1rem;
    margin-bottom: 1rem;
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -5px;
    top: 5px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--railway-blue);
}

.timeline-item:last-child {
    border-left: none;
}

@media (max-width: 768px) {
    .display-6 {
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .timeline-item {
        padding-left: 0.75rem;
        margin-bottom: 0.75rem;
    }
}

@media (max-width: 576px) {
    .col-xl-3, .col-lg-6, .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
    
    .table td, .table th {
        padding: 0.5rem 0.25rem;
        font-size: 0.8rem;
    }
}

/* Chart container responsive height */
#statusChart, #priorityChart {
    max-height: 300px;
}

@media (max-width: 768px) {
    #statusChart, #priorityChart {
        max-height: 250px;
    }
}
</style>
