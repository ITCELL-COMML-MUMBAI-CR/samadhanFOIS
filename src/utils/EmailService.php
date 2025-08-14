<?php
/**
 * Email Service
 * Handles sending emails for the complaint system using Hostinger SMTP
 */

class EmailService {
    private $from;
    private $fromName;
    private $headers;
    
    public function __construct() {
        // Load email configuration
        if (file_exists(__DIR__ . '/../../config/email_config.php')) {
            require_once __DIR__ . '/../../config/email_config.php';
            $this->from = EMAIL_FROM;
            $this->fromName = EMAIL_FROM_NAME;
        } else {
            $this->from = 'admin@itcellbbcr.in';
            $this->fromName = 'SAMPARK FOIS - Railway Complaint System';
        }
        
        $this->headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->from . '>',
            'Reply-To: ' . (defined('EMAIL_REPLY_TO') ? EMAIL_REPLY_TO : $this->from),
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Configure SMTP for production
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            $this->configureSMTP();
        }
    }

    /**
     * Configure SMTP settings for Hostinger production
     */
    private function configureSMTP() {
        if (defined('SMTP_HOST')) {
            // Configure PHP mail settings for Hostinger SMTP
            ini_set('SMTP', SMTP_HOST);
            ini_set('smtp_port', SMTP_PORT);
            
            // Set additional headers for SMTP authentication
            $this->headers[] = 'X-SMTP-Host: ' . SMTP_HOST;
            $this->headers[] = 'X-SMTP-Port: ' . SMTP_PORT;
        }
    }
    
    /**
     * Send complaint confirmation email
     */
    public function sendComplaintConfirmation($customerEmail, $customerName, $complaintId, $complaintDetails) {
        $subject = "Complaint Submitted Successfully - ID: $complaintId";
        $message = $this->buildConfirmationEmail($customerName, $complaintId, $complaintDetails);
        
        return $this->sendEmail($customerEmail, $subject, $message);
    }
    
    /**
     * Send complaint status update email
     */
    public function sendStatusUpdate($customerEmail, $customerName, $complaintId, $oldStatus, $newStatus, $remarks = '') {
        $subject = "Complaint Status Updated - ID: $complaintId";
        $message = $this->buildStatusUpdateEmail($customerName, $complaintId, $oldStatus, $newStatus, $remarks);
        
        return $this->sendEmail($customerEmail, $subject, $message);
    }
    
    /**
     * Build confirmation email HTML content
     */
    private function buildConfirmationEmail($customerName, $complaintId, $complaintDetails) {
        $submissionDate = date('d-m-Y H:i A');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1e40af; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f8f9fa; padding: 20px; }
                .details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #1e40af; }
                .footer { background-color: #e5e7eb; padding: 15px; text-align: center; font-size: 12px; }
                .complaint-id { font-size: 24px; font-weight: bold; color: #1e40af; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚂 SAMPARK FOIS</h1>
                    <p>Railway Complaint Management System</p>
                </div>
                
                <div class='content'>
                    <h2>Dear $customerName,</h2>
                    
                    <p>Thank you for submitting your complaint. We have received your grievance and it has been assigned the following ID:</p>
                    
                    <div class='complaint-id'>$complaintId</div>
                    
                    <div class='details'>
                        <h3>Complaint Details:</h3>
                        <p><strong>Submission Date:</strong> $submissionDate</p>
                        <p><strong>Type:</strong> {$complaintDetails['complaint_type']}</p>
                        <p><strong>Subtype:</strong> {$complaintDetails['complaint_subtype']}</p>
                        <p><strong>Category:</strong> {$complaintDetails['category']}</p>
                        <p><strong>Location:</strong> {$complaintDetails['location']}</p>" . 
                        (!empty($complaintDetails['fnr_no']) ? "<p><strong>FNR Number:</strong> {$complaintDetails['fnr_no']}</p>" : "") . "
                        <p><strong>Status:</strong> Pending Review</p>
                        <p><strong>Assigned Department:</strong> {$complaintDetails['department']}</p>
                    </div>
                    
                    <h3>What happens next?</h3>
                    <ul>
                        <li>Your complaint has been forwarded to the {$complaintDetails['department']} department</li>
                        <li>You will receive updates via email as your complaint progresses</li>
                        <li>Expected response time: 24-48 hours</li>
                        <li>You can track your complaint status using the complaint ID above</li>
                    </ul>
                    
                    <h3>Need Help?</h3>
                    <p>If you have any questions about your complaint regarding this email, please contact us at:</p>
                    <ul>
                        <li>Email: sampark-admin@itcellbbcr.in</li>
                        <li>Reference your complaint ID: <strong>$complaintId</strong></li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from SAMPARK FOIS</p>
                    <p>Central Railway, Ministry of Railways, Government of India</p>
                    <p>Please do not reply to this email. For support, use the contact information above.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Build status update email HTML content
     */
    private function buildStatusUpdateEmail($customerName, $complaintId, $oldStatus, $newStatus, $remarks) {
        $updateDate = date('d-m-Y H:i A');
        
        $statusLabels = [
            'pending' => 'Pending Review',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            'rejected' => 'Rejected'
        ];
        
        $oldStatusLabel = $statusLabels[$oldStatus] ?? ucfirst($oldStatus);
        $newStatusLabel = $statusLabels[$newStatus] ?? ucfirst($newStatus);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1e40af; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f8f9fa; padding: 20px; }
                .details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #1e40af; }
                .footer { background-color: #e5e7eb; padding: 15px; text-align: center; font-size: 12px; }
                .status-update { font-size: 18px; font-weight: bold; padding: 10px; margin: 10px 0; }
                .status-old { background-color: #fef3c7; color: #92400e; }
                .status-new { background-color: #d1fae5; color: #065f46; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚂 SAMPARK FOIS</h1>
                    <p>Complaint Status Update</p>
                </div>
                
                <div class='content'>
                    <h2>Dear $customerName,</h2>
                    
                    <p>Your complaint status has been updated:</p>
                    
                    <div class='details'>
                        <h3>Complaint ID: $complaintId</h3>
                        <p><strong>Update Date:</strong> $updateDate</p>
                        
                        <div class='status-update status-old'>Previous Status: $oldStatusLabel</div>
                        <div class='status-update status-new'>Current Status: $newStatusLabel</div>
                        
                        " . ($remarks ? "<p><strong>Remarks:</strong> $remarks</p>" : "") . "
                    </div>
                    
                    <p>You can continue to track your complaint progress using your complaint ID.</p>
                    
                    <h3>Need Help?</h3>
                    <p>If you have any questions regarding this email, please contact us at:</p>
                    <ul>
                        <li>Email: sampark-admin@itcellbbcr.in</li>
                        <li>Reference your complaint ID: <strong>$complaintId</strong></li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from SAMPARK FOIS</p>
                    <p>Central Railway, Ministry of Railways, Government of India</p>
                    <p>Please do not reply to this email. For support, use the contact information above.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Send email using PHP mail function with Hostinger SMTP
     */
    private function sendEmail($to, $subject, $message) {
        try {
            // Check if mail server is available
            if (!$this->isMailServerAvailable()) {
                error_log("Mail server not available. Email not sent to: $to, Subject: $subject");
                return false;
            }
            
            // For production (Hostinger), configure SMTP settings
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
                // Configure PHP mail settings for Hostinger SMTP
                if (defined('SMTP_HOST')) {
                    ini_set('SMTP', SMTP_HOST);
                    ini_set('smtp_port', SMTP_PORT);
                    
                    // Add SMTP authentication headers
                    $this->headers[] = 'X-SMTP-Host: ' . SMTP_HOST;
                    $this->headers[] = 'X-SMTP-Port: ' . SMTP_PORT;
                    $this->headers[] = 'X-SMTP-Username: ' . SMTP_USERNAME;
                }
            }
            
            $headers = implode("\r\n", $this->headers);
            
            $result = mail($to, $subject, $message, $headers);
            
            if ($result) {
                error_log("Email sent successfully to: $to, Subject: $subject");
                return true;
            } else {
                error_log("Failed to send email to: $to, Subject: $subject");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if mail server is available
     */
    private function isMailServerAvailable() {
        // For Hostinger production, always return true
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            return true;
        }
        
        // In development environment, mail server might not be configured
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            return false;
        }
        
        // Check if we're on localhost (development environment)
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        if (in_array($serverName, ['localhost', '127.0.0.1', '::1'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate email address
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
