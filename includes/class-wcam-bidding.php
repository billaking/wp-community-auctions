<?php
/**
 * Bidding System
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Bidding {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // No hooks needed here, handled by AJAX class
    }

    /**
     * Place a bid
     */
    public function place_bid($auction_id, $user_id, $bid_amount) {
        global $wpdb;

        // Validate auction
        if (!$this->validate_auction($auction_id)) {
            return new WP_Error('invalid_auction', __('Invalid auction.', 'bk-auction-manager'));
        }

        // Check if auction is active
        $status = get_post_meta($auction_id, '_bk_auction_auction_status', true);
        if ($status !== 'active') {
            return new WP_Error('auction_not_active', __('Auction is not active.', 'bk-auction-manager'));
        }

        // Check if auction has ended
        if ($this->has_auction_ended($auction_id)) {
            return new WP_Error('auction_ended', __('Auction has ended.', 'bk-auction-manager'));
        }

        // Validate bid amount
        $validation = $this->validate_bid_amount($auction_id, $bid_amount);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Check if user is not the owner
        $auction = get_post($auction_id);
        if ($auction->post_author == $user_id) {
            return new WP_Error('own_auction', __('You cannot bid on your own auction.', 'bk-auction-manager'));
        }

        // Insert bid
        $table_name = $wpdb->prefix . 'bk_auction_bids';
        $result = $wpdb->insert(
            $table_name,
            array(
                'auction_id' => $auction_id,
                'user_id' => $user_id,
                'bid_amount' => $bid_amount,
                'bid_time' => current_time('mysql'),
                'ip_address' => $this->get_user_ip(),
                'status' => 'active',
            ),
            array('%d', '%d', '%f', '%s', '%s', '%s')
        );

        if ($result === false) {
            return new WP_Error('bid_failed', __('Failed to place bid.', 'bk-auction-manager'));
        }

        $bid_id = $wpdb->insert_id;

        // Update auction meta
        update_post_meta($auction_id, '_bk_auction_current_bid', $bid_amount);
        update_post_meta($auction_id, '_bk_auction_highest_bidder', $user_id);

        // Send notifications
        do_action('bk_auction_bid_placed', $bid_id, $auction_id, $user_id, $bid_amount);

        return $bid_id;
    }

    /**
     * Buy now
     */
    public function buy_now($auction_id, $user_id) {
        $buy_now_price = get_post_meta($auction_id, '_bk_auction_buy_now_price', true);

        if (empty($buy_now_price) || $buy_now_price <= 0) {
            return new WP_Error('no_buy_now', __('Buy now is not available for this auction.', 'bk-auction-manager'));
        }

        // Place bid at buy now price
        $result = $this->place_bid($auction_id, $user_id, $buy_now_price);

        if (!is_wp_error($result)) {
            // End auction immediately
            update_post_meta($auction_id, '_bk_auction_auction_status', 'ended');
            update_post_meta($auction_id, '_bk_auction_end_date', current_time('mysql'));

            do_action('bk_auction_buy_now_completed', $auction_id, $user_id, $buy_now_price);
        }

        return $result;
    }

    /**
     * Validate auction
     */
    private function validate_auction($auction_id) {
        $post = get_post($auction_id);
        return $post && $post->post_type === 'bk_auction_auction' && $post->post_status === 'publish';
    }

    /**
     * Validate bid amount
     */
    private function validate_bid_amount($auction_id, $bid_amount) {
        $current_bid = bk_auction_get_current_bid($auction_id);
        $starting_price = get_post_meta($auction_id, '_bk_auction_starting_price', true);
        $bid_increment = get_post_meta($auction_id, '_bk_auction_bid_increment', true);

        if (empty($bid_increment)) {
            $bid_increment = 1;
        }

        $minimum_bid = $current_bid > 0 ? $current_bid + $bid_increment : $starting_price;

        if ($bid_amount < $minimum_bid) {
            return new WP_Error('bid_too_low', sprintf(__('Bid must be at least %s.', 'bk-auction-manager'), bk_auction_format_price($minimum_bid)));
        }

        return true;
    }

    /**
     * Check if auction has ended
     */
    private function has_auction_ended($auction_id) {
        $end_date = get_post_meta($auction_id, '_bk_auction_end_date', true);

        if (empty($end_date)) {
            return false;
        }

        return strtotime($end_date) < current_time('timestamp');
    }

    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }

    /**
     * Get all bids for an auction
     */
    public function get_auction_bids($auction_id, $limit = 10) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bk_auction_bids';

        $bids = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE auction_id = %d ORDER BY bid_time DESC LIMIT %d",
            $auction_id,
            $limit
        ));

        return $bids;
    }

    /**
     * Get user's bids
     */
    public function get_user_bids($user_id, $limit = 20) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bk_auction_bids';

        $bids = $wpdb->get_results($wpdb->prepare(
            "SELECT b.*, p.post_title
             FROM $table_name b
             LEFT JOIN {$wpdb->posts} p ON b.auction_id = p.ID
             WHERE b.user_id = %d
             ORDER BY b.bid_time DESC
             LIMIT %d",
            $user_id,
            $limit
        ));

        return $bids;
    }

    /**
     * Get winning auctions for user
     */
    public function get_winning_auctions($user_id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT DISTINCT p.ID, p.post_title, pm.meta_value as current_bid
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_bk_auction_highest_bidder'
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_bk_auction_current_bid'
             WHERE pm1.meta_value = %d
             AND p.post_type = 'bk_auction_auction'
             AND p.post_status = 'publish'
             ORDER BY p.post_date DESC",
            $user_id
        );

        return $wpdb->get_results($query);
    }

    /**
     * Cancel bid (admin only)
     */
    public function cancel_bid($bid_id) {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            return new WP_Error('permission_denied', __('Permission denied.', 'bk-auction-manager'));
        }

        $table_name = $wpdb->prefix . 'bk_auction_bids';

        $result = $wpdb->update(
            $table_name,
            array('status' => 'cancelled'),
            array('id' => $bid_id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            // Recalculate highest bid
            $bid = $wpdb->get_row($wpdb->prepare("SELECT auction_id FROM $table_name WHERE id = %d", $bid_id));
            if ($bid) {
                $this->recalculate_highest_bid($bid->auction_id);
            }
        }

        return $result !== false;
    }

    /**
     * Recalculate highest bid
     */
    private function recalculate_highest_bid($auction_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bk_auction_bids';

        $highest_bid = $wpdb->get_row($wpdb->prepare(
            "SELECT bid_amount, user_id FROM $table_name
             WHERE auction_id = %d AND status = 'active'
             ORDER BY bid_amount DESC LIMIT 1",
            $auction_id
        ));

        if ($highest_bid) {
            update_post_meta($auction_id, '_bk_auction_current_bid', $highest_bid->bid_amount);
            update_post_meta($auction_id, '_bk_auction_highest_bidder', $highest_bid->user_id);
        } else {
            delete_post_meta($auction_id, '_bk_auction_current_bid');
            delete_post_meta($auction_id, '_bk_auction_highest_bidder');
        }
    }
}
