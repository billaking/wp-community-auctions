# WP Community Auction Manager

A complete auction management system for WordPress with bidding, user dashboards, and email notifications.

## Features

### Core Functionality
- **Custom Auction Post Type** - Dedicated post type for managing auctions
- **Real-Time Bidding** - AJAX-powered bidding system with instant updates
- **Buy Now Option** - Allow instant purchases at a set price
- **Reserve Pricing** - Set minimum acceptable prices for auctions
- **Automatic Auction Ending** - Cron job to automatically close expired auctions
- **Bid Increment Control** - Define minimum bid increments

### User Features
- **User Dashboards** - Comprehensive dashboards for buyers and sellers
- **My Auctions** - Sellers can view and manage their auctions
- **My Bids** - Track all bids and see winning status
- **Frontend Auction Creation** - Users can create auctions from the frontend
- **Bid Tracking** - See all bids on each auction

### Notification System
- **Bid Confirmation Emails** - Buyers receive confirmation when they place a bid
- **Outbid Alerts** - Automatic notifications when someone outbids you
- **Auction Won Emails** - Winners are notified when auction ends
- **Seller Notifications** - Sellers receive updates on new bids and sales

### Admin Features
- **Settings Panel** - Configure currency, features, and behavior
- **Bid Management** - View and manage all bids across all auctions
- **Auction Categories & Tags** - Organize auctions with taxonomies
- **Detailed Reporting** - View auction statistics and bid history

## Installation

1. Upload the `wp-community-auction-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Auctions' > 'Settings' to configure the plugin
4. Create your first auction!

## Usage

### Creating an Auction (Admin)

1. Go to **Auctions** > **Add New**
2. Enter auction title and description
3. Set the auction details:
   - Starting price
   - Reserve price (optional)
   - Buy now price (optional)
   - Bid increment
   - Start and end dates
4. Add a featured image
5. Assign categories/tags
6. Publish the auction

### Creating an Auction (Frontend)

Users can create auctions from the frontend using the `[wcam_create_auction]` shortcode.

### Shortcodes

**Display Auction Listings:**
```
[wcam_auctions number="12" status="active" orderby="date" order="DESC"]
```

**User Dashboard:**
```
[wcam_dashboard]
```

**My Auctions:**
```
[wcam_my_auctions]
```

**My Bids:**
```
[wcam_my_bids]
```

**Create Auction Form:**
```
[wcam_create_auction]
```

**Single Auction:**
```
[wcam_auction id="123"]
```

### Settings

Navigate to **Auctions** > **Settings** to configure:

- **Currency Symbol** - Set your currency symbol (default: $)
- **Currency Position** - Before or after the amount
- **Enable Buy Now** - Allow instant purchases
- **Enable Reserve Price** - Allow minimum selling prices
- **Auto-End Auctions** - Automatically close expired auctions
- **Email Notifications** - Enable/disable email notifications
- **Frontend Submission** - Allow users to create auctions from frontend

## Database

The plugin creates the following custom table:

- `wp_wcam_bids` - Stores all bid information

## Templates

You can override plugin templates by copying them to your theme:

1. Create a folder `wcam` in your theme directory
2. Copy template files from `wp-community-auction-manager/templates/`
3. Customize as needed

Available templates:
- `single-auction.php` - Single auction page
- `archive-auction.php` - Auction listing archive
- `auction-listing.php` - Shortcode auction grid
- `dashboard/main.php` - Main dashboard
- `dashboard/my-auctions.php` - User's auctions
- `dashboard/my-bids.php` - User's bids
- `dashboard/create-auction.php` - Create auction form

## Hooks & Filters

### Actions

```php
// Triggered when a bid is placed
do_action('wcam_bid_placed', $bid_id, $auction_id, $user_id, $bid_amount);

// Triggered when buy now is completed
do_action('wcam_buy_now_completed', $auction_id, $user_id, $amount);

// Triggered when an auction ends
do_action('wcam_auction_ended', $auction_id);
```

### Filters

```php
// Modify formatted price display
apply_filters('wcam_format_price', $formatted_price, $amount);

// Modify minimum bid calculation
apply_filters('wcam_minimum_bid', $minimum_bid, $auction_id);
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Support

For support, please visit [your support URL]

## Changelog

### 1.0.0
- Initial release
- Custom auction post type
- Real-time bidding system
- User dashboards
- Email notifications
- Admin settings panel
- Frontend auction creation
- Buy now functionality
- Reserve pricing
- Automatic auction ending

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name]
