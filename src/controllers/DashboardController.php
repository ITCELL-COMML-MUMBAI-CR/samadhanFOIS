<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class DashboardController extends BaseController {
    public function index() {
        SessionManager::requireLogin();
        $currentUser = SessionManager::getCurrentUser();
        $userRole = $currentUser['role'];

        $complaintModel = $this->loadModel('Complaint');
        $userModel = $this->loadModel('User');
        $transactionModel = $this->loadModel('Transaction');

        $statistics = [];
        $recentGrievances = [];
        $dashboardData = [];

        switch ($userRole) {
            case 'customer':
                $filters = ['customer_id' => $currentUser['customer_id']];
                $statistics = $complaintModel->getStatistics($filters);
                $recentGrievances = $complaintModel->findByCustomer($currentUser['customer_id'], 5);
                break;
            case 'controller':
                $filters = ['assigned_to' => $currentUser['login_id']];
                $statistics = $complaintModel->getStatistics($filters);
                $recentGrievances = $complaintModel->findAssignedTo($currentUser['login_id'], 5);
                break;
            default: // admin, viewer
                $statistics = $complaintModel->getStatistics();
                $recentGrievances = $complaintModel->getRecent(5);
                break;
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
            'dashboardData' => $dashboardData,
            'statusChartData' => $this->prepareStatusChartData($statistics),
            'priorityChartData' => $this->preparePriorityChartData($statistics)
        ];
        
        $this->loadView('header', $data);
        $this->loadView('pages/dashboard', $data);
        $this->loadView('footer');
    }

    private function prepareStatusChartData($statistics) {
        $chartData = [];
        if (!empty($statistics['by_status'])) {
            foreach ($statistics['by_status'] as $status => $count) {
                $chartData[] = ['label' => ucfirst(str_replace('_', ' ', $status)), 'value' => $count, 'color' => $this->getStatusColor($status)];
            }
        }
        return $chartData;
    }

    private function preparePriorityChartData($statistics) {
        $chartData = [];
        if (!empty($statistics['by_priority'])) {
            foreach ($statistics['by_priority'] as $priority => $count) {
                $chartData[] = ['label' => ucfirst($priority), 'value' => $count, 'color' => $this->getPriorityColor($priority)];
            }
        }
        return $chartData;
    }

    private function getStatusColor($status) {
        $colors = ['pending' => '#fbbf24', 'replied' => '#3b82f6', 'closed' => '#6b7280', 'rejected' => '#dc2626'];
        return $colors[$status] ?? '#6b7280';
    }

    private function getPriorityColor($priority) {
        $colors = ['low' => '#22c55e', 'medium' => '#fbbf24', 'high' => '#ea580c', 'critical' => '#dc2626'];
        return $colors[$priority] ?? '#6b7280';
    }
}
