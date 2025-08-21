<?php
// This file is now a view and should not contain business logic.
// The logic is handled by DashboardController.php
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/dashboard.css">
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
    
    <!-- Timeline Toggle -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="btn-group" role="group" aria-label="Timeline Toggle">
                            <input type="radio" class="btn-check" name="timeline" id="current" value="current" checked>
                            <label class="btn btn-outline-primary" for="current">
                                <i class="fas fa-calendar-day"></i> Current
                            </label>
                            
                            <input type="radio" class="btn-check" name="timeline" id="yesterday" value="yesterday">
                            <label class="btn btn-outline-primary" for="yesterday">
                                <i class="fas fa-calendar-minus"></i> Yesterday
                            </label>
                            
                            <input type="radio" class="btn-check" name="timeline" id="month" value="month">
                            <label class="btn btn-outline-primary" for="month">
                                <i class="fas fa-calendar-alt"></i> This Month
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- First Row: Key Metrics -->
    <div class="row mb-4" id="firstRow">
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card dashboard-card card-total-complaints">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold" id="totalComplaints">-</h3>
                            <p class="mb-0">Total Complaints</p>
                            <small class="text-muted variance-indicator" id="totalComplaintsVariance"></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-6 col-md-12 mb-3">
            <div class="card dashboard-card card-status-bifurcation">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-info"></i> Status Bifurcation
                        </h5>
                        <i class="fas fa-chart-pie fa-2x text-info"></i>
                    </div>
                    <div id="statusBifurcationDetails" class="status-breakdown">
                        <!-- Status breakdown will be loaded here -->
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card dashboard-card card-avg-pendency">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold" id="averagePendency">-</h3>
                            <p class="mb-0">Avg Pendency (Days)</p>
                            <small class="text-muted variance-indicator" id="averagePendencyVariance"></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card dashboard-card card-avg-reply-time">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold" id="averageReplyTime">-</h3>
                            <p class="mb-0">Avg Reply Time (Days)</p>
                            <small class="text-muted variance-indicator" id="averageReplyTimeVariance"></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-reply fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card dashboard-card card-forwards">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold" id="numberOfForwards">-</h3>
                            <p class="mb-0">Number of Forwards</p>
                            <small class="text-muted variance-indicator" id="numberOfForwardsVariance"></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-share fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Second Row: Category and Type Analysis -->
    <div class="row mb-4" id="secondRow">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tags"></i> Category Wise Count
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Type Wise Count
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Third Row: Customer Analytics -->
    <div class="row mb-4" id="thirdRow">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Customer Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="customer-metric">
                                <h3 class="display-6 fw-bold text-primary" id="customersAdded">-</h3>
                                <p class="mb-0">Customers Added</p>
                                <small class="text-muted variance-indicator" id="customersAddedVariance"></small>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="customer-details">
                                <h6>Customer Growth Trends</h6>
                                <p class="text-muted">Track customer acquisition and engagement patterns over time.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Bifurcation Modal -->
    <div class="modal fade" id="statusBifurcationModal" tabindex="-1" aria-labelledby="statusBifurcationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusBifurcationModalLabel">Status Bifurcation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="statusBifurcationDetails">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo BASE_URL; ?>js/dashboard.js"></script>


