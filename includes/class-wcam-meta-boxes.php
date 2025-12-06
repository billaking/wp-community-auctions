<?php
/**
 * Meta Boxes for Auction Post Type
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCAM_Meta_Boxes {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_wcam_auction', array($this, 'save_auction_meta'), 10, 2);
        add_filter('manage_wcam_auction_posts_columns', array($this, 'auction_columns'));
        add_action('manage_wcam_auction_posts_custom_column', array($this, 'auction_column_content'), 10, 2);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'wcam_auction_details',
            __('Auction Details', 'wp-community-auction-manager'),
            array($this, 'auction_details_callback'),
            'wcam_auction',
            'normal',
            'high'
        );

        add_meta_box(
            'wcam_auction_gallery',
            __('Item Photos (Up to 5)', 'wp-community-auction-manager'),
            array($this, 'auction_gallery_callback'),
            'wcam_auction',
            'normal',
            'high'
        );

        add_meta_box(
            'wcam_auction_bidding',
            __('Bidding Information', 'wp-community-auction-manager'),
            array($this, 'auction_bidding_callback'),
            'wcam_auction',
            'side',
            'default'
        );
    }

    /**
     * Auction details meta box
     */
    public function auction_details_callback($post) {
        wp_nonce_field('wcam_save_auction_meta', 'wcam_auction_nonce');

        $start_date = get_post_meta($post->ID, '_wcam_start_date', true);
        $end_date = get_post_meta($post->ID, '_wcam_end_date', true);
        $starting_price = get_post_meta($post->ID, '_wcam_starting_price', true);
        $reserve_price = get_post_meta($post->ID, '_wcam_reserve_price', true);
        $buy_now_price = get_post_meta($post->ID, '_wcam_buy_now_price', true);
        $bid_increment = get_post_meta($post->ID, '_wcam_bid_increment', true);
        $auction_status = get_post_meta($post->ID, '_wcam_auction_status', true);

        ?>
        <table class="form-table">
            <tr>
                <th><label for="wcam_start_date"><?php _e('Start Date & Time', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <input type="text" id="wcam_start_date" name="wcam_start_date" class="wcam-datepicker" value="<?php echo esc_attr($start_date); ?>" />
                    <p class="description"><?php _e('Format: YYYY-MM-DD HH:MM:SS', 'wp-community-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="wcam_end_date"><?php _e('End Date & Time', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <input type="text" id="wcam_end_date" name="wcam_end_date" class="wcam-datepicker" value="<?php echo esc_attr($end_date); ?>" />
                    <p class="description"><?php _e('Format: YYYY-MM-DD HH:MM:SS', 'wp-community-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="wcam_starting_price"><?php _e('Starting Price', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="wcam_starting_price" name="wcam_starting_price" step="0.01" min="0" value="<?php echo esc_attr($starting_price); ?>" />
                </td>
            </tr>
            <tr>
                <th><label for="wcam_reserve_price"><?php _e('Reserve Price', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="wcam_reserve_price" name="wcam_reserve_price" step="0.01" min="0" value="<?php echo esc_attr($reserve_price); ?>" />
                    <p class="description"><?php _e('Optional minimum price to sell', 'wp-community-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="wcam_buy_now_price"><?php _e('Buy Now Price', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="wcam_buy_now_price" name="wcam_buy_now_price" step="0.01" min="0" value="<?php echo esc_attr($buy_now_price); ?>" />
                    <p class="description"><?php _e('Optional instant purchase price', 'wp-community-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="wcam_bid_increment"><?php _e('Minimum Bid Increment', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="wcam_bid_increment" name="wcam_bid_increment" step="0.01" min="0.01" value="<?php echo esc_attr($bid_increment ? $bid_increment : '1.00'); ?>" />
                </td>
            </tr>
            <tr>
                <th><label for="wcam_auction_status"><?php _e('Auction Status', 'wp-community-auction-manager'); ?></label></th>
                <td>
                    <select id="wcam_auction_status" name="wcam_auction_status">
                        <option value="upcoming" <?php selected($auction_status, 'upcoming'); ?>><?php _e('Upcoming', 'wp-community-auction-manager'); ?></option>
                        <option value="active" <?php selected($auction_status, 'active'); ?>><?php _e('Active', 'wp-community-auction-manager'); ?></option>
                        <option value="ended" <?php selected($auction_status, 'ended'); ?>><?php _e('Ended', 'wp-community-auction-manager'); ?></option>
                        <option value="cancelled" <?php selected($auction_status, 'cancelled'); ?>><?php _e('Cancelled', 'wp-community-auction-manager'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Photo gallery meta box
     */
    public function auction_gallery_callback($post) {
        $photo_labels = array(
            'top' => __('Top View', 'wp-community-auction-manager'),
            'front' => __('Front View', 'wp-community-auction-manager'),
            'left' => __('Left Side', 'wp-community-auction-manager'),
            'right' => __('Right Side', 'wp-community-auction-manager'),
            'back' => __('Back View', 'wp-community-auction-manager'),
        );

        ?>
        <div class="wcam-photo-gallery-admin">
            <p class="description" style="margin-bottom: 20px;">
                <?php _e('Upload up to 5 photos showing different angles of your item. These help buyers see exactly what they\'re bidding on.', 'wp-community-auction-manager'); ?>
            </p>
            <div class="wcam-gallery-grid">
                <?php foreach ($photo_labels as $key => $label):
                    $photo_id = get_post_meta($post->ID, '_wcam_photo_' . $key, true);
                    $photo_url = $photo_id ? wp_get_attachment_image_url($photo_id, 'medium') : '';
                ?>
                    <div class="wcam-gallery-item" data-label="<?php echo esc_attr($key); ?>">
                        <div class="wcam-gallery-label">
                            <span class="label-icon">📷</span>
                            <strong><?php echo esc_html($label); ?></strong>
                        </div>
                        <div class="wcam-gallery-preview" style="<?php echo $photo_url ? 'background-image: url(' . esc_url($photo_url) . ');' : ''; ?>">
                            <?php if (!$photo_url): ?>
                                <span class="placeholder-icon">📸</span>
                                <span class="placeholder-text"><?php _e('Click to upload', 'wp-community-auction-manager'); ?></span>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="wcam_photo_<?php echo esc_attr($key); ?>" class="wcam-photo-id" value="<?php echo esc_attr($photo_id); ?>" />
                        <div class="wcam-gallery-buttons">
                            <button type="button" class="button wcam-upload-photo">
                                <?php echo $photo_url ? __('Change', 'wp-community-auction-manager') : __('Upload', 'wp-community-auction-manager'); ?>
                            </button>
                            <?php if ($photo_url): ?>
                                <button type="button" class="button wcam-remove-photo"><?php _e('Remove', 'wp-community-auction-manager'); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Bidding information meta box
     */
    public function auction_bidding_callback($post) {
        $current_bid = wcam_get_current_bid($post->ID);
        $total_bids = wcam_get_bid_count($post->ID);
        $highest_bidder = wcam_get_highest_bidder($post->ID);

        ?>
        <div class="wcam-bidding-info">
            <p><strong><?php _e('Current Bid:', 'wp-community-auction-manager'); ?></strong> <?php echo wcam_format_price($current_bid); ?></p>
            <p><strong><?php _e('Total Bids:', 'wp-community-auction-manager'); ?></strong> <?php echo absint($total_bids); ?></p>
            <?php if ($highest_bidder): ?>
                <p><strong><?php _e('Highest Bidder:', 'wp-community-auction-manager'); ?></strong>
                    <?php echo esc_html($highest_bidder->display_name); ?>
                    (<?php echo esc_html($highest_bidder->user_email); ?>)
                </p>
            <?php endif; ?>
            <p><a href="<?php echo admin_url('edit.php?post_type=wcam_auction&page=wcam-bids&auction_id=' . $post->ID); ?>" class="button"><?php _e('View All Bids', 'wp-community-auction-manager'); ?></a></p>
        </div>
        <?php
    }

    /**
     * Save auction meta
     */
    public function save_auction_meta($post_id, $post) {
        // Check nonce
        if (!isset($_POST['wcam_auction_nonce']) || !wp_verify_nonce($_POST['wcam_auction_nonce'], 'wcam_save_auction_meta')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save fields
        $fields = array(
            'wcam_start_date',
            'wcam_end_date',
            'wcam_starting_price',
            'wcam_reserve_price',
            'wcam_buy_now_price',
            'wcam_bid_increment',
            'wcam_auction_status',
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save photo gallery
        $photo_keys = array('top', 'front', 'left', 'right', 'back');
        foreach ($photo_keys as $key) {
            $field_name = 'wcam_photo_' . $key;
            if (isset($_POST[$field_name])) {
                $photo_id = absint($_POST[$field_name]);
                if ($photo_id) {
                    update_post_meta($post_id, '_' . $field_name, $photo_id);
                } else {
                    delete_post_meta($post_id, '_' . $field_name);
                }
            }
        }
    }

    /**
     * Custom columns for auction list
     */
    public function auction_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['starting_price'] = __('Starting Price', 'wp-community-auction-manager');
                $new_columns['current_bid'] = __('Current Bid', 'wp-community-auction-manager');
                $new_columns['bids'] = __('Bids', 'wp-community-auction-manager');
                $new_columns['status'] = __('Status', 'wp-community-auction-manager');
                $new_columns['end_date'] = __('End Date', 'wp-community-auction-manager');
            }
        }
        return $new_columns;
    }

    /**
     * Custom column content
     */
    public function auction_column_content($column, $post_id) {
        switch ($column) {
            case 'starting_price':
                echo wcam_format_price(get_post_meta($post_id, '_wcam_starting_price', true));
                break;
            case 'current_bid':
                echo wcam_format_price(wcam_get_current_bid($post_id));
                break;
            case 'bids':
                echo absint(wcam_get_bid_count($post_id));
                break;
            case 'status':
                $status = get_post_meta($post_id, '_wcam_auction_status', true);
                $statuses = array(
                    'upcoming' => __('Upcoming', 'wp-community-auction-manager'),
                    'active' => __('Active', 'wp-community-auction-manager'),
                    'ended' => __('Ended', 'wp-community-auction-manager'),
                    'cancelled' => __('Cancelled', 'wp-community-auction-manager'),
                );
                echo isset($statuses[$status]) ? esc_html($statuses[$status]) : esc_html($status);
                break;
            case 'end_date':
                $end_date = get_post_meta($post_id, '_wcam_end_date', true);
                echo $end_date ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($end_date))) : '—';
                break;
        }
    }
}
