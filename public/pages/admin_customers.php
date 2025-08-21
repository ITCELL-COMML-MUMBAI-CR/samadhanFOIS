<?php
/**
 * Admin Customer Management Page
 * Provides comprehensive customer management for administrators
 */

require_once '../src/utils/SessionManager.php';

// Handle AJAX requests first, before any HTML output
if (isset($_POST['action']) && $_POST['action'] === 'ajax') {
    // Prevent stray output from corrupting JSON response
    error_reporting(0);
    ini_set('display_errors', 0);

    header('Content-Type: application/json');

    try {
        // Start session and check authentication for AJAX requests
        SessionManager::start();

        // Check authentication for AJAX requests
        if (!SessionManager::isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh the page and try again.']);
            exit;
        }

        // Get current user data using the updated SessionManager
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh the page and try again.']);
            exit;
        }

        // Check if user has admin role
        if (!SessionManager::hasRole('admin')) {
            echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
            exit;
        }

        // Load models for AJAX requests
        require_once '../src/models/BaseModel.php';
        require_once '../src/models/Customer.php';
        require_once '../src/utils/Helpers.php';
        require_once '../src/utils/Database.php';

        $db = Database::getInstance();
        $connection = $db->getConnection();

        $action = $_POST['ajax_action'] ?? '';
        $response = ['success' => false, 'message' => 'Invalid action'];

        switch ($action) {
            case 'delete_customer':
                $customerId = sanitizeInput($_POST['customer_id'] ?? '');
                if (!empty($customerId)) {
                    $customerModel = new Customer();
                    $result = $customerModel->deleteCustomer($customerId);
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Customer deleted successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to delete customer'];
                    }
                }
                break;

            case 'update_customer':
                $customerId = sanitizeInput($_POST['customer_id'] ?? '');
                $data = [
                    'Name' => sanitizeInput($_POST['Name'] ?? ''),
                    'Email' => sanitizeInput($_POST['Email'] ?? ''),
                    'MobileNumber' => sanitizeInput($_POST['MobileNumber'] ?? ''),
                    'CompanyName' => sanitizeInput($_POST['CompanyName'] ?? ''),
                    'Designation' => sanitizeInput($_POST['Designation'] ?? '')
                ];

                $customerModel = new Customer();
                $errors = $customerModel->validateCustomerData($data);

                if (!empty($errors)) {
                    $response = ['success' => false, 'message' => implode('<br>', $errors)];
                    echo json_encode($response);
                    exit;
                }

                // Handle password change if provided
                $newPassword = $_POST['Password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (!empty($newPassword)) {
                    if ($newPassword !== $confirmPassword) {
                        $response = ['success' => false, 'message' => 'Passwords do not match'];
                        echo json_encode($response);
                        exit;
                    }

                    if (strlen($newPassword) < 6) {
                        $response = ['success' => false, 'message' => 'Password must be at least 6 characters long'];
                        echo json_encode($response);
                        exit;
                    }

                    // Hash the new password
                    $data['Password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }

                if (!empty($customerId)) {
                    $customerModel = new Customer();
                    $result = $customerModel->updateCustomer($customerId, $data);
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Customer updated successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update customer'];
                    }
                }
                break;

            case 'get_customer_details':
                $customerId = sanitizeInput($_POST['customer_id'] ?? '');
                if (!empty($customerId)) {
                    $customerModel = new Customer();
                    $customer = $customerModel->getCustomerById($customerId);
                    if ($customer) {
                        // Get customer's complaints
                        try {
                            $complaintsSql = "SELECT * FROM complaints WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10";
                            $stmt = $connection->prepare($complaintsSql);
                            $stmt->execute([$customerId]);
                            $complaints = $stmt->fetchAll();
                        } catch (Exception $e) {
                            // If complaints table doesn't exist or has different structure, use empty array
                            $complaints = [];
                        }

                        $html = '
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Customer Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Customer ID:</strong></td><td>' . htmlspecialchars($customer['CustomerID']) . '</td></tr>
                                    <tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($customer['Name']) . '</td></tr>
                                    <tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($customer['Email']) . '</td></tr>
                                    <tr><td><strong>Mobile:</strong></td><td>' . htmlspecialchars($customer['MobileNumber']) . '</td></tr>
                                    <tr><td><strong>Company:</strong></td><td>' . htmlspecialchars($customer['CompanyName']) . '</td></tr>
                                    <tr><td><strong>Designation:</strong></td><td>' . htmlspecialchars($customer['Designation'] ?: 'Not specified') . '</td></tr>
                                    <tr><td><strong>Registration Date:</strong></td><td>' . (isset($customer['created_at']) ? date('d M Y', strtotime($customer['created_at'])) : 'Not available') . '</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Recent Complaints (' . count($complaints) . ')</h6>';

                        if (empty($complaints)) {
                            $html .= '<p class="text-muted">No complaints found for this customer.</p>';
                        } else {
                            $html .= '<div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Complaint ID</th>
                                                    <th>Subject</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>';

                            foreach ($complaints as $complaint) {
                                $statusClass = '';
                                $status = $complaint['status'] ?? 'unknown';
                                switch ($status) {
                                    case 'open': $statusClass = 'badge bg-warning'; break;
                                    case 'in_progress': $statusClass = 'badge bg-info'; break;
                                    case 'resolved': $statusClass = 'badge bg-success'; break;
                                    case 'closed': $statusClass = 'badge bg-secondary'; break;
                                    default: $statusClass = 'badge bg-secondary';
                                }

                                $complaintId = $complaint['complaint_id'] ?? $complaint['id'] ?? 'N/A';
                                $subject = $complaint['subject'] ?? $complaint['title'] ?? 'No subject';
                                $createdAt = isset($complaint['created_at']) ? date('d M Y', strtotime($complaint['created_at'])) : 'N/A';

                                $html .= '<tr>
                                            <td><small>' . htmlspecialchars($complaintId) . '</small></td>
                                            <td>' . htmlspecialchars(substr($subject, 0, 30)) . (strlen($subject) > 30 ? '...' : '') . '</td>
                                            <td><span class="' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span></td>
                                            <td><small>' . $createdAt . '</small></td>
                                          </tr>';
                            }

                            $html .= '</tbody></table></div>';
                        }

                        $html .= '</div></div>';

                        $response = ['success' => true, 'html' => $html];
                    } else {
                        $response = ['success' => false, 'message' => 'Customer not found'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Customer ID is required'];
                }
                break;
        }
    } catch (Exception $e) {
        // Catch any uncaught exceptions and return a JSON error
        http_response_code(500); // Internal Server Error
        $response = ['success' => false, 'message' => 'A server error occurred: ' . $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}

// For non-AJAX requests, require admin access and load the page
SessionManager::requireRole('admin');

// Get current user
$currentUser = SessionManager::getCurrentUser();

// Load models for data
require_once '../src/models/BaseModel.php';
require_once '../src/models/Customer.php';
require_once '../src/utils/Helpers.php';
require_once '../src/utils/Database.php';

// Include header
require_once '../src/views/header.php';
?>

<!-- Include customer-specific styles -->
<link href="<?php echo BASE_URL; ?>css/admin_customers.css" rel="stylesheet">

<?php
$db = Database::getInstance();
$connection = $db->getConnection();

// Get filters
$filters = [
    'search' => sanitizeInput(is_array($_GET['search'] ?? '') ? '' : ($_GET['search'] ?? '')),
    'company' => sanitizeInput(is_array($_GET['company'] ?? '') ? '' : ($_GET['company'] ?? '')),
    'status' => sanitizeInput(is_array($_GET['status'] ?? '') ? '' : ($_GET['status'] ?? ''))
];

// Build query
$whereConditions = [];
$params = [];

if (!empty($filters['search'])) {
    $whereConditions[] = "(c.Name LIKE ? OR c.Email LIKE ? OR c.MobileNumber LIKE ? OR c.CompanyName LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($filters['company'])) {
    $whereConditions[] = "c.CompanyName LIKE ?";
    $params[] = '%' . $filters['company'] . '%';
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get total count
$countSql = "SELECT COUNT(*) as count FROM customers c " . $whereClause;
$stmt = $connection->prepare($countSql);
$stmt->execute($params);
$totalCustomers = $stmt->fetch()['count'];

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$totalPages = ceil($totalCustomers / $limit);

// Get customers
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM complaints WHERE customer_id = c.CustomerID) as complaint_count
        FROM customers c 
        " . $whereClause . " 
        ORDER BY c.CustomerID DESC 
        LIMIT ? OFFSET ?";

$stmt = $connection->prepare($sql);
$stmt->execute(array_merge($params, [$limit, $offset]));
$customers = $stmt->fetchAll();

// Get unique companies for filter
$stmt = $connection->prepare("SELECT DISTINCT CompanyName FROM customers WHERE CompanyName IS NOT NULL AND CompanyName != '' ORDER BY CompanyName ASC");
$stmt->execute();
$companies = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM customers");
$stmt->execute();
$stats['total_customers'] = $stmt->fetch()['count'];

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM customers WHERE CustomerID IN (SELECT DISTINCT customer_id FROM complaints)");
$stmt->execute();
$stats['customers_with_complaints'] = $stmt->fetch()['count'];

$stmt = $connection->prepare("SELECT COUNT(DISTINCT CompanyName) as count FROM customers WHERE CompanyName IS NOT NULL AND CompanyName != ''");
$stmt->execute();
$stats['unique_companies'] = $stmt->fetch()['count'];

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM customers WHERE CustomerID LIKE 'ED" . date('Y') . "%'");
$stmt->execute();
$stats['new_this_year'] = $stmt->fetch()['count'];

// Set page title for header
$pageTitle = 'Customer Management';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="fas fa-users-cog text-primary"></i> Customer Management
                        </h1>
                        <p class="text-muted mb-0">Manage all registered customers and their information</p>
                    </div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>admin/dashboard" class="btn btn-railway-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>customer/add" class="btn btn-railway-primary">
                            <i class="fas fa-user-plus"></i> Add New Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search by name, email, mobile, or company" 
                                   value="<?php echo htmlspecialchars(is_array($filters['search']) ? '' : $filters['search']); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="company" class="form-select">
                                <option value="">All Companies</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['CompanyName']); ?>" 
                                            <?php echo ($filters['company'] === $company['CompanyName']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['CompanyName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-railway-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo BASE_URL; ?>admin/customers" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-info w-100" onclick="exportCustomers()">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Customers 
                            <span class="badge bg-secondary"><?php echo number_format($totalCustomers); ?></span>
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Company</th>
                                    <th>Designation</th>
                                    <th>Complaints</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-2x mb-3"></i>
                                                <p>No customers found</p>
                                                <a href="<?php echo BASE_URL; ?>customer/add" class="btn btn-sm btn-railway-primary">
                                                    <i class="fas fa-user-plus"></i> Add First Customer
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars($customer['CustomerID']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($customer['Name']); ?></strong>
                                            </td>
                                                                                         <td>
                                                 <?php echo htmlspecialchars($customer['Email']); ?>
                                             </td>
                                             <td>
                                                 <?php echo htmlspecialchars($customer['MobileNumber']); ?>
                                             </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($customer['CompanyName']); ?></span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($customer['Designation'] ?: '-'); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $customer['complaint_count'] > 0 ? 'warning' : 'secondary'; ?>">
                                                    <?php echo $customer['complaint_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="openEditCustomer('<?php echo htmlspecialchars($customer['CustomerID']); ?>')"
                                                            title="Edit Customer">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="viewCustomerDetails('<?php echo htmlspecialchars($customer['CustomerID']); ?>')"
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteCustomer('<?php echo htmlspecialchars($customer['CustomerID']); ?>', '<?php echo htmlspecialchars($customer['Name']); ?>')"
                                                            title="Delete Customer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Customer pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">
                    <i class="fas fa-edit"></i> Edit Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCustomerForm">
                <div class="modal-body">
                    <input type="hidden" id="editCustomerId" name="customer_id">
                    <input type="hidden" name="action" value="ajax">
                    <input type="hidden" name="ajax_action" value="update_customer">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="editName" name="Name" placeholder="Name" required>
                                <label for="editName">
                                    <i class="fas fa-user"></i> Name *
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="editEmail" name="Email" placeholder="Email" autocomplete="email" required>
                                <label for="editEmail">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control" id="editMobile" name="MobileNumber" placeholder="Mobile" required>
                                <label for="editMobile">
                                    <i class="fas fa-phone"></i> Mobile *
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="editCompany" name="CompanyName" placeholder="Company" required>
                                <label for="editCompany">
                                    <i class="fas fa-building"></i> Company *
                                </label>
                            </div>
                        </div>
                    </div>
                    
                                         <div class="row">
                         <div class="col-md-12">
                             <div class="form-floating mb-3">
                                 <input type="text" class="form-control" id="editDesignation" name="Designation" placeholder="Designation">
                                 <label for="editDesignation">
                                     <i class="fas fa-id-badge"></i> Designation
                                 </label>
                             </div>
                         </div>
                     </div>
                     
                     <!-- Password Change Section -->
                     <hr class="my-4">
                     <div class="password-section">
                         <div class="row">
                             <div class="col-12">
                                 <h6 class="text-muted mb-3">
                                     <i class="fas fa-key"></i> Change Password (Optional)
                                 </h6>
                                 <div class="alert alert-info">
                                     <small>
                                         <i class="fas fa-info-circle"></i> Leave password fields empty if you don't want to change the password.
                                     </small>
                                 </div>
                             </div>
                         </div>
                     <div class="row">
                         <div class="col-md-6">
                             <div class="form-floating mb-3">
                                 <input type="password" class="form-control" id="editPassword" name="Password" placeholder="New Password" minlength="6" autocomplete="new-password">
                                 <label for="editPassword">
                                     <i class="fas fa-lock"></i> New Password
                                 </label>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="form-floating mb-3">
                                 <input type="password" class="form-control" id="editConfirmPassword" name="confirm_password" placeholder="Confirm Password" minlength="6" autocomplete="new-password">
                                 <label for="editConfirmPassword">
                                     <i class="fas fa-lock"></i> Confirm Password
                                 </label>
                             </div>
                         </div>
                     </div>
                     </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-railway-primary">
                        <i class="fas fa-save"></i> Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerDetailsModalLabel">
                    <i class="fas fa-user"></i> Customer Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="customerDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript files -->
<script src="<?php echo BASE_URL; ?>js/admin_customers.js"></script>

<?php require_once '../src/views/footer.php'; ?>
