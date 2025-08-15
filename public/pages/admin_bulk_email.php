<?php
// Get users from controller data
$users = $data['users'] ?? [];

// Email templates
$emailTemplates = [
    'sampark_invitation' => [
        'name' => 'SAMPARK Portal Invitation',
        'subject' => 'Welcome to SAMPARK FOIS - Railway Complaint Management System',
        'content' => '<p>Dear {name},</p>
<p>You have been invited to join the SAMPARK FOIS (Feedback, Opinions, Information, Suggestions) portal - our comprehensive Railway Complaint Management System.</p>
<p><strong>Portal Features:</strong></p>
<ul>
<li>Submit and track complaints easily</li>
<li>Real-time status updates</li>
<li>Direct communication with railway officials</li>
<li>Comprehensive reporting and analytics</li>
</ul>
<p><strong>Your Login Details:</strong></p>
<ul>
<li>Login ID: {login_id}</li>
<li>Portal URL: {portal_url}</li>
</ul>
<p>Please contact the system administrator if you need assistance with your login credentials.</p>
<p>Best regards,<br>SAMPARK FOIS Team<br>Central Railway</p>'
    ],
    'system_maintenance' => [
        'name' => 'System Maintenance Notice',
        'subject' => 'SAMPARK FOIS - Scheduled Maintenance Notice',
        'content' => '<p>Dear {name},</p>
<p>This is to inform you that the SAMPARK FOIS portal will undergo scheduled maintenance on {maintenance_date} from {maintenance_time}.</p>
<p><strong>Maintenance Details:</strong></p>
<ul>
<li>Date: {maintenance_date}</li>
<li>Time: {maintenance_time}</li>
<li>Duration: Approximately 2 hours</li>
<li>Services Affected: Portal access and complaint submission</li>
</ul>
<p>We apologize for any inconvenience this may cause. The portal will be fully functional after the maintenance period.</p>
<p>For urgent matters during maintenance, please contact: sampark-admin@itcellbbcr.in</p>
<p>Thank you for your understanding.</p>
<p>Best regards,<br>SAMPARK FOIS Team<br>Central Railway</p>'
    ],
    'policy_update' => [
        'name' => 'Policy Update Notification',
        'subject' => 'SAMPARK FOIS - Important Policy Updates',
        'content' => '<p>Dear {name},</p>
<p>We would like to inform you about important updates to the SAMPARK FOIS portal policies and procedures.</p>
<p><strong>Key Updates:</strong></p>
<ul>
<li>Enhanced complaint categorization</li>
<li>Improved response time commitments</li>
<li>New evidence upload features</li>
<li>Updated user guidelines</li>
</ul>
<p>Please review the updated policies in the portal under the "Guidelines" section.</p>
<p>These changes are effective immediately and will help improve our service delivery.</p>
<p>If you have any questions about these updates, please contact the system administrator.</p>
<p>Best regards,<br>SAMPARK FOIS Team<br>Central Railway</p>'
    ],
    'custom' => [
        'name' => 'Custom Email',
        'subject' => '',
        'content' => ''
    ]
];
?>

<div class="container-fluid">
    <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="fas fa-envelope text-primary"></i> Bulk Email Management</h1>
                <div>
                    <a href="<?php echo BASE_URL; ?>admin/email-templates" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-envelope-open-text"></i> Manage Templates
                    </a>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form id="bulkEmailForm" method="POST" action="<?php echo BASE_URL; ?>api/bulk_email">
        <div class="row">
            <!-- Recipients Selection -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-users"></i> Select Recipients</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" id="all_users" value="all" checked>
                                <label class="form-check-label" for="all_users">
                                    <strong>Send to All Users</strong>
                                    <small class="text-muted d-block">(<?php echo count($users); ?> users)</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" id="by_role" value="by_role">
                                <label class="form-check-label" for="by_role">
                                    <strong>Send by User Type</strong>
                                    <small class="text-muted d-block">(Select by role)</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" id="select_users" value="select">
                                <label class="form-check-label" for="select_users">
                                    <strong>Select Specific Users</strong>
                                    <small class="text-muted d-block recipient-count-display">(<span class="recipient-count">0</span> selected)</small>
                                </label>
                            </div>
                        </div>

                        <!-- User Type Selection -->
                        <div id="userTypeSelection" class="mt-3" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label"><strong>Select User Types:</strong></label>
                                <div class="row">
                                    <?php
                                    // Group users by role and count them
                                    $roleCounts = [];
                                    foreach ($users as $user) {
                                        $role = $user['role'];
                                        if (!isset($roleCounts[$role])) {
                                            $roleCounts[$role] = 0;
                                        }
                                        $roleCounts[$role]++;
                                    }
                                    ?>
                                    <?php foreach ($roleCounts as $role => $count): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input role-checkbox" type="checkbox" name="selected_roles[]" 
                                                       value="<?php echo htmlspecialchars($role); ?>" 
                                                       id="role_<?php echo htmlspecialchars($role); ?>">
                                                <label class="form-check-label" for="role_<?php echo htmlspecialchars($role); ?>">
                                                    <strong><?php echo ucfirst($role); ?>s</strong>
                                                    <small class="text-muted d-block">(<?php echo $count; ?> users)</small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Selected users will be shown below:</span>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="previewSelectedUsers()">
                                        <i class="fas fa-eye"></i> Preview Users
                                    </button>
                                </div>
                            </div>
                            
                            <div id="selectedUsersPreview" class="user-list" style="max-height: 200px; overflow-y: auto; display: none;">
                                <!-- Selected users will be populated here -->
                            </div>
                        </div>
                        
                        <!-- Individual User Selection -->
                        <div id="userSelection" class="mt-3" style="display: none;">
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllUsers()">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllUsers()">Deselect All</button>
                            </div>
                            <div class="user-list" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($users as $user): ?>
                                    <div class="form-check">
                                        <input class="form-check-input user-checkbox" type="checkbox" name="selected_users[]" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                                               id="user_<?php echo htmlspecialchars($user['login_id']); ?>">
                                        <label class="form-check-label" for="user_<?php echo htmlspecialchars($user['login_id']); ?>">
                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($user['email']); ?> (<?php echo ucfirst($user['role']); ?>)</small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Composition -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-edit"></i> Compose Email</h5>
                    </div>
                    <div class="card-body">
                        <!-- Template Selection -->
                        <div class="mb-3">
                            <label for="email_template" class="form-label">Email Template</label>
                            <select class="form-select" id="email_template" name="template">
                                <option value="">-- Select Template --</option>
                                <?php foreach ($emailTemplates as $key => $template): ?>
                                    <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($template['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Email Fields -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="email_subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="email_subject" name="subject" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email_cc" class="form-label">CC (Optional)</label>
                                    <input type="text" class="form-control" id="email_cc" name="cc" placeholder="email1@example.com, email2@example.com">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_content" class="form-label">Email Content *</label>
                            <textarea class="form-control" id="email_content" name="content" rows="12" required></textarea>
                            <div class="form-text">
                                <strong>Available placeholders:</strong> {name}, {login_id}, {email}, {department}, {role}, {portal_url}
                                <br><small class="text-muted">These will be automatically replaced with actual user data when sending.</small>
                            </div>
                        </div>

                        <!-- Template Variables (for maintenance template) -->
                        <div id="templateVariables" class="mb-3" style="display: none;">
                            <h6>Template Variables</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="maintenance_date" class="form-label">Maintenance Date</label>
                                    <input type="date" class="form-control" id="maintenance_date" name="maintenance_date">
                                </div>
                                <div class="col-md-6">
                                    <label for="maintenance_time" class="form-label">Maintenance Time</label>
                                    <input type="text" class="form-control" id="maintenance_time" name="maintenance_time" placeholder="e.g., 10:00 PM - 12:00 AM">
                                </div>
                            </div>
                        </div>

                        <!-- Send Button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="previewEmail()">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="testEmail()">
                                    <i class="fas fa-paper-plane"></i> Send Test
                                </button>
                            </div>
                            <button type="submit" class="btn btn-railway-primary" id="sendButton">
                                <i class="fas fa-envelope"></i> Send Bulk Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="emailPreview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_email" class="form-label">Test Email Address</label>
                    <input type="email" class="form-control" id="test_email" placeholder="Enter email address for test">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">Send Test</button>
            </div>
        </div>
    </div>
</div>

<!-- Include CSS and JS files -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/bulk_email.css">

<script>
// Email templates data
const emailTemplates = <?php echo json_encode($emailTemplates); ?>;
</script>
<script src="<?php echo BASE_URL; ?>js/bulk_email.js"></script>
