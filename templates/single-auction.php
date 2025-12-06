<?php
/**
 * Template: Single Auction
 */

get_header();

while (have_posts()) : the_post();
    $auction_id = get_the_ID();
    $current_bid = wcam_get_current_bid($auction_id);
    $bid_count = wcam_get_bid_count($auction_id);
    $time_remaining = wcam_get_time_remaining($auction_id);
    $is_active = wcam_is_auction_active($auction_id);
    $buy_now_price = get_post_meta($auction_id, '_wcam_buy_now_price', true);
    $minimum_bid = wcam_get_minimum_bid($auction_id);
    ?>

    <!-- Instructions Banner -->
    <div class="wcam-instructions">
        <h3>How to Participate in This Auction</h3>
        <p>Ready to bid? Here's everything you need to know:</p>
        <ul>
            <li><strong>Review the details</strong> - Check the item description, current bid, and time remaining</li>
            <li><strong>Place your bid</strong> - Enter an amount equal to or greater than the minimum bid shown</li>
            <li><strong>Buy Now option</strong> - If available, you can purchase instantly at the Buy Now price</li>
            <li><strong>Stay updated</strong> - You'll receive email notifications if you're outbid</li>
            <li><strong>Win the auction</strong> - The highest bidder when time expires wins the item!</li>
        </ul>
    </div>

    <article id="post-<?php the_ID(); ?>" <?php post_class('wcam-single-auction'); ?>>

        <div class="wcam-auction-header">
            <div class="wcam-header-top">
                <?php
                $auction_categories = get_the_terms($auction_id, 'wcam_category');
                if ($auction_categories && !is_wp_error($auction_categories)):
                ?>
                    <div class="wcam-auction-categories-single">
                        <?php foreach ($auction_categories as $cat): ?>
                            <a href="<?php echo get_term_link($cat); ?>" class="wcam-category-badge wcam-category-badge-large">
                                <span class="badge-icon">📦</span>
                                <?php echo esc_html($cat->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <span class="wcam-status wcam-status-<?php echo esc_attr(get_post_meta($auction_id, '_wcam_auction_status', true)); ?>">
                    <?php echo esc_html(wcam_get_status_label($auction_id)); ?>
                </span>
            </div>
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </div>

        <div class="wcam-auction-content">

            <div class="wcam-auction-image">
                <?php
                // Get all gallery photos
                $photo_keys = array('top', 'front', 'left', 'right', 'back');
                $photo_labels = array(
                    'top' => __('Top View', 'wp-community-auction-manager'),
                    'front' => __('Front View', 'wp-community-auction-manager'),
                    'left' => __('Left Side', 'wp-community-auction-manager'),
                    'right' => __('Right Side', 'wp-community-auction-manager'),
                    'back' => __('Back View', 'wp-community-auction-manager'),
                );

                $photos = array();
                foreach ($photo_keys as $key) {
                    $photo_id = get_post_meta($auction_id, '_wcam_photo_' . $key, true);
                    if ($photo_id) {
                        $photos[$key] = array(
                            'id' => $photo_id,
                            'url' => wp_get_attachment_image_url($photo_id, 'large'),
                            'thumb' => wp_get_attachment_image_url($photo_id, 'thumbnail'),
                            'label' => $photo_labels[$key]
                        );
                    }
                }

                // Show gallery if photos exist, otherwise featured image or placeholder
                if (!empty($photos)):
                    $first_photo = reset($photos);
                ?>
                    <div class="wcam-photo-gallery">
                        <div class="wcam-gallery-main">
                            <img id="wcam-main-photo" src="<?php echo esc_url($first_photo['url']); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                            <span class="wcam-photo-label" id="wcam-current-label"><?php echo esc_html($first_photo['label']); ?></span>
                        </div>
                        <div class="wcam-gallery-thumbnails">
                            <?php foreach ($photos as $key => $photo): ?>
                                <div class="wcam-gallery-thumb <?php echo $key === array_key_first($photos) ? 'active' : ''; ?>"
                                     data-full="<?php echo esc_url($photo['url']); ?>"
                                     data-label="<?php echo esc_attr($photo['label']); ?>">
                                    <img src="<?php echo esc_url($photo['thumb']); ?>" alt="<?php echo esc_attr($photo['label']); ?>" />
                                    <span class="thumb-label"><?php echo esc_html($photo['label']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('large'); ?>
                <?php else: ?>
                    <div class="wcam-no-image">
                        <p><?php _e('No image available', 'wp-community-auction-manager'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="wcam-auction-info">

                <div class="wcam-bid-info">
                    <div class="wcam-current-bid">
                        <span class="wcam-label"><?php _e('Current Bid:', 'wp-community-auction-manager'); ?></span>
                        <span class="wcam-amount" data-auction-id="<?php echo esc_attr($auction_id); ?>">
                            <?php echo wcam_format_price($current_bid); ?>
                        </span>
                    </div>

                    <div class="wcam-bid-count">
                        <span class="wcam-label"><?php _e('Total Bids:', 'wp-community-auction-manager'); ?></span>
                        <span class="wcam-count"><?php echo absint($bid_count); ?></span>
                    </div>

                    <div class="wcam-time-remaining">
                        <span class="wcam-label"><?php _e('Time Remaining:', 'wp-community-auction-manager'); ?></span>
                        <span class="wcam-time" data-auction-id="<?php echo esc_attr($auction_id); ?>">
                            <?php echo esc_html($time_remaining['time']); ?>
                        </span>
                    </div>
                </div>

                <?php if ($is_active && is_user_logged_in()): ?>

                    <div class="wcam-bid-form">
                        <h3><?php _e('Place Your Bid', 'wp-community-auction-manager'); ?></h3>

                        <form id="wcam-bid-form" class="wcam-form">
                            <input type="hidden" name="auction_id" value="<?php echo esc_attr($auction_id); ?>">

                            <div class="form-group">
                                <label for="bid_amount"><?php _e('Bid Amount:', 'wp-community-auction-manager'); ?></label>
                                <input type="number" id="bid_amount" name="bid_amount"
                                       min="<?php echo esc_attr($minimum_bid); ?>"
                                       step="0.01"
                                       value="<?php echo esc_attr($minimum_bid); ?>"
                                       required>
                                <p class="description">
                                    <?php printf(__('Minimum bid: %s', 'wp-community-auction-manager'), wcam_format_price($minimum_bid)); ?>
                                </p>
                            </div>

                            <button type="submit" class="wcam-btn wcam-btn-primary">
                                <?php _e('Place Bid', 'wp-community-auction-manager'); ?>
                            </button>
                        </form>

                        <?php if ($buy_now_price && get_option('wcam_enable_buy_now', true)): ?>
                            <div class="wcam-buy-now">
                                <p><?php printf(__('Or buy it now for %s', 'wp-community-auction-manager'), wcam_format_price($buy_now_price)); ?></p>
                                <button id="wcam-buy-now-btn" class="wcam-btn wcam-btn-success" data-auction-id="<?php echo esc_attr($auction_id); ?>">
                                    <?php _e('Buy Now', 'wp-community-auction-manager'); ?>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="wcam-message"></div>
                    </div>

                <?php elseif ($is_active && !is_user_logged_in()): ?>

                    <div class="wcam-login-required">
                        <p><?php _e('Please log in to place a bid.', 'wp-community-auction-manager'); ?></p>
                        <a href="<?php echo wp_login_url(get_permalink()); ?>" class="wcam-btn wcam-btn-primary">
                            <?php _e('Log In', 'wp-community-auction-manager'); ?>
                        </a>
                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="wcam-auction-description">
            <h2><?php _e('Description', 'wp-community-auction-manager'); ?></h2>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </div>

        <div class="wcam-auction-details">
            <h3><?php _e('Auction Details', 'wp-community-auction-manager'); ?></h3>
            <table class="wcam-details-table">
                <tr>
                    <th><?php _e('Starting Price:', 'wp-community-auction-manager'); ?></th>
                    <td><?php echo wcam_format_price(get_post_meta($auction_id, '_wcam_starting_price', true)); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Bid Increment:', 'wp-community-auction-manager'); ?></th>
                    <td><?php echo wcam_format_price(get_post_meta($auction_id, '_wcam_bid_increment', true)); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Start Date:', 'wp-community-auction-manager'); ?></th>
                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'),
                        strtotime(get_post_meta($auction_id, '_wcam_start_date', true))); ?></td>
                </tr>
                <tr>
                    <th><?php _e('End Date:', 'wp-community-auction-manager'); ?></th>
                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'),
                        strtotime(get_post_meta($auction_id, '_wcam_end_date', true))); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Seller:', 'wp-community-auction-manager'); ?></th>
                    <td><?php the_author(); ?></td>
                </tr>
            </table>
        </div>

        <?php
        // Display recent bids
        $bidding = WCAM_Bidding::get_instance();
        $recent_bids = $bidding->get_auction_bids($auction_id, 10);

        if ($recent_bids):
        ?>
            <div class="wcam-recent-bids">
                <h3><?php _e('Recent Bids', 'wp-community-auction-manager'); ?></h3>
                <table class="wcam-bids-table">
                    <thead>
                        <tr>
                            <th><?php _e('Bidder', 'wp-community-auction-manager'); ?></th>
                            <th><?php _e('Amount', 'wp-community-auction-manager'); ?></th>
                            <th><?php _e('Time', 'wp-community-auction-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bids as $bid): ?>
                            <tr>
                                <td><?php echo esc_html(get_userdata($bid->user_id)->display_name); ?></td>
                                <td><?php echo wcam_format_price($bid->bid_amount); ?></td>
                                <td><?php echo human_time_diff(strtotime($bid->bid_time), current_time('timestamp')) . ' ' . __('ago', 'wp-community-auction-manager'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </article>

<?php
endwhile;

get_footer();
