<?php
/**
 * Settings Page - Modern Configuration Interface
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle settings save
if (isset($_POST['bk_auction_save_settings']) && check_admin_referer('bk_auction_settings_nonce')) {
    // General Settings
    update_option('bk_auction_currency_symbol', sanitize_text_field($_POST['bk_auction_currency_symbol']));
    update_option('bk_auction_currency_position', sanitize_text_field($_POST['bk_auction_currency_position']));
    update_option('bk_auction_decimal_separator', sanitize_text_field($_POST['bk_auction_decimal_separator']));
    update_option('bk_auction_thousand_separator', sanitize_text_field($_POST['bk_auction_thousand_separator']));
    update_option('bk_auction_number_decimals', intval($_POST['bk_auction_number_decimals']));

    // Auction Settings
    update_option('bk_auction_bid_increment', floatval($_POST['bk_auction_bid_increment']));
    update_option('bk_auction_auto_extend_enabled', isset($_POST['bk_auction_auto_extend_enabled']) ? '1' : '0');
    update_option('bk_auction_auto_extend_minutes', intval($_POST['bk_auction_auto_extend_minutes']));
    update_option('bk_auction_require_login', isset($_POST['bk_auction_require_login']) ? '1' : '0');

    // Email Settings
    update_option('bk_auction_email_from_name', sanitize_text_field($_POST['bk_auction_email_from_name']));
    update_option('bk_auction_email_from_address', sanitize_email($_POST['bk_auction_email_from_address']));
    update_option('bk_auction_notify_admin_new_bid', isset($_POST['bk_auction_notify_admin_new_bid']) ? '1' : '0');
    update_option('bk_auction_notify_bidder_outbid', isset($_POST['bk_auction_notify_bidder_outbid']) ? '1' : '0');
    update_option('bk_auction_notify_winner', isset($_POST['bk_auction_notify_winner']) ? '1' : '0');

    // Display Settings
    update_option('bk_auction_show_countdown', isset($_POST['bk_auction_show_countdown']) ? '1' : '0');
    update_option('bk_auction_show_bid_history', isset($_POST['bk_auction_show_bid_history']) ? '1' : '0');
    update_option('bk_auction_auctions_per_page', intval($_POST['bk_auction_auctions_per_page']));

    echo '<div class="wcam-message wcam-message-success" style="margin: 20px 0;"><p>' . __('Settings saved successfully!', 'bk-auction-manager') . '</p></div>';
}

// Get current settings
$settings = array(
    'currency_symbol' => get_option('bk_auction_currency_symbol', '$'),
    'currency_position' => get_option('bk_auction_currency_position', 'before'),
    'decimal_separator' => get_option('bk_auction_decimal_separator', '.'),
    'thousand_separator' => get_option('bk_auction_thousand_separator', ','),
    'number_decimals' => get_option('bk_auction_number_decimals', 2),
    'bid_increment' => get_option('bk_auction_bid_increment', 1.00),
    'auto_extend_enabled' => get_option('bk_auction_auto_extend_enabled', '0'),
    'auto_extend_minutes' => get_option('bk_auction_auto_extend_minutes', 5),
    'require_login' => get_option('bk_auction_require_login', '1'),
    'email_from_name' => get_option('bk_auction_email_from_name', get_bloginfo('name')),
    'email_from_address' => get_option('bk_auction_email_from_address', get_option('admin_email')),
    'notify_admin_new_bid' => get_option('bk_auction_notify_admin_new_bid', '1'),
    'notify_bidder_outbid' => get_option('bk_auction_notify_bidder_outbid', '1'),
    'notify_winner' => get_option('bk_auction_notify_winner', '1'),
    'show_countdown' => get_option('bk_auction_show_countdown', '1'),
    'show_bid_history' => get_option('bk_auction_show_bid_history', '1'),
    'auctions_per_page' => get_option('bk_auction_auctions_per_page', 12),
);

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap wcam-settings-page">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-settings" style="font-size: 28px; color: #2271b1; vertical-align: middle;"></span>
        <?php _e('Auction Settings', 'bk-auction-manager'); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Settings Tabs -->
    <div class="wcam-settings-tabs" style="margin: 20px 0;">
        <a href="?page=wcam-settings&tab=general" class="wcam-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php _e('General', 'bk-auction-manager'); ?>
        </a>
        <a href="?page=wcam-settings&tab=auction" class="wcam-tab <?php echo $active_tab === 'auction' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-megaphone"></span>
            <?php _e('Auction', 'bk-auction-manager'); ?>
        </a>
        <a href="?page=wcam-settings&tab=email" class="wcam-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-email"></span>
            <?php _e('Email', 'bk-auction-manager'); ?>
        </a>
        <a href="?page=wcam-settings&tab=display" class="wcam-tab <?php echo $active_tab === 'display' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-desktop"></span>
            <?php _e('Display', 'bk-auction-manager'); ?>
        </a>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('bk_auction_settings_nonce'); ?>

        <!-- General Tab -->
        <?php if ($active_tab === 'general'): ?>
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php _e('General Settings', 'bk-auction-manager'); ?>
                </h2>

                <table class="wcam-form-table">
                    <tr>
                        <th><?php _e('Currency Symbol', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="text" name="bk_auction_currency_symbol" value="<?php echo esc_attr($settings['currency_symbol']); ?>" class="wcam-input-small">
                            <p class="wcam-description"><?php _e('Currency symbol to display (e.g., $, €, £)', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Currency Position', 'bk-auction-manager'); ?></th>
                        <td>
                            <select name="bk_auction_currency_position" class="wcam-select">
                                <option value="before" <?php selected($settings['currency_position'], 'before'); ?>><?php _e('Before amount ($100)', 'bk-auction-manager'); ?></option>
                                <option value="after" <?php selected($settings['currency_position'], 'after'); ?>><?php _e('After amount (100$)', 'bk-auction-manager'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Decimal Separator', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="text" name="bk_auction_decimal_separator" value="<?php echo esc_attr($settings['decimal_separator']); ?>" class="wcam-input-small">
                            <p class="wcam-description"><?php _e('Character to separate decimals (e.g., . or ,)', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Thousand Separator', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="text" name="bk_auction_thousand_separator" value="<?php echo esc_attr($settings['thousand_separator']); ?>" class="wcam-input-small">
                            <p class="wcam-description"><?php _e('Character to separate thousands (e.g., , or .)', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Number of Decimals', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="number" name="bk_auction_number_decimals" value="<?php echo esc_attr($settings['number_decimals']); ?>" min="0" max="4" class="wcam-input-small">
                            <p class="wcam-description"><?php _e('Number of decimal places to display', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <!-- Auction Tab -->
        <?php if ($active_tab === 'auction'): ?>
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php _e('Auction Settings', 'bk-auction-manager'); ?>
                </h2>

                <table class="wcam-form-table">
                    <tr>
                        <th><?php _e('Minimum Bid Increment', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="number" name="bk_auction_bid_increment" value="<?php echo esc_attr($settings['bid_increment']); ?>" step="0.01" min="0" class="wcam-input-medium">
                            <p class="wcam-description"><?php _e('Minimum amount each bid must increase', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Auto-Extend Auctions', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_auto_extend_enabled" value="1" <?php checked($settings['auto_extend_enabled'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Automatically extend auction time when bid is placed near end', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Auto-Extend Duration', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="number" name="bk_auction_auto_extend_minutes" value="<?php echo esc_attr($settings['auto_extend_minutes']); ?>" min="1" max="60" class="wcam-input-small">
                            <span><?php _e('minutes', 'bk-auction-manager'); ?></span>
                            <p class="wcam-description"><?php _e('How many minutes to extend when bid is placed in final minutes', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Require Login to Bid', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_require_login" value="1" <?php checked($settings['require_login'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Users must be logged in to place bids', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <!-- Email Tab -->
        <?php if ($active_tab === 'email'): ?>
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-email"></span>
                    <?php _e('Email Settings', 'bk-auction-manager'); ?>
                </h2>

                <table class="wcam-form-table">
                    <tr>
                        <th><?php _e('From Name', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="text" name="bk_auction_email_from_name" value="<?php echo esc_attr($settings['email_from_name']); ?>" class="wcam-input-large">
                            <p class="wcam-description"><?php _e('Name shown in email "From" field', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('From Email Address', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="email" name="bk_auction_email_from_address" value="<?php echo esc_attr($settings['email_from_address']); ?>" class="wcam-input-large">
                            <p class="wcam-description"><?php _e('Email address shown in "From" field', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Notify Admin of New Bids', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_notify_admin_new_bid" value="1" <?php checked($settings['notify_admin_new_bid'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Send email to admin when new bid is placed', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Notify Bidders When Outbid', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_notify_bidder_outbid" value="1" <?php checked($settings['notify_bidder_outbid'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Send email when a bidder is outbid', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Notify Auction Winners', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_notify_winner" value="1" <?php checked($settings['notify_winner'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Send email to winner when auction ends', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <!-- Display Tab -->
        <?php if ($active_tab === 'display'): ?>
            <div class="wcam-dashboard-section">
                <h2>
                    <span class="dashicons dashicons-desktop"></span>
                    <?php _e('Display Settings', 'bk-auction-manager'); ?>
                </h2>

                <table class="wcam-form-table">
                    <tr>
                        <th><?php _e('Show Countdown Timer', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_show_countdown" value="1" <?php checked($settings['show_countdown'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Display countdown timer on auction pages', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Show Bid History', 'bk-auction-manager'); ?></th>
                        <td>
                            <label class="wcam-toggle">
                                <input type="checkbox" name="bk_auction_show_bid_history" value="1" <?php checked($settings['show_bid_history'], '1'); ?>>
                                <span class="wcam-toggle-slider"></span>
                            </label>
                            <p class="wcam-description"><?php _e('Display bid history on auction pages', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Auctions Per Page', 'bk-auction-manager'); ?></th>
                        <td>
                            <input type="number" name="bk_auction_auctions_per_page" value="<?php echo esc_attr($settings['auctions_per_page']); ?>" min="1" max="100" class="wcam-input-small">
                            <p class="wcam-description"><?php _e('Number of auctions to show per page in archive', 'bk-auction-manager'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <!-- Save Button -->
        <div class="wcam-dashboard-section">
            <button type="submit" name="bk_auction_save_settings" class="wcam-btn wcam-btn-primary wcam-btn-large">
                <span class="dashicons dashicons-saved"></span>
                <?php _e('Save Settings', 'bk-auction-manager'); ?>
            </button>
        </div>
    </form>
</div>

<style>
.wcam-settings-tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 0;
}

.wcam-tab {
    padding: 12px 20px;
    background: #f0f0f0;
    border: 1px solid #ddd;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    text-decoration: none;
    color: #555;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.wcam-tab .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.wcam-tab:hover {
    background: #e8e8e8;
    color: #2271b1;
}

.wcam-tab.active {
    background: #fff;
    color: #2271b1;
    border-bottom: 2px solid #fff;
    margin-bottom: -2px;
}

.wcam-form-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.wcam-form-table tr {
    border-bottom: 1px solid #eee;
}

.wcam-form-table th {
    width: 30%;
    padding: 20px;
    text-align: left;
    font-weight: 500;
    color: #23282d;
    vertical-align: top;
}

.wcam-form-table td {
    padding: 20px;
}

.wcam-description {
    margin: 8px 0 0 0;
    color: #666;
    font-size: 13px;
}

.wcam-input-small {
    width: 100px;
}

.wcam-input-medium {
    width: 200px;
}

.wcam-input-large {
    width: 400px;
    max-width: 100%;
}

.wcam-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

/* Toggle Switch */
.wcam-toggle {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.wcam-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.wcam-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 26px;
}

.wcam-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.wcam-toggle input:checked + .wcam-toggle-slider {
    background-color: #2271b1;
}

.wcam-toggle input:checked + .wcam-toggle-slider:before {
    transform: translateX(24px);
}

.wcam-btn-large {
    padding: 12px 30px !important;
    font-size: 16px !important;
}

@media (max-width: 782px) {
    .wcam-settings-tabs {
        flex-wrap: wrap;
    }

    .wcam-form-table th,
    .wcam-form-table td {
        display: block;
        width: 100%;
        padding: 10px;
    }

    .wcam-form-table th {
        padding-bottom: 5px;
    }

    .wcam-input-large {
        width: 100%;
    }
}
</style>
