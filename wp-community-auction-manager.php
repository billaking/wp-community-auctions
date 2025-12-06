<?php
/**
 * Plugin Name: WP Community Auction Manager
 * Plugin URI: https://example.com/bk-auction-manager
 * Description: A complete auction management system for WordPress with bidding, user dashboards, and email notifications
 * Version: 1.0.0
 * Author: billaking
 * Author URI: https://github.com/billaking
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bk-auction-manager
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WP Community Core plugin is active
 */
function bk_auction_check_dependencies() {
    // Check if WP Community Core is active
    if (!class_exists('BK_Community_Core')) {
        add_action('admin_notices', 'bk_auction_missing_dependency_notice');
        return false;
    }
    return true;
}

/**
 * Display admin notice when dependency is missing
 */
function bk_auction_missing_dependency_notice() {
    ?>
    <div class="notice notice-warning">
        <p>
            <strong>WP Community Auction Manager</strong> works best with the <strong>WP Community Core</strong> plugin.
            For full functionality, please install and activate <a href="<?php echo admin_url('plugin-install.php?s=wp-community-core&tab=search&type=term'); ?>">WP Community Core</a>.
        </p>
    </div>
    <?php
}

// Check dependencies (but don't prevent loading)
bk_auction_check_dependencies();

// Define plugin constants
define('BK_AUCTION_VERSION', '2.2.0');
define('BK_AUCTION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BK_AUCTION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BK_AUCTION_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main WP Community Auction Manager Class
 */
class BK_Auction_Manager {

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
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-post-types.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-meta-boxes.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-bidding.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-notifications.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-user-dashboard.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'admin/class-wcam-admin.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-templates.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-ajax.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-cron.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/class-wcam-categories.php';
        require_once BK_AUCTION_PLUGIN_DIR . 'includes/wcam-functions.php';
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
        load_plugin_textdomain('bk-auction-manager', false, dirname(BK_AUCTION_PLUGIN_BASENAME) . '/languages');

        // Initialize classes
        BK_AUCTION_Post_Types::get_instance();
        BK_AUCTION_Meta_Boxes::get_instance();
        BK_AUCTION_Bidding::get_instance();
        BK_AUCTION_Notifications::get_instance();
        BK_AUCTION_User_Dashboard::get_instance();
        BK_AUCTION_Admin::get_instance();
        BK_AUCTION_Templates::get_instance();
        BK_AUCTION_Ajax::get_instance();
        BK_AUCTION_Cron::get_instance();
        BK_AUCTION_Categories::get_instance();
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on auction pages
        if (!is_singular('auction') && !is_post_type_archive('auction') && !is_page()) {
            return;
        }

        wp_enqueue_style('wcam-style', BK_AUCTION_PLUGIN_URL . 'assets/css/wcam-style.css', array(), BK_AUCTION_VERSION);
        wp_enqueue_script('wcam-script', BK_AUCTION_PLUGIN_URL . 'assets/js/wcam-script.js', array('jquery'), BK_AUCTION_VERSION, true);

        // Localize script
        wp_localize_script('wcam-script', 'wcamData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bk_auction_nonce'),
            'strings' => array(
                'bidPlaced' => __('Bid placed successfully!', 'bk-auction-manager'),
                'bidError' => __('Error placing bid. Please try again.', 'bk-auction-manager'),
                'loginRequired' => __('You must be logged in to place a bid.', 'bk-auction-manager'),
            )
        ));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on auction post type pages
        global $post_type;
        if ('bk_auction_auction' !== $post_type && strpos($hook, 'wcam-') === false) {
            return;
        }

        wp_enqueue_style('wcam-admin-style', BK_AUCTION_PLUGIN_URL . 'assets/css/wcam-admin-style.css', array(), BK_AUCTION_VERSION);
        wp_enqueue_script('wcam-admin-script', BK_AUCTION_PLUGIN_URL . 'assets/js/wcam-admin-script.js', array('jquery', 'jquery-ui-datepicker'), BK_AUCTION_VERSION, true);
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1');

        // Localize admin script
        wp_localize_script('wcam-admin-script', 'wcamAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bk_auction_ajax_nonce'),
        ));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create tables
        $this->create_tables();

        // Flush rewrite rules
        BK_AUCTION_Post_Types::register_post_types();
        flush_rewrite_rules();

        // Schedule cron jobs
        if (!wp_next_scheduled('bk_auction_check_expired_auctions')) {
            wp_schedule_event(time(), 'hourly', 'bk_auction_check_expired_auctions');
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('bk_auction_check_expired_auctions');

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
        $table_name = $wpdb->prefix . 'bk_auction_bids';
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

        // Categories table
        $categories_table = $wpdb->prefix . 'bk_auction_categories';
        $sql_categories = "CREATE TABLE $categories_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            category_type varchar(50) NOT NULL,
            slug varchar(100) NOT NULL,
            name varchar(200) NOT NULL,
            description text,
            sort_order int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY category_type (category_type),
            KEY slug (slug),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql_categories);

        // Update version
        update_option('bk_auction_db_version', '1.1.0');
    }
}

/**
 * Initialize the plugin
 */
function bk_auction_init() {
    return WP_Community_Auction_Manager::get_instance();
}

// Start the plugin
bk_auction_init();
