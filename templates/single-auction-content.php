<?php
/**
 * Template: Single Auction Content (for shortcode)
 */

if (!defined('ABSPATH')) {
    exit;
}

$auction_id = $auction->ID;
$current_bid = bk_auction_get_current_bid($auction_id);
$bid_count = bk_auction_get_bid_count($auction_id);
$time_remaining = bk_auction_get_time_remaining($auction_id);
$is_active = bk_auction_is_auction_active($auction_id);
$buy_now_price = get_post_meta($auction_id, '_bk_auction_buy_now_price', true);
$minimum_bid = bk_auction_get_minimum_bid($auction_id);
?>

<div class="wcam-single-auction-content">

    <div class="wcam-auction-header">
        <h2><?php echo esc_html($auction->post_title); ?></h2>
        <span class="wcam-status wcam-status-<?php echo esc_attr(get_post_meta($auction_id, '_bk_auction_auction_status', true)); ?>">
            <?php echo esc_html(bk_auction_get_status_label($auction_id)); ?>
        </span>
    </div>

    <div class="wcam-auction-content">

        <div class="wcam-auction-image">
            <?php if (has_post_thumbnail($auction_id)): ?>
                <?php echo get_the_post_thumbnail($auction_id, 'large'); ?>
            <?php else: ?>
                <div class="wcam-no-image">
                    <p><?php _e('No image available', 'bk-auction-manager'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="wcam-auction-info">

            <div class="wcam-bid-info">
                <div class="wcam-current-bid">
                    <span class="wcam-label"><?php _e('Current Bid:', 'bk-auction-manager'); ?></span>
                    <span class="wcam-amount" data-auction-id="<?php echo esc_attr($auction_id); ?>">
                        <?php echo bk_auction_format_price($current_bid); ?>
                    </span>
                </div>

                <div class="wcam-bid-count">
                    <span class="wcam-label"><?php _e('Total Bids:', 'bk-auction-manager'); ?></span>
                    <span class="wcam-count"><?php echo absint($bid_count); ?></span>
                </div>

                <div class="wcam-time-remaining">
                    <span class="wcam-label"><?php _e('Time Remaining:', 'bk-auction-manager'); ?></span>
                    <span class="wcam-time" data-auction-id="<?php echo esc_attr($auction_id); ?>">
                        <?php echo esc_html($time_remaining['time']); ?>
                    </span>
                </div>
            </div>

            <div class="wcam-auction-description">
                <?php echo wpautop($auction->post_content); ?>
            </div>

            <a href="<?php echo get_permalink($auction_id); ?>" class="wcam-btn wcam-btn-primary wcam-btn-block">
                <?php _e('View Full Auction', 'bk-auction-manager'); ?>
            </a>

        </div>

    </div>

</div>
