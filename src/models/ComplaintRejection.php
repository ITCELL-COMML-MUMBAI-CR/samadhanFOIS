<?php
/**
 * ComplaintRejection Model
 * Handles complaint rejection/revert tracking using the existing complaints table
 */

require_once 'BaseModel.php';

class ComplaintRejection extends BaseModel {
    protected $table = 'complaints';
    
    /**
     * Get rejections/reverts for a complaint
     * Since we use the status field in complaints table, this method returns complaints with 'Reverted' status
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT c.*, 
                   cu.Name as customer_name,
                   u.name as assigned_to_name
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN users u ON c.Assigned_To_Department = u.login_id
            WHERE c.complaint_id = ? AND c.status = 'Reverted'
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Log commercial rejection to concern department
     */
    public function logCommercialToConcern($complaintId, $revertedBy, $revertedTo, $reason) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET status = 'Reverted', 
                action_taken = ?, 
                Assigned_To_Department = ?, 
                updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$reason, $revertedTo, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Log concern rejection back to commercial
     */
    public function logConcernToCommercial($complaintId, $revertedBy, $revertedTo, $reason) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET status = 'Reverted', 
                action_taken = ?, 
                Assigned_To_Department = ?, 
                updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$reason, $revertedTo, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Log commercial rejection to customer
     */
    public function logCommercialToCustomer($complaintId, $revertedBy, $revertedTo, $reason) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET status = 'Reverted', 
                action_taken = ?, 
                Assigned_To_Department = ?, 
                updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$reason, $revertedTo, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Get recent rejections/reverts
     */
    public function getRecent($limit = 10, $userId = null) {
        $sql = "
            SELECT c.*, 
                   cu.Name as customer_name,
                   u.name as assigned_to_name
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN users u ON c.Assigned_To_Department = u.login_id
            WHERE c.status = 'Reverted'
        ";
        
        $params = [];
        
        if ($userId) {
            $sql .= " AND c.Assigned_To_Department = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY c.updated_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get rejection/revert statistics
     */
    public function getStatistics($filters = []) {
        $stats = [];
        
        // Base WHERE clause for filters
        $whereClause = "WHERE status = 'Reverted'";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause .= " AND Assigned_To_Department = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(updated_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(updated_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Total rejections/reverts
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM complaints $whereClause");
        $stmt->execute($params);
        $stats['total'] = $stmt->fetch()['count'];
        
        // By department
        $stmt = $this->connection->prepare("
            SELECT department, COUNT(*) as count 
            FROM complaints $whereClause
            GROUP BY department
        ");
        $stmt->execute($params);
        $deptStats = $stmt->fetchAll();
        
        foreach ($deptStats as $deptStat) {
            $stats['by_department'][$deptStat['department']] = $deptStat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Check if complaint has been rejected
     */
    public function hasRejectionAtStage($complaintId, $stage) {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM complaints 
            WHERE complaint_id = ? AND status = 'Reverted'
        ");
        $stmt->execute([$complaintId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get latest rejection for complaint
     */
    public function getLatestRejection($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT c.*, 
                   cu.Name as customer_name,
                   u.name as assigned_to_name
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN users u ON c.Assigned_To_Department = u.login_id
            WHERE c.complaint_id = ? AND c.status = 'Reverted'
            ORDER BY c.updated_at DESC
            LIMIT 1
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetch();
    }
}
?>
