<?php
/**
 * News Model
 * Handles news and announcements for the customer home page
 */

require_once __DIR__ . '/BaseModel.php';

class News extends BaseModel {
    protected $table = 'news';
    
    /**
     * Get all active news items with pagination
     */
    public function getActiveNews($limit = 10, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                AND (publish_date IS NULL OR publish_date <= NOW()) 
                AND (expire_date IS NULL OR expire_date >= NOW()) 
                ORDER BY priority DESC, created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get news by type (news, advertisement, announcement)
     */
    public function getNewsByType($type, $limit = 5) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                AND type = ?
                AND (publish_date IS NULL OR publish_date <= NOW()) 
                AND (expire_date IS NULL OR expire_date >= NOW()) 
                ORDER BY priority DESC, created_at DESC 
                LIMIT ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$type, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get marquee text items
     */
    public function getMarqueeItems() {
        $sql = "SELECT title, content FROM {$this->table} 
                WHERE status = 'active' 
                AND show_in_marquee = 1
                AND (publish_date IS NULL OR publish_date <= NOW()) 
                AND (expire_date IS NULL OR expire_date >= NOW()) 
                ORDER BY priority DESC, created_at DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured items for cards
     */
    public function getFeaturedItems($limit = 6) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                AND featured = 1
                AND (publish_date IS NULL OR publish_date <= NOW()) 
                AND (expire_date IS NULL OR expire_date >= NOW()) 
                ORDER BY priority DESC, created_at DESC 
                LIMIT ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create news item with validation
     */
    public function createNews($data) {
        // Set defaults
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        
        // Validate required fields
        if (empty($data['title']) || empty($data['content'])) {
            throw new Exception('Title and content are required');
        }
        
        // Set default values if not provided
        $data['status'] = $data['status'] ?? 'active';
        $data['type'] = $data['type'] ?? 'news';
        $data['priority'] = $data['priority'] ?? 0;
        $data['featured'] = $data['featured'] ?? 0;
        $data['show_in_marquee'] = $data['show_in_marquee'] ?? 0;
        
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
     * Get news statistics for admin
     */
    public function getStatistics() {
        $stats = [];
        
        // Total news
        $stats['total'] = $this->count();
        
        // Active news
        $stats['active'] = $this->count(['status' => 'active']);
        
        // Featured news
        $stats['featured'] = $this->count(['featured' => 1]);
        
        // News by type
        $types = ['news', 'advertisement', 'announcement'];
        foreach ($types as $type) {
            $stats['by_type'][$type] = $this->count(['type' => $type, 'status' => 'active']);
        }
        
        return $stats;
    }
    
    /**
     * Archive expired news
     */
    public function archiveExpiredNews() {
        $sql = "UPDATE {$this->table} 
                SET status = 'archived', updated_at = NOW() 
                WHERE status = 'active' 
                AND expire_date IS NOT NULL 
                AND expire_date < NOW()";
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute();
    }
}
?>
