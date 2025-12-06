<?php
/**
 * Template: My Bids
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wcam-my-bids">

    <!-- Instructions -->
    <div class="wcam-instructions">
        <h3>Tracking Your Bids</h3>
        <p>Keep tabs on all the auctions you're participating in:</p>
        <ul>
            <li><strong>Winning Status</strong> - Green highlight shows auctions where you're currently the highest bidder</li>
            <li><strong>Outbid Alerts</strong> - Yellow badge indicates you've been outbid and can bid again</li>
            <li><strong>Ended Auctions</strong> - See final results of completed auctions</li>
            <li><strong>Quick Actions</strong> - Click "View Auction" to place a new bid or check details</li>
        </ul>
    </div>

    <h3><?php _e('My Bids', 'bk-auction-manager'); ?></h3>

    <?php if ($bids): ?>

        <table class="wcam-table wcam-bids-table">
            <thead>
                <tr>
                    <th><?php _e('Auction', 'bk-auction-manager'); ?></th>
                    <th><?php _e('My Bid', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Current Bid', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Status', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Time', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Actions', 'bk-auction-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $displayed_auctions = array();
                foreach ($bids as $bid):
                    // Only show the latest bid for each auction
                    if (in_array($bid->auction_id, $displayed_auctions)) {
                        continue;
                    }
                    $displayed_auctions[] = $bid->auction_id;

                    $current_bid = bk_auction_get_current_bid($bid->auction_id);
                    $is_winning = bk_auction_is_user_winning($bid->auction_id, $user_id);
                    $auction_status = get_post_meta($bid->auction_id, '_bk_auction_auction_status', true);
                ?>
                    <tr class="<?php echo $is_winning ? 'wcam-winning' : ''; ?>">
                        <td>
                            <a href="<?php echo get_permalink($bid->auction_id); ?>">
                                <?php echo esc_html($bid->post_title); ?>
                            </a>
                        </td>
                        <td><?php echo bk_auction_format_price($bid->bid_amount); ?></td>
                        <td><?php echo bk_auction_format_price($current_bid); ?></td>
                        <td>
                            <?php if ($is_winning): ?>
                                <span class="wcam-badge wcam-badge-success"><?php _e('Winning', 'bk-auction-manager'); ?></span>
                            <?php elseif ($auction_status === 'ended'): ?>
                                <span class="wcam-badge wcam-badge-default"><?php _e('Ended', 'bk-auction-manager'); ?></span>
                            <?php else: ?>
                                <span class="wcam-badge wcam-badge-warning"><?php _e('Outbid', 'bk-auction-manager'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo human_time_diff(strtotime($bid->bid_time), current_time('timestamp')) . ' ' . __('ago', 'bk-auction-manager'); ?>
                        </td>
                        <td>
                            <a href="<?php echo get_permalink($bid->auction_id); ?>" class="wcam-btn wcam-btn-small">
                                <?php _e('View Auction', 'bk-auction-manager'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p><?php _e('You have not placed any bids yet.', 'bk-auction-manager'); ?></p>

    <?php endif; ?>
</div>
