/**
 * Support & Assistance JavaScript
 * Enhanced functionality for the customer support tickets interface
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the support assistance interface
    initSupportAssistance();
});

function initSupportAssistance() {
    // Setup filter functionality
    setupFilters();
    
    // Setup ticket selection
    setupTicketSelection();
    
    // Setup search functionality with debouncing
    setupSearchDebouncing();
    
    // Setup date filter functionality
    setupDateFilter();
    
    // Setup priority filter functionality
    setupPriorityFilter();
    
    // Setup sorting functionality
    setupSorting();
    
    // Setup real-time updates
    setupRealTimeUpdates();
    
    // Setup keyboard shortcuts
    setupKeyboardShortcuts();
    
    // Auto-refresh functionality
    setupAutoRefresh();
    
    // Update list title based on current filter
    updateListTitle();
    
    // Apply initial sorting (priority high to low) on page load
    setTimeout(() => {
        applyAllFilters();
    }, 100);
}

function setupFilters() {
    const filterItems = document.querySelectorAll('.filter-item');
    
    filterItems.forEach(item => {
        item.addEventListener('click', function() {
            const filter = this.dataset.filter;
            applyFilter(filter);
        });
    });
}

function applyFilter(filter) {
    // Remove active class from all filters
    document.querySelectorAll('.filter-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked filter
    event.target.closest('.filter-item').classList.add('active');
    
    // Build URL with filter parameters
    const currentUrl = new URL(window.location);
    
    // Clear existing filter parameters
    currentUrl.searchParams.delete('status');
    currentUrl.searchParams.delete('view');
    
    // Set new filter parameters
    if (filter === 'all') {
        currentUrl.searchParams.set('view', 'all');
    } else {
        currentUrl.searchParams.set('status', filter);
    }
    
    // Preserve search query if exists
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value) {
        currentUrl.searchParams.set('search', searchInput.value);
    }
    
    // Navigate to new URL
    window.location.href = currentUrl.toString();
}

function setupTicketSelection() {
    const ticketItems = document.querySelectorAll('.support-ticket-item');
    const defaultState = document.getElementById('defaultState');
    const ticketDetails = document.getElementById('ticketDetails');
    
    ticketItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            ticketItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Get ticket ID
            const ticketId = this.dataset.ticketId;
            
            // Show ticket details
            showTicketDetails(ticketId, this);
        });
    });
}

function showTicketDetails(ticketId, ticketElement) {
    const defaultState = document.getElementById('defaultState');
    const ticketDetails = document.getElementById('ticketDetails');
    
    // Hide default state and show ticket details
    defaultState.style.display = 'none';
    ticketDetails.style.display = 'flex';
    
    // Update ticket header information
    updateTicketHeader(ticketId, ticketElement);
    
    // Load ticket details
    loadTicketDetails(ticketId);
    
    // Setup action buttons
    setupActionButtons(ticketId);
}

function updateTicketHeader(ticketId, ticketElement) {
    const ticketIdText = document.getElementById('ticketIdText');
    const detailStatus = document.getElementById('detailStatus');
    const detailPriority = document.getElementById('detailPriority');
    
    // Get ticket information from the element
    const ticketIdTextContent = ticketElement.querySelector('.ticket-id').textContent.trim();
    const statusBadge = ticketElement.querySelector('.badge.status-pending, .badge.status-replied, .badge.status-closed, .badge.status-reverted');
    const priorityBadge = ticketElement.querySelector('.badge.priority-critical, .badge.priority-high, .badge.priority-medium, .badge.priority-normal');
    
    const status = statusBadge ? statusBadge.textContent.trim() : 'Pending';
    const priority = priorityBadge ? priorityBadge.textContent.trim() : '';
    
    // Update header
    ticketIdText.textContent = ticketIdTextContent.replace('#', '').trim();
    detailStatus.textContent = status;
    detailStatus.className = `ticket-status-badge status-${status.toLowerCase().replace(' ', '-')}`;
    
    // Update priority badge (only show for non-closed tickets)
    if (priority && status.toLowerCase() !== 'closed') {
        detailPriority.innerHTML = `<i class="fas fa-flag"></i> ${priority}`;
        detailPriority.className = `ticket-priority-badge priority-${priority.toLowerCase()}`;
        detailPriority.style.display = 'flex';
    } else {
        detailPriority.style.display = 'none';
    }
}

function loadTicketDetails(ticketId) {
    // Show loading state
    const infoSection = document.querySelector('.ticket-info-section');
    infoSection.innerHTML = '<div class="text-center text-muted">Loading ticket details...</div>';
    
    // Fetch ticket details
    fetch(`${BASE_URL}api/complaints/view/${ticketId}`)
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data); // Debug log
            if (!data.error) {
                // API returns ticket data directly in the response
                const ticket = data.data || data;
                displayTicketDetails(ticket);
                loadTransactionHistory(ticket);
            } else {
                infoSection.innerHTML = '<div class="text-center text-muted">Failed to load ticket details: ' + (data.message || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading ticket details:', error);
            infoSection.innerHTML = '<div class="text-center text-muted">Error loading ticket details: ' + error.message + '</div>';
        });
}

function displayTicketDetails(ticket) {
    const infoSection = document.querySelector('.ticket-info-section');
    
    console.log('Ticket data:', ticket); // Debug log
    
    // Create ticket details HTML
    const detailsHTML = `
        <div class="info-group">
            <label class="info-label">Ticket Type</label>
            <div class="info-content">
                ${ticket.complaint_type || 'Not specified'}
                ${ticket.complaint_subtype ? `<br><small>${ticket.complaint_subtype}</small>` : ''}
            </div>
        </div>
        
                 <div class="info-group">
             <label class="info-label">Location</label>
             <div class="info-content">
                 ${ticket.location || 'Not specified'}
             </div>
         </div>
         
         <div class="info-group">
             <label class="info-label">Wagon</label>
             <div class="info-content">
                 ${ticket.wagon_type ? (ticket.wagon_code ? ticket.wagon_code + ' - ' + ticket.wagon_type : ticket.wagon_type) : 'Not specified'}
             </div>
         </div>   

        
        <div class="info-group">
            <label class="info-label">Created Date</label>
            <div class="info-content">
                ${formatDateTime(ticket.created_at || new Date())}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Last Updated</label>
            <div class="info-content">
                ${formatDateTime(ticket.updated_at || ticket.created_at || new Date())}
            </div>
        </div>
        
        <div class="info-group full-width">
            <label class="info-label">Description</label>
            <div class="info-content description-text">
                ${ticket.description || 'No description provided'}
            </div>
        </div>
    `;
    
    infoSection.innerHTML = detailsHTML;
}

function loadTransactionHistory(ticket) {
    const historyList = document.getElementById('transactionHistory');
    
    if (!ticket.transactions || ticket.transactions.length === 0) {
        historyList.innerHTML = '<div class="text-center text-muted">No progress history available</div>';
        return;
    }
    
    let historyHTML = '';
    ticket.transactions.forEach(transaction => {
        // Map transaction types to readable actions
        let actionText = transaction.transaction_type;
        switch (transaction.transaction_type) {
            case 'forward':
                actionText = 'Forwarded';
                break;
            case 'internal_remark':
                actionText = 'Internal Remark';
                break;
            case 'status_update':
                actionText = 'Status Updated';
                break;
            case 'assignment':
                actionText = 'Assigned';
                break;
            case 'close':
                actionText = 'Closed';
                break;
            case 'revert':
                actionText = 'Reverted';
                break;
            default:
                actionText = transaction.transaction_type.replace('_', ' ').toUpperCase();
        }
        
        historyHTML += `
            <div class="history-item">
                <div class="history-item-header">
                    <span class="history-action">${actionText}</span>
                    <span class="history-time">${formatDateTime(transaction.created_at)}</span>
                </div>
                <div class="history-remarks">
                    ${transaction.remarks || 'No remarks'}
                    ${transaction.created_by_name ? `<br><small>By: ${transaction.created_by_name}</small>` : ''}
                </div>
            </div>
        `;
    });
    
    historyList.innerHTML = historyHTML;
}

function setupActionButtons(ticketId) {
    const viewDetailsBtn = document.getElementById('viewDetailsBtn');
    const addResponseBtn = document.getElementById('addResponseBtn');
    
    // Hide view button by default
    if (viewDetailsBtn) {
        viewDetailsBtn.style.display = 'none';
    }
    
    // Get the current ticket element to check status
    const activeTicket = document.querySelector('.support-ticket-item.active');
    if (activeTicket) {
        const statusBadge = activeTicket.querySelector('.badge.status-pending, .badge.status-replied, .badge.status-closed, .badge.status-reverted');
        const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : 'pending';
        
                 // Show respond button only for reverted tickets
         if (addResponseBtn) {
             if (status === 'reverted') {
                 addResponseBtn.innerHTML = '<i class="fas fa-reply"></i> Respond';
                 addResponseBtn.className = 'btn btn-sm btn-outline-warning';
                 addResponseBtn.style.display = 'inline-block';
                 addResponseBtn.onclick = () => {
                     addResponseToTicket(ticketId);
                 };
             } else if (status === 'replied') {
                 // Show feedback button for replied tickets
                 addResponseBtn.innerHTML = '<i class="fas fa-star"></i> Give Feedback';
                 addResponseBtn.className = 'btn btn-sm btn-outline-success';
                 addResponseBtn.style.display = 'inline-block';
                 addResponseBtn.onclick = () => {
                     giveFeedbackToTicket(ticketId);
                 };
             } else {
                 addResponseBtn.style.display = 'none';
             }
         }
    }
}

function addResponseToTicket(ticketId) {
    // Redirect to complaint form with ticket ID for response
    window.location.href = `${BASE_URL}grievances/new?response_to=${ticketId}`;
}

function giveFeedbackToTicket(ticketId) {
    // Show feedback modal or redirect to feedback page
    Swal.fire({
        title: 'Give Feedback',
        html: `
            <div class="mb-3">
                <label class="form-label">Rating</label>
                <div class="rating-stars">
                    <i class="fas fa-star star-rating" data-rating="1"></i>
                    <i class="fas fa-star star-rating" data-rating="2"></i>
                    <i class="fas fa-star star-rating" data-rating="3"></i>
                    <i class="fas fa-star star-rating" data-rating="4"></i>
                    <i class="fas fa-star star-rating" data-rating="5"></i>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Comments</label>
                <textarea id="feedbackComments" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Feedback',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const rating = document.querySelector('.star-rating.selected')?.dataset.rating || 0;
            const comments = document.getElementById('feedbackComments').value;
            
            if (rating == 0) {
                Swal.showValidationMessage('Please select a rating');
                return false;
            }
            
            return { rating, comments };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFeedback(ticketId, result.value.rating, result.value.comments);
        }
    });
    
    // Add star rating functionality
    setTimeout(() => {
        const stars = document.querySelectorAll('.star-rating');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                stars.forEach(s => {
                    s.classList.remove('selected', 'text-warning');
                    if (s.dataset.rating <= rating) {
                        s.classList.add('selected', 'text-warning');
                    }
                });
            });
        });
    }, 100);
}

function submitFeedback(ticketId, rating, comments) {
    // Submit feedback via API
    fetch(`${BASE_URL}api/complaints/submit_feedback`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            complaint_id: ticketId,
            rating: rating,
            comments: comments
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Feedback Submitted!',
                text: 'Thank you for your feedback.',
                confirmButtonColor: '#28a745'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to submit feedback',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error submitting feedback:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to submit feedback. Please try again.',
            confirmButtonColor: '#dc3545'
        });
    });
}



function setupSearchDebouncing() {
    const searchInput = document.getElementById('searchInput');
    const searchClearBtn = document.getElementById('searchClearBtn');
    
    if (!searchInput) return;
    
    // Real-time search as user types
    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        applyAllFilters();
        
        // Show/hide clear button
        if (searchClearBtn) {
            searchClearBtn.style.display = query.trim() ? 'block' : 'none';
        }
    });
    
    // Also handle Enter key for traditional search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performTraditionalSearch(searchInput.value);
        }
    });
    
    // Clear button functionality
    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            applyAllFilters();
            searchClearBtn.style.display = 'none';
        });
    }
    
    // Show clear button if there's initial search value
    if (searchInput.value.trim() && searchClearBtn) {
        searchClearBtn.style.display = 'block';
    }
}

function setupDateFilter() {
    const dateFilter = document.getElementById('dateFilter');
    const customDateGroup = document.getElementById('customDateGroup');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (!dateFilter) return;
    
    dateFilter.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateGroup.style.display = 'block';
        } else {
            customDateGroup.style.display = 'none';
            applyAllFilters();
        }
    });
    
    // Custom date inputs
    if (startDate && endDate) {
        startDate.addEventListener('change', applyAllFilters);
        endDate.addEventListener('change', applyAllFilters);
    }
}

function setupPriorityFilter() {
    const priorityFilter = document.getElementById('priorityFilter');
    
    if (!priorityFilter) return;
    
    priorityFilter.addEventListener('change', applyAllFilters);
}

function setupSorting() {
    const sortFilter = document.getElementById('sortFilter');
    
    if (!sortFilter) return;
    
    sortFilter.addEventListener('change', applyAllFilters);
}

function applyAllFilters() {
    const searchQuery = document.getElementById('searchInput')?.value || '';
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    const priorityFilter = document.getElementById('priorityFilter')?.value || '';
    const sortBy = document.getElementById('sortFilter')?.value || 'priority';
    
    const ticketItems = document.querySelectorAll('.support-ticket-item');
    let visibleCount = 0;
    
    ticketItems.forEach(item => {
        const matchesSearch = matchesSearchFilter(item, searchQuery);
        const matchesDate = matchesDateFilter(item, dateFilter);
        const matchesPriority = matchesPriorityFilter(item, priorityFilter);
        
        if (matchesSearch && matchesDate && matchesPriority) {
            item.style.display = 'block';
            item.style.opacity = '1';
            visibleCount++;
        } else {
            item.style.opacity = '0';
            setTimeout(() => {
                item.style.display = 'none';
            }, 150);
        }
    });
    
    // Sort tickets
    sortTickets(sortBy);
    
    // Update empty state and title
    updateEmptyState(visibleCount);
    updateListTitleForFilters(searchQuery, dateFilter, priorityFilter, visibleCount);
    
    // Add search highlight effect
    if (searchQuery) {
        highlightSearchTerms(searchQuery);
    } else {
        removeSearchHighlights();
    }
}

function matchesSearchFilter(item, searchTerm) {
    if (!searchTerm) return true;
    
    const searchLower = searchTerm.toLowerCase().trim();
    const ticketId = item.querySelector('.ticket-id').textContent.toLowerCase();
    const ticketType = item.querySelector('.ticket-type').textContent.toLowerCase();
    const ticketPreview = item.querySelector('.ticket-preview').textContent.toLowerCase();
    
    return ticketId.includes(searchLower) ||
           ticketType.includes(searchLower) ||
           ticketPreview.includes(searchLower);
}

function matchesDateFilter(item, dateFilter) {
    if (!dateFilter) return true;
    
    const ticketDate = new Date(item.querySelector('.ticket-time').textContent);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    switch (dateFilter) {
        case 'today':
            return ticketDate >= today;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            return ticketDate >= yesterday && ticketDate < today;
        case 'week':
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            return ticketDate >= weekAgo;
        case 'month':
            const monthAgo = new Date(today);
            monthAgo.setMonth(monthAgo.getMonth() - 1);
            return ticketDate >= monthAgo;
        case 'custom':
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            if (!startDate || !endDate) return true;
            
            const start = new Date(startDate);
            const end = new Date(endDate);
            end.setHours(23, 59, 59, 999);
            
            return ticketDate >= start && ticketDate <= end;
        default:
            return true;
    }
}

function matchesPriorityFilter(item, priorityFilter) {
    if (!priorityFilter) return true;
    
    const priorityBadge = item.querySelector('.badge.priority-critical, .badge.priority-high, .badge.priority-medium, .badge.priority-normal');
    if (!priorityBadge) return false;
    
    const itemPriority = priorityBadge.textContent.toLowerCase().trim();
    return itemPriority === priorityFilter.toLowerCase();
}

function sortTickets(sortBy) {
    const ticketsList = document.getElementById('supportTicketsList');
    const ticketItems = Array.from(ticketsList.querySelectorAll('.support-ticket-item'));
    
    ticketItems.sort((a, b) => {
        switch (sortBy) {
            case 'priority':
                return getPriorityWeight(b) - getPriorityWeight(a);
            case 'date':
                return new Date(b.querySelector('.ticket-time').textContent) - 
                       new Date(a.querySelector('.ticket-time').textContent);
            case 'date_old':
                return new Date(a.querySelector('.ticket-time').textContent) - 
                       new Date(b.querySelector('.ticket-time').textContent);
            default:
                return 0;
        }
    });
    
    // Re-append sorted items
    ticketItems.forEach(item => {
        ticketsList.appendChild(item);
    });
}

function getPriorityWeight(item) {
    const priorityBadge = item.querySelector('.badge.priority-critical, .badge.priority-high, .badge.priority-medium, .badge.priority-normal');
    if (!priorityBadge) return 0;
    
    const priority = priorityBadge.textContent.toLowerCase().trim();
    switch (priority) {
        case 'critical': return 4;
        case 'high': return 3;
        case 'medium': return 2;
        case 'normal': return 1;
        default: return 0;
    }
}

function updateListTitleForFilters(searchQuery, dateFilter, priorityFilter, visibleCount) {
    const listTitle = document.getElementById('listTitle');
    const totalTickets = document.querySelectorAll('.support-ticket-item').length;
    
    let title = '';
    const filters = [];
    
    if (searchQuery) filters.push(`Search: "${searchQuery}"`);
    if (dateFilter) filters.push(`Date: ${dateFilter}`);
    if (priorityFilter) filters.push(`Priority: ${priorityFilter}`);
    
    if (filters.length > 0) {
        title = `Filtered Results (${visibleCount}/${totalTickets})`;
    } else {
        const activeFilter = document.querySelector('.filter-item.active');
        if (activeFilter) {
            title = activeFilter.querySelector('span').textContent;
        } else {
            title = 'All Support Tickets';
        }
    }
    
    listTitle.textContent = title;
}

function highlightSearchTerms(searchTerm) {
    const ticketItems = document.querySelectorAll('.support-ticket-item');
    
    ticketItems.forEach(item => {
        if (item.style.display !== 'none') {
            const elements = item.querySelectorAll('.ticket-id, .ticket-type, .ticket-preview');
            
            elements.forEach(element => {
                const originalText = element.getAttribute('data-original-text') || element.innerHTML;
                element.setAttribute('data-original-text', originalText);
                
                const highlightedText = originalText.replace(
                    new RegExp(searchTerm, 'gi'),
                    match => `<mark class="search-highlight">${match}</mark>`
                );
                element.innerHTML = highlightedText;
            });
        }
    });
}

function removeSearchHighlights() {
    const ticketItems = document.querySelectorAll('.support-ticket-item');
    
    ticketItems.forEach(item => {
        const elements = item.querySelectorAll('.ticket-id, .ticket-type, .ticket-preview');
        
        elements.forEach(element => {
            const originalText = element.getAttribute('data-original-text');
            if (originalText) {
                element.innerHTML = originalText;
                element.removeAttribute('data-original-text');
            }
        });
    });
}

function updateEmptyState(visibleCount) {
    const ticketsList = document.getElementById('supportTicketsList');
    let emptyState = ticketsList.querySelector('.empty-state');
    
    if (visibleCount === 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <div class="empty-icon">
                    <i class="fas fa-search fa-2x text-muted"></i>
                </div>
                <h6 class="empty-title">No support tickets found</h6>
                <p class="empty-message">
                    No support tickets match your search criteria.
                </p>
            `;
            ticketsList.appendChild(emptyState);
        }
    } else if (emptyState) {
        emptyState.remove();
    }
}

function performTraditionalSearch(query) {
    const currentUrl = new URL(window.location);
    
    if (query.trim()) {
        currentUrl.searchParams.set('search', query);
    } else {
        currentUrl.searchParams.delete('search');
    }
    
    // Preserve current filter
    const activeFilter = document.querySelector('.filter-item.active');
    if (activeFilter) {
        const filter = activeFilter.dataset.filter;
        if (filter === 'all') {
            currentUrl.searchParams.set('view', 'all');
        } else {
            currentUrl.searchParams.set('status', filter);
        }
    }
    
    window.location.href = currentUrl.toString();
}

function updateListTitle() {
    const listTitle = document.getElementById('listTitle');
    const activeFilter = document.querySelector('.filter-item.active');
    
    if (activeFilter) {
        const filterText = activeFilter.querySelector('span').textContent;
        listTitle.textContent = filterText;
    }
}

function setupRealTimeUpdates() {
    // Poll for updates every 30 seconds
    setInterval(function() {
        checkForNewTickets();
    }, 30000);
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Ctrl/Cmd + R: Refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            location.reload();
        }
        
        // Escape: Clear selection
        if (e.key === 'Escape') {
            clearTicketSelection();
        }
    });
}

function clearTicketSelection() {
    const ticketItems = document.querySelectorAll('.support-ticket-item');
    const defaultState = document.getElementById('defaultState');
    const ticketDetails = document.getElementById('ticketDetails');
    
    // Remove active class from all items
    ticketItems.forEach(i => i.classList.remove('active'));
    
    // Show default state
    defaultState.style.display = 'flex';
    ticketDetails.style.display = 'none';
}

function setupAutoRefresh() {
    // Auto-refresh every 60 seconds if no modals are open
    setInterval(function() {
        if (!document.querySelector('.modal.show')) {
            // Check if user is active
            if (isUserActive()) {
                location.reload();
            }
        }
    }, 60000);
}

function checkForNewTickets() {
    // Check for new tickets without full page reload
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            const currentTickets = document.querySelectorAll('.support-ticket-item');
            const newTickets = newDoc.querySelectorAll('.support-ticket-item');
            
            if (newTickets.length > currentTickets.length) {
                showNotification('New support tickets available', 'info');
            }
        })
        .catch(error => {
            console.log('Error checking for updates:', error);
        });
}

function isUserActive() {
    // Simple check for user activity
    return true;
}

function showNotification(message, type = 'info') {
    // Use centralized alert system if available
    if (typeof window.showAlert === 'function') {
        window.showAlert(message, type, 5000);
    } else {
        // Fallback to local implementation
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}

function formatDateTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', { 
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    });
}

// New ticket creation function
function createNewTicket() {
    // Redirect to new support ticket form
    window.location.href = `${BASE_URL}support/new`;
}
