<?php
/**
 * Admin Logs Page
 * View system logs (Admin only)
 */

require_once '../src/utils/SessionManager.php';
require_once '../src/utils/Logger.php';

// Require admin access
SessionManager::requireRole('admin');

$currentUser = SessionManager::getCurrentUser();
$action = $_GET['action'] ?? 'view';
$message = '';

// Handle log actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'cleanup':
            $days = (int)($_POST['days'] ?? 30);
            Logger::cleanup($days);
            $message = "Log cleanup completed. Kept entries from last {$days} days.";
            Logger::logUserAction('LOG_CLEANUP', $currentUser['login_id'], ['days_kept' => $days]);
            break;
            
        case 'test':
            if (Logger::test()) {
                $message = "Logger test successful!";
            } else {
                $message = "Logger test failed!";
            }
            break;
    }
}

// Get recent logs
$recentLogs = Logger::getRecentLogs(200);
$logFile = Logger::getLogFile();
$logSize = file_exists($logFile) ? number_format(filesize($logFile) / 1024, 2) : 0;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt"></i> System Logs
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Log Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo count($recentLogs); ?></h5>
                                    <p class="mb-0">Recent Entries</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo $logSize; ?> KB</h5>
                                    <p class="mb-0">Log File Size</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo date('d-M-Y'); ?></h5>
                                    <p class="mb-0">Today's Date</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo is_writable($logFile) ? 'Yes' : 'No'; ?></h5>
                                    <p class="mb-0">Writable</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Log Actions -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="test">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-vial"></i> Test Logger
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cleanup old logs?')">
                                <input type="hidden" name="action" value="cleanup">
                                <div class="input-group" style="max-width: 250px;">
                                    <input type="number" name="days" value="30" min="1" max="365" class="form-control" placeholder="Days to keep">
                                    <button type="submit" class="btn btn-outline-warning">
                                        <i class="fas fa-broom"></i> Cleanup
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Log Viewer -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Recent Log Entries (Last 200)</h6>
                        </div>
                        <div class="card-body">
                            <div style="max-height: 500px; overflow-y: auto; font-family: monospace; font-size: 0.9em; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
                                <?php if (empty($recentLogs)): ?>
                                    <p class="text-muted">No log entries found.</p>
                                <?php else: ?>
                                    <?php foreach (array_reverse($recentLogs) as $logEntry): ?>
                                        <?php
                                        $level = '';
                                        $class = 'text-dark';
                                        
                                        if (strpos($logEntry, '[ERROR]') !== false || strpos($logEntry, '[CRITICAL]') !== false) {
                                            $class = 'text-danger';
                                        } elseif (strpos($logEntry, '[WARNING]') !== false) {
                                            $class = 'text-warning';
                                        } elseif (strpos($logEntry, '[INFO]') !== false) {
                                            $class = 'text-info';
                                        } elseif (strpos($logEntry, '[DEBUG]') !== false) {
                                            $class = 'text-secondary';
                                        }
                                        ?>
                                        <div class="<?php echo $class; ?> mb-1" style="border-left: 3px solid currentColor; padding-left: 8px;">
                                            <?php echo htmlspecialchars($logEntry); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.input-group {
    display: inline-flex;
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 15px;
    }
    
    .col-md-6 {
        margin-bottom: 15px;
    }
}
</style>

<script>
// Auto-refresh logs every 30 seconds
setInterval(function() {
    if (confirm('Refresh logs to see latest entries?')) {
        location.reload();
    }
}, 30000);

// Scroll to bottom of log viewer
document.addEventListener('DOMContentLoaded', function() {
    const logViewer = document.querySelector('[style*="max-height: 500px"]');
    if (logViewer) {
        logViewer.scrollTop = logViewer.scrollHeight;
    }
});
</script>
