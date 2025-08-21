<?php
/**
 * EmailTemplate Model
 * Handles email template management
 */

require_once 'BaseModel.php';

class EmailTemplate extends BaseModel {
    protected $table = 'email_templates';
    
    /**
     * Create new email template
     */
    public function createTemplate($data) {
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        
        return $this->create($data);
    }
    
    /**
     * Update email template
     */
    public function updateTemplate($id, $data) {
        $data['updated_at'] = getCurrentDateTime();
        return $this->update($id, $data);
    }
    
    /**
     * Get template by ID
     */
    public function findById($id) {
        return $this->find($id);
    }
    
    /**
     * Get templates by category
     */
    public function findByCategory($category, $limit = null) {
        $sql = "SELECT * FROM email_templates WHERE category = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get default templates
     */
    public function getDefaultTemplates($limit = null) {
        $sql = "SELECT * FROM email_templates WHERE is_default = 1 ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get custom templates
     */
    public function getCustomTemplates($limit = null) {
        $sql = "SELECT * FROM email_templates WHERE is_default = 0 ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Search templates
     */
    public function search($searchTerm, $limit = null) {
        $sql = "
            SELECT * FROM email_templates 
            WHERE name LIKE ? 
               OR subject LIKE ? 
               OR description LIKE ?
            ORDER BY created_at DESC
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
     * Get template statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total templates
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM email_templates");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Templates by category
        $stmt = $this->connection->prepare("
            SELECT category, COUNT(*) as count 
            FROM email_templates 
            GROUP BY category
        ");
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll();
        
        // Default vs custom templates
        $stmt = $this->connection->prepare("
            SELECT is_default, COUNT(*) as count 
            FROM email_templates 
            GROUP BY is_default
        ");
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll();
        
        // Recent templates (last 30 days)
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM email_templates 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $stats['recent_30_days'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT category 
            FROM email_templates 
            ORDER BY category ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Check if template name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM email_templates WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Delete template
     */
    public function deleteTemplate($id) {
        return $this->delete($id);
    }
}
