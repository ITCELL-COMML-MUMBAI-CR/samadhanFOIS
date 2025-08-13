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
        
        $count = 0;
        
        // For now, return a simple count based on assigned complaints
        // This can be enhanced later with proper notification system
        if ($userRole !== 'customer') {
            require_once '../models/Complaint.php';
            $complaintModel = new Complaint();
            
            // Count pending assignments
            $pendingComplaints = $complaintModel->findAssignedTo($userId);
            $count = count(array_filter($pendingComplaints, function($complaint) {
                return $complaint['status'] === 'pending' || $complaint['status'] === 'in_progress';
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
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $notifications = [];
        
        // Simple notification system - can be enhanced later
        if ($userRole !== 'customer') {
            require_once '../models/Complaint.php';
            require_once '../models/Transaction.php';
            
            $complaintModel = new Complaint();
            $transactionModel = new Transaction();
            
            // Get recent complaints assigned to user
            $assignedComplaints = $complaintModel->findAssignedTo($userId, $limit);
            
            foreach ($assignedComplaints as $complaint) {
                if ($complaint['status'] === 'pending' || $complaint['status'] === 'in_progress') {
                    $notifications[] = [
                        'id' => 'complaint_' . $complaint['complaint_id'],
                        'type' => 'complaint_assigned',
                        'title' => 'Complaint Assigned',
                        'message' => "Complaint {$complaint['complaint_id']} has been assigned to you",
                        'created_at' => $complaint['created_at'],
                        'read' => false,
                        'url' => BASE_URL . 'complaints/view/' . $complaint['complaint_id']
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
