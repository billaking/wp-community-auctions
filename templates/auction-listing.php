<?php
/**
 * Template: Auction Listing (for shortcode)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wcam-auctions-listing">

    <!-- Instructions Banner -->
    <div class="wcam-instructions">
        <h3>Featured Auctions</h3>
        <p>Browse our current auction listings and find great deals!</p>
        <ul>
            <li><strong>Click any card</strong> to view full auction details and place bids</li>
            <li><strong>Check the timer</strong> to see how much time remains</li>
            <li><strong>View current bid</strong> to see the latest price</li>
        </ul>
    </div>

    <?php if ($auctions->have_posts()): ?>

        <div class="wcam-auctions-grid">
            <?php while ($auctions->have_posts()): $auctions->the_post();
                $auction_id = get_the_ID();
                $current_bid = bk_auction_get_current_bid($auction_id);
                $bid_count = bk_auction_get_bid_count($auction_id);
                $time_remaining = bk_auction_get_time_remaining($auction_id);
                $status = get_post_meta($auction_id, '_bk_auction_auction_status', true);
            ?>

                <div class="wcam-auction-card wcam-status-<?php echo esc_attr($status); ?>">
                    <div class="wcam-auction-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('medium'); ?>
                            <?php else: ?>
                                <div class="wcam-no-image-placeholder">
                                    <span class="dashicons dashicons-hammer"></span>
                                </div>
                            <?php endif; ?>
                        </a>
                        <span class="wcam-status-badge">
                            <?php echo esc_html(bk_auction_get_status_label($auction_id)); ?>
                        </span>
                    </div>

                    <div class="wcam-auction-body">
                        <?php
                        $auction_categories = get_the_terms($auction_id, 'bk_auction_category');
                        if ($auction_categories && !is_wp_error($auction_categories)):
                        ?>
                            <div class="wcam-auction-categories">
                                <?php foreach ($auction_categories as $cat): ?>
                                    <span class="wcam-category-badge"><?php echo esc_html($cat->name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="wcam-auction-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <div class="wcam-auction-meta">
                            <div class="wcam-meta-item">
                                <span class="wcam-label"><?php _e('Current Bid:', 'bk-auction-manager'); ?></span>
                                <span class="wcam-value wcam-price"><?php echo bk_auction_format_price($current_bid); ?></span>
                            </div>

                            <div class="wcam-meta-item">
                                <span class="wcam-label"><?php _e('Bids:', 'bk-auction-manager'); ?></span>
                                <span class="wcam-value"><?php echo absint($bid_count); ?></span>
                            </div>

                            <div class="wcam-meta-item wcam-time">
                                <span class="wcam-label"><?php _e('Time Left:', 'bk-auction-manager'); ?></span>
                                <span class="wcam-value"><?php echo esc_html($time_remaining['time']); ?></span>
                            </div>
                        </div>

                        <a href="<?php the_permalink(); ?>" class="wcam-btn wcam-btn-primary wcam-btn-block">
                            <?php _e('View Auction', 'bk-auction-manager'); ?>
                        </a>
                    </div>
                </div>

            <?php endwhile; ?>
        </div>

    <?php else: ?>

        <p class="wcam-no-auctions"><?php _e('No auctions found.', 'bk-auction-manager'); ?></p>

    <?php endif; ?>

</div>
