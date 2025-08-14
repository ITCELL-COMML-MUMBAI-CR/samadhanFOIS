<?php
/**
 * Home Page
 * Landing page for the complaint system
 */
?>

<div class="container-fluid">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-body text-center py-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8 mx-auto">
                            <h1 class="display-4 fw-bold text-primary mb-4">
                                <i class="fas fa-train me-3"></i>
                                SAMPARK
                            </h1>
                            <p class="lead mb-4">
                                Central Railway Freight Customer Grievance Management System
                            </p>
                            <p class="text-muted mb-4">
                                Submit, track, and resolve freight-related grievances efficiently. 
                                Our dedicated team ensures prompt response and resolution to your concerns.
                            </p>
                            
                            <?php if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="<?php echo BASE_URL; ?>login" class="btn btn-railway-primary btn-lg me-md-2">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>register" class="btn btn-railway-secondary btn-lg">
                                        <i class="fas fa-user-plus"></i> Register
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-railway-primary btn-lg me-md-2">
                                        <i class="fas fa-dashboard"></i> Go to Dashboard
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>grievances/new" class="btn btn-railway-secondary btn-lg">
                                        <i class="fas fa-plus-circle"></i> New Grievance
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">System Features</h2>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-file-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Easy Grievance Submission</h5>
                    <p class="card-text">
                        Submit grievances with detailed descriptions, evidence images, and location information.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-search fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Real-time Tracking</h5>
                    <p class="card-text">
                        Track the status of your grievances in real-time with detailed progress updates.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">Multi-Department Support</h5>
                    <p class="card-text">
                        Grievances are automatically routed to the appropriate department for quick resolution.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Mobile Responsive</h5>
                    <p class="card-text">
                        Access the system from any device - desktop, tablet, or mobile phone.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title">Secure & Confidential</h5>
                    <p class="card-text">
                        Your grievance data is secure and confidential with role-based access control.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Analytics & Reports</h5>
                    <p class="card-text">
                        Comprehensive reporting and analytics for performance monitoring and improvement.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">System Statistics</h2>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card dashboard-card card-complaints text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php
                        try {
                            require_once '../src/models/Complaint.php';
                            $complaintModel = new Complaint();
                            $totalComplaints = $complaintModel->count();
                            echo number_format($totalComplaints);
                        } catch (Exception $e) {
                            echo "N/A";
                        }
                        ?>
                    </h3>
                    <p class="mb-0">Total Grievances</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card dashboard-card card-resolved text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php
                        try {
                            $resolvedComplaints = $complaintModel->count(['status' => 'replied']) + $complaintModel->count(['status' => 'closed']);
                            echo number_format($resolvedComplaints);
                        } catch (Exception $e) {
                            echo "N/A";
                        }
                        ?>
                    </h3>
                    <p class="mb-0">Resolved</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card dashboard-card card-pending text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php
                        try {
                            $pendingComplaints = $complaintModel->count(['status' => 'pending']);
                            echo number_format($pendingComplaints);
                        } catch (Exception $e) {
                            echo "N/A";
                        }
                        ?>
                    </h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card dashboard-card card-users text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php
                        try {
                            require_once '../src/models/User.php';
                            $userModel = new User();
                            $totalUsers = $userModel->count(['status' => 'active']);
                            echo number_format($totalUsers);
                        } catch (Exception $e) {
                            echo "N/A";
                        }
                        ?>
                    </h3>
                    <p class="mb-0">Active Users</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-info-circle"></i> Important Information
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>How to File a Grievance</h5>
                            <ol>
                                <li>Register or login to your account</li>
                                <li>Click on "New Grievance" button</li>
                                <li>Fill in the grievance details</li>
                                <li>Upload evidence images (if any)</li>
                                <li>Submit the grievance</li>
                                <li>Track status from your dashboard</li>
                            </ol>
                        </div>
                        <div class="col-lg-6">
                            <h5>Contact Support</h5>
                            <p><strong>Email:</strong> sampark-admin@itcellbbcr.in</p>
                            <p><strong>Phone:</strong> +91 12345 67890</p>
                            <p><strong>Response Time:</strong> Within 24 hours</p>
                            
                            <div class="mt-3">
                                <h6>Emergency Contact</h6>
                                <p class="text-danger">
                                    <strong>For urgent matters:</strong> +91 98765 43210
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>
