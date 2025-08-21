/**
 * Admin Customers Management JavaScript
 * Handles customer management functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize form validation
    initializeFormValidation();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize export functionality
    initializeExport();
});

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const editForm = document.getElementById('editCustomerForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditCustomer);
    }
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Add debounced search
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    this.closest('form').submit();
                }
            }, 500);
        });
    }
}

/**
 * Initialize export functionality
 */
function initializeExport() {
    // Export functionality will be implemented here
}

/**
 * Open edit customer modal
 */
function openEditCustomer(customerId) {
    // Find the customer row
    const row = [...document.querySelectorAll('tbody tr')].find(r => 
        r.querySelector('td small')?.textContent === customerId
    );
    
    if (!row) {
        SAMPARKApp.alerts.show('Customer not found', 'danger');
        return;
    }

    // Extract customer data from the row
    const cells = row.querySelectorAll('td');
    const customerData = {
        id: customerId,
        name: cells[1].innerText.trim(),
        email: cells[2].innerText.trim(),
        mobile: cells[3].innerText.trim(),
        company: cells[4].querySelector('.badge')?.innerText || '',
        designation: cells[5].innerText.trim() === '-' ? '' : cells[5].innerText.trim()
    };

    // Populate the edit form
    document.getElementById('editCustomerId').value = customerData.id;
    document.getElementById('editName').value = customerData.name;
    document.getElementById('editEmail').value = customerData.email;
    document.getElementById('editMobile').value = customerData.mobile;
    document.getElementById('editCompany').value = customerData.company;
    document.getElementById('editDesignation').value = customerData.designation;
    
    // Clear password fields
    document.getElementById('editPassword').value = '';
    document.getElementById('editConfirmPassword').value = '';

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    modal.show();
}

/**
 * Handle edit customer form submission
 */
function handleEditCustomer(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Validate password if provided
    const password = formData.get('Password');
    const confirmPassword = formData.get('confirm_password');
    
    if (password || confirmPassword) {
        if (!password) {
            SAMPARKApp.alerts.show('Please enter a new password', 'danger');
            return;
        }
        
        if (!confirmPassword) {
            SAMPARKApp.alerts.show('Please confirm the new password', 'danger');
            return;
        }
        
        if (password !== confirmPassword) {
            SAMPARKApp.alerts.show('Passwords do not match', 'danger');
            return;
        }
        
        if (password.length < 6) {
            SAMPARKApp.alerts.show('Password must be at least 6 characters long', 'danger');
            return;
        }
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    
    // Send AJAX request
    SAMPARKApp.utils.fetchJson(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(data => {
        if (data.success) {
            SAMPARKApp.alerts.show(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('editCustomerModal')).hide();
            // Refresh the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            SAMPARKApp.alerts.show(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.message.includes('non-JSON response')) {
            SAMPARKApp.alerts.show('Session may have expired. Please refresh the page and try again.', 'warning');
        } else {
            SAMPARKApp.alerts.show('An error occurred while updating the customer', 'danger');
        }
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * View customer details
 */
function viewCustomerDetails(customerId) {
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
    const content = document.getElementById('customerDetailsContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading customer details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch customer details via AJAX
    const formData = new FormData();
    formData.append('action', 'ajax');
    formData.append('ajax_action', 'get_customer_details');
    formData.append('customer_id', customerId);
    
    SAMPARKApp.utils.fetchJson(window.location.href, {
        method: 'POST',
        body: formData
    })
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (error.message.includes('non-JSON response')) {
                content.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Session may have expired. Please refresh the page and try again.
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Failed to load customer details
                    </div>
                `;
            }
        });
}

/**
 * Delete customer
 */
function deleteCustomer(customerId, customerName) {
    if (!confirm(`Are you sure you want to delete customer "${customerName}"?\n\nThis action cannot be undone and will also delete all associated complaints.`)) {
        return;
    }
    
    // Show loading overlay
    showLoadingOverlay();
    
    // Send delete request
    const formData = new FormData();
    formData.append('action', 'ajax');
    formData.append('ajax_action', 'delete_customer');
    formData.append('customer_id', customerId);
    
    SAMPARKApp.utils.fetchJson(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(data => {
        hideLoadingOverlay();
        
        if (data.success) {
            SAMPARKApp.alerts.show(data.message, 'success');
            // Refresh the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            SAMPARKApp.alerts.show(data.message, 'danger');
        }
    })
    .catch(error => {
        hideLoadingOverlay();
        console.error('Error:', error);
        SAMPARKApp.alerts.show('An error occurred while deleting the customer', 'danger');
    });
}

/**
 * Refresh table
 */
function refreshTable() {
    const refreshBtn = document.querySelector('button[onclick="refreshTable()"]');
    const originalText = refreshBtn.innerHTML;
    
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Refreshing...';
    
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

/**
 * Export customers data
 */
function exportCustomers() {
    const exportBtn = document.querySelector('button[onclick="exportCustomers()"]');
    const originalText = exportBtn.innerHTML;
    
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
    
    // Get current filters
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {
        search: urlParams.get('search') || '',
        company: urlParams.get('company') || '',
        status: urlParams.get('status') || ''
    };
    
    // Create export URL
    const exportUrl = `${window.location.origin}${window.location.pathname}?action=export&${new URLSearchParams(filters).toString()}`;
    
    // Trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `customers_export_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset button
    setTimeout(() => {
        exportBtn.disabled = false;
        exportBtn.innerHTML = originalText;
    }, 1000);
}



/**
 * Show loading overlay
 */
function showLoadingOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(overlay);
}

/**
 * Hide loading overlay
 */
function hideLoadingOverlay() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

/**
 * Format phone number
 */
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    input.value = value;
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate mobile number
 */
function validateMobile(mobile) {
    const re = /^[0-9]{10}$/;
    return re.test(mobile);
}

/**
 * Validate password
 */
function validatePassword(field) {
    const value = field.value.trim();
    const isValid = value.length >= 6;
    
    field.classList.remove('is-valid', 'is-invalid');
    
    if (value !== '') {
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
        }
    }
    
    return isValid;
}

/**
 * Validate confirm password
 */
function validateConfirmPassword(field, password) {
    const value = field.value.trim();
    const isValid = value === password && value.length >= 6;
    
    field.classList.remove('is-valid', 'is-invalid');
    
    if (value !== '') {
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
        }
    }
    
    return isValid;
}

/**
 * Real-time form validation
 */
function validateFormField(field, validationFunction) {
    const value = field.value.trim();
    const isValid = validationFunction(value);
    
    field.classList.remove('is-valid', 'is-invalid');
    
    if (value !== '') {
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
        }
    }
    
    return isValid;
}

/**
 * Initialize real-time validation for edit form
 */
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editCustomerForm');
    if (editForm) {
        const emailField = editForm.querySelector('#editEmail');
        const mobileField = editForm.querySelector('#editMobile');
        const passwordField = editForm.querySelector('#editPassword');
        const confirmPasswordField = editForm.querySelector('#editConfirmPassword');
        
        if (emailField) {
            emailField.addEventListener('input', function() {
                validateFormField(this, validateEmail);
            });
        }
        
        if (mobileField) {
            mobileField.addEventListener('input', function() {
                formatPhoneNumber(this);
                validateFormField(this, validateMobile);
            });
        }
        
        // Password validation
        if (passwordField) {
            passwordField.addEventListener('input', function() {
                validatePassword(this);
                if (confirmPasswordField && confirmPasswordField.value) {
                    validateConfirmPassword(confirmPasswordField, this.value);
                }
            });
        }
        
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                if (passwordField) {
                    validateConfirmPassword(this, passwordField.value);
                }
            });
        }
    }
});

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + F to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Ctrl/Cmd + N to add new customer
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const addBtn = document.querySelector('a[href*="customer/add"]');
        if (addBtn) {
            addBtn.click();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }
});

/**
 * Auto-save form data (optional feature)
 */
function autoSaveForm() {
    const form = document.getElementById('editCustomerForm');
    if (form) {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        localStorage.setItem('edit_customer_form_draft', JSON.stringify(data));
    }
}

/**
 * Restore form from auto-save
 */
function restoreFormDraft() {
    const draft = localStorage.getItem('edit_customer_form_draft');
    if (draft) {
        try {
            const formData = JSON.parse(draft);
            const form = document.getElementById('editCustomerForm');
            if (form) {
                Object.keys(formData).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = formData[key];
                    }
                });
            }
        } catch (e) {
            console.error('Error restoring form draft:', e);
        }
    }
}

/**
 * Clear form draft
 */
function clearFormDraft() {
    localStorage.removeItem('edit_customer_form_draft');
}

// Auto-save form data every 30 seconds
setInterval(autoSaveForm, 30000);

// Restore draft when modal opens
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editCustomerModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function() {
            restoreFormDraft();
        });
        
        editModal.addEventListener('hidden.bs.modal', function() {
            clearFormDraft();
        });
    }
});

/**
 * Utility function for debouncing
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
 * Export functions for potential testing
 */
window.adminCustomersJS = {
    openEditCustomer,
    deleteCustomer,
    viewCustomerDetails,
    refreshTable,
    exportCustomers,
    validateEmail,
    validateMobile,
    validatePassword,
    validateConfirmPassword,
    formatPhoneNumber,
    debounce
};
