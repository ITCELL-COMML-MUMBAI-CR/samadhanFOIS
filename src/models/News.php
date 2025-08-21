<?php
/**
 * News Model
 * Handles news, announcements, and advertisements
 */

require_once 'BaseModel.php';

class News extends BaseModel {
    protected $table = 'news';
    
    /**
     * Create new news item
     */
    public function createNews($data) {
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        
        return $this->create($data);
    }
    
    /**
     * Update news item
     */
    public function updateNews($id, $data) {
        $data['updated_at'] = getCurrentDateTime();
        return $this->update($id, $data);
    }
    
    /**
     * Get news by ID
     */
    public function findById($id) {
        return $this->find($id);
    }
    
    /**
     * Get active news items
     */
    public function getActive($limit = null) {
        $sql = "SELECT * FROM news WHERE status = 'active' ORDER BY priority DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured news items
     */
    public function getFeatured($limit = null) {
        $sql = "SELECT * FROM news WHERE status = 'active' AND featured = 1 ORDER BY priority DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get marquee news items
     */
    public function getMarquee($limit = null) {
        $sql = "SELECT * FROM news WHERE status = 'active' AND show_in_marquee = 1 ORDER BY priority DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get marquee items for display
     */
    public function getMarqueeItems($limit = 5) {
        $sql = "
            SELECT id, title, content, created_at 
            FROM news 
            WHERE show_in_marquee = 1 AND status = 'active' 
            ORDER BY created_at DESC 
            LIMIT ?
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get news by type
     */
    public function findByType($type, $limit = null) {
        $sql = "SELECT * FROM news WHERE status = 'active' AND type = ? ORDER BY priority DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get news by status
     */
    public function findByStatus($status, $limit = null) {
        $sql = "SELECT * FROM news WHERE status = ? ORDER BY priority DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    /**
     * Search news
     */
    public function search($searchTerm, $limit = null) {
        $sql = "
            SELECT * FROM news 
            WHERE (title LIKE ? OR content LIKE ?) AND status = 'active'
            ORDER BY priority DESC, created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $searchPattern = "%$searchTerm%";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern]);
        return $stmt->fetchAll();
    }
    
    /**
     * Increment view count
     */
    public function incrementViews($id) {
        $stmt = $this->connection->prepare("
            UPDATE news 
            SET views = views + 1, updated_at = ? 
            WHERE id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $id]);
    }
    
    /**
     * Get news statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total news items
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM news");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // News by type
        $stmt = $this->connection->prepare("
            SELECT type, COUNT(*) as count 
            FROM news 
            GROUP BY type
        ");
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll();
        
        // News by status
        $stmt = $this->connection->prepare("
            SELECT status, COUNT(*) as count 
            FROM news 
            GROUP BY status
        ");
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll();
        
        // Featured news count
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM news 
            WHERE featured = 1 AND status = 'active'
        ");
        $stmt->execute();
        $stats['featured'] = $stmt->fetch()['count'];
        
        // Marquee news count
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM news 
            WHERE show_in_marquee = 1 AND status = 'active'
        ");
        $stmt->execute();
        $stats['marquee'] = $stmt->fetch()['count'];
        
        // Total views
        $stmt = $this->connection->prepare("SELECT SUM(views) as total_views FROM news");
        $stmt->execute();
        $stats['total_views'] = $stmt->fetch()['total_views'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get recent news
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM news WHERE status = 'active' ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get popular news (by views)
     */
    public function getPopular($limit = 10) {
        $sql = "SELECT * FROM news WHERE status = 'active' ORDER BY views DESC, created_at DESC LIMIT ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Archive news item
     */
    public function archive($id) {
        $stmt = $this->connection->prepare("
            UPDATE news 
            SET status = 'archived', updated_at = ? 
            WHERE id = ?
        ");
        return $stmt->execute([getCurrentDateTime(), $id]);
    }
    
    /**
     * Delete news item
     */
    public function deleteNews($id) {
        return $this->delete($id);
    }
}
?>
