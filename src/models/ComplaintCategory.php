<?php
/**
 * ComplaintCategory Model
 * Handles complaint categories, types, and subtypes
 */

require_once 'BaseModel.php';

class ComplaintCategory extends BaseModel {
    protected $table = 'complaint_categories';
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT Category 
            FROM complaint_categories 
            ORDER BY Category ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get types for a category
     */
    public function getTypesByCategory($category) {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT Type 
            FROM complaint_categories 
            WHERE Category = ? 
            ORDER BY Type ASC
        ");
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get subtypes for a category and type
     */
    public function getSubtypesByCategoryAndType($category, $type) {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT SubType 
            FROM complaint_categories 
            WHERE Category = ? AND Type = ? 
            ORDER BY SubType ASC
        ");
        $stmt->execute([$category, $type]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get all categories with their types and subtypes (hierarchical)
     */
    public function getHierarchicalData() {
        $stmt = $this->connection->prepare("
            SELECT Category, Type, SubType 
            FROM complaint_categories 
            ORDER BY Category ASC, Type ASC, SubType ASC
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $hierarchical = [];
        foreach ($results as $row) {
            $category = $row['Category'];
            $type = $row['Type'];
            $subtype = $row['SubType'];
            
            if (!isset($hierarchical[$category])) {
                $hierarchical[$category] = [];
            }
            
            if (!isset($hierarchical[$category][$type])) {
                $hierarchical[$category][$type] = [];
            }
            
            $hierarchical[$category][$type][] = $subtype;
        }
        
        return $hierarchical;
    }
    
    /**
     * Add new category entry
     */
    public function addCategory($category, $type, $subtype) {
        // Check if combination already exists
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM complaint_categories 
            WHERE Category = ? AND Type = ? AND SubType = ?
        ");
        $stmt->execute([$category, $type, $subtype]);
        $exists = $stmt->fetch()['count'] > 0;
        
        if ($exists) {
            return false; // Already exists
        }
        
        return $this->create([
            'Category' => $category,
            'Type' => $type,
            'SubType' => $subtype
        ]);
    }
    
    /**
     * Update category entry
     */
    public function updateCategory($id, $category, $type, $subtype) {
        $stmt = $this->connection->prepare("
            UPDATE complaint_categories 
            SET Category = ?, Type = ?, SubType = ? 
            WHERE CategoryID = ?
        ");
        return $stmt->execute([$category, $type, $subtype, $id]);
    }
    
    /**
     * Delete category entry
     */
    public function deleteCategory($id) {
        $stmt = $this->connection->prepare("DELETE FROM complaint_categories WHERE CategoryID = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Search categories
     */
    public function searchCategories($searchTerm) {
        $searchParam = "%$searchTerm%";
        $stmt = $this->connection->prepare("
            SELECT * FROM complaint_categories 
            WHERE Category LIKE ? OR Type LIKE ? OR SubType LIKE ?
            ORDER BY Category ASC, Type ASC, SubType ASC
        ");
        $stmt->execute([$searchParam, $searchParam, $searchParam]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all category records for management
     */
    public function getAllForManagement() {
        $stmt = $this->connection->prepare("
            SELECT CategoryID, Category, Type, SubType 
            FROM complaint_categories 
            ORDER BY Category ASC, Type ASC, SubType ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Validate category combination exists
     */
    public function validateCombination($category, $type, $subtype) {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM complaint_categories 
            WHERE Category = ? AND Type = ? AND SubType = ?
        ");
        $stmt->execute([$category, $type, $subtype]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get category statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total categories
        $stmt = $this->connection->prepare("SELECT COUNT(DISTINCT Category) as count FROM complaint_categories");
        $stmt->execute();
        $stats['total_categories'] = $stmt->fetch()['count'];
        
        // Total types
        $stmt = $this->connection->prepare("SELECT COUNT(DISTINCT Type) as count FROM complaint_categories");
        $stmt->execute();
        $stats['total_types'] = $stmt->fetch()['count'];
        
        // Total subtypes
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM complaint_categories");
        $stmt->execute();
        $stats['total_subtypes'] = $stmt->fetch()['count'];
        
        // Categories breakdown
        $stmt = $this->connection->prepare("
            SELECT Category, COUNT(*) as count 
            FROM complaint_categories 
            GROUP BY Category 
            ORDER BY count DESC
        ");
        $stmt->execute();
        $categoryBreakdown = $stmt->fetchAll();
        
        foreach ($categoryBreakdown as $item) {
            $stats['by_category'][$item['Category']] = $item['count'];
        }
        
        return $stats;
    }
    
    /**
     * Export categories to array format
     */
    public function exportToArray() {
        return $this->getAllForManagement();
    }
    
    /**
     * Import categories from array
     */
    public function importFromArray($categories) {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($categories as $category) {
            try {
                $result = $this->addCategory(
                    $category['Category'],
                    $category['Type'],
                    $category['SubType']
                );
                
                if ($result !== false) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Duplicate entry: {$category['Category']} - {$category['Type']} - {$category['SubType']}";
                }
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Error importing: {$category['Category']} - {$e->getMessage()}";
            }
        }
        
        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ];
    }
}
?>
