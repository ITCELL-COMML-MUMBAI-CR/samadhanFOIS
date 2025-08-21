/**
 * New Support Ticket with Authentication JavaScript
 * Handles customer authentication and form submission
 */

class SupportTicketAuth {
    constructor() {
        this.initializeEventListeners();
        this.initializeTooltips();
    }

    /**
     * Initialize all event listeners
     */
    initializeEventListeners() {
        // Customer Authentication Form
        const authForm = document.getElementById('customerAuthForm');
        if (authForm) {
            authForm.addEventListener('submit', this.handleAuthentication.bind(this));
        }

        // Support Ticket Form
        const supportForm = document.getElementById('supportTicketForm');
        if (supportForm) {
            supportForm.addEventListener('submit', this.handleFormSubmission.bind(this));
            supportForm.addEventListener('reset', this.handleFormReset.bind(this));
        }

        // File Upload
        const fileInput = document.getElementById('evidence');
        const fileUploadArea = document.getElementById('fileUploadArea');
        if (fileInput && fileUploadArea) {
            this.setupFileUpload(fileInput, fileUploadArea);
        }

        // Type-Subtype Cascading Dropdown
        const typeSelect = document.getElementById('complaint_type');
        const subtypeSelect = document.getElementById('complaint_subtype');
        if (typeSelect && subtypeSelect) {
            this.setupCascadingDropdown(typeSelect, subtypeSelect);
        }
    }

    /**
     * Initialize Bootstrap tooltips
     */
    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Handle customer authentication
     */
    async handleAuthentication(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('authSubmitBtn');
        const originalBtnText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Authenticating...';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch(BASE_URL + 'customer-auth/authenticate', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Authentication successful
                Swal.fire({
                    icon: 'success',
                    title: 'Authentication Successful!',
                    text: `Welcome back, ${data.customer.name}!`,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Continue'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                // Authentication failed
                Swal.fire({
                    icon: 'error',
                    title: 'Authentication Failed',
                    text: data.message,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Authentication error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'There was a network error. Please try again.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }

    /**
     * Handle support ticket form submission
     */
    async handleFormSubmission(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('submitBtn');
        const descriptionField = document.getElementById('description');
        const originalBtnText = submitBtn.innerHTML;
        
        // Validate description length
        if (descriptionField.value.length < 20) {
            descriptionField.focus();
            descriptionField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            Swal.fire({
                icon: 'error',
                title: 'Description Too Short',
                html: `
                    <div class="text-start">
                        <p>Your description is only <strong>${descriptionField.value.length}</strong> characters long.</p>
                        <p>Please provide at least <strong>20 characters</strong> with more details about your issue.</p>
                        <div class="alert alert-info">
                            <small><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Include specific details like:</small>
                            <ul class="small mb-0 mt-2">
                                <li>When did the issue occur?</li>
                                <li>What exactly happened?</li>
                                <li>What was the impact?</li>
                                <li>Any error messages or codes?</li>
                            </ul>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'I Understand',
                width: '500px'
            });
            
            return;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Submitting Support Ticket...',
            html: 'Please wait while we process your request.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Submitting...';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const textData = await response.text();
                data = { success: false, message: textData };
            }
            
            if (data.success) {
                // Success
                Swal.fire({
                    icon: 'success',
                    title: 'Support Ticket Submitted Successfully!',
                    text: data.message || 'Your complaint has been submitted and will be reviewed by our team.',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'View My Tickets',
                    showCancelButton: true,
                    cancelButtonText: 'Submit Another',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = BASE_URL + 'support/assistance';
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        this.resetForm(form);
                    }
                });
            } else {
                // Error
                let errorMessage = 'There was an error submitting your support ticket. Please try again.';
                
                if (data.message) {
                    errorMessage = data.message;
                } else if (typeof data === 'string' && data.includes('alert-danger')) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    const errorElement = tempDiv.querySelector('.alert-danger');
                    if (errorElement) {
                        errorMessage = errorElement.textContent.trim();
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Form submission error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'There was a network error. Please check your connection and try again.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }

    /**
     * Handle form reset
     */
    handleFormReset(e) {
        const imagePreview = document.getElementById('imagePreview');
        const subtypeSelect = document.getElementById('complaint_subtype');
        
        if (imagePreview) {
            imagePreview.innerHTML = '';
        }
        
        if (subtypeSelect) {
            subtypeSelect.disabled = true;
            subtypeSelect.innerHTML = '<option value="">First select an issue type</option>';
        }
    }

    /**
     * Reset form completely
     */
    resetForm(form) {
        form.reset();
        this.handleFormReset({});
    }

    /**
     * Setup file upload functionality
     */
    setupFileUpload(fileInput, fileUploadArea) {
        // Drag and drop functionality
        if (typeof SAMPARKApp !== 'undefined' && SAMPARKApp.fileUpload) {
            SAMPARKApp.fileUpload.setupDragDrop(fileUploadArea);
        }
        
        // Click to upload
        fileUploadArea.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
                fileInput.click();
            }
        });
        
        // File selection
        fileInput.addEventListener('change', (e) => {
            const files = e.target.files;
            const imagePreview = document.getElementById('imagePreview');
            
            if (files.length === 0) {
                imagePreview.innerHTML = '';
                return;
            }
            
            if (typeof SAMPARKApp !== 'undefined' && SAMPARKApp.fileUpload) {
                const validation = SAMPARKApp.fileUpload.validate(files);
                
                if (!validation.valid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Upload Error',
                        text: validation.errors.join('\n'),
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                    fileInput.value = '';
                    imagePreview.innerHTML = '';
                    return;
                }
                
                SAMPARKApp.fileUpload.previewImages(files, imagePreview);
            }
        });
    }

    /**
     * Setup cascading dropdown for type and subtype
     */
    setupCascadingDropdown(typeSelect, subtypeSelect) {
        typeSelect.addEventListener('change', () => {
            const selectedType = typeSelect.value;
            this.updateSubtypeOptions(selectedType, subtypeSelect);
        });
        
        // Initialize if type is already selected
        if (typeSelect.value) {
            this.updateSubtypeOptions(typeSelect.value, subtypeSelect);
        }
    }

    /**
     * Update subtype options based on selected type
     */
    updateSubtypeOptions(selectedType, subtypeSelect) {
        subtypeSelect.innerHTML = '<option value="">Select Issue Subtype</option>';
        
        if (selectedType && window.typeSubtypeMapping && window.typeSubtypeMapping[selectedType]) {
            subtypeSelect.disabled = false;
            
            window.typeSubtypeMapping[selectedType].forEach(subtype => {
                const option = document.createElement('option');
                option.value = subtype;
                option.textContent = subtype;
                subtypeSelect.appendChild(option);
            });
        } else {
            subtypeSelect.disabled = true;
            subtypeSelect.innerHTML = '<option value="">First select an issue type</option>';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new SupportTicketAuth();
});

// Export for global access if needed
window.SupportTicketAuth = SupportTicketAuth;
