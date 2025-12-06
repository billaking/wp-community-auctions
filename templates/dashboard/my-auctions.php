<?php
/**
 * Template: My Auctions
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wcam-my-auctions">

    <!-- Instructions -->
    <div class="wcam-instructions">
        <h3>Managing Your Auctions</h3>
        <p>Track and manage all the items you're selling:</p>
        <ul>
            <li><strong>Monitor Status</strong> - See which auctions are active, upcoming, or ended</li>
            <li><strong>Track Bids</strong> - View current bid amounts and total number of bids</li>
            <li><strong>Edit Auctions</strong> - Update auction details before they end</li>
            <li><strong>Check Winners</strong> - Contact buyers when your auctions close</li>
        </ul>
    </div>

    <h3><?php _e('My Auctions', 'wp-community-auction-manager'); ?></h3>

    <?php if ($auctions->have_posts()): ?>

        <table class="wcam-table wcam-auctions-table">
            <thead>
                <tr>
                    <th><?php _e('Auction', 'wp-community-auction-manager'); ?></th>
                    <th><?php _e('Status', 'wp-community-auction-manager'); ?></th>
                    <th><?php _e('Current Bid', 'wp-community-auction-manager'); ?></th>
                    <th><?php _e('Bids', 'wp-community-auction-manager'); ?></th>
                    <th><?php _e('Ends', 'wp-community-auction-manager'); ?></th>
                    <th><?php _e('Actions', 'wp-community-auction-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($auctions->have_posts()): $auctions->the_post();
                    $auction_id = get_the_ID();
                    $current_bid = wcam_get_current_bid($auction_id);
                    $bid_count = wcam_get_bid_count($auction_id);
                    $status = get_post_meta($auction_id, '_wcam_auction_status', true);
                    $end_date = get_post_meta($auction_id, '_wcam_end_date', true);
                ?>
                    <tr>
                        <td>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </td>
                        <td>
                            <span class="wcam-status-badge wcam-status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(wcam_get_status_label($auction_id)); ?>
                            </span>
                        </td>
                        <td><?php echo wcam_format_price($current_bid); ?></td>
                        <td><?php echo absint($bid_count); ?></td>
                        <td>
                            <?php
                            if ($end_date) {
                                echo esc_html(date_i18n(get_option('date_format'), strtotime($end_date)));
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php the_permalink(); ?>" class="wcam-btn wcam-btn-small">
                                <?php _e('View', 'wp-community-auction-manager'); ?>
                            </a>
                            <a href="<?php echo get_edit_post_link(); ?>" class="wcam-btn wcam-btn-small">
                                <?php _e('Edit', 'wp-community-auction-manager'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p><?php _e('You have not created any auctions yet.', 'wp-community-auction-manager'); ?></p>

    <?php endif; ?>
</div>
