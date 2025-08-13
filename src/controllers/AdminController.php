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
}
