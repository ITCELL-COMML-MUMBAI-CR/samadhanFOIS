<?php
require_once __DIR__ . '/../../src/utils/SessionManager.php';
SessionManager::start();

// Check if user is logged in
if (!SessionManager::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $_SESSION['user'] ?? null;
$userRole = $_SESSION['user_role'] ?? '';

// Only allow admin and controller roles to access reports
if (!in_array($userRole, ['admin', 'controller', 'viewer'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-chart-line me-2"></i>Comprehensive Reports & Analytics
                    </h1>
                    <p class="text-muted mb-0">Detailed insights and MIS reports for complaint management</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exportReport()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <button class="btn btn-primary" onclick="printReport()">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" id="dateFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" id="dateTo" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select id="reportType" class="form-select">
                                <option value="dashboard">Dashboard Overview</option>
                                <option value="mis">MIS Report</option>
                                <option value="performance">Performance Metrics</option>
                                <option value="pivot">Pivot Table Analysis</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="loadReports()">
                                <i class="fas fa-search me-1"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Generating reports...</p>
    </div>

    <!-- Dashboard Overview -->
    <div id="dashboardReport" class="report-section">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Complaints
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalComplaints">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Resolved
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="resolvedComplaints">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pending
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingComplaints">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Resolution Rate
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="resolutionRate">0%</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Status Distribution Chart -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Complaints by Status</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="#" onclick="exportChart('statusChart')">Export</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Priority Distribution Chart -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Complaints by Priority</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="#" onclick="exportChart('priorityChart')">Export</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="priorityChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Complaints Timeline</h6>
                        <div class="d-flex gap-2">
                            <select id="timelineGroup" class="form-select form-select-sm" style="width: auto;">
                                <option value="day">Daily</option>
                                <option value="week">Weekly</option>
                                <option value="month">Monthly</option>
                            </select>
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="#" onclick="exportChart('timelineChart')">Export</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="timelineChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department and Category Charts -->
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Complaints by Department</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Top Complaint Categories</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MIS Report Section -->
    <div id="misReport" class="report-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Executive Summary</h6>
                    </div>
                    <div class="card-body" id="misSummary">
                        <!-- MIS summary will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Department Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="departmentTable">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Total Complaints</th>
                                        <th>Resolved</th>
                                        <th>Resolution Rate</th>
                                        <th>Avg. Resolution Time (Days)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Monthly Trends</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTrendsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Section -->
    <div id="performanceReport" class="report-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                    </div>
                    <div class="card-body" id="performanceMetrics">
                        <!-- Performance metrics will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">User Activity Analysis</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="userActivityTable">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Department</th>
                                        <th>Total Actions</th>
                                        <th>Forwards</th>
                                        <th>Status Updates</th>
                                        <th>Assignments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pivot Table Section -->
    <div id="pivotReport" class="report-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pivot Table Analysis</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Rows</label>
                                <select id="pivotRows" class="form-select">
                                    <option value="department">Department</option>
                                    <option value="complaint_type">Complaint Type</option>
                                    <option value="priority">Priority</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Columns</label>
                                <select id="pivotColumns" class="form-select">
                                    <option value="status">Status</option>
                                    <option value="priority">Priority</option>
                                    <option value="complaint_type">Complaint Type</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Values</label>
                                <select id="pivotValues" class="form-select">
                                    <option value="count">Count</option>
                                    <option value="avg_resolution_days">Avg Resolution Days</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary w-100" onclick="loadPivotTable()">
                                    <i class="fas fa-refresh me-1"></i>Update
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="pivotTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <!-- Dynamic columns will be added here -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Reports CSS -->
<link rel="stylesheet" href="css/reports.css">

<!-- Reports JavaScript -->
<script src="js/reports.js"></script>

