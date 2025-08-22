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
     * Update user profile
     */
    public function updateProfile($loginId, $data) {
        $data['updated_at'] = getCurrentDateTime();
        
        $columns = array_keys($data);
        $setClause = array_map(function($column) {
            return "$column = ?";
        }, $columns);
        
        $sql = "UPDATE users SET " . implode(', ', $setClause) . " WHERE login_id = ?";
        
        $params = array_values($data);
        $params[] = $loginId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update user (alias for updateProfile)
     */
    public function updateUser($loginId, $data) {
        return $this->updateProfile($loginId, $data);
    }
    
    /**
     * Get all users with optional filters
     */
    public function getAllUsers($filters = [], $limit = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
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
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role, $limit = null) {
        $sql = "SELECT * FROM users WHERE role = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get users by department
     */
    public function getByDepartment($department, $limit = null) {
        $sql = "SELECT * FROM users WHERE department = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$department]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get users by department, division, and zone
     */
    public function getByDepartmentAndDivision($department, $division = null, $zone = null, $limit = null) {
        $sql = "SELECT * FROM users WHERE department = ?";
        $params = [$department];
        
        if ($division) {
            $sql .= " AND Division = ?";
            $params[] = $division;
        }
        
        if ($zone) {
            $sql .= " AND Zone = ?";
            $params[] = $zone;
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
     * Get commercial controllers by division and zone
     */
    public function getCommercialControllersByDivision($division, $zone = null) {
        return $this->getByDepartmentAndDivision('COMMERCIAL', $division, $zone);
    }
    
    /**
     * Search users
     */
    public function search($searchTerm, $filters = [], $limit = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        // Add search conditions
        if (!empty($searchTerm)) {
            $sql .= " AND (login_id LIKE ? OR name LIKE ? OR email LIKE ?)";
            $searchPattern = "%$searchTerm%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $sql .= " AND $key = ?";
                    $params[] = $value;
                }
            }
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
     * Get user statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total users
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Users by role
        $stmt = $this->connection->prepare("
            SELECT role, COUNT(*) as count 
            FROM users 
            GROUP BY role
        ");
        $stmt->execute();
        $stats['by_role'] = $stmt->fetchAll();
        
        // Users by department
        $stmt = $this->connection->prepare("
            SELECT department, COUNT(*) as count 
            FROM users 
            WHERE department IS NOT NULL
            GROUP BY department
        ");
        $stmt->execute();
        $stats['by_department'] = $stmt->fetchAll();
        
        // Users by status
        $stmt = $this->connection->prepare("
            SELECT status, COUNT(*) as count 
            FROM users 
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll();
        
        // Recent users (last 30 days)
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $stats['recent_30_days'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Check if login ID exists
     */
    public function loginIdExists($loginId) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM users WHERE login_id = ?");
        $stmt->execute([$loginId]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeLoginId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeLoginId) {
            $sql .= " AND login_id != ?";
            $params[] = $excludeLoginId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Deactivate user
     */
    public function deactivateUser($loginId) {
        $stmt = $this->connection->prepare("
            UPDATE users 
            SET status = 'inactive', updated_at = ? 
            WHERE login_id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $loginId]);
    }
    
    /**
     * Activate user
     */
    public function activateUser($loginId) {
        $stmt = $this->connection->prepare("
            UPDATE users 
            SET status = 'active', updated_at = ? 
            WHERE login_id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $loginId]);
    }
    
    /**
     * Delete user
     */
    public function deleteUser($loginId) {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE login_id = ?");
        return $stmt->execute([$loginId]);
    }
    
    /**
     * Delete user by login ID (alias for deleteUser)
     */
    public function deleteByLoginId($loginId) {
        return $this->deleteUser($loginId);
    }
    
    /**
     * Get users grouped by department
     */
    public function getUsersByDepartment() {
        $stmt = $this->connection->prepare("
            SELECT department, login_id, name, email, role, status
            FROM users 
            WHERE department IS NOT NULL AND status = 'active'
            ORDER BY department ASC, name ASC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $grouped = [];
        foreach ($users as $user) {
            $department = $user['department'];
            if (!isset($grouped[$department])) {
                $grouped[$department] = [];
            }
            $grouped[$department][] = $user;
        }
        
        return $grouped;
    }
}
?>
