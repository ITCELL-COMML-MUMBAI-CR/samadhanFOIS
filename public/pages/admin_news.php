<?php
/**
 * Admin News & Announcements Management Page
 * Allows administrators to manage news, announcements and advertisements for the customer home page
 */
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-newspaper text-primary"></i> News & Announcements Management</h1>
            <button type="button" class="btn btn-railway-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                <i class="fas fa-plus"></i> Add News
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-primary text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['total']; ?></h3>
                    <p class="mb-0">Total Items</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-success text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['active']; ?></h3>
                    <p class="mb-0">Active</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-warning text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold"><?php echo $stats['featured']; ?></h3>
                    <p class="mb-0">Featured</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card dashboard-card card-info text-center">
                <div class="card-body">
                    <h3 class="display-6 fw-bold">
                        <?php echo ($stats['by_type']['news'] ?? 0) + ($stats['by_type']['announcement'] ?? 0) + ($stats['by_type']['advertisement'] ?? 0); ?>
                    </h3>
                    <p class="mb-0">Published</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search by title, content, or author" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="news" <?php echo ($filters['type'] ?? '') === 'news' ? 'selected' : ''; ?>>News</option>
                        <option value="announcement" <?php echo ($filters['type'] ?? '') === 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                        <option value="advertisement" <?php echo ($filters['type'] ?? '') === 'advertisement' ? 'selected' : ''; ?>>Advertisement</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="archived" <?php echo ($filters['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="featured" class="form-select">
                        <option value="">All</option>
                        <option value="1" <?php echo ($filters['featured'] ?? '') === '1' ? 'selected' : ''; ?>>Featured</option>
                        <option value="0" <?php echo ($filters['featured'] ?? '') === '0' ? 'selected' : ''; ?>>Not Featured</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-railway-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0">Bulk Actions</h6>
                </div>
                <div class="col-md-6 text-end">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="archive_expired">
                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Archive all expired news items?')">
                            <i class="fas fa-archive"></i> Archive Expired
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- News List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">News & Announcements List</h5>
        </div>
        <div class="card-body">
            <?php if (empty($newsList)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No news items found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Author</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newsList as $news): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($news['title']); ?></strong>
                                                <?php if ($news['featured']): ?>
                                                    <span class="badge bg-warning ms-2">Featured</span>
                                                <?php endif; ?>
                                                <?php if ($news['show_in_marquee']): ?>
                                                    <span class="badge bg-info ms-1">Marquee</span>
                                                <?php endif; ?>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars(substr($news['content'], 0, 100)) . '...'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $news['type'] === 'news' ? 'primary' : 
                                                ($news['type'] === 'announcement' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($news['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $news['status'] === 'active' ? 'success' : 
                                                ($news['status'] === 'inactive' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($news['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $news['priority']; ?></td>
                                    <td><?php echo htmlspecialchars($news['author_name'] ?? 'Unknown'); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editNewsModal"
                                                    onclick="loadNewsForEdit(<?php echo htmlspecialchars(json_encode($news)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewNewsModal"
                                                    onclick="showNewsDetails(<?php echo htmlspecialchars(json_encode($news)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this news item?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add News Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="add_title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="add_title" name="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="add_type" class="form-label">Type *</label>
                            <select class="form-select" id="add_type" name="type" required>
                                <option value="news">News</option>
                                <option value="announcement">Announcement</option>
                                <option value="advertisement">Advertisement</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_content" class="form-label">Content *</label>
                        <textarea class="form-control" id="add_content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="add_status" class="form-label">Status</label>
                            <select class="form-select" id="add_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="add_priority" class="form-label">Priority</label>
                            <input type="number" class="form-control" id="add_priority" name="priority" value="0" min="0" max="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="add_author" class="form-label">Author Name</label>
                            <input type="text" class="form-control" id="add_author" name="author_name">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_image_url" class="form-label">Image URL (Optional)</label>
                            <input type="url" class="form-control" id="add_image_url" name="image_url">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_link_url" class="form-label">Link URL (Optional)</label>
                            <input type="url" class="form-control" id="add_link_url" name="link_url">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_publish_date" class="form-label">Publish Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="add_publish_date" name="publish_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_expire_date" class="form-label">Expire Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="add_expire_date" name="expire_date">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_featured" name="featured">
                                <label class="form-check-label" for="add_featured">
                                    Featured (Show prominently)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_marquee" name="show_in_marquee">
                                <label class="form-check-label" for="add_marquee">
                                    Show in Marquee
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-railway-primary">Add News Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit News Modal -->
<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit News Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editNewsForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_type" class="form-label">Type *</label>
                            <select class="form-select" id="edit_type" name="type" required>
                                <option value="news">News</option>
                                <option value="announcement">Announcement</option>
                                <option value="advertisement">Advertisement</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Content *</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_priority" class="form-label">Priority</label>
                            <input type="number" class="form-control" id="edit_priority" name="priority" min="0" max="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_author" class="form-label">Author Name</label>
                            <input type="text" class="form-control" id="edit_author" name="author_name">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_image_url" class="form-label">Image URL (Optional)</label>
                            <input type="url" class="form-control" id="edit_image_url" name="image_url">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_link_url" class="form-label">Link URL (Optional)</label>
                            <input type="url" class="form-control" id="edit_link_url" name="link_url">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_publish_date" class="form-label">Publish Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="edit_publish_date" name="publish_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_expire_date" class="form-label">Expire Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="edit_expire_date" name="expire_date">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_featured" name="featured">
                                <label class="form-check-label" for="edit_featured">
                                    Featured (Show prominently)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_marquee" name="show_in_marquee">
                                <label class="form-check-label" for="edit_marquee">
                                    Show in Marquee
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-railway-primary">Update News Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View News Modal -->
<div class="modal fade" id="viewNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> News Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="newsDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadNewsForEdit(news) {
    document.getElementById('edit_id').value = news.id;
    document.getElementById('edit_title').value = news.title;
    document.getElementById('edit_content').value = news.content;
    document.getElementById('edit_type').value = news.type;
    document.getElementById('edit_status').value = news.status;
    document.getElementById('edit_priority').value = news.priority;
    document.getElementById('edit_author').value = news.author_name || '';
    document.getElementById('edit_image_url').value = news.image_url || '';
    document.getElementById('edit_link_url').value = news.link_url || '';
    
    // Format dates for datetime-local input
    if (news.publish_date) {
        const publishDate = new Date(news.publish_date);
        document.getElementById('edit_publish_date').value = publishDate.toISOString().slice(0, 16);
    }
    if (news.expire_date) {
        const expireDate = new Date(news.expire_date);
        document.getElementById('edit_expire_date').value = expireDate.toISOString().slice(0, 16);
    }
    
    document.getElementById('edit_featured').checked = news.featured == 1;
    document.getElementById('edit_marquee').checked = news.show_in_marquee == 1;
}

function showNewsDetails(news) {
    const content = `
        <div class="row">
            <div class="col-12">
                <h4>${news.title}</h4>
                <div class="mb-3">
                    <span class="badge bg-${news.type === 'news' ? 'primary' : (news.type === 'announcement' ? 'warning' : 'success')} me-2">${news.type.charAt(0).toUpperCase() + news.type.slice(1)}</span>
                    <span class="badge bg-${news.status === 'active' ? 'success' : (news.status === 'inactive' ? 'warning' : 'secondary')} me-2">${news.status.charAt(0).toUpperCase() + news.status.slice(1)}</span>
                    ${news.featured == 1 ? '<span class="badge bg-warning me-2">Featured</span>' : ''}
                    ${news.show_in_marquee == 1 ? '<span class="badge bg-info me-2">Marquee</span>' : ''}
                </div>
                <div class="mb-3">
                    <strong>Content:</strong>
                    <p class="mt-2">${news.content}</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Author:</strong> ${news.author_name || 'Unknown'}<br>
                        <strong>Priority:</strong> ${news.priority}<br>
                        <strong>Views:</strong> ${news.views}
                    </div>
                    <div class="col-md-6">
                        <strong>Created:</strong> ${new Date(news.created_at).toLocaleString()}<br>
                        <strong>Updated:</strong> ${new Date(news.updated_at).toLocaleString()}
                        ${news.publish_date ? '<br><strong>Publish Date:</strong> ' + new Date(news.publish_date).toLocaleString() : ''}
                        ${news.expire_date ? '<br><strong>Expire Date:</strong> ' + new Date(news.expire_date).toLocaleString() : ''}
                    </div>
                </div>
                ${news.image_url ? '<div class="mt-3"><strong>Image URL:</strong> <a href="' + news.image_url + '" target="_blank">' + news.image_url + '</a></div>' : ''}
                ${news.link_url ? '<div class="mt-2"><strong>Link URL:</strong> <a href="' + news.link_url + '" target="_blank">' + news.link_url + '</a></div>' : ''}
            </div>
        </div>
    `;
    document.getElementById('newsDetailsContent').innerHTML = content;
}
</script>
