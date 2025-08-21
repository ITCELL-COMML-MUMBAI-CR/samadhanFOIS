/**
 * Main JavaScript file for SAMPARK FOIS - Railway Complaint System
 * 
 * This file can be used for general purpose JavaScript functions
 * that are used across the application.
 */

// Prevent duplicate loading
if (typeof window.SAMPARKApp !== 'undefined') {
    // Already loaded, exit gracefully
    console.warn('SAMPARKApp already loaded, skipping initialization');
} else {

// Global App Object
const SAMPARKApp = {
    // Configuration
    config: {
        apiUrl: (typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin + '/') + 'api/',
        maxFileSize: 2 * 1024 * 1024, // 2MB
        allowedFileTypes: ['jpg', 'jpeg', 'png', 'gif'],
        maxFiles: 3
    },
    
    // Utility functions
    utils: {
        // Format date to Indian locale
        formatDate: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        },
        
        // Format date and time to Indian locale
        formatDateTime: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        // Sanitize HTML content
        sanitizeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        // Show loading spinner
        showLoading: function(element) {
            if (element) {
                element.innerHTML = '<span class="loading-spinner"></span> Loading...';
                element.disabled = true;
            }
        },
        
        // Hide loading spinner
        hideLoading: function(element, originalText) {
            if (element) {
                element.innerHTML = originalText;
                element.disabled = false;
            }
        },
        
        // Generate random ID
        generateId: function() {
            return 'id_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },
        
        // Safe JSON response handler
        handleJsonResponse: function(response) {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Handle non-JSON response (likely HTML error page or redirect)
                throw new Error('Server returned non-JSON response. Please refresh the page and try again.');
            }
        },
        
        // Enhanced fetch with JSON handling
        fetchJson: function(url, options = {}) {
            return fetch(url, options)
                .then(response => SAMPARKApp.utils.handleJsonResponse(response));
        }
    },
    
    // Alert system
    alerts: {
        show: function(message, type = 'info', duration = 5000) {
            const alertId = SAMPARKApp.utils.generateId();
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${SAMPARKApp.utils.sanitizeHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Find or create alert container
            let alertContainer = document.querySelector('.alert-container');
            if (!alertContainer) {
                alertContainer = document.createElement('div');
                alertContainer.className = 'alert-container container-fluid mt-3';
                const mainContent = document.querySelector('.main-content');
                if (mainContent) {
                    mainContent.insertBefore(alertContainer, mainContent.firstChild);
                }
            }
            
            alertContainer.insertAdjacentHTML('beforeend', alertHtml);
            
            // Auto-hide after duration
            if (duration > 0) {
                setTimeout(() => {
                    const alertElement = document.getElementById(alertId);
                    if (alertElement) {
                        SAMPARKApp.alerts.dismissAlert(alertElement);
                    }
                }, duration);
            }
        },
        
        success: function(message, duration = 5000) {
            this.show(message, 'success', duration);
        },
        
        error: function(message, duration = 5000) {
            this.show(message, 'danger', duration);
        },
        
        warning: function(message, duration = 5000) {
            this.show(message, 'warning', duration);
        },
        
        info: function(message, duration = 5000) {
            this.show(message, 'info', duration);
        },
        
        // Dismiss alert with animation
        dismissAlert: function(alertElement) {
            if (!alertElement) return;
            
            // Add slide-out animation
            alertElement.classList.add('alert-slide-out');
            
            // Remove alert after animation
            setTimeout(() => {
                if (alertElement && alertElement.parentNode) {
                    try {
                        const bsAlert = new bootstrap.Alert(alertElement);
                        bsAlert.close();
                    } catch (e) {
                        // Fallback: remove manually if Bootstrap Alert fails
                        alertElement.remove();
                    }
                }
            }, 300); // Wait for slide-out animation
        },
        
        // Initialize auto-dismiss for all alerts
        initAutoDismiss: function() {
            // Handle existing alerts
            this.autoDismissExistingAlerts();
            
            // Set up observer for dynamically created alerts
            this.setupAlertObserver();
        },
        
        // Auto-dismiss existing alerts
        autoDismissExistingAlerts: function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                // Add slide-in animation
                alert.classList.add('alert-slide-in');
                
                // Set timeout to auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        SAMPARKApp.alerts.dismissAlert(alert);
                    }
                }, 5000);
                
                // Add close button if not present
                if (!alert.querySelector('.btn-close')) {
                    const closeButton = document.createElement('button');
                    closeButton.type = 'button';
                    closeButton.className = 'btn-close';
                    closeButton.setAttribute('data-bs-dismiss', 'alert');
                    closeButton.setAttribute('aria-label', 'Close');
                    closeButton.innerHTML = '&times;';
                    alert.appendChild(closeButton);
                }
                
                // Add click handler for manual close
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        SAMPARKApp.alerts.dismissAlert(alert);
                    });
                }
            });
        },
        
        // Set up observer for dynamically created alerts
        setupAlertObserver: function() {
            // Create global functions for other scripts to use
            window.showAlert = function(message, type = 'info', duration = 5000) {
                const alertContainer = document.createElement('div');
                alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
                alertContainer.innerHTML = `
                    <i class="fas fa-${SAMPARKApp.alerts.getAlertIcon(type)}"></i> ${SAMPARKApp.utils.sanitizeHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                // Find the best container to insert the alert
                let container = document.querySelector('.alert-container');
                if (!container) {
                    container = document.querySelector('.container-fluid');
                }
                if (!container) {
                    container = document.querySelector('.container');
                }
                if (!container) {
                    container = document.body;
                }
                
                container.insertBefore(alertContainer, container.firstChild);
                
                // Add slide-in animation
                alertContainer.classList.add('alert-slide-in');
                
                // Auto-dismiss after specified duration
                setTimeout(() => {
                    if (alertContainer.parentNode) {
                        SAMPARKApp.alerts.dismissAlert(alertContainer);
                    }
                }, duration);
                
                // Add click handler for manual close
                const closeBtn = alertContainer.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        SAMPARKApp.alerts.dismissAlert(alertContainer);
                    });
                }
            };
            
            // Global showToast function for compatibility
            window.showToast = function(type, message, duration = 5000) {
                const iconMap = {
                    'success': 'success',
                    'danger': 'error',
                    'error': 'error',
                    'warning': 'warning',
                    'info': 'info'
                };
                
                Swal.fire({
                    icon: iconMap[type] || 'info',
                    html: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: duration,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            };
            
            // Set up MutationObserver to watch for new alerts
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && node.classList && node.classList.contains('alert')) {
                            // New alert added
                            if (!node.classList.contains('alert-permanent')) {
                                node.classList.add('alert-slide-in');
                                
                                setTimeout(() => {
                                    if (node.parentNode) {
                                        SAMPARKApp.alerts.dismissAlert(node);
                                    }
                                }, 5000);
                                
                                // Add close button if not present
                                if (!node.querySelector('.btn-close')) {
                                    const closeButton = document.createElement('button');
                                    closeButton.type = 'button';
                                    closeButton.className = 'btn-close';
                                    closeButton.setAttribute('data-bs-dismiss', 'alert');
                                    closeButton.setAttribute('aria-label', 'Close');
                                    closeButton.innerHTML = '&times;';
                                    node.appendChild(closeButton);
                                }
                                
                                // Add click handler for manual close
                                const closeBtn = node.querySelector('.btn-close');
                                if (closeBtn) {
                                    closeBtn.addEventListener('click', function() {
                                        SAMPARKApp.alerts.dismissAlert(node);
                                    });
                                }
                            }
                        }
                    });
                });
            });
            
            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        },
        
        // Get alert icon based on type
        getAlertIcon: function(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-triangle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }
    },
    
    // File upload handling
    fileUpload: {
        validate: function(files) {
            if (!files || files.length === 0) {
                return { valid: true, errors: [] };
            }
            
            const errors = [];
            const config = SAMPARKApp.config;
            
            if (files.length > config.maxFiles) {
                errors.push(`Maximum ${config.maxFiles} files allowed`);
                return { valid: false, errors };
            }
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Check file size
                if (file.size > config.maxFileSize) {
                    errors.push(`File "${file.name}" is too large. Maximum size: ${Math.round(config.maxFileSize / (1024 * 1024))}MB`);
                }
                
                // Check file type
                const extension = file.name.split('.').pop().toLowerCase();
                if (!config.allowedFileTypes.includes(extension)) {
                    errors.push(`File "${file.name}" has invalid type. Allowed: ${config.allowedFileTypes.join(', ')}`);
                }
            }
            
            return { valid: errors.length === 0, errors };
        },
        
        setupDragDrop: function(element) {
            if (!element) return;
            
            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                element.classList.add('dragover');
            });
            
            element.addEventListener('dragleave', function(e) {
                e.preventDefault();
                element.classList.remove('dragover');
            });
            
            element.addEventListener('drop', function(e) {
                e.preventDefault();
                element.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                const fileInput = element.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        },
        
        previewImages: function(files, container) {
            if (!container) return;
            
            container.innerHTML = '';
            
            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'col-md-4 mb-3';
                        preview.innerHTML = `
                            <div class="card">
                                <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted">${file.name}</small>
                                    <br>
                                    <small class="text-muted">${Math.round(file.size / 1024)} KB</small>
                                </div>
                            </div>
                        `;
                        container.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    },
    
    // API calls
    api: {
        request: async function(endpoint, options = {}) {
            const url = SAMPARKApp.config.apiUrl + endpoint;
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            const finalOptions = { ...defaultOptions, ...options };
            
            try {
                const response = await fetch(url, finalOptions);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'API request failed');
                }
                
                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        },
        
        get: function(endpoint) {
            return this.request(endpoint, { method: 'GET' });
        },
        
        post: function(endpoint, data) {
            return this.request(endpoint, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        put: function(endpoint, data) {
            return this.request(endpoint, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        
        delete: function(endpoint) {
            return this.request(endpoint, { method: 'DELETE' });
        }
    },
    
    // Form handling
    forms: {
        serialize: function(form) {
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            return data;
        },
        
        validate: function(form) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        },
        
        reset: function(form) {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
        }
    },
    
    // Initialize the application
    init: function() {
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize file upload areas
            const uploadAreas = document.querySelectorAll('.file-upload-area');
            uploadAreas.forEach(area => {
                SAMPARKApp.fileUpload.setupDragDrop(area);
            });
            
            // Initialize file input change handlers
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const validation = SAMPARKApp.fileUpload.validate(this.files);
                    
                    if (!validation.valid) {
                        SAMPARKApp.alerts.error(validation.errors.join('<br>'));
                        this.value = '';
                        return;
                    }
                    
                    // Preview images if container exists
                    const previewContainer = document.querySelector('.image-preview-container');
                    if (previewContainer && this.files.length > 0) {
                        SAMPARKApp.fileUpload.previewImages(this.files, previewContainer);
                    }
                });
            });
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize rating systems
            const ratingContainers = document.querySelectorAll('.rating');
            ratingContainers.forEach(container => {
                SAMPARKApp.rating.init(container);
            });
            
            // Initialize alert auto-dismiss
            SAMPARKApp.alerts.initAutoDismiss();
            
            //console.log('SAMPARK App initialized successfully');
        });
    },
    
    // Rating system
    rating: {
        init: function(container) {
            const stars = container.querySelectorAll('.star');
            const input = container.querySelector('input[type="hidden"]');
            
            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    const rating = index + 1;
                    if (input) input.value = rating;
                    
                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    stars.forEach((s, i) => {
                        if (i <= index) {
                            s.style.color = '#fbbf24';
                        } else {
                            s.style.color = '#e2e8f0';
                        }
                    });
                });
            });
            
            container.addEventListener('mouseleave', function() {
                const currentRating = input ? parseInt(input.value) || 0 : 0;
                stars.forEach((s, i) => {
                    if (i < currentRating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#e2e8f0';
                    }
                });
            });
        }
    }
};

// Initialize the application
SAMPARKApp.init();

// Make it globally available
window.SAMPARKApp = SAMPARKApp;
}
