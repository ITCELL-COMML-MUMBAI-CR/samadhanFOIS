<?php
/**
 * Support & Assistance Page
 * Three-Column Layout for Customer Support Tickets
 * Filters | Support Tickets List | Ticket Details
 */

// Data is passed from the controller
// $supportTickets, $totalCount, $currentUser, $error, $success, $status, $priority, $search, $view, $page, $totalPages, $statistics

// Check if data is being passed correctly
if (!isset($currentUser)) {
    echo '<div class="alert alert-danger">Error: No user data available</div>';
    return;
}
?>
<!-- Page Header with Title and New Support Ticket Button -->
<div class="page-header-section">
    <div class="page-title-section">
        <h1 class="page-title">
            <i class="fas fa-headset text-primary"></i> Support & Assistance
        </h1>
    </div>
    <div class="page-actions-section">
        <button class="btn btn-primary btn-new-ticket" onclick="createNewTicket()">
            <i class="fas fa-plus"></i> New Support Ticket
        </button>
    </div>
</div>

<!-- Three-Column Support & Assistance Layout -->
<div class="three-column-layout">
    <!-- First Column: Filters -->
    <div class="filters-column">
        <div class="filters-header">
            <h5 class="filters-title">
                <i class="fas fa-filter text-primary"></i> Filters
            </h5>
        </div>
       
        <div class="filters-list">
            <div class="filter-item <?php echo $view === 'all' && empty($status) ? 'active' : ''; ?>" data-filter="all">
                <i class="fas fa-globe"></i>
                <span>All Support Tickets</span>
                <span class="filter-count"><?php echo $statistics['total']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'pending' ? 'active' : ''; ?>" data-filter="pending">
                <i class="fas fa-clock"></i>
                <span>Pending</span>
                <span class="filter-count"><?php echo $statistics['pending']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'replied' ? 'active' : ''; ?>" data-filter="replied">
                <i class="fas fa-reply"></i>
                <span>Replied</span>
                <span class="filter-count"><?php echo $statistics['replied']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'closed' ? 'active' : ''; ?>" data-filter="closed">
                <i class="fas fa-check-circle"></i>
                <span>Closed</span>
                <span class="filter-count"><?php echo $statistics['closed']; ?></span>
            </div>
            
            <div class="filter-item <?php echo $status === 'reverted' ? 'active' : ''; ?>" data-filter="reverted">
                <i class="fas fa-undo"></i>
                <span>Reverted</span>
                <span class="filter-count"><?php echo $statistics['reverted']; ?></span>
            </div>
        </div>
        
        
    </div>

    <!-- Second Column: Support Tickets List -->
    <div class="list-column">
        <div class="list-header">
            <h5 class="list-title">
                <i class="fas fa-list text-primary"></i> 
                <span id="listTitle">All Support Tickets</span>
                <span class="list-subtitle">as per filters</span>
            </h5>
            <div class="list-actions">
                <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Date Filter -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="date-filter-group">
                    <label for="dateFilter" class="filter-label">
                        <i class="fas fa-calendar"></i> Date Filter
                    </label>
                    <select class="form-control form-control-sm" id="dateFilter">
                        <option value="">All Dates</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="custom-date-group" id="customDateGroup" style="display: none;">
                    <div class="date-inputs">
                        <input type="date" class="form-control form-control-sm" id="startDate" placeholder="Start Date">
                        <span class="date-separator">to</span>
                        <input type="date" class="form-control form-control-sm" id="endDate" placeholder="End Date">
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-section">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Search support tickets..." 
                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button type="button" class="search-clear-btn" id="searchClearBtn" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Priority Filter -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="priority-filter-group">
                    <label for="priorityFilter" class="filter-label">
                        <i class="fas fa-flag"></i> Priority Filter
                    </label>
                    <select class="form-control form-control-sm" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
                <div class="sort-group">
                    <label class="filter-label">
                        <i class="fas fa-sort"></i> Sort By
                    </label>
                    <select class="form-control form-control-sm" id="sortFilter">
                        <option value="priority">Priority (High to Low)</option>
                        <option value="date">Date (Newest First)</option>
                        <option value="date_old">Date (Oldest First)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="support-tickets-list" id="supportTicketsList">
            <?php if (empty($supportTickets)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox fa-2x text-muted"></i>
                    </div>
                    <h6 class="empty-title">No support tickets found</h6>
                    <p class="empty-message">
                        No support tickets match your current filters.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($supportTickets as $ticket): ?>
                    <div class="support-ticket-item" data-ticket-id="<?php echo htmlspecialchars($ticket['complaint_id'] ?? ''); ?>">
                        <div class="ticket-header">
                            <div class="ticket-id">
                                <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($ticket['complaint_id'] ?? ''); ?>
                            </div>
                            <div class="ticket-time">
                                <?php echo date('d M Y, H:i', strtotime($ticket['created_at'])); ?>
                            </div>
                        </div>
                        
                                                 <div class="ticket-type">
                             <strong><?php echo htmlspecialchars($ticket['Type'] ?? 'Not specified'); ?></strong>
                             <?php if (!empty($ticket['Subtype'])): ?>
                                 <span class="subtype">- <?php echo htmlspecialchars($ticket['Subtype'] ?? ''); ?></span>
                             <?php endif; ?>
                             <?php if (!empty($ticket['wagon_type'])): ?>
                                 <div class="wagon-info">
                                     <small class="text-muted">
                                         <i class="fas fa-train"></i> 
                                         <?php echo htmlspecialchars($ticket['wagon_code'] ? $ticket['wagon_code'] . ' - ' . $ticket['wagon_type'] : $ticket['wagon_type']); ?>
                                     </small>
                                 </div>
                             <?php endif; ?>
                         </div>
                        
                        <div class="ticket-preview">
                            <?php echo htmlspecialchars(substr($ticket['description'] ?? '', 0, 100)); ?>
                            <?php if (strlen($ticket['description'] ?? '') > 100): ?>
                                <span class="text-muted">...</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ticket-badges">
                            <?php 
                            $priority = $ticket['display_priority'] ?? $ticket['priority'] ?? 'medium';
                            $status = $ticket['status'] ?? 'pending';
                            ?>
                            <?php if (strtolower($status) !== 'closed'): ?>
                                <span class="badge priority-<?php echo strtolower($priority); ?>">
                                    <i class="fas fa-flag"></i> <?php echo ucfirst(strtolower($priority)); ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge status-<?php echo str_replace('_', '-', strtolower($status)); ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Third Column: Ticket Details -->
    <div class="details-column">
        <!-- Default State -->
        <div class="default-state" id="defaultState">
            <div class="default-content">
                <div class="default-icon">
                    <i class="fas fa-file-alt fa-3x text-muted"></i>
                </div>
                <h5 class="default-title">Select a Support Ticket</h5>
                <p class="default-message">
                    Choose a support ticket from the list to view full details and track progress.
                </p>
            </div>
        </div>

        <!-- Ticket Details (Hidden by default) -->
        <div class="ticket-details" id="ticketDetails" style="display: none;">
            <!-- Ticket Header with Number and Actions -->
            <div class="ticket-detail-header">
                <div class="ticket-number-section">
                    <h5 class="ticket-number" id="detailTicketId">
                        <i class="fas fa-hashtag"></i> <span id="ticketIdText"></span>
                    </h5>
                    <div class="ticket-badges-section">
                        <div class="ticket-status-badge" id="detailStatus"></div>
                        <div class="ticket-priority-badge" id="detailPriority" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-sm btn-outline-primary" id="viewDetailsBtn" title="View Full Details" style="display: none;">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-success" id="addResponseBtn" title="Add Response" style="display: none;">
                        <i class="fas fa-reply"></i> Respond
                    </button>
                </div>
            </div>

            <!-- Ticket Information -->
            <div class="ticket-info-section">
                <div class="info-group">
                    <label class="info-label">Ticket Type</label>
                    <div class="info-content" id="detailTicketType"></div>
                </div>
                
                                 <div class="info-group">
                     <label class="info-label">Location</label>
                     <div class="info-content" id="detailLocation"></div>
                 </div>
                 
                 <div class="info-group">
                     <label class="info-label">Wagon</label>
                     <div class="info-content" id="detailWagon"></div>
                 </div>
                
                <div class="info-group">
                    <label class="info-label">Priority</label>
                    <div class="info-content" id="detailPriorityInfo"></div>
                </div>
                

                
                <div class="info-group">
                    <label class="info-label">Created Date</label>
                    <div class="info-content" id="detailCreatedDate"></div>
                </div>
                
                <div class="info-group">
                    <label class="info-label">Last Updated</label>
                    <div class="info-content" id="detailUpdatedDate"></div>
                </div>
                
                <div class="info-group full-width">
                    <label class="info-label">Description</label>
                    <div class="info-content description-text" id="detailDescription"></div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="transaction-history">
                <h6 class="history-title">
                    <i class="fas fa-history"></i> Progress History
                </h6>
                <div class="history-list" id="transactionHistory">
                    <!-- Transaction history will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Current view and status for filtering
const currentView = '<?php echo $view; ?>';
const currentStatus = '<?php echo $status; ?>';
</script>
<script src="<?php echo BASE_URL; ?>js/support_assistance.js"></script>
