<?php
/**
 * Navigation Bar Component
 * Stunning navbar with gradients, animations, and role-based navigation
 */

$currentUser = SessionManager::getCurrentUser();
$userRole = $_SESSION['user_role'] ?? '';

// Get current page for highlighting
$currentPage = getCurrentPage();
?>

<!-- Stunning Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark railway-navbar">
    <div class="container-fluid">        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (SessionManager::isLoggedIn()): ?>
                    <!-- Home Link -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated <?php echo ($currentPage === 'customer-home') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customer-home">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <!-- Dashboard Link - Only for controller, viewer, and admin -->
                    <?php if (in_array($userRole, ['controller', 'viewer', 'admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>dashboard">
                                <i class="fas fa-dashboard"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <!-- Role-based Navigation -->
                    <?php if ($userRole === 'customer'): ?>
                        <!-- Customer Navigation -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'customer-tickets') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customer-tickets">
                                <i class="fas fa-ticket-alt"></i>
                                <span>My Support Tickets</span>
                            </a>
                        </li>
                    <?php elseif ((isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) || 
                               (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && 
                                isset($_SESSION['user_customer_id']) && !empty($_SESSION['user_customer_id']))): ?>
                        <!-- Customer logged in (either method) - Show tickets link -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'customer-tickets') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customer-tickets">
                                <i class="fas fa-ticket-alt"></i>
                                <span>My Support Tickets</span>
                            </a>
                        </li>
                    <?php elseif ($userRole === 'controller'): ?>
                        <!-- Controller Navigation -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'grievances-hub') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>grievances/hub">
                                <i class="fas fa-comments"></i>
                                <span>Complaints Hub</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'reports') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>reports">
                                <i class="fas fa-chart-bar"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                        
                    <?php elseif ($userRole === 'admin'): ?>
                        <!-- Admin Navigation -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle nav-link-animated <?php echo (strpos($currentPage, 'admin-') === 0) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i>
                                <span>Administration</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-animated">
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-dashboard') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard">
                                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-users') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/users">
                                    <i class="fas fa-users"></i> User Management
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-customers') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/customers">
                                    <i class="fas fa-users-cog"></i> Customer Management
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-categories') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/categories">
                                    <i class="fas fa-tags"></i> Manage Categories
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-news') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/news">
                                    <i class="fas fa-newspaper"></i> News & Announcements
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-quicklinks') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/quicklinks">
                                    <i class="fas fa-external-link-alt"></i> Quick Links
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-reports') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/reports">
                                    <i class="fas fa-chart-line"></i> Analytics
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-bulk-email') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/bulk-email">
                                    <i class="fas fa-envelope"></i> Bulk Email
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-email-templates') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/email-templates">
                                    <i class="fas fa-envelope-open-text"></i> Email Templates
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'customer-add') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customer/add">
                                    <i class="fas fa-user-plus"></i> Add Customer
                                </a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'register') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>register">
                                    <i class="fas fa-user-edit"></i> Add User
                                </a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'grievances-hub') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>grievances/hub">
                                <i class="fas fa-comments"></i>
                                <span>Complaints Hub</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated <?php echo ($currentPage === 'admin-logs') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/logs">
                                <i class="fas fa-history"></i>
                                <span>System Logs</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Public Navigation -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated <?php echo ($currentPage === 'home') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- User Actions Section -->
            <ul class="navbar-nav ms-auto">
                <?php if (SessionManager::isLoggedIn()): ?>
                    <!-- Help Link -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated <?php echo ($currentPage === 'help') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>help">
                            <i class="fas fa-life-ring"></i>
                            <span>Help</span>
                        </a>
                    </li>
                <?php elseif ((isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) || 
                           (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && 
                            isset($_SESSION['user_customer_id']) && !empty($_SESSION['user_customer_id']))): ?>
                    <!-- Customer logged in - Show tickets button prominently -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated btn btn-outline-light btn-sm me-2 <?php echo ($currentPage === 'customer-tickets') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customer-tickets">
                            <i class="fas fa-ticket-alt"></i>
                            <span>My Tickets</span>
                        </a>
                    </li>
                    <!-- Help Link for customers -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated <?php echo ($currentPage === 'help') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>help">
                            <i class="fas fa-life-ring"></i>
                            <span>Help</span>
                        </a>
                    </li>
                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-profile-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                                <span class="user-role"><?php echo strtoupper($currentUser['role']); ?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                            
                            <li><a class="dropdown-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>profile">
                                <i class="fas fa-user"></i> Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout-link" href="<?php echo BASE_URL; ?>logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                <?php elseif ((isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']) || 
                           (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && 
                            isset($_SESSION['user_customer_id']) && !empty($_SESSION['user_customer_id']))): ?>
                    <!-- Customer logged in (either method) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-profile-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="customerDropdownToggle">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo htmlspecialchars(isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : $_SESSION['user_name']); ?></span>
                                <span class="user-role">CUSTOMER</span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated" id="customerDropdownMenu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer-tickets">
                                <i class="fas fa-ticket-alt"></i> My Support Tickets
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout-link" href="<?php echo BASE_URL; ?>customer/logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Login Link for customers -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated login-link <?php echo ($currentPage === 'customer-login') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customer-login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Link to external navbar styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/navbar.css">
