<?php
/**
 * Notification Helper
 * Utility functions for creating notifications during complaint workflow
 */

class NotificationHelper {
    
    /**
     * Create notification when complaint is assigned
     */
    public static function notifyComplaintAssigned($complaintId, $assignedToUserId, $fromUserId = null) {
        try {
            require_once __DIR__ . '/../models/Notification.php';
            $notificationModel = new Notification();
            
            $title = 'New Complaint Assigned';
            $message = "Complaint {$complaintId} has been assigned to you and requires your attention.";
            $url = BASE_URL . 'grievances/tome';
            
            return $notificationModel->createComplaintNotification(
                $assignedToUserId, 
                $complaintId, 
                'complaint_assigned', 
                $title, 
                $message, 
                $url
            );
        } catch (Exception $e) {
            error_log('Failed to create assignment notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create notification when complaint is forwarded
     */
    public static function notifyComplaintForwarded($complaintId, $toUserId, $fromUserId, $remarks = '') {
        try {
            require_once __DIR__ . '/../models/Notification.php';
            $notificationModel = new Notification();
            
            $title = 'Complaint Forwarded to You';
            $message = "Complaint {$complaintId} has been forwarded to you for action.";
            if (!empty($remarks)) {
                $message .= " Note: " . substr($remarks, 0, 100);
            }
            $url = BASE_URL . 'grievances/tome';
            
            return $notificationModel->createComplaintNotification(
                $toUserId, 
                $complaintId, 
                'forward', 
                $title, 
                $message, 
                $url
            );
        } catch (Exception $e) {
            error_log('Failed to create forward notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create notification when complaint status changes for customer
     */
    public static function notifyCustomerStatusChange($complaintId, $customerId, $status, $remarks = '') {
        try {
            require_once __DIR__ . '/../models/Notification.php';
            require_once __DIR__ . '/../models/User.php';
            
            $notificationModel = new Notification();
            $userModel = new User();
            
            // Get customer's login ID
            $customer = $userModel->findByCustomerId($customerId);
            if (!$customer) {
                return false;
            }
            
            $type = '';
            $title = '';
            $message = '';
            $url = BASE_URL . 'grievances/my';
            
            switch ($status) {
                case 'resolved':
                    $type = 'complaint_resolved';
                    $title = 'Complaint Resolved';
                    $message = "Your complaint {$complaintId} has been resolved. Please provide your feedback.";
                    break;
                    
                case 'rejected':
                    $type = 'more_info_required';
                    $title = 'More Information Required';
                    $message = "Your complaint {$complaintId} requires additional information.";
                    if (!empty($remarks)) {
                        $message .= " Reason: " . substr($remarks, 0, 100);
                    }
                    break;
                    
                case 'replied':
                    $type = 'reply_received';
                    $title = 'Reply Received';
                    $message = "Your complaint {$complaintId} has received a reply from our team.";
                    break;
                    
                default:
                    return false;
            }
            
            return $notificationModel->createComplaintNotification(
                $customer['login_id'], 
                $complaintId, 
                $type, 
                $title, 
                $message, 
                $url
            );
        } catch (Exception $e) {
            error_log('Failed to create customer status notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create notification for approval needed
     */
    public static function notifyApprovalNeeded($complaintId, $approverUserId) {
        try {
            require_once __DIR__ . '/../models/Notification.php';
            $notificationModel = new Notification();
            
            $title = 'Approval Required';
            $message = "Complaint {$complaintId} requires your approval before closure.";
            $url = BASE_URL . 'grievances/approvals';
            
            return $notificationModel->createComplaintNotification(
                $approverUserId, 
                $complaintId, 
                'approval_needed', 
                $title, 
                $message, 
                $url
            );
        } catch (Exception $e) {
            error_log('Failed to create approval notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send system-wide announcement
     */
    public static function sendSystemAnnouncement($title, $message, $priority = 'normal') {
        try {
            require_once __DIR__ . '/../models/Notification.php';
            require_once __DIR__ . '/../models/User.php';
            
            $notificationModel = new Notification();
            $userModel = new User();
            
            $allUsers = $userModel->findAll();
            $userIds = array_column($allUsers, 'login_id');
            
            return $notificationModel->createBulkNotifications($userIds, 'system', $title, $message, $priority);
        } catch (Exception $e) {
            error_log('Failed to send system announcement: ' . $e->getMessage());
            return 0;
        }
    }
}
?>
