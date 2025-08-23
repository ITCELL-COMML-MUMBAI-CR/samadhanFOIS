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

/**
 * File Upload Handler for additional information modal
 */
class AdditionalInfoFileUpload {
    constructor() {
        this.selectedFiles = []; // Array to store selected files
        this.maxAllowedFiles = 3; // Default max files, will be updated based on existing files
        this.initializeFileUpload();
    }

    /**
     * Initialize file upload functionality
     */
    initializeFileUpload() {
        const fileInput = document.getElementById('evidence');
        const fileUploadArea = document.getElementById('fileUploadArea');
        
        if (fileInput && fileUploadArea) {
            // Custom drag and drop functionality
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('dragover');
            });
            
            fileUploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('dragover');
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('dragover');
                
                const droppedFiles = Array.from(e.dataTransfer.files);
                const imagePreview = document.getElementById('imagePreview');
                
                if (droppedFiles.length === 0) {
                    return;
                }
                
                // Check if adding these files would exceed the limit
                if (this.selectedFiles.length + droppedFiles.length > this.maxAllowedFiles) {
                    showAlert(`You can only select up to ${this.maxAllowedFiles} files based on existing evidence. You currently have ${this.selectedFiles.length} files selected.`, 'warning');
                    return;
                }
                
                // Add dropped files to selected files array
                this.selectedFiles = [...this.selectedFiles, ...droppedFiles];
                
                // Update preview
                this.updateFilePreview(imagePreview);
            });
            
            // Click to upload
            fileUploadArea.addEventListener('click', (e) => {
                if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
                    fileInput.click();
                }
            });
            
            // File selection - handles incremental addition
            fileInput.addEventListener('change', (e) => {
                const newFiles = Array.from(e.target.files);
                const imagePreview = document.getElementById('imagePreview');
                
                if (newFiles.length === 0) {
                    return;
                }
                
                // Check if adding these files would exceed the limit
                if (this.selectedFiles.length + newFiles.length > this.maxAllowedFiles) {
                    showAlert(`You can only select up to ${this.maxAllowedFiles} files based on existing evidence. You currently have ${this.selectedFiles.length} files selected.`, 'warning');
                    fileInput.value = '';
                    return;
                }
                
                // Add new files to selected files array
                this.selectedFiles = [...this.selectedFiles, ...newFiles];
                
                // Update preview
                this.updateFilePreview(imagePreview);
                
                // Clear the file input for next selection
                fileInput.value = '';
            });
        }
    }

    /**
     * Update file preview with all selected files
     */
    updateFilePreview(container) {
        if (!container) return;
        
        container.innerHTML = '';
        
        this.selectedFiles.forEach((file, index) => {
            const preview = document.createElement('div');
            preview.className = 'col-md-4 mb-3';
            
            if (file.type.startsWith('image/')) {
                // Image preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `
                        <div class="card file-preview-card">
                            <img src="${e.target.result}" class="card-img-top">
                            <div class="card-body">
                                <button type="button" class="btn btn-outline-danger btn-remove" 
                                        onclick="window.additionalInfoFileUpload.removeFile(${index})" title="Remove file">
                                    <i class="fas fa-times"></i>
                                </button>
                                <small class="text-muted d-block">${file.name}</small>
                                <small class="text-muted">${Math.round(file.size / 1024)} KB</small>
                            </div>
                        </div>
                    `;
                    
                    // Call updateFileCountDisplay after the preview is generated to ensure accurate counts
                    if (typeof updateFileCountDisplay === 'function') {
                        updateFileCountDisplay();
                    }
                };
                reader.readAsDataURL(file);
            } else {
                // Non-image file preview
                preview.innerHTML = `
                    <div class="card file-preview-card">
                        <div class="file-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-outline-danger btn-remove" 
                                    onclick="window.additionalInfoFileUpload.removeFile(${index})" title="Remove file">
                                <i class="fas fa-times"></i>
                            </button>
                            <small class="text-muted d-block">${file.name}</small>
                            <small class="text-muted">${Math.round(file.size / 1024)} KB</small>
                        </div>
                    </div>
                `;
            }
            
            container.appendChild(preview);
        });
        
        // Update file count display for immediate feedback
        if (typeof updateFileCountDisplay === 'function') {
            updateFileCountDisplay();
        }
        
        // Use the dedicated function to update text
        updateFileUploadAreaText();
    }

    /**
     * Remove a file from the selected files array
     */
    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        const imagePreview = document.getElementById('imagePreview');
        this.updateFilePreview(imagePreview);
        
        // Update file count display
        if (typeof updateFileCountDisplay === 'function') {
            updateFileCountDisplay();
        }
        
        // Update file upload area text
        updateFileUploadAreaText();
    }

    /**
     * Reset the file upload component
     */
    reset() {
        this.selectedFiles = [];
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) {
            imagePreview.innerHTML = '';
        }
        
        const fileInput = document.getElementById('evidence');
        if (fileInput) {
            fileInput.value = '';
        }
        
        const uploadArea = document.getElementById('fileUploadArea');
        if (uploadArea) {
            const uploadText = uploadArea.querySelector('p');
            if (uploadText) {
                uploadText.textContent = 'Drag and drop files here or click to select (Max 3 files)';
            }
            uploadArea.classList.remove('disabled');
        }
    }
}

// Create a global instance for the file upload handler
window.additionalInfoFileUpload = null;

// Main function to run when the document is fully loaded and ready.
$(document).ready(function() {
    console.log("Customer Tickets JS Loaded. Initializing...");
    
    // Initialize file upload handler
    window.additionalInfoFileUpload = new AdditionalInfoFileUpload();

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
            //loadTransactionHistory(ticketId);
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
    $('input[name="feedback_rating"]').prop('checked', false); // Reset rating radio buttons.
    const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
}

/**
 * Opens the "Provide Additional Info" modal, fetches existing evidence, and sets up the form.
 * @param {string} ticketId - The ID of the ticket.
 */
function provideAdditionalInfo(ticketId) {
    const modal = new bootstrap.Modal(document.getElementById('additionalInfoModal'));
    const form = document.getElementById('additionalInfoForm');
    const existingEvidenceContainer = document.getElementById('existingEvidenceSection');
    const imagePreview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('evidence');
    const askedInfoSection = document.getElementById('askedInfoSection');

    // Reset form and containers
    form.reset();
    existingEvidenceContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Loading existing evidence...</div>';
    imagePreview.innerHTML = '';
    if(fileInput) fileInput.value = '';
    $('#additionalInfoTicketId').val(ticketId);
    
    // Reset file upload if instance exists
    if (window.additionalInfoFileUpload) {
        window.additionalInfoFileUpload.reset();
    }

    // Fetch ticket details to get existing evidence
    fetch(`${CUSTOMER_TICKETS_BASE_URL}customer-tickets/details/${ticketId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error || !data.data) {
                existingEvidenceContainer.innerHTML = '<div class="alert alert-warning p-2 small">Could not load existing evidence.</div>';
                return;
            }
            
            const ticket = data.data;
            let evidenceHTML = '';
            
            if (ticket.evidence && ticket.evidence.length > 0) {
                // Calculate available slots
                const totalEvidenceCount = ticket.evidence.length;
                const availableSlots = 3 - totalEvidenceCount;

                askedInfoSection.innerHTML = `
                    ${ticket.action_taken ? 
                        ticket.status.toLowerCase() === 'reverted' ? 
                            `<h6><i class="fas fa-exclamation-triangle text-warning"></i> Information Requested:</h6><p class="bg-warning bg-opacity-10 border border-warning p-3 rounded">${ticket.action_taken}</p>` :
                            `<h6>Action Taken / Remarks:</h6><p class="bg-light p-3 rounded">${ticket.action_taken}</p>`
                        : ''
                    }
                `;
                evidenceHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-title mb-0"><i class="fas fa-images"></i> Manage Existing Evidence</h6>
                        <span class="badge bg-info">
                            ${totalEvidenceCount}/3 slots used
                            ${availableSlots > 0 ? `(${availableSlots} available)` : '(no slots available)'}
                        </span>
                    </div>
                    <p class="card-text small">Select images to delete if you want to add new ones. Maximum total: 3 files.</p>
                    <div class="row g-2">
                `;
                
                // Add event listeners to update file slots dynamically
                setTimeout(() => {
                    const deleteCheckboxes = document.querySelectorAll('input[name="delete_images[]"]');
                    deleteCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', updateFileCountDisplay);
                    });
                }, 100);
                
                ticket.evidence.forEach((img, index) => {
                    evidenceHTML += `
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="delete_images[]" value="${img.filename}" id="delete_img_${index}">
                                    <label class="form-check-label" for="delete_img_${index}">
                                        <small class="text-muted">Delete this image</small>
                                    </label>
                                </div>
                                <div class="ratio ratio-4x3 mt-2">
                                    <img src="${img.url}" alt="Evidence" class="w-100 h-100 object-fit-cover">
                                </div>
                                <small class="text-muted d-block mt-1 text-truncate">${img.filename}</small>
                            </div>
                        </div>
                    `;
                });
                evidenceHTML += '</div>';
                
                // Update max allowed new files based on existing evidence
                if (window.additionalInfoFileUpload) {
                    window.additionalInfoFileUpload.maxAllowedFiles = 3 - (totalEvidenceCount - 0); // Initially no checked boxes
                    updateFileUploadAreaText();
                }
                
            } else {
                evidenceHTML = '<div class="d-flex justify-content-between align-items-center mb-2"><p class="text-muted small mb-0">No existing evidence attached to this ticket.</p><span class="badge bg-success">0/3 slots used (3 available)</span></div>';
                
                // Set max allowed new files to 3 since there's no existing evidence
                if (window.additionalInfoFileUpload) {
                    window.additionalInfoFileUpload.maxAllowedFiles = 3;
                    updateFileUploadAreaText();
                }
            }
            
            existingEvidenceContainer.innerHTML = evidenceHTML;
        })
        .catch(error => {
            console.error('Error fetching existing evidence:', error);
            existingEvidenceContainer.innerHTML = '<div class="alert alert-danger p-2 small">Error loading evidence.</div>';
        });

    modal.show();
}

/**
 * Submits the feedback form via an AJAX POST request.
 */
function submitFeedback() {
    const form = document.getElementById('feedbackForm');
    const formData = new FormData(form);
    const rating = formData.get('feedback_rating');

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
        bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
        setTimeout(() => window.location.reload(), 500); // Reload page to reflect changes.
        setTimeout(() => window.location.reload(), 500); // Reload page to reflect changes.
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
    
    // Check total file count limit (existing + new - deleted)
    const existingFilesCount = document.querySelectorAll('#existingEvidenceSection .col-6.col-md-4').length;
    const selectedToDelete = document.querySelectorAll('input[name="delete_images[]"]:checked').length;
    const newFilesCount = window.additionalInfoFileUpload ? window.additionalInfoFileUpload.selectedFiles.length : 0;
    const totalFiles = existingFilesCount - selectedToDelete + newFilesCount;
    
    if (totalFiles > 3) {
        Swal.fire({
            icon: 'warning',
            title: 'Too Many Evidence Files',
            html: `You can have a maximum of 3 evidence files per ticket.<br>
                  <br>
                  Current count: ${existingFilesCount} existing files<br>
                  Marked for deletion: ${selectedToDelete}<br>
                  New files: ${newFilesCount}<br>
                  <br>
                  Total after changes: ${totalFiles}<br>
                  <br>
                  Please delete more existing files or select fewer new files.`,
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    // Add selected files to form data if file upload is available
    if (window.additionalInfoFileUpload && window.additionalInfoFileUpload.selectedFiles) {
        console.log("Adding files to form data:", window.additionalInfoFileUpload.selectedFiles);
        
        // Try both naming formats to ensure compatibility with backend
        window.additionalInfoFileUpload.selectedFiles.forEach((file, index) => {
            console.log(`Adding file ${index} as additional_evidence[${index}]:`, file.name, file.size);
            formData.append(`additional_evidence[${index}]`, file);
            
            // Also append with evidence name for backup
            console.log(`Adding file ${index} as evidence[${index}]:`, file.name, file.size);
            formData.append(`evidence[${index}]`, file);
        });
        
        // Debug: log all form data entries
        console.log("Form data entries:");
        for (let pair of formData.entries()) {
            console.log(pair[0], pair[1] instanceof File ? `File: ${pair[1].name}` : pair[1]);
        }
    } else {
        console.log("No files to upload");
    }

    const submitBtn = $('#submitAdditionalInfoBtn');
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');

    // Show loading state
    Swal.fire({
        title: 'Submitting Additional Information...',
        html: 'Please wait while we process your request.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Log request data before sending
    console.log(`Sending to: ${CUSTOMER_TICKETS_BASE_URL}customer-tickets/additional-info`);
    
    fetch(`${CUSTOMER_TICKETS_BASE_URL}customer-tickets/additional-info`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        console.log('Response status:', response.status);
        
        // Try to parse as JSON first
        try {
            const text = await response.text();
            console.log('Response text:', text);
            
            // Try to parse the text as JSON
            const data = JSON.parse(text);
            console.log('Parsed JSON data:', data);
            
            // Check if it's an error response
            if (data.error === true) {
                throw new Error(data.message || 'Unknown error occurred');
            }
            
            // Success! Show success message
            Swal.fire({
                icon: 'success',
                title: 'Information Submitted Successfully!',
                text: data.message || 'Your additional information has been submitted successfully.',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'OK'
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('additionalInfoModal')).hide();
                window.location.reload(); // Reload page to reflect changes
            });
            
        } catch (parseError) {
            console.error('Error parsing response:', parseError);
            throw new Error('Invalid response from server');
        }
    })
    .catch(error => {
        console.error('Error submitting additional info:', error);
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: error.message || 'There was an error submitting your information. Please try again.',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
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
    let evidenceHTML = '<h6>Evidence:</h6>';
    if (ticket.evidence && ticket.evidence.length > 0) {
        evidenceHTML += '<div class="row">';
        ticket.evidence.forEach(img => {
            evidenceHTML += `
                <div class="col-md-4 mb-2">
                    <a href="${img.url}" target="_blank">
                        <img src="${img.url}" class="img-thumbnail" alt="Evidence">
                    </a>
                </div>
            `;
        });
        evidenceHTML += '</div>';
    } else {
        evidenceHTML += '<p class="text-muted">No evidence submitted.</p>';
    }

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
                    <tr><th>Location:</th><td>${ticket.shed_terminal ? ticket.shed_terminal + (ticket.shed_type ? ' (' + ticket.shed_type + ')' : '') : 'N/A'}</td></tr>
                </table>
                <h6>Description:</h6>
                <p class="bg-light p-3 rounded">${ticket.description || 'No description.'}</p>
                
                ${ticket.action_taken ? 
                    ticket.status.toLowerCase() === 'reverted' ? 
                        `<h6><i class="fas fa-exclamation-triangle text-warning"></i> Information Requested:</h6><p class="bg-warning bg-opacity-10 border border-warning p-3 rounded">${ticket.action_taken}</p>` :
                        `<h6>Action Taken / Remarks:</h6><p class="bg-light p-3 rounded">${ticket.action_taken}</p>`
                    : ''
                }
                
                <hr>
                ${evidenceHTML}
            </div>
            <!--<div class="col-lg-4">
                <h5><i class="fas fa-history text-primary"></i> History</h5>
                <div id="transactionHistory" class="transaction-history-section">
                    <div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>
                </div>
            </div> -->
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
        // Map transaction types to more readable actions
        let actionText = '';
        switch (t.transaction_type.toLowerCase()) {
            case 'forward':
                actionText = 'Ticket Forwarded';
                break;
            case 'status_update':
                actionText = 'Status Updated';
                break;
            case 'close':
                actionText = 'Ticket Closed';
                break;
            case 'revert':
                actionText = 'Additional Information Requested';
                break;
            case 'additional_info_provided':
                actionText = 'Additional Information Provided';
                break;
            case 'feedback_submitted':
                actionText = 'Feedback Submitted';
                break;
            case 'approve':
                actionText = 'Ticket Approved';
                break;
            case 'reject':
                actionText = 'Ticket Rejected';
                break;
            default:
                actionText = t.transaction_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Extract just the action from the remarks
        let remarks = t.remarks;
        // Remove any references to who took the action
        remarks = remarks.replace(/by\s+[\w\s]+:/i, ':');
        remarks = remarks.replace(/by\s+[\w\s]+$/i, '');
        remarks = remarks.replace(/:\s*$/, '');

        html += `
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong class="timeline-title">${actionText}</strong>
                        <span class="timeline-date small text-muted">${formatDate(t.created_at)}</span>
                    </div>
                    <p class="timeline-body small mb-0">${remarks}</p>
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
 * Update the file count display and available slots
 */
function updateFileCountDisplay() {
    const existingFilesCount = document.querySelectorAll('#existingEvidenceSection .col-6.col-md-4').length;
    const selectedToDelete = document.querySelectorAll('input[name="delete_images[]"]:checked').length;
    const newFilesCount = window.additionalInfoFileUpload ? window.additionalInfoFileUpload.selectedFiles.length : 0;
    
    const usedSlots = existingFilesCount - selectedToDelete + newFilesCount;
    const availableSlots = 3 - usedSlots;
    const badge = document.querySelector('#existingEvidenceSection .badge');
    
    if (badge) {
        badge.textContent = `${usedSlots}/3 slots used ${availableSlots > 0 ? `(${availableSlots} available)` : '(no slots available)'}`;
        
        // Update badge color based on availability
        badge.className = 'badge';
        if (availableSlots === 0) {
            badge.classList.add('bg-warning');
        } else if (availableSlots < 0) {
            badge.classList.add('bg-danger');
        } else if (availableSlots === 3) {
            badge.classList.add('bg-success');
        } else {
            badge.classList.add('bg-info');
        }
    }
    
    // Update max allowed files for the file uploader
    if (window.additionalInfoFileUpload) {
        window.additionalInfoFileUpload.maxAllowedFiles = Math.max(0, 3 - (existingFilesCount - selectedToDelete));
        updateFileUploadAreaText();
    }
}

/**
 * Update the file upload area text based on available slots
 */
function updateFileUploadAreaText() {
    if (!window.additionalInfoFileUpload) return;
    
    const uploadArea = document.getElementById('fileUploadArea');
    if (!uploadArea) return;
    
    const uploadText = uploadArea.querySelector('p');
    if (!uploadText) return;
    
    const maxAllowed = window.additionalInfoFileUpload.maxAllowedFiles;
    const selectedFiles = window.additionalInfoFileUpload.selectedFiles.length;
    const remainingSlots = Math.max(0, maxAllowed - selectedFiles);
    
    if (maxAllowed <= 0) {
        uploadText.textContent = 'No slots available. Delete existing files to add new ones.';
        uploadArea.classList.add('disabled');
    } else if (remainingSlots === 0) {
        uploadText.textContent = 'Maximum files selected. Remove files to add more.';
        uploadArea.classList.add('disabled');
    } else if (remainingSlots === 1) {
        uploadText.textContent = `Drag and drop 1 more file here or click to select`;
        uploadArea.classList.remove('disabled');
    } else {
        uploadText.textContent = `Drag and drop up to ${remainingSlots} more files here or click to select`;
        uploadArea.classList.remove('disabled');
    }
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