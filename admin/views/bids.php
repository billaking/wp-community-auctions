<?php
/**
 * All Bids Page - Modern Responsive Design
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get all bids
global $wpdb;
$bids_table = $wpdb->prefix . 'bk_auction_bids';
$posts_table = $wpdb->prefix . 'posts';

$per_page = 20;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $per_page;

// Filter by status
$status_filter = isset($_GET['bid_status']) ? sanitize_text_field($_GET['bid_status']) : 'all';
$where_status = $status_filter !== 'all' ? $wpdb->prepare("AND b.status = %s", $status_filter) : '';

// Search
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$where_search = '';
if (!empty($search)) {
    $where_search = $wpdb->prepare(
        "AND (p.post_title LIKE %s OR u.display_name LIKE %s)",
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%'
    );
}

$total_bids = $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$bids_table} b
    LEFT JOIN {$posts_table} p ON b.auction_id = p.ID
    LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
    WHERE 1=1 {$where_status} {$where_search}
");

$bids = $wpdb->get_results("
    SELECT b.*, p.post_title as auction_title, u.display_name, u.user_email
    FROM {$bids_table} b
    LEFT JOIN {$posts_table} p ON b.auction_id = p.ID
    LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
    WHERE 1=1 {$where_status} {$where_search}
    ORDER BY b.bid_time DESC
    LIMIT {$per_page} OFFSET {$offset}
");

$total_pages = ceil($total_bids / $per_page);
?>

<div class="wrap wcam-bids-page">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-money-alt" style="font-size: 28px; color: #2271b1; vertical-align: middle;"></span>
        <?php _e('All Bids', 'bk-auction-manager'); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="wcam-dashboard-section" style="margin-top: 20px;">
        <div class="wcam-filters">
            <div class="wcam-filter-group">
                <label for="bid-status-filter"><?php _e('Filter by Status:', 'bk-auction-manager'); ?></label>
                <select id="bid-status-filter" onchange="window.location.href='<?php echo admin_url('admin.php?page=wcam-bids'); ?>&bid_status=' + this.value + '<?php echo !empty($search) ? '&s=' . urlencode($search) : ''; ?>'">
                    <option value="all" <?php selected($status_filter, 'all'); ?>><?php _e('All Statuses', 'bk-auction-manager'); ?></option>
                    <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('Active', 'bk-auction-manager'); ?></option>
                    <option value="winning" <?php selected($status_filter, 'winning'); ?>><?php _e('Winning', 'bk-auction-manager'); ?></option>
                    <option value="outbid" <?php selected($status_filter, 'outbid'); ?>><?php _e('Outbid', 'bk-auction-manager'); ?></option>
                </select>
            </div>

            <div class="wcam-filter-group wcam-search-box">
                <form method="get" action="<?php echo admin_url('admin.php'); ?>">
                    <input type="hidden" name="page" value="wcam-bids">
                    <input type="hidden" name="bid_status" value="<?php echo esc_attr($status_filter); ?>">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search bids...', 'bk-auction-manager'); ?>">
                    <button type="submit" class="wcam-btn wcam-btn-primary">
                        <span class="dashicons dashicons-search"></span>
                        <?php _e('Search', 'bk-auction-manager'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="wcam-dashboard-stats" style="margin: 30px 0;">
        <div class="wcam-stat-card wcam-stat-primary">
            <div class="wcam-stat-icon dashicons dashicons-money-alt"></div>
            <div class="wcam-stat-content">
                <h3><?php echo esc_html($total_bids); ?></h3>
                <p><?php _e('Total Bids', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-active">
            <div class="wcam-stat-icon dashicons dashicons-yes-alt"></div>
            <div class="wcam-stat-content">
                <h3><?php
                    echo esc_html($wpdb->get_var("SELECT COUNT(*) FROM {$bids_table} WHERE status = 'active'"));
                ?></h3>
                <p><?php _e('Active Bids', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-bids">
            <div class="wcam-stat-icon dashicons dashicons-groups"></div>
            <div class="wcam-stat-content">
                <h3><?php
                    echo esc_html($wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$bids_table}"));
                ?></h3>
                <p><?php _e('Unique Bidders', 'bk-auction-manager'); ?></p>
            </div>
        </div>

        <div class="wcam-stat-card wcam-stat-ended">
            <div class="wcam-stat-icon dashicons dashicons-chart-line"></div>
            <div class="wcam-stat-content">
                <h3><?php
                    echo bk_auction_format_price($wpdb->get_var("SELECT SUM(bid_amount) FROM {$bids_table}"));
                ?></h3>
                <p><?php _e('Total Bid Value', 'bk-auction-manager'); ?></p>
            </div>
        </div>
    </div>

    <!-- Bids Table -->
    <div class="wcam-dashboard-section">
        <h2>
            <span class="dashicons dashicons-list-view"></span>
            <?php _e('Bid History', 'bk-auction-manager'); ?>
        </h2>

        <?php if (!empty($bids)): ?>
            <table class="wcam-table">
                <thead>
                    <tr>
                        <th><?php _e('Bidder', 'bk-auction-manager'); ?></th>
                        <th><?php _e('Auction', 'bk-auction-manager'); ?></th>
                        <th><?php _e('Amount', 'bk-auction-manager'); ?></th>
                        <th><?php _e('Time', 'bk-auction-manager'); ?></th>
                        <th><?php _e('Status', 'bk-auction-manager'); ?></th>
                        <th><?php _e('Actions', 'bk-auction-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bids as $bid): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($bid->display_name); ?></strong><br>
                                <small style="color: #666;"><?php echo esc_html($bid->user_email); ?></small>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_post_link($bid->auction_id); ?>" style="color: #2271b1;">
                                    <?php echo esc_html($bid->auction_title); ?>
                                </a>
                            </td>
                            <td class="wcam-amount-income">
                                <strong><?php echo bk_auction_format_price($bid->bid_amount); ?></strong>
                            </td>
                            <td>
                                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($bid->bid_time)); ?><br>
                                <small style="color: #666;"><?php echo human_time_diff(strtotime($bid->bid_time), current_time('timestamp')) . ' ' . __('ago', 'bk-auction-manager'); ?></small>
                            </td>
                            <td>
                                <?php
                                $status_class = 'wcam-status-' . esc_attr($bid->status);
                                $status_label = ucfirst($bid->status);
                                ?>
                                <span class="wcam-status-badge <?php echo $status_class; ?>">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo get_permalink($bid->auction_id); ?>" target="_blank" class="wcam-btn wcam-btn-small">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <?php _e('View', 'bk-auction-manager'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="wcam-pagination">
                    <?php
                    $big = 999999999;
                    echo paginate_links(array(
                        'base' => str_replace($big, '%#%', esc_url(add_query_arg('paged', $big))),
                        'format' => '?paged=%#%',
                        'current' => $page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo; ' . __('Previous', 'bk-auction-manager'),
                        'next_text' => __('Next', 'bk-auction-manager') . ' &raquo;',
                    ));
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="wcam-message wcam-message-info">
                <p><?php _e('No bids found.', 'bk-auction-manager'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wcam-bids-page .wcam-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
}

.wcam-filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.wcam-filter-group label {
    font-weight: 500;
    color: #23282d;
}

.wcam-filter-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.wcam-search-box {
    margin-left: auto;
}

.wcam-search-box form {
    display: flex;
    gap: 8px;
}

.wcam-search-box input[type="search"] {
    min-width: 300px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.wcam-btn-small {
    padding: 4px 8px !important;
    font-size: 12px !important;
}

.wcam-btn-small .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    vertical-align: middle;
}

@media (max-width: 782px) {
    .wcam-search-box {
        margin-left: 0;
        width: 100%;
    }

    .wcam-search-box input[type="search"] {
        width: 100%;
        min-width: auto;
    }
}
</style>
