<?php
/**
 * Admin User Management Page
 * Provides comprehensive user management for administrators
 */

require_once '../src/utils/SessionManager.php';

// Require admin access
SessionManager::requireRole('admin');

// Get current user
$currentUser = SessionManager::getCurrentUser();

// Load models for data
require_once '../src/models/BaseModel.php';
require_once '../src/models/User.php';
require_once '../src/utils/Helpers.php';
require_once '../src/utils/Database.php';

$db = Database::getInstance();
$connection = $db->getConnection();

// Get filters
$filters = [
    'search' => sanitizeInput($_GET['search'] ?? ''),
    'role' => sanitizeInput($_GET['role'] ?? ''),
    'department' => sanitizeInput($_GET['department'] ?? ''),
    'status' => sanitizeInput($_GET['status'] ?? '')
];

// Build query
$whereConditions = [];
$params = [];

if (!empty($filters['search'])) {
    $whereConditions[] = "(login_id LIKE ? OR name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($filters['role'])) {
    $whereConditions[] = "role = ?";
    $params[] = $filters['role'];
}

if (!empty($filters['department'])) {
    $whereConditions[] = "department = ?";
    $params[] = $filters['department'];
}

if (!empty($filters['status'])) {
    $whereConditions[] = "status = ?";
    $params[] = $filters['status'];
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get users
$sql = "SELECT * FROM users " . $whereClause . " ORDER BY login_id ASC";
$stmt = $connection->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Set page title for header
$pageTitle = 'User Management';

// Include header
require_once '../src/views/header.php';
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-users text-primary"></i> User Management</h1>
            <a href="<?php echo BASE_URL; ?>register" class="btn btn-railway-primary btn-sm"><i class="fas fa-user-plus"></i> Add User</a>
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
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search by login, name, email or mobile" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <?php foreach (['admin','controller','viewer','customer'] as $r): ?>
                            <option value="<?php echo $r; ?>" <?php echo (($filters['role'] ?? '')===$r)?'selected':''; ?>><?php echo ucfirst($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach (['SYSTEM','COMMERCIAL','OPERATING','MECHANICAL','ELECTRICAL','ENGINEERING','SECURITY','MEDICAL','ACCOUNTS','PERSONNEL'] as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo (($filters['department'] ?? '')===$d)?'selected':''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <?php foreach (['active','inactive','suspended'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo (($filters['status'] ?? '')===$s)?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-railway-primary"><i class="fas fa-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Login ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Division</th>
                            <th>Zone</th>
                            <th>Status</th>
                            <th>Customer</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="9" class="text-center py-4 text-muted">No users found.</td></tr>
                        <?php else: foreach ($users as $u): ?>
                            <tr>
                                <td><small class="text-muted"><?php echo htmlspecialchars($u['login_id']); ?></small></td>
                                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['mobile']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo strtoupper($u['role']); ?></span></td>
                                <td><?php echo htmlspecialchars($u['department']); ?></td>
                                <td><?php echo htmlspecialchars($u['Division'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($u['Zone'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo $u['status']==='active'?'bg-success':($u['status']==='inactive'?'bg-warning':'bg-danger'); ?>"><?php echo ucfirst($u['status']); ?></span>
                                </td>
                                <td><small><?php echo htmlspecialchars($u['customer_id'] ?? '-'); ?></small></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="openEditUser('<?php echo $u['login_id']; ?>')"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-warning" onclick="openResetPassword('<?php echo $u['login_id']; ?>')"><i class="fas fa-key"></i></button>
                                        <button class="btn btn-outline-danger" onclick="confirmDeleteUser('<?php echo $u['login_id']; ?>')"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="original_login_id" id="originalLoginId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="login_id" id="editLoginId" placeholder="Login ID" required>
                                    <label>Login ID *</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="name" id="editName" placeholder="Name" required>
                                    <label>Name *</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="email" class="form-control" name="email" id="editEmail" placeholder="Email">
                                    <label>Email</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="mobile" id="editMobile" placeholder="Mobile">
                                    <label>Mobile</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" name="role" id="editRole" required>
                                        <?php foreach (['admin','controller','viewer','customer'] as $r): ?>
                                            <option value="<?php echo $r; ?>"><?php echo ucfirst($r); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label>Role *</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" name="department" id="editDepartment">
                                        <option value="">Select Department</option>
                                        <?php foreach (['SYSTEM','COMMERCIAL','OPERATING','MECHANICAL','ELECTRICAL','ENGINEERING','SECURITY','MEDICAL','ACCOUNTS','PERSONNEL'] as $d): ?>
                                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label>Department</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="Division" id="editDivision" placeholder="Division">
                                    <label>Division</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="Zone" id="editZone" placeholder="Zone">
                                    <label>Zone</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="customer_id" id="editCustomerId" placeholder="Customer ID">
                                    <label>Customer ID</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" name="status" id="editStatus">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <label>Status</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-railway-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="login_id" id="resetLoginId">
                    <div class="modal-body">
                        <div class="form-floating position-relative mb-3">
                            <input type="password" class="form-control" name="new_password" id="newPassword" placeholder="New Password" required>
                            <label>New Password *</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary password-toggle" data-target="newPassword" style="position:absolute; right:10px; top:8px;">Show</button>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="form-floating position-relative">
                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
                            <label>Confirm Password *</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary password-toggle" data-target="confirmPassword" style="position:absolute; right:10px; top:8px;">Show</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditUser(loginId) {
    const row = [...document.querySelectorAll('tbody tr')].find(r => r.querySelector('td small')?.textContent === loginId);
    if (!row) return;
    document.getElementById('originalLoginId').value = loginId;
    document.getElementById('editLoginId').value = loginId;
    document.getElementById('editName').value = row.children[1].innerText.trim();
    document.getElementById('editEmail').value = row.children[2].innerText.trim();
    document.getElementById('editMobile').value = row.children[3].innerText.trim();
    document.getElementById('editRole').value = row.children[4].innerText.trim().toLowerCase();
    document.getElementById('editDepartment').value = row.children[5].innerText.trim();
    document.getElementById('editDivision').value = row.children[6].innerText.trim() === '-' ? '' : row.children[6].innerText.trim();
    document.getElementById('editZone').value = row.children[7].innerText.trim() === '-' ? '' : row.children[7].innerText.trim();
    document.getElementById('editStatus').value = row.children[8].innerText.trim().toLowerCase();
    document.getElementById('editCustomerId').value = row.children[9].innerText.trim() === '-' ? '' : row.children[9].innerText.trim();
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
function openResetPassword(loginId) {
    document.getElementById('resetLoginId').value = loginId;
    document.getElementById('newPassword').value = '';
    const confirmEl = document.getElementById('confirmPassword');
    if (confirmEl) confirmEl.value = '';
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}
function confirmDeleteUser(loginId) {
    if (typeof Swal === 'undefined') { if (confirm('Delete user '+loginId+'?')) submitDelete(loginId); return; }
    Swal.fire({
        title: 'Delete user?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then(r => { if (r.isConfirmed) submitDelete(loginId); });
}
function submitDelete(loginId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="action" value="delete_user">'+
                     '<input type="hidden" name="login_id" value="'+loginId+'">';
    document.body.appendChild(form);
    form.submit();
}

// Toggle password visibility buttons
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.password-toggle');
    if (!btn) return;
    const targetId = btn.getAttribute('data-target');
    const input = document.getElementById(targetId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.textContent = isPassword ? 'Hide' : 'Show';
});
</script>

<?php require_once '../src/views/footer.php'; ?>

