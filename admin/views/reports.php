<?php
/**
 * Reports Page - Analytics & Insights
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$bids_table = $wpdb->prefix . 'bk_auction_bids';

// Date range filter
$date_range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : '30';
$date_condition = '';
if ($date_range !== 'all') {
    $days_ago = intval($date_range);
    $date_condition = $wpdb->prepare(
        "AND bid_time >= DATE_SUB(NOW(), INTERVAL %d DAY)",
        $days_ago
    );
}

// Top Performing Auctions
$top_auctions = $wpdb->get_results("
    SELECT
        p.ID,
        p.post_title,
        COUNT(b.id) as total_bids,
        MAX(b.bid_amount) as highest_bid,
        COUNT(DISTINCT b.user_id) as unique_bidders
    FROM {$wpdb->posts} p
    LEFT JOIN {$bids_table} b ON p.ID = b.auction_id
    WHERE p.post_type = 'auction' AND p.post_status = 'publish' {$date_condition}
    GROUP BY p.ID
    ORDER BY total_bids DESC
    LIMIT 10
");

// Top Bidders
$top_bidders = $wpdb->get_results("
    SELECT
        u.ID,
        u.display_name,
        u.user_email,
        COUNT(b.id) as total_bids,
        SUM(b.bid_amount) as total_bid_amount,
        MAX(b.bid_time) as last_bid
    FROM {$wpdb->users} u
    INNER JOIN {$bids_table} b ON u.ID = b.user_id
    WHERE 1=1 {$date_condition}
    GROUP BY u.ID
    ORDER BY total_bids DESC
    LIMIT 10
");

// Bidding Activity by Day
$activity_by_day = $wpdb->get_results("
    SELECT
        DATE(bid_time) as bid_date,
        COUNT(id) as bid_count,
        SUM(bid_amount) as total_amount
    FROM {$bids_table}
    WHERE 1=1 {$date_condition}
    GROUP BY DATE(bid_time)
    ORDER BY bid_date DESC
    LIMIT 30
");

// Overall Statistics
$stats = array(
    'total_auctions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'auction' AND post_status = 'publish'"),
    'active_auctions' => $wpdb->get_var("
        SELECT COUNT(*) FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_auction_start_date'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_auction_end_date'
        WHERE p.post_type = 'auction'
        AND p.post_status = 'publish'
        AND pm1.meta_value <= NOW()
        AND pm2.meta_value >= NOW()
    "),
    'total_bids' => $wpdb->get_var("SELECT COUNT(*) FROM {$bids_table} WHERE 1=1 {$date_condition}"),
    'total_bidders' => $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$bids_table} WHERE 1=1 {$date_condition}"),
    'total_bid_value' => $wpdb->get_var("SELECT SUM(bid_amount) FROM {$bids_table} WHERE 1=1 {$date_condition}"),
    'avg_bid_value' => $wpdb->get_var("SELECT AVG(bid_amount) FROM {$bids_table} WHERE 1=1 {$date_condition}"),
);
?>

<div class="wrap wcam-reports-page">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-chart-line" style="font-size: 28px; color: #2271b1; vertical-align: middle;"></span>
        <?php _e('Reports & Analytics', 'bk-auction-manager'); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Date Range Filter -->
    <div class="wcam-dashboard-section" style="margin-top: 20px;">
        <div class="wcam-date-filter">
            <label for="date-range"><?php _e('Date Range:', 'bk-auction-manager'); ?></label>
            <select id="date-range" onchange="window.location.href='<?php echo admin_url('admin.php?page=wcam-reports'); ?>&range=' + this.value">
                <option value="7" <?php selected($date_range, '7'); ?>><?php _e('Last 7 Days', 'bk-auction-manager'); ?></option>
                <option value="30" <?php selected($date_range, '30'); ?>><?php _e('Last 30 Days', 'bk-auction-manager'); ?></option>
                <option value="90" <?php selected($date_range, '90'); ?>><?php _e('Last 90 Days', 'bk-auction-manager'); ?></option>
                <option value="365" <?php selected($date_range, '365'); ?>><?php _e('Last Year', 'bk-auction-manager'); ?></option>
                <option value="all" <?php selected($date_range, 'all'); ?>><?php _e('All Time', 'bk-auction-manager'); ?></option>
            </select>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="wcam-dashboard-stats" style="margin: 30px 0;">
        <div class="wcam-stat-card wcam-stat-primary">
            <div class="wcam-stat-icon dashicons dashicons-megaphone"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($stats['total_auctions']); ?></h3>
                <p><?php _e('Total Auctions', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-active">
            <div class="wcam-stat-icon dashicons dashicons-hourglass"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($stats['active_auctions']); ?></h3>
                <p><?php _e('Active Now', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-bids">
            <div class="wcam-stat-icon dashicons dashicons-money-alt"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($stats['total_bids']); ?></h3>
                <p><?php _e('Total Bids', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-ended">
            <div class="wcam-stat-icon dashicons dashicons-groups"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($stats['total_bidders']); ?></h3>
                <p><?php _e('Unique Bidders', 'bk-auction-manager'); ?></p>
            </div>
        </div>
    </div>

    <div class="wcam-dashboard-grid">
        <!-- Left Column -->
        <div class="wcam-grid-left">
            <!-- Top Performing Auctions -->
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-awards"></span>
                    <?php _e('Top Performing Auctions', 'bk-auction-manager'); ?>
                </h2>

                <?php if (!empty($top_auctions)): ?>
                    <table class="wcam-table">
                        <thead>
                            <tr>
                                <th><?php _e('Auction', 'bk-auction-manager'); ?></th>
                                <th><?php _e('Total Bids', 'bk-auction-manager'); ?></th>
                                <th><?php _e('Highest Bid', 'bk-auction-manager'); ?></th>
                                <th><?php _e('Bidders', 'bk-auction-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_auctions as $auction): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($auction->ID); ?>" style="color: #2271b1; font-weight: 500;">
                                            <?php echo esc_html($auction->post_title); ?>
                                        </a>
                                    </td>
                                    <td><strong><?php echo esc_html($auction->total_bids); ?></strong></td>
                                    <td class="wcam-amount-income">
                                        <?php echo bk_auction_format_price($auction->highest_bid); ?>
                                    </td>
                                    <td><?php echo esc_html($auction->unique_bidders); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="wcam-message wcam-message-info">
                        <p><?php _e('No auction data available for this period.', 'bk-auction-manager'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bidding Activity -->
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php _e('Bidding Activity', 'bk-auction-manager'); ?>
                </h2>

                <?php if (!empty($activity_by_day)): ?>
                    <table class="wcam-table">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'bk-auction-manager'); ?></th>
                                <th><?php _e('Bids', 'bk-auction-manager'); ?></th>
                                <th><?php _e('Total Amount', 'bk-auction-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activity_by_day as $day): ?>
                                <tr>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($day->bid_date)); ?></td>
                                    <td><strong><?php echo esc_html($day->bid_count); ?></strong></td>
                                    <td class="wcam-amount-income">
                                        <?php echo bk_auction_format_price($day->total_amount); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="wcam-message wcam-message-info">
                        <p><?php _e('No activity data available for this period.', 'bk-auction-manager'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column -->
        <div class="wcam-grid-right">
            <!-- Top Bidders -->
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-businessman"></span>
                    <?php _e('Top Bidders', 'bk-auction-manager'); ?>
                </h2>

                <?php if (!empty($top_bidders)): ?>
                    <div class="wcam-top-bidders-list">
                        <?php foreach ($top_bidders as $index => $bidder): ?>
                            <div class="wcam-bidder-card">
                                <div class="wcam-bidder-rank">#<?php echo ($index + 1); ?></div>
                                <div class="wcam-bidder-info">
                                    <strong><?php echo esc_html($bidder->display_name); ?></strong><br>
                                    <small style="color: #666;"><?php echo esc_html($bidder->user_email); ?></small>
                                </div>
                                <div class="wcam-bidder-stats">
                                    <div class="wcam-stat-item">
                                        <span class="dashicons dashicons-money-alt"></span>
                                        <strong><?php echo esc_html($bidder->total_bids); ?></strong> <?php _e('bids', 'bk-auction-manager'); ?>
                                    </div>
                                    <div class="wcam-stat-item wcam-amount-income">
                                        <strong><?php echo bk_auction_format_price($bidder->total_bid_amount); ?></strong>
                                    </div>
                                    <div class="wcam-stat-item" style="font-size: 11px; color: #666;">
                                        <?php _e('Last bid:', 'bk-auction-manager'); ?>
                                        <?php echo human_time_diff(strtotime($bidder->last_bid), current_time('timestamp')) . ' ' . __('ago', 'bk-auction-manager'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="wcam-message wcam-message-info">
                        <p><?php _e('No bidder data available for this period.', 'bk-auction-manager'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Key Metrics -->
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-analytics"></span>
                    <?php _e('Key Metrics', 'bk-auction-manager'); ?>
                </h2>

                <div class="wcam-metrics-grid">
                    <div class="wcam-metric-item">
                        <div class="wcam-metric-label"><?php _e('Total Bid Value', 'bk-auction-manager'); ?></div>
                        <div class="wcam-metric-value wcam-amount-income">
                            <?php echo bk_auction_format_price($stats['total_bid_value']); ?>
                        </div>
                    </div>

                    <div class="wcam-metric-item">
                        <div class="wcam-metric-label"><?php _e('Average Bid Amount', 'bk-auction-manager'); ?></div>
                        <div class="wcam-metric-value wcam-amount-income">
                            <?php echo bk_auction_format_price($stats['avg_bid_value']); ?>
                        </div>
                    </div>

                    <div class="wcam-metric-item">
                        <div class="wcam-metric-label"><?php _e('Bids per Auction', 'bk-auction-manager'); ?></div>
                        <div class="wcam-metric-value" style="color: #2271b1;">
                            <?php echo $stats['total_auctions'] > 0 ? number_format($stats['total_bids'] / $stats['total_auctions'], 1) : '0'; ?>
                        </div>
                    </div>

                    <div class="wcam-metric-item">
                        <div class="wcam-metric-label"><?php _e('Bids per Bidder', 'bk-auction-manager'); ?></div>
                        <div class="wcam-metric-value" style="color: #2271b1;">
                            <?php echo $stats['total_bidders'] > 0 ? number_format($stats['total_bids'] / $stats['total_bidders'], 1) : '0'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wcam-reports-page .wcam-date-filter {
    display: flex;
    align-items: center;
    gap: 10px;
}

.wcam-date-filter label {
    font-weight: 500;
    color: #23282d;
}

.wcam-date-filter select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.wcam-top-bidders-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.wcam-bidder-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #2271b1;
}

.wcam-bidder-rank {
    font-size: 24px;
    font-weight: bold;
    color: #2271b1;
    min-width: 40px;
    text-align: center;
}

.wcam-bidder-info {
    flex: 1;
}

.wcam-bidder-stats {
    text-align: right;
}

.wcam-stat-item {
    margin-bottom: 5px;
}

.wcam-stat-item .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    vertical-align: middle;
}

.wcam-metrics-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.wcam-metric-item {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    text-align: center;
}

.wcam-metric-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wcam-metric-value {
    font-size: 24px;
    font-weight: bold;
}

@media (max-width: 782px) {
    .wcam-bidder-card {
        flex-direction: column;
        text-align: center;
    }

    .wcam-bidder-stats {
        text-align: center;
    }

    .wcam-metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
