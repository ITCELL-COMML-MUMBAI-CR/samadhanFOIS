<?php
/**
 * Notification Model
 * Handles notification persistence and management
 */

require_once 'BaseModel.php';

class Notification extends BaseModel {
    protected $table = 'notifications';
    
    /**
     * Create new notification
     */
    public function createNotification($data) {
        $data['notification_id'] = $this->generateNotificationId();
        $data['created_at'] = getCurrentDateTime();
        $data['is_read'] = 0;
        
        return $this->createWithId($data);
    }
    
    /**
     * Create record with custom ID
     */
    private function createWithId($data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute(array_values($data));
        
        return $result ? $data['notification_id'] : false;
    }
    
    /**
     * Get notifications for a user
     */
    public function findByUserId($userId, $limit = null, $onlyUnread = false) {
        $sql = "
            SELECT * FROM notifications 
            WHERE user_id = ?
        ";
        
        $params = [$userId];
        
        if ($onlyUnread) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        $stmt = $this->connection->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = ? 
            WHERE notification_id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $notificationId]);
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        $stmt = $this->connection->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = ? 
            WHERE user_id = ? AND is_read = 0
        ");
        return $stmt->execute([getCurrentDateTime(), $userId]);
    }
    
    /**
     * Count unread notifications for a user
     */
    public function countUnread($userId) {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Delete old notifications (older than 30 days)
     */
    public function cleanupOldNotifications() {
        $stmt = $this->connection->prepare("
            DELETE FROM notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        return $stmt->execute();
    }
    
    /**
     * Generate unique notification ID
     */
    private function generateNotificationId() {
        do {
            $id = 'NOTIF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM notifications WHERE notification_id = ?");
            $stmt->execute([$id]);
            $exists = $stmt->fetch()['count'] > 0;
        } while ($exists);
        
        return $id;
    }
    
    /**
     * Create notification for complaint status change
     */
    public function createComplaintNotification($userId, $complaintId, $type, $title, $message, $url = null) {
        return $this->createNotification([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_id' => $complaintId,
            'related_type' => 'complaint',
            'action_url' => $url,
            'priority' => 'normal'
        ]);
    }
    
    /**
     * Create system notification
     */
    public function createSystemNotification($userId, $type, $title, $message, $priority = 'normal') {
        return $this->createNotification([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_type' => 'system',
            'priority' => $priority
        ]);
    }
    
    /**
     * Bulk create notifications for multiple users
     */
    public function createBulkNotifications($userIds, $type, $title, $message, $priority = 'normal') {
        $successCount = 0;
        
        foreach ($userIds as $userId) {
            if ($this->createSystemNotification($userId, $type, $title, $message, $priority)) {
                $successCount++;
            }
        }
        
        return $successCount;
    }
}
?>
