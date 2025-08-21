<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class DashboardController extends BaseController {
    public function index() {
        SessionManager::requireLogin();
        $currentUser = SessionManager::getCurrentUser();
        $userRole = $currentUser['role'];

        // Only allow controller, viewer, and admin roles
        $allowedRoles = ['controller', 'viewer', 'admin'];
        if (!in_array($userRole, $allowedRoles)) {
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

        $complaintModel = $this->loadModel('Complaint');
        $userModel = $this->loadModel('User');
        $transactionModel = $this->loadModel('Transaction');

        $statistics = [];
        $recentGrievances = [];
        $dashboardData = [];

        // Update auto-priorities before loading dashboard data
        $complaintModel->updateAutoPriorities();

        // Build filters based on user role and permissions
        $filters = $this->buildUserFilters($currentUser);

        switch ($userRole) {
            case 'controller':
                $filters['assigned_to'] = $currentUser['login_id'];
                $statistics = $complaintModel->getStatistics($filters);
                $recentGrievances = $complaintModel->findAssignedTo($currentUser['login_id'], 5);
                break;
            case 'admin':
            case 'viewer':
                $statistics = $complaintModel->getStatistics($filters);
                $recentGrievances = $complaintModel->getRecent(5);
                break;
        }

        // Calculate auto-priority for recent grievances display
        foreach ($recentGrievances as &$grievance) {
            $grievance['display_priority'] = $complaintModel->calculateAutoPriority($grievance['created_at']);
        }

        if ($userRole === 'admin') {
            $dashboardData['user_stats'] = $userModel->getStatistics();
        }

        $recentTransactions = $transactionModel->getRecent(5, $userRole === 'customer' ? null : $currentUser['login_id']);

        $data = [
            'pageTitle' => 'Dashboard',
            'currentUser' => $currentUser,
            'userRole' => $userRole,
            'statistics' => $statistics,
            'recentGrievances' => $recentGrievances,
            'recentTransactions' => $recentTransactions,
            'dashboardData' => $dashboardData
        ];
        
        $this->loadView('header', $data);
        $this->loadView('pages/dashboard', $data);
        $this->loadView('footer');
    }


    
    /**
     * Build user-specific filters based on role and permissions
     */
    private function buildUserFilters($currentUser) {
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
}

