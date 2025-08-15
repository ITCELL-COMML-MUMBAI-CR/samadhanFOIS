<?php
/**
 * Email Template Model
 * Handles database operations for email templates
 */

require_once __DIR__ . '/BaseModel.php';

class EmailTemplate extends BaseModel {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'email_templates';
    }
    
    /**
     * Create a new email template
     */
    public function create($data) {
        $stmt = $this->connection->prepare("
            INSERT INTO email_templates 
            (name, category, subject, content, description, is_default, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $data['name'],
            $data['category'],
            $data['subject'],
            $data['content'],
            $data['description'],
            $data['is_default'],
            $data['created_by']
        ]);
        
        if ($result) {
            return $this->connection->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update an email template
     */
    public function update($id, $data) {
        $stmt = $this->connection->prepare("
            UPDATE email_templates 
            SET name = ?, category = ?, subject = ?, content = ?, description = ?, 
                is_default = ?, updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['category'],
            $data['subject'],
            $data['content'],
            $data['description'],
            $data['is_default'],
            $data['updated_by'],
            $id
        ]);
    }
    
    /**
     * Delete an email template
     */
    public function delete($id) {
        $stmt = $this->connection->prepare("DELETE FROM email_templates WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Find template by ID
     */
    public function findById($id) {
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all templates
     */
    public function getAll() {
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            ORDER BY category ASC, name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get templates by category
     */
    public function getByCategory($category) {
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            WHERE category = ? 
            ORDER BY is_default DESC, name ASC
        ");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get default template for a category
     */
    public function getDefaultByCategory($category) {
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            WHERE category = ? AND is_default = 1 
            LIMIT 1
        ");
        $stmt->execute([$category]);
        return $stmt->fetch();
    }
    
    /**
     * Unset default for a category (except specified template)
     */
    public function unsetDefaultForCategory($category, $excludeId = null) {
        $sql = "UPDATE email_templates SET is_default = 0 WHERE category = ?";
        $params = [$category];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Search templates
     */
    public function search($query) {
        $searchTerm = "%{$query}%";
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            WHERE name LIKE ? OR subject LIKE ? OR content LIKE ? OR description LIKE ?
            ORDER BY category ASC, name ASC
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get template statistics
     */
    public function getStats() {
        $stmt = $this->connection->prepare("
            SELECT 
                COUNT(*) as total_templates,
                COUNT(CASE WHEN is_default = 1 THEN 1 END) as default_templates,
                COUNT(DISTINCT category) as categories
            FROM email_templates
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Get templates with usage count
     */
    public function getWithUsageCount() {
        $stmt = $this->connection->prepare("
            SELECT 
                et.*,
                COALESCE(usage_count.count, 0) as usage_count
            FROM email_templates et
            LEFT JOIN (
                SELECT 
                    template_id,
                    COUNT(*) as count
                FROM bulk_email_logs
                GROUP BY template_id
            ) usage_count ON et.id = usage_count.template_id
            ORDER BY et.category ASC, et.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Check if template name exists
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM email_templates WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get recent templates
     */
    public function getRecent($limit = 5) {
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            ORDER BY updated_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get templates by creator
     */
    public function getByCreator($creatorId) {
        $stmt = $this->connection->prepare("
            SELECT * FROM email_templates 
            WHERE created_by = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$creatorId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Duplicate a template
     */
    public function duplicate($id, $newName, $creatorId) {
        $original = $this->findById($id);
        if (!$original) {
            return false;
        }
        
        $data = [
            'name' => $newName,
            'category' => $original['category'],
            'subject' => $original['subject'],
            'content' => $original['content'],
            'description' => $original['description'] . ' (Copy)',
            'is_default' => 0, // Duplicates are never default
            'created_by' => $creatorId
        ];
        
        return $this->create($data);
    }
}
