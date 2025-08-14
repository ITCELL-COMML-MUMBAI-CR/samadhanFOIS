<?php
/**
 * Notifications API
 * Handles notification-related requests
 */

switch ($action) {
    case 'count':
        if ($method === 'GET') {
            handleNotificationCount();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'list':
        if ($method === 'GET') {
            handleNotificationList();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    case 'mark_read':
        if ($method === 'POST') {
            handleMarkAsRead();
        } else {
            sendError('Method not allowed', 405);
        }
        break;
        
    default:
        sendError('Invalid notification action', 400);
        break;
}

function handleNotificationCount() {
    try {
        $userId = $_SESSION['user_login_id'];
        $userRole = $_SESSION['user_role'];
        $customerId = $_SESSION['user_customer_id'] ?? null;
        
        $count = 0;
        
        // For now, return a simple count based on assigned complaints
        // This can be enhanced later with proper notification system
        if ($userRole !== 'customer') {
            require_once __DIR__ . '/../models/Complaint.php';
            $complaintModel = new Complaint();
            
            // Count pending assignments and approvals (for Commercial)
            $pendingComplaints = $complaintModel->findAssignedTo($userId);
            $count = count(array_filter($pendingComplaints, function($complaint) use ($userRole) {
                if ($complaint['status'] === 'pending') return true;
                if ($userRole === 'controller' && ($complaint['status'] ?? '') === 'awaiting_approval') return true;
                return false;
            }));
        } else {
            // Customer notifications: replied, rejected, or resolved (awaiting feedback)
            require_once __DIR__ . '/../models/Complaint.php';
            $complaintModel = new Complaint();
            $customerComplaints = $complaintModel->findByCustomer($customerId);
            $count = count(array_filter($customerComplaints, function($complaint) {
                return in_array($complaint['status'], ['replied', 'rejected', 'resolved']);
            }));
        }
        
        sendSuccess(['count' => $count]);
        
    } catch (Exception $e) {
        sendError('Failed to get notification count');
    }
}

function handleNotificationList() {
    try {
        $userId = $_SESSION['user_login_id'];
        $userRole = $_SESSION['user_role'];
        $customerId = $_SESSION['user_customer_id'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $notifications = [];
        
        // Simple notification system - can be enhanced later
        if ($userRole !== 'customer') {
            require_once __DIR__ . '/../models/Complaint.php';
            require_once __DIR__ . '/../models/Transaction.php';
            
            $complaintModel = new Complaint();
            $transactionModel = new Transaction();
            
            // Get recent complaints assigned to user
            $assignedComplaints = $complaintModel->findAssignedTo($userId, $limit);
            
            foreach ($assignedComplaints as $complaint) {
                if ($complaint['status'] === 'pending') {
                    $notifications[] = [
                        'id' => 'complaint_' . $complaint['complaint_id'],
                        'type' => 'complaint_assigned',
                        'title' => 'New Complaint Assigned',
                        'message' => "Complaint {$complaint['complaint_id']} is pending your reply",
                        'created_at' => $complaint['created_at'],
                        'read' => false,
                        'url' => BASE_URL . 'complaints/view/' . $complaint['complaint_id']
                    ];
                } elseif ($complaint['status'] === 'awaiting_approval') {
                    $notifications[] = [
                        'id' => 'approval_' . $complaint['complaint_id'],
                        'type' => 'approval',
                        'title' => 'Approval Needed',
                        'message' => "Complaint {$complaint['complaint_id']} awaiting your approval",
                        'created_at' => $complaint['updated_at'] ?? $complaint['created_at'],
                        'read' => false,
                        'url' => BASE_URL . 'grievances/approvals'
                    ];
                }
            }
            
            // Get recent transactions
            $recentTransactions = $transactionModel->getRecent(5, $userId);
            
            foreach ($recentTransactions as $transaction) {
                $notifications[] = [
                    'id' => 'transaction_' . $transaction['transaction_id'],
                    'type' => 'transaction',
                    'title' => ucfirst(str_replace('_', ' ', $transaction['transaction_type'])),
                    'message' => substr($transaction['remarks'], 0, 100) . '...',
                    'created_at' => $transaction['created_at'],
                    'read' => false,
                    'url' => BASE_URL . 'complaints/view/' . $transaction['complaint_id']
                ];
            }
        } else {
            // Customer notifications for replied/rejected/resolved complaints
            require_once __DIR__ . '/../models/Complaint.php';
            $complaintModel = new Complaint();
            $customerComplaints = $complaintModel->findByCustomer($customerId, $limit);
            foreach ($customerComplaints as $complaint) {
                if (in_array($complaint['status'], ['replied', 'rejected', 'resolved'])) {
                    $notifications[] = [
                        'id' => 'cust_update_' . $complaint['complaint_id'],
                        'type' => $complaint['status'] === 'replied' ? 'reply' : ($complaint['status'] === 'resolved' ? 'resolved' : 'rejection'),
                        'title' => $complaint['status'] === 'replied' ? 'Reply Received' : ($complaint['status'] === 'resolved' ? 'Action Taken Approved' : 'More Information Requested'),
                        'message' => $complaint['status'] === 'replied'
                            ? "Complaint {$complaint['complaint_id']} has a reply from Commercial."
                            : ($complaint['status'] === 'resolved' ? "Complaint {$complaint['complaint_id']} action taken has been approved. Please provide feedback." : "Complaint {$complaint['complaint_id']} needs more information per remarks."),
                        'created_at' => $complaint['updated_at'] ?? $complaint['created_at'],
                        'read' => false,
                        'url' => BASE_URL . 'complaints/view/' . $complaint['complaint_id']
                    ];
                }
            }
        }
        
        // Sort by created_at desc
        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Limit results
        $notifications = array_slice($notifications, 0, $limit);
        
        sendSuccess($notifications);
        
    } catch (Exception $e) {
        sendError('Failed to get notifications');
    }
}

function handleMarkAsRead() {
    try {
        $input = getJsonInput();
        $notificationId = $input['notification_id'] ?? '';
        
        if (empty($notificationId)) {
            sendError('Notification ID is required');
        }
        
        // For now, just return success
        // In a full implementation, you would update the notification status in database
        sendSuccess(['marked_as_read' => true]);
        
    } catch (Exception $e) {
        sendError('Failed to mark notification as read');
    }
}
?>
