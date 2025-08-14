<?php
/**
 * Commercial Approvals Page View
 * Lists complaints awaiting Commercial approval and allows approval action
 */

// Data is passed from the controller
// $approvals, $currentUser, $error, $success

// Check if data is being passed correctly
if (!isset($currentUser)) {
    echo '<div class="alert alert-danger">Error: No user data available</div>';
    return;
}
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check text-primary"></i>
                Approvals Queue
            </h1>
            <div>
                <span class="badge bg-info">Commercial</span>
            </div>
        </div>
    </div>

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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table"></i> Awaiting Approval</h5>
            <span class="badge bg-secondary"><?php echo count($approvals); ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($approvals)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No items awaiting approval</h5>
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
                                <th class="text-center align-middle">Date</th>
                                <th class="text-center align-middle" width="160">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvals as $row): ?>
                            <tr>
                                <td><small class="text-muted"><?php echo htmlspecialchars($row['complaint_id']); ?></small></td>
                                <td>
                                    <?php if (!empty($row['category'])): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($row['category']); ?></span>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['complaint_type']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['complaint_subtype']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['customer_id']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td>
                                    <small>
                                        <?php echo date('d-M-Y', strtotime($row['date'])); ?>
                                        <br>
                                        <?php echo date('H:i', strtotime($row['time'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>grievances/view/<?php echo $row['complaint_id']; ?>" target="_blank" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success" onclick="approve('<?php echo $row['complaint_id']; ?>')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check"></i> Approve Action Taken</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="complaint_id" id="approveComplaintId">
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="remarks" placeholder="Optional remarks" style="height: 120px"></textarea>
                        <label>Remarks (optional)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                </div>
            </form>
        </div>
    </div>
    
</div>

<script>
function approve(complaintId) {
    document.getElementById('approveComplaintId').value = complaintId;
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}
</script>

<style>
@media (max-width: 768px) {
    .table-responsive { font-size: 0.9rem; }
}
</style>


