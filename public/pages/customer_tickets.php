<?php
/**
 * Customer Tickets Page
 * Shows only pending, reverted, and replied tickets for customers
 * Features: View complaint, give feedback, provide additional information
 */

// Include CSRF utility
require_once __DIR__ . '/../../src/utils/CSRF.php';

// Data is passed from the controller
// $tickets, $currentUser, $error, $success

// Check if data is being passed correctly
if (!isset($currentUser)) {
    echo '<div class="alert alert-danger">Error: No user data available</div>';
    return;
}
?>

<div class="page-header-section">
    <div class="page-title-section">
        <h1 class="page-title">
            <i class="fas fa-ticket-alt text-primary"></i> My Support Tickets
        </h1>
        <p class="page-subtitle">Track and manage your support requests</p>
    </div>
    <div class="page-actions-section">
        <a href="<?php echo BASE_URL; ?>support/new" class="btn btn-primary btn-new-ticket">
            <i class="fas fa-plus"></i> New Support Ticket
        </a>
    </div>
</div>

<div class="tickets-container">
    <div class="card">
        <div class="card-body">
            <table id="ticketsTable" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo htmlspecialchars($ticket['complaint_id']); ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($ticket['Type'] ?? 'Not specified'); ?></strong>
                                        <?php if (!empty($ticket['Subtype'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($ticket['Subtype']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="ticket-description-cell">
                                        <?php echo htmlspecialchars(substr($ticket['description'] ?? '', 0, 100)); ?>
                                        <?php if (strlen($ticket['description'] ?? '') > 100): ?>
                                            <span class="text-muted">...</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($ticket['shed_terminal'])): ?>
                                        <i class="fas fa-industry text-muted"></i>
                                        <?php echo htmlspecialchars($ticket['shed_terminal']); ?>
                                        <?php if (!empty($ticket['shed_type'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($ticket['shed_type']); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = 'status-' . str_replace('_', '-', strtolower($ticket['status']));
                                    $statusIcon = '';
                                    switch (strtolower($ticket['status'])) {
                                        case 'pending':
                                            $statusIcon = 'fas fa-clock';
                                            break;
                                        case 'replied':
                                            $statusIcon = 'fas fa-reply';
                                            break;
                                        case 'reverted':
                                            $statusIcon = 'fas fa-undo';
                                            break;
                                        default:
                                            $statusIcon = 'fas fa-circle';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <i class="<?php echo $statusIcon; ?>"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="ticket-date-cell">
                                        <div><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></div>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($ticket['created_at'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-primary btn-sm view-ticket-btn" data-ticket-id="<?php echo htmlspecialchars($ticket['complaint_id']); ?>" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if (strtolower($ticket['status']) === 'replied'): ?>
                                            <button class="btn btn-success btn-sm give-feedback-btn" data-ticket-id="<?php echo htmlspecialchars($ticket['complaint_id']); ?>" title="Give Feedback">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (strtolower($ticket['status']) === 'reverted'): ?>
                                            <button class="btn btn-warning btn-sm provide-info-btn" data-ticket-id="<?php echo htmlspecialchars($ticket['complaint_id']); ?>" title="Add Information">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">
                    <i class="fas fa-star text-warning"></i> Give Feedback
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm">
                    <input type="hidden" id="feedbackTicketId" name="ticket_id">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <div class="rating-stars">
                            <input type="radio" name="rating" value="5" id="star5">
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="feedbackRemarks" class="form-label">Additional Comments (Optional)</label>
                        <textarea class="form-control" id="feedbackRemarks" name="remarks" rows="4" 
                                  placeholder="Share your experience with our support team..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="submitFeedbackBtn">
                    <i class="fas fa-check"></i> Submit Feedback
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="additionalInfoModal" tabindex="-1" aria-labelledby="additionalInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="additionalInfoModalLabel">
                    <i class="fas fa-plus-circle text-warning"></i> Provide Additional Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="additionalInfoForm">
                    <input type="hidden" id="additionalInfoTicketId" name="ticket_id">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Your ticket has been reverted for additional information. Please provide the requested details to help us resolve your issue faster.
                    </div>
                    
                    <div class="mb-3">
                        <label for="additionalInfo" class="form-label">Additional Information</label>
                        <textarea class="form-control" id="additionalInfo" name="additional_info" rows="6" 
                                  placeholder="Please provide the additional information requested by our support team..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="submitAdditionalInfoBtn">
                    <i class="fas fa-paper-plane"></i> Submit Information
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketDetailsModalLabel">
                    <i class="fas fa-ticket-alt text-primary"></i> Ticket Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                </div>
        </div>
    </div>
</div>