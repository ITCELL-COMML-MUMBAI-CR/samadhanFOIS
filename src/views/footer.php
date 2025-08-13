    </main>
    <!-- Main Content End -->
    
    <!-- Footer -->
    <footer class="footer py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><i class="fas fa-train"></i> Samadhan FOIS</h5>
                    <p class="text-light">
                        Central Railway Freight Customer Complaint Management System
                    </p>
                    <p class="text-light mb-0">
                        <small>
                            Powered by Central Railway<br>
                            Ministry of Railways, Government of India
                        </small>
                    </p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-light text-decoration-none">Home</a></li>
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <li><a href="<?php echo BASE_URL; ?>dashboard" class="text-light text-decoration-none">Dashboard</a></li>
                            <li><a href="<?php echo BASE_URL; ?>complaints/new" class="text-light text-decoration-none">New Complaint</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>login" class="text-light text-decoration-none">Login</a></li>
                            <li><a href="<?php echo BASE_URL; ?>register" class="text-light text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>help" class="text-light text-decoration-none">Help Center</a></li>
                        <li><a href="<?php echo BASE_URL; ?>faq" class="text-light text-decoration-none">FAQ</a></li>
                        <li><a href="<?php echo BASE_URL; ?>contact" class="text-light text-decoration-none">Contact Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>guidelines" class="text-light text-decoration-none">Guidelines</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6>Contact Information</h6>
                    <div class="text-light">
                        <p class="mb-2">
                            <i class="fas fa-envelope"></i> 
                            <a href="mailto:complaints@cr.railnet.gov.in" class="text-light text-decoration-none">
                                complaints@cr.railnet.gov.in
                            </a>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone"></i> 
                            <a href="tel:+911234567890" class="text-light text-decoration-none">
                                +91 12345 67890
                            </a>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-clock"></i> 
                            24/7 Support Available
                        </p>
                    </div>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: #4a5568;">
            
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="text-light mb-0">
                        &copy; <?php echo date('Y'); ?> Central Railway. All rights reserved. | 
                        <a href="<?php echo BASE_URL; ?>privacy" class="text-light text-decoration-none">Privacy Policy</a> | 
                        <a href="<?php echo BASE_URL; ?>terms" class="text-light text-decoration-none">Terms of Service</a>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="text-light">
                        <small>
                            Version <?php echo APP_VERSION; ?> | 
                            Server Time: <?php echo date('d-M-Y H:i:s'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($customJS)): ?>
        <?php foreach ((array)$customJS as $jsFile): ?>
            <script src="<?php echo BASE_URL . $jsFile; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Global JavaScript functions
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Notification count update (if user is logged in)
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                updateNotificationCount();
                
                // Update notification count every 30 seconds
                setInterval(updateNotificationCount, 30000);
            <?php endif; ?>

            // Display session alert if available
            <?php
            require_once __DIR__ . '/../utils/SessionManager.php';
            $alert = SessionManager::getAlert();
            if ($alert):
            ?>
                showSweetAlert(
                    '<?php echo addslashes($alert['message']); ?>', 
                    '<?php echo addslashes($alert['type']); ?>'
                );
            <?php endif; ?>
        });
        
        // Utility functions
        function showSweetAlert(message, type = 'info') {
            const iconMap = {
                'success': 'success',
                'danger': 'error',
                'warning': 'warning',
                'info': 'info'
            };

            Swal.fire({
                icon: iconMap[type] || 'info',
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }
        
        function updateNotificationCount() {
            fetch('<?php echo BASE_URL; ?>api/notifications/count')
                .then(response => response.json())
                .then(data => {
                    const countElement = document.getElementById('notificationCount');
                    if (countElement && data.count !== undefined) {
                        countElement.textContent = data.count;
                        countElement.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => {
                    console.log('Error fetching notification count:', error);
                });
        }
        
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }
        
        // File upload validation
        function validateFileUpload(fileInput, maxSize = <?php echo MAX_FILE_SIZE; ?>, allowedTypes = <?php echo json_encode(ALLOWED_EXTENSIONS); ?>) {
            const files = fileInput.files;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Check file size
                if (file.size > maxSize) {
                    showSweetAlert(`File "${file.name}" is too large. Maximum size allowed is ${Math.round(maxSize / (1024 * 1024))}MB.`, 'danger');
                    fileInput.value = '';
                    return false;
                }
                
                // Check file type
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(fileExtension)) {
                    showSweetAlert(`File "${file.name}" has an invalid type. Allowed types: ${allowedTypes.join(', ')}`, 'danger');
                    fileInput.value = '';
                    return false;
                }
            }
            
            return true;
        }
    </script>
</body>
</html>
