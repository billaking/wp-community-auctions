/**
 * WP Community Auction Manager - Frontend JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Place Bid Handler
        $('#wcam-bid-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $message = $('.wcam-message');
            var $button = $form.find('button[type="submit"]');

            var auctionId = $form.find('input[name="auction_id"]').val();
            var bidAmount = $form.find('input[name="bid_amount"]').val();

            // Disable button
            $button.prop('disabled', true).text(wcamData.strings.processing || 'Processing...');
            $message.removeClass('success error').text('');

            $.ajax({
                url: wcamData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcam_place_bid',
                    nonce: wcamData.nonce,
                    auction_id: auctionId,
                    bid_amount: bidAmount
                },
                success: function(response) {
                    if (response.success) {
                        $message.addClass('success').text(response.data.message);

                        // Update current bid display
                        $('.wcam-current-bid .wcam-amount[data-auction-id="' + auctionId + '"]')
                            .text(response.data.current_bid);

                        // Update bid count
                        $('.wcam-bid-count .wcam-count').text(response.data.bid_count);

                        // Reset form
                        $form[0].reset();

                        // Reload page after 2 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $message.addClass('error').text(response.data.message);
                    }
                },
                error: function() {
                    $message.addClass('error').text(wcamData.strings.bidError);
                },
                complete: function() {
                    $button.prop('disabled', false)
                        .text(wcamData.strings.placeBid || 'Place Bid');
                }
            });
        });

        // Buy Now Handler
        $('#wcam-buy-now-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm(wcamData.strings.confirmBuyNow || 'Are you sure you want to buy this item now?')) {
                return;
            }

            var $button = $(this);
            var $message = $('.wcam-message');
            var auctionId = $button.data('auction-id');

            $button.prop('disabled', true).text(wcamData.strings.processing || 'Processing...');
            $message.removeClass('success error').text('');

            $.ajax({
                url: wcamData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcam_buy_now',
                    nonce: wcamData.nonce,
                    auction_id: auctionId
                },
                success: function(response) {
                    if (response.success) {
                        $message.addClass('success').text(response.data.message);

                        // Redirect to auction page
                        setTimeout(function() {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else {
                                location.reload();
                            }
                        }, 1500);
                    } else {
                        $message.addClass('error').text(response.data.message);
                        $button.prop('disabled', false)
                            .text(wcamData.strings.buyNow || 'Buy Now');
                    }
                },
                error: function() {
                    $message.addClass('error').text(wcamData.strings.bidError);
                    $button.prop('disabled', false)
                        .text(wcamData.strings.buyNow || 'Buy Now');
                }
            });
        });

        // Update current bid periodically
        function updateCurrentBid() {
            $('.wcam-current-bid .wcam-amount[data-auction-id]').each(function() {
                var $element = $(this);
                var auctionId = $element.data('auction-id');

                $.ajax({
                    url: wcamData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wcam_get_current_bid',
                        auction_id: auctionId
                    },
                    success: function(response) {
                        if (response.success) {
                            $element.text(response.data.current_bid);

                            // Update bid count if element exists
                            var $bidCount = $('.wcam-bid-count .wcam-count');
                            if ($bidCount.length) {
                                $bidCount.text(response.data.bid_count);
                            }
                        }
                    }
                });
            });
        }

        // Update time remaining
        function updateTimeRemaining() {
            $('.wcam-time[data-auction-id]').each(function() {
                var $element = $(this);
                var auctionId = $element.data('auction-id');

                $.ajax({
                    url: wcamData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'wcam_get_time_remaining',
                        auction_id: auctionId
                    },
                    success: function(response) {
                        if (response.success) {
                            $element.text(response.data.time_remaining);

                            if (response.data.ended) {
                                // Reload page if auction has ended
                                setTimeout(function() {
                                    location.reload();
                                }, 3000);
                            }
                        }
                    }
                });
            });
        }

        // Update every 10 seconds if on single auction page
        if ($('.wcam-single-auction').length || $('.wcam-auction-card').length) {
            setInterval(function() {
                updateCurrentBid();
                updateTimeRemaining();
            }, 10000);
        }

        // Category pill filter with smooth animations
        $('.wcam-category-pill').on('click', function(e) {
            e.preventDefault();

            var $pill = $(this);
            var category = $pill.data('category');
            var currentUrl = window.location.href.split('?')[0];

            // Update active state with animation
            $('.wcam-category-pill').removeClass('active');
            $pill.addClass('active');

            // Add loading state to grid
            $('.wcam-auctions-grid').css('opacity', '0.5');

            // Navigate to filtered URL
            setTimeout(function() {
                if (category) {
                    window.location.href = currentUrl + '?wcam_category=' + category;
                } else {
                    window.location.href = currentUrl;
                }
            }, 200);
        });

        // Set active pill based on URL parameter
        if (window.location.search) {
            var urlParams = new URLSearchParams(window.location.search);
            var activeCategory = urlParams.get('wcam_category');

            if (activeCategory) {
                $('.wcam-category-pill').removeClass('active');
                $('.wcam-category-pill[data-category="' + activeCategory + '"]').addClass('active');
            }
        }

        // Old category filter for backwards compatibility
        $('#wcam-category-filter').on('change', function() {
            var category = $(this).val();
            var currentUrl = window.location.href.split('?')[0];

            if (category) {
                window.location.href = currentUrl + '?wcam_category=' + category;
            } else {
                window.location.href = currentUrl;
            }
        });

        // Smooth scroll for dashboard navigation
        $('.wcam-dashboard-navigation a[href^=\"#\"]').on('click', function(e) {
            e.preventDefault();

            var target = $(this).attr('href');

            if ($(target).length) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 50
                }, 500);
            }
        });

        // Photo Gallery Thumbnail Switcher
        $('.wcam-gallery-thumb').on('click', function() {
            var $thumb = $(this);
            var fullImage = $thumb.data('full');
            var label = $thumb.data('label');

            // Update active state
            $('.wcam-gallery-thumb').removeClass('active');
            $thumb.addClass('active');

            // Fade out current image
            $('#wcam-main-photo').css('opacity', '0');

            // After fade, change image and fade in
            setTimeout(function() {
                $('#wcam-main-photo')
                    .attr('src', fullImage)
                    .css('opacity', '1');
                $('#wcam-current-label').text(label);
            }, 300);
        });

        // Keyboard navigation for gallery
        $(document).on('keydown', function(e) {
            if (!$('.wcam-photo-gallery').length) return;

            var $thumbs = $('.wcam-gallery-thumb');
            var $active = $('.wcam-gallery-thumb.active');
            var currentIndex = $thumbs.index($active);

            if (e.keyCode === 37) { // Left arrow
                e.preventDefault();
                var prevIndex = currentIndex > 0 ? currentIndex - 1 : $thumbs.length - 1;
                $thumbs.eq(prevIndex).click();
            } else if (e.keyCode === 39) { // Right arrow
                e.preventDefault();
                var nextIndex = currentIndex < $thumbs.length - 1 ? currentIndex + 1 : 0;
                $thumbs.eq(nextIndex).click();
            }
        });

    });

})(jQuery);
