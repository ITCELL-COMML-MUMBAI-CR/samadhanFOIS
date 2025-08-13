<?php
/**
 * Generic Error Page
 * Shown when system errors occur
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    header('Location: /');
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-danger text-white text-center">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h3 class="fw-bold">System Error</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <p class="lead text-muted">
                            We're sorry, but something went wrong on our end.
                        </p>
                        <p class="text-muted">
                            Our technical team has been notified and is working to resolve this issue.
                        </p>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>What you can do:</strong>
                        <ul class="list-unstyled mt-2 mb-0">
                            <li>• Try refreshing the page</li>
                            <li>• Go back to the previous page</li>
                            <li>• Contact support if the problem persists</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button onclick="history.back()" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </button>
                        <a href="<?php echo BASE_URL; ?>home" class="btn btn-primary">
                            <i class="fas fa-home"></i> Home Page
                        </a>
                        <button onclick="location.reload()" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Refresh
                        </button>
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
            
            <!-- Error ID for support reference -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    Error Reference: #<?php echo date('YmdHis') . '-' . substr(md5(uniqid()), 0, 8); ?>
                </small>
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
}
</style>

<script>
// Auto-refresh after 30 seconds if user doesn't take action
setTimeout(function() {
    if (confirm('Would you like to try refreshing the page automatically?')) {
        location.reload();
    }
}, 30000);
</script>
