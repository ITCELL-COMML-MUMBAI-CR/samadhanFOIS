<?php
require_once dirname(__DIR__) . '/utils/Database.php';
require_once dirname(__DIR__) . '/models/Complaint.php';
require_once dirname(__DIR__) . '/models/Customer.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/utils/SessionManager.php';

header('Content-Type: application/json');

// Check if user is logged in
SessionManager::requireLogin();
$currentUser = SessionManager::getCurrentUser();

// Only allow controller, viewer, and admin roles
$allowedRoles = ['controller', 'viewer', 'admin'];
if (!in_array($currentUser['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Dashboard is only available for Controller, Viewer, and Admin roles.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $timeline = $_GET['timeline'] ?? 'current';
    $validTimelines = ['current', 'yesterday', 'month'];
    
    if (!in_array($timeline, $validTimelines)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid timeline parameter']);
        exit;
    }
    
    try {
        $complaintModel = new Complaint();
        $customerModel = new Customer();
        $userModel = new User();
        
        // Get date ranges based on timeline
        $dateRanges = getDateRanges($timeline);
        
        // Build filters based on user role and permissions
        $filters = buildUserFilters($currentUser);
        
        // Get current period data
        $currentData = getDashboardData($complaintModel, $customerModel, $dateRanges['current'], $filters);
        
        // Get previous period data for comparison
        $previousData = getDashboardData($complaintModel, $customerModel, $dateRanges['previous'], $filters);
        
        // Calculate variance percentages
        $data = calculateVariance($currentData, $previousData);
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timeline' => $timeline,
            'dateRanges' => $dateRanges
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch dashboard data: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function getDateRanges($timeline) {
    $now = new DateTime();
    
    switch ($timeline) {
        case 'current':
            $currentStart = $now->format('Y-m-d 00:00:00');
            $currentEnd = $now->format('Y-m-d 23:59:59');
            $previousStart = $now->modify('-1 day')->format('Y-m-d 00:00:00');
            $previousEnd = $now->format('Y-m-d 23:59:59');
            break;
            
        case 'yesterday':
            $currentStart = $now->modify('-1 day')->format('Y-m-d 00:00:00');
            $currentEnd = $now->format('Y-m-d 23:59:59');
            $previousStart = $now->modify('-1 day')->format('Y-m-d 00:00:00');
            $previousEnd = $now->format('Y-m-d 23:59:59');
            break;
            
        case 'month':
            $currentStart = $now->format('Y-m-01 00:00:00');
            $currentEnd = $now->format('Y-m-d 23:59:59');
            $previousStart = $now->modify('-1 month')->format('Y-m-01 00:00:00');
            $previousEnd = $now->modify('last day of this month')->format('Y-m-d 23:59:59');
            break;
    }
    
    return [
        'current' => ['start' => $currentStart, 'end' => $currentEnd],
        'previous' => ['start' => $previousStart, 'end' => $previousEnd]
    ];
}

function buildUserFilters($currentUser) {
    $filters = [];
    
    // Department filtering
    if (!empty($currentUser['department'])) {
        // Commercial department can see all departments
        if ($currentUser['department'] !== 'COMMERCIAL') {
            $filters['department'] = $currentUser['department'];
        }
    }
    
    return $filters;
}

function getDashboardData($complaintModel, $customerModel, $dateRange, $filters) {
    $data = [];
    
    // Add date filters
    $filters['date_from'] = $dateRange['start'];
    $filters['date_to'] = $dateRange['end'];
    
    // Get complaint statistics
    $complaintStats = $complaintModel->getStatistics($filters);
    
    // First Row Data
    $data['firstRow'] = [
        'totalComplaints' => $complaintStats['total'] ?? 0,
        'statusBifurcation' => $complaintStats['by_status'] ?? [],
        'statusBifurcationCount' => count($complaintStats['by_status'] ?? []),
        'averagePendency' => $complaintModel->calculateAveragePendency($filters),
        'averageReplyTime' => $complaintModel->calculateAverageReplyTime($filters),
        'numberOfForwards' => $complaintModel->calculateNumberOfForwards($filters)
    ];
    
    // Second Row Data
    $data['secondRow'] = [
        'categoryWiseCount' => $complaintStats['by_category'] ?? [],
        'typeWiseCount' => $complaintStats['by_type'] ?? []
    ];
    
    // Third Row Data
    $data['thirdRow'] = [
        'customersAdded' => $customerModel->getCustomersAdded($dateRange, $filters)
    ];
    
    return $data;
}

function calculateVariance($currentData, $previousData) {
    $varianceData = [];
    
    // Calculate variance for first row
    $varianceData['firstRow'] = [
        'totalComplaints' => calculatePercentageChange(
            $currentData['firstRow']['totalComplaints'],
            $previousData['firstRow']['totalComplaints']
        ),
        'averagePendency' => calculatePercentageChange(
            $currentData['firstRow']['averagePendency'],
            $previousData['firstRow']['averagePendency']
        ),
        'averageReplyTime' => calculatePercentageChange(
            $currentData['firstRow']['averageReplyTime'],
            $previousData['firstRow']['averageReplyTime']
        ),
        'numberOfForwards' => calculatePercentageChange(
            $currentData['firstRow']['numberOfForwards'],
            $previousData['firstRow']['numberOfForwards']
        )
    ];
    
    // Calculate variance for third row
    $varianceData['thirdRow'] = [
        'customersAdded' => calculatePercentageChange(
            $currentData['thirdRow']['customersAdded'],
            $previousData['thirdRow']['customersAdded']
        )
    ];
    
    return [
        'current' => $currentData,
        'previous' => $previousData,
        'variance' => $varianceData
    ];
}

function calculatePercentageChange($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    
    return round((($current - $previous) / $previous) * 100, 2);
}
?>
