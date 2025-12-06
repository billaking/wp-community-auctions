<?php
/**
 * Auction Manager Dashboard - Modern Responsive Design
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
global $wpdb;
$auctions_table = $wpdb->prefix . 'posts';
$bids_table = $wpdb->prefix . 'bk_auction_bids';

$total_auctions = wp_count_posts('bk_auction_auction')->publish;
$active_auctions = $wpdb->get_var("
    SELECT COUNT(*) FROM {$auctions_table} p
    LEFT JOIN {$wpdb->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_bk_auction_start_date'
    LEFT JOIN {$wpdb->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_bk_auction_end_date'
    WHERE p.post_type = 'bk_auction_auction'
    AND p.post_status = 'publish'
    AND pm1.meta_value <= NOW()
    AND pm2.meta_value >= NOW()
");

$total_bids = $wpdb->get_var("SELECT COUNT(*) FROM {$bids_table}");
$total_bidders = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$bids_table}");

// Recent bids
$recent_bids = $wpdb->get_results("
    SELECT b.*, p.post_title as auction_title, u.display_name
    FROM {$bids_table} b
    LEFT JOIN {$auctions_table} p ON b.auction_id = p.ID
    LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
    ORDER BY b.bid_time DESC
    LIMIT 10
");

// Upcoming auctions
$upcoming_auctions = $wpdb->get_results("
    SELECT p.ID, p.post_title, pm.meta_value as start_date
    FROM {$auctions_table} p
    LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_bk_auction_start_date'
    WHERE p.post_type = 'bk_auction_auction'
    AND p.post_status = 'publish'
    AND pm.meta_value > NOW()
    ORDER BY pm.meta_value ASC
    LIMIT 5
");
?>

<div class="wrap wcam-dashboard">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-hammer" style="font-size: 28px; color: #2271b1; vertical-align: middle;"></span>
        <?php _e('Auction Manager Dashboard', 'bk-auction-manager'); ?>
    </h1>

    <a href="<?php echo admin_url('post-new.php?post_type=bk_auction_auction'); ?>" class="page-title-action wcam-btn-primary">
        <span class="dashicons dashicons-plus-alt"></span>
        <?php _e('Add New Auction', 'bk-auction-manager'); ?>
    </a>

    <hr class="wp-header-end">

    <!-- Statistics Cards -->
    <div class="wcam-dashboard-stats">
        <div class="wcam-stat-card wcam-stat-primary">
            <div class="wcam-stat-icon dashicons dashicons-megaphone"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($total_auctions); ?></h3>
                <p><?php _e('Total Auctions', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-active">
            <div class="wcam-stat-icon dashicons dashicons-hourglass"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($active_auctions); ?></h3>
                <p><?php _e('Active Auctions', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-bids">
            <div class="wcam-stat-icon dashicons dashicons-money-alt"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($total_bids); ?></h3>
                <p><?php _e('Total Bids', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-ended">
            <div class="wcam-stat-icon dashicons dashicons-groups"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($total_bidders); ?></h3>
                <p><?php _e('Active Bidders', 'bk-auction-manager'); ?></p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="wcam-dashboard-grid">
        <!-- Recent Bids -->
        <div class="wcam-dashboard-section">
            <h2>
                <span class="dashicons dashicons-money-alt"></span>
                <?php _e('Recent Bids', 'bk-auction-manager'); ?>
            </h2>

            <?php if (!empty($recent_bids)): ?>
                <table class="wcam-table">
                    <thead>
                        <tr>
                            <th><?php _e('Bidder', 'bk-auction-manager'); ?></th>
                            <th><?php _e('Auction', 'bk-auction-manager'); ?></th>
                            <th><?php _e('Amount', 'bk-auction-manager'); ?></th>
                            <th><?php _e('Time', 'bk-auction-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bids as $bid): ?>
                            <tr>
                                <td><strong><?php echo esc_html($bid->display_name); ?></strong></td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($bid->auction_id); ?>">
                                        <?php echo esc_html($bid->auction_title); ?>
                                    </a>
                                </td>
                                <td class="wcam-amount-income">
                                    <?php echo bk_auction_format_price($bid->bid_amount); ?>
                                </td>
                                <td><?php echo human_time_diff(strtotime($bid->bid_time), current_time('timestamp')) . ' ' . __('ago', 'bk-auction-manager'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="wcam-message wcam-message-info">
                    <p><?php _e('No bids yet. Bids will appear here as they are placed.', 'bk-auction-manager'); ?></p>
                </div>
            <?php endif; ?>

            <p style="text-align: right; margin-top: 15px;">
                <a href="<?php echo admin_url('admin.php?page=wcam-bids'); ?>" class="wcam-btn wcam-btn-primary">
                    <?php _e('View All Bids', 'bk-auction-manager'); ?> →
                </a>
            </p>
        </div>

        <!-- Upcoming Auctions -->
        <div class="wcam-dashboard-section">
            <h2>
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php _e('Upcoming Auctions', 'bk-auction-manager'); ?>
            </h2>

            <?php if (!empty($upcoming_auctions)): ?>
                <div class="wcam-upcoming-list">
                    <?php foreach ($upcoming_auctions as $auction): ?>
                        <div class="wcam-upcoming-item">
                            <div class="wcam-upcoming-icon">
                                <span class="dashicons dashicons-megaphone"></span>
                            </div>
                            <div class="wcam-upcoming-content">
                                <h4>
                                    <a href="<?php echo get_edit_post_link($auction->ID); ?>">
                                        <?php echo esc_html($auction->post_title); ?>
                                    </a>
                                </h4>
                                <p class="wcam-upcoming-date">
                                    <span class="dashicons dashicons-clock"></span>
                                    <?php echo __('Starts', 'bk-auction-manager') . ': ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($auction->start_date)); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="wcam-message wcam-message-info">
                    <p><?php _e('No upcoming auctions scheduled.', 'bk-auction-manager'); ?></p>
                </div>
            <?php endif; ?>

            <p style="text-align: right; margin-top: 15px;">
                <a href="<?php echo admin_url('edit.php?post_type=bk_auction_auction'); ?>" class="wcam-btn wcam-btn-primary">
                    <?php _e('View All Auctions', 'bk-auction-manager'); ?> →
                </a>
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="wcam-dashboard-section wcam-quick-actions">
        <h2>
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Quick Actions', 'bk-auction-manager'); ?>
        </h2>

        <div class="wcam-action-grid">
            <a href="<?php echo admin_url('post-new.php?post_type=bk_auction_auction'); ?>" class="wcam-action-card">
                <span class="dashicons dashicons-plus-alt"></span>
                <h3><?php _e('Create Auction', 'bk-auction-manager'); ?></h3>
                <p><?php _e('Add a new auction item', 'bk-auction-manager'); ?></p>
            </a>

            <a href="<?php echo admin_url('admin.php?page=wcam-categories'); ?>" class="wcam-action-card">
                <span class="dashicons dashicons-category"></span>
                <h3><?php _e('Manage Categories', 'bk-auction-manager'); ?></h3>
                <p><?php _e('Organize auction items', 'bk-auction-manager'); ?></p>
            </a>

            <a href="<?php echo admin_url('admin.php?page=wcam-reports'); ?>" class="wcam-action-card">
                <span class="dashicons dashicons-chart-bar"></span>
                <h3><?php _e('View Reports', 'bk-auction-manager'); ?></h3>
                <p><?php _e('Analyze auction performance', 'bk-auction-manager'); ?></p>
            </a>

            <a href="<?php echo admin_url('admin.php?page=wcam-settings'); ?>" class="wcam-action-card">
                <span class="dashicons dashicons-admin-settings"></span>
                <h3><?php _e('Settings', 'bk-auction-manager'); ?></h3>
                <p><?php _e('Configure auction options', 'bk-auction-manager'); ?></p>
            </a>
        </div>
    </div>

    <!-- Help & Instructions -->
    <div class="wcam-dashboard-section wcam-message wcam-message-info">
        <h3>
            <span class="dashicons dashicons-info"></span>
            <?php _e('Getting Started', 'bk-auction-manager'); ?>
        </h3>
        <ul style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
            <li><strong><?php _e('Create Auctions:', 'bk-auction-manager'); ?></strong> <?php _e('Click "Add New Auction" to create your first auction item with photos, descriptions, and bidding rules.', 'bk-auction-manager'); ?></li>
            <li><strong><?php _e('Set Categories:', 'bk-auction-manager'); ?></strong> <?php _e('Organize items into categories like Art, Electronics, or Services for easier browsing.', 'bk-auction-manager'); ?></li>
            <li><strong><?php _e('Monitor Bids:', 'bk-auction-manager'); ?></strong> <?php _e('Track all bids in real-time from the "All Bids" page. Get notifications when new bids are placed.', 'bk-auction-manager'); ?></li>
            <li><strong><?php _e('Analyze Performance:', 'bk-auction-manager'); ?></strong> <?php _e('Use the Reports page to see which auctions are performing best and bidder engagement.', 'bk-auction-manager'); ?></li>
            <li><strong><?php _e('Configure Settings:', 'bk-auction-manager'); ?></strong> <?php _e('Customize currency, email notifications, and auction rules in Settings.', 'bk-auction-manager'); ?></li>
        </ul>
    </div>
</div>

<style>
.wcam-dashboard {
    padding: 20px;
}

.wcam-dashboard h1 {
    margin-bottom: 20px;
}

.wcam-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.wcam-upcoming-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.wcam-upcoming-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 4px solid #2271b1;
    transition: all 0.2s;
}

.wcam-upcoming-item:hover {
    background: #f0f0f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wcam-upcoming-icon {
    font-size: 32px;
    color: #2271b1;
}

.wcam-upcoming-icon .dashicons {
    font-size: 32px;
}

.wcam-upcoming-content h4 {
    margin: 0 0 5px;
    font-size: 16px;
}

.wcam-upcoming-content h4 a {
    color: #2271b1;
    text-decoration: none;
}

.wcam-upcoming-content h4 a:hover {
    color: #135e96;
}

.wcam-upcoming-date {
    margin: 0;
    color: #666;
    font-size: 13px;
}

.wcam-upcoming-date .dashicons {
    font-size: 14px;
    vertical-align: middle;
}

.wcam-quick-actions .wcam-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.wcam-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 30px 20px;
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s;
}

.wcam-action-card:hover {
    border-color: #2271b1;
    box-shadow: 0 4px 12px rgba(34, 113, 177, 0.15);
    transform: translateY(-2px);
}

.wcam-action-card .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #2271b1;
    margin-bottom: 15px;
}

.wcam-action-card h3 {
    margin: 0 0 8px;
    font-size: 16px;
    color: #23282d;
}

.wcam-action-card p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

@media (max-width: 782px) {
    .wcam-dashboard-grid {
        grid-template-columns: 1fr;
    }

    .wcam-quick-actions .wcam-action-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
