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

    public function news() {
        $newsModel = $this->loadModel('News');
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            list($error, $success) = $this->handleNewsAction($newsModel);
        }

        // Filters and search
        $search = $_GET['search'] ?? '';
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'featured' => $_GET['featured'] ?? ''
        ];

        // Get news list with filters
        $conditions = [];
        if (!empty($search)) {
            // For search, we'll use a custom query
            $sql = "SELECT * FROM news WHERE (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            
            if (!empty($filters['type'])) {
                $sql .= " AND type = ?";
                $params[] = $filters['type'];
            }
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['featured'])) {
                $sql .= " AND featured = ?";
                $params[] = (int)$filters['featured'];
            }
            
            $sql .= " ORDER BY created_at DESC";
            $newsList = $newsModel->query($sql, $params);
        } else {
            // Apply filters to conditions
            if (!empty($filters['type'])) {
                $conditions['type'] = $filters['type'];
            }
            if (!empty($filters['status'])) {
                $conditions['status'] = $filters['status'];
            }
            if (!empty($filters['featured'])) {
                $conditions['featured'] = (int)$filters['featured'];
            }
            
            $newsList = $newsModel->findAll($conditions);
        }

        // Handle edit action
        $action = $_GET['action'] ?? 'list';
        $editNews = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $editNews = $newsModel->find((int)$_GET['id']);
        }

        $data = [
            'newsList' => $newsList,
            'stats' => $newsModel->getStatistics(),
            'pageTitle' => 'News & Announcements Management',
            'error' => $error,
            'success' => $success,
            'search' => $search,
            'filters' => $filters,
            'action' => $action,
            'editNews' => $editNews
        ];

        $this->loadView('header', $data);
        $this->loadView('pages/admin_news', $data);
        $this->loadView('footer');
    }

    private function handleNewsAction($newsModel) {
        $postAction = $_POST['action'] ?? '';
        try {
            switch ($postAction) {
                case 'add':
                    $data = [
                        'title' => sanitizeInput($_POST['title'] ?? ''),
                        'content' => sanitizeInput($_POST['content'] ?? ''),
                        'type' => sanitizeInput($_POST['type'] ?? 'news'),
                        'status' => sanitizeInput($_POST['status'] ?? 'active'),
                        'priority' => (int)($_POST['priority'] ?? 0),
                        'featured' => isset($_POST['featured']) ? 1 : 0,
                        'show_in_marquee' => isset($_POST['show_in_marquee']) ? 1 : 0,
                        'image_url' => sanitizeInput($_POST['image_url'] ?? ''),
                        'link_url' => sanitizeInput($_POST['link_url'] ?? ''),
                        'author_name' => sanitizeInput($_POST['author_name'] ?? ''),
                        'publish_date' => !empty($_POST['publish_date']) ? $_POST['publish_date'] : null,
                        'expire_date' => !empty($_POST['expire_date']) ? $_POST['expire_date'] : null
                    ];
                    
                    if (empty($data['title']) || empty($data['content'])) {
                        return ['Title and content are required.', ''];
                    }
                    
                    $result = $newsModel->createNews($data);
                    return $result ? ['', 'News item created successfully!'] : ['Failed to create news item.', ''];

                case 'edit':
                    $id = (int)($_POST['id'] ?? 0);
                    $data = [
                        'title' => sanitizeInput($_POST['title'] ?? ''),
                        'content' => sanitizeInput($_POST['content'] ?? ''),
                        'type' => sanitizeInput($_POST['type'] ?? 'news'),
                        'status' => sanitizeInput($_POST['status'] ?? 'active'),
                        'priority' => (int)($_POST['priority'] ?? 0),
                        'featured' => isset($_POST['featured']) ? 1 : 0,
                        'show_in_marquee' => isset($_POST['show_in_marquee']) ? 1 : 0,
                        'image_url' => sanitizeInput($_POST['image_url'] ?? ''),
                        'link_url' => sanitizeInput($_POST['link_url'] ?? ''),
                        'author_name' => sanitizeInput($_POST['author_name'] ?? ''),
                        'publish_date' => !empty($_POST['publish_date']) ? $_POST['publish_date'] : null,
                        'expire_date' => !empty($_POST['expire_date']) ? $_POST['expire_date'] : null
                    ];
                    
                    if (empty($data['title']) || empty($data['content'])) {
                        return ['Title and content are required.', ''];
                    }
                    
                    $result = $newsModel->updateNews($id, $data);
                    return $result ? ['', 'News item updated successfully!'] : ['Failed to update news item.', ''];

                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    $result = $newsModel->delete($id);
                    return $result ? ['', 'News item deleted successfully!'] : ['Failed to delete news item.', ''];

                case 'archive_expired':
                    $result = $newsModel->archiveExpiredNews();
                    return $result ? ['', 'Expired news items archived successfully!'] : ['No expired items found or archive failed.', ''];
            }
        } catch (Exception $e) {
            error_log('News management error: ' . $e->getMessage());
            return ['An error occurred. Please try again.', ''];
        }
        return ['', ''];
    }

    public function quicklinks() {
        $quickLinkModel = $this->loadModel('QuickLink');
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            list($error, $success) = $this->handleQuickLinkAction($quickLinkModel);
        }

        // Filters and search
        $search = $_GET['search'] ?? '';
        $filters = [
            'category' => $_GET['category'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        // Get quick links list with filters
        $conditions = [];
        if (!empty($search)) {
            // For search, we'll use a custom query
            $sql = "SELECT * FROM quick_links WHERE (title LIKE ? OR description LIKE ? OR url LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
            
            if (!empty($filters['category'])) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            $sql .= " ORDER BY category ASC, position ASC, created_at DESC";
            $quickLinksList = $quickLinkModel->query($sql, $params);
        } else {
            // Apply filters to conditions
            if (!empty($filters['category'])) {
                $conditions['category'] = $filters['category'];
            }
            if (!empty($filters['status'])) {
                $conditions['status'] = $filters['status'];
            }
            
            $quickLinksList = $quickLinkModel->findAll($conditions, 'category ASC, position ASC, created_at DESC');
        }

        // Handle edit action
        $action = $_GET['action'] ?? 'list';
        $editQuickLink = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $editQuickLink = $quickLinkModel->find((int)$_GET['id']);
        }

        $data = [
            'quickLinksList' => $quickLinksList,
            'stats' => $quickLinkModel->getStatistics(),
            'iconSuggestions' => $quickLinkModel->getIconSuggestions(),
            'pageTitle' => 'Quick Links Management',
            'error' => $error,
            'success' => $success,
            'search' => $search,
            'filters' => $filters,
            'action' => $action,
            'editQuickLink' => $editQuickLink
        ];

        $this->loadView('header', $data);
        $this->loadView('pages/admin_quick_links', $data);
        $this->loadView('footer');
    }

    private function handleQuickLinkAction($quickLinkModel) {
        $postAction = $_POST['action'] ?? '';
        try {
            switch ($postAction) {
                case 'add':
                    // Handle icon upload if provided
                    $iconPath = null;
                    if (isset($_FILES['icon_upload']) && $_FILES['icon_upload']['error'] === UPLOAD_ERR_OK) {
                        try {
                            $iconPath = $quickLinkModel->uploadIcon($_FILES['icon_upload']);
                        } catch (Exception $e) {
                            return ['Icon upload failed: ' . $e->getMessage(), ''];
                        }
                    }

                    $data = [
                        'title' => sanitizeInput($_POST['title'] ?? ''),
                        'description' => sanitizeInput($_POST['description'] ?? ''),
                        'url' => sanitizeInput($_POST['url'] ?? ''),
                        'category' => sanitizeInput($_POST['category'] ?? 'system'),
                        'icon_type' => sanitizeInput($_POST['icon_type'] ?? 'fontawesome'),
                        'icon_class' => sanitizeInput($_POST['icon_class'] ?? ''),
                        'icon_path' => $iconPath,
                        'position' => (int)($_POST['position'] ?? 0),
                        'target' => sanitizeInput($_POST['target'] ?? '_self'),
                        'status' => sanitizeInput($_POST['status'] ?? 'active'),
                        'author_name' => sanitizeInput($_POST['author_name'] ?? '')
                    ];
                    
                    if (empty($data['title']) || empty($data['url'])) {
                        return ['Title and URL are required.', ''];
                    }
                    
                    $result = $quickLinkModel->createLink($data);
                    return $result ? ['', 'Quick link created successfully!'] : ['Failed to create quick link.', ''];

                case 'edit':
                    $id = (int)($_POST['id'] ?? 0);
                    
                    // Handle icon upload if provided
                    $iconPath = null;
                    if (isset($_FILES['icon_upload']) && $_FILES['icon_upload']['error'] === UPLOAD_ERR_OK) {
                        try {
                            $iconPath = $quickLinkModel->uploadIcon($_FILES['icon_upload']);
                            
                            // Delete old icon if exists
                            $oldLink = $quickLinkModel->find($id);
                            if ($oldLink && !empty($oldLink['icon_path'])) {
                                $quickLinkModel->deleteIcon($oldLink['icon_path']);
                            }
                        } catch (Exception $e) {
                            return ['Icon upload failed: ' . $e->getMessage(), ''];
                        }
                    }

                    $data = [
                        'title' => sanitizeInput($_POST['title'] ?? ''),
                        'description' => sanitizeInput($_POST['description'] ?? ''),
                        'url' => sanitizeInput($_POST['url'] ?? ''),
                        'category' => sanitizeInput($_POST['category'] ?? 'system'),
                        'icon_type' => sanitizeInput($_POST['icon_type'] ?? 'fontawesome'),
                        'icon_class' => sanitizeInput($_POST['icon_class'] ?? ''),
                        'position' => (int)($_POST['position'] ?? 0),
                        'target' => sanitizeInput($_POST['target'] ?? '_self'),
                        'status' => sanitizeInput($_POST['status'] ?? 'active'),
                        'author_name' => sanitizeInput($_POST['author_name'] ?? '')
                    ];
                    
                    // Only update icon_path if a new one was uploaded
                    if ($iconPath) {
                        $data['icon_path'] = $iconPath;
                    }
                    
                    if (empty($data['title']) || empty($data['url'])) {
                        return ['Title and URL are required.', ''];
                    }
                    
                    $result = $quickLinkModel->updateLink($id, $data);
                    return $result ? ['', 'Quick link updated successfully!'] : ['Failed to update quick link.', ''];

                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    
                    // Delete associated icon file
                    $link = $quickLinkModel->find($id);
                    if ($link && !empty($link['icon_path'])) {
                        $quickLinkModel->deleteIcon($link['icon_path']);
                    }
                    
                    $result = $quickLinkModel->delete($id);
                    return $result ? ['', 'Quick link deleted successfully!'] : ['Failed to delete quick link.', ''];

                case 'update_positions':
                    $positions = $_POST['positions'] ?? [];
                    if (!empty($positions)) {
                        $quickLinkModel->updatePositions($positions);
                        return ['', 'Link positions updated successfully!'];
                    }
                    return ['No position data received.', ''];
            }
        } catch (Exception $e) {
            error_log('Quick link management error: ' . $e->getMessage());
            return ['An error occurred. Please try again.', ''];
        }
        return ['', ''];
    }
}
