<?php
/**
 * Customer Model
 * Handles customer creation and management
 */

require_once 'BaseModel.php';

class Customer extends BaseModel {
    protected $table = 'customers';
    
    /**
     * Generate a new customer ID in format ED + YYYYMMDD + two digit random number
     */
    public function generateCustomerId() {
        $maxAttempts = 100; // Prevent infinite loops
        $attempt = 0;
        
        do {
            $dateStr = date('Ymd'); // YYYYMMDD format
            $randomNumber = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT); // Two digit random number
            $customerId = 'ED' . $dateStr . $randomNumber;
            
            $attempt++;
            
            // Check if this ID already exists
            if (!$this->customerIdExists($customerId)) {
                return $customerId;
            }
            
        } while ($attempt < $maxAttempts);
        
        // Fallback: use timestamp + random if all attempts failed
        $timestamp = time();
        $randomNumber = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
        return 'ED' . substr($timestamp, -6) . $randomNumber;
    }
    
    /**
     * Check if customer ID exists
     */
    public function customerIdExists($customerId) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Create new customer
     */
    public function createCustomer($data) {
        // Generate customer ID if not provided
        if (empty($data['CustomerID'])) {
            $data['CustomerID'] = $this->generateCustomerId();
        }
        
        // Validate required fields
        if (empty($data['Name'])) {
            throw new Exception('Customer name is required');
        }
        
        if (empty($data['CompanyName'])) {
            throw new Exception('Company name is required');
        }
        
        if (empty($data['Email'])) {
            throw new Exception('Email is required');
        }
        
        if (empty($data['MobileNumber'])) {
            throw new Exception('Mobile number is required');
        }
        
        // Hash password if provided
        if (!empty($data['Password'])) {
            $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);
        }
        
        // Prepare data for insertion
        $customerData = [
            'CustomerID' => $data['CustomerID'],
            'Name' => $data['Name'],
            'Email' => $data['Email'],
            'MobileNumber' => $data['MobileNumber'],
            'CompanyName' => $data['CompanyName'],
            'Designation' => $data['Designation'] ?? null,
            'Password' => $data['Password'] ?? null,
            'Role' => $data['Role'] ?? 'Customer'
        ];
        
        // Insert customer
        $columns = array_keys($customerData);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute(array_values($customerData));
        
        if ($result) {
            return $data['CustomerID'];
        }
        
        return false;
    }
    
    /**
     * Find customer by ID
     */
    public function findById($customerId) {
        $stmt = $this->connection->prepare("SELECT * FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
    
    /**
     * Find customers by name (search)
     */
    public function findByName($name, $limit = 10) {
        $searchTerm = '%' . $name . '%';
        $stmt = $this->connection->prepare("
            SELECT * FROM customers 
            WHERE Name LIKE ? OR CompanyName LIKE ? 
            ORDER BY Name ASC 
            LIMIT ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all customers with pagination
     */
    public function getAll($page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->connection->prepare("
            SELECT * FROM customers 
            ORDER BY CustomerID DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent customers
     */
    public function getRecent($limit = 10) {
        $stmt = $this->connection->prepare("
            SELECT * FROM customers 
            ORDER BY CustomerID DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update customer information
     */
    public function updateCustomer($customerId, $data) {
        $allowedFields = ['Name', 'Email', 'MobileNumber', 'CompanyName', 'Designation', 'Password'];
        $updateFields = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $values[] = $customerId; // Add customer ID for WHERE clause
        
        $sql = "UPDATE customers SET " . implode(', ', $updateFields) . " WHERE CustomerID = ?";
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Delete customer (soft delete by marking as inactive if needed)
     * Note: This should be used carefully as it may affect existing complaints
     */
    public function deleteCustomer($customerId) {
        // First check if customer has any complaints
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM complaints WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception('Cannot delete customer with existing complaints');
        }
        
        // Delete customer
        $stmt = $this->connection->prepare("DELETE FROM customers WHERE CustomerID = ?");
        return $stmt->execute([$customerId]);
    }
    
    /**
     * Get customer statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total customers
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM customers");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Customers with complaints
        $stmt = $this->connection->prepare("
            SELECT COUNT(DISTINCT customer_id) as count 
            FROM complaints 
            WHERE customer_id IN (SELECT CustomerID FROM customers)
        ");
        $stmt->execute();
        $stats['with_complaints'] = $stmt->fetch()['count'];
        
        // Recent customers (last 30 days based on CustomerID pattern)
        $lastMonth = date('Ymd', strtotime('-30 days'));
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM customers 
            WHERE CustomerID LIKE 'ED" . date('Y') . "%'
        ");
        $stmt->execute();
        $stats['recent'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Get customers added in a specific date range
     */
    public function getCustomersAdded($dateRange, $filters = []) {
        $whereConditions = [];
        $params = [];
        
        // Date range filter - using CustomerID pattern for date filtering
        if (!empty($dateRange['start']) && !empty($dateRange['end'])) {
            $startDate = date('Ymd', strtotime($dateRange['start']));
            $endDate = date('Ymd', strtotime($dateRange['end']));
            $whereConditions[] = "CustomerID LIKE 'ED%' AND CAST(SUBSTRING(CustomerID, 3, 8) AS UNSIGNED) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $sql = "SELECT COUNT(*) as count FROM customers " . $whereClause;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Validate customer data
     */
    public function validateCustomerData($data) {
        $errors = [];
        
        if (empty($data['Name'])) {
            $errors[] = 'Customer name is required';
        } elseif (strlen($data['Name']) < 2) {
            $errors[] = 'Customer name must be at least 2 characters long';
        }
        
        if (empty($data['CompanyName'])) {
            $errors[] = 'Company name is required';
        } elseif (strlen($data['CompanyName']) < 2) {
            $errors[] = 'Company name must be at least 2 characters long';
        }
        
        if (!empty($data['Email']) && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (!empty($data['MobileNumber'])) {
            // Remove any non-digit characters for validation
            $cleanMobile = preg_replace('/[^0-9]/', '', $data['MobileNumber']);
            if (strlen($cleanMobile) !== 10) {
                $errors[] = 'Mobile number must be exactly 10 digits';
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if customer name and company combination already exists
     */
    public function isDuplicateCustomer($name, $companyName, $excludeCustomerId = null) {
        $sql = "SELECT COUNT(*) as count FROM customers WHERE Name = ? AND CompanyName = ?";
        $params = [$name, $companyName];
        
        if ($excludeCustomerId) {
            $sql .= " AND CustomerID != ?";
            $params[] = $excludeCustomerId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }

    /**
     * Authenticate customer using Email or Mobile and password
     */
    public function authenticateCustomerByEmailOrMobile($identifier, $password) {
        if (empty($identifier) || empty($password)) {
            return false;
        }
        
        // Check if identifier is email or mobile
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Search by email
            $stmt = $this->connection->prepare("SELECT * FROM customers WHERE Email = ?");
        } else {
            // Search by mobile number (clean the mobile number)
            $cleanMobile = preg_replace('/[^0-9]/', '', $identifier);
            $stmt = $this->connection->prepare("SELECT * FROM customers WHERE MobileNumber = ?");
            $identifier = $cleanMobile;
        }
        
        $stmt->execute([$identifier]);
        $customer = $stmt->fetch();
        
        if ($customer && password_verify($password, $customer['Password'])) {
            return $customer;
        }
        
        return false;
    }

    /**
     * Authenticate customer using CustomerID and password (legacy method)
     */
    public function authenticateCustomer($customerId, $password) {
        if (empty($customerId) || empty($password)) {
            return false;
        }
        
        $stmt = $this->connection->prepare("SELECT * FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch();
        
        if ($customer && password_verify($password, $customer['Password'])) {
            return $customer;
        }
        
        return false;
    }

    /**
     * Find customer by email
     */
    public function findByEmail($email) {
        $stmt = $this->connection->prepare("SELECT * FROM customers WHERE Email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Find customer by mobile number
     */
    public function findByMobile($mobileNumber) {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobileNumber);
        $stmt = $this->connection->prepare("SELECT * FROM customers WHERE MobileNumber = ?");
        $stmt->execute([$cleanMobile]);
        return $stmt->fetch();
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById($customerId) {
        $stmt = $this->connection->prepare("SELECT * FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
}
?>
