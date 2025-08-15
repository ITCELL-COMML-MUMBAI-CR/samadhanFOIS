/**
 * Bulk Email Management JavaScript
 * Handles all client-side functionality for the bulk email page
 */

class BulkEmailManager {
    constructor() {
        this.emailTemplates = {};
        this.initializeEventListeners();
        this.loadTemplates();
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

        // Real-time validation
        this.setupRealTimeValidation();
    }

    /**
     * Load email templates from the page
     */
    loadTemplates() {
        try {
            // Templates are embedded in the page via PHP
            if (typeof emailTemplates !== 'undefined') {
                this.emailTemplates = emailTemplates;
            }
        } catch (error) {
            console.error('Error loading templates:', error);
        }
    }

    /**
     * Handle recipient type change
     */
    handleRecipientTypeChange(event) {
        const userSelection = document.getElementById('userSelection');
        if (event.target.value === 'select') {
            userSelection.style.display = 'block';
        } else {
            userSelection.style.display = 'none';
        }
        this.updateRecipientCount();
    }

    /**
     * Handle template selection change
     */
    handleTemplateChange(event) {
        const template = this.emailTemplates[event.target.value];
        if (template) {
            document.getElementById('email_subject').value = template.subject;
            document.getElementById('email_content').value = template.content;
            
            // Show/hide template variables
            this.toggleTemplateVariables(event.target.value);
        }
    }

    /**
     * Toggle template variables section
     */
    toggleTemplateVariables(templateKey) {
        const templateVariables = document.getElementById('templateVariables');
        if (templateKey === 'system_maintenance') {
            templateVariables.style.display = 'block';
        } else {
            templateVariables.style.display = 'none';
        }
    }

    /**
     * Update recipient count display
     */
    updateRecipientCount() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        const countElement = document.querySelector('.recipient-count');
        if (countElement) {
            countElement.textContent = selectedUsers.length;
        }
        
        // Update the display visibility
        const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
        const countDisplay = document.querySelector('.recipient-count-display');
        if (countDisplay) {
            countDisplay.style.display = recipientType === 'select' ? 'block' : 'none';
        }
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
            
            const response = await fetch(BASE_URL + 'api/bulk_email.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
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
    handleFormSubmission(event) {
        event.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }
        
        const recipientCount = this.getRecipientCount();
        const confirmMessage = `Are you sure you want to send this email to ${recipientCount} recipient(s)?`;
        
        if (confirm(confirmMessage)) {
            this.setLoadingState(true);
            event.target.submit();
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
        } else {
            return document.querySelectorAll('.user-checkbox:checked').length;
        }
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
    
    // Initialize the bulk email manager
    const bulkEmailManager = new BulkEmailManager();
    
    // Add user checkbox change listeners
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => bulkEmailManager.updateRecipientCount());
    });
});
