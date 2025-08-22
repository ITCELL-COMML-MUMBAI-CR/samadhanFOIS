<?php
/**
 * Customer Home Page
 * Displays news, announcements, and advertisements for logged-in customers
 * Features: Marquee text, simplified layout, responsive design, optimized performance
 */

// Clean the test DB file after successful operation
if (file_exists('../../test_news_db.php')) {
    unlink('../../test_news_db.php');
}
?>

<!-- Marquee Section -->
<?php if (!empty($marqueeItems)): ?>
    <div class="marquee-container">
        <div class="marquee-content">
            <div class="marquee-scroll">
                <?php foreach ($marqueeItems as $item): ?>
                    <span class="marquee-item">
                        <i class="fas fa-bullhorn"></i>
                        <strong><?php echo htmlspecialchars($item['title']); ?>:</strong>
                        <?php echo htmlspecialchars($item['content']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="container-fluid customer-home">
    <!-- News and Announcements Grid -->
    <div class="row mb-5">
        <!-- Latest News -->
        <?php if (!empty($newsItems)): ?>
            <div class="col-lg-6 mb-4">
                <div class="news-section">
                    <h3 class="section-title">
                        <i class="fas fa-newspaper text-primary"></i>
                        Latest News
                    </h3>
                    <div class="news-cards">
                        <?php foreach ($newsItems as $news): ?>
                            <div class="news-card">
                                <div class="news-card-header">
                                    <h5 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h5>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="news-card-body">
                                    <p class="news-content">
                                        <?php echo htmlspecialchars(substr($news['content'], 0, 100)) . '...'; ?>
                                    </p>
                                    <?php if (!empty($news['author_name'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($news['author_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($news['link_url'])): ?>
                                    <div class="news-card-footer">
                                        <a href="<?php echo htmlspecialchars($news['link_url']); ?>" class="btn btn-link btn-sm"
                                            target="_blank">
                                            Read Full Article <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Important Announcements -->
        <?php if (!empty($announcements)): ?>
            <div class="col-lg-6 mb-4">
                <div class="announcements-section">
                    <h3 class="section-title">
                        <i class="fas fa-bullhorn text-warning"></i>
                        Important Announcements
                    </h3>
                    <div class="announcements-cards">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-card">
                                <div class="announcement-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="announcement-content">
                                    <h5 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                    <p class="announcement-text">
                                        <?php echo htmlspecialchars(substr($announcement['content'], 0, 80)) . '...'; ?>
                                    </p>
                                    <div class="announcement-meta">
                                        <?php if (!empty($announcement['author_name'])): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($announcement['author_name']); ?>
                                            </small>
                                        <?php endif; ?>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('M d', strtotime($announcement['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php if (!empty($announcement['link_url'])): ?>
                                    <div class="announcement-action">
                                        <a href="<?php echo htmlspecialchars($announcement['link_url']); ?>"
                                            class="btn btn-warning btn-sm" target="_blank">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Advertisements Banner -->
    <?php if (!empty($advertisements)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="section-title">
                    <i class="fas fa-ad text-success"></i>
                    Special Notices
                </h3>
                <div class="advertisements-banner">
                    <div class="advertisement-grid">
                        <?php foreach ($advertisements as $ad): ?>
                            <div class="advertisement-card">
                                <?php if (!empty($ad['image_url'])): ?>
                                    <div class="advertisement-image">
                                        <img src="<?php echo htmlspecialchars($ad['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($ad['title']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="advertisement-content">
                                    <h4 class="advertisement-title"><?php echo htmlspecialchars($ad['title']); ?></h4>
                                    <p class="advertisement-text"><?php echo htmlspecialchars($ad['content']); ?></p>
                                    <?php if (!empty($ad['link_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($ad['link_url']); ?>"
                                            class="btn btn-success btn-sm" target="_blank">
                                            Learn More <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- External Quick Links Only -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="quick-actions-section">
                <h3 class="section-title">
                    <i class="fas fa-external-link-alt text-info"></i>
                    External Links
                </h3>
                <div class="quick-actions-grid">
                    <?php if (!empty($quickLinks)): ?>
                        <?php foreach ($quickLinks as $link): ?>
                            <?php
                            // Only show external links (not internal pages)
                            $url = $link['url'];
                            if (!preg_match('/^https?:\/\//', $url)) {
                                continue; // Skip internal links
                            }

                            // Determine CSS class based on category
                            $categoryClass = '';
                            switch ($link['category']) {
                                case 'railway':
                                    $categoryClass = 'railway-system';
                                    break;
                                case 'external':
                                    $categoryClass = 'external-system';
                                    break;
                                default:
                                    $categoryClass = 'external-system';
                            }
                            ?>
                            <a href="<?php echo htmlspecialchars($url); ?>"
                                class="quick-action-card <?php echo $categoryClass; ?>"
                                target="<?php echo htmlspecialchars($link['target']); ?>">
                                <div class="quick-action-icon">
                                    <?php if ($link['icon_type'] === 'upload' && !empty($link['icon_path'])): ?>
                                        <img src="<?php echo BASE_URL . htmlspecialchars($link['icon_path']); ?>"
                                            alt="<?php echo htmlspecialchars($link['title']); ?>"
                                            style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <i class="<?php echo htmlspecialchars($link['icon_class'] ?: 'fas fa-link'); ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="quick-action-content">
                                    <h5><?php echo htmlspecialchars($link['title']); ?></h5>
                                    <p><?php echo htmlspecialchars($link['description'] ?: 'Click to access'); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- New Support Ticket Section -->
    <div class="container-fluid mb-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="new-support-ticket-section text-center">
                    <div class="support-ticket-card">
                        <div class="support-ticket-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h3 class="support-ticket-title">Need Help?</h3>
                        <p class="support-ticket-description">
                            Submit a new support ticket for assistance with your freight-related issues.
                        </p>
                        <a href="<?php echo BASE_URL; ?>support/new" class="btn btn-railway-primary btn-lg">
                            <i class="fas fa-headset"></i> Create New Support Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load optimized CSS and JS files -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/customer_home.css">
<script src="<?php echo BASE_URL; ?>js/customer_home.js"></script>