<?php
/**
 * ComplaintRejection Model
 * Handles complaint rejection tracking
 */

require_once 'BaseModel.php';

class ComplaintRejection extends BaseModel {
    protected $table = 'complaint_rejections';
    
    /**
     * Create rejection record
     */
    public function createRejection($data) {
        $data['created_at'] = getCurrentDateTime();
        return $this->create($data);
    }
    
    /**
     * Get rejections for a complaint
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT cr.*, 
                   u1.name as rejected_by_name,
                   u2.name as rejected_to_name
            FROM complaint_rejections cr
            LEFT JOIN users u1 ON cr.rejected_by = u1.login_id
            LEFT JOIN users u2 ON cr.rejected_to = u2.login_id
            WHERE cr.complaint_id = ?
            ORDER BY cr.created_at DESC
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Log commercial rejection to concern department
     */
    public function logCommercialToConcern($complaintId, $rejectedBy, $rejectedTo, $reason) {
        return $this->createRejection([
            'complaint_id' => $complaintId,
            'rejected_by' => $rejectedBy,
            'rejected_to' => $rejectedTo,
            'rejection_reason' => $reason,
            'rejection_stage' => 'commercial_to_concern'
        ]);
    }
    
    /**
     * Log concern rejection back to commercial
     */
    public function logConcernToCommercial($complaintId, $rejectedBy, $rejectedTo, $reason) {
        return $this->createRejection([
            'complaint_id' => $complaintId,
            'rejected_by' => $rejectedBy,
            'rejected_to' => $rejectedTo,
            'rejection_reason' => $reason,
            'rejection_stage' => 'concern_to_commercial'
        ]);
    }
    
    /**
     * Log commercial rejection to customer
     */
    public function logCommercialToCustomer($complaintId, $rejectedBy, $rejectedTo, $reason) {
        return $this->createRejection([
            'complaint_id' => $complaintId,
            'rejected_by' => $rejectedBy,
            'rejected_to' => $rejectedTo,
            'rejection_reason' => $reason,
            'rejection_stage' => 'commercial_to_customer'
        ]);
    }
    
    /**
     * Get recent rejections
     */
    public function getRecent($limit = 10, $userId = null) {
        $sql = "
            SELECT cr.*, 
                   c.complaint_type,
                   u1.name as rejected_by_name,
                   u2.name as rejected_to_name
            FROM complaint_rejections cr
            LEFT JOIN complaints c ON cr.complaint_id = c.complaint_id
            LEFT JOIN users u1 ON cr.rejected_by = u1.login_id
            LEFT JOIN users u2 ON cr.rejected_to = u2.login_id
        ";
        
        $params = [];
        
        if ($userId) {
            $sql .= " WHERE (cr.rejected_by = ? OR cr.rejected_to = ?)";
            $params = [$userId, $userId];
        }
        
        $sql .= " ORDER BY cr.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get rejection statistics
     */
    public function getStatistics($filters = []) {
        $stats = [];
        
        // Base WHERE clause for filters
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause .= " AND (rejected_by = ? OR rejected_to = ?)";
            $params[] = $filters['user_id'];
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Total rejections
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM complaint_rejections $whereClause");
        $stmt->execute($params);
        $stats['total'] = $stmt->fetch()['count'];
        
        // By rejection stage
        $stmt = $this->connection->prepare("
            SELECT rejection_stage, COUNT(*) as count 
            FROM complaint_rejections $whereClause
            GROUP BY rejection_stage
        ");
        $stmt->execute($params);
        $stageStats = $stmt->fetchAll();
        
        foreach ($stageStats as $stageStat) {
            $stats['by_stage'][$stageStat['rejection_stage']] = $stageStat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Check if complaint has been rejected at specific stage
     */
    public function hasRejectionAtStage($complaintId, $stage) {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM complaint_rejections 
            WHERE complaint_id = ? AND rejection_stage = ?
        ");
        $stmt->execute([$complaintId, $stage]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get latest rejection for complaint
     */
    public function getLatestRejection($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT cr.*, 
                   u1.name as rejected_by_name,
                   u2.name as rejected_to_name
            FROM complaint_rejections cr
            LEFT JOIN users u1 ON cr.rejected_by = u1.login_id
            LEFT JOIN users u2 ON cr.rejected_to = u2.login_id
            WHERE cr.complaint_id = ?
            ORDER BY cr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetch();
    }
}
?>
