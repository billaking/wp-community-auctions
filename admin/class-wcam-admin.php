<?php
/**
 * Auction Manager Admin Class - Modern Dashboard Design
 * Based on Church Office responsive design
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Auction Manager', 'bk-auction-manager'),
            __('Auctions', 'bk-auction-manager'),
            'manage_options',
            'wcam-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-hammer',
            30
        );

        add_submenu_page(
            'wcam-dashboard',
            __('Dashboard', 'bk-auction-manager'),
            __('Dashboard', 'bk-auction-manager'),
            'manage_options',
            'wcam-dashboard',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'wcam-dashboard',
            __('All Auctions', 'bk-auction-manager'),
            __('All Auctions', 'bk-auction-manager'),
            'manage_options',
            'edit.php?post_type=bk_auction_auction'
        );

        add_submenu_page(
            'wcam-dashboard',
            __('Add New Auction', 'bk-auction-manager'),
            __('Add New', 'bk-auction-manager'),
            'manage_options',
            'post-new.php?post_type=bk_auction_auction'
        );

        add_submenu_page(
            'wcam-dashboard',
            __('All Bids', 'bk-auction-manager'),
            __('All Bids', 'bk-auction-manager'),
            'manage_options',
            'wcam-bids',
            array($this, 'render_bids')
        );

        add_submenu_page(
            'wcam-dashboard',
            __('Categories', 'bk-auction-manager'),
            __('Categories', 'bk-auction-manager'),
            'manage_options',
            'wcam-categories',
            array($this, 'render_categories')
        );

        add_submenu_page(
            'wcam-dashboard',
            __('Reports', 'bk-auction-manager'),
            __('Reports', 'bk-auction-manager'),
            'manage_options',
            'wcam-reports',
            array($this, 'render_reports')
        );

        add_submenu_page(
            'wcam-dashboard',
            __('Settings', 'bk-auction-manager'),
            __('Settings', 'bk-auction-manager'),
            'manage_options',
            'wcam-settings',
            array($this, 'render_settings')
        );
    }

    /**
     * Render Dashboard
     */
    public function render_dashboard() {
        include BK_AUCTION_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render Bids Page
     */
    public function render_bids() {
        include BK_AUCTION_PLUGIN_DIR . 'admin/views/bids.php';
    }

    /**
     * Render Categories Page
     */
    public function render_categories() {
        include BK_AUCTION_PLUGIN_DIR . 'admin/views/categories.php';
    }

    /**
     * Render Reports Page
     */
    public function render_reports() {
        include BK_AUCTION_PLUGIN_DIR . 'admin/views/reports.php';
    }

    /**
     * Render Settings Page
     */
    public function render_settings() {
        include BK_AUCTION_PLUGIN_DIR . 'admin/views/settings.php';
    }
}

BK_AUCTION_Admin::get_instance();
