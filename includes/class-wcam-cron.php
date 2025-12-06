<?php
/**
 * Cron Jobs
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Cron {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('bk_auction_check_expired_auctions', array($this, 'check_expired_auctions'));
    }

    /**
     * Check and end expired auctions
     */
    public function check_expired_auctions() {
        if (!get_option('bk_auction_auto_end_auctions', true)) {
            return;
        }

        $args = array(
            'post_type' => 'bk_auction_auction',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_bk_auction_auction_status',
                    'value' => 'active',
                ),
                array(
                    'key' => '_bk_auction_end_date',
                    'value' => current_time('mysql'),
                    'compare' => '<',
                    'type' => 'DATETIME',
                ),
            ),
        );

        $expired_auctions = get_posts($args);

        foreach ($expired_auctions as $auction) {
            $this->end_auction($auction->ID);
        }
    }

    /**
     * End an auction
     */
    private function end_auction($auction_id) {
        update_post_meta($auction_id, '_bk_auction_auction_status', 'ended');

        // Trigger notification
        do_action('bk_auction_auction_ended', $auction_id);
    }
}
