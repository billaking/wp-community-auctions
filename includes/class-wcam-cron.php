<?php
/**
 * Cron Jobs
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCAM_Cron {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wcam_check_expired_auctions', array($this, 'check_expired_auctions'));
    }

    /**
     * Check and end expired auctions
     */
    public function check_expired_auctions() {
        if (!get_option('wcam_auto_end_auctions', true)) {
            return;
        }

        $args = array(
            'post_type' => 'wcam_auction',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_wcam_auction_status',
                    'value' => 'active',
                ),
                array(
                    'key' => '_wcam_end_date',
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
        update_post_meta($auction_id, '_wcam_auction_status', 'ended');

        // Trigger notification
        do_action('wcam_auction_ended', $auction_id);
    }
}
