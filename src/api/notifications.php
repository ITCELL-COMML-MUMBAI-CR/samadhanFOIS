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
        
        // Use persistent notification model
        require_once __DIR__ . '/../models/Notification.php';
        $notificationModel = new Notification();
        
        $count = $notificationModel->countUnread($userId);
        
        sendSuccess(['count' => $count]);
        
    } catch (Exception $e) {
        error_log('Notification count error: ' . $e->getMessage());
        sendError('Failed to get notification count');
    }
}

function handleNotificationList() {
    try {
        $userId = $_SESSION['user_login_id'];
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        // Use persistent notification model
        require_once __DIR__ . '/../models/Notification.php';
        $notificationModel = new Notification();
        
        $notifications = $notificationModel->findByUserId($userId, $limit);
        
        // Format notifications for frontend
        $formattedNotifications = array_map(function($notification) {
            return [
                'id' => $notification['notification_id'],
                'type' => $notification['type'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'created_at' => $notification['created_at'],
                'read' => (bool)$notification['is_read'],
                'priority' => $notification['priority'],
                'url' => $notification['action_url'] ?? ''
            ];
        }, $notifications);
        
        sendSuccess($formattedNotifications);
        
    } catch (Exception $e) {
        error_log('Notification list error: ' . $e->getMessage());
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
        
        // Use persistent notification model
        require_once __DIR__ . '/../models/Notification.php';
        $notificationModel = new Notification();
        
        if ($notificationId === 'all') {
            // Mark all notifications as read for this user
            $userId = $_SESSION['user_login_id'];
            $result = $notificationModel->markAllAsRead($userId);
        } else {
            // Mark specific notification as read
            $result = $notificationModel->markAsRead($notificationId);
        }
        
        if ($result) {
            sendSuccess(['marked_as_read' => true]);
        } else {
            sendError('Failed to mark notification as read');
        }
        
    } catch (Exception $e) {
        error_log('Mark as read error: ' . $e->getMessage());
        sendError('Failed to mark notification as read');
    }
}
?>
