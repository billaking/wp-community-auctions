<?php
/**
 * Meta Boxes for Auction Post Type
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Meta_Boxes {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_bk_auction_auction', array($this, 'save_auction_meta'), 10, 2);
        add_filter('manage_bk_auction_auction_posts_columns', array($this, 'auction_columns'));
        add_action('manage_bk_auction_auction_posts_custom_column', array($this, 'auction_column_content'), 10, 2);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'bk_auction_auction_details',
            __('Auction Details', 'bk-auction-manager'),
            array($this, 'auction_details_callback'),
            'bk_auction_auction',
            'normal',
            'high'
        );

        add_meta_box(
            'bk_auction_auction_gallery',
            __('Item Photos (Up to 5)', 'bk-auction-manager'),
            array($this, 'auction_gallery_callback'),
            'bk_auction_auction',
            'normal',
            'high'
        );

        add_meta_box(
            'bk_auction_auction_bidding',
            __('Bidding Information', 'bk-auction-manager'),
            array($this, 'auction_bidding_callback'),
            'bk_auction_auction',
            'side',
            'default'
        );
    }

    /**
     * Auction details meta box
     */
    public function auction_details_callback($post) {
        wp_nonce_field('bk_auction_save_auction_meta', 'bk_auction_auction_nonce');

        $start_date = get_post_meta($post->ID, '_bk_auction_start_date', true);
        $end_date = get_post_meta($post->ID, '_bk_auction_end_date', true);
        $starting_price = get_post_meta($post->ID, '_bk_auction_starting_price', true);
        $reserve_price = get_post_meta($post->ID, '_bk_auction_reserve_price', true);
        $buy_now_price = get_post_meta($post->ID, '_bk_auction_buy_now_price', true);
        $bid_increment = get_post_meta($post->ID, '_bk_auction_bid_increment', true);
        $auction_status = get_post_meta($post->ID, '_bk_auction_auction_status', true);

        ?>
        <table class="form-table">
            <tr>
                <th><label for="bk_auction_start_date"><?php _e('Start Date & Time', 'bk-auction-manager'); ?></label></th>
                <td>
                    <input type="text" id="bk_auction_start_date" name="bk_auction_start_date" class="wcam-datepicker" value="<?php echo esc_attr($start_date); ?>" />
                    <p class="description"><?php _e('Format: YYYY-MM-DD HH:MM:SS', 'bk-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bk_auction_end_date"><?php _e('End Date & Time', 'bk-auction-manager'); ?></label></th>
                <td>
                    <input type="text" id="bk_auction_end_date" name="bk_auction_end_date" class="wcam-datepicker" value="<?php echo esc_attr($end_date); ?>" />
                    <p class="description"><?php _e('Format: YYYY-MM-DD HH:MM:SS', 'bk-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bk_auction_starting_price"><?php _e('Starting Price', 'bk-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="bk_auction_starting_price" name="bk_auction_starting_price" step="0.01" min="0" value="<?php echo esc_attr($starting_price); ?>" />
                </td>
            </tr>
            <tr>
                <th><label for="bk_auction_reserve_price"><?php _e('Reserve Price', 'bk-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="bk_auction_reserve_price" name="bk_auction_reserve_price" step="0.01" min="0" value="<?php echo esc_attr($reserve_price); ?>" />
                    <p class="description"><?php _e('Optional minimum price to sell', 'bk-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bk_auction_buy_now_price"><?php _e('Buy Now Price', 'bk-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="bk_auction_buy_now_price" name="bk_auction_buy_now_price" step="0.01" min="0" value="<?php echo esc_attr($buy_now_price); ?>" />
                    <p class="description"><?php _e('Optional instant purchase price', 'bk-auction-manager'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bk_auction_bid_increment"><?php _e('Minimum Bid Increment', 'bk-auction-manager'); ?></label></th>
                <td>
                    <input type="number" id="bk_auction_bid_increment" name="bk_auction_bid_increment" step="0.01" min="0.01" value="<?php echo esc_attr($bid_increment ? $bid_increment : '1.00'); ?>" />
                </td>
            </tr>
            <tr>
                <th><label for="bk_auction_auction_status"><?php _e('Auction Status', 'bk-auction-manager'); ?></label></th>
                <td>
                    <select id="bk_auction_auction_status" name="bk_auction_auction_status">
                        <option value="upcoming" <?php selected($auction_status, 'upcoming'); ?>><?php _e('Upcoming', 'bk-auction-manager'); ?></option>
                        <option value="active" <?php selected($auction_status, 'active'); ?>><?php _e('Active', 'bk-auction-manager'); ?></option>
                        <option value="ended" <?php selected($auction_status, 'ended'); ?>><?php _e('Ended', 'bk-auction-manager'); ?></option>
                        <option value="cancelled" <?php selected($auction_status, 'cancelled'); ?>><?php _e('Cancelled', 'bk-auction-manager'); ?></option>
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
            'top' => __('Top View', 'bk-auction-manager'),
            'front' => __('Front View', 'bk-auction-manager'),
            'left' => __('Left Side', 'bk-auction-manager'),
            'right' => __('Right Side', 'bk-auction-manager'),
            'back' => __('Back View', 'bk-auction-manager'),
        );

        ?>
        <div class="wcam-photo-gallery-admin">
            <p class="description" style="margin-bottom: 20px;">
                <?php _e('Upload up to 5 photos showing different angles of your item. These help buyers see exactly what they\'re bidding on.', 'bk-auction-manager'); ?>
            </p>
            <div class="wcam-gallery-grid">
                <?php foreach ($photo_labels as $key => $label):
                    $photo_id = get_post_meta($post->ID, '_bk_auction_photo_' . $key, true);
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
                                <span class="placeholder-text"><?php _e('Click to upload', 'bk-auction-manager'); ?></span>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="bk_auction_photo_<?php echo esc_attr($key); ?>" class="wcam-photo-id" value="<?php echo esc_attr($photo_id); ?>" />
                        <div class="wcam-gallery-buttons">
                            <button type="button" class="button wcam-upload-photo">
                                <?php echo $photo_url ? __('Change', 'bk-auction-manager') : __('Upload', 'bk-auction-manager'); ?>
                            </button>
                            <?php if ($photo_url): ?>
                                <button type="button" class="button wcam-remove-photo"><?php _e('Remove', 'bk-auction-manager'); ?></button>
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
        $current_bid = bk_auction_get_current_bid($post->ID);
        $total_bids = bk_auction_get_bid_count($post->ID);
        $highest_bidder = bk_auction_get_highest_bidder($post->ID);

        ?>
        <div class="wcam-bidding-info">
            <p><strong><?php _e('Current Bid:', 'bk-auction-manager'); ?></strong> <?php echo bk_auction_format_price($current_bid); ?></p>
            <p><strong><?php _e('Total Bids:', 'bk-auction-manager'); ?></strong> <?php echo absint($total_bids); ?></p>
            <?php if ($highest_bidder): ?>
                <p><strong><?php _e('Highest Bidder:', 'bk-auction-manager'); ?></strong>
                    <?php echo esc_html($highest_bidder->display_name); ?>
                    (<?php echo esc_html($highest_bidder->user_email); ?>)
                </p>
            <?php endif; ?>
            <p><a href="<?php echo admin_url('edit.php?post_type=bk_auction_auction&page=wcam-bids&auction_id=' . $post->ID); ?>" class="button"><?php _e('View All Bids', 'bk-auction-manager'); ?></a></p>
        </div>
        <?php
    }

    /**
     * Save auction meta
     */
    public function save_auction_meta($post_id, $post) {
        // Check nonce
        if (!isset($_POST['bk_auction_auction_nonce']) || !wp_verify_nonce($_POST['bk_auction_auction_nonce'], 'bk_auction_save_auction_meta')) {
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
            'bk_auction_start_date',
            'bk_auction_end_date',
            'bk_auction_starting_price',
            'bk_auction_reserve_price',
            'bk_auction_buy_now_price',
            'bk_auction_bid_increment',
            'bk_auction_auction_status',
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save photo gallery
        $photo_keys = array('top', 'front', 'left', 'right', 'back');
        foreach ($photo_keys as $key) {
            $field_name = 'bk_auction_photo_' . $key;
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
                $new_columns['starting_price'] = __('Starting Price', 'bk-auction-manager');
                $new_columns['current_bid'] = __('Current Bid', 'bk-auction-manager');
                $new_columns['bids'] = __('Bids', 'bk-auction-manager');
                $new_columns['status'] = __('Status', 'bk-auction-manager');
                $new_columns['end_date'] = __('End Date', 'bk-auction-manager');
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
                echo bk_auction_format_price(get_post_meta($post_id, '_bk_auction_starting_price', true));
                break;
            case 'current_bid':
                echo bk_auction_format_price(bk_auction_get_current_bid($post_id));
                break;
            case 'bids':
                echo absint(bk_auction_get_bid_count($post_id));
                break;
            case 'status':
                $status = get_post_meta($post_id, '_bk_auction_auction_status', true);
                $statuses = array(
                    'upcoming' => __('Upcoming', 'bk-auction-manager'),
                    'active' => __('Active', 'bk-auction-manager'),
                    'ended' => __('Ended', 'bk-auction-manager'),
                    'cancelled' => __('Cancelled', 'bk-auction-manager'),
                );
                echo isset($statuses[$status]) ? esc_html($statuses[$status]) : esc_html($status);
                break;
            case 'end_date':
                $end_date = get_post_meta($post_id, '_bk_auction_end_date', true);
                echo $end_date ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($end_date))) : '—';
                break;
        }
    }
}
