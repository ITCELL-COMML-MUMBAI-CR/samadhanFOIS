<?php
/**
 * Shed Model
 * Handles shed/terminal information
 */

require_once 'BaseModel.php';

class Shed extends BaseModel {
    protected $table = 'shed';
    
    /**
     * Get all sheds
     */
    public function getAllSheds($limit = null) {
        $sql = "SELECT * FROM shed ORDER BY Zone ASC, Division ASC, Terminal ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get sheds by zone
     */
    public function findByZone($zone, $limit = null) {
        $sql = "SELECT * FROM shed WHERE Zone = ? ORDER BY Division ASC, Terminal ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$zone]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sheds by division
     */
    public function findByDivision($division, $limit = null) {
        $sql = "SELECT * FROM shed WHERE Division = ? ORDER BY Terminal ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$division]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sheds by type
     */
    public function findByType($type, $limit = null) {
        $sql = "SELECT * FROM shed WHERE Type = ? ORDER BY Zone ASC, Division ASC, Terminal ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find shed by terminal name
     */
    public function findByTerminal($terminal) {
        $stmt = $this->connection->prepare("SELECT * FROM shed WHERE Terminal = ?");
        $stmt->execute([$terminal]);
        return $stmt->fetch();
    }
    
    /**
     * Search sheds
     */
    public function search($searchTerm, $limit = null) {
        $sql = "
            SELECT * FROM shed 
            WHERE Terminal LIKE ? OR Type LIKE ?
            ORDER BY Zone ASC, Division ASC, Terminal ASC
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
     * Get shed statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total sheds
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM shed");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Sheds by zone
        $stmt = $this->connection->prepare("
            SELECT Zone, COUNT(*) as count 
            FROM shed 
            GROUP BY Zone
        ");
        $stmt->execute();
        $stats['by_zone'] = $stmt->fetchAll();
        
        // Sheds by division
        $stmt = $this->connection->prepare("
            SELECT Division, COUNT(*) as count 
            FROM shed 
            GROUP BY Division
        ");
        $stmt->execute();
        $stats['by_division'] = $stmt->fetchAll();
        
        // Sheds by type
        $stmt = $this->connection->prepare("
            SELECT Type, COUNT(*) as count 
            FROM shed 
            GROUP BY Type
        ");
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Get all zones
     */
    public function getZones() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT Zone 
            FROM shed 
            ORDER BY Zone ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get divisions by zone
     */
    public function getDivisionsByZone($zone) {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT Division 
            FROM shed 
            WHERE Zone = ? 
            ORDER BY Division ASC
        ");
        $stmt->execute([$zone]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get all divisions
     */
    public function getDivisions() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT Division 
            FROM shed 
            ORDER BY Division ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get all types
     */
    public function getTypes() {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT Type 
            FROM shed 
            ORDER BY Type ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get terminals by zone and division
     */
    public function getTerminalsByZoneAndDivision($zone, $division) {
        $stmt = $this->connection->prepare("
            SELECT * FROM shed 
            WHERE Zone = ? AND Division = ? 
            ORDER BY Terminal ASC
        ");
        $stmt->execute([$zone, $division]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get terminals by type
     */
    public function getTerminalsByType($type) {
        $stmt = $this->connection->prepare("
            SELECT * FROM shed 
            WHERE Type = ? 
            ORDER BY Zone ASC, Division ASC, Terminal ASC
        ");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
}
