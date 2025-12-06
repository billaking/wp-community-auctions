<?php
/**
 * Helper Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Format price with currency symbol
 */
function bk_auction_format_price($amount) {
    $symbol = get_option('bk_auction_currency_symbol', '$');
    $position = get_option('bk_auction_currency_position', 'before');
    $formatted_amount = number_format((float)$amount, 2, '.', ',');

    if ($position === 'after') {
        return $formatted_amount . $symbol;
    }

    return $symbol . $formatted_amount;
}

/**
 * Get current bid for an auction
 */
function bk_auction_get_current_bid($auction_id) {
    $current_bid = get_post_meta($auction_id, '_bk_auction_current_bid', true);

    if (empty($current_bid)) {
        $current_bid = get_post_meta($auction_id, '_bk_auction_starting_price', true);
    }

    return (float)$current_bid;
}

/**
 * Get bid count for an auction
 */
function bk_auction_get_bid_count($auction_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'bk_auction_bids';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE auction_id = %d AND status = 'active'",
        $auction_id
    ));

    return absint($count);
}

/**
 * Get highest bidder for an auction
 */
function bk_auction_get_highest_bidder($auction_id) {
    $user_id = get_post_meta($auction_id, '_bk_auction_highest_bidder', true);

    if (!$user_id) {
        return null;
    }

    return get_userdata($user_id);
}

/**
 * Check if user is winning an auction
 */
function bk_auction_is_user_winning($auction_id, $user_id = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    $highest_bidder_id = get_post_meta($auction_id, '_bk_auction_highest_bidder', true);

    return $highest_bidder_id == $user_id;
}

/**
 * Check if user has bid on an auction
 */
function bk_auction_has_user_bid($auction_id, $user_id = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'bk_auction_bids';

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE auction_id = %d AND user_id = %d",
        $auction_id,
        $user_id
    ));

    return $count > 0;
}

/**
 * Get time remaining for an auction
 */
function bk_auction_get_time_remaining($auction_id) {
    $end_date = get_post_meta($auction_id, '_bk_auction_end_date', true);

    if (empty($end_date)) {
        return array('ended' => false, 'time' => '');
    }

    $now = current_time('timestamp');
    $end = strtotime($end_date);
    $diff = $end - $now;

    if ($diff <= 0) {
        return array('ended' => true, 'time' => __('Auction Ended', 'bk-auction-manager'));
    }

    return array(
        'ended' => false,
        'time' => bk_auction_format_time_remaining($diff),
        'seconds' => $diff,
    );
}

/**
 * Format time remaining
 */
function bk_auction_format_time_remaining($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    $parts = array();

    if ($days > 0) {
        $parts[] = sprintf(_n('%d day', '%d days', $days, 'bk-auction-manager'), $days);
    }

    if ($hours > 0 || $days > 0) {
        $parts[] = sprintf(_n('%d hour', '%d hours', $hours, 'bk-auction-manager'), $hours);
    }

    if ($days == 0) {
        $parts[] = sprintf(_n('%d minute', '%d minutes', $minutes, 'bk-auction-manager'), $minutes);
    }

    if ($days == 0 && $hours == 0) {
        $parts[] = sprintf(_n('%d second', '%d seconds', $secs, 'bk-auction-manager'), $secs);
    }

    return implode(', ', $parts);
}

/**
 * Check if auction is active
 */
function bk_auction_is_auction_active($auction_id) {
    $status = get_post_meta($auction_id, '_bk_auction_auction_status', true);
    $time_remaining = bk_auction_get_time_remaining($auction_id);

    return $status === 'active' && !$time_remaining['ended'];
}

/**
 * Get minimum bid amount
 */
function bk_auction_get_minimum_bid($auction_id) {
    $current_bid = bk_auction_get_current_bid($auction_id);
    $starting_price = get_post_meta($auction_id, '_bk_auction_starting_price', true);
    $bid_increment = get_post_meta($auction_id, '_bk_auction_bid_increment', true);

    if (empty($bid_increment)) {
        $bid_increment = 1;
    }

    return $current_bid > 0 ? $current_bid + $bid_increment : $starting_price;
}

/**
 * Get auction status label
 */
function bk_auction_get_status_label($auction_id) {
    $status = get_post_meta($auction_id, '_bk_auction_auction_status', true);

    $labels = array(
        'upcoming' => __('Upcoming', 'bk-auction-manager'),
        'active' => __('Active', 'bk-auction-manager'),
        'ended' => __('Ended', 'bk-auction-manager'),
        'cancelled' => __('Cancelled', 'bk-auction-manager'),
    );

    return isset($labels[$status]) ? $labels[$status] : $status;
}
