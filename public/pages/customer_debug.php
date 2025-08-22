<?php
/**
 * Customer Debug Page
 * Temporary page to check customer authentication status
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check various authentication methods
$customerLoggedIn = isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'];
$userLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$userCustomerId = isset($_SESSION['user_customer_id']) && !empty($_SESSION['user_customer_id']);
$userRole = $_SESSION['user_role'] ?? '';

// Determine authentication status
$isAuthenticated = $customerLoggedIn || ($userLoggedIn && $userCustomerId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Authentication Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Customer Authentication Debug</h4>
                    </div>
                    <div class="card-body">
                        <h5>Authentication Status:</h5>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">
                                <strong>Customer Logged In:</strong> 
                                <span class="badge bg-<?php echo $customerLoggedIn ? 'success' : 'danger'; ?>">
                                    <?php echo $customerLoggedIn ? 'Yes' : 'No'; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <strong>User Logged In:</strong> 
                                <span class="badge bg-<?php echo $userLoggedIn ? 'success' : 'danger'; ?>">
                                    <?php echo $userLoggedIn ? 'Yes' : 'No'; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <strong>User Customer ID:</strong> 
                                <span class="badge bg-<?php echo $userCustomerId ? 'success' : 'danger'; ?>">
                                    <?php echo $userCustomerId ? 'Yes' : 'No'; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <strong>User Role:</strong> 
                                <span class="badge bg-info"><?php echo $userRole ?: 'None'; ?></span>
                            </li>
                            <li class="list-group-item">
                                <strong>Overall Authenticated:</strong> 
                                <span class="badge bg-<?php echo $isAuthenticated ? 'success' : 'danger'; ?>">
                                    <?php echo $isAuthenticated ? 'Yes' : 'No'; ?>
                                </span>
                            </li>
                        </ul>

                        <h5>Session Variables:</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Variable</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>customer_logged_in</td>
                                        <td><?php echo isset($_SESSION['customer_logged_in']) ? var_export($_SESSION['customer_logged_in'], true) : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>customer_id</td>
                                        <td><?php echo isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>customer_name</td>
                                        <td><?php echo isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>customer_email</td>
                                        <td><?php echo isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>user_logged_in</td>
                                        <td><?php echo isset($_SESSION['user_logged_in']) ? var_export($_SESSION['user_logged_in'], true) : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>user_customer_id</td>
                                        <td><?php echo isset($_SESSION['user_customer_id']) ? $_SESSION['user_customer_id'] : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>user_name</td>
                                        <td><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Not set'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>user_role</td>
                                        <td><?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>customer-tickets" class="btn btn-primary">
                                <i class="fas fa-ticket-alt"></i> Go to My Tickets
                            </a>
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">
                                <i class="fas fa-home"></i> Go Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
