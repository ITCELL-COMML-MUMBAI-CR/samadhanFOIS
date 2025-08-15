<?php
/**
 * Navigation Bar Component
 * Stunning navbar with gradients, animations, and role-based navigation
 */

$currentUser = SessionManager::getCurrentUser();
$userRole = $_SESSION['user_role'] ?? '';
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
                        <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>customer-home">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    
                    <!-- Dashboard Link -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>dashboard">
                            <i class="fas fa-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Role-based Navigation -->
                    <?php if ($userRole === 'customer'): ?>
                        <!-- Customer Navigation -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>grievances/new">
                                <i class="fas fa-plus-circle"></i>
                                <span>New Grievance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>grievances/my">
                                <i class="fas fa-list"></i>
                                <span>My Grievances</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>track">
                                <i class="fas fa-search"></i>
                                <span>Track Status</span>
                            </a>
                        </li>
                        
                    <?php elseif ($userRole === 'controller'): ?>
                        <!-- Controller Navigation -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>grievances">
                                <i class="fas fa-clipboard-list"></i>
                                <span>All Grievances</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>grievances/tome">
                                <i class="fas fa-tasks"></i>
                                <span>Assigned to Me</span>
                            </a>
                        </li>
                        <?php if (strtoupper($currentUser['department'] ?? '') === 'COMMERCIAL'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>grievances/approvals">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Approvals</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>reports">
                                <i class="fas fa-chart-bar"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                        
                    <?php elseif ($userRole === 'admin'): ?>
                        <!-- Admin Navigation -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle nav-link-animated" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i>
                                <span>Administration</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-animated">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/users">
                                    <i class="fas fa-users"></i> User Management
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/categories">
                                    <i class="fas fa-tags"></i> Manage Categories
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/news">
                                    <i class="fas fa-newspaper"></i> News & Announcements
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/quicklinks">
                                    <i class="fas fa-external-link-alt"></i> Quick Links
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/reports">
                                    <i class="fas fa-chart-line"></i> Analytics
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/bulk-email">
                                    <i class="fas fa-envelope"></i> Bulk Email
                                </a></li>

                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/add">
                                    <i class="fas fa-user-plus"></i> Add Customer
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>register">
                                    <i class="fas fa-user-edit"></i> Add User
                                </a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>grievances">
                                <i class="fas fa-clipboard-list"></i>
                                <span>All Grievances</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>admin/logs">
                                <i class="fas fa-history"></i>
                                <span>System Logs</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Public Navigation -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>about">
                            <i class="fas fa-info-circle"></i>
                            <span>About</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated" href="<?php echo BASE_URL; ?>contact">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- User Actions Section -->
            <ul class="navbar-nav ms-auto">
                <?php if (SessionManager::isLoggedIn()): ?>

                    
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
                            
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">
                                <i class="fas fa-user"></i> Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout-link" href="<?php echo BASE_URL; ?>logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                    
                <?php else: ?>
                    <!-- Login/Register Links -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated login-link" href="<?php echo BASE_URL; ?>login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-animated register-link" href="<?php echo BASE_URL; ?>register">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Link to external navbar styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/navbar.css">
