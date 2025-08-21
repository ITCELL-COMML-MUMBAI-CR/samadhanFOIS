/**
 * Add Customer Page JavaScript
 * Handles form validation, customer suggestions, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const customerForm = document.getElementById('customerForm');
    const nameField = document.getElementById('Name');
    const companyNameField = document.getElementById('CompanyName');
    const emailField = document.getElementById('Email');
    const mobileField = document.getElementById('MobileNumber');
    const designationField = document.getElementById('Designation');
    const passwordField = document.getElementById('Password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');
    
    // Display elements
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordMatch = document.getElementById('passwordMatch');
    
    // Original button text for loading states
    const originalSubmitText = submitBtn.innerHTML;
    
    // Initialize all functionality
    initializeFormValidation();
    initializePasswordValidation();
    initializeFormSubmission();
    
    /**
     * Initialize form validation
     */
    function initializeFormValidation() {
        // Real-time validation
        nameField.addEventListener('input', validateName);
        companyNameField.addEventListener('input', validateCompanyName);
        emailField.addEventListener('input', validateEmail);
        mobileField.addEventListener('input', validateMobile);
        designationField.addEventListener('input', validateDesignation);
    }
    
    /**
     * Initialize password validation
     */
    function initializePasswordValidation() {
        passwordField.addEventListener('input', function() {
            validatePasswordStrength();
            validatePasswordMatch();
        });
        
        confirmPasswordField.addEventListener('input', validatePasswordMatch);
    }
    
    /**
     * Validate password strength
     */
    function validatePasswordStrength() {
        const password = passwordField.value;
        let strength = 'weak';
        let strengthText = '';
        
        if (password.length === 0) {
            passwordStrength.innerHTML = '';
            passwordStrength.className = 'password-strength';
            return;
        }
        
        if (password.length >= 6) {
            strength = 'medium';
            strengthText = 'Medium strength';
            
            if (password.length >= 8 && 
                /[A-Z]/.test(password) && 
                /[a-z]/.test(password) && 
                /[0-9]/.test(password)) {
                strength = 'strong';
                strengthText = 'Strong password';
            }
        } else {
            strengthText = 'Too short (min 6 characters)';
        }
        
        passwordStrength.className = `password-strength ${strength}`;
        passwordStrength.setAttribute('title', strengthText);
    }
    
    /**
     * Validate password match
     */
    function validatePasswordMatch() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        if (confirmPassword.length === 0) {
            passwordMatch.innerHTML = '';
            passwordMatch.className = 'password-match';
            confirmPasswordField.setCustomValidity('');
            return;
        }
        
        if (password === confirmPassword) {
            passwordMatch.innerHTML = '<i class="fas fa-check"></i> Passwords match';
            passwordMatch.className = 'password-match match';
            confirmPasswordField.setCustomValidity('');
        } else {
            passwordMatch.innerHTML = '<i class="fas fa-times"></i> Passwords do not match';
            passwordMatch.className = 'password-match no-match';
            confirmPasswordField.setCustomValidity('Passwords do not match');
        }
    }
    

    
    /**
     * Initialize form submission
     */
    function initializeFormSubmission() {
        customerForm.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Customer...';
            
            // Re-enable after timeout (in case of issues)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalSubmitText;
            }, 10000);
        });
        
        resetBtn.addEventListener('click', function() {
            // Clear validation states
            document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                el.classList.remove('is-valid', 'is-invalid');
            });
            
            // Clear password indicators
            passwordStrength.innerHTML = '';
            passwordStrength.className = 'password-strength';
            passwordMatch.innerHTML = '';
            passwordMatch.className = 'password-match';
            
            // Reset custom validity
            confirmPasswordField.setCustomValidity('');
            
            // Focus first field
            setTimeout(() => {
                nameField.focus();
            }, 100);
        });
    }
    
    /**
     * Individual field validations
     */
    function validateName() {
        const value = nameField.value.trim();
        const isValid = value.length >= 2;
        
        updateFieldValidation(nameField, isValid);
        return isValid;
    }
    
    function validateCompanyName() {
        const value = companyNameField.value.trim();
        const isValid = value.length >= 2;
        
        updateFieldValidation(companyNameField, isValid);
        return isValid;
    }
    
    function validateEmail() {
        const value = emailField.value.trim();
        const isValid = value === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        
        updateFieldValidation(emailField, isValid);
        return isValid;
    }
    
    function validateMobile() {
        const value = mobileField.value.trim();
        const isValid = value === '' || /^[0-9]{10}$/.test(value);
        
        updateFieldValidation(mobileField, isValid);
        return isValid;
    }

    function validateDesignation() {
        const value = designationField.value.trim();
        const isValid = value.length >= 2;
        updateFieldValidation(designationField, isValid);
        return isValid;
    }
    
    /**
     * Update field validation visual state
     */
    function updateFieldValidation(field, isValid) {
        field.classList.remove('is-valid', 'is-invalid');
        
        if (field.value.trim() !== '') {
            if (isValid) {
                field.classList.add('is-valid');
            } else {
                field.classList.add('is-invalid');
            }
        }
    }
    
    /**
     * Reset form to initial state
     */
    function resetForm() {
        customerForm.reset();
        
        // Clear validation states
        document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });
        
        // Clear password indicators
        passwordStrength.innerHTML = '';
        passwordStrength.className = 'password-strength';
        passwordMatch.innerHTML = '';
        passwordMatch.className = 'password-match';
        
        // Reset custom validity
        confirmPasswordField.setCustomValidity('');
        
        // Focus first field
        nameField.focus();
    }
    
    /**
     * Utility function for displaying notifications
     */
    function showNotification(message, type = 'info') {
        // Use centralized alert system if available
        if (typeof window.showAlert === 'function') {
            window.showAlert(message, type, 5000);
        } else {
            // Fallback to local implementation
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show notification-popup`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                max-width: 400px;
            `;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification && notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    }
    
    /**
     * Keyboard shortcuts
     */
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            if (!submitBtn.disabled) {
                customerForm.submit();
            }
        }
    });
    
    /**
     * Form auto-save (optional feature)
     */
    function autoSaveForm() {
        const formData = {
            Name: nameField.value,
            CompanyName: companyNameField.value,
            Email: emailField.value,
            MobileNumber: mobileField.value,
            Designation: designationField.value
        };
        
        localStorage.setItem('add_customer_form_draft', JSON.stringify(formData));
    }
    
    /**
     * Restore form from auto-save
     */
    function restoreFormDraft() {
        const draft = localStorage.getItem('add_customer_form_draft');
        if (draft) {
            try {
                const formData = JSON.parse(draft);
                nameField.value = formData.Name || '';
                companyNameField.value = formData.CompanyName || '';
                emailField.value = formData.Email || '';
                mobileField.value = formData.MobileNumber || '';
                designationField.value = formData.Designation || '';
                
                // Trigger validation
                validateName();
                validateCompanyName();
                validateEmail();
                validateMobile();
                validateDesignation();
            } catch (e) {
                console.error('Error restoring form draft:', e);
            }
        }
    }
    
    /**
     * Clear form draft on successful submission
     */
    customerForm.addEventListener('submit', function() {
        localStorage.removeItem('add_customer_form_draft');
    });
    
    // Auto-save form data every 30 seconds
    setInterval(autoSaveForm, 30000);
    
    // Restore draft on page load
    restoreFormDraft();
    
    // Initial focus
    nameField.focus();
    
    // Add smooth animations to form elements
    document.querySelectorAll('.form-floating').forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
        element.classList.add('fade-in');
    });
    
    // Check for success message on page load and clear form if present
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        // Clear form after displaying success message for a moment
        setTimeout(() => {
            resetForm();
        }, 5000);
    }
});

/**
 * Global utility functions
 */

// Format phone number as user types
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    input.value = value;
}

// Debounce function for search
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

// Export functions for potential testing
window.addCustomerJS = {
    formatPhoneNumber,
    debounce
};
