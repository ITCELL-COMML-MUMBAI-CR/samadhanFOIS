<?php
/**
 * Complaint Model
 * Handles complaint operations
 */

require_once 'BaseModel.php';

class Complaint extends BaseModel {
    protected $table = 'complaints';
    
    /**
     * Create new complaint
     */
    public function createComplaint($data) {
        // Generate unique complaint ID
        $data['complaint_id'] = $this->generateComplaintId();
        $data['date'] = getCurrentDate();
        $data['time'] = getCurrentTime();
        $data['created_at'] = getCurrentDateTime();
        $data['status'] = 'pending';
        $data['priority'] = 'normal'; // Default priority is now 'normal'
        $data['department'] = $data['department'] ?? 'COMMERCIAL';
        // Default assignment to Commercial Controller if not explicitly provided
        if (empty($data['assigned_to'])) {
            $data['assigned_to'] = 'commercial_controller';
        }
        
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
        
        return $result ? $data['complaint_id'] : false;
    }
    
    /**
     * Find complaint by complaint ID
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT c.*, u.name as customer_name, u.email as customer_email,
                   a.name as assigned_to_name
            FROM complaints c
            LEFT JOIN users u ON c.customer_id = u.customer_id
            LEFT JOIN users a ON c.assigned_to = a.login_id
            WHERE c.complaint_id = ?
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetch();
    }
    
    /**
     * Get complaints by customer
     */
    public function findByCustomer($customerId, $limit = null) {
        $sql = "
            SELECT c.*, u.name as customer_name 
            FROM complaints c
            LEFT JOIN users u ON c.customer_id = u.customer_id
            WHERE c.customer_id = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaints by status
     */
    public function findByStatus($status, $limit = null) {
        $sql = "
            SELECT c.*, u.name as customer_name 
            FROM complaints c
            LEFT JOIN users u ON c.customer_id = u.customer_id
            WHERE c.status = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaints assigned to user
     */
    public function findAssignedTo($loginId, $limit = null) {
        $sql = "
            SELECT c.*, u.name as customer_name 
            FROM complaints c
            LEFT JOIN users u ON c.customer_id = u.customer_id
            WHERE c.assigned_to = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$loginId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update complaint status
     */
    public function updateStatus($complaintId, $status, $actionTaken = null) {
        $sql = "UPDATE complaints SET status = ?, updated_at = ?";
        $params = [$status, getCurrentDateTime()];
        
        if ($actionTaken !== null) {
            $sql .= ", action_taken = ?";
            $params[] = $actionTaken;
        }
        
        $sql .= " WHERE complaint_id = ?";
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Assign complaint to user
     */
    public function assignTo($complaintId, $loginId) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET assigned_to = ?, updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$loginId, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Update priority
     */
    public function updatePriority($complaintId, $priority) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET priority = ?, updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$priority, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Calculate automatic priority based on complaint age
     * Rules: normal -> 1 hour -> medium -> 3 hours -> high -> 1 day -> critical
     */
    public function calculateAutoPriority($createdAt) {
        $now = time();
        $created = strtotime($createdAt);
        $ageInHours = ($now - $created) / 3600; // Convert to hours
        
        if ($ageInHours >= 24) { // 1 day or more
            return 'critical';
        } elseif ($ageInHours >= 3) { // 3 hours or more
            return 'high';
        } elseif ($ageInHours >= 1) { // 1 hour or more
            return 'medium';
        } else {
            return 'normal'; // Less than 1 hour
        }
    }
    
    /**
     * Update priorities for all pending complaints based on age
     * This should be called periodically (via cron job or on page load)
     */
    public function updateAutoPriorities() {
        try {
            // Get all pending complaints that might need priority updates
            $stmt = $this->connection->prepare("
                SELECT complaint_id, created_at, priority 
                FROM complaints 
                WHERE status IN ('pending', 'assigned', 'replied') 
                ORDER BY created_at ASC
            ");
            $stmt->execute();
            $complaints = $stmt->fetchAll();
            
            $updatedCount = 0;
            foreach ($complaints as $complaint) {
                $currentPriority = $complaint['priority'];
                $autoPriority = $this->calculateAutoPriority($complaint['created_at']);
                
                // Only update if auto-calculated priority is higher than current
                $priorityLevels = ['normal' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
                $currentLevel = $priorityLevels[$currentPriority] ?? 1;
                $autoLevel = $priorityLevels[$autoPriority] ?? 1;
                
                if ($autoLevel > $currentLevel) {
                    $this->updatePriority($complaint['complaint_id'], $autoPriority);
                    $updatedCount++;
                }
            }
            
            return $updatedCount;
        } catch (Exception $e) {
            error_log('Auto priority update error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get complaint with calculated auto priority
     */
    public function findByComplaintIdWithAutoPriority($complaintId) {
        $complaint = $this->findByComplaintId($complaintId);
        if ($complaint) {
            $complaint['auto_priority'] = $this->calculateAutoPriority($complaint['created_at']);
            $complaint['display_priority'] = $complaint['auto_priority']; // Use auto-calculated priority for display
        }
        return $complaint;
    }
    
    /**
     * Search complaints
     */
    public function search($searchTerm, $filters = []) {
        $sql = "
            SELECT c.*, u.name as customer_name 
            FROM complaints c
            LEFT JOIN users u ON c.customer_id = u.customer_id
            WHERE (
                c.complaint_id LIKE ? OR 
                c.complaint_type LIKE ? OR 
                c.description LIKE ? OR 
                c.location LIKE ? OR
                u.name LIKE ?
            )
        ";
        
        $searchParam = "%$searchTerm%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
        
        // Add filters
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND c.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['department'])) {
            $sql .= " AND c.department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND c.date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND c.date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaint statistics
     */
    public function getStatistics($filters = []) {
        $stats = [];
        
        // Base WHERE clause for filters
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['customer_id'])) {
            $whereClause .= " AND customer_id = ?";
            $params[] = $filters['customer_id'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $whereClause .= " AND assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND date <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Total complaints
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM complaints $whereClause");
        $stmt->execute($params);
        $stats['total'] = $stmt->fetch()['count'];
        
        // By status
        $stmt = $this->connection->prepare("
            SELECT status, COUNT(*) as count 
            FROM complaints $whereClause
            GROUP BY status
        ");
        $stmt->execute($params);
        $statusStats = $stmt->fetchAll();
        
        foreach ($statusStats as $statusStat) {
            $stats['by_status'][$statusStat['status']] = $statusStat['count'];
        }
        
        // By priority
        $stmt = $this->connection->prepare("
            SELECT priority, COUNT(*) as count 
            FROM complaints $whereClause
            GROUP BY priority
        ");
        $stmt->execute($params);
        $priorityStats = $stmt->fetchAll();
        
        foreach ($priorityStats as $priorityStat) {
            $stats['by_priority'][$priorityStat['priority']] = $priorityStat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Generate unique complaint ID
     */
    private function generateComplaintId() {
        do {
            $id = generateComplaintId();
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM complaints WHERE complaint_id = ?");
            $stmt->execute([$id]);
            $exists = $stmt->fetch()['count'] > 0;
        } while ($exists);
        
        return $id;
    }
    
    /**
     * Get recent complaints
     */
    public function getRecent($limit = 10) {
        $stmt = $this->connection->prepare("
            SELECT c.*, u.name as customer_name 
            FROM complaints c
            LEFT JOIN users u ON c.customer_id = u.customer_id
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update complaint
     */
    public function updateComplaint($complaintId, $data) {
        $columns = array_keys($data);
        $setClause = array_map(function($column) {
            return "$column = ?";
        }, $columns);
        
        $sql = "UPDATE complaints SET " . implode(', ', $setClause) . " WHERE complaint_id = ?";
        
        $params = array_values($data);
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Assign complaint to user
     */
    public function assignComplaint($complaintId, $assignedTo) {
        $sql = "UPDATE complaints SET assigned_to = ?, updated_at = ? WHERE complaint_id = ?";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([$assignedTo, getCurrentDateTime(), $complaintId]);
    }
}
?>
