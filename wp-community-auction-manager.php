<?php
/**
 * Plugin Name: WP Community Auction Manager
 * Plugin URI: https://example.com/wp-community-auction-manager
 * Description: A complete auction management system for WordPress with bidding, user dashboards, and email notifications
 * Version: 1.0.0
 * Author: billaking
 * Author URI: https://github.com/billaking
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-community-auction-manager
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WCAM_VERSION', '2.2.0');
define('WCAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCAM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCAM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WP Community Auction Manager Class
 */
class WP_Community_Auction_Manager {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-post-types.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-meta-boxes.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-bidding.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-notifications.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-user-dashboard.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-admin-settings.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-templates.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-ajax.php';
        require_once WCAM_PLUGIN_DIR . 'includes/class-wcam-cron.php';
        require_once WCAM_PLUGIN_DIR . 'includes/wcam-functions.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wp-community-auction-manager', false, dirname(WCAM_PLUGIN_BASENAME) . '/languages');

        // Initialize classes
        WCAM_Post_Types::get_instance();
        WCAM_Meta_Boxes::get_instance();
        WCAM_Bidding::get_instance();
        WCAM_Notifications::get_instance();
        WCAM_User_Dashboard::get_instance();
        WCAM_Admin_Settings::get_instance();
        WCAM_Templates::get_instance();
        WCAM_Ajax::get_instance();
        WCAM_Cron::get_instance();
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('wcam-style', WCAM_PLUGIN_URL . 'assets/css/wcam-style.css', array(), WCAM_VERSION);
        wp_enqueue_script('wcam-script', WCAM_PLUGIN_URL . 'assets/js/wcam-script.js', array('jquery'), WCAM_VERSION, true);

        // Localize script
        wp_localize_script('wcam-script', 'wcamData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcam_nonce'),
            'strings' => array(
                'bidPlaced' => __('Bid placed successfully!', 'wp-community-auction-manager'),
                'bidError' => __('Error placing bid. Please try again.', 'wp-community-auction-manager'),
                'loginRequired' => __('You must be logged in to place a bid.', 'wp-community-auction-manager'),
            )
        ));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        wp_enqueue_style('wcam-admin-style', WCAM_PLUGIN_URL . 'assets/css/wcam-admin-style.css', array(), WCAM_VERSION);
        wp_enqueue_script('wcam-admin-script', WCAM_PLUGIN_URL . 'assets/js/wcam-admin-script.js', array('jquery', 'jquery-ui-datepicker'), WCAM_VERSION, true);
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create tables
        $this->create_tables();

        // Flush rewrite rules
        WCAM_Post_Types::register_post_types();
        flush_rewrite_rules();

        // Schedule cron jobs
        if (!wp_next_scheduled('wcam_check_expired_auctions')) {
            wp_schedule_event(time(), 'hourly', 'wcam_check_expired_auctions');
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('wcam_check_expired_auctions');

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Bids table
        $table_name = $wpdb->prefix . 'wcam_bids';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            auction_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            bid_amount decimal(10,2) NOT NULL,
            bid_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ip_address varchar(100),
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY  (id),
            KEY auction_id (auction_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Update version
        update_option('wcam_db_version', '1.0.0');
    }
}

/**
 * Initialize the plugin
 */
function wcam_init() {
    return WP_Community_Auction_Manager::get_instance();
}

// Start the plugin
wcam_init();
