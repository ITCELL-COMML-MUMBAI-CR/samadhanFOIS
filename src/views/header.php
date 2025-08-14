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
    <link href="<?php echo BASE_URL; ?>css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>css/navbar.css" rel="stylesheet">
    
    <style>
        :root {
            --railway-blue: #1e3a8a;
            --railway-orange: #ea580c;
            --railway-green: #16a34a;
            --railway-red: #dc2626;
            --railway-gold: #f59e0b;
        }
        
        /* Global Styles */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-content {
            flex: 1;
            padding-top: 20px;
        }
        
        /* Compact Header Styles */
        .compact-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }
        
        /* Header Content */
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        
        /* Logo Styles */
        .logo-left, .logo-right {
            flex-shrink: 0;
        }
        
        
        /* System Name Center */
        .system-name-center {
            flex: 1;
            text-align: center;
        }
        
        .system-acronym {
            font-size: 5.5rem;
            font-weight: 900;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
            letter-spacing: 2px;
        }
        
        
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .system-acronym {
                font-size: 2rem;
            }
            
            .system-full-name {
                font-size: 0.8rem;
            }
            
            .railway-logo, .sampark-logo {
                height: 50px;
            }
        }
        
        @media (max-width: 576px) {
            .compact-header {
                padding: 0.75rem 0;
            }
            
            .system-acronym {
                font-size: 1.8rem;
            }
            
            .system-full-name {
                font-size: 0.7rem;
            }
            
            .railway-logo, .sampark-logo {
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Compact Header -->
    <header class="compact-header">
        <div class="container">
            <div class="header-content">
                <!-- Left Logo -->
                <div class="logo-left">
                    <img src="<?php echo BASE_URL; ?>images/indian_railways_logo.png" alt="Indian Railways" class="railways-logo">
                </div>
                
                <!-- Center System Name -->
                <div class="system-name-center">
                    <h1 class="system-acronym">SAMPARK</h1>
                    <p class="system-full-name">Support and Mediation Portal for All Rail Cargo</p>
                </div>
                
                <!-- Right Logo -->
                <div class="logo-right">
                    <img src="<?php echo BASE_URL; ?>images/Icon SAMPARK.png" alt="SAMPARK" class="sampark-logo">
                </div>
            </div>
        </div>
    </header>
    
    <!-- JavaScript Files -->
    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
        <script>
            // Define BASE_URL for JavaScript
            const BASE_URL = '<?php echo BASE_URL; ?>';
        </script>
        <script src="<?php echo BASE_URL; ?>js/notifications.js"></script>
    <?php endif; ?>
    
    <!-- Navigation Bar - Hide on login page -->
    <?php 
    $currentPage = $_SERVER['REQUEST_URI'] ?? '';
    $isLoginPage = strpos($currentPage, 'login') !== false;
    
    if (!$isLoginPage): 
    ?>
        <?php include __DIR__ . '/navbar.php'; ?>
    <?php endif; ?>
    
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

