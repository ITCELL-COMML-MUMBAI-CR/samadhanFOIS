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
    <!-- AOS (Animate On Scroll) CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Mermaid.js for diagrams -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>css/navbar.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>css/help.css" rel="stylesheet">
    <?php if (isset($pageTitle) && ($pageTitle === 'My Grievances' || $pageTitle === 'Customer Home')): ?>
    <link href="<?php echo BASE_URL; ?>css/customer_home.css" rel="stylesheet">
    <?php endif; ?>
    
    <style>
        :root {
            /* Apple-inspired color palette */
            --apple-black: #000000;
            --apple-dark-gray: #666666;
            --apple-medium-gray: #979797;
            --apple-light-gray: #eeeeee;
            --apple-blue: #0088cc;
            
            /* Gradient colors */
            --gradient-start: hsla(330, 100%, 99%, 1);
            --gradient-end: hsla(0, 0%, 88%, 1);
        }
        
        /* Global Styles - Apple-inspired */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--gradient-start);
            background: linear-gradient(90deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            background: -moz-linear-gradient(90deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            background: -webkit-linear-gradient(90deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#FFFBFD", endColorstr="#E1E1E1", GradientType=1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            font-weight: 400;
            letter-spacing: -0.022em;
        }
        
        .main-content {
            flex: 1;
            padding-top: 20px;
            margin-top: 0;
        }
        
        /* Ensure proper spacing when both header and navbar are sticky */
        .compact-header + .railway-navbar {
            margin-top: 0;
        }
        
        /* Apple-inspired Header Styles */
        .compact-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1002;
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
        
        .railways-logo, .sampark-logo {
            height: 60px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        
        
        /* Apple-inspired System Name Center */
        .system-name-center {
            flex: 1;
            text-align: center;
        }
        
        .system-acronym {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--apple-black);
            margin: 0;
            letter-spacing: -0.022em;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.05;
        }
        
        /* Apple-inspired Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .system-acronym {
                font-size: 2.5rem;
            }
            
            .system-full-name {
                font-size: 1rem;
            }
            
            .railways-logo, .sampark-logo {
                height: 50px;
            }
        }
        
        @media (max-width: 576px) {
            .compact-header {
                padding: 0.75rem 0;
            }
            
            .system-acronym {
                font-size: 2rem;
            }
            
            .system-full-name {
                font-size: 0.9rem;
            }
            
            .railways-logo, .sampark-logo {
                height: 40px;
            }
        }
        
        @media (max-width: 480px) {
            .system-acronym {
                font-size: 1.8rem;
            }
            
            .system-full-name {
                font-size: 0.8rem;
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

