<?php
/**
 * AJAX Handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_bk_auction_place_bid', array($this, 'place_bid'));
        add_action('wp_ajax_nopriv_bk_auction_place_bid', array($this, 'place_bid'));

        add_action('wp_ajax_bk_auction_buy_now', array($this, 'buy_now'));
        add_action('wp_ajax_nopriv_bk_auction_buy_now', array($this, 'buy_now'));

        add_action('wp_ajax_bk_auction_get_current_bid', array($this, 'get_current_bid'));
        add_action('wp_ajax_nopriv_bk_auction_get_current_bid', array($this, 'get_current_bid'));

        add_action('wp_ajax_bk_auction_get_time_remaining', array($this, 'get_time_remaining'));
        add_action('wp_ajax_nopriv_bk_auction_get_time_remaining', array($this, 'get_time_remaining'));
    }

    /**
     * Place bid via AJAX
     */
    public function place_bid() {
        check_ajax_referer('bk_auction_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to place a bid.', 'bk-auction-manager')));
        }

        $auction_id = isset($_POST['auction_id']) ? absint($_POST['auction_id']) : 0;
        $bid_amount = isset($_POST['bid_amount']) ? floatval($_POST['bid_amount']) : 0;
        $user_id = get_current_user_id();

        if (!$auction_id || !$bid_amount) {
            wp_send_json_error(array('message' => __('Invalid data.', 'bk-auction-manager')));
        }

        $bidding = BK_AUCTION_Bidding::get_instance();
        $result = $bidding->place_bid($auction_id, $user_id, $bid_amount);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Bid placed successfully!', 'bk-auction-manager'),
            'bid_id' => $result,
            'current_bid' => bk_auction_format_price($bid_amount),
            'bid_count' => bk_auction_get_bid_count($auction_id),
        ));
    }

    /**
     * Buy now via AJAX
     */
    public function buy_now() {
        check_ajax_referer('bk_auction_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to buy now.', 'bk-auction-manager')));
        }

        $auction_id = isset($_POST['auction_id']) ? absint($_POST['auction_id']) : 0;
        $user_id = get_current_user_id();

        if (!$auction_id) {
            wp_send_json_error(array('message' => __('Invalid auction.', 'bk-auction-manager')));
        }

        $bidding = BK_AUCTION_Bidding::get_instance();
        $result = $bidding->buy_now($auction_id, $user_id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Purchase completed successfully!', 'bk-auction-manager'),
            'redirect' => get_permalink($auction_id),
        ));
    }

    /**
     * Get current bid via AJAX
     */
    public function get_current_bid() {
        $auction_id = isset($_POST['auction_id']) ? absint($_POST['auction_id']) : 0;

        if (!$auction_id) {
            wp_send_json_error();
        }

        $current_bid = bk_auction_get_current_bid($auction_id);
        $bid_count = bk_auction_get_bid_count($auction_id);

        wp_send_json_success(array(
            'current_bid' => bk_auction_format_price($current_bid),
            'current_bid_raw' => $current_bid,
            'bid_count' => $bid_count,
        ));
    }

    /**
     * Get time remaining via AJAX
     */
    public function get_time_remaining() {
        $auction_id = isset($_POST['auction_id']) ? absint($_POST['auction_id']) : 0;

        if (!$auction_id) {
            wp_send_json_error();
        }

        $end_date = get_post_meta($auction_id, '_bk_auction_end_date', true);

        if (empty($end_date)) {
            wp_send_json_error();
        }

        $now = current_time('timestamp');
        $end = strtotime($end_date);
        $diff = $end - $now;

        if ($diff <= 0) {
            wp_send_json_success(array(
                'ended' => true,
                'time_remaining' => __('Auction Ended', 'bk-auction-manager'),
            ));
        }

        wp_send_json_success(array(
            'ended' => false,
            'time_remaining' => bk_auction_format_time_remaining($diff),
            'seconds' => $diff,
        ));
    }
}
