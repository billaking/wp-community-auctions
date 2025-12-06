<?php
/**
 * Test Script - Verify Auction Manager Rendering
 * Visit: http://your-site.local/wp-content/plugins/bk-auction-manager/test-render.php
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You must be an administrator to run this test.');
}

echo '<!DOCTYPE html><html><head><title>Auction Manager Render Test</title>';
echo '<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; } .test { margin: 20px 0; padding: 15px; border-left: 4px solid #2271b1; background: #f0f6fc; } .pass { border-color: #4caf50; background: #e8f5e9; } .fail { border-color: #f44336; background: #ffebee; } h1 { color: #23282d; } h2 { color: #2271b1; margin-top: 30px; }</style>';
echo '</head><body>';

echo '<h1>🔨 Auction Manager Render Test</h1>';
echo '<p>Date: ' . date('Y-m-d H:i:s') . '</p>';

// Test 1: Plugin Active
echo '<div class="test ' . (class_exists('WP_Community_Auction_Manager') ? 'pass' : 'fail') . '">';
echo '<strong>Test 1: Plugin Active</strong><br>';
echo class_exists('WP_Community_Auction_Manager') ? '✅ Plugin class exists' : '❌ Plugin class not found';
echo '</div>';

// Test 2: Admin Class Loaded
echo '<div class="test ' . (class_exists('BK_AUCTION_Admin') ? 'pass' : 'fail') . '">';
echo '<strong>Test 2: Admin Class</strong><br>';
echo class_exists('BK_AUCTION_Admin') ? '✅ BK_AUCTION_Admin class exists' : '❌ BK_AUCTION_Admin class not found';
echo '</div>';

// Test 3: Post Type Registered
$post_types = get_post_types();
echo '<div class="test ' . (in_array('bk_auction_auction', $post_types) ? 'pass' : 'fail') . '">';
echo '<strong>Test 3: Post Type</strong><br>';
echo in_array('bk_auction_auction', $post_types) ? '✅ bk_auction_auction post type registered' : '❌ bk_auction_auction post type not found';
echo '</div>';

// Test 4: View Files Exist
$views = ['dashboard', 'bids', 'reports', 'settings', 'categories'];
$all_exist = true;
echo '<div class="test">';
echo '<strong>Test 4: View Files</strong><br>';
foreach ($views as $view) {
    $file = BK_AUCTION_PLUGIN_DIR . 'admin/views/' . $view . '.php';
    $exists = file_exists($file);
    echo ($exists ? '✅' : '❌') . ' ' . $view . '.php<br>';
    if (!$exists) $all_exist = false;
}
echo '</div>';

// Test 5: CSS File Loaded
echo '<div class="test ' . (file_exists(BK_AUCTION_PLUGIN_DIR . 'assets/css/wcam-admin-style.css') ? 'pass' : 'fail') . '">';
echo '<strong>Test 5: CSS File</strong><br>';
if (file_exists(BK_AUCTION_PLUGIN_DIR . 'assets/css/wcam-admin-style.css')) {
    $css_size = filesize(BK_AUCTION_PLUGIN_DIR . 'assets/css/wcam-admin-style.css');
    echo '✅ wcam-admin-style.css exists (' . number_format($css_size) . ' bytes)';
} else {
    echo '❌ wcam-admin-style.css not found';
}
echo '</div>';

// Test 6: JS File Loaded
echo '<div class="test ' . (file_exists(BK_AUCTION_PLUGIN_DIR . 'assets/js/wcam-admin-script.js') ? 'pass' : 'fail') . '">';
echo '<strong>Test 6: JavaScript File</strong><br>';
if (file_exists(BK_AUCTION_PLUGIN_DIR . 'assets/js/wcam-admin-script.js')) {
    $js_size = filesize(BK_AUCTION_PLUGIN_DIR . 'assets/js/wcam-admin-script.js');
    echo '✅ wcam-admin-script.js exists (' . number_format($js_size) . ' bytes)';
} else {
    echo '❌ wcam-admin-script.js not found';
}
echo '</div>';

// Test 7: Database Tables
global $wpdb;
$bids_table = $wpdb->prefix . 'bk_auction_bids';
$categories_table = $wpdb->prefix . 'bk_auction_categories';

$bids_exists = $wpdb->get_var("SHOW TABLES LIKE '$bids_table'") === $bids_table;
$categories_exists = $wpdb->get_var("SHOW TABLES LIKE '$categories_table'") === $categories_table;

echo '<div class="test ' . ($bids_exists && $categories_exists ? 'pass' : 'fail') . '">';
echo '<strong>Test 7: Database Tables</strong><br>';
echo ($bids_exists ? '✅' : '❌') . ' ' . $bids_table . '<br>';
echo ($categories_exists ? '✅' : '❌') . ' ' . $categories_table;
echo '</div>';

// Test 8: Menu Registration
echo '<div class="test">';
echo '<strong>Test 8: Admin Menu</strong><br>';
echo '⚠️ Menu hooks only register when logged into WordPress admin<br>';
echo 'Please check: <a href="' . admin_url('admin.php?page=wcam-dashboard') . '">Auctions → Dashboard</a>';
echo '</div>';

// Test 9: Helper Functions
echo '<div class="test ' . (function_exists('bk_auction_format_price') ? 'pass' : 'fail') . '">';
echo '<strong>Test 9: Helper Functions</strong><br>';
if (function_exists('bk_auction_format_price')) {
    echo '✅ bk_auction_format_price() exists<br>';
    echo 'Example: ' . bk_auction_format_price(1234.56);
} else {
    echo '❌ bk_auction_format_price() not found';
}
echo '</div>';

// Test 10: Constants
echo '<div class="test ' . (defined('BK_AUCTION_PLUGIN_DIR') && defined('BK_AUCTION_PLUGIN_URL') ? 'pass' : 'fail') . '">';
echo '<strong>Test 10: Plugin Constants</strong><br>';
echo (defined('BK_AUCTION_PLUGIN_DIR') ? '✅' : '❌') . ' BK_AUCTION_PLUGIN_DIR: ' . (defined('BK_AUCTION_PLUGIN_DIR') ? BK_AUCTION_PLUGIN_DIR : 'Not defined') . '<br>';
echo (defined('BK_AUCTION_PLUGIN_URL') ? '✅' : '❌') . ' BK_AUCTION_PLUGIN_URL: ' . (defined('BK_AUCTION_PLUGIN_URL') ? BK_AUCTION_PLUGIN_URL : 'Not defined') . '<br>';
echo (defined('BK_AUCTION_VERSION') ? '✅' : '❌') . ' BK_AUCTION_VERSION: ' . (defined('BK_AUCTION_VERSION') ? BK_AUCTION_VERSION : 'Not defined');
echo '</div>';

echo '<h2>Summary</h2>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li>All tests should show green checkmarks (✅)</li>';
echo '<li>Visit <a href="' . admin_url('admin.php?page=wcam-dashboard') . '">Auctions → Dashboard</a> in WordPress admin</li>';
echo '<li>Clear browser cache: Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)</li>';
echo '<li>Check browser console (F12) for JavaScript errors</li>';
echo '<li>Check for PHP errors in: wp-content/debug.log (if WP_DEBUG is enabled)</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';

echo '</body></html>';
