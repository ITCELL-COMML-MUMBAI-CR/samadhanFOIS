/**
 * Navbar Enhancement JavaScript
 * Provides enhanced interactions and animations for the stunning navbar
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize navbar animations
    initializeNavbarAnimations();
    
    // Initialize notification system
    initializeNotifications();
    
    // Initialize user profile interactions
    initializeUserProfile();
    
    // Initialize responsive behavior
    initializeResponsiveBehavior();
});

/**
 * Initialize navbar animations
 */
function initializeNavbarAnimations() {
    // Add scroll effect to navbar
    const navbar = document.querySelector('.railway-navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }
    
    // Add hover effects to navigation links
    const navLinks = document.querySelectorAll('.nav-link-animated');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.05)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.nav-link-animated, .login-link, .register-link');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            createRippleEffect(e, this);
        });
    });
}

/**
 * Create ripple effect on click
 */
function createRippleEffect(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

/**
 * Initialize notification system
 */
function initializeNotifications() {
    const notificationBell = document.querySelector('.notification-bell-container');
    const notificationCount = document.getElementById('notificationCount');
    
    if (notificationBell && notificationCount) {
        // Add click handler for notifications
        notificationBell.addEventListener('click', function() {
            showNotificationPanel();
        });
        
        // Simulate notification updates (replace with real API calls)
        setInterval(() => {
            updateNotificationCount();
        }, 30000); // Update every 30 seconds
    }
}

/**
 * Show notification panel
 */
function showNotificationPanel() {
    // Create notification panel
    const panel = document.createElement('div');
    panel.className = 'notification-panel';
    panel.innerHTML = `
        <div class="notification-panel-header">
            <h6>Notifications</h6>
            <button class="close-notifications">&times;</button>
        </div>
        <div class="notification-list">
            <div class="notification-item">
                <i class="fas fa-info-circle text-primary"></i>
                <div class="notification-content">
                    <div class="notification-title">System Update</div>
                    <div class="notification-message">New features have been added to the system.</div>
                    <div class="notification-time">2 minutes ago</div>
                </div>
            </div>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(panel);
    
    // Add close functionality
    const closeBtn = panel.querySelector('.close-notifications');
    closeBtn.addEventListener('click', () => {
        panel.remove();
    });
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (panel.parentNode) {
            panel.remove();
        }
    }, 5000);
}

/**
 * Update notification count
 */
function updateNotificationCount() {
    const countElement = document.getElementById('notificationCount');
    if (countElement) {
        // Simulate random notification count (replace with real API call)
        const newCount = Math.floor(Math.random() * 5);
        countElement.textContent = newCount;
        
        if (newCount > 0) {
            countElement.style.display = 'flex';
            // Add pulse animation
            countElement.classList.add('notification-pulse-active');
            setTimeout(() => {
                countElement.classList.remove('notification-pulse-active');
            }, 1000);
        } else {
            countElement.style.display = 'none';
        }
    }
}

/**
 * Initialize user profile interactions
 */
function initializeUserProfile() {
    const userProfileLink = document.querySelector('.user-profile-link');
    const userAvatar = document.querySelector('.user-avatar');
    
    if (userAvatar) {
        // Add hover effect to user avatar
        userAvatar.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        userAvatar.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    }
    
    // Add logout confirmation
    const logoutLink = document.querySelector('.logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            confirmLogout(this.href);
        });
    }
}

/**
 * Confirm logout action
 */
function confirmLogout(logoutUrl) {
    if (typeof Swal === 'undefined') {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = logoutUrl;
        }
        return;
    }
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = logoutUrl;
        }
    });
}

/**
 * Initialize responsive behavior
 */
function initializeResponsiveBehavior() {
    const navbarToggler = document.querySelector('.custom-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        // Add smooth animation to mobile menu
        navbarToggler.addEventListener('click', function() {
            setTimeout(() => {
                if (navbarCollapse.classList.contains('show')) {
                    navbarCollapse.style.animation = 'slideDown 0.3s ease-out';
                } else {
                    navbarCollapse.style.animation = 'slideUp 0.3s ease-out';
                }
            }, 10);
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            // Reset mobile menu state on desktop
            if (navbarCollapse) {
                navbarCollapse.classList.remove('show');
                navbarCollapse.style.animation = '';
            }
        }
    });
}

/**
 * Add CSS animations for enhanced effects
 */
function addEnhancedStyles() {
    const style = document.createElement('style');
    style.textContent = `
        /* Enhanced navbar styles */
        .navbar-scrolled {
            background: rgba(30, 58, 138, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }
        
        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Notification panel */
        .notification-panel {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 350px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1050;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .notification-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .close-notifications {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        
        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .notification-item {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s ease;
        }
        
        .notification-item:hover {
            background-color: #f9fafb;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .notification-message {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .notification-time {
            color: #9ca3af;
            font-size: 0.75rem;
        }
        
        /* Enhanced notification pulse */
        .notification-pulse-active {
            animation: notificationPulse 1s ease-in-out;
        }
        
        @keyframes notificationPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        /* Mobile menu animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }
        
        /* Enhanced hover effects */
        .nav-link-animated:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .logo-container:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.3);
        }
        
        /* Loading animation for async operations */
        .loading {
            position: relative;
            overflow: hidden;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }
    `;
    document.head.appendChild(style);
}

// Initialize enhanced styles
addEnhancedStyles();
