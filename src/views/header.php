<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>public/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --railway-blue: #1e3a8a;
            --railway-orange: #ea580c;
            --railway-green: #16a34a;
            --railway-red: #dc2626;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
        }
        
        .railway-header {
            background: linear-gradient(135deg, var(--railway-blue), #3b82f6);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .railway-logo {
            height: 40px;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-role {
            font-size: 0.75rem;
        }
        
        .notification-bell {
            position: relative;
            cursor: pointer;
        }
        
        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--railway-red);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .footer {
            background: #2d3748;
            color: white;
            margin-top: auto;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark railway-header">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-train railway-logo"></i>
                <span>Samadhan FOIS</span>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (SessionManager::isLoggedIn()): ?>
                        <?php $currentUser = SessionManager::getCurrentUser(); ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard">
                                <i class="fas fa-dashboard"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if (SessionManager::hasRole('customer')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>grievances/new">
                                    <i class="fas fa-plus-circle"></i> New Grievance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>grievances/my">
                                    <i class="fas fa-list"></i> My Grievances
                                </a>
                            </li>
                        <?php elseif (SessionManager::hasRole('controller')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>grievances">
                                    <i class="fas fa-clipboard-list"></i> All Grievances
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>grievances/tome">
                                    <i class="fas fa-tasks"></i> Grievances to Me
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>grievances">
                                    <i class="fas fa-clipboard-list"></i> All Grievances
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (SessionManager::hasRole('admin')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i> Administration
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/users">User Management</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/categories">Manage Categories</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/reports">Reports</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>register">Add User</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <!-- User Info and Actions -->
                <ul class="navbar-nav">
                    <?php if (SessionManager::isLoggedIn()): ?>
                        <!-- Notifications -->
                        <li class="nav-item">
                            <span class="nav-link notification-bell">
                                <i class="fas fa-bell"></i>
                                <span class="notification-count" id="notificationCount">0</span>
                            </span>
                        </li>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-info" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fa-lg"></i>
                                <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                                <span class="badge bg-secondary badge-role">
                                    <?php echo strtoupper($currentUser['role']); ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header"><?php echo htmlspecialchars($currentUser['name']); ?></h6></li>
                                <li><small class="dropdown-header text-muted">
                                    <?php echo htmlspecialchars($currentUser['department'] ?? 'No Department'); ?>
                                </small></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">
                                    <i class="fas fa-user"></i> Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>settings">
                                    <i class="fas fa-cog"></i> Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Alert Messages -->
    <?php if (isset($_SESSION['alert_message'])): ?>
        <div class="container-fluid mt-3">
            <div class="alert alert-<?php echo $_SESSION['alert_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['alert_message']);
                unset($_SESSION['alert_message'], $_SESSION['alert_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Start -->
    <main class="main-content">

