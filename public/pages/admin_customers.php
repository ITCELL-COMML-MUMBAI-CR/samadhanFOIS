<?php
/**
 * Admin Customer Management Page (View)
 * This file only handles the presentation of customer data provided by the AdminController.
 * All business logic, database queries, and session handling are now in AdminController.php.
 */

// The variables used in this view ($customers, $totalCustomers, $totalPages, etc.)
// are expected to be extracted from the $data array passed by the AdminController's customers() method.
?>

<!-- Include customer-specific styles -->
<link href="<?php echo BASE_URL; ?>css/admin_customers.css" rel="stylesheet">

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
                                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="company" class="form-select">
                                <option value="">All Companies</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['CompanyName']); ?>"
                                            <?php echo (($filters['company'] ?? '') === $company['CompanyName']) ? 'selected' : ''; ?>>
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
                                                            onclick="deleteCustomer('<?php echo htmlspecialchars($customer['CustomerID']); ?>', '<?php echo htmlspecialchars(addslashes($customer['Name'])); ?>')"
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
                <?php if ($totalPages > 1):
                    $page = $_GET['page'] ?? 1;
                ?>
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
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
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
                <!-- Content will be loaded dynamically via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript file -->
<script src="<?php echo BASE_URL; ?>js/admin_customers.js"></script>
