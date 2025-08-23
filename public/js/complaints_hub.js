/**
 * Three-Column Complaints Hub JavaScript
 * Enhanced functionality for the modern complaints interface
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize the complaints hub interface
    initComplaintsHub();
});

function initComplaintsHub() {
    const approveRejectModalEl = document.getElementById('approveRejectModal');
    if (approveRejectModalEl) {
        const approveRejectModal = new bootstrap.Modal(approveRejectModalEl);

        const takeActionBtn = document.getElementById('takeActionBtn');
        if (takeActionBtn) {
            takeActionBtn.addEventListener('click', function () {
                const complaintId = this.dataset.complaintId;
                const actionTaken = this.dataset.actionTaken;

                // Populate and show the modal
                document.getElementById('modalComplaintId').textContent = complaintId;
                document.getElementById('modalActionTaken').textContent = actionTaken || 'No action details provided.';
                document.getElementById('approveComplaintId').value = complaintId;
                document.getElementById('rejectComplaintId').value = complaintId;

                document.getElementById('approveForm').style.display = 'block';
                document.getElementById('rejectForm').style.display = 'block';
                document.getElementById('rejectionReason').value = '';
                document.querySelector('#approveForm textarea[name="remarks"]').value = '';

                approveRejectModal.show();
            });
        }
    }
    // Setup filter functionality
    setupFilters();

    // Setup complaint selection
    setupComplaintSelection();

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
        item.addEventListener('click', function () {
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
    } else if (filter === 'assigned') {
        currentUrl.searchParams.set('view', 'assigned');
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

function setupComplaintSelection() {
    const complaintItems = document.querySelectorAll('.complaint-item');
    const defaultState = document.getElementById('defaultState');
    const complaintDetails = document.getElementById('complaintDetails');

    complaintItems.forEach(item => {
        item.addEventListener('click', function () {
            // Remove active class from all items
            complaintItems.forEach(i => i.classList.remove('active'));

            // Add active class to clicked item
            this.classList.add('active');

            // Get complaint ID
            const complaintId = this.dataset.complaintId;

            // Show complaint details
            showComplaintDetails(complaintId, this);
        });
    });
}

/* function showComplaintDetails(complaintId, complaintElement) {
    const defaultState = document.getElementById('defaultState');
    const complaintDetails = document.getElementById('complaintDetails');
    
    // Hide default state and show complaint details
    defaultState.style.display = 'none';
    complaintDetails.style.display = 'flex';
    
    // Update complaint header information
    updateComplaintHeader(complaintId, complaintElement);
    
    // Load complaint details
    loadComplaintDetails(complaintId);
    
    // Setup action buttons
    setupActionButtons(complaintId);
} */

function showComplaintDetails(complaintId, complaintElement) {
    document.getElementById('defaultState').style.display = 'none';
    document.getElementById('complaintDetails').style.display = 'flex';

    const status = complaintElement.dataset.complaintStatus;

    document.querySelectorAll('.action-btn').forEach(btn => btn.style.display = 'none');

    if (status === 'awaiting_approval') {
        // Use the now-defined currentUserRole and currentUserDepartment variables
        if (currentUserRole === 'admin' || (currentUserDepartment && currentUserDepartment.toUpperCase() === 'COMMERCIAL')) {
            const takeActionBtn = document.getElementById('takeActionBtn');
            if (takeActionBtn) {
                takeActionBtn.style.display = 'inline-flex';
                takeActionBtn.dataset.complaintId = complaintId;
                takeActionBtn.dataset.actionTaken = complaintElement.dataset.actionTaken;
            }
        }
    } else {
        document.getElementById('forwardBtn').style.display = 'inline-flex';
        document.getElementById('closeBtn').style.display = 'inline-flex';
        if (document.getElementById('revertBtn')) {
            document.getElementById('revertBtn').style.display = 'inline-flex';
        }
    }

    document.getElementById('forwardBtn').onclick = () => forwardComplaint(complaintId);
    document.getElementById('closeBtn').onclick = () => closeComplaint(complaintId);
    if (document.getElementById('revertBtn')) {
        document.getElementById('revertBtn').onclick = () => revertComplaint(complaintId);
    }
updateComplaintHeader(complaintId, complaintElement);
    loadComplaintDetails(complaintId);
}

function updateComplaintHeader(complaintId, complaintElement) {
    const complaintIdText = document.getElementById('complaintIdText');
    const detailStatus = document.getElementById('detailStatus');
    const detailPriority = document.getElementById('detailPriority');

    // Get complaint information from the element
    const complaintIdTextContent = complaintElement.querySelector('.complaint-id').textContent.trim();
    const statusBadge = complaintElement.querySelector('.badge.status-pending, .badge.status-replied, .badge.status-closed, .badge.status-awaiting-approval, .badge.status-reverted, .badge.status-forwarded');
    const priorityBadge = complaintElement.querySelector('.badge.priority-critical, .badge.priority-high, .badge.priority-medium, .badge.priority-normal');

    const status = statusBadge ? statusBadge.textContent.trim() : 'Pending';
    const priority = priorityBadge ? priorityBadge.textContent.trim() : '';

    // Update header
    complaintIdText.textContent = complaintIdTextContent.replace('#', '').trim();
    detailStatus.textContent = status;
    detailStatus.className = `complaint-status-badge status-${status.toLowerCase().replace(' ', '-')}`;

    // Update priority badge (only show for non-closed complaints)
    if (priority && status.toLowerCase() !== 'closed') {
        detailPriority.innerHTML = `<i class="fas fa-flag"></i> ${priority}`;
        detailPriority.className = `complaint-priority-badge priority-${priority.toLowerCase()}`;
        detailPriority.style.display = 'flex';
    } else {
        detailPriority.style.display = 'none';
    }
}

function loadComplaintDetails(complaintId) {
    // Show loading state
    const infoSection = document.querySelector('.complaint-info-section');
    infoSection.innerHTML = '<div class="text-center text-muted">Loading complaint details...</div>';

    // Fetch complaint details
    fetch(`${BASE_URL}api/complaints/view/${complaintId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                // API returns complaint data in the data field
                const complaint = data.data;
                displayComplaintDetails(complaint);
                loadTransactionHistory(complaint);
            } else {
                infoSection.innerHTML = '<div class="text-center text-muted">Failed to load complaint details: ' + (data.message || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading complaint details:', error);
            infoSection.innerHTML = '<div class="text-center text-muted">Error loading complaint details: ' + error.message + '</div>';
        });
}

function displayComplaintDetails(complaint) {
    const infoSection = document.querySelector('.complaint-info-section');
    //console.log('Complaint data:', complaint);
    //console.log('Evidence data:', complaint.evidence);

    // Create complaint details HTML with all available fields except status and priority
    const detailsHTML = `
        <div class="info-group">
            <label class="info-label">Customer Information</label>
            <div class="info-content"><p>
                <strong>${complaint.customer_name || 'Unknown'}</strong></br>
                <small>ID: ${complaint.customer_id || 'N/A'}</small><br>
                ${complaint.customer_email ? `<small>Email: ${complaint.customer_email}</small>` : ''}
            </p>
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Complaint Type</label>
            <div class="info-content">
                ${complaint.Type || 'Not specified'}
                ${complaint.Subtype ? `<br><small>${complaint.Subtype}</small>` : ''}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Category</label>
            <div class="info-content">
                ${complaint.category || 'Not specified'}
            </div>
        </div>
                
        <div class="info-group">
            <label class="info-label">Shed Information</label>
            <div class="info-content">
                ${complaint.shed_terminal ? `${complaint.shed_terminal}` : ''}
                ${complaint.shed_type ? `<br><small>Type: ${complaint.shed_type}</small>` : ''}
                ${!complaint.shed_terminal && !complaint.shed_type ? 'Not specified' : ''}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Wagon Information</label>
            <div class="info-content">
                ${complaint.wagon_type ? `Type: ${complaint.wagon_type}` : ''}
                ${complaint.wagon_code ? `<br>Code: ${complaint.wagon_code}` : ''}
                ${!complaint.wagon_type && !complaint.wagon_code ? 'Not specified' : ''}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">FNR Number / GST Number / eIndent </label>
            <div class="info-content">
                ${complaint.FNR_Number || 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Department</label>
            <div class="info-content">
                ${complaint.department || 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Division</label>
            <div class="info-content">
                ${complaint.Division || 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Zone</label>
            <div class="info-content">
                ${complaint.Zone || 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Assigned To</label>
            <div class="info-content">
                ${complaint.assigned_to_name || complaint.assigned_to || 'Not assigned'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Date</label>
            <div class="info-content">
                ${complaint.date ? new Date(complaint.date).toLocaleDateString() : 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Time</label>
            <div class="info-content">
                ${complaint.time || 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Created Date</label>
            <div class="info-content">
                ${formatDateTime(complaint.created_at || new Date())}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Updated Date</label>
            <div class="info-content">
                ${complaint.updated_at ? formatDateTime(complaint.updated_at) : 'Not specified'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Forwarded Flag</label>
            <div class="info-content">
                ${complaint.Forwarded_Flag === 'Y' ? 'Yes' : 'No'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Awaiting Approval</label>
            <div class="info-content">
                ${complaint.Awaiting_Approval_Flag === 'Y' ? 'Yes' : 'No'}
            </div>
        </div>
        
        <div class="info-group">
            <label class="info-label">Rating</label>
            <div class="info-content">
                ${complaint.rating || 'Not rated'}
            </div>
        </div>
        
        <div class="info-group full-width">
            <label class="info-label">Description</label>
            <div class="info-content description-text">
                ${complaint.description || 'No description provided'}
            </div>
        </div>
        
        <div class="info-group full-width">
            <label class="info-label">Action Taken</label>
            <div class="info-content description-text">
                ${complaint.action_taken || 'No action taken yet'}
            </div>
        </div>
        
        ${complaint.rating_remarks ? `
        <div class="info-group full-width">
            <label class="info-label">Rating Remarks</label>
            <div class="info-content description-text">
                ${complaint.rating_remarks}
            </div>
        </div>
        ` : ''}
        
        ${complaint.evidence && complaint.evidence.length > 0 ? `
        <div class="info-group full-width">
            <label class="info-label">Evidence Files</label>
            <div class="info-content evidence-section">
                <div class="evidence-grid">
                    ${complaint.evidence.map((evidence, index) => `
                        <div class="evidence-item">
                            <div class="evidence-preview">
                                ${evidence.filename.toLowerCase().match(/\.(jpg|jpeg|png|gif|webp)$/) ? 
                                    `<img src="${evidence.url}" alt="Evidence ${index + 1}" class="evidence-image" onclick="openEvidenceModal('${evidence.url}', '${evidence.filename}')">` :
                                    `<div class="evidence-file-icon" onclick="openEvidenceModal('${evidence.url}', '${evidence.filename}')">
                                        <i class="fas fa-file-alt"></i>
                                        <span class="file-name">${evidence.filename}</span>
                                    </div>`
                                }
                            </div>
                            <div class="evidence-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="openEvidenceModal('${evidence.url}', '${evidence.filename}')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="${evidence.url}" download="${evidence.filename}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        ` : ''}
    `;

    infoSection.innerHTML = detailsHTML;
}

function loadTransactionHistory(complaint) {
    const historyList = document.getElementById('transactionHistory');

    if (!complaint.transactions || complaint.transactions.length === 0) {
        historyList.innerHTML = '<div class="text-center text-muted">No transaction history available</div>';
        return;
    }

    let historyHTML = '';
    complaint.transactions.forEach(transaction => {
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

function setupActionButtons(complaintId) {
    const viewDetailsBtn = document.getElementById('viewDetailsBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const closeBtn = document.getElementById('closeBtn');
    const revertBtn = document.getElementById('revertBtn');

    // View details button
    if (viewDetailsBtn) {
        viewDetailsBtn.onclick = () => {
            window.open(`${BASE_URL}complaints/view/${complaintId}`, '_blank');
        };
    }

    // Forward button
    if (forwardBtn) {
        forwardBtn.onclick = () => {
            forwardComplaint(complaintId);
        };
    }

    // Close button
    if (closeBtn) {
        closeBtn.onclick = () => {
            closeComplaint(complaintId);
        };
    }

    // Revert button (if exists)
    if (revertBtn) {
        revertBtn.onclick = () => {
            revertComplaint(complaintId);
        };
    }
}

function setupSearchDebouncing() {
    const searchInput = document.getElementById('searchInput');
    const searchClearBtn = document.getElementById('searchClearBtn');

    if (!searchInput) return;

    // Real-time search as user types
    searchInput.addEventListener('input', function () {
        const query = searchInput.value;
        applyAllFilters();

        // Show/hide clear button
        if (searchClearBtn) {
            searchClearBtn.style.display = query.trim() ? 'block' : 'none';
        }
    });

    // Also handle Enter key for traditional search
    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            performTraditionalSearch(searchInput.value);
        }
    });

    // Clear button functionality
    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function () {
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

    dateFilter.addEventListener('change', function () {
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

    const complaintItems = document.querySelectorAll('.complaint-item');
    let visibleCount = 0;

    complaintItems.forEach(item => {
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

    // Sort complaints
    sortComplaints(sortBy);

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
    const complaintId = item.querySelector('.complaint-id').textContent.toLowerCase();
    const customerName = item.querySelector('.complaint-customer').textContent.toLowerCase();
    const complaintType = item.querySelector('.complaint-type').textContent.toLowerCase();
    const complaintPreview = item.querySelector('.complaint-preview').textContent.toLowerCase();

    return complaintId.includes(searchLower) ||
        customerName.includes(searchLower) ||
        complaintType.includes(searchLower) ||
        complaintPreview.includes(searchLower);
}

function matchesDateFilter(item, dateFilter) {
    if (!dateFilter) return true;

    const complaintDate = new Date(item.querySelector('.complaint-time').textContent);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    switch (dateFilter) {
        case 'today':
            return complaintDate >= today;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            return complaintDate >= yesterday && complaintDate < today;
        case 'week':
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            return complaintDate >= weekAgo;
        case 'month':
            const monthAgo = new Date(today);
            monthAgo.setMonth(monthAgo.getMonth() - 1);
            return complaintDate >= monthAgo;
        case 'custom':
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            if (!startDate || !endDate) return true;

            const start = new Date(startDate);
            const end = new Date(endDate);
            end.setHours(23, 59, 59, 999);

            return complaintDate >= start && complaintDate <= end;
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

function sortComplaints(sortBy) {
    const complaintsList = document.getElementById('complaintsList');
    const complaintItems = Array.from(complaintsList.querySelectorAll('.complaint-item'));

    complaintItems.sort((a, b) => {
        switch (sortBy) {
            case 'priority':
                return getPriorityWeight(b) - getPriorityWeight(a);
            case 'date':
                return new Date(b.querySelector('.complaint-time').textContent) -
                    new Date(a.querySelector('.complaint-time').textContent);
            case 'date_old':
                return new Date(a.querySelector('.complaint-time').textContent) -
                    new Date(b.querySelector('.complaint-time').textContent);
            default:
                return 0;
        }
    });

    // Re-append sorted items
    complaintItems.forEach(item => {
        complaintsList.appendChild(item);
    });
}

function getPriorityWeight(item) {
    const priorityBadge = item.querySelector('.badge.priority-critical, .badge.priority-high, .badge.priority-medium, .badge.priority-normal');
    if (!priorityBadge) return 0;

    const priority = priorityBadge.textContent.toLowerCase().trim();
    switch (priority) {
        case 'critical':
            return 4;
        case 'high':
            return 3;
        case 'medium':
            return 2;
        case 'normal':
            return 1;
        default:
            return 0;
    }
}

function updateListTitleForFilters(searchQuery, dateFilter, priorityFilter, visibleCount) {
    const listTitle = document.getElementById('listTitle');
    const totalComplaints = document.querySelectorAll('.complaint-item').length;

    let title = '';
    const filters = [];

    if (searchQuery) filters.push(`Search: "${searchQuery}"`);
    if (dateFilter) filters.push(`Date: ${dateFilter}`);
    if (priorityFilter) filters.push(`Priority: ${priorityFilter}`);

    if (filters.length > 0) {
        title = `Filtered Results (${visibleCount}/${totalComplaints})`;
    } else {
        const activeFilter = document.querySelector('.filter-item.active');
        if (activeFilter) {
            title = activeFilter.querySelector('span').textContent;
        } else {
            title = 'All Complaints';
        }
    }

    listTitle.textContent = title;
}

function setupDateFilter() {
    const dateFilter = document.getElementById('dateFilter');
    const customDateGroup = document.getElementById('customDateGroup');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');

    if (!dateFilter) return;

    dateFilter.addEventListener('change', function () {
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

    const complaintItems = document.querySelectorAll('.complaint-item');
    let visibleCount = 0;

    complaintItems.forEach(item => {
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

    // Sort complaints
    sortComplaints(sortBy);

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
    const complaintId = item.querySelector('.complaint-id').textContent.toLowerCase();
    const customerName = item.querySelector('.complaint-customer').textContent.toLowerCase();
    const complaintType = item.querySelector('.complaint-type').textContent.toLowerCase();
    const complaintPreview = item.querySelector('.complaint-preview').textContent.toLowerCase();

    return complaintId.includes(searchLower) ||
        customerName.includes(searchLower) ||
        complaintType.includes(searchLower) ||
        complaintPreview.includes(searchLower);
}

function matchesDateFilter(item, dateFilter) {
    if (!dateFilter) return true;

    const complaintDate = new Date(item.querySelector('.complaint-time').textContent);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    switch (dateFilter) {
        case 'today':
            return complaintDate >= today;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            return complaintDate >= yesterday && complaintDate < today;
        case 'week':
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            return complaintDate >= weekAgo;
        case 'month':
            const monthAgo = new Date(today);
            monthAgo.setMonth(monthAgo.getMonth() - 1);
            return complaintDate >= monthAgo;
        case 'custom':
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            if (!startDate || !endDate) return true;

            const start = new Date(startDate);
            const end = new Date(endDate);
            end.setHours(23, 59, 59, 999);

            return complaintDate >= start && complaintDate <= end;
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

function sortComplaints(sortBy) {
    const complaintsList = document.getElementById('complaintsList');
    const complaintItems = Array.from(complaintsList.querySelectorAll('.complaint-item'));

    complaintItems.sort((a, b) => {
        switch (sortBy) {
            case 'priority':
                return getPriorityWeight(b) - getPriorityWeight(a);
            case 'date':
                return new Date(b.querySelector('.complaint-time').textContent) -
                    new Date(a.querySelector('.complaint-time').textContent);
            case 'date_old':
                return new Date(a.querySelector('.complaint-time').textContent) -
                    new Date(b.querySelector('.complaint-time').textContent);
            default:
                return 0;
        }
    });

    // Re-append sorted items
    complaintItems.forEach(item => {
        complaintsList.appendChild(item);
    });
}

function getPriorityWeight(item) {
    const priorityBadge = item.querySelector('.badge.priority-critical, .badge.priority-high, .badge.priority-medium, .badge.priority-normal');
    if (!priorityBadge) return 0;

    const priority = priorityBadge.textContent.toLowerCase().trim();
    switch (priority) {
        case 'critical':
            return 4;
        case 'high':
            return 3;
        case 'medium':
            return 2;
        case 'normal':
            return 1;
        default:
            return 0;
    }
}

function updateListTitleForFilters(searchQuery, dateFilter, priorityFilter, visibleCount) {
    const listTitle = document.getElementById('listTitle');
    const totalComplaints = document.querySelectorAll('.complaint-item').length;

    let title = '';
    const filters = [];

    if (searchQuery) filters.push(`Search: "${searchQuery}"`);
    if (dateFilter) filters.push(`Date: ${dateFilter}`);
    if (priorityFilter) filters.push(`Priority: ${priorityFilter}`);

    if (filters.length > 0) {
        title = `Filtered Results (${visibleCount}/${totalComplaints})`;
    } else {
        const activeFilter = document.querySelector('.filter-item.active');
        if (activeFilter) {
            title = activeFilter.querySelector('span').textContent;
        } else {
            title = 'All Complaints';
        }
    }

    listTitle.textContent = title;
}

function performRealTimeSearch(query) {
    applyAllFilters();
}

function highlightSearchTerms(searchTerm) {
    const complaintItems = document.querySelectorAll('.complaint-item');

    complaintItems.forEach(item => {
        if (item.style.display !== 'none') {
            const elements = item.querySelectorAll('.complaint-id, .complaint-customer, .complaint-type, .complaint-preview');

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
    const complaintItems = document.querySelectorAll('.complaint-item');

    complaintItems.forEach(item => {
        const elements = item.querySelectorAll('.complaint-id, .complaint-customer, .complaint-type, .complaint-preview');

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
    const complaintsList = document.getElementById('complaintsList');
    let emptyState = complaintsList.querySelector('.empty-state');

    if (visibleCount === 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <div class="empty-icon">
                    <i class="fas fa-search fa-2x text-muted"></i>
                </div>
                <h6 class="empty-title">No complaints found</h6>
                <p class="empty-message">
                    No complaints match your search criteria.
                </p>
            `;
            complaintsList.appendChild(emptyState);
        }
    } else if (emptyState) {
        emptyState.remove();
    }
}

function updateListTitleForSearch(query, visibleCount) {
    const listTitle = document.getElementById('listTitle');
    const activeFilter = document.querySelector('.filter-item.active');

    if (query.trim()) {
        const totalComplaints = document.querySelectorAll('.complaint-item').length;
        listTitle.textContent = `Search Results (${visibleCount}/${totalComplaints})`;
    } else {
        // Restore original title
        if (activeFilter) {
            const filterText = activeFilter.querySelector('span').textContent;
            listTitle.textContent = filterText;
        }
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
        } else if (filter === 'assigned') {
            currentUrl.searchParams.set('view', 'assigned');
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
    setInterval(function () {
        checkForNewComplaints();
    }, 30000);
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
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
            clearComplaintSelection();
        }
    });
}

function clearComplaintSelection() {
    const complaintItems = document.querySelectorAll('.complaint-item');
    const defaultState = document.getElementById('defaultState');
    const complaintDetails = document.getElementById('complaintDetails');

    // Remove active class from all items
    complaintItems.forEach(i => i.classList.remove('active'));

    // Show default state
    defaultState.style.display = 'flex';
    complaintDetails.style.display = 'none';
}

function setupAutoRefresh() {
    // Auto-refresh every 60 seconds if no modals are open
    setInterval(function () {
        if (!document.querySelector('.modal.show')) {
            // Check if user is active
            if (isUserActive()) {
                location.reload();
            }
        }
    }, 60000);
}

function checkForNewComplaints() {
    // Check for new complaints without full page reload
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            const currentComplaints = document.querySelectorAll('.complaint-item');
            const newComplaints = newDoc.querySelectorAll('.complaint-item');

            if (newComplaints.length > currentComplaints.length) {
                showNotification('New complaints available', 'info');
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

// Action functions (from modals)
function viewComplaint(complaintId) {
    window.open(`${BASE_URL}complaints/view/${encodeURIComponent(complaintId)}`, '_blank');
}

function forwardComplaint(complaintId) {
    document.getElementById('forwardComplaintId').value = complaintId;
    new bootstrap.Modal(document.getElementById('forwardModal')).show();
}

function closeComplaint(complaintId) {
    document.getElementById('closeComplaintId').value = complaintId;
    new bootstrap.Modal(document.getElementById('closeModal')).show();
}

function revertComplaint(complaintId) {
    document.getElementById('revertComplaintId').value = complaintId;
    new bootstrap.Modal(document.getElementById('revertModal')).show();
}

// Department users data handling
function updateUserDropdown() {
    const toDepartmentSelect = document.getElementById('toDepartment');
    const toUserSelect = document.getElementById('toUser');

    if (toDepartmentSelect && toUserSelect && typeof departmentUsers !== 'undefined') {
        toDepartmentSelect.addEventListener('change', function () {
            const department = this.value;
            toUserSelect.innerHTML = '<option value="">Select User (Optional)</option>';

            if (department && departmentUsers[department]) {
                departmentUsers[department].forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.login_id;
                    option.textContent = user.name;
                    toUserSelect.appendChild(option);
                });
            }
        });
    }
}

// Evidence modal functionality
function openEvidenceModal(url, filename) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('evidenceModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'evidenceModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Evidence: ${filename}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="evidenceContent"></div>
                    </div>
                    <div class="modal-footer">
                        <a href="${url}" download="${filename}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Update modal content
    const evidenceContent = modal.querySelector('#evidenceContent');
    const fileExtension = filename.toLowerCase().split('.').pop();

    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
        // Image file
        evidenceContent.innerHTML = `<img src="${url}" alt="${filename}" class="img-fluid evidence-modal-image">`;
    } else if (['pdf'].includes(fileExtension)) {
        // PDF file
        evidenceContent.innerHTML = `<iframe src="${url}" width="100%" height="500px" frameborder="0"></iframe>`;
    } else {
        // Other file types
        evidenceContent.innerHTML = `
            <div class="evidence-file-preview">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h6>${filename}</h6>
                <p class="text-muted">This file type cannot be previewed directly.</p>
                <a href="${url}" download="${filename}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download to View
                </a>
            </div>
        `;
    }

    // Show modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

// Initialize department dropdown functionality
document.addEventListener('DOMContentLoaded', function () {
    updateUserDropdown();
});