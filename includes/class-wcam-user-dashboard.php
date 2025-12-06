<?php
/**
 * User Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCAM_User_Dashboard {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('wcam_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('wcam_my_auctions', array($this, 'my_auctions_shortcode'));
        add_shortcode('wcam_my_bids', array($this, 'my_bids_shortcode'));
        add_shortcode('wcam_create_auction', array($this, 'create_auction_shortcode'));

        add_action('init', array($this, 'handle_auction_submission'));
    }

    /**
     * Main dashboard shortcode
     */
    public function dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your dashboard.', 'wp-community-auction-manager') . '</p>';
        }

        ob_start();
        include WCAM_PLUGIN_DIR . 'templates/dashboard/main.php';
        return ob_get_clean();
    }

    /**
     * My auctions shortcode
     */
    public function my_auctions_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your auctions.', 'wp-community-auction-manager') . '</p>';
        }

        $user_id = get_current_user_id();

        $args = array(
            'post_type' => 'wcam_auction',
            'author' => $user_id,
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $auctions = new WP_Query($args);

        ob_start();
        include WCAM_PLUGIN_DIR . 'templates/dashboard/my-auctions.php';
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * My bids shortcode
     */
    public function my_bids_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your bids.', 'wp-community-auction-manager') . '</p>';
        }

        $user_id = get_current_user_id();
        $bidding = WCAM_Bidding::get_instance();
        $bids = $bidding->get_user_bids($user_id, 50);

        ob_start();
        include WCAM_PLUGIN_DIR . 'templates/dashboard/my-bids.php';
        return ob_get_clean();
    }

    /**
     * Create auction shortcode
     */
    public function create_auction_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to create an auction.', 'wp-community-auction-manager') . '</p>';
        }

        ob_start();
        include WCAM_PLUGIN_DIR . 'templates/dashboard/create-auction.php';
        return ob_get_clean();
    }

    /**
     * Handle auction submission from frontend
     */
    public function handle_auction_submission() {
        if (!isset($_POST['wcam_submit_auction']) || !is_user_logged_in()) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['wcam_create_auction_nonce']) ||
            !wp_verify_nonce($_POST['wcam_create_auction_nonce'], 'wcam_create_auction')) {
            wp_die(__('Security check failed.', 'wp-community-auction-manager'));
        }

        // Sanitize and validate data
        $title = sanitize_text_field($_POST['auction_title']);
        $description = wp_kses_post($_POST['auction_description']);
        $starting_price = floatval($_POST['starting_price']);
        $reserve_price = floatval($_POST['reserve_price']);
        $buy_now_price = floatval($_POST['buy_now_price']);
        $bid_increment = floatval($_POST['bid_increment']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);

        // Create auction post
        $auction_data = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish',
            'post_type' => 'wcam_auction',
            'post_author' => get_current_user_id(),
        );

        $auction_id = wp_insert_post($auction_data);

        if ($auction_id && !is_wp_error($auction_id)) {
            // Save meta data
            update_post_meta($auction_id, '_wcam_starting_price', $starting_price);
            update_post_meta($auction_id, '_wcam_reserve_price', $reserve_price);
            update_post_meta($auction_id, '_wcam_buy_now_price', $buy_now_price);
            update_post_meta($auction_id, '_wcam_bid_increment', $bid_increment);
            update_post_meta($auction_id, '_wcam_start_date', $start_date);
            update_post_meta($auction_id, '_wcam_end_date', $end_date);
            update_post_meta($auction_id, '_wcam_auction_status', 'active');

            // Handle photo gallery uploads
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $photo_keys = array('top', 'front', 'left', 'right', 'back');
            $first_photo_set = false;

            foreach ($photo_keys as $key) {
                $file_key = 'auction_photo_' . $key;
                if (!empty($_FILES[$file_key]['name'])) {
                    $attachment_id = media_handle_upload($file_key, $auction_id);

                    if (!is_wp_error($attachment_id)) {
                        update_post_meta($auction_id, '_wcam_photo_' . $key, $attachment_id);

                        // Set first uploaded photo as featured image
                        if (!$first_photo_set) {
                            set_post_thumbnail($auction_id, $attachment_id);
                            $first_photo_set = true;
                        }
                    }
                }
            }

            // Redirect to the new auction
            wp_redirect(get_permalink($auction_id));
            exit;
        }
    }

    /**
     * Get user statistics
     */
    public function get_user_stats($user_id) {
        global $wpdb;

        $stats = array();

        // Total auctions created
        $stats['total_auctions'] = count_user_posts($user_id, 'wcam_auction');

        // Active auctions
        $active_auctions = get_posts(array(
            'post_type' => 'wcam_auction',
            'author' => $user_id,
            'meta_query' => array(
                array(
                    'key' => '_wcam_auction_status',
                    'value' => 'active',
                )
            ),
            'fields' => 'ids',
        ));
        $stats['active_auctions'] = count($active_auctions);

        // Total bids placed
        $table_name = $wpdb->prefix . 'wcam_bids';
        $stats['total_bids'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
            $user_id
        ));

        // Winning auctions
        $bidding = WCAM_Bidding::get_instance();
        $winning = $bidding->get_winning_auctions($user_id);
        $stats['winning_auctions'] = count($winning);

        return $stats;
    }
}
