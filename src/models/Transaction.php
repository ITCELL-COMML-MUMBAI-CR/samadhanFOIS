<?php
/**
 * Transaction Model
 * Handles complaint transaction/activity logging
 */

require_once 'BaseModel.php';

class Transaction extends BaseModel {
    protected $table = 'transactions';
    
    /**
     * Create new transaction
     */
    public function createTransaction($data) {
        $data['transaction_id'] = $this->generateTransactionId();
        $data['created_at'] = getCurrentDateTime();
        
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
        
        return $result ? $data['transaction_id'] : false;
    }
    
    /**
     * Get transactions for a complaint
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT t.*, 
                   u1.name as created_by_name,
                   u2.name as from_user_name,
                   u3.name as to_user_name
            FROM transactions t
            LEFT JOIN users u1 ON t.created_by = u1.login_id
            LEFT JOIN users u2 ON t.from_user = u2.login_id
            LEFT JOIN users u3 ON t.to_user = u3.login_id
            WHERE t.complaint_id = ?
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Log complaint forward action
     */
    public function logForward($complaintId, $fromUser, $toUser, $fromDept, $toDept, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'forward',
            'from_user' => $fromUser,
            'to_user' => $toUser,
            'from_department' => $fromDept,
            'to_department' => $toDept,
            'remarks' => $remarks,
            'created_by' => $createdBy
        ]);
    }
    
    /**
     * Log internal remark
     */
    public function logInternalRemark($complaintId, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'internal_remark',
            'remarks' => $remarks,
            'created_by' => $createdBy
        ]);
    }
    
    /**
     * Log status update
     */
    public function logStatusUpdate($complaintId, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'status_update',
            'remarks' => $remarks,
            'created_by' => $createdBy
        ]);
    }
    
    /**
     * Log assignment
     */
    public function logAssignment($complaintId, $fromUser, $toUser, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'assignment',
            'from_user' => $fromUser,
            'to_user' => $toUser,
            'remarks' => $remarks,
            'created_by' => $createdBy
        ]);
    }
    
    /**
     * Get recent transactions
     */
    public function getRecent($limit = 10, $userId = null) {
        $sql = "
            SELECT t.*, 
                   c.complaint_type,
                   u1.name as created_by_name
            FROM transactions t
            LEFT JOIN complaints c ON t.complaint_id = c.complaint_id
            LEFT JOIN users u1 ON t.created_by = u1.login_id
        ";
        
        $params = [];
        
        if ($userId) {
            $sql .= " WHERE (t.from_user = ? OR t.to_user = ? OR t.created_by = ?)";
            $params = [$userId, $userId, $userId];
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get transaction statistics
     */
    public function getStatistics($filters = []) {
        $stats = [];
        
        // Base WHERE clause for filters
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause .= " AND (from_user = ? OR to_user = ? OR created_by = ?)";
            $params[] = $filters['user_id'];
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
        
        // Total transactions
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM transactions $whereClause");
        $stmt->execute($params);
        $stats['total'] = $stmt->fetch()['count'];
        
        // By transaction type
        $stmt = $this->connection->prepare("
            SELECT transaction_type, COUNT(*) as count 
            FROM transactions $whereClause
            GROUP BY transaction_type
        ");
        $stmt->execute($params);
        $typeStats = $stmt->fetchAll();
        
        foreach ($typeStats as $typeStat) {
            $stats['by_type'][$typeStat['transaction_type']] = $typeStat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId() {
        do {
            $id = generateTransactionId();
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM transactions WHERE transaction_id = ?");
            $stmt->execute([$id]);
            $exists = $stmt->fetch()['count'] > 0;
        } while ($exists);
        
        return $id;
    }
    
    /**
     * Get transactions by type
     */
    public function findByType($type, $limit = null) {
        $sql = "
            SELECT t.*, 
                   c.complaint_type,
                   u1.name as created_by_name
            FROM transactions t
            LEFT JOIN complaints c ON t.complaint_id = c.complaint_id
            LEFT JOIN users u1 ON t.created_by = u1.login_id
            WHERE t.transaction_type = ?
            ORDER BY t.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaint history/timeline
     */
    public function getComplaintHistory($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT 
                transaction_id,
                transaction_type,
                remarks,
                created_at,
                created_by,
                from_user,
                to_user,
                from_department,
                to_department,
                (SELECT name FROM users WHERE login_id = t.created_by) as created_by_name,
                (SELECT name FROM users WHERE login_id = t.from_user) as from_user_name,
                (SELECT name FROM users WHERE login_id = t.to_user) as to_user_name
            FROM transactions t
            WHERE complaint_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetchAll();
    }
}
?>
