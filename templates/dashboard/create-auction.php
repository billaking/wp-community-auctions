<?php
/**
 * Template: Create Auction Form
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!get_option('wcam_allow_frontend_submission', true)) {
    echo '<p>' . __('Auction submission is currently disabled.', 'wp-community-auction-manager') . '</p>';
    return;
}
?>

<div class="wcam-create-auction">

    <!-- Instructions -->
    <div class="wcam-instructions">
        <h3>Create Your Auction</h3>
        <p>List your item for sale in just a few simple steps:</p>
        <ul>
            <li><strong>Add Details</strong> - Provide a clear title and detailed description of your item</li>
            <li><strong>Upload Photos</strong> - Add up to 5 photos showing different angles (Top, Front, Left, Right, Back)</li>
            <li><strong>Set Pricing</strong> - Define starting price, bid increment, and optional Buy Now price</li>
            <li><strong>Choose Dates</strong> - Set when your auction starts and ends</li>
            <li><strong>Review & Submit</strong> - Double-check everything before publishing your auction</li>
        </ul>
    </div>

    <h3><?php _e('Create New Auction', 'wp-community-auction-manager'); ?></h3>

    <form method="post" enctype="multipart/form-data" class="wcam-form">
        <?php wp_nonce_field('wcam_create_auction', 'wcam_create_auction_nonce'); ?>

        <div class="form-group">
            <label for="auction_title"><?php _e('Auction Title *', 'wp-community-auction-manager'); ?></label>
            <input type="text" id="auction_title" name="auction_title" required>
        </div>

        <div class="form-group">
            <label for="auction_description"><?php _e('Description *', 'wp-community-auction-manager'); ?></label>
            <textarea id="auction_description" name="auction_description" rows="6" required></textarea>
        </div>

        <div class="form-group">
            <label><?php _e('Item Photos (Up to 5)', 'wp-community-auction-manager'); ?></label>
            <p class="description" style="margin-bottom: 12px;">
                <?php _e('Upload photos showing different angles of your item. This helps buyers see exactly what they\'re bidding on.', 'wp-community-auction-manager'); ?>
            </p>
            <div class="wcam-photo-upload-grid">
                <?php
                $photo_labels = array(
                    'top' => __('Top View', 'wp-community-auction-manager'),
                    'front' => __('Front View', 'wp-community-auction-manager'),
                    'left' => __('Left Side', 'wp-community-auction-manager'),
                    'right' => __('Right Side', 'wp-community-auction-manager'),
                    'back' => __('Back View', 'wp-community-auction-manager'),
                );
                foreach ($photo_labels as $key => $label):
                ?>
                    <div class="wcam-photo-upload-item">
                        <div class="photo-label">📷 <?php echo esc_html($label); ?></div>
                        <input type="file" id="auction_photo_<?php echo esc_attr($key); ?>" name="auction_photo_<?php echo esc_attr($key); ?>" accept="image/*" class="wcam-file-input">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="starting_price"><?php _e('Starting Price *', 'wp-community-auction-manager'); ?></label>
                <input type="number" id="starting_price" name="starting_price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="bid_increment"><?php _e('Bid Increment *', 'wp-community-auction-manager'); ?></label>
                <input type="number" id="bid_increment" name="bid_increment" step="0.01" min="0.01" value="1.00" required>
            </div>
        </div>

        <?php if (get_option('wcam_enable_reserve_price', true)): ?>
            <div class="form-group">
                <label for="reserve_price"><?php _e('Reserve Price (Optional)', 'wp-community-auction-manager'); ?></label>
                <input type="number" id="reserve_price" name="reserve_price" step="0.01" min="0">
                <p class="description"><?php _e('Minimum price you will accept', 'wp-community-auction-manager'); ?></p>
            </div>
        <?php endif; ?>

        <?php if (get_option('wcam_enable_buy_now', true)): ?>
            <div class="form-group">
                <label for="buy_now_price"><?php _e('Buy Now Price (Optional)', 'wp-community-auction-manager'); ?></label>
                <input type="number" id="buy_now_price" name="buy_now_price" step="0.01" min="0">
                <p class="description"><?php _e('Instant purchase price', 'wp-community-auction-manager'); ?></p>
            </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date"><?php _e('Start Date & Time *', 'wp-community-auction-manager'); ?></label>
                <input type="datetime-local" id="start_date" name="start_date" required>
            </div>

            <div class="form-group">
                <label for="end_date"><?php _e('End Date & Time *', 'wp-community-auction-manager'); ?></label>
                <input type="datetime-local" id="end_date" name="end_date" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="wcam_submit_auction" class="wcam-btn wcam-btn-primary">
                <?php _e('Create Auction', 'wp-community-auction-manager'); ?>
            </button>
        </div>
    </form>
</div>
