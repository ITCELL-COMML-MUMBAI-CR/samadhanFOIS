<?php
/**
 * User Model
 * Handles user authentication and management
 */

require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    
    /**
     * Authenticate user
     */
    public function authenticate($loginId, $password) {
        $stmt = $this->connection->prepare("
            SELECT * FROM users 
            WHERE login_id = ? AND status = 'active'
        ");
        $stmt->execute([$loginId]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login time if needed
            $this->updateLastLogin($user['login_id']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Set default values
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        $data['status'] = $data['status'] ?? 'active';
        
        return $this->createWithoutId($data);
    }
    
    /**
     * Create record without auto-increment ID
     */
    private function createWithoutId($data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute(array_values($data));
        
        return $result ? $data['login_id'] : false;
    }
    
    /**
     * Find user by login ID
     */
    public function findByLoginId($loginId) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE login_id = ?");
        $stmt->execute([$loginId]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by customer ID
     */
    public function findByCustomerId($customerId) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
    
    /**
     * Update user password
     */
    public function updatePassword($loginId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("
            UPDATE users 
            SET password = ?, updated_at = ? 
            WHERE login_id = ?
        ");
        return $stmt->execute([$hashedPassword, getCurrentDateTime(), $loginId]);
    }
    
    /**
     * Update last login time
     */
    private function updateLastLogin($loginId) {
        $stmt = $this->connection->prepare("
            UPDATE users 
            SET updated_at = ? 
            WHERE login_id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $loginId]);
    }
    
    /**
     * Get users by role
     */
    public function findByRole($role) {
        $stmt = $this->connection->prepare("
            SELECT * FROM users 
            WHERE role = ? AND status = 'active'
            ORDER BY name ASC
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get users by department
     */
    public function findByDepartment($department) {
        $stmt = $this->connection->prepare("
            SELECT * FROM users 
            WHERE department = ? AND status = 'active'
            ORDER BY name ASC
        ");
        $stmt->execute([$department]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if login ID exists
     */
    public function loginIdExists($loginId) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM users WHERE login_id = ?");
        $stmt->execute([$loginId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Update user status
     */
    public function updateStatus($loginId, $status) {
        $stmt = $this->connection->prepare("
            UPDATE users 
            SET status = ?, updated_at = ? 
            WHERE login_id = ?
        ");
        return $stmt->execute([$status, getCurrentDateTime(), $loginId]);
    }
    
    /**
     * Update user fields (except password). Allows changing login_id.
     */
    public function updateUser($originalLoginId, $data) {
        $allowed = ['login_id','name','email','mobile','role','department','customer_id','status'];
        $setParts = [];
        $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        $setParts[] = 'updated_at = ?';
        $params[] = getCurrentDateTime();
        if (empty($setParts)) {
            return false;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE login_id = ?';
        $params[] = $originalLoginId;
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete user by login id
     */
    public function deleteByLoginId($loginId) {
        $stmt = $this->connection->prepare('DELETE FROM users WHERE login_id = ?');
        return $stmt->execute([$loginId]);
    }
    
    /**
     * Search users with optional filters
     */
    public function search($searchTerm = '', $filters = []) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        if ($searchTerm !== '') {
            $sql .= " AND (login_id LIKE ? OR name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
            $like = "%$searchTerm%";
            array_push($params, $like, $like, $like, $like);
        }
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        if (!empty($filters['department'])) {
            $sql .= " AND department = ?";
            $params[] = $filters['department'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        $sql .= " ORDER BY updated_at DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total users
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Active users
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $stmt->execute();
        $stats['active'] = $stmt->fetch()['count'];
        
        // Users by role
        $stmt = $this->connection->prepare("
            SELECT role, COUNT(*) as count 
            FROM users 
            WHERE status = 'active' 
            GROUP BY role
        ");
        $stmt->execute();
        $roleStats = $stmt->fetchAll();
        
        foreach ($roleStats as $roleStat) {
            $stats['by_role'][$roleStat['role']] = $roleStat['count'];
        }
        
        return $stats;
    }
}
?>
