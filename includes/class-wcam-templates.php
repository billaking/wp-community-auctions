<?php
/**
 * Template Loader
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Templates {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('template_include', array($this, 'template_loader'));
        add_shortcode('bk_auction_auctions', array($this, 'auctions_shortcode'));
        add_shortcode('bk_auction_auction', array($this, 'single_auction_shortcode'));
    }

    /**
     * Template loader
     */
    public function template_loader($template) {
        if (is_singular('bk_auction_auction')) {
            $custom_template = $this->locate_template('single-auction.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        if (is_post_type_archive('bk_auction_auction')) {
            $custom_template = $this->locate_template('archive-auction.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Locate template
     */
    private function locate_template($template_name) {
        // Check theme directory first
        $theme_template = locate_template(array(
            'wcam/' . $template_name,
            $template_name,
        ));

        if ($theme_template) {
            return $theme_template;
        }

        // Fall back to plugin template
        $plugin_template = BK_AUCTION_PLUGIN_DIR . 'templates/' . $template_name;
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return false;
    }

    /**
     * Auctions listing shortcode
     */
    public function auctions_shortcode($atts) {
        $atts = shortcode_atts(array(
            'number' => 12,
            'status' => 'active',
            'category' => '',
            'orderby' => 'date',
            'order' => 'DESC',
        ), $atts);

        $args = array(
            'post_type' => 'bk_auction_auction',
            'posts_per_page' => absint($atts['number']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );

        if ($atts['status']) {
            $args['meta_query'] = array(
                array(
                    'key' => '_bk_auction_auction_status',
                    'value' => $atts['status'],
                ),
            );
        }

        if ($atts['category']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'bk_auction_category',
                    'field' => 'slug',
                    'terms' => $atts['category'],
                ),
            );
        }

        $auctions = new WP_Query($args);

        ob_start();
        include BK_AUCTION_PLUGIN_DIR . 'templates/auction-listing.php';
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Single auction shortcode
     */
    public function single_auction_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        if (!$atts['id']) {
            return '';
        }

        $auction = get_post($atts['id']);

        if (!$auction || $auction->post_type !== 'bk_auction_auction') {
            return '';
        }

        ob_start();
        include BK_AUCTION_PLUGIN_DIR . 'templates/single-auction-content.php';
        return ob_get_clean();
    }
}
