<?php
require_once dirname(__DIR__) . '/utils/Database.php';
require_once dirname(__DIR__) . '/models/Complaint.php';
require_once dirname(__DIR__) . '/utils/SessionManager.php';

header('Content-Type: application/json');

// Check if user is logged in
SessionManager::requireLogin();
$currentUser = SessionManager::getCurrentUser();

// Only allow controller, viewer, and admin roles
$allowedRoles = ['controller', 'viewer', 'admin'];
if (!in_array($currentUser['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? '';
    $timeline = $_GET['timeline'] ?? 'current';
    
    if (empty($type)) {
        http_response_code(400);
        echo json_encode(['error' => 'Type parameter is required']);
        exit;
    }
    
    $validTimelines = ['current', 'yesterday', 'month'];
    if (!in_array($timeline, $validTimelines)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid timeline parameter']);
        exit;
    }
    
    try {
        $complaintModel = new Complaint();
        
        // Get date ranges based on timeline
        $dateRanges = getSubtypeDateRanges($timeline);
        
        // Build filters based on user role and permissions
        $filters = buildSubtypeUserFilters($currentUser);
        $filters['date_from'] = $dateRanges['current']['start'];
        $filters['date_to'] = $dateRanges['current']['end'];
        
        // Get subtype bifurcation
        $subtypeData = $complaintModel->getSubtypeBifurcation($type, $filters);
        
        echo json_encode([
            'success' => true,
            'data' => $subtypeData,
            'type' => $type,
            'timeline' => $timeline
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch subtype data: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function getSubtypeDateRanges($timeline) {
    $now = new DateTime();
    
    switch ($timeline) {
        case 'current':
            $currentStart = $now->format('Y-m-d 00:00:00');
            $currentEnd = $now->format('Y-m-d 23:59:59');
            break;
            
        case 'yesterday':
            $currentStart = $now->modify('-1 day')->format('Y-m-d 00:00:00');
            $currentEnd = $now->format('Y-m-d 23:59:59');
            break;
            
        case 'month':
            $currentStart = $now->format('Y-m-01 00:00:00');
            $currentEnd = $now->format('Y-m-d 23:59:59');
            break;
    }
    
    return [
        'current' => ['start' => $currentStart, 'end' => $currentEnd]
    ];
}

function buildSubtypeUserFilters($currentUser) {
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
?>
