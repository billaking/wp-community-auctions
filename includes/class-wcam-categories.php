<?php
/**
 * Auction Categories management class
 * Based on WP Community Church Office Categories design
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Categories {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_bk_auction_save_category', array($this, 'save_category'));
        add_action('wp_ajax_bk_auction_get_categories', array($this, 'get_categories'));
        add_action('wp_ajax_bk_auction_delete_category', array($this, 'delete_category'));
    }

    /**
     * Get default auction categories
     */
    public static function get_default_categories() {
        return array(
            'auction' => array(
                array('slug' => 'art', 'name' => 'Art & Collectibles'),
                array('slug' => 'jewelry', 'name' => 'Jewelry & Watches'),
                array('slug' => 'sports', 'name' => 'Sports Memorabilia'),
                array('slug' => 'electronics', 'name' => 'Electronics'),
                array('slug' => 'home-garden', 'name' => 'Home & Garden'),
                array('slug' => 'services', 'name' => 'Services & Experiences'),
                array('slug' => 'handmade', 'name' => 'Handmade Items'),
                array('slug' => 'other', 'name' => 'Other'),
            ),
            'auction_status' => array(
                array('slug' => 'upcoming', 'name' => 'Upcoming'),
                array('slug' => 'active', 'name' => 'Active'),
                array('slug' => 'ended', 'name' => 'Ended'),
                array('slug' => 'cancelled', 'name' => 'Cancelled'),
            ),
            'payment_methods' => array(
                array('slug' => 'cash', 'name' => 'Cash'),
                array('slug' => 'check', 'name' => 'Check'),
                array('slug' => 'card', 'name' => 'Credit/Debit Card'),
                array('slug' => 'transfer', 'name' => 'Bank Transfer'),
                array('slug' => 'online', 'name' => 'Online Payment'),
                array('slug' => 'mobile', 'name' => 'Mobile Payment'),
                array('slug' => 'other', 'name' => 'Other'),
            ),
        );
    }

    /**
     * Get categories for a specific type
     */
    public static function get_categories_for_type($type) {
        global $wpdb;
        $table = $wpdb->prefix . 'bk_auction_categories';

        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE category_type = %s AND status = 'active' ORDER BY sort_order ASC, name ASC",
            $type
        ));

        // If no custom categories, return defaults
        if (empty($categories)) {
            $defaults = self::get_default_categories();
            if (isset($defaults[$type])) {
                return $defaults[$type];
            }
        }

        return $categories;
    }

    /**
     * Save category
     */
    public function save_category() {
        check_ajax_referer('bk_auction_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'bk-auction-manager')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'bk_auction_categories';

        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

        $data = array(
            'category_type' => sanitize_text_field($_POST['category_type']),
            'slug' => sanitize_title($_POST['slug']),
            'name' => sanitize_text_field($_POST['name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,
        );

        if ($category_id > 0) {
            $result = $wpdb->update($table, $data, array('id' => $category_id));
        } else {
            $result = $wpdb->insert($table, $data);
            $category_id = $wpdb->insert_id;
        }

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Category saved successfully', 'bk-auction-manager'),
                'category_id' => $category_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save category', 'bk-auction-manager')));
        }
    }

    /**
     * Get categories
     */
    public function get_categories() {
        check_ajax_referer('bk_auction_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'bk-auction-manager')));
        }

        $category_type = isset($_POST['category_type']) ? sanitize_text_field($_POST['category_type']) : '';

        global $wpdb;
        $table = $wpdb->prefix . 'bk_auction_categories';

        if ($category_type) {
            $categories = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE category_type = %s AND status = 'active' ORDER BY sort_order ASC, name ASC",
                $category_type
            ));
        } else {
            $categories = $wpdb->get_results("SELECT * FROM $table WHERE status = 'active' ORDER BY category_type, sort_order ASC, name ASC");
        }

        wp_send_json_success(array('categories' => $categories));
    }

    /**
     * Delete category
     */
    public function delete_category() {
        check_ajax_referer('bk_auction_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'bk-auction-manager')));
        }

        $category_id = intval($_POST['category_id']);

        global $wpdb;
        $table = $wpdb->prefix . 'bk_auction_categories';

        $result = $wpdb->update(
            $table,
            array('status' => 'deleted'),
            array('id' => $category_id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => __('Category deleted successfully', 'bk-auction-manager')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete category', 'bk-auction-manager')));
        }
    }
}

BK_AUCTION_Categories::get_instance();
