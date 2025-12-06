<?php
/**
 * Template: Archive Auctions
 */

get_header();
?>

<div class="wcam-archive-auctions">

    <!-- Instructions Banner -->
    <div class="wcam-instructions">
        <h3>Browse Active Auctions</h3>
        <p>Welcome to our auction marketplace! Here you can browse all available auctions and place bids on items you're interested in.</p>
        <ul>
            <li><strong>Click on any auction card</strong> to view full details and place bids</li>
            <li><strong>Use the category filter</strong> below to find specific types of items</li>
            <li><strong>Watch the timer</strong> on each auction to see how much time is left</li>
            <li><strong>Log in</strong> to place bids and participate in auctions</li>
        </ul>
    </div>

    <header class="page-header">
        <h1 class="page-title"><?php _e('Auctions', 'wp-community-auction-manager'); ?></h1>

        <?php
        // Category filter
        $categories = get_terms(array('taxonomy' => 'wcam_category', 'hide_empty' => true));
        if ($categories):
        ?>
            <div class="wcam-category-filters">
                <div class="wcam-filter-label">
                    <span class="filter-icon">🏷️</span>
                    <?php _e('Browse by Category:', 'wp-community-auction-manager'); ?>
                </div>
                <div class="wcam-category-pills">
                    <button class="wcam-category-pill active" data-category="">
                        <span class="pill-icon">✨</span>
                        <?php _e('All Auctions', 'wp-community-auction-manager'); ?>
                        <span class="pill-count"><?php echo wp_count_posts('wcam_auction')->publish; ?></span>
                    </button>
                    <?php foreach ($categories as $category): ?>
                        <button class="wcam-category-pill" data-category="<?php echo esc_attr($category->slug); ?>">
                            <span class="pill-icon">📦</span>
                            <?php echo esc_html($category->name); ?>
                            <span class="pill-count"><?php echo $category->count; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()): ?>

        <div class="wcam-auctions-grid">
            <?php while (have_posts()): the_post();
                $auction_id = get_the_ID();
                $current_bid = wcam_get_current_bid($auction_id);
                $bid_count = wcam_get_bid_count($auction_id);
                $time_remaining = wcam_get_time_remaining($auction_id);
                $status = get_post_meta($auction_id, '_wcam_auction_status', true);
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
                            <?php echo esc_html(wcam_get_status_label($auction_id)); ?>
                        </span>
                    </div>

                    <div class="wcam-auction-body">
                        <?php
                        $auction_categories = get_the_terms($auction_id, 'wcam_category');
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
                                <span class="wcam-label"><?php _e('Current Bid:', 'wp-community-auction-manager'); ?></span>
                                <span class="wcam-value wcam-price"><?php echo wcam_format_price($current_bid); ?></span>
                            </div>

                            <div class="wcam-meta-item">
                                <span class="wcam-label"><?php _e('Bids:', 'wp-community-auction-manager'); ?></span>
                                <span class="wcam-value"><?php echo absint($bid_count); ?></span>
                            </div>

                            <div class="wcam-meta-item wcam-time">
                                <span class="wcam-label"><?php _e('Time Left:', 'wp-community-auction-manager'); ?></span>
                                <span class="wcam-value"><?php echo esc_html($time_remaining['time']); ?></span>
                            </div>
                        </div>

                        <a href="<?php the_permalink(); ?>" class="wcam-btn wcam-btn-primary wcam-btn-block">
                            <?php _e('View Auction', 'wp-community-auction-manager'); ?>
                        </a>
                    </div>
                </div>

            <?php endwhile; ?>
        </div>

        <div class="wcam-pagination">
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('&laquo; Previous', 'wp-community-auction-manager'),
                'next_text' => __('Next &raquo;', 'wp-community-auction-manager'),
            ));
            ?>
        </div>

    <?php else: ?>

        <p class="wcam-no-auctions"><?php _e('No auctions found.', 'wp-community-auction-manager'); ?></p>

    <?php endif; ?>

</div>

<?php
get_footer();
