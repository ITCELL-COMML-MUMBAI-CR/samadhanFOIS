    </main>
    <!-- Main Content End -->
    
    <!-- Footer -->
    <footer class="footer py-4 mt-5" style="background: linear-gradient(135deg, #1a202c 0%, #2d3748 50%, #4a5568 100%); color: #e2e8f0;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-light mb-0" style="color: #e2e8f0 !important;">
                        © 2025 Central Railway. All rights reserved. Designed and Developed by ITCell Mumbai Commercial CR.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS (Animate On Scroll) JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>js/app.js"></script>
    <?php if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
        <!-- Load navbar.js for non-logged-in users -->
        <script src="<?php echo BASE_URL; ?>js/navbar.js"></script>
    <?php endif; ?>
    
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
                html: message,
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
