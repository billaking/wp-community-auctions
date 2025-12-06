<?php
/**
 * Email Notifications
 */

if (!defined('ABSPATH')) {
    exit;
}

class BK_AUCTION_Notifications {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('bk_auction_bid_placed', array($this, 'notify_bid_placed'), 10, 4);
        add_action('bk_auction_buy_now_completed', array($this, 'notify_buy_now'), 10, 3);
        add_action('bk_auction_auction_ended', array($this, 'notify_auction_ended'), 10, 1);
    }

    /**
     * Notify when bid is placed
     */
    public function notify_bid_placed($bid_id, $auction_id, $user_id, $bid_amount) {
        // Notify auction owner
        $auction = get_post($auction_id);
        $owner_email = get_the_author_meta('user_email', $auction->post_author);
        $bidder = get_userdata($user_id);

        $subject = sprintf(__('[%s] New bid on your auction: %s', 'bk-auction-manager'),
            get_bloginfo('name'),
            $auction->post_title
        );

        $message = sprintf(
            __("Hello,\n\nA new bid has been placed on your auction '%s'.\n\nBid Amount: %s\nBidder: %s\n\nView auction: %s\n\nThank you!", 'bk-auction-manager'),
            $auction->post_title,
            bk_auction_format_price($bid_amount),
            $bidder->display_name,
            get_permalink($auction_id)
        );

        wp_mail($owner_email, $subject, $message);

        // Notify previous highest bidder (they've been outbid)
        $previous_bidder_id = get_post_meta($auction_id, '_bk_auction_highest_bidder', true);
        if ($previous_bidder_id && $previous_bidder_id != $user_id) {
            $previous_bidder = get_userdata($previous_bidder_id);

            $outbid_subject = sprintf(__('[%s] You have been outbid on: %s', 'bk-auction-manager'),
                get_bloginfo('name'),
                $auction->post_title
            );

            $outbid_message = sprintf(
                __("Hello %s,\n\nYou have been outbid on the auction '%s'.\n\nNew Bid Amount: %s\n\nPlace a new bid: %s\n\nThank you!", 'bk-auction-manager'),
                $previous_bidder->display_name,
                $auction->post_title,
                bk_auction_format_price($bid_amount),
                get_permalink($auction_id)
            );

            wp_mail($previous_bidder->user_email, $outbid_subject, $outbid_message);
        }

        // Notify current bidder (confirmation)
        $confirmation_subject = sprintf(__('[%s] Bid confirmation for: %s', 'bk-auction-manager'),
            get_bloginfo('name'),
            $auction->post_title
        );

        $confirmation_message = sprintf(
            __("Hello %s,\n\nYour bid has been successfully placed on '%s'.\n\nYour Bid Amount: %s\n\nView auction: %s\n\nThank you!", 'bk-auction-manager'),
            $bidder->display_name,
            $auction->post_title,
            bk_auction_format_price($bid_amount),
            get_permalink($auction_id)
        );

        wp_mail($bidder->user_email, $confirmation_subject, $confirmation_message);
    }

    /**
     * Notify when buy now is completed
     */
    public function notify_buy_now($auction_id, $user_id, $amount) {
        $auction = get_post($auction_id);
        $buyer = get_userdata($user_id);
        $owner_email = get_the_author_meta('user_email', $auction->post_author);

        // Notify buyer
        $buyer_subject = sprintf(__('[%s] Purchase confirmation for: %s', 'bk-auction-manager'),
            get_bloginfo('name'),
            $auction->post_title
        );

        $buyer_message = sprintf(
            __("Hello %s,\n\nCongratulations! You have successfully purchased '%s' using Buy Now.\n\nPurchase Amount: %s\n\nThe seller will contact you soon.\n\nThank you!", 'bk-auction-manager'),
            $buyer->display_name,
            $auction->post_title,
            bk_auction_format_price($amount)
        );

        wp_mail($buyer->user_email, $buyer_subject, $buyer_message);

        // Notify seller
        $seller_subject = sprintf(__('[%s] Your auction has been sold: %s', 'bk-auction-manager'),
            get_bloginfo('name'),
            $auction->post_title
        );

        $seller_message = sprintf(
            __("Hello,\n\nYour auction '%s' has been sold via Buy Now.\n\nSale Amount: %s\nBuyer: %s (%s)\n\nPlease contact the buyer to arrange payment and delivery.\n\nThank you!", 'bk-auction-manager'),
            $auction->post_title,
            bk_auction_format_price($amount),
            $buyer->display_name,
            $buyer->user_email
        );

        wp_mail($owner_email, $seller_subject, $seller_message);
    }

    /**
     * Notify when auction ends
     */
    public function notify_auction_ended($auction_id) {
        $auction = get_post($auction_id);
        $highest_bidder_id = get_post_meta($auction_id, '_bk_auction_highest_bidder', true);
        $current_bid = bk_auction_get_current_bid($auction_id);
        $reserve_price = get_post_meta($auction_id, '_bk_auction_reserve_price', true);

        // Check if reserve price was met
        $reserve_met = empty($reserve_price) || $current_bid >= $reserve_price;

        // Notify winner
        if ($highest_bidder_id && $reserve_met) {
            $winner = get_userdata($highest_bidder_id);

            $winner_subject = sprintf(__('[%s] Congratulations! You won: %s', 'bk-auction-manager'),
                get_bloginfo('name'),
                $auction->post_title
            );

            $winner_message = sprintf(
                __("Hello %s,\n\nCongratulations! You have won the auction '%s'.\n\nWinning Bid: %s\n\nThe seller will contact you soon to arrange payment and delivery.\n\nThank you!", 'bk-auction-manager'),
                $winner->display_name,
                $auction->post_title,
                bk_auction_format_price($current_bid)
            );

            wp_mail($winner->user_email, $winner_subject, $winner_message);

            // Notify seller
            $owner_email = get_the_author_meta('user_email', $auction->post_author);

            $seller_subject = sprintf(__('[%s] Your auction has ended: %s', 'bk-auction-manager'),
                get_bloginfo('name'),
                $auction->post_title
            );

            $seller_message = sprintf(
                __("Hello,\n\nYour auction '%s' has ended.\n\nFinal Bid: %s\nWinner: %s (%s)\n\nPlease contact the winner to arrange payment and delivery.\n\nThank you!", 'bk-auction-manager'),
                $auction->post_title,
                bk_auction_format_price($current_bid),
                $winner->display_name,
                $winner->user_email
            );

            wp_mail($owner_email, $seller_subject, $seller_message);
        } elseif ($highest_bidder_id && !$reserve_met) {
            // Reserve not met
            $owner_email = get_the_author_meta('user_email', $auction->post_author);

            $subject = sprintf(__('[%s] Auction ended - Reserve not met: %s', 'bk-auction-manager'),
                get_bloginfo('name'),
                $auction->post_title
            );

            $message = sprintf(
                __("Hello,\n\nYour auction '%s' has ended, but the reserve price was not met.\n\nFinal Bid: %s\nReserve Price: %s\n\nThank you!", 'bk-auction-manager'),
                $auction->post_title,
                bk_auction_format_price($current_bid),
                bk_auction_format_price($reserve_price)
            );

            wp_mail($owner_email, $subject, $message);
        } else {
            // No bids
            $owner_email = get_the_author_meta('user_email', $auction->post_author);

            $subject = sprintf(__('[%s] Auction ended - No bids: %s', 'bk-auction-manager'),
                get_bloginfo('name'),
                $auction->post_title
            );

            $message = sprintf(
                __("Hello,\n\nYour auction '%s' has ended with no bids.\n\nThank you!", 'bk-auction-manager'),
                $auction->post_title
            );

            wp_mail($owner_email, $subject, $message);
        }
    }
}
