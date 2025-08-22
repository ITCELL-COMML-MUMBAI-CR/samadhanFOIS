<?php
/**
 * Wagon Model
 * Handles wagon-related database operations
 */

require_once 'BaseModel.php';

class Wagon extends BaseModel {
    protected $table = 'wagon_details';
    
    /**
     * Get all wagons
     */
    public function getAllWagons() {
        $stmt = $this->connection->prepare("SELECT WagonID, WagonCode, Type FROM wagon_details ORDER BY Type ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get wagon by ID
     */
    public function getWagonById($wagonId) {
        $stmt = $this->connection->prepare("SELECT WagonID, WagonCode, Type FROM wagon_details WHERE WagonID = ?");
        $stmt->execute([$wagonId]);
        return $stmt->fetch();
    }
    
    /**
     * Get wagons by type
     */
    public function getWagonsByType($type) {
        $stmt = $this->connection->prepare("SELECT WagonID, WagonCode, Type FROM wagon_details WHERE Type = ? ORDER BY WagonCode ASC");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all wagon types
     */
    public function getAllWagonTypes() {
        $stmt = $this->connection->prepare("SELECT DISTINCT Type FROM wagon_details ORDER BY Type ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Search wagons by code or type
     */
    public function searchWagons($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $stmt = $this->connection->prepare("
            SELECT WagonID, WagonCode, Type 
            FROM wagon_details 
            WHERE WagonCode LIKE ? OR Type LIKE ? 
            ORDER BY Type ASC, WagonCode ASC
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
