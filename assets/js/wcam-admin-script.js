/**
 * WP Community Auction Manager - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Initialize datepicker for auction dates
        if ($('.wcam-datepicker').length) {
            $('.wcam-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                showTime: true
            });
        }

        // Datetime local fallback
        if ($('#wcam_start_date, #wcam_end_date').length) {
            // Convert datetime-local format if needed
            $('#wcam_start_date, #wcam_end_date').attr('type', 'datetime-local');
        }

        // Confirm before cancelling bid
        $('.wcam-cancel-bid').on('click', function(e) {
            if (!confirm('Are you sure you want to cancel this bid?')) {
                e.preventDefault();
            }
        });

        // Photo Gallery Uploader
        var mediaUploader;
        var currentPhotoLabel;

        $('.wcam-upload-photo').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $item = $button.closest('.wcam-gallery-item');
            currentPhotoLabel = $item.data('label');

            // Create media uploader
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'Select or Upload Photo - ' + $item.find('.wcam-gallery-label strong').text(),
                button: {
                    text: 'Use this photo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();

                // Find the correct item
                var $targetItem = $('.wcam-gallery-item[data-label="' + currentPhotoLabel + '"]');

                // Update preview
                $targetItem.find('.wcam-gallery-preview')
                    .css('background-image', 'url(' + attachment.url + ')')
                    .find('.placeholder-icon, .placeholder-text').remove();

                // Update hidden input
                $targetItem.find('.wcam-photo-id').val(attachment.id);

                // Update button text
                $targetItem.find('.wcam-upload-photo').text('Change');

                // Add remove button if not exists
                if (!$targetItem.find('.wcam-remove-photo').length) {
                    $targetItem.find('.wcam-gallery-buttons').append(
                        '<button type=\"button\" class=\"button wcam-remove-photo\">Remove</button>'
                    );
                }
            });

            mediaUploader.open();
        });

        // Remove photo
        $(document).on('click', '.wcam-remove-photo', function(e) {
            e.preventDefault();

            if (!confirm('Remove this photo?')) {
                return;
            }

            var $item = $(this).closest('.wcam-gallery-item');

            // Clear preview
            $item.find('.wcam-gallery-preview')
                .css('background-image', '')
                .html('<span class=\"placeholder-icon\">📸</span><span class=\"placeholder-text\">Click to upload</span>');

            // Clear hidden input
            $item.find('.wcam-photo-id').val('');

            // Update button
            $item.find('.wcam-upload-photo').text('Upload');
            $(this).remove();
        });

    });

})(jQuery);
