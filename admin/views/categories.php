<div class="wrap wcam-categories">
    <h1><?php _e('Auction Category Management', 'bk-auction-manager'); ?></h1>

    <div class="wcam-page-header" style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
        <select id="bk-auction-category-type-selector" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="auction"><?php _e('Auction Categories', 'bk-auction-manager'); ?></option>
            <option value="auction_status"><?php _e('Auction Status', 'bk-auction-manager'); ?></option>
            <option value="payment_methods"><?php _e('Payment Methods', 'bk-auction-manager'); ?></option>
        </select>

        <button class="button button-primary wcam-btn-primary" id="bk-auction-add-category-btn">
            <span class="dashicons dashicons-plus"></span>
            <?php _e('Add Category', 'bk-auction-manager'); ?>
        </button>
    </div>

    <div id="bk-auction-category-form" class="wcam-dashboard-section" style="display: none;">
        <h2 id="bk-auction-category-form-title"><?php _e('Add Category', 'bk-auction-manager'); ?></h2>
        <form id="bk-auction-category-form-element">
            <input type="hidden" id="category-id" name="category_id" value="0">
            <input type="hidden" id="category-type" name="category_type" value="">

            <table class="form-table">
                <tr>
                    <th><label for="category-name"><?php _e('Name', 'bk-auction-manager'); ?></label></th>
                    <td><input type="text" id="category-name" name="name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="category-slug"><?php _e('Slug', 'bk-auction-manager'); ?></label></th>
                    <td>
                        <input type="text" id="category-slug" name="slug" class="regular-text" required>
                        <p class="description"><?php _e('Unique identifier (lowercase, no spaces)', 'bk-auction-manager'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="category-description"><?php _e('Description', 'bk-auction-manager'); ?></label></th>
                    <td><textarea id="category-description" name="description" class="large-text" rows="3"></textarea></td>
                </tr>
                <tr>
                    <th><label for="category-sort-order"><?php _e('Sort Order', 'bk-auction-manager'); ?></label></th>
                    <td>
                        <input type="number" id="category-sort-order" name="sort_order" value="0" min="0">
                        <p class="description"><?php _e('Lower numbers appear first', 'bk-auction-manager'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary wcam-btn-primary"><?php _e('Save Category', 'bk-auction-manager'); ?></button>
                <button type="button" class="button" id="bk-auction-cancel-category"><?php _e('Cancel', 'bk-auction-manager'); ?></button>
            </p>
        </form>
    </div>

    <div class="wcam-dashboard-section">
        <h2><?php _e('Categories', 'bk-auction-manager'); ?></h2>
        <table class="wp-list-table widefat fixed striped wcam-table" id="bk-auction-categories-table">
            <thead>
                <tr>
                    <th><?php _e('Name', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Slug', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Description', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Sort Order', 'bk-auction-manager'); ?></th>
                    <th><?php _e('Actions', 'bk-auction-manager'); ?></th>
                </tr>
            </thead>
            <tbody id="bk-auction-categories-list">
                <tr>
                    <td colspan="5"><?php _e('Loading categories...', 'bk-auction-manager'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="wcam-dashboard-section wcam-message wcam-message-info">
        <h3><?php _e('About Auction Categories', 'bk-auction-manager'); ?></h3>
        <p><?php _e('Categories help organize your auctions and improve the browsing experience for bidders. You can create custom categories to match your specific auction items.', 'bk-auction-manager'); ?></p>
        <ul>
            <li><?php _e('<strong>Auction Categories:</strong> Organize items like art, jewelry, electronics, services, and more.', 'bk-auction-manager'); ?></li>
            <li><?php _e('<strong>Auction Status:</strong> Track auction lifecycle stages (upcoming, active, ended, cancelled).', 'bk-auction-manager'); ?></li>
            <li><?php _e('<strong>Payment Methods:</strong> Define how winners can pay for their winning bids (cash, check, card, transfers, etc.).', 'bk-auction-manager'); ?></li>
        </ul>
    </div>
</div>

<style>
.wcam-delete-btn {
    color: #f44336 !important;
}
.wcam-delete-btn:hover {
    color: #da190b !important;
}
</style>
