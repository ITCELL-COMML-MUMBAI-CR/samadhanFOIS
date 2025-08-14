<?php
/**
 * QuickLink Model
 * Handles quick links management for the customer home page
 */

require_once __DIR__ . '/BaseModel.php';

class QuickLink extends BaseModel {
    protected $table = 'quick_links';
    
    /**
     * Get all active quick links ordered by position
     */
    public function getActiveLinks() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                ORDER BY position ASC, created_at ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get quick links by category
     */
    public function getLinksByCategory($category) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                AND category = ?
                ORDER BY position ASC, created_at ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create quick link with validation
     */
    public function createLink($data) {
        // Set defaults
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        
        // Validate required fields
        if (empty($data['title']) || empty($data['url'])) {
            throw new Exception('Title and URL are required');
        }
        
        // Set default values if not provided
        $data['status'] = $data['status'] ?? 'active';
        $data['category'] = $data['category'] ?? 'system';
        $data['position'] = $data['position'] ?? 0;
        $data['target'] = $data['target'] ?? '_self';
        $data['icon_type'] = $data['icon_type'] ?? 'fontawesome';
        
        // If position is 0, auto-assign next position
        if ($data['position'] == 0) {
            $data['position'] = $this->getNextPosition($data['category']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update quick link
     */
    public function updateLink($id, $data) {
        $data['updated_at'] = getCurrentDateTime();
        return $this->update($id, $data);
    }
    
    /**
     * Get next position for a category
     */
    private function getNextPosition($category) {
        $sql = "SELECT MAX(position) as max_pos FROM {$this->table} WHERE category = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$category]);
        $result = $stmt->fetch();
        
        return ($result['max_pos'] ?? 0) + 1;
    }
    
    /**
     * Update link positions
     */
    public function updatePositions($linkPositions) {
        $this->beginTransaction();
        
        try {
            foreach ($linkPositions as $linkId => $position) {
                $this->update($linkId, ['position' => $position, 'updated_at' => getCurrentDateTime()]);
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get quick link statistics for admin
     */
    public function getStatistics() {
        $stats = [];
        
        // Total links
        $stats['total'] = $this->count();
        
        // Active links
        $stats['active'] = $this->count(['status' => 'active']);
        
        // Inactive links
        $stats['inactive'] = $this->count(['status' => 'inactive']);
        
        // Links by category
        $categories = ['railway', 'system', 'external', 'grievance'];
        foreach ($categories as $category) {
            $stats['by_category'][$category] = $this->count(['category' => $category, 'status' => 'active']);
        }
        
        return $stats;
    }
    
    /**
     * Get predefined icon suggestions
     */
    public function getIconSuggestions() {
        return [
            'railway' => [
                'fas fa-train' => 'Train',
                'fas fa-subway' => 'Subway',
                'fas fa-shipping-fast' => 'Shipping',
                'fas fa-route' => 'Route',
                'fas fa-map-marked-alt' => 'Location',
                'fas fa-calendar-alt' => 'Schedule',
                'fas fa-ticket-alt' => 'Ticket',
                'fas fa-clock' => 'Time',
                'fas fa-info-circle' => 'Information'
            ],
            'system' => [
                'fas fa-desktop' => 'Desktop',
                'fas fa-cog' => 'Settings',
                'fas fa-database' => 'Database',
                'fas fa-server' => 'Server',
                'fas fa-cloud' => 'Cloud',
                'fas fa-wifi' => 'Network',
                'fas fa-shield-alt' => 'Security',
                'fas fa-key' => 'Authentication',
                'fas fa-lock' => 'Lock'
            ],
            'external' => [
                'fas fa-external-link-alt' => 'External Link',
                'fas fa-globe' => 'Website',
                'fas fa-link' => 'Link',
                'fas fa-share' => 'Share',
                'fas fa-arrow-right' => 'Arrow Right',
                'fas fa-window-restore' => 'Window',
                'fas fa-expand' => 'Expand',
                'fas fa-eye' => 'View',
                'fas fa-search' => 'Search'
            ],
            'grievance' => [
                'fas fa-plus-circle' => 'Add',
                'fas fa-edit' => 'Edit',
                'fas fa-list' => 'List',
                'fas fa-file-alt' => 'Document',
                'fas fa-clipboard' => 'Clipboard',
                'fas fa-tasks' => 'Tasks',
                'fas fa-check-circle' => 'Check',
                'fas fa-exclamation-triangle' => 'Warning',
                'fas fa-comment' => 'Comment'
            ],
            'general' => [
                'fas fa-home' => 'Home',
                'fas fa-user' => 'User',
                'fas fa-users' => 'Users',
                'fas fa-envelope' => 'Email',
                'fas fa-phone' => 'Phone',
                'fas fa-question-circle' => 'Help',
                'fas fa-book' => 'Documentation',
                'fas fa-download' => 'Download',
                'fas fa-upload' => 'Upload'
            ]
        ];
    }
    
    /**
     * Upload and process icon file
     */
    public function uploadIcon($file) {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/icons/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and SVG are allowed.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum 2MB allowed.');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'icon_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/icons/' . $filename;
        } else {
            throw new Exception('Failed to upload icon file.');
        }
    }
    
    /**
     * Delete icon file
     */
    public function deleteIcon($iconPath) {
        if (!empty($iconPath) && $iconPath !== '' && strpos($iconPath, 'uploads/icons/') === 0) {
            $fullPath = dirname(__DIR__, 2) . '/public/' . $iconPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
?>
