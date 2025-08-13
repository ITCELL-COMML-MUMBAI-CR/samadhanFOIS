<?php
/**
 * Grievances to Me Page
 * Controller interface for handling assigned grievances
 */

require_once '../src/utils/SessionManager.php';

// Require controller access
SessionManager::requireRole('controller');

$error = '';
$success = '';
$currentUser = SessionManager::getCurrentUser();

// Get filters
$status = $_GET['status'] ?? '';
$priority = $_GET['priority'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $complaintId = $_POST['complaint_id'] ?? '';
        
        // Validate CSRF token
        if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token');
        }
        
        require_once '../src/models/Complaint.php';
        require_once '../src/models/Transaction.php';
        require_once '../src/models/ComplaintRejection.php';
        
        $complaintModel = new Complaint();
        $transactionModel = new Transaction();
        $rejectionModel = new ComplaintRejection();
        
        switch ($action) {
            case 'update_status':
                $newStatus = $_POST['new_status'] ?? '';
                $actionTaken = sanitizeInput($_POST['action_taken'] ?? '');
                $remarks = sanitizeInput($_POST['remarks'] ?? '');
                
                if (empty($newStatus) || empty($remarks)) {
                    throw new Exception('Status and remarks are required');
                }
                
                // Update complaint status
                $result = $complaintModel->updateStatus($complaintId, $newStatus, $actionTaken);
                
                if ($result) {
                    // Log transaction
                    $transactionModel->logStatusUpdate($complaintId, $remarks, $currentUser['login_id']);
                    $success = 'Grievance status updated successfully!';
                } else {
                    $error = 'Failed to update grievance status.';
                }
                break;
                
            case 'forward':
                $toDepartment = $_POST['to_department'] ?? '';
                $toUser = $_POST['to_user'] ?? '';
                $forwardRemarks = sanitizeInput($_POST['forward_remarks'] ?? '');
                
                if (empty($toDepartment) || empty($forwardRemarks)) {
                    throw new Exception('Department and remarks are required for forwarding');
                }
                
                // Update complaint assignment
                if (!empty($toUser)) {
                    $complaintModel->assignTo($complaintId, $toUser);
                }
                
                // Log forward transaction
                $transactionModel->logForward(
                    $complaintId,
                    $currentUser['login_id'],
                    $toUser,
                    $currentUser['department'],
                    $toDepartment,
                    $forwardRemarks,
                    $currentUser['login_id']
                );
                
                $success = 'Grievance forwarded successfully!';
                break;
                
            case 'reject':
                $rejectionReason = sanitizeInput($_POST['rejection_reason'] ?? '');
                $rejectTo = $_POST['reject_to'] ?? '';
                
                if (empty($rejectionReason)) {
                    throw new Exception('Rejection reason is required');
                }
                
                // Log rejection
                $rejectionModel->logCommercialToConcern($complaintId, $currentUser['login_id'], $rejectTo, $rejectionReason);
                
                // Update complaint status
                $complaintModel->updateStatus($complaintId, 'pending');
                
                // Log transaction
                $transactionModel->logStatusUpdate($complaintId, "Rejected: $rejectionReason", $currentUser['login_id']);
                
                $success = 'Grievance rejected and sent back with remarks.';
                break;
                
            case 'assign_priority':
                $newPriority = $_POST['new_priority'] ?? '';
                
                if (empty($newPriority)) {
                    throw new Exception('Priority is required');
                }
                
                $result = $complaintModel->updatePriority($complaintId, $newPriority);
                
                if ($result) {
                    $transactionModel->logStatusUpdate($complaintId, "Priority updated to: $newPriority", $currentUser['login_id']);
                    $success = 'Priority updated successfully!';
                } else {
                    $error = 'Failed to update priority.';
                }
                break;
        }
        
    } catch (Exception $e) {
        error_log('Controller action error: ' . $e->getMessage());
        $error = $e->getMessage();
    }
}

// Get grievances assigned to current user
$grievances = [];
$totalCount = 0;

try {
    require_once '../src/models/Complaint.php';
    $complaintModel = new Complaint();
    
    // Build filter conditions
    $filters = [
        'assigned_to' => $currentUser['login_id']
    ];
    
    if (!empty($status)) {
        $filters['status'] = $status;
    }
    
    if (!empty($priority)) {
        $filters['priority'] = $priority;
    }
    
    // Get filtered grievances
    if (!empty($search)) {
        $grievances = $complaintModel->search($search, $filters);
    } else {
        $grievances = $complaintModel->findAssignedTo($currentUser['login_id']);
        
        // Apply additional filters
        if (!empty($status) || !empty($priority)) {
            $grievances = array_filter($grievances, function($g) use ($status, $priority) {
                if (!empty($status) && $g['status'] !== $status) return false;
                if (!empty($priority) && $g['priority'] !== $priority) return false;
                return true;
            });
        }
    }
    
    $totalCount = count($grievances);
    
    // Apply pagination
    $grievances = array_slice($grievances, $offset, $limit);
    
} catch (Exception $e) {
    $error = 'Unable to load grievances.';
}

// Get department users for forwarding
$departmentUsers = [];
$departments = ['COMMERCIAL', 'OPERATING', 'MECHANICAL', 'ELECTRICAL', 'ENGINEERING', 'SECURITY', 'MEDICAL', 'ACCOUNTS', 'PERSONNEL'];

try {
    require_once '../src/models/User.php';
    $userModel = new User();
    
    foreach ($departments as $dept) {
        $departmentUsers[$dept] = $userModel->findByDepartment($dept);
    }
} catch (Exception $e) {
    // Handle silently
}

// Calculate pagination
$totalPages = ceil($totalCount / $limit);
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
            <div class="card dashboard-card card-resolved text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php 
                        $inProgressCount = count(array_filter($grievances, fn($g) => $g['status'] === 'in_progress'));
                        echo $inProgressCount;
                        ?>
                    </h3>
                    <p class="mb-0">In Progress</p>
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
                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Low</option>
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
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grievances as $grievance): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($grievance['complaint_id']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($grievance['complaint_type']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($grievance['complaint_subtype']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($grievance['customer_name'] ?? 'Unknown'); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($grievance['customer_id']); ?></small>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                        <?php echo htmlspecialchars($grievance['location']); ?>
                                    </td>
                                    <td>
                                        <span class="badge priority-<?php echo $grievance['priority']; ?>">
                                            <?php echo ucfirst($grievance['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-<?php echo str_replace('_', '-', $grievance['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $grievance['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('d-M-Y', strtotime($grievance['date'])); ?>
                                            <br>
                                            <?php echo date('H:i', strtotime($grievance['time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="viewGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="updateStatus('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="forwardGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="Forward">
                                                <i class="fas fa-share"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="rejectGrievance('<?php echo $grievance['complaint_id']; ?>')"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Update Grievance Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="complaint_id" id="updateComplaintId">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="new_status" required>
                                    <option value="">Select Status</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <label>New Status *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="new_priority">
                                    <option value="">Keep Current</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                                <label>Priority</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="action_taken" placeholder="Action Taken" style="height: 100px"></textarea>
                        <label>Action Taken</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="remarks" placeholder="Remarks" style="height: 80px" required></textarea>
                        <label>Internal Remarks *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-railway-primary">
                        <i class="fas fa-save"></i> Update Status
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times text-danger"></i> Reject Grievance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="complaint_id" id="rejectComplaintId">
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This will send the grievance back with rejection remarks.
                    </div>
                    
                    <div class="form-floating mb-3">
                        <select class="form-select" name="reject_to">
                            <option value="">Select User (Optional)</option>
                            <?php foreach ($departmentUsers[$currentUser['department']] ?? [] as $user): ?>
                                <option value="<?php echo $user['login_id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Reject to User</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="rejection_reason" placeholder="Rejection Reason" style="height: 120px" required></textarea>
                        <label>Rejection Reason *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject
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

function updateStatus(complaintId) {
    document.getElementById('updateComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function forwardGrievance(complaintId) {
    document.getElementById('forwardComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('forwardModal'));
    modal.show();
}

function rejectGrievance(complaintId) {
    document.getElementById('rejectComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
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
