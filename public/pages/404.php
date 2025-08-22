<?php
/**
 * 404 Not Found Page View
 * Displayed when a page or route is not found
 */
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-warning text-dark text-center">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h3 class="fw-bold">Page Not Found</h3>
                    <p class="mb-0">Error 404</p>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <p class="lead text-muted">
                            Sorry, the page you're looking for doesn't exist.
                        </p>
                        <p class="text-muted">
                            The page may have been moved, deleted, or you may have entered an incorrect URL.
                        </p>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-lightbulb"></i>
                        <strong>What you can do:</strong>
                        <ul class="list-unstyled mt-2 mb-0">
                            <li>• Check the URL for any typos</li>
                            <li>• Go back to the previous page</li>
                            <li>• Visit our homepage</li>
                            <li>• Use the navigation menu</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button onclick="history.back()" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </button>
                        <a href="<?php echo BASE_URL; ?>home" class="btn btn-primary">
                            <i class="fas fa-home"></i> Home Page
                        </a>
                        <?php if (class_exists('SessionManager') && SessionManager::isLoggedIn() && in_array($_SESSION['user_role'], ['controller', 'viewer', 'admin'])): ?>
                            <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-outline-success">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php elseif (class_exists('SessionManager') && SessionManager::isLoggedIn()): ?>
                            <a href="<?php echo BASE_URL; ?>customer-tickets" class="btn btn-outline-success">
                                <i class="fas fa-headset"></i> Support & Assistance
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>login" class="btn btn-outline-success">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-envelope"></i>
                        Need help? Contact: 
                        <a href="mailto:support@cr.railnet.gov.in">support@cr.railnet.gov.in</a>
                    </small>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-link"></i> Quick Links
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">For Users:</h6>
                            <ul class="list-unstyled">
                                <li><a href="<?php echo BASE_URL; ?>home" class="text-decoration-none"><i class="fas fa-home"></i> Home</a></li>
                                <li><a href="<?php echo BASE_URL; ?>login" class="text-decoration-none"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                                <li><a href="<?php echo BASE_URL; ?>policy" class="text-decoration-none"><i class="fas fa-file-alt"></i> Policy</a></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">For Members:</h6>
                            <ul class="list-unstyled">
                                <?php if (class_exists('SessionManager') && SessionManager::isLoggedIn() && in_array($_SESSION['user_role'], ['controller', 'viewer', 'admin'])): ?>
                                    <li><a href="<?php echo BASE_URL; ?>dashboard" class="text-decoration-none"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo BASE_URL; ?>customer-tickets" class="text-decoration-none"><i class="fas fa-ticket-alt"></i> My Support Tickets</a></li>
                                <li><a href="<?php echo BASE_URL; ?>grievances/my" class="text-decoration-none"><i class="fas fa-list"></i> My Grievances</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}

.btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
}

.alert {
    border-radius: 10px;
    text-align: left;
}

.list-unstyled li {
    margin-bottom: 8px;
}

.list-unstyled a:hover {
    color: var(--bs-primary) !important;
}

@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
    
    .d-md-flex {
        display: block !important;
    }
    
    .btn {
        margin-bottom: 10px;
        width: 100%;
    }
    
    .col-md-6 {
        margin-bottom: 20px;
    }
}
</style>

<script>
// Log 404 error for analytics
if (typeof Logger !== 'undefined') {
    Logger.info('404 Page Viewed', {
        'requested_url': window.location.href,
        'referrer': document.referrer || 'direct',
        'user_agent': navigator.userAgent
    });
}

// Auto-suggest based on URL
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const suggestions = document.createElement('div');
    suggestions.className = 'alert alert-secondary mt-3';
    
    let suggestedLinks = [];
    
    // Suggest similar pages based on URL
    if (currentPath.includes('login')) {
        suggestedLinks.push('<a href="<?php echo BASE_URL; ?>login" class="alert-link">Login Page</a>');
    } else if (currentPath.includes('dashboard')) {
        suggestedLinks.push('<a href="<?php echo BASE_URL; ?>dashboard" class="alert-link">Dashboard</a>');
    } else if (currentPath.includes('grievance') || currentPath.includes('complaint')) {
                                suggestedLinks.push('<a href="<?php echo BASE_URL; ?>customer-tickets" class="alert-link">My Support Tickets</a>');
        suggestedLinks.push('<a href="<?php echo BASE_URL; ?>grievances/my" class="alert-link">My Grievances</a>');
    }
    
    if (suggestedLinks.length > 0) {
        suggestions.innerHTML = '<i class="fas fa-lightbulb"></i> <strong>Did you mean:</strong> ' + suggestedLinks.join(' | ');
        document.querySelector('.card-body').appendChild(suggestions);
    }
});
</script>

