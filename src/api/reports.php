<?php
/**
 * Reports API
 * Handles report-related requests with comprehensive analytics
 */

require_once '../../config/config.php';
require_once '../utils/SessionManager.php';
require_once '../models/Complaint.php';
require_once '../models/User.php';
require_once '../models/Transaction.php';
require_once '../models/Customer.php';

// Ensure user is authenticated
SessionManager::start();
if (!SessionManager::isLoggedIn()) {
    sendError('Authentication required', 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'dashboard_stats':
            handleDashboardStats();
            break;
        case 'complaints_by_status':
            handleComplaintsByStatus();
            break;
        case 'complaints_by_priority':
            handleComplaintsByPriority();
            break;
        case 'complaints_by_department':
            handleComplaintsByDepartment();
            break;
        case 'complaints_by_category':
            handleComplaintsByCategory();
            break;
        case 'complaints_timeline':
            handleComplaintsTimeline();
            break;
        case 'performance_metrics':
            handlePerformanceMetrics();
            break;
        case 'user_activity':
            handleUserActivity();
            break;
        case 'resolution_time':
            handleResolutionTime();
            break;
        case 'pivot_table':
            handlePivotTable();
            break;
        case 'mis_report':
            handleMISReport();
            break;
        case 'export_data':
            handleExportData();
            break;
        case 'generate_sample_data':
            handleGenerateSampleData();
            break;
        case 'test':
            handleTest();
            break;
        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError('Reports API error: ' . $e->getMessage());
}

function handleDashboardStats() {
    try {
        $complaintModel = new Complaint();
        $userModel = new User();
        $transactionModel = new Transaction();
        
        // Get date range from request
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
        $dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today
        
        $filters = ['date_from' => $dateFrom, 'date_to' => $dateTo];
        
        // Get basic statistics
        $stats = $complaintModel->getStatistics($filters);
        
        // Get additional metrics
        $totalUsers = $userModel->count(['status' => 'active']);
        $totalCustomers = (new Customer())->count();
        
        // Get recent activity
        $recentComplaints = $complaintModel->getRecent(5);
        $recentTransactions = $transactionModel->getRecent(10);
        
        // Calculate pending and replied counts from status distribution
        $pendingCount = 0;
        $repliedCount = 0;
        foreach ($stats['by_status'] ?? [] as $statusItem) {
            if ($statusItem['status'] === 'Pending') {
                $pendingCount = $statusItem['count'];
            } elseif ($statusItem['status'] === 'Replied') {
                $repliedCount = $statusItem['count'];
            }
        }
        
        $response = [
            'summary' => [
                'total_complaints' => $stats['total'] ?? 0,
                'total_users' => $totalUsers ?? 0,
                'total_customers' => $totalCustomers ?? 0,
                'pending_complaints' => $pendingCount,
                'replied_complaints' => $repliedCount
            ],
            'status_distribution' => $stats['by_status'] ?? [],
            'priority_distribution' => $stats['by_priority'] ?? [],
            'recent_complaints' => $recentComplaints ?? [],
            'recent_activity' => $recentTransactions ?? []
        ];
        
        sendSuccess($response);
    } catch (Exception $e) {
        sendError('Dashboard stats error: ' . $e->getMessage());
    }
}

function handleComplaintsByStatus() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = "
        SELECT 
            status,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM complaints WHERE date BETWEEN ? AND ?), 2) as percentage
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY status
        ORDER BY count DESC
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo, $dateFrom, $dateTo]);
    
    sendSuccess($data);
}

function handleComplaintsByPriority() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = "
        SELECT 
            priority,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM complaints WHERE date BETWEEN ? AND ?), 2) as percentage
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY priority
        ORDER BY 
            CASE priority 
                WHEN 'Critical' THEN 1 
                WHEN 'High' THEN 2 
                WHEN 'Medium' THEN 3 
                WHEN 'Low' THEN 4 
            END
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo, $dateFrom, $dateTo]);
    
    sendSuccess($data);
}

function handleComplaintsByDepartment() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = "
        SELECT 
            department,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM complaints WHERE date BETWEEN ? AND ?), 2) as percentage
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY department
        ORDER BY count DESC
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo, $dateFrom, $dateTo]);
    
    sendSuccess($data);
}

function handleComplaintsByCategory() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = "
        SELECT 
            Type as category,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM complaints WHERE date BETWEEN ? AND ?), 2) as percentage
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY Type
        ORDER BY count DESC
        LIMIT 10
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo, $dateFrom, $dateTo]);
    
    sendSuccess($data);
}

function handleComplaintsTimeline() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $groupBy = $_GET['group_by'] ?? 'day'; // day, week, month
    
    $dateFormat = $groupBy === 'month' ? '%Y-%m' : ($groupBy === 'week' ? '%Y-%u' : '%Y-%m-%d');
    
    $sql = "
        SELECT 
            DATE_FORMAT(date, ?) as period,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as replied,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Reverted' THEN 1 ELSE 0 END) as rejected
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(date, ?)
        ORDER BY period
    ";
    
    $data = $complaintModel->query($sql, [$dateFormat, $dateFrom, $dateTo, $dateFormat]);
    
    sendSuccess($data);
}

function handlePerformanceMetrics() {
    $complaintModel = new Complaint();
    $transactionModel = new Transaction();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    // Average resolution time
    $sql = "
        SELECT 
            AVG(DATEDIFF(updated_at, created_at)) as avg_resolution_days,
            MIN(DATEDIFF(updated_at, created_at)) as min_resolution_days,
            MAX(DATEDIFF(updated_at, created_at)) as max_resolution_days
        FROM complaints 
        WHERE status = 'Replied' 
        AND date BETWEEN ? AND ?
        AND updated_at IS NOT NULL
    ";
    
    $resolutionTime = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    $resolutionTime = $resolutionTime[0] ?? [];
    
    // Response time metrics
    $sql = "
        SELECT 
            AVG(TIMESTAMPDIFF(HOUR, c.created_at, t.created_at)) as avg_response_hours
        FROM complaints c
        JOIN transactions t ON c.complaint_id = t.complaint_id
        WHERE c.date BETWEEN ? AND ?
        AND t.transaction_type = 'forward'
        AND t.created_at > c.created_at
    ";
    
    $responseTime = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    $responseTime = $responseTime[0] ?? [];
    
    // Satisfaction metrics (based on resolution rate)
    $sql = "
        SELECT 
            COUNT(*) as total_complaints,
            SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as replied_complaints,
            ROUND(SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as reply_rate
        FROM complaints 
        WHERE date BETWEEN ? AND ?
    ";
    
    $satisfaction = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    $satisfaction = $satisfaction[0] ?? [];
    
    $response = [
        'resolution_time' => $resolutionTime,
        'response_time' => $responseTime,
        'satisfaction' => $satisfaction
    ];
    
    sendSuccess($response);
}

function handleUserActivity() {
    $transactionModel = new Transaction();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = "
        SELECT 
            u.name as user_name,
            u.department,
            COUNT(t.transaction_id) as total_actions,
            SUM(CASE WHEN t.transaction_type = 'forward' THEN 1 ELSE 0 END) as forwards,
            SUM(CASE WHEN t.transaction_type = 'status_update' THEN 1 ELSE 0 END) as status_updates,
            SUM(CASE WHEN t.transaction_type = 'assignment' THEN 1 ELSE 0 END) as assignments
        FROM users u
        LEFT JOIN transactions t ON u.login_id = t.created_by
        WHERE (t.created_at BETWEEN ? AND ?) OR t.created_at IS NULL
        GROUP BY u.login_id, u.name, u.department
        ORDER BY total_actions DESC
    ";
    
    $data = $transactionModel->query($sql, [$dateFrom, $dateTo]);
    
    sendSuccess($data);
}

function handleResolutionTime() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = "
        SELECT 
            Type,
            department,
            AVG(DATEDIFF(updated_at, created_at)) as avg_days,
            COUNT(*) as total_complaints,
            SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as resolved_count
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        AND updated_at IS NOT NULL
        GROUP BY Type, department
        ORDER BY avg_days DESC
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    
    sendSuccess($data);
}

function handlePivotTable() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $rows = $_GET['rows'] ?? 'department';
    $columns = $_GET['columns'] ?? 'status';
    $values = $_GET['values'] ?? 'count';
    
    $sql = "
        SELECT 
            $rows,
            $columns,
            COUNT(*) as count,
            AVG(DATEDIFF(updated_at, created_at)) as avg_resolution_days
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY $rows, $columns
        ORDER BY $rows, $columns
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    
    // Transform to pivot format
    $pivotData = [];
    $uniqueRows = [];
    $uniqueColumns = [];
    
    foreach ($data as $row) {
        $rowValue = $row[$rows];
        $colValue = $row[$columns];
        $uniqueRows[$rowValue] = true;
        $uniqueColumns[$colValue] = true;
        
        if (!isset($pivotData[$rowValue])) {
            $pivotData[$rowValue] = [];
        }
        
        $pivotData[$rowValue][$colValue] = $row[$values];
    }
    
    $response = [
        'data' => $pivotData,
        'rows' => array_keys($uniqueRows),
        'columns' => array_keys($uniqueColumns),
        'raw_data' => $data
    ];
    
    sendSuccess($response);
}

function handleMISReport() {
    $complaintModel = new Complaint();
    $userModel = new User();
    $transactionModel = new Transaction();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    // Executive Summary
    $sql = "
        SELECT 
            COUNT(*) as total_complaints,
            SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as replied,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Reverted' THEN 1 ELSE 0 END) as rejected,
            AVG(DATEDIFF(updated_at, created_at)) as avg_reply_days,
            ROUND(SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as reply_rate
        FROM complaints 
        WHERE date BETWEEN ? AND ?
    ";
    
    $summary = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    $summary = $summary[0] ?? [];
    
    // Department Performance
    $sql = "
        SELECT 
            department,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as replied,
            ROUND(SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as reply_rate,
            AVG(DATEDIFF(updated_at, created_at)) as avg_days
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY department
        ORDER BY total DESC
    ";
    
    $departmentPerformance = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    
    // Top Issues
    $sql = "
        SELECT 
            Type,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM complaints WHERE date BETWEEN ? AND ?), 2) as percentage
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY Type
        ORDER BY count DESC
        LIMIT 10
    ";
    
    $topIssues = $complaintModel->query($sql, [$dateFrom, $dateTo, $dateFrom, $dateTo]);
    
    // Monthly Trends
    $sql = "
        SELECT 
            DATE_FORMAT(date, '%Y-%m') as month,
            COUNT(*) as complaints,
            SUM(CASE WHEN status = 'Replied' THEN 1 ELSE 0 END) as replied
        FROM complaints 
        WHERE date BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(date, '%Y-%m')
        ORDER BY month
    ";
    
    $monthlyTrends = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    
    $response = [
        'period' => [
            'from' => $dateFrom,
            'to' => $dateTo
        ],
        'executive_summary' => $summary,
        'department_performance' => $departmentPerformance,
        'top_issues' => $topIssues,
        'monthly_trends' => $monthlyTrends
    ];
    
    sendSuccess($response);
}

function handleExportData() {
    $complaintModel = new Complaint();
    
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $format = $_GET['format'] ?? 'json';
    
    $sql = "
        SELECT 
            c.*,
            u.name as customer_name,
            u.email as customer_email,
            a.name as assigned_to_name
        FROM complaints c
        LEFT JOIN users u ON c.customer_id = u.customer_id
        LEFT JOIN users a ON c.Assigned_To_Department = a.login_id
        WHERE c.date BETWEEN ? AND ?
        ORDER BY c.created_at DESC
    ";
    
    $data = $complaintModel->query($sql, [$dateFrom, $dateTo]);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="complaints_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // CSV data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    } else {
        sendSuccess($data);
    }
}

function handleGenerateSampleData() {
    // This function generates sample data for testing reports
    $complaintModel = new Complaint();
    $transactionModel = new Transaction();
    
    // Sample complaint types
    $complaintTypes = ['Freight Damage', 'Delay in Delivery', 'Billing Issue', 'Service Quality', 'Documentation'];
    $departments = ['COMMERCIAL', 'OPERATIONS', 'FINANCE', 'CUSTOMER_SERVICE'];
    $priorities = ['Critical', 'High', 'Medium', 'Low'];
    $statuses = ['Pending', 'Replied', 'Reverted'];
    
    // Generate sample complaints for the last 3 months
    for ($i = 0; $i < 50; $i++) {
        $date = date('Y-m-d', strtotime('-' . rand(0, 90) . ' days'));
        $status = $statuses[array_rand($statuses)];
        $updatedAt = $status === 'Replied' ? date('Y-m-d H:i:s', strtotime($date . ' +' . rand(1, 30) . ' days')) : null;
        
        $complaintData = [
            'customer_id' => 'ED' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'Type' => $complaintTypes[array_rand($complaintTypes)],
            'description' => 'Sample complaint description ' . ($i + 1),
            'department' => $departments[array_rand($departments)],
            'priority' => $priorities[array_rand($priorities)],
            'status' => $status,
            'date' => $date,
            'time' => date('H:i:s'),
            'created_at' => $date . ' ' . date('H:i:s'),
            'updated_at' => $updatedAt,
            'Assigned_To_Department' => 'commercial_controller'
        ];
        
        $complaintId = $complaintModel->createComplaint($complaintData);
        
        if ($complaintId) {
            // Generate sample transactions
            $transactionModel->logStatusUpdate($complaintId, 'Complaint received and assigned', 'commercial_controller');
            
            if ($status === 'Replied') {
                $transactionModel->logStatusUpdate($complaintId, 'Complaint replied successfully', 'commercial_controller');
            } elseif ($status === 'Reverted') {
                $transactionModel->logStatusUpdate($complaintId, 'Complaint reverted due to insufficient information', 'commercial_controller');
            }
        }
    }
    
    sendSuccess(['message' => 'Sample data generated successfully', 'count' => 50]);
}

function sendSuccess($data, $message = 'Success') {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function handleTest() {
    // Simple test to verify API is working
    $response = [
        'success' => true,
        'message' => 'Reports API is working',
        'timestamp' => getCurrentDateTime(),
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s')
    ];
    
    sendSuccess($response);
}

function sendError($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
?>
