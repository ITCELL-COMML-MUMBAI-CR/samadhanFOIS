/**
 * Bulk Email Management JavaScript
 * Handles all client-side functionality for the bulk email page
 */

class BulkEmailManager {
    constructor() {
        this.initializeEventListeners();
    }

    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        // Recipient type change handler
        document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleRecipientTypeChange(e));
        });

        // Template change handler
        const templateSelect = document.getElementById('email_template');
        if (templateSelect) {
            templateSelect.addEventListener('change', (e) => this.handleTemplateChange(e));
        }

        // Form submission
        const form = document.getElementById('bulkEmailForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmission(e));
        }

        // Role checkbox change handlers
        document.querySelectorAll('.role-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateRecipientCount());
        });

        // Real-time validation
        this.setupRealTimeValidation();
    }



    /**
     * Handle recipient type change
     */
    handleRecipientTypeChange(event) {
        const userSelection = document.getElementById('userSelection');
        const userTypeSelection = document.getElementById('userTypeSelection');
        
        // Hide all selection areas first
        userSelection.style.display = 'none';
        userTypeSelection.style.display = 'none';
        
        // Show appropriate selection area
        if (event.target.value === 'select') {
            userSelection.style.display = 'block';
        } else if (event.target.value === 'by_role') {
            userTypeSelection.style.display = 'block';
        }
        
        this.updateRecipientCount();
    }

    /**
     * Handle template selection change
     */
    handleTemplateChange(event) {
        const selectedOption = event.target.options[event.target.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const subject = selectedOption.getAttribute('data-subject');
            const content = selectedOption.getAttribute('data-content');
            
            document.getElementById('email_subject').value = subject || '';
            document.getElementById('email_content').value = content || '';
            
            // Show/hide template variables based on template category
            this.toggleTemplateVariables(selectedOption.textContent.toLowerCase());
        }
    }

    /**
     * Toggle template variables section
     */
    toggleTemplateVariables(templateName) {
        const templateVariables = document.getElementById('templateVariables');
        if (templateName.includes('maintenance')) {
            templateVariables.style.display = 'block';
        } else {
            templateVariables.style.display = 'none';
        }
    }

    /**
     * Update recipient count display
     */
    updateRecipientCount() {
        const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
        const countElement = document.querySelector('.recipient-count');
        const countDisplay = document.querySelector('.recipient-count-display');
        
        let count = 0;
        
        if (recipientType === 'select') {
            const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
            count = selectedUsers.length;
        } else if (recipientType === 'by_role') {
            const selectedRoles = document.querySelectorAll('.role-checkbox:checked');
            count = this.getUserCountByRoles(selectedRoles);
        } else if (recipientType === 'all') {
            count = document.querySelectorAll('.user-checkbox').length;
        }
        
        if (countElement) {
            countElement.textContent = count;
        }
        
        // Update the display visibility
        if (countDisplay) {
            countDisplay.style.display = recipientType === 'select' ? 'block' : 'none';
        }
    }
    
    /**
     * Get user count by selected roles
     */
    getUserCountByRoles(selectedRoles) {
        let count = 0;
        selectedRoles.forEach(roleCheckbox => {
            const role = roleCheckbox.value;
            const roleLabel = roleCheckbox.closest('.form-check').querySelector('label');
            const countText = roleLabel.querySelector('small').textContent;
            const match = countText.match(/\((\d+) users\)/);
            if (match) {
                count += parseInt(match[1]);
            }
        });
        return count;
    }

    /**
     * Select all users
     */
    selectAllUsers() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        this.updateRecipientCount();
    }

    /**
     * Deselect all users
     */
    deselectAllUsers() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        this.updateRecipientCount();
    }

    /**
     * Preview email
     */
    previewEmail() {
        const subject = document.getElementById('email_subject').value.trim();
        const content = document.getElementById('email_content').value.trim();
        
        if (!subject || !content) {
            this.showAlert('Please fill in both subject and content before previewing.', 'warning');
            return;
        }
        
        const preview = `
            <div class="email-preview">
                <h6>Subject: ${this.escapeHtml(subject)}</h6>
                <hr>
                <div class="email-content">
                    ${content}
                </div>
            </div>
        `;
        
        document.getElementById('emailPreview').innerHTML = preview;
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }
    
    /**
     * Preview selected users by role
     */
    previewSelectedUsers() {
        const selectedRoles = document.querySelectorAll('.role-checkbox:checked');
        if (selectedRoles.length === 0) {
            this.showAlert('Please select at least one user type.', 'warning');
            return;
        }
        
        const previewContainer = document.getElementById('selectedUsersPreview');
        let previewHTML = '<div class="mb-2"><strong>Selected Users:</strong></div>';
        
        selectedRoles.forEach(roleCheckbox => {
            const role = roleCheckbox.value;
            const roleLabel = roleCheckbox.closest('.form-check').querySelector('label');
            const countText = roleLabel.querySelector('small').textContent;
            
            previewHTML += `
                <div class="alert alert-info alert-sm mb-2">
                    <i class="fas fa-users"></i> <strong>${role.charAt(0).toUpperCase() + role.slice(1)}s</strong>
                    <span class="text-muted">${countText}</span>
                </div>
            `;
        });
        
        previewContainer.innerHTML = previewHTML;
        previewContainer.style.display = 'block';
    }

    /**
     * Show test email modal
     */
    testEmail() {
        new bootstrap.Modal(document.getElementById('testEmailModal')).show();
    }

    /**
     * Send test email
     */
    async sendTestEmail() {
        const testEmail = document.getElementById('test_email').value.trim();
        if (!testEmail) {
            this.showAlert('Please enter a test email address.', 'warning');
            return;
        }

        if (!this.isValidEmail(testEmail)) {
            this.showAlert('Please enter a valid email address.', 'warning');
            return;
        }
        
        const formData = new FormData(document.getElementById('bulkEmailForm'));
        formData.append('test_email', testEmail);
        formData.append('action', 'test');
        
        try {
            this.setLoadingState(true);
            
            const response = await fetch(`${BASE_URL}api/bulk_email`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
                         if (!data.error) {
                 this.showAlert('Test email sent successfully!', 'success');
                 bootstrap.Modal.getInstance(document.getElementById('testEmailModal')).hide();
             } else {
                 this.showAlert('Error sending test email: ' + data.message, 'danger');
             }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error sending test email. Please try again.', 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Handle form submission
     */
    async handleFormSubmission(event) {
        event.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }
        
        const recipientCount = this.getRecipientCount();
        const confirmMessage = `Are you sure you want to send this email to ${recipientCount} recipient(s)?`;
        
        if (confirm(confirmMessage)) {
            this.setLoadingState(true);
            
            try {
                const formData = new FormData(event.target);
                const response = await fetch(event.target.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    this.showAlert(data.message, 'danger');
                } else {
                    this.showAlert(data.message, 'success');
                    // Reset form on success
                    event.target.reset();
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Error sending bulk email. Please try again.', 'danger');
            } finally {
                this.setLoadingState(false);
            }
        }
    }

    /**
     * Validate form
     */
    validateForm() {
        const subject = document.getElementById('email_subject').value.trim();
        const content = document.getElementById('email_content').value.trim();
        const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
        
        if (!subject || !content) {
            this.showAlert('Please fill in both subject and content.', 'warning');
            return false;
        }
        
        if (recipientType === 'select') {
            const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
            if (selectedUsers.length === 0) {
                this.showAlert('Please select at least one user.', 'warning');
                return false;
            }
        } else if (recipientType === 'by_role') {
            const selectedRoles = document.querySelectorAll('.role-checkbox:checked');
            if (selectedRoles.length === 0) {
                this.showAlert('Please select at least one user type.', 'warning');
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get recipient count
     */
    getRecipientCount() {
        const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
        
        if (recipientType === 'all') {
            return document.querySelectorAll('.user-checkbox').length;
        } else if (recipientType === 'select') {
            return document.querySelectorAll('.user-checkbox:checked').length;
        } else if (recipientType === 'by_role') {
            const selectedRoles = document.querySelectorAll('.role-checkbox:checked');
            return this.getUserCountByRoles(selectedRoles);
        }
        
        return 0;
    }

    /**
     * Setup real-time validation
     */
    setupRealTimeValidation() {
        const subjectInput = document.getElementById('email_subject');
        const contentInput = document.getElementById('email_content');
        
        if (subjectInput) {
            subjectInput.addEventListener('input', () => this.validateField(subjectInput, 'Subject is required'));
        }
        
        if (contentInput) {
            contentInput.addEventListener('input', () => this.validateField(contentInput, 'Content is required'));
        }
    }

    /**
     * Validate individual field
     */
    validateField(field, message) {
        const value = field.value.trim();
        const isValid = value.length > 0;
        
        field.classList.toggle('is-invalid', !isValid);
        
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = isValid ? '' : message;
    }

    /**
     * Set loading state
     */
    setLoadingState(loading) {
        const sendButton = document.getElementById('sendButton');
        if (sendButton) {
            sendButton.disabled = loading;
            sendButton.classList.toggle('loading', loading);
        }
    }

    /**
     * Show alert message
     */
    showAlert(message, type = 'info') {
        // Use centralized alert system if available
        if (typeof window.showAlert === 'function') {
            window.showAlert(message, type, 5000);
        } else {
            // Fallback to local implementation
            const alertContainer = document.createElement('div');
            alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
            alertContainer.innerHTML = `
                <i class="fas fa-${this.getAlertIcon(type)}"></i> ${this.escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertContainer, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertContainer.parentNode) {
                    alertContainer.remove();
                }
            }, 5000);
        }
    }

    /**
     * Get alert icon based on type
     */
    getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-triangle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * Validate email format
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Make functions globally available for inline onclick handlers
    window.selectAllUsers = function() {
        bulkEmailManager.selectAllUsers();
    };
    
    window.deselectAllUsers = function() {
        bulkEmailManager.deselectAllUsers();
    };
    
    window.previewEmail = function() {
        bulkEmailManager.previewEmail();
    };
    
    window.testEmail = function() {
        bulkEmailManager.testEmail();
    };
    
    window.sendTestEmail = function() {
        bulkEmailManager.sendTestEmail();
    };
    
    window.previewSelectedUsers = function() {
        bulkEmailManager.previewSelectedUsers();
    };
    
    // Initialize the bulk email manager
    const bulkEmailManager = new BulkEmailManager();
    
    // Add user checkbox change listeners
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => bulkEmailManager.updateRecipientCount());
    });
});
