<?php
/**
 * Complaint Model
 * Handles complaint operations
 */

require_once 'BaseModel.php';

class Complaint extends BaseModel {
    protected $table = 'complaints';
    
    /**
     * Create new complaint
     */
    public function createComplaint($data) {
        // Generate unique complaint ID
        $data['complaint_id'] = $this->generateComplaintId();
        $data['date'] = getCurrentDate();
        $data['time'] = getCurrentTime();
        $data['created_at'] = getCurrentDateTime();
        $data['updated_at'] = getCurrentDateTime();
        $data['status'] = 'Pending';
        $data['priority'] = 'Low'; // Default priority is now 'Low' as per requirements
        $data['department'] = $data['department'] ?? '';
        $data['Forwarded_Flag'] = 'N';
        $data['Awaiting_Approval_Flag'] = 'N';
        
        // Map assigned_to to Assigned_To_Department for backward compatibility
        if (!empty($data['assigned_to'])) {
            $data['Assigned_To_Department'] = $data['assigned_to'];
            unset($data['assigned_to']);
        }
        
        // Set default assignment to Commercial department if not explicitly provided
        if (empty($data['Assigned_To_Department'])) {
            $data['Assigned_To_Department'] = 'COMMERCIAL';
        }
        
        // If shed_id is provided, assign to appropriate commercial controller based on division/zone
        if (!empty($data['shed_id'])) {
            $commercialController = $this->getCommercialControllerForShed($data['shed_id']);
            if ($commercialController) {
                $data['Assigned_To_Department'] = $commercialController;
            }
        }
        
        return $this->createWithId($data);
    }
    
    /**
     * Create record with custom ID
     */
    private function createWithId($data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute(array_values($data));
        
        return $result ? $data['complaint_id'] : false;
    }
    
    /**
     * Find complaint by complaint ID
     */
    public function findByComplaintId($complaintId) {
        $stmt = $this->connection->prepare("
            SELECT c.*, cu.Name as customer_name, cu.Email as customer_email,
                   u.name as assigned_to_name, w.Type as wagon_type, w.WagonCode as wagon_code,
                   c.Assigned_To_Department as assigned_to, s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN users u ON c.Assigned_To_Department = u.login_id
            LEFT JOIN wagon_details w ON c.wagon_id = w.WagonID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.complaint_id = ?
        ");
        $stmt->execute([$complaintId]);
        return $stmt->fetch();
    }
    
    /**
     * Get complaints by customer
     */
    public function findByCustomer($customerId, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, c.Assigned_To_Department as assigned_to,
                   s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.customer_id = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaints by status
     */
    public function findByStatus($status, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, c.Assigned_To_Department as assigned_to,
                   s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.status = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaints assigned to user
     */
    public function findAssignedTo($loginId, $limit = null) {
        // Get user details to check division
        $userModel = new User();
        $userDetails = $userModel->findByLoginId($loginId);
        $userDivision = $userDetails['Division'] ?? null;
        $userDepartment = $userDetails['department'] ?? null;
        
        // Base query including either direct assignment or in same division for forwarded complaints
        $sql = "
            SELECT c.*, cu.Name as customer_name, c.Assigned_To_Department as assigned_to,
                   s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE (c.Assigned_To_Department = ? 
                  OR (c.Division = ? AND c.Forwarded_Flag = 'Y' 
                      AND " . ($userDepartment === 'COMMERCIAL' ? "1=1" : "c.department = ?") . "))
            ORDER BY c.created_at DESC
        ";
        
        $params = [$loginId, $userDivision];
        if ($userDepartment !== 'COMMERCIAL') {
            $params[] = $userDepartment;
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Update complaint status
     */
    public function updateStatus($complaintId, $status, $actionTaken = null) {
        $sql = "UPDATE complaints SET status = ?, updated_at = ?";
        $params = [$status, getCurrentDateTime()];
        
        if ($actionTaken !== null) {
            $sql .= ", action_taken = ?";
            $params[] = $actionTaken;
        }

        if (in_array($status, ['replied', 'awaiting_approval', 'closed'])) {
            $sql .= ", Forwarded_Flag = 'N'";
        }

        if (in_array($status, ['replied', 'closed'])) {
            $sql .= ", Assigned_To_Department = NULL";
        }
        
        $sql .= " WHERE complaint_id = ?";
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Assign complaint to user
     */
    public function assignTo($complaintId, $loginId, $setForwardingFlag = false) {
        $sql = "UPDATE complaints SET Assigned_To_Department = ?, updated_at = ?";
        $params = [$loginId, getCurrentDateTime()];
        
        if ($setForwardingFlag) {
            $sql .= ", Forwarded_Flag = 'Y'";
        }
        
        $sql .= " WHERE complaint_id = ?";
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update priority
     */
    public function updatePriority($complaintId, $priority) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET priority = ?, updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$priority, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Calculate automatic priority based on complaint age
     * Rules: Low -> 1 hour -> Medium -> 3 hours -> High -> 1 day -> Critical
     */
    public function calculateAutoPriority($createdAt) {
        // Handle null or empty createdAt
        if (empty($createdAt)) {
            return 'Low';
        }
        
        $now = time();
        $created = strtotime($createdAt);
        
        // Handle invalid date
        if ($created === false) {
            return 'Low';
        }
        
        $ageInHours = ($now - $created) / 3600; // Convert to hours
        
        if ($ageInHours >= 24) { // 1 day or more
            return 'Critical';
        } elseif ($ageInHours >= 3) { // 3 hours or more
            return 'High';
        } elseif ($ageInHours >= 1) { // 1 hour or more
            return 'Medium';
        } else {
            return 'Low'; // Less than 1 hour
        }
    }
    
    /**
     * Update priorities for all pending complaints based on age
     * This should be called periodically (via cron job or on page load)
     */
    public function updateAutoPriorities() {
        try {
            // Get all pending complaints that might need priority updates
            $stmt = $this->connection->prepare("
                SELECT complaint_id, created_at, priority 
                FROM complaints 
                WHERE status IN ('Pending', 'Replied') 
                ORDER BY created_at ASC
            ");
            $stmt->execute();
            $complaints = $stmt->fetchAll();
            
            $updated = 0;
            foreach ($complaints as $complaint) {
                $newPriority = $this->calculateAutoPriority($complaint['created_at']);
                
                // Only update if priority has changed
                if ($newPriority !== $complaint['priority']) {
                    $this->updatePriority($complaint['complaint_id'], $newPriority);
                    $updated++;
                }
            }
            
            return $updated;
            
        } catch (Exception $e) {
            error_log("Error updating auto priorities: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get complaints by department
     */
    public function findByDepartment($department, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.department = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$department]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaints by priority
     */
    public function findByPriority($priority, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.priority = ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$priority]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get complaints by date range
     */
    public function findByDateRange($startDate, $endDate, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.date BETWEEN ? AND ?
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Search complaints
     */
    public function search($searchTerm, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.complaint_id LIKE ? 
               OR c.description LIKE ? 
               OR cu.Name LIKE ?
            ORDER BY c.created_at DESC
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
     * Get complaint statistics
     */
    public function getStatistics($filters = []) {
        $stats = [];
        
        // Build WHERE clause with all filters
        $whereConditions = [];
        $params = [];
        
        // Date filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereConditions[] = "date BETWEEN ? AND ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
        }
        
        // Department filter
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        // Assigned to filter
        if (!empty($filters['assigned_to'])) {
            $whereConditions[] = "Assigned_To_Department = ?";
            $params[] = $filters['assigned_to'];
        }
        
        // Customer filter
        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "customer_id = ?";
            $params[] = $filters['customer_id'];
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // Total complaints
        $sql = "SELECT COUNT(*) as count FROM complaints " . $whereClause;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $stats['total'] = $stmt->fetch()['count'];
        
        // Complaints by status
        $sql = "
            SELECT status, COUNT(*) as count 
            FROM complaints 
            " . $whereClause . "
            GROUP BY status
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $stats['by_status'] = $stmt->fetchAll();
        
        // Complaints by priority
        $sql = "
            SELECT priority, COUNT(*) as count 
            FROM complaints 
            " . $whereClause . "
            GROUP BY priority
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $stats['by_priority'] = $stmt->fetchAll();
        
        // Complaints by department
        $sql = "
            SELECT department, COUNT(*) as count 
            FROM complaints 
            " . $whereClause . "
            GROUP BY department
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $stats['by_department'] = $stmt->fetchAll();
        
        // Complaints by category
        $sql = "
            SELECT category, COUNT(*) as count 
            FROM complaints 
            " . $whereClause . "
            GROUP BY category
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $stats['by_category'] = $stmt->fetchAll();
        
        // Complaints by type
        $sql = "
            SELECT Type, COUNT(*) as count 
            FROM complaints 
            " . $whereClause . "
            GROUP BY Type
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $stats['by_type'] = $stmt->fetchAll();
        
        // Recent complaints (last 7 days)
        $sql = "
            SELECT COUNT(*) as count 
            FROM complaints 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $stats['recent_7_days'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Calculate average pendency of complaints
     */
    public function calculateAveragePendency($filters = []) {
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereConditions[] = "date BETWEEN ? AND ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $sql = "
            SELECT AVG(DATEDIFF(NOW(), created_at)) as avg_pendency
            FROM complaints 
            " . $whereClause . "
            AND status IN ('Pending', 'Replied')
        ";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return round($result['avg_pendency'] ?? 0, 1);
    }
    
    /**
     * Calculate average reply time
     */
    public function calculateAverageReplyTime($filters = []) {
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereConditions[] = "date BETWEEN ? AND ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $sql = "
            SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_reply_time
            FROM complaints 
            " . $whereClause . "
            AND status = 'Replied'
        ";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return round($result['avg_reply_time'] ?? 0, 1);
    }
    
    /**
     * Calculate number of forwards
     */
    public function calculateNumberOfForwards($filters = []) {
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereConditions[] = "date BETWEEN ? AND ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $sql = "
            SELECT COUNT(*) as forward_count
            FROM complaints 
            " . $whereClause . "
            AND Forwarded_Flag = 'Y'
        ";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['forward_count'] ?? 0;
    }
    
    /**
     * Get subtype bifurcation for a specific type
     */
    public function getSubtypeBifurcation($type, $filters = []) {
        $whereConditions = ["Type = ?"];
        $params = [$type];
        
        // Apply filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereConditions[] = "date BETWEEN ? AND ?";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
        
        $sql = "
            SELECT Subtype, COUNT(*) as count 
            FROM complaints 
            " . $whereClause . "
            GROUP BY Subtype
        ";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get dashboard data for user
     */
    public function getDashboardData($userId, $userRole) {
        $data = [];
        
        if ($userRole === 'customer') {
            // Customer dashboard
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE customer_id = ?
            ");
            $stmt->execute([$userId]);
            $data['total_complaints'] = $stmt->fetch()['count'];
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE customer_id = ? AND status = 'Pending'
            ");
            $stmt->execute([$userId]);
            $data['pending_complaints'] = $stmt->fetch()['count'];
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE customer_id = ? AND status = 'Replied'
            ");
            $stmt->execute([$userId]);
            $data['replied_complaints'] = $stmt->fetch()['count'];
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE customer_id = ? AND status = 'Closed'
            ");
            $stmt->execute([$userId]);
            $data['closed_complaints'] = $stmt->fetch()['count'];
            
        } else {
            // Staff dashboard
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE Assigned_To_Department = ?
            ");
            $stmt->execute([$userId]);
            $data['assigned_complaints'] = $stmt->fetch()['count'];
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE Assigned_To_Department = ? AND status = 'Pending'
            ");
            $stmt->execute([$userId]);
            $data['pending_complaints'] = $stmt->fetch()['count'];
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE Assigned_To_Department = ? AND status = 'Replied'
            ");
            $stmt->execute([$userId]);
            $data['replied_complaints'] = $stmt->fetch()['count'];
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) as count 
                FROM complaints 
                WHERE Assigned_To_Department = ? AND status = 'Closed'
            ");
            $stmt->execute([$userId]);
            $data['closed_complaints'] = $stmt->fetch()['count'];
        }
        
        return $data;
    }
    
    /**
     * Update complaint rating
     */
    public function updateRating($complaintId, $rating, $ratingRemarks = null) {
        $sql = "UPDATE complaints SET rating = ?, updated_at = ?";
        $params = [$rating, getCurrentDateTime()];
        
        if ($ratingRemarks !== null) {
            $sql .= ", rating_remarks = ?";
            $params[] = $ratingRemarks;
        }
        
        $sql .= " WHERE complaint_id = ?";
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Forward complaint to another department
     */
    public function forwardComplaint($complaintId, $toDepartment) {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET Assigned_To_Department = ?, Forwarded_Flag = 'Y', updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$toDepartment, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Set awaiting approval flag
     */
    public function setAwaitingApproval($complaintId, $flag = 'Y') {
        $stmt = $this->connection->prepare("
            UPDATE complaints 
            SET Awaiting_Approval_Flag = ?, updated_at = ? 
            WHERE complaint_id = ?
        ");
        return $stmt->execute([$flag, getCurrentDateTime(), $complaintId]);
    }
    
    /**
     * Find complaints awaiting approval
     */
    public function findAwaitingApproval($assignedTo = null, $limit = null) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.status = 'awaiting_approval' AND c.Awaiting_Approval_Flag = 'Y'
        ";
        
        $params = [];
        
        // Filter by assigned user if provided
        if ($assignedTo) {
            $sql .= " AND c.Assigned_To_Department = ?";
            $params[] = $assignedTo;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count complaints awaiting approval (both status and flag)
     */
    public function countAwaitingApproval($assignedTo = null) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM complaints c 
            WHERE c.status = 'awaiting_approval' AND c.Awaiting_Approval_Flag = 'Y'
        ";
        
        $params = [];
        
        // Filter by assigned user if provided
        if ($assignedTo) {
            $sql .= " AND c.Assigned_To_Department = ?";
            $params[] = $assignedTo;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Count complaints with filters
     */
    public function countWithFilters($filters = [], $search = '', $userLoginId = null) {
        // If user login ID provided, get user details for division restriction
        $userDivision = null;
        if ($userLoginId) {
            $userModel = new User();
            $userDetails = $userModel->findByLoginId($userLoginId);
            $userDivision = $userDetails['Division'] ?? null;
        }
        
        $sql = "SELECT COUNT(*) as count FROM complaints c";
        $params = [];
        $conditions = [];
        
        // Add division restriction if applicable
        if ($userDivision && $userDivision !== 'HQ') {
            $conditions[] = "(c.Division = ? OR c.Division = 'HQ' OR c.Division IS NULL)";
            $params[] = $userDivision;
        }
        
        // Add search condition
        if (!empty($search)) {
            $conditions[] = "(c.complaint_id LIKE ? OR c.description LIKE ?)";
            $searchPattern = "%$search%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'status':
                            $conditions[] = "c.status = ?";
                            $params[] = $value;
                            break;
                        case 'priority':
                            $conditions[] = "c.priority = ?";
                            $params[] = $value;
                            break;
                        case 'department':
                            $conditions[] = "c.department = ?";
                            $params[] = $value;
                            break;
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Find assigned complaints with filters
     */
    public function findAssignedToWithFilters($loginId, $filters = [], $search = '', $limit = null, $offset = null) {
        // Get user details to check division
        $userModel = new User();
        $userDetails = $userModel->findByLoginId($loginId);
        $userDivision = $userDetails['Division'] ?? null;
        $userDepartment = $userDetails['department'] ?? null;

        // Base query including either direct assignment or in same division for forwarded complaints
        $sql = "
            SELECT c.*, cu.Name as customer_name, c.Assigned_To_Department as assigned_to,
                   s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE (c.Assigned_To_Department = ? 
                  OR (c.Division = ? AND c.Forwarded_Flag = 'Y' 
                      AND " . ($userDepartment === 'COMMERCIAL' ? "1=1" : "c.department = ?") . "))
        ";
        
        $params = [$loginId, $userDivision];
        if ($userDepartment !== 'COMMERCIAL') {
            $params[] = $userDepartment;
        }
        
        $conditions = [];
        
        // Add search condition
        if (!empty($search)) {
            $conditions[] = "(c.complaint_id LIKE ? OR c.description LIKE ?)";
            $searchPattern = "%$search%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'status':
                            $conditions[] = "c.status = ?";
                            $params[] = $value;
                            break;
                        case 'priority':
                            $conditions[] = "c.priority = ?";
                            $params[] = $value;
                            break;
                        case 'department':
                            $conditions[] = "c.department = ?";
                            $params[] = $value;
                            break;
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count assigned complaints with filters
     */
    public function countAssignedToWithFilters($loginId, $filters = [], $search = '') {
        // Get user details to check division
        $userModel = new User();
        $userDetails = $userModel->findByLoginId($loginId);
        $userDivision = $userDetails['Division'] ?? null;
        $userDepartment = $userDetails['department'] ?? null;
        
        // Base query including either direct assignment or in same division for forwarded complaints
        $sql = "SELECT COUNT(*) as count FROM complaints c WHERE (c.Assigned_To_Department = ? 
                OR (c.Division = ? AND c.Forwarded_Flag = 'Y' 
                    AND " . ($userDepartment === 'COMMERCIAL' ? "1=1" : "c.department = ?") . "))";
                    
        $params = [$loginId, $userDivision];
        if ($userDepartment !== 'COMMERCIAL') {
            $params[] = $userDepartment;
        }
        
        $conditions = [];
        
        // Add search condition
        if (!empty($search)) {
            $conditions[] = "(c.complaint_id LIKE ? OR c.description LIKE ?)";
            $searchPattern = "%$search%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'status':
                            $conditions[] = "c.status = ?";
                            $params[] = $value;
                            break;
                        case 'priority':
                            $conditions[] = "c.priority = ?";
                            $params[] = $value;
                            break;
                        case 'department':
                            $conditions[] = "c.department = ?";
                            $params[] = $value;
                            break;
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Search complaints with filters and pagination
     * Added division restriction - departments can only see complaints from their division except HQ
     */
    public function searchWithFilters($searchTerm = '', $filters = [], $limit = null, $offset = null, $userLoginId = null) {
        // If user login ID provided, get user details for division restriction
        $userDivision = null;
        if ($userLoginId) {
            $userModel = new User();
            $userDetails = $userModel->findByLoginId($userLoginId);
            $userDivision = $userDetails['Division'] ?? null;
        }
        
        $sql = "
            SELECT c.*, cu.Name as customer_name 
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
        ";
        $params = [];
        $conditions = [];
        
        // Add division restriction if applicable
        if ($userDivision && $userDivision !== 'HQ') {
            $conditions[] = "(c.Division = ? OR c.Division = 'HQ' OR c.Division IS NULL)";
            $params[] = $userDivision;
        }
        
        // Add search condition
        if (!empty($searchTerm)) {
            $conditions[] = "(c.complaint_id LIKE ? OR c.description LIKE ?)";
            $searchPattern = "%$searchTerm%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'status':
                            $conditions[] = "c.status = ?";
                            $params[] = $value;
                            break;
                        case 'priority':
                            $conditions[] = "c.priority = ?";
                            $params[] = $value;
                            break;
                        case 'department':
                            $conditions[] = "c.department = ?";
                            $params[] = $value;
                            break;
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Update complaint
     */
    public function updateComplaint($complaintId, $data) {
        $data['updated_at'] = getCurrentDateTime();
        
        $columns = array_keys($data);
        $setClause = array_map(function($column) {
            return "$column = ?";
        }, $columns);
        
        $sql = "UPDATE complaints SET " . implode(', ', $setClause) . " WHERE complaint_id = ?";
        
        $params = array_values($data);
        $params[] = $complaintId;
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Count complaints assigned to a user
     */
    public function countAssignedTo($loginId) {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM complaints 
            WHERE Assigned_To_Department = ?
        ");
        $stmt->execute([$loginId]);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Generate unique complaint ID
     */
    private function generateComplaintId() {
        $prefix = 'CMP';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $date . $random;
    }
    
    /**
     * Find complaints by customer with filters
     * SECURITY: This method ensures customers can only see their own complaints
     * The WHERE clause filters by customer_id to prevent data leakage
     */
    public function findByCustomerWithFilters($customerId, $filters = [], $search = '', $limit = null, $offset = null) {
        // Validate customer ID to prevent SQL injection
        if (empty($customerId)) {
            return [];
        }
        
        $sql = "
            SELECT c.*, cu.Name as customer_name, w.Type as wagon_type, w.WagonCode as wagon_code,
                   s.Terminal as shed_terminal, s.Type as shed_type
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            LEFT JOIN wagon_details w ON c.wagon_id = w.WagonID
            LEFT JOIN shed s ON c.shed_id = s.ShedID
            WHERE c.customer_id = ?
        ";
        $params = [$customerId];
        $conditions = [];
        
        // Add search condition
        if (!empty($search)) {
            $conditions[] = "(c.complaint_id LIKE ? OR c.Type LIKE ? OR c.description LIKE ?)";
            $searchPattern = "%$search%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'status':
                            $conditions[] = "c.status = ?";
                            $params[] = $value;
                            break;
                        case 'priority':
                            $conditions[] = "c.priority = ?";
                            $params[] = $value;
                            break;
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count complaints by customer with filters
     * SECURITY: This method ensures customers can only count their own complaints
     * The WHERE clause filters by customer_id to prevent data leakage
     */
    public function countByCustomerWithFilters($customerId, $filters = [], $search = '') {
        // Validate customer ID to prevent SQL injection
        if (empty($customerId)) {
            return 0;
        }
        
        $sql = "
            SELECT COUNT(*) as count 
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            WHERE c.customer_id = ?
        ";
        $params = [$customerId];
        $conditions = [];
        
        // Add search condition
        if (!empty($search)) {
            $conditions[] = "(c.complaint_id LIKE ? OR c.Type LIKE ? OR c.description LIKE ?)";
            $searchPattern = "%$search%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Add filter conditions
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'status':
                            $conditions[] = "c.status = ?";
                            $params[] = $value;
                            break;
                        case 'priority':
                            $conditions[] = "c.priority = ?";
                            $params[] = $value;
                            break;
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Count complaints by customer
     * SECURITY: This method ensures customers can only count their own complaints
     */
    public function countByCustomer($customerId) {
        // Validate customer ID to prevent SQL injection
        if (empty($customerId)) {
            return 0;
        }
        
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM complaints 
            WHERE customer_id = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Auto-close complaints after 3 days with null feedback
     * This should be called periodically (via cron job or on page load)
     */
    public function autoCloseOldComplaints() {
        try {
            // Get complaints that are older than 3 days and still in replied status
            $stmt = $this->connection->prepare("
                SELECT complaint_id, customer_id, created_at 
                FROM complaints 
                WHERE status = 'Replied' 
                AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)
                AND (rating IS NULL OR rating = '')
            ");
            $stmt->execute();
            $oldComplaints = $stmt->fetchAll();
            
            $closedCount = 0;
            foreach ($oldComplaints as $complaint) {
                // Update complaint to closed with null feedback
                $updateData = [
                    'status' => 'Closed',
                    'rating' => null,
                    'rating_remarks' => 'Auto-closed'
                ];
                
                if ($this->updateComplaint($complaint['complaint_id'], $updateData)) {
                    // Log the auto-close transaction
                    require_once __DIR__ . '/Transaction.php';
                    $transactionModel = new Transaction();
                    $transactionModel->createTransaction([
                        'complaint_id' => $complaint['complaint_id'],
                        'transaction_type' => 'auto_close',
                        'remarks' => 'Complaint auto-closed after 3 days due to no customer feedback',
                        'created_by' => 'system'
                    ]);
                    
                    $closedCount++;
                }
            }
            
            return $closedCount;
            
        } catch (Exception $e) {
            error_log("Error auto-closing old complaints: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent complaints
     */
    public function getRecent($limit = 10) {
        $sql = "
            SELECT c.*, cu.Name as customer_name, c.Assigned_To_Department as assigned_to
            FROM complaints c
            LEFT JOIN customers cu ON c.customer_id = cu.CustomerID
            ORDER BY c.created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get commercial controller for a specific shed based on division and zone
     */
    public function getCommercialControllerForShed($shedId) {
        try {
            // Get shed details with division and zone
            $stmt = $this->connection->prepare("
                SELECT s.Zone, s.Division 
                FROM shed s 
                WHERE s.ShedID = ?
            ");
            $stmt->execute([$shedId]);
            $shedDetails = $stmt->fetch();
            
            if (!$shedDetails) {
                return null;
            }
            
            // Find commercial controller for this division and zone
            $stmt = $this->connection->prepare("
                SELECT login_id 
                FROM users 
                WHERE role = 'controller' 
                AND department = 'COMMERCIAL' 
                AND Division = ? 
                AND Zone = ? 
                AND status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$shedDetails['Division'], $shedDetails['Zone']]);
            $controller = $stmt->fetch();
            
            return $controller ? $controller['login_id'] : null;
            
        } catch (Exception $e) {
            error_log("Error getting commercial controller for shed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Override findAll to include assigned_to alias for backward compatibility
     */
    public function findAll($conditions = [], $orderBy = 'created_at DESC', $limit = null) {
        $sql = "SELECT *, Assigned_To_Department as assigned_to FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
