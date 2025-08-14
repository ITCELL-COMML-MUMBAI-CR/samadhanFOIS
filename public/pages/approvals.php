<?php
/**
 * Commercial Approvals Page
 * Lists complaints awaiting Commercial approval and allows approval action
 */

require_once '../src/utils/SessionManager.php';

// Require controller access and Commercial department
SessionManager::requireRole('controller');
$currentUser = SessionManager::getCurrentUser();
if (strtoupper($currentUser['department'] ?? '') !== 'COMMERCIAL') {
    if (!headers_sent()) {
        header('Location: ' . BASE_URL . 'dashboard?error=access_denied');
        exit;
    }
}

$error = '';
$success = '';

// Check for session alerts (from redirect after POST)
if (isset($_SESSION['alert_message'])) {
    if ($_SESSION['alert_type'] === 'success') {
        $success = $_SESSION['alert_message'];
    } else {
        $error = $_SESSION['alert_message'];
    }
    unset($_SESSION['alert_message'], $_SESSION['alert_type']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid security token');
        }

        $action = $_POST['action'] ?? '';
        $complaintId = $_POST['complaint_id'] ?? '';
        $remarks = sanitizeInput($_POST['remarks'] ?? '');

        require_once '../src/models/Complaint.php';
        require_once '../src/models/Transaction.php';
        require_once '../src/models/User.php';
        require_once '../src/utils/EmailService.php';

        $complaintModel = new Complaint();
        $transactionModel = new Transaction();
        $userModel = new User();
        $emailService = new EmailService();

        if ($action === 'approve') {
            $complaint = $complaintModel->findByComplaintId($complaintId);
            if (!$complaint) {
                throw new Exception('Complaint not found');
            }
            $customerId = $complaint['customer_id'] ?? null;
            $customerUser = $customerId ? $userModel->findByCustomerId($customerId) : null;
            $customerLoginId = $customerUser['login_id'] ?? null;

            // Update status to resolved and assign to customer
            $complaintModel->updateStatus($complaintId, 'resolved');
            if ($customerLoginId) {
                $complaintModel->assignTo($complaintId, $customerLoginId);
            }

            // Log approval
            $transactionModel->logStatusUpdate($complaintId, 'Commercial approval granted. ' . ($remarks ? ('Remarks: ' . $remarks) : ''), $currentUser['login_id']);

            // Email customer about resolved status
            $customerEmail = $complaint['customer_email'] ?? '';
            $customerName = $complaint['customer_name'] ?? 'Valued Customer';
            if ($customerEmail && EmailService::isValidEmail($customerEmail)) {
                $emailService->sendStatusUpdate($customerEmail, $customerName, $complaintId, 'awaiting_approval', 'resolved', $remarks);
            }

            // Set success message in session and redirect to prevent resubmission
            $_SESSION['alert_message'] = 'Action taken approved and sent to customer for feedback.';
            $_SESSION['alert_type'] = 'success';
            header('Location: ' . BASE_URL . 'grievances/approvals');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['alert_message'] = $e->getMessage();
        $_SESSION['alert_type'] = 'error';
        header('Location: ' . BASE_URL . 'grievances/approvals');
        exit;
    }
}

// Fetch approvals list
require_once '../src/models/Complaint.php';
$complaintModel = new Complaint();
// Show all complaints awaiting approval, regardless of current assignment
$approvals = $complaintModel->findByStatus('awaiting_approval');

// Debug: Show all complaints to see what statuses exist
$allComplaints = $complaintModel->search('');
$statusCount = [];
foreach ($allComplaints as $c) {
    $status = $c['status'] ?? 'null';
    $statusCount[$status] = ($statusCount[$status] ?? 0) + 1;
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
                <!-- Debug info -->
                <small class="text-muted">
                    Statuses in DB: <?php foreach($statusCount as $s => $c) echo "$s($c) "; ?>
                </small>
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
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th width="160">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvals as $row): ?>
                            <tr>
                                <td><small class="text-muted"><?php echo htmlspecialchars($row['complaint_id']); ?></small></td>
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


