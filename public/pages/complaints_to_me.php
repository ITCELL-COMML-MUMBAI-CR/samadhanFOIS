<?php
/**
 * Grievances to Me Page View
 * Displays grievances assigned to the current controller
 */

// Data is passed from the controller
// $grievances, $totalCount, $currentUser, $error, $success, $status, $priority, $search, $page, $totalPages, $departmentUsers, $departments

// Check if data is being passed correctly
if (!isset($currentUser)) {
    echo '<div class="alert alert-danger">Error: No user data available</div>';
    return;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-tasks text-primary"></i> Grievances Assigned to Me
                </h1>
                <div>
                    <span class="badge bg-info fs-6">
                        Department: <?php echo htmlspecialchars($currentUser['department']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card card-complaints text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $totalCount; ?></h3>
                    <p class="mb-0">Total Assigned</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card card-pending text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php 
                        $pendingCount = count(array_filter($grievances, fn($g) => $g['status'] === 'pending'));
                        echo $pendingCount;
                        ?>
                    </h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
                            <div class="card dashboard-card card-replied text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php 
                        $inProgressCount = 0;
                        echo $inProgressCount;
                        ?>
                    </h3>
                    <p class="mb-0">—</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card card-users text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php 
                        $highPriorityCount = count(array_filter($grievances, fn($g) => in_array($g['priority'], ['high', 'critical'])));
                        echo $highPriorityCount;
                        ?>
                    </h3>
                    <p class="mb-0">High Priority</p>
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
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="replied" <?php echo $status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="priority">
                        <option value="">All Priorities</option>
                        <option value="normal" <?php echo $priority === 'normal' ? 'selected' : ''; ?>>Normal</option>
                        <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="critical" <?php echo $priority === 'critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search grievances..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-railway-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Grievances List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Assigned Grievances
                    <?php if ($totalCount > 0): ?>
                        <span class="badge bg-secondary"><?php echo $totalCount; ?></span>
                    <?php endif; ?>
                </h5>
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPage()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($grievances)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No grievances assigned</h5>
                    <p class="text-muted">You don't have any grievances assigned to you at the moment.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center align-middle">ID</th>
                                <th class="text-center align-middle">Category</th>
                                <th class="text-center align-middle">Type</th>
                                <th class="text-center align-middle">Customer</th>
                                <th class="text-center align-middle">Location</th>
                                <th class="text-center align-middle">Priority</th>
                                <th class="text-center align-middle">Status</th>
                                <th class="text-center align-middle">Date</th>
                                <th class="text-center align-middle" width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grievances as $grievance): ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <small class="text-muted"><?php echo htmlspecialchars($grievance['complaint_id']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if (!empty($grievance['category'])): ?>
                                            <span class="badge bg-success"><?php echo htmlspecialchars($grievance['category']); ?></span>
                                        <?php else: ?>
                                            <small class="text-muted">N/A</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <strong><?php echo htmlspecialchars($grievance['Type']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($grievance['Subtype']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <strong><?php echo htmlspecialchars($grievance['customer_name'] ?? 'Unknown'); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($grievance['customer_id']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                        <?php echo htmlspecialchars($grievance['location']); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge priority-<?php echo $grievance['display_priority']; ?>">
                                            <?php echo ucfirst($grievance['display_priority']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge status-<?php echo str_replace('_', '-', $grievance['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $grievance['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <small>
                                            <?php echo date('d-M-Y', strtotime($grievance['date'])); ?>
                                            <br>
                                            <?php echo date('H:i', strtotime($grievance['time'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="viewGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="View Complaint">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="forwardGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="Forward Complaint">
                                                <i class="fas fa-share"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="closeGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="Close Complaint (Action taken + internal remarks)">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                            <?php if (strtoupper($currentUser['department'] ?? '') === 'COMMERCIAL'): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="revertGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="Revert back to customer">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&priority=<?php echo $priority; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&priority=<?php echo $priority; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&priority=<?php echo $priority; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Close Complaint Modal -->
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Close Complaint
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="close">
                <input type="hidden" name="complaint_id" id="closeComplaintId">
                
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="action_taken" placeholder="Action Taken" style="height: 100px" required></textarea>
                        <label>Action Taken *</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="remarks" placeholder="Remarks" style="height: 80px" required></textarea>
                        <label>Internal Remarks *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-railway-primary">
                        <i class="fas fa-check"></i> Send for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Forward Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-share"></i> Forward Grievance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="forward">
                <input type="hidden" name="complaint_id" id="forwardComplaintId">
                
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <select class="form-select" name="to_department" id="toDepartment" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Forward to Department *</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <select class="form-select" name="to_user" id="toUser">
                            <option value="">Select User (Optional)</option>
                        </select>
                        <label>Assign to User</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="forward_remarks" placeholder="Forward Remarks" style="height: 100px" required></textarea>
                        <label>Forward Remarks *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-share"></i> Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Revert Modal (Commercial only) -->
<div class="modal fade" id="revertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-undo text-danger"></i> Revert back to customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="revert">
                <input type="hidden" name="complaint_id" id="revertComplaintId">
                
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="rejection_reason" placeholder="Remarks to customer (what more info is needed)" style="height: 120px" required></textarea>
                        <label>Remarks to Customer *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo"></i> Revert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Department users data
const departmentUsers = <?php echo json_encode($departmentUsers); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const toDepartmentSelect = document.getElementById('toDepartment');
    const toUserSelect = document.getElementById('toUser');
    
    // Update user dropdown when department changes
    toDepartmentSelect.addEventListener('change', function() {
        const department = this.value;
        toUserSelect.innerHTML = '<option value="">Select User (Optional)</option>';
        
        if (department && departmentUsers[department]) {
            departmentUsers[department].forEach(user => {
                const option = document.createElement('option');
                option.value = user.login_id;
                option.textContent = user.name;
                toUserSelect.appendChild(option);
            });
        }
    });
});

function viewGrievance(complaintId) {
    window.open('<?php echo BASE_URL; ?>grievances/view/' + complaintId, '_blank');
}

function closeGrievance(complaintId) {
    document.getElementById('closeComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('closeModal'));
    modal.show();
}

function forwardGrievance(complaintId) {
    document.getElementById('forwardComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('forwardModal'));
    modal.show();
}

function revertGrievance(complaintId) {
    document.getElementById('revertComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('revertModal'));
    modal.show();
}

function refreshPage() {
    window.location.reload();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        window.location.reload();
    }
}, 300000);
</script>

<style>
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .display-6 {
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .col-xl-3 {
        margin-bottom: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
        border-radius: 0.25rem !important;
    }
    
    .table td, .table th {
        padding: 0.5rem 0.25rem;
        font-size: 0.8rem;
    }
}
</style>
