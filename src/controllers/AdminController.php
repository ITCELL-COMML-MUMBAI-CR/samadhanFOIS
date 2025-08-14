<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class AdminController extends BaseController {

    public function __construct() {
        SessionManager::requireRole('admin');
    }

    public function categories() {
        $categoryModel = $this->loadModel('ComplaintCategory');
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            list($error, $success) = $this->handleCategoryAction($categoryModel);
        }

        $searchTerm = $_GET['search'] ?? '';
        $categories = !empty($searchTerm) ? $categoryModel->searchCategories($searchTerm) : $categoryModel->getAllForManagement();
        
        $action = $_GET['action'] ?? 'list';
        $editCategory = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $editId = (int)$_GET['id'];
            foreach ($categories as $cat) {
                if ($cat['CategoryID'] == $editId) {
                    $editCategory = $cat;
                    break;
                }
            }
        }

        $data = [
            'categories' => $categories,
            'hierarchicalData' => $categoryModel->getHierarchicalData(),
            'stats' => $categoryModel->getStatistics(),
            'pageTitle' => 'Manage Categories',
            'error' => $error,
            'success' => $success,
            'searchTerm' => $searchTerm,
            'action' => $action,
            'editCategory' => $editCategory
        ];

        $this->loadView('header', $data);
        $this->loadView('pages/manage_categories', $data);
        $this->loadView('footer');
    }

    private function handleCategoryAction($categoryModel) {
        $postAction = $_POST['action'] ?? '';
        try {
            switch ($postAction) {
                case 'add':
                    $category = sanitizeInput($_POST['category'] ?? '');
                    $type = sanitizeInput($_POST['type'] ?? '');
                    $subtype = sanitizeInput($_POST['subtype'] ?? '');
                    if (empty($category) || empty($type) || empty($subtype)) {
                        return ['All fields are required.', ''];
                    }
                    return $categoryModel->addCategory($category, $type, $subtype) ? ['', 'Category added successfully!'] : ['Category combination already exists.', ''];
                case 'edit':
                    $id = (int)($_POST['id'] ?? 0);
                    $category = sanitizeInput($_POST['category'] ?? '');
                    $type = sanitizeInput($_POST['type'] ?? '');
                    $subtype = sanitizeInput($_POST['subtype'] ?? '');
                    if (empty($category) || empty($type) || empty($subtype)) {
                        return ['All fields are required.', ''];
                    }
                    return $categoryModel->updateCategory($id, $category, $type, $subtype) ? ['', 'Category updated successfully!'] : ['Failed to update category.', ''];
                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    return $categoryModel->deleteCategory($id) ? ['', 'Category deleted successfully!'] : ['Failed to delete category.', ''];
            }
        } catch (Exception $e) {
            error_log('Category management error: ' . $e->getMessage());
            return ['An error occurred. Please try again.', ''];
        }
        return ['', ''];
    }

    public function logs() {
        $data['pageTitle'] = 'System Logs';
        $this->loadView('header', $data);
        $this->loadView('pages/admin_logs', $data);
        $this->loadView('footer');
    }

    public function users() {
        $error = '';
        $success = '';
        $userModel = $this->loadModel('User');

        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postAction = $_POST['action'] ?? '';
            try {
                switch ($postAction) {
                    case 'update_user':
                        $originalLoginId = sanitizeInput($_POST['original_login_id'] ?? '');
                        if (empty($originalLoginId)) { $error = 'Original Login ID missing.'; break; }
                        $payload = [
                            'login_id' => sanitizeInput($_POST['login_id'] ?? ''),
                            'name' => sanitizeInput($_POST['name'] ?? ''),
                            'email' => sanitizeInput($_POST['email'] ?? ''),
                            'mobile' => sanitizeInput($_POST['mobile'] ?? ''),
                            'role' => sanitizeInput($_POST['role'] ?? ''),
                            'department' => sanitizeInput($_POST['department'] ?? ''),
                            'customer_id' => sanitizeInput($_POST['customer_id'] ?? ''),
                            'status' => sanitizeInput($_POST['status'] ?? 'active'),
                        ];
                        // Basic validation
                        if (empty($payload['login_id']) || empty($payload['name']) || empty($payload['role'])) {
                            $error = 'Login ID, Name and Role are required.'; break;
                        }
                        if (!empty($payload['email']) && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                            $error = 'Invalid email format.'; break;
                        }
                        $result = $userModel->updateUser($originalLoginId, $payload);
                        if ($result) { $success = 'User updated successfully!'; } else { $error = 'No changes or update failed.'; }
                        break;
                    case 'reset_password':
                        $loginId = sanitizeInput($_POST['login_id'] ?? '');
                        $newPassword = $_POST['new_password'] ?? '';
                        $confirmPassword = $_POST['confirm_password'] ?? '';
                        if ($newPassword !== $confirmPassword) { $error = 'Passwords do not match.'; break; }
                        if (strlen($newPassword) < 6) { $error = 'Password must be at least 6 characters.'; break; }
                        $result = $userModel->updatePassword($loginId, $newPassword);
                        if ($result) { $success = 'Password reset successfully!'; } else { $error = 'Failed to reset password.'; }
                        break;
                    case 'delete_user':
                        $loginId = sanitizeInput($_POST['login_id'] ?? '');
                        if (empty($loginId)) { $error = 'Login ID is required.'; break; }
                        if ($loginId === 'admin') { $error = 'Cannot delete default admin.'; break; }
                        $result = $userModel->deleteByLoginId($loginId);
                        if ($result) { $success = 'User deleted successfully!'; } else { $error = 'Failed to delete user.'; }
                        break;
                }
            } catch (Exception $e) {
                $error = 'Operation failed. Please try again.';
                error_log('User management error: ' . $e->getMessage());
            }
        }

        // Filters & list
        $search = $_GET['search'] ?? '';
        $filters = [
            'role' => $_GET['role'] ?? '',
            'department' => $_GET['department'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        $users = $this->loadModel('User')->search($search, $filters);

        $data = [
            'pageTitle' => 'User Management',
            'error' => $error,
            'success' => $success,
            'users' => $users,
            'search' => $search,
            'filters' => $filters
        ];
        $this->loadView('header', $data);
        $this->loadView('pages/admin_users', $data);
        $this->loadView('footer');
    }

    public function reports() {
        $data['pageTitle'] = 'Analytics & Reports';
        $this->loadView('header', $data);
        $this->loadView('pages/admin_reports', $data);
        $this->loadView('footer');
    }
}
