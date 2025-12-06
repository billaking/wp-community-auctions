<?php
/**
 * Admin Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCAM_Admin_Settings {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=wcam_auction',
            __('Settings', 'wp-community-auction-manager'),
            __('Settings', 'wp-community-auction-manager'),
            'manage_options',
            'wcam-settings',
            array($this, 'settings_page')
        );

        add_submenu_page(
            'edit.php?post_type=wcam_auction',
            __('All Bids', 'wp-community-auction-manager'),
            __('All Bids', 'wp-community-auction-manager'),
            'manage_options',
            'wcam-bids',
            array($this, 'bids_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wcam_settings', 'wcam_currency_symbol', array(
            'type' => 'string',
            'default' => '$',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('wcam_settings', 'wcam_currency_position', array(
            'type' => 'string',
            'default' => 'before',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('wcam_settings', 'wcam_enable_buy_now', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('wcam_settings', 'wcam_enable_reserve_price', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('wcam_settings', 'wcam_auto_end_auctions', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('wcam_settings', 'wcam_email_notifications', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        register_setting('wcam_settings', 'wcam_allow_frontend_submission', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
    }

    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('wcam_settings');
                ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wcam_currency_symbol"><?php _e('Currency Symbol', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="wcam_currency_symbol" name="wcam_currency_symbol"
                                   value="<?php echo esc_attr(get_option('wcam_currency_symbol', '$')); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wcam_currency_position"><?php _e('Currency Position', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <select id="wcam_currency_position" name="wcam_currency_position">
                                <option value="before" <?php selected(get_option('wcam_currency_position', 'before'), 'before'); ?>>
                                    <?php _e('Before amount ($100)', 'wp-community-auction-manager'); ?>
                                </option>
                                <option value="after" <?php selected(get_option('wcam_currency_position', 'before'), 'after'); ?>>
                                    <?php _e('After amount (100$)', 'wp-community-auction-manager'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wcam_enable_buy_now"><?php _e('Enable Buy Now', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wcam_enable_buy_now" name="wcam_enable_buy_now" value="1"
                                   <?php checked(get_option('wcam_enable_buy_now', true)); ?> />
                            <p class="description"><?php _e('Allow instant purchase option on auctions', 'wp-community-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wcam_enable_reserve_price"><?php _e('Enable Reserve Price', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wcam_enable_reserve_price" name="wcam_enable_reserve_price" value="1"
                                   <?php checked(get_option('wcam_enable_reserve_price', true)); ?> />
                            <p class="description"><?php _e('Allow sellers to set minimum selling price', 'wp-community-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wcam_auto_end_auctions"><?php _e('Auto-End Auctions', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wcam_auto_end_auctions" name="wcam_auto_end_auctions" value="1"
                                   <?php checked(get_option('wcam_auto_end_auctions', true)); ?> />
                            <p class="description"><?php _e('Automatically end auctions when time expires', 'wp-community-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wcam_email_notifications"><?php _e('Email Notifications', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wcam_email_notifications" name="wcam_email_notifications" value="1"
                                   <?php checked(get_option('wcam_email_notifications', true)); ?> />
                            <p class="description"><?php _e('Send email notifications for bids, outbids, and auction completion', 'wp-community-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wcam_allow_frontend_submission"><?php _e('Frontend Auction Submission', 'wp-community-auction-manager'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wcam_allow_frontend_submission" name="wcam_allow_frontend_submission" value="1"
                                   <?php checked(get_option('wcam_allow_frontend_submission', true)); ?> />
                            <p class="description"><?php _e('Allow users to create auctions from the frontend', 'wp-community-auction-manager'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * All bids page
     */
    public function bids_page() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wcam_bids';
        $auction_id = isset($_GET['auction_id']) ? absint($_GET['auction_id']) : 0;

        if ($auction_id) {
            $bids = $wpdb->get_results($wpdb->prepare(
                "SELECT b.*, u.display_name, u.user_email
                 FROM $table_name b
                 LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
                 WHERE b.auction_id = %d
                 ORDER BY b.bid_time DESC",
                $auction_id
            ));

            $auction = get_post($auction_id);
        } else {
            $bids = $wpdb->get_results(
                "SELECT b.*, u.display_name, u.user_email, p.post_title
                 FROM $table_name b
                 LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
                 LEFT JOIN {$wpdb->posts} p ON b.auction_id = p.ID
                 ORDER BY b.bid_time DESC
                 LIMIT 100"
            );
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($auction_id && $auction): ?>
                <h2><?php echo esc_html($auction->post_title); ?></h2>
                <p><a href="<?php echo admin_url('edit.php?post_type=wcam_auction&page=wcam-bids'); ?>">&larr; <?php _e('Back to all bids', 'wp-community-auction-manager'); ?></a></p>
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wp-community-auction-manager'); ?></th>
                        <?php if (!$auction_id): ?>
                            <th><?php _e('Auction', 'wp-community-auction-manager'); ?></th>
                        <?php endif; ?>
                        <th><?php _e('Bidder', 'wp-community-auction-manager'); ?></th>
                        <th><?php _e('Amount', 'wp-community-auction-manager'); ?></th>
                        <th><?php _e('Time', 'wp-community-auction-manager'); ?></th>
                        <th><?php _e('Status', 'wp-community-auction-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bids): ?>
                        <?php foreach ($bids as $bid): ?>
                            <tr>
                                <td><?php echo absint($bid->id); ?></td>
                                <?php if (!$auction_id): ?>
                                    <td>
                                        <?php if (isset($bid->post_title)): ?>
                                            <a href="<?php echo admin_url('edit.php?post_type=wcam_auction&page=wcam-bids&auction_id=' . $bid->auction_id); ?>">
                                                <?php echo esc_html($bid->post_title); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo esc_html($bid->display_name); ?> (<?php echo esc_html($bid->user_email); ?>)</td>
                                <td><?php echo wcam_format_price($bid->bid_amount); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($bid->bid_time))); ?></td>
                                <td><?php echo esc_html($bid->status); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6"><?php _e('No bids found.', 'wp-community-auction-manager'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
