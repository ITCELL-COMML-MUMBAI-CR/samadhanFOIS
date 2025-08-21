<?php
/**
 * Department Model
 * Handles department information
 */

require_once 'BaseModel.php';

class Department extends BaseModel {
    protected $table = 'departments';
    
    /**
     * Get all departments
     */
    public function getAllDepartments($limit = null) {
        $sql = "SELECT * FROM departments ORDER BY name ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get department by ID
     */
    public function findById($id) {
        return $this->find($id);
    }
    
    /**
     * Find department by code
     */
    public function findByCode($code) {
        $stmt = $this->connection->prepare("SELECT * FROM departments WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }
    
    /**
     * Find department by name
     */
    public function findByName($name) {
        $stmt = $this->connection->prepare("SELECT * FROM departments WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    /**
     * Search departments
     */
    public function search($searchTerm, $limit = null) {
        $sql = "
            SELECT * FROM departments 
            WHERE name LIKE ? OR code LIKE ? OR description LIKE ?
            ORDER BY name ASC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $searchPattern = "%$searchTerm%";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get department statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total departments
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM departments");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Active departments
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM departments 
            WHERE status = 'active'
        ");
        $stmt->execute();
        $stats['active'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Get all department codes
     */
    public function getDepartmentCodes() {
        $stmt = $this->connection->prepare("
            SELECT code, name 
            FROM departments 
            WHERE status = 'active' 
            ORDER BY name ASC
        ");
        $stmt->execute();
        $departments = $stmt->fetchAll();
        
        $result = [];
        foreach ($departments as $dept) {
            $result[$dept['code']] = $dept['name'];
        }
        
        return $result;
    }
    
    /**
     * Get departments as array
     */
    public function getDepartmentsArray() {
        $stmt = $this->connection->prepare("
            SELECT id, name, code 
            FROM departments 
            WHERE status = 'active' 
            ORDER BY name ASC
        ");
        $stmt->execute();
        $departments = $stmt->fetchAll();
        
        $result = [];
        foreach ($departments as $dept) {
            $result[$dept['id']] = [
                'name' => $dept['name'],
                'code' => $dept['code']
            ];
        }
        
        return $result;
    }
    
    /**
     * Create department
     */
    public function createDepartment($data) {
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        
        return $this->create($data);
    }
    
    /**
     * Update department
     */
    public function updateDepartment($id, $data) {
        $data['updated_at'] = getCurrentDateTime();
        return $this->update($id, $data);
    }
    
    /**
     * Delete department
     */
    public function deleteDepartment($id) {
        return $this->delete($id);
    }
    
    /**
     * Check if department code exists
     */
    public function codeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM departments WHERE code = ?";
        $params = [$code];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Check if department name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM departments WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }
}
