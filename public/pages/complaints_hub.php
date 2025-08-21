<?php
/**
 * Three-Column Complaints Hub Layout
 * Filters | Complaints List | Complaint Details
 */

// Data is passed from the controller
// $grievances, $totalCount, $currentUser, $error, $success, $status, $priority, $department, $search, $view, $page, $totalPages, $departmentUsers, $departments, $statistics

// Check if data is being passed correctly
if (!isset($currentUser)) {
    echo '<div class="alert alert-danger">Error: No user data available</div>';
    return;
}
?>

<!-- Three-Column Complaints Hub Layout -->
<div class="three-column-layout">
    <!-- First Column: Filters -->
    <div class="filters-column">
        <div class="filters-header">
            <h5 class="filters-title">
                <i class="fas fa-filter text-primary"></i> Filters
            </h5>
        </div>
        
        <div class="filters-list">
            <div class="filter-item <?php echo $view === 'all' && empty($status) ? 'active' : ''; ?>" data-filter="all">
                <i class="fas fa-globe"></i>
                <span>All Complaints</span>
                <span class="filter-count"><?php echo $statistics['total']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $view === 'assigned' ? 'active' : ''; ?>" data-filter="assigned">
                <i class="fas fa-user-check"></i>
                <span>Assigned to Me</span>
                <span class="filter-count"><?php echo $statistics['assigned']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'pending' ? 'active' : ''; ?>" data-filter="pending">
                <i class="fas fa-clock"></i>
                <span>Pending</span>
                <span class="filter-count"><?php echo $statistics['pending']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'replied' ? 'active' : ''; ?>" data-filter="replied">
                <i class="fas fa-reply"></i>
                <span>Replied</span>
                <span class="filter-count"><?php echo $statistics['replied']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'closed' ? 'active' : ''; ?>" data-filter="closed">
                <i class="fas fa-check-circle"></i>
                <span>Closed</span>
                <span class="filter-count"><?php echo $statistics['closed']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'forwarded' ? 'active' : ''; ?>" data-filter="forwarded">
                <i class="fas fa-share"></i>
                <span>Forwarded</span>
                <span class="filter-count">0</span>
            </div>
            
            <div class="filter-item <?php echo $status === 'reverted' ? 'active' : ''; ?>" data-filter="reverted">
                <i class="fas fa-undo"></i>
                <span>Reverted</span>
                <span class="filter-count">0</span>
            </div>
            
            <div class="filter-item <?php echo $status === 'awaiting_approval' ? 'active' : ''; ?>" data-filter="awaiting_approval">
                <i class="fas fa-clock"></i>
                <span>Pending Approval</span>
                <span class="filter-count"><?php echo $statistics['awaiting_approval'] ?? 0; ?></span>
            </div>
        </div>
    </div>

    <!-- Second Column: Complaints List -->
    <div class="list-column">
        <div class="list-header">
            <h5 class="list-title">
                <i class="fas fa-list text-primary"></i> 
                <span id="listTitle">All Complaints</span>
                <span class="list-subtitle">as per filters</span>
            </h5>
            <div class="list-actions">
                <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Date Filter -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="date-filter-group">
                    <label for="dateFilter" class="filter-label">
                        <i class="fas fa-calendar"></i> Date Filter
                    </label>
                    <select class="form-control form-control-sm" id="dateFilter">
                        <option value="">All Dates</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="custom-date-group" id="customDateGroup" style="display: none;">
                    <div class="date-inputs">
                        <input type="date" class="form-control form-control-sm" id="startDate" placeholder="Start Date">
                        <span class="date-separator">to</span>
                        <input type="date" class="form-control form-control-sm" id="endDate" placeholder="End Date">
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-section">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Search complaints..." 
                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button type="button" class="search-clear-btn" id="searchClearBtn" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Priority Filter -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="priority-filter-group">
                    <label for="priorityFilter" class="filter-label">
                        <i class="fas fa-flag"></i> Priority Filter
                    </label>
                    <select class="form-control form-control-sm" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
                <div class="sort-group">
                    <label class="filter-label">
                        <i class="fas fa-sort"></i> Sort By
                    </label>
                    <select class="form-control form-control-sm" id="sortFilter">
                        <option value="priority">Priority (High to Low)</option>
                        <option value="date">Date (Newest First)</option>
                        <option value="date_old">Date (Oldest First)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="complaints-list" id="complaintsList">
            <?php if (empty($grievances)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox fa-2x text-muted"></i>
                    </div>
                    <h6 class="empty-title">No complaints found</h6>
                    <p class="empty-message">
                        No complaints match your current filters.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($grievances as $grievance): ?>
                    <?php 
                    // Debug: Check what data we have
                    // echo "<!-- Debug: " . json_encode($grievance) . " -->"; 
                    ?>
                    <div class="complaint-item" data-complaint-id="<?php echo htmlspecialchars($grievance['complaint_id'] ?? ''); ?>">
                        <div class="complaint-header">
                            <div class="complaint-id">
                                <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($grievance['complaint_id'] ?? ''); ?>
                            </div>
                            <div class="complaint-time">
                                <?php echo date('d M Y, H:i', strtotime($grievance['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="complaint-customer">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($grievance['customer_name'] ?? 'Unknown'); ?>
                        </div>
                        
                        <div class="complaint-type">
                            <strong><?php echo htmlspecialchars($grievance['Type'] ?? 'Not specified'); ?></strong>
                            <?php if (!empty($grievance['Subtype'])): ?>
                                <span class="subtype">- <?php echo htmlspecialchars($grievance['Subtype'] ?? ''); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="complaint-preview">
                            <?php echo htmlspecialchars(substr($grievance['description'] ?? '', 0, 100)); ?>
                            <?php if (strlen($grievance['description'] ?? '') > 100): ?>
                                <span class="text-muted">...</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="complaint-badges">
                            <?php 
                            $priority = $grievance['display_priority'] ?? $grievance['priority'] ?? 'medium';
                            $status = $grievance['status'] ?? 'pending';
                            ?>
                            <?php if (strtolower($status) !== 'closed'): ?>
                                <span class="badge priority-<?php echo strtolower($priority); ?>">
                                    <i class="fas fa-flag"></i> <?php echo ucfirst(strtolower($priority)); ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge status-<?php echo str_replace('_', '-', strtolower($status)); ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Third Column: Complaint Details -->
    <div class="details-column">
        <!-- Default State -->
        <div class="default-state" id="defaultState">
            <div class="default-content">
                <div class="default-icon">
                    <i class="fas fa-file-alt fa-3x text-muted"></i>
                </div>
                <h5 class="default-title">Select a Complaint</h5>
                <p class="default-message">
                    Choose a complaint from the list to view full details and take action.
                </p>
            </div>
        </div>

        <!-- Complaint Details (Hidden by default) -->
        <div class="complaint-details" id="complaintDetails" style="display: none;">
            <!-- Complaint Header with Number and Actions -->
            <div class="complaint-detail-header">
                <div class="complaint-number-section">
                    <h5 class="complaint-number" id="detailComplaintId">
                        <i class="fas fa-hashtag"></i> <span id="complaintIdText"></span>
                    </h5>
                    <div class="complaint-badges-section">
                        <div class="complaint-status-badge" id="detailStatus"></div>
                        <div class="complaint-priority-badge" id="detailPriority" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-sm btn-outline-primary" id="viewDetailsBtn" title="View Full Details">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-warning" id="forwardBtn" title="Forward Complaint">
                        <i class="fas fa-share"></i> Forward
                    </button>
                    <button class="btn btn-sm btn-outline-success" id="closeBtn" title="Close Complaint">
                        <i class="fas fa-check-circle"></i> Close
                    </button>
                    <?php if (strtoupper($currentUser['department'] ?? '') === 'COMMERCIAL'): ?>
                        <button class="btn btn-sm btn-outline-danger" id="revertBtn" title="Revert to Customer">
                            <i class="fas fa-undo"></i> Revert
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Complaint Information -->
            <div class="complaint-info-section">
                <div class="info-group">
                    <label class="info-label">Customer Information</label>
                    <div class="info-content" id="detailCustomerInfo"></div>
                </div>
                
                <div class="info-group">
                    <label class="info-label">Complaint Type</label>
                    <div class="info-content" id="detailComplaintType"></div>
                </div>
                
                <div class="info-group">
                    <label class="info-label">Location</label>
                    <div class="info-content" id="detailLocation"></div>
                </div>
                
                <div class="info-group">
                    <label class="info-label">Priority</label>
                    <div class="info-content" id="detailPriority"></div>
                </div>
                
                <div class="info-group">
                    <label class="info-label">Status</label>
                    <div class="info-content" id="detailStatusInfo"></div>
                </div>
                
                <div class="info-group">
                    <label class="info-label">Created Date</label>
                    <div class="info-content" id="detailCreatedDate"></div>
                </div>
                
                <div class="info-group full-width">
                    <label class="info-label">Description</label>
                    <div class="info-content description-text" id="detailDescription"></div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="transaction-history">
                <h6 class="history-title">
                    <i class="fas fa-history"></i> Transaction History
                </h6>
                <div class="history-list" id="transactionHistory">
                    <!-- Transaction history will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'modals/close_complaint_modal.php'; ?>
<?php include 'modals/forward_complaint_modal.php'; ?>
<?php include 'modals/revert_complaint_modal.php'; ?>

<script>
// Department users data
const departmentUsers = <?php echo json_encode($departmentUsers); ?>;
const currentView = '<?php echo $view; ?>';
const currentStatus = '<?php echo $status; ?>';
</script>
<script src="<?php echo BASE_URL; ?>js/complaints_hub.js"></script>
