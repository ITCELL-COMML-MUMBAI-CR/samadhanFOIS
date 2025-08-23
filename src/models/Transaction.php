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
        
        // Default creator type to 'user' if not specified
        if (!isset($data['created_by_type'])) {
            $data['created_by_type'] = 'user';
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
        
        return $result ? $data['transaction_id'] : false;
    }
    
    /**
     * Get transactions for a complaint, joining with both users and customers.
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT t.*, 
                   COALESCE(u.name, c.Name) as created_by_name,
                   u2.name as from_user_name,
                   u3.name as to_user_name
            FROM transactions t
            LEFT JOIN users u ON t.created_by = u.login_id AND t.created_by_type = 'user'
            LEFT JOIN customers c ON t.created_by = c.CustomerID AND t.created_by_type = 'customer'
            LEFT JOIN users u2 ON t.from_user = u2.login_id
            LEFT JOIN users u3 ON t.to_user = u3.login_id
            WHERE t.complaint_id = ?
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Log complaint forward action (by a user)
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
            'created_by' => $createdBy,
            'created_by_type' => 'user'
        ]);
    }
    
    /**
     * Log internal remark (by a user)
     */
    public function logInternalRemark($complaintId, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'internal_remark',
            'remarks' => $remarks,
            'created_by' => $createdBy,
            'created_by_type' => 'user'
        ]);
    }
    
    /**
     * Log status update (by a user)
     */
    public function logStatusUpdate($complaintId, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'status_update',
            'remarks' => $remarks,
            'created_by' => $createdBy,
            'created_by_type' => 'user'
        ]);
    }
    
    /**
     * Log assignment (by a user)
     */
    public function logAssignment($complaintId, $fromUser, $toUser, $remarks, $createdBy) {
        return $this->createTransaction([
            'complaint_id' => $complaintId,
            'transaction_type' => 'assignment',
            'from_user' => $fromUser,
            'to_user' => $toUser,
            'remarks' => $remarks,
            'created_by' => $createdBy,
            'created_by_type' => 'user'
        ]);
    }
    
    /**
     * Get recent transactions
     */
    public function getRecent($limit = 10) {
        $stmt = $this->connection->prepare("
            SELECT t.*, 
                   COALESCE(u.name, c.Name) as created_by_name,
                   u2.name as from_user_name,
                   u3.name as to_user_name
            FROM transactions t
            LEFT JOIN users u ON t.created_by = u.login_id AND t.created_by_type = 'user'
            LEFT JOIN customers c ON t.created_by = c.CustomerID AND t.created_by_type = 'customer'
            LEFT JOIN users u2 ON t.from_user = u2.login_id
            LEFT JOIN users u3 ON t.to_user = u3.login_id
            ORDER BY t.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get transactions by type
     */
    public function findByType($type, $limit = null) {
        $sql = "
            SELECT t.*, 
                   COALESCE(u.name, c.Name) as created_by_name,
                   u2.name as from_user_name,
                   u3.name as to_user_name
            FROM transactions t
            LEFT JOIN users u ON t.created_by = u.login_id AND t.created_by_type = 'user'
            LEFT JOIN customers c ON t.created_by = c.CustomerID AND t.created_by_type = 'customer'
            LEFT JOIN users u2 ON t.from_user = u2.login_id
            LEFT JOIN users u3 ON t.to_user = u3.login_id
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
     * Get transactions by user
     */
    public function findByUser($userId, $limit = null) {
        $sql = "
            SELECT t.*, 
                   COALESCE(u.name, c.Name) as created_by_name,
                   u2.name as from_user_name,
                   u3.name as to_user_name
            FROM transactions t
            LEFT JOIN users u ON t.created_by = u.login_id AND t.created_by_type = 'user'
            LEFT JOIN customers c ON t.created_by = c.CustomerID AND t.created_by_type = 'customer'
            LEFT JOIN users u2 ON t.from_user = u2.login_id
            LEFT JOIN users u3 ON t.to_user = u3.login_id
            WHERE (t.created_by = ? AND t.created_by_type = 'user') OR t.from_user = ? OR t.to_user = ?
            ORDER BY t.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get transaction statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total transactions
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM transactions");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Transactions by type
        $stmt = $this->connection->prepare("
            SELECT transaction_type, COUNT(*) as count 
            FROM transactions 
            GROUP BY transaction_type
        ");
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll();
        
        // Recent transactions (last 7 days)
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM transactions 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $stats['recent_7_days'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId() {
        $prefix = 'TXN';
        $date = date('Ymd');
        $time = date('His');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $date . $time . $random;
    }
    
    /**
     * Get complaint history
     */
    public function getComplaintHistory($complaintId) {
        return $this->findByComplaintId($complaintId);
    }
}
?>
