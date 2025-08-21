<?php
/**
 * QuickLink Model
 * Handles quick links and external resources
 */

require_once 'BaseModel.php';

class QuickLink extends BaseModel {
    protected $table = 'quick_links';
    
    /**
     * Create new quick link
     */
    public function createQuickLink($data) {
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        
        return $this->create($data);
    }
    
    /**
     * Update quick link
     */
    public function updateQuickLink($id, $data) {
        $data['updated_at'] = getCurrentDateTime();
        return $this->update($id, $data);
    }
    
    /**
     * Get quick link by ID
     */
    public function findById($id) {
        return $this->find($id);
    }
    
    /**
     * Get active quick links
     */
    public function getActive($limit = null) {
        $sql = "SELECT * FROM quick_links WHERE status = 'active' ORDER BY position ASC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get quick links by category
     */
    public function findByCategory($category, $limit = null) {
        $sql = "SELECT * FROM quick_links WHERE status = 'active' AND category = ? ORDER BY position ASC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get quick links by position
     */
    public function findByPosition($position, $limit = null) {
        $sql = "SELECT * FROM quick_links WHERE status = 'active' AND position = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$position]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get quick links by target
     */
    public function findByTarget($target, $limit = null) {
        $sql = "SELECT * FROM quick_links WHERE status = 'active' AND target = ? ORDER BY position ASC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$target]);
        return $stmt->fetchAll();
    }
    
    /**
     * Search quick links
     */
    public function search($searchTerm, $limit = null) {
        $sql = "
            SELECT * FROM quick_links 
            WHERE (title LIKE ? OR description LIKE ? OR url LIKE ?) AND status = 'active'
            ORDER BY position ASC, created_at DESC
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
     * Get quick link statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total quick links
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM quick_links");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Quick links by category
        $stmt = $this->connection->prepare("
            SELECT category, COUNT(*) as count 
            FROM quick_links 
            GROUP BY category
        ");
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll();
        
        // Quick links by status
        $stmt = $this->connection->prepare("
            SELECT status, COUNT(*) as count 
            FROM quick_links 
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll();
        
        // Quick links by target
        $stmt = $this->connection->prepare("
            SELECT target, COUNT(*) as count 
            FROM quick_links 
            GROUP BY target
        ");
        $stmt->execute();
        $stats['by_target'] = $stmt->fetchAll();
        
        // Active quick links count
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM quick_links 
            WHERE status = 'active'
        ");
        $stmt->execute();
        $stats['active'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT category 
            FROM quick_links 
            ORDER BY category ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get all positions
     */
    public function getPositions() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT position 
            FROM quick_links 
            ORDER BY position ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Update position
     */
    public function updatePosition($id, $position) {
        $stmt = $this->connection->prepare("
            UPDATE quick_links 
            SET position = ?, updated_at = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$position, getCurrentDateTime(), $id]);
    }
    
    /**
     * Activate quick link
     */
    public function activate($id) {
        $stmt = $this->connection->prepare("
            UPDATE quick_links 
            SET status = 'active', updated_at = ? 
            WHERE id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $id]);
    }
    
    /**
     * Deactivate quick link
     */
    public function deactivate($id) {
        $stmt = $this->connection->prepare("
            UPDATE quick_links 
            SET status = 'inactive', updated_at = ? 
            WHERE id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $id]);
    }
    
    /**
     * Delete quick link
     */
    public function deleteQuickLink($id) {
        return $this->delete($id);
    }
}
?>
