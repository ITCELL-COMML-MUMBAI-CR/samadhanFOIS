/**
 * Customer Profile Page JavaScript
 * Handles form validation, password requirements, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize profile page functionality
    initializeProfilePage();
});

function initializeProfilePage() {
    // Initialize password validation
    initializePasswordValidation();
    
    // Initialize form submissions
    initializeFormSubmissions();
    
    // Initialize mobile number formatting
    initializeMobileFormatting();
    
    // Initialize auto-hide alerts
    initializeAlertAutoHide();
}

/**
 * Initialize password validation and requirements checking
 */
function initializePasswordValidation() {
    const newPasswordField = document.getElementById('new_password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const currentPasswordField = document.getElementById('current_password');
    
    if (!newPasswordField || !confirmPasswordField) return;
    
    const lengthCheck = document.getElementById('length-check');
    const matchCheck = document.getElementById('match-check');
    
    // Function to update password requirements
    function updatePasswordRequirements() {
        const newPassword = newPasswordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        // Check length requirement
        if (lengthCheck) {
            if (newPassword.length >= 6) {
                lengthCheck.classList.add('valid');
                lengthCheck.classList.remove('invalid');
            } else if (newPassword.length > 0) {
                lengthCheck.classList.add('invalid');
                lengthCheck.classList.remove('valid');
            } else {
                lengthCheck.classList.remove('valid', 'invalid');
            }
        }
        
        // Check match requirement
        if (matchCheck) {
            if (confirmPassword.length > 0) {
                if (newPassword === confirmPassword) {
                    matchCheck.classList.add('valid');
                    matchCheck.classList.remove('invalid');
                } else {
                    matchCheck.classList.add('invalid');
                    matchCheck.classList.remove('valid');
                }
            } else {
                matchCheck.classList.remove('valid', 'invalid');
            }
        }
    }
    
    // Add event listeners for password validation
    newPasswordField.addEventListener('input', updatePasswordRequirements);
    confirmPasswordField.addEventListener('input', updatePasswordRequirements);
    
    // Add show/hide password toggles
    addPasswordToggles([newPasswordField, confirmPasswordField, currentPasswordField]);
}

/**
 * Add show/hide password toggles to password fields
 */
function addPasswordToggles(passwordFields) {
    passwordFields.forEach(field => {
        if (!field) return;
        
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-outline-secondary btn-sm password-toggle';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        toggleButton.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            border: none;
            background: transparent;
            color: #6c757d;
            padding: 5px;
            border-radius: 4px;
        `;
        
        // Position the field container
        field.parentElement.style.position = 'relative';
        field.parentElement.appendChild(toggleButton);
        
        // Toggle password visibility
        toggleButton.addEventListener('click', function() {
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            const icon = type === 'password' ? 'fa-eye' : 'fa-eye-slash';
            toggleButton.innerHTML = `<i class="fas ${icon}"></i>`;
        });
    });
}

/**
 * Initialize form submissions with loading states
 */
function initializeFormSubmissions() {
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const submitButton = profileForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('loading');
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Updating...';
            }
        });
    }
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            // Validate password requirements before submission
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            if (!currentPassword) {
                e.preventDefault();
                showAlert('Please enter your current password', 'danger');
                return;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                showAlert('New password must be at least 6 characters long', 'danger');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showAlert('New passwords do not match', 'danger');
                return;
            }
            
            const submitButton = passwordForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('loading');
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Changing Password...';
            }
        });
    }
}

/**
 * Initialize mobile number formatting
 */
function initializeMobileFormatting() {
    const mobileField = document.getElementById('mobile');
    if (!mobileField) return;
    
    mobileField.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        
        // Limit to 10 digits
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        // Format as XXX-XXX-XXXX
        if (value.length >= 6) {
            value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
        } else if (value.length >= 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        
        e.target.value = value;
    });
}

/**
 * Initialize auto-hide alerts
 */
function initializeAlertAutoHide() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.parentElement.remove();
            }
        }, 5000);
        
        // Add click to dismiss functionality
        alert.addEventListener('click', function() {
            this.parentElement.remove();
        });
    });
}

/**
 * Show custom alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.parentElement.remove();
        }
    }, 5000);
}

/**
 * Create alert container if it doesn't exist
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.className = 'alert-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate mobile number format
 */
function validateMobile(mobile) {
    const cleanMobile = mobile.replace(/\D/g, '');
    return cleanMobile.length === 10;
}

/**
 * Format phone number for display
 */
function formatPhoneNumber(phoneNumber) {
    const cleaned = phoneNumber.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
    }
    return phoneNumber;
}

/**
 * Debounce function for performance optimization
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Smooth scroll to element
 */
function smoothScrollTo(element) {
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

/**
 * Handle form field focus effects
 */
function initializeFieldFocusEffects() {
    const formFields = document.querySelectorAll('.form-floating .form-control');
    
    formFields.forEach(field => {
        field.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        field.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

/**
 * Initialize tooltips if Bootstrap is available
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Handle responsive behavior
 */
function handleResponsiveBehavior() {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Adjust form layout for mobile
        const formRows = document.querySelectorAll('.row');
        formRows.forEach(row => {
            const cols = row.querySelectorAll('.col-md-6');
            cols.forEach(col => {
                col.className = col.className.replace('col-md-6', 'col-12');
            });
        });
    }
}

// Initialize additional features when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeFieldFocusEffects();
    initializeTooltips();
    handleResponsiveBehavior();
    
    // Handle window resize
    window.addEventListener('resize', debounce(handleResponsiveBehavior, 250));
});

// Export functions for potential external use
window.ProfilePage = {
    showAlert,
    validateEmail,
    validateMobile,
    formatPhoneNumber,
    smoothScrollTo
};
