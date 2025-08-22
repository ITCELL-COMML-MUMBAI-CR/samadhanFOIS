/**
 * Customer Tickets JavaScript
 * Handles DataTables initialization and modal interactions
 */

// Global variables
let ticketsTable;
// Use existing BASE_URL if available, otherwise define it
const CUSTOMER_TICKETS_BASE_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/');

/**
 * View ticket details
 */
function viewTicket(ticketId) {
    console.log('viewTicket function called with ticketId:', ticketId);
    if (!ticketId) return;
    
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    const content = document.getElementById('ticketDetailsContent');
    content.innerHTML = '<div class="loading-spinner"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading ticket details...</p></div>';
    modal.show();
    
    // Fetch ticket details
    fetch(`${CUSTOMER_TICKETS_BASE_URL}api/complaints/${ticketId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            } else {
                content.innerHTML = generateTicketDetailsHTML(data.data);
                // Load transaction history
                loadTransactionHistory(ticketId);
            }
        })
        .catch(error => {
            console.error('Error fetching ticket details:', error);
            content.innerHTML = '<div class="alert alert-danger">Failed to load ticket details. Please try again.</div>';
        });
}

/**
 * Give feedback for a ticket
 */
function giveFeedback(ticketId) {
    if (!ticketId) return;
    
    // Set the ticket ID in the form
    document.getElementById('feedbackTicketId').value = ticketId;
    
    // Reset form
    document.getElementById('feedbackForm').reset();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
}

/**
 * Provide additional information for a ticket
 */
function provideAdditionalInfo(ticketId) {
    if (!ticketId) return;
    
    // Set the ticket ID in the form
    document.getElementById('additionalInfoTicketId').value = ticketId;
    
    // Reset form
    document.getElementById('additionalInfoForm').reset();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('additionalInfoModal'));
    modal.show();
}

/**
 * Submit feedback
 */
function submitFeedback() {
    const form = document.getElementById('feedbackForm');
    const formData = new FormData(form);
    
    // Validate rating
    const rating = formData.get('rating');
    if (!rating) {
        showAlert('Please select a rating', 'warning');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#feedbackModal .btn-success');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    fetch(`${CUSTOMER_TICKETS_BASE_URL}api/complaints/feedback`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            showAlert(data.message, 'error');
        } else {
            showAlert('Feedback submitted successfully!', 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
            modal.hide();
            // Refresh table
            if (ticketsTable) {
                ticketsTable.ajax.reload();
            } else {
                window.location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error submitting feedback:', error);
        showAlert('Failed to submit feedback. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Submit additional information
 */
function submitAdditionalInfo() {
    const form = document.getElementById('additionalInfoForm');
    const formData = new FormData(form);
    
    // Validate additional info
    const additionalInfo = formData.get('additional_info');
    if (!additionalInfo.trim()) {
        showAlert('Please provide additional information', 'warning');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#additionalInfoModal .btn-warning');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    fetch(`${CUSTOMER_TICKETS_BASE_URL}api/complaints/additional-info`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            showAlert(data.message, 'error');
        } else {
            showAlert('Additional information submitted successfully!', 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('additionalInfoModal'));
            modal.hide();
            // Refresh table
            if (ticketsTable) {
                ticketsTable.ajax.reload();
            } else {
                window.location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error submitting additional info:', error);
        showAlert('Failed to submit additional information. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Refresh the DataTable
 */
function refreshTable() {
    if (ticketsTable) {
        ticketsTable.ajax.reload();
    } else {
        window.location.reload();
    }
}

// Immediately expose all functions to global scope
window.viewTicket = viewTicket;
window.giveFeedback = giveFeedback;
window.provideAdditionalInfo = provideAdditionalInfo;
window.submitFeedback = submitFeedback;
window.submitAdditionalInfo = submitAdditionalInfo;
window.refreshTable = refreshTable;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Customer Tickets JS: DOM loaded, initializing...');
    initializeDataTable();
    initializeModals();
    initializeEventListeners();
});

/**
 * Initialize DataTables
 */
function initializeDataTable() {
    ticketsTable = $('#ticketsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        order: [[5, 'desc']], // Sort by created date descending
        columnDefs: [
            {
                targets: [0], // Ticket ID column
                width: '100px'
            },
            {
                targets: [1], // Type column
                width: '150px'
            },
            {
                targets: [2], // Description column
                width: '300px'
            },
            {
                targets: [3], // Location column
                width: '120px'
            },
            {
                targets: [4], // Status column
                width: '120px'
            },
            {
                targets: [5], // Created Date column
                width: '120px'
            },
            {
                targets: [6], // Actions column
                width: '120px',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            search: "Search tickets:",
            lengthMenu: "Show _MENU_ tickets per page",
            info: "Showing _START_ to _END_ of _TOTAL_ tickets",
            infoEmpty: "Showing 0 to 0 of 0 tickets",
            infoFiltered: "(filtered from _MAX_ total tickets)",
            emptyTable: "No tickets found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function() {
            console.log('DataTable initialization complete');
            // Add custom styling to DataTables elements
            this.api().columns().every(function() {
                let column = this;
                let title = column.header().textContent;
                
                // Add tooltips to column headers
                $(column.header()).attr('title', 'Click to sort by ' + title);
            });
        }
    });
    
    // Add event listeners after DataTable is initialized
    $('#ticketsTable').on('click', '.view-ticket-btn', function() {
        const ticketId = $(this).data('ticket-id');
        console.log('View ticket button clicked via DataTable event for ticket:', ticketId);
        viewTicket(ticketId);
    });
    
    $('#ticketsTable').on('click', '.give-feedback-btn', function() {
        const ticketId = $(this).data('ticket-id');
        console.log('Give feedback button clicked via DataTable event for ticket:', ticketId);
        giveFeedback(ticketId);
    });
    
    $('#ticketsTable').on('click', '.provide-info-btn', function() {
        const ticketId = $(this).data('ticket-id');
        console.log('Provide info button clicked via DataTable event for ticket:', ticketId);
        provideAdditionalInfo(ticketId);
    });
}

/**
 * Initialize modal interactions
 */
function initializeModals() {
    // Initialize Bootstrap modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
    
    // Add event listeners for modal close
    const modalCloseButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    });
}

/**
 * Initialize event listeners for buttons
 */
function initializeEventListeners() {
    console.log('Initializing event listeners for modals...');
    
    // Modal submit buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.submit-feedback-btn')) {
            console.log('Submit feedback button clicked');
            submitFeedback();
        }
        
        if (e.target.closest('.submit-additional-info-btn')) {
            console.log('Submit additional info button clicked');
            submitAdditionalInfo();
        }
    });
}

/**
 * Generate HTML for ticket details
 */
function generateTicketDetailsHTML(ticket) {
    return `
        <div class="ticket-details-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="section-title">
                        <i class="fas fa-info-circle text-primary"></i> Ticket Information
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Ticket ID</label>
                            <span><strong>#${ticket.complaint_id}</strong></span>
                        </div>
                        <div class="info-item">
                            <label>Type</label>
                            <span>${ticket.Type || 'Not specified'}</span>
                        </div>
                        <div class="info-item">
                            <label>Subtype</label>
                            <span>${ticket.Subtype || 'Not specified'}</span>
                        </div>
                        <div class="info-item">
                            <label>Status</label>
                            <span class="status-badge status-${ticket.status.toLowerCase()}">
                                <i class="fas fa-${getStatusIcon(ticket.status)}"></i>
                                ${ticket.status}
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Created Date</label>
                            <span>${formatDate(ticket.created_at)}</span>
                        </div>
                        <div class="info-item">
                            <label>Location</label>
                            <span>${ticket.Location || 'Not specified'}</span>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <label>Description</label>
                        <div class="description-content">${ticket.description || 'No description provided'}</div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="ticket-actions-section">
                        <h6><i class="fas fa-tools text-primary"></i> Actions</h6>
                        <div class="action-buttons">
                            ${ticket.status.toLowerCase() === 'replied' ? 
                                `<button class="btn btn-success btn-sm w-100 mb-2 give-feedback-btn" data-ticket-id="${ticket.complaint_id}">
                                    <i class="fas fa-star"></i> Give Feedback
                                </button>` : ''
                            }
                            ${ticket.status.toLowerCase() === 'reverted' ? 
                                `<button class="btn btn-warning btn-sm w-100 mb-2 provide-info-btn" data-ticket-id="${ticket.complaint_id}">
                                    <i class="fas fa-plus-circle"></i> Add Information
                                </button>` : ''
                            }
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="transaction-history-section">
                <div class="section-title">
                    <i class="fas fa-history text-primary"></i> Transaction History
                </div>
                <div id="transactionHistory">
                    <div class="loading-spinner">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading history...</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Load transaction history
 */
function loadTransactionHistory(ticketId) {
    const historyContainer = document.getElementById('transactionHistory');
    if (!historyContainer) return;
    
    fetch(`${CUSTOMER_TICKETS_BASE_URL}api/complaints/${ticketId}/history`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                historyContainer.innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
            } else {
                historyContainer.innerHTML = generateTransactionHistoryHTML(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching transaction history:', error);
            historyContainer.innerHTML = '<div class="alert alert-danger">Failed to load transaction history.</div>';
        });
}

/**
 * Generate HTML for transaction history
 */
function generateTransactionHistoryHTML(transactions) {
    if (!transactions || transactions.length === 0) {
        return '<div class="alert alert-info">No transaction history available.</div>';
    }
    
    let html = '<div class="timeline">';
    transactions.forEach(transaction => {
        const markerClass = getTransactionMarkerClass(transaction.transaction_type);
        html += `
            <div class="timeline-item">
                <div class="timeline-marker ${markerClass}"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <h6 class="timeline-title">${transaction.transaction_type.replace('_', ' ').toUpperCase()}</h6>
                        <span class="timeline-date">${formatDate(transaction.created_at)}</span>
                    </div>
                    <div class="timeline-body">
                        ${transaction.remarks || 'No remarks provided'}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    // Check if SweetAlert2 is available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info',
            text: message,
            icon: type === 'success' ? 'success' : type === 'error' ? 'error' : 'info',
            confirmButtonColor: '#667eea',
            timer: type === 'success' ? 3000 : undefined,
            timerProgressBar: type === 'success'
        });
    } else {
        // Fallback to Bootstrap alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the page
        const container = document.querySelector('.tickets-container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

/**
 * Get status icon
 */
function getStatusIcon(status) {
    switch (status.toLowerCase()) {
        case 'pending': return 'clock';
        case 'replied': return 'reply';
        case 'reverted': return 'undo';
        case 'closed': return 'check-circle';
        default: return 'circle';
    }
}

/**
 * Get transaction marker class
 */
function getTransactionMarkerClass(transactionType) {
    switch (transactionType.toLowerCase()) {
        case 'created': return 'marker-primary';
        case 'replied': return 'marker-success';
        case 'reverted': return 'marker-warning';
        case 'closed': return 'marker-info';
        default: return 'marker-primary';
    }
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
