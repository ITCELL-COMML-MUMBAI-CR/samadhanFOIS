/**
 * Email Template Management JavaScript
 * Handles all client-side functionality for the email template management page
 */

class EmailTemplateManager {
    constructor() {
        this.initializeEventListeners();
    }

    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        // Form submission
        const form = document.getElementById('templateForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmission(e));
        }

        // Real-time validation
        this.setupRealTimeValidation();
    }

    /**
     * Show add template modal
     */
    showAddTemplateModal() {
        this.resetForm();
        document.getElementById('templateModalTitle').textContent = 'Add New Template';
        document.getElementById('action').value = 'create';
        document.getElementById('template_id').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('templateModal'));
        modal.show();
    }

    /**
     * Edit template
     */
    async editTemplate(templateId) {
        try {
            this.setLoadingState(true);
            
            const response = await fetch(`${BASE_URL}api/email_templates`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get&template_id=${templateId}`
            });
            
            const data = await response.json();
            
            if (data.error) {
                this.showAlert(data.message, 'danger');
                return;
            }
            
            this.populateForm(data.data);
            document.getElementById('templateModalTitle').textContent = 'Edit Template';
            document.getElementById('action').value = 'update';
            
            const modal = new bootstrap.Modal(document.getElementById('templateModal'));
            modal.show();
            
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error loading template. Please try again.', 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Populate form with template data
     */
    populateForm(template) {
        document.getElementById('template_id').value = template.id;
        document.getElementById('template_name').value = template.name;
        document.getElementById('template_category').value = template.category;
        document.getElementById('template_subject').value = template.subject;
        document.getElementById('template_content').value = template.content;
        document.getElementById('template_description').value = template.description || '';
        document.getElementById('template_is_default').checked = template.is_default == 1;
    }

    /**
     * Reset form
     */
    resetForm() {
        document.getElementById('templateForm').reset();
        document.getElementById('template_id').value = '';
        document.getElementById('action').value = 'create';
        
        // Clear validation states
        document.querySelectorAll('.form-control').forEach(field => {
            field.classList.remove('is-invalid');
        });
        
        document.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.remove();
        });
    }

    /**
     * Preview template
     */
    async previewTemplate(templateId) {
        try {
            this.setLoadingState(true);
            
            const response = await fetch(`${BASE_URL}api/email_templates`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get&template_id=${templateId}`
            });
            
            const data = await response.json();
            
            if (data.error) {
                this.showAlert(data.message, 'danger');
                return;
            }
            
            this.showTemplatePreview(data.data);
            
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error loading template preview. Please try again.', 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Show template preview
     */
    showTemplatePreview(template) {
        const preview = `
            <div class="template-preview">
                <h6>Subject: ${this.escapeHtml(template.subject)}</h6>
                <div class="email-content">
                    ${template.content}
                </div>
                ${template.description ? `<hr><p class="text-muted"><strong>Description:</strong> ${this.escapeHtml(template.description)}</p>` : ''}
            </div>
        `;
        
        document.getElementById('templatePreview').innerHTML = preview;
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    /**
     * Preview template content from form
     */
    previewTemplateContent() {
        const subject = document.getElementById('template_subject').value.trim();
        const content = document.getElementById('template_content').value.trim();
        
        if (!subject || !content) {
            this.showAlert('Please fill in both subject and content before previewing.', 'warning');
            return;
        }
        
        const preview = `
            <div class="template-preview">
                <h6>Subject: ${this.escapeHtml(subject)}</h6>
                <div class="email-content">
                    ${content}
                </div>
            </div>
        `;
        
        document.getElementById('templatePreview').innerHTML = preview;
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    /**
     * Delete template
     */
    async deleteTemplate(templateId, templateName) {
        const confirmMessage = `Are you sure you want to delete the template "${templateName}"? This action cannot be undone.`;
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        try {
            this.setLoadingState(true);
            
            const response = await fetch(`${BASE_URL}api/email_templates`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&template_id=${templateId}`
            });
            
            const data = await response.json();
            
            if (data.error) {
                this.showAlert(data.message, 'danger');
            } else {
                this.showAlert(data.message, 'success');
                // Reload the page to refresh the template list
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error deleting template. Please try again.', 'danger');
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
        
        try {
            this.setLoadingState(true);
            
            const formData = new FormData(event.target);
            const response = await fetch(`${BASE_URL}api/email_templates`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.error) {
                this.showAlert(data.message, 'danger');
            } else {
                this.showAlert(data.message, 'success');
                // Close modal and reload page
                bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error saving template. Please try again.', 'danger');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Validate form
     */
    validateForm() {
        const name = document.getElementById('template_name').value.trim();
        const category = document.getElementById('template_category').value;
        const subject = document.getElementById('template_subject').value.trim();
        const content = document.getElementById('template_content').value.trim();
        
        let isValid = true;
        
        if (!name) {
            this.validateField(document.getElementById('template_name'), 'Template name is required');
            isValid = false;
        }
        
        if (!category) {
            this.validateField(document.getElementById('template_category'), 'Category is required');
            isValid = false;
        }
        
        if (!subject) {
            this.validateField(document.getElementById('template_subject'), 'Subject is required');
            isValid = false;
        }
        
        if (!content) {
            this.validateField(document.getElementById('template_content'), 'Content is required');
            isValid = false;
        }
        
        return isValid;
    }

    /**
     * Setup real-time validation
     */
    setupRealTimeValidation() {
        const fields = ['template_name', 'template_category', 'template_subject', 'template_content'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => {
                    this.clearFieldValidation(field);
                });
            }
        });
    }

    /**
     * Validate individual field
     */
    validateField(field, message) {
        field.classList.add('is-invalid');
        
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
    }

    /**
     * Clear field validation
     */
    clearFieldValidation(field) {
        field.classList.remove('is-invalid');
        
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    /**
     * Set loading state
     */
    setLoadingState(loading) {
        const saveButton = document.getElementById('saveTemplateBtn');
        if (saveButton) {
            saveButton.disabled = loading;
            saveButton.classList.toggle('loading', loading);
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
    window.showAddTemplateModal = function() {
        emailTemplateManager.showAddTemplateModal();
    };
    
    window.editTemplate = function(templateId) {
        emailTemplateManager.editTemplate(templateId);
    };
    
    window.previewTemplate = function(templateId) {
        emailTemplateManager.previewTemplate(templateId);
    };
    
    window.deleteTemplate = function(templateId, templateName) {
        emailTemplateManager.deleteTemplate(templateId, templateName);
    };
    
    window.previewTemplateContent = function() {
        emailTemplateManager.previewTemplateContent();
    };
    
    // Initialize the email template manager
    const emailTemplateManager = new EmailTemplateManager();
});
