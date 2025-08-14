/**
 * Notifications JavaScript
 * Handles notification dropdown and real-time updates
 */

// Global notification variables
let notificationData = [];
let notificationDropdownShown = false;

// Initialize notifications when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();
});

/**
 * Initialize notification system
 */
function initializeNotifications() {
    // Attach event listeners
    const notificationBell = document.querySelector('.notification-bell-container');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationBell && notificationDropdown) {
        // Use Bootstrap dropdown events
        notificationDropdown.addEventListener('show.bs.dropdown', function() {
            if (!notificationDropdownShown) {
                loadNotifications();
                notificationDropdownShown = true;
            }
        });
        
        // Reset flag when dropdown is hidden
        notificationDropdown.addEventListener('hidden.bs.dropdown', function() {
            notificationDropdownShown = false;
        });
        
        // Alternative click handler in case Bootstrap events don't fire
        notificationBell.addEventListener('click', function() {
            setTimeout(() => {
                if (!notificationDropdownShown) {
                    loadNotifications();
                    notificationDropdownShown = true;
                }
            }, 100);
        });
    }
    
    // Initial notification count update
    updateNotificationCount();
    
    // Periodic updates every 30 seconds
    setInterval(updateNotificationCount, 30000);
}

/**
 * Update notification count in navbar
 */
function updateNotificationCount() {
    fetch(BASE_URL + 'api/notifications/count')
        .then(response => response.json())
        .then(data => {
            const countElement = document.getElementById('notificationCount');
            const bellContainer = document.querySelector('.notification-bell-container');
            const count = (data && data.data && typeof data.data.count !== 'undefined') ? data.data.count : 0;
            
            if (countElement) {
                countElement.textContent = count;
                countElement.style.display = count > 0 ? 'flex' : 'none';
                
                // Update pulse animation
                const pulseElement = document.querySelector('.notification-pulse');
                if (pulseElement) {
                    pulseElement.style.display = count > 0 ? 'block' : 'none';
                }
                
                // Add/remove glow effect based on notification count
                if (bellContainer) {
                    if (count > 0) {
                        bellContainer.classList.add('has-notifications');
                    } else {
                        bellContainer.classList.remove('has-notifications');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
}

/**
 * Load and display notifications in dropdown
 */
function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    
    if (!notificationList) return;
    
    // Show loading state
    notificationList.innerHTML = `
        <div class="notification-item">
            <div class="d-flex justify-content-center align-items-center p-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading notifications...</span>
            </div>
        </div>
    `;
    
    fetch(BASE_URL + 'api/notifications/list?limit=10')
        .then(response => response.json())
        .then(data => {
            if (data && data.data) {
                notificationData = data.data;
                displayNotifications(notificationData);
            } else {
                showEmptyNotifications();
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            showErrorNotifications();
        });
}

/**
 * Display notifications in the dropdown
 */
function displayNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    
    if (!notificationList) return;
    
    if (!notifications || notifications.length === 0) {
        showEmptyNotifications();
        return;
    }
    
    let html = '';
    
    notifications.forEach(notification => {
        const icon = getNotificationIcon(notification.type);
        const timeAgo = formatTimeAgo(notification.created_at);
        const priorityBadge = notification.priority ? 
            `<span class="notification-priority ${notification.priority}">${notification.priority}</span>` : '';
        
        const readStatusClass = notification.read ? 'read' : 'unread';
        const readIcon = notification.read ? '<i class="fas fa-check-circle text-success" title="Read"></i>' : '<i class="fas fa-circle text-primary" title="Unread"></i>';
        
        html += `
            <div class="notification-item ${readStatusClass}" 
                 onclick="handleNotificationClick('${notification.id}', '${notification.url}')">
                <div class="notification-title">
                    <i class="fas ${icon} notification-type-icon ${notification.type}"></i>
                    ${escapeHtml(notification.title)}
                    ${priorityBadge}
                    <span class="notification-read-status">${readIcon}</span>
                </div>
                <div class="notification-message">
                    ${escapeHtml(notification.message)}
                </div>
                <div class="notification-time">
                    ${timeAgo}
                </div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
}

/**
 * Show empty state for notifications
 */
function showEmptyNotifications() {
    const notificationList = document.getElementById('notificationList');
    
    if (notificationList) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <div>No new notifications</div>
            </div>
        `;
    }
}

/**
 * Show error state for notifications
 */
function showErrorNotifications() {
    const notificationList = document.getElementById('notificationList');
    
    if (notificationList) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-exclamation-triangle"></i>
                <div>Failed to load notifications</div>
            </div>
        `;
    }
}

/**
 * Get icon for notification type
 */
function getNotificationIcon(type) {
    const iconMap = {
        'complaint_assigned': 'fa-clipboard-list',
        'approval_needed': 'fa-check-circle',
        'forward': 'fa-share',
        'assignment': 'fa-user-tag',
        'reply_received': 'fa-reply',
        'more_info_required': 'fa-info-circle',
        'complaint_resolved': 'fa-check-double'
    };
    
    return iconMap[type] || 'fa-bell';
}

/**
 * Handle notification click
 */
function handleNotificationClick(notificationId, url) {
    // Mark notification as read
    markNotificationAsRead(notificationId);
    
    // Navigate to URL
    if (url && url !== 'undefined') {
        window.location.href = url;
    }
}

/**
 * Mark notification as read
 */
function markNotificationAsRead(notificationId) {
    fetch(BASE_URL + 'api/notifications/mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        // Update notification count after marking as read
        updateNotificationCount();
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

/**
 * Mark all notifications as read
 */
function markAllAsRead() {
    fetch(BASE_URL + 'api/notifications/mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: 'all'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && !data.error) {
            // Update notification count and reload notifications
            updateNotificationCount();
            loadNotifications();
            
            // Show success message
            if (typeof showSweetAlert === 'function') {
                showSweetAlert('All notifications marked as read', 'success');
            }
        } else {
            console.error('Error marking all notifications as read:', data.message);
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

/**
 * Format time ago string
 */
function formatTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days !== 1 ? 's' : ''} ago`;
    } else {
        return date.toLocaleDateString('en-IN', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
