<?php
/**
 * Template: User Dashboard Main
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$dashboard = WCAM_User_Dashboard::get_instance();
$stats = $dashboard->get_user_stats($user_id);
?>

<div class="wcam-dashboard">

    <!-- Instructions Banner -->
    <div class="wcam-instructions">
        <h3>Welcome to Your Auction Dashboard!</h3>
        <p>Manage all your auction activities from one central location. Here's what you can do:</p>
        <ul>
            <li><strong>View Statistics</strong> - See your auction performance at a glance</li>
            <li><strong>My Auctions</strong> - Manage items you're selling and track bids received</li>
            <li><strong>My Bids</strong> - Monitor auctions you're bidding on and your winning status</li>
            <li><strong>Create Auction</strong> - List new items for sale with just a few clicks</li>
            <li><strong>Get Notifications</strong> - Receive email alerts for bids, outbids, and wins</li>
        </ul>
    </div>

    <h2><?php printf(__('Welcome, %s!', 'wp-community-auction-manager'), wp_get_current_user()->display_name); ?></h2>

    <div class="wcam-dashboard-stats">
        <div class="wcam-stat-card">
            <div class="wcam-stat-value"><?php echo absint($stats['total_auctions']); ?></div>
            <div class="wcam-stat-label"><?php _e('Total Auctions', 'wp-community-auction-manager'); ?></div>
        </div>

        <div class="wcam-stat-card">
            <div class="wcam-stat-value"><?php echo absint($stats['active_auctions']); ?></div>
            <div class="wcam-stat-label"><?php _e('Active Auctions', 'wp-community-auction-manager'); ?></div>
        </div>

        <div class="wcam-stat-card">
            <div class="wcam-stat-value"><?php echo absint($stats['total_bids']); ?></div>
            <div class="wcam-stat-label"><?php _e('Bids Placed', 'wp-community-auction-manager'); ?></div>
        </div>

        <div class="wcam-stat-card">
            <div class="wcam-stat-value"><?php echo absint($stats['winning_auctions']); ?></div>
            <div class="wcam-stat-label"><?php _e('Winning', 'wp-community-auction-manager'); ?></div>
        </div>
    </div>

    <div class="wcam-dashboard-navigation">
        <a href="#my-auctions" class="wcam-btn wcam-btn-primary">
            <?php _e('My Auctions', 'wp-community-auction-manager'); ?>
        </a>
        <a href="#my-bids" class="wcam-btn wcam-btn-secondary">
            <?php _e('My Bids', 'wp-community-auction-manager'); ?>
        </a>
        <?php if (get_option('wcam_allow_frontend_submission', true)): ?>
            <a href="#create-auction" class="wcam-btn wcam-btn-success">
                <?php _e('Create Auction', 'wp-community-auction-manager'); ?>
            </a>
        <?php endif; ?>
    </div>

    <div id="my-auctions" class="wcam-dashboard-section">
        <?php echo do_shortcode('[wcam_my_auctions]'); ?>
    </div>

    <div id="my-bids" class="wcam-dashboard-section">
        <?php echo do_shortcode('[wcam_my_bids]'); ?>
    </div>

    <?php if (get_option('wcam_allow_frontend_submission', true)): ?>
        <div id="create-auction" class="wcam-dashboard-section">
            <?php echo do_shortcode('[wcam_create_auction]'); ?>
        </div>
    <?php endif; ?>

</div>
