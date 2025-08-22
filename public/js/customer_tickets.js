/**
 * Customer Tickets JavaScript
 * Handles DataTables initialization and modal interactions for the customer-facing ticket page.
 *
 * This script relies on jQuery and the DataTables library, which must be loaded
 * before this script is executed.
 */

// Use existing BASE_URL if available, otherwise define a fallback.
// This ensures API calls are directed to the correct path.
const CUSTOMER_TICKETS_BASE_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : window.location.origin + '/samadhanFOIS/public/';

// Main function to run when the document is fully loaded and ready.
$(document).ready(function() {
    console.log("Customer Tickets JS Loaded. Initializing...");

    // 1. Initialize the DataTable on the table with the ID 'ticketsTable'.
    // This adds search, sorting, and pagination functionality.
    const ticketsTable = $('#ticketsTable').DataTable({
        "responsive": true, // Makes the table responsive to screen size changes.
        "pageLength": 10, // Default number of entries to show.
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], // Dropdown options for entries per page.
        "order": [[5, 'desc']], // Default sort order: 6th column (Created Date) descending.
        "language": { // Customize default text.
            "search": "_INPUT_",
            "searchPlaceholder": "Search tickets...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": { "first": "First", "last": "Last", "next": "&rarr;", "previous": "&larr;" }
        },
        "columnDefs": [
            { "orderable": false, "targets": 6 } // Disable sorting on the 'Actions' column (7th column).
        ]
    });
    console.log("DataTable Initialized.");

    // 2. Setup Event Listeners using jQuery's event delegation.
    // This is crucial because DataTables rebuilds the table on sort/pagination,
    // and direct event listeners would be lost. Delegation ensures events work on all table rows.
    $('#ticketsTable tbody').on('click', '.view-ticket-btn', function () {
        const ticketId = $(this).data('ticket-id');
        console.log("View button clicked for ticket:", ticketId);
        viewTicket(ticketId);
    });

    $('#ticketsTable tbody').on('click', '.give-feedback-btn', function () {
        const ticketId = $(this).data('ticket-id');
        console.log("Feedback button clicked for ticket:", ticketId);
        giveFeedback(ticketId);
    });

    $('#ticketsTable tbody').on('click', '.provide-info-btn', function () {
        const ticketId = $(this).data('ticket-id');
        console.log("Add Info button clicked for ticket:", ticketId);
        provideAdditionalInfo(ticketId);
    });

    // Event listeners for modal submission buttons.
    $('#submitFeedbackBtn').on('click', function() {
        submitFeedback();
    });

    $('#submitAdditionalInfoBtn').on('click', function() {
        submitAdditionalInfo();
    });

    console.log("Event listeners are set up.");
});


// --- Function Definitions ---

/**
 * Fetches and displays the details for a specific ticket in a modal.
 * @param {string} ticketId - The ID of the ticket to view.
 */
function viewTicket(ticketId) {
    if (!ticketId) return;

    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    const content = $('#ticketDetailsContent');
    
    // Show a loading state in the modal body.
    content.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading ticket details...</p></div>');
    modal.show();

    // Fetch the ticket data from the API.
    fetch(`${CUSTOMER_TICKETS_BASE_URL}customer-tickets/details/${ticketId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.error || !data.data) {
                throw new Error(data.message || 'Invalid data received');
            }
            // Populate the modal with the ticket details and load its history.
            content.html(generateTicketDetailsHTML(data.data));
            loadTransactionHistory(ticketId);
        })
        .catch(error => {
            console.error('Error fetching ticket details:', error);
            content.html(`<div class="alert alert-danger">Error: Could not load ticket details. ${error.message}</div>`);
        });
}

/**
 * Opens the "Give Feedback" modal and sets the ticket ID.
 * @param {string} ticketId - The ID of the ticket.
 */
function giveFeedback(ticketId) {
    $('#feedbackTicketId').val(ticketId);
    $('#feedbackForm')[0].reset();
    $('.rating-stars input').prop('checked', false); // Reset rating stars.
    const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
}

/**
 * Opens the "Provide Additional Info" modal and sets the ticket ID.
 * @param {string} ticketId - The ID of the ticket.
 */
function provideAdditionalInfo(ticketId) {
    $('#additionalInfoTicketId').val(ticketId);
    $('#additionalInfoForm')[0].reset();
    const modal = new bootstrap.Modal(document.getElementById('additionalInfoModal'));
    modal.show();
}

/**
 * Submits the feedback form via an AJAX POST request.
 */
function submitFeedback() {
    const form = document.getElementById('feedbackForm');
    const formData = new FormData(form);
    const rating = formData.get('rating');

    // Basic validation.
    if (!rating) {
        if (window.showAlert) showAlert('Please select a rating.', 'warning');
        else alert('Please select a rating.');
        return;
    }
    
    const submitBtn = $('#submitFeedbackBtn');
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');

    fetch(`${CUSTOMER_TICKETS_BASE_URL}customer-tickets/feedback`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message);
        }
        if (window.showAlert) showAlert('Feedback submitted successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
        setTimeout(() => window.location.reload(), 1500); // Reload page to reflect changes.
    })
    .catch(error => {
        console.error('Error submitting feedback:', error);
        if (window.showAlert) showAlert(`Error: ${error.message}`, 'danger');
    })
    .finally(() => {
        submitBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Submit Feedback');
    });
}

/**
 * Submits the additional info form via an AJAX POST request.
 */
function submitAdditionalInfo() {
    const form = document.getElementById('additionalInfoForm');
    const formData = new FormData(form);
    const additionalInfo = formData.get('additional_info');
    
    // Basic validation.
    if (!additionalInfo || additionalInfo.trim() === '') {
        if (window.showAlert) showAlert('Please provide the requested information.', 'warning');
        else alert('Please provide the requested information.');
        return;
    }

    const submitBtn = $('#submitAdditionalInfoBtn');
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');

    fetch(`${CUSTOMER_TICKETS_BASE_URL}customer-tickets/additional-info`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message);
        }
        if (window.showAlert) showAlert('Information submitted successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('additionalInfoModal')).hide();
        setTimeout(() => window.location.reload(), 1500); // Reload page to reflect changes.
    })
    .catch(error => {
        console.error('Error submitting additional info:', error);
        if (window.showAlert) showAlert(`Error: ${error.message}`, 'danger');
    })
    .finally(() => {
        submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Information');
    });
}

// --- Helper Functions ---

/**
 * Generates the HTML content for the ticket details modal body.
 * @param {object} ticket - The ticket data object from the API.
 * @returns {string} The generated HTML string.
 */
function generateTicketDetailsHTML(ticket) {
    return `
        <div class="row">
            <div class="col-lg-8">
                <h5><i class="fas fa-info-circle text-primary"></i> Ticket Information</h5>
                <table class="table table-sm table-borderless">
                    <tr><th style="width: 150px;">Ticket ID:</th><td><strong>#${ticket.complaint_id}</strong></td></tr>
                    <tr><th>Type:</th><td>${ticket.Type || 'N/A'}</td></tr>
                    <tr><th>Subtype:</th><td>${ticket.Subtype || 'N/A'}</td></tr>
                    <tr><th>Status:</th><td><span class="status-badge status-${ticket.status.toLowerCase()}">${ticket.status}</span></td></tr>
                    <tr><th>Created:</th><td>${formatDate(ticket.created_at)}</td></tr>
                    <tr><th>Location:</th><td>${ticket.Location || 'N/A'}</td></tr>
                </table>
                <h6>Description:</h6>
                <p class="bg-light p-3 rounded">${ticket.description || 'No description.'}</p>
                
                ${ticket.action_taken ? `<h6>Action Taken:</h6><p class="bg-light p-3 rounded">${ticket.action_taken}</p>` : ''}
            </div>
            <div class="col-lg-4">
                <h5><i class="fas fa-history text-primary"></i> History</h5>
                <div id="transactionHistory" class="transaction-history-section">
                    <div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Fetches and displays the transaction history for a ticket inside the details modal.
 * @param {string} ticketId - The ID of the ticket.
 */
function loadTransactionHistory(ticketId) {
    const historyContainer = $('#transactionHistory');
    
    fetch(`${CUSTOMER_TICKETS_BASE_URL}customer-tickets/history/${ticketId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error || !data.data) {
                throw new Error(data.message || 'No history data found');
            }
            historyContainer.html(generateTransactionHistoryHTML(data.data));
        })
        .catch(error => {
            console.error('Error fetching history:', error);
            historyContainer.html(`<div class="alert alert-warning p-2 small">${error.message}</div>`);
        });
}

/**
 * Generates the HTML for the transaction history timeline.
 * @param {Array} transactions - An array of transaction objects.
 * @returns {string} The generated HTML string.
 */
function generateTransactionHistoryHTML(transactions) {
    if (transactions.length === 0) {
        return '<p class="text-muted small">No history available.</p>';
    }
    let html = '<div class="timeline">';
    transactions.forEach(t => {
        html += `
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong class="timeline-title">${t.transaction_type.replace(/_/g, ' ').toUpperCase()}</strong>
                        <span class="timeline-date small text-muted">${formatDate(t.created_at)}</span>
                    </div>
                    <p class="timeline-body small mb-0">${t.remarks}</p>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

/**
 * Formats a date string into a more readable format.
 * @param {string} dateString - The date string to format.
 * @returns {string} The formatted date.
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-IN', options);
}

/**
 * Shows an alert message to the user.
 * @param {string} message - The message to display.
 * @param {string} type - The type of alert (e.g., 'success', 'warning', 'danger').
 */
function showAlert(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        alert(message);
    }
}
