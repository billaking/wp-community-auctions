/**
 * WP Community Auction Manager - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Initialize datepicker for auction dates
        if ($('.bkAuction-datepicker').length) {
            $('.bkAuction-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                showTime: true
            });
        }

        // Datetime local fallback
        if ($('#bkAuction_start_date, #bkAuction_end_date').length) {
            // Convert datetime-local format if needed
            $('#bkAuction_start_date, #bkAuction_end_date').attr('type', 'datetime-local');
        }

        // Confirm before cancelling bid
        $('.bkAuction-cancel-bid').on('click', function(e) {
            if (!confirm('Are you sure you want to cancel this bid?')) {
                e.preventDefault();
            }
        });

        // Photo Gallery Uploader
        var mediaUploader;
        var currentPhotoLabel;

        $('.bkAuction-upload-photo').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $item = $button.closest('.bkAuction-gallery-item');
            currentPhotoLabel = $item.data('label');

            // Create media uploader
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'Select or Upload Photo - ' + $item.find('.bkAuction-gallery-label strong').text(),
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
                var $targetItem = $('.bkAuction-gallery-item[data-label="' + currentPhotoLabel + '"]');

                // Update preview
                $targetItem.find('.bkAuction-gallery-preview')
                    .css('background-image', 'url(' + attachment.url + ')')
                    .find('.placeholder-icon, .placeholder-text').remove();

                // Update hidden input
                $targetItem.find('.bkAuction-photo-id').val(attachment.id);

                // Update button text
                $targetItem.find('.bkAuction-upload-photo').text('Change');

                // Add remove button if not exists
                if (!$targetItem.find('.bkAuction-remove-photo').length) {
                    $targetItem.find('.bkAuction-gallery-buttons').append(
                        '<button type=\"button\" class=\"button bkAuction-remove-photo\">Remove</button>'
                    );
                }
            });

            mediaUploader.open();
        });

        // Remove photo
        $(document).on('click', '.bkAuction-remove-photo', function(e) {
            e.preventDefault();

            if (!confirm('Remove this photo?')) {
                return;
            }

            var $item = $(this).closest('.bkAuction-gallery-item');

            // Clear preview
            $item.find('.bkAuction-gallery-preview')
                .css('background-image', '')
                .html('<span class=\"placeholder-icon\">📸</span><span class=\"placeholder-text\">Click to upload</span>');

            // Clear hidden input
            $item.find('.bkAuction-photo-id').val('');

            // Update button
            $item.find('.bkAuction-upload-photo').text('Upload');
            $(this).remove();
        });

        // ===================================
        // CATEGORY MANAGEMENT (Church Office Style)
        // ===================================

        if ($('#bkAuction-categories-table').length) {
            initCategoryManagement();
        }

        function initCategoryManagement() {
            // Load categories on page load
            var initialType = $('#bkAuction-category-type-selector').val();
            loadCategories(initialType);

            // Change category type
            $('#bkAuction-category-type-selector').on('change', function() {
                loadCategories($(this).val());
            });

            // Show add category form
            $('#bkAuction-add-category-btn').on('click', function() {
                $('#bkAuction-category-form-title').text('Add Category');
                $('#category-id').val('0');
                $('#category-type').val($('#bkAuction-category-type-selector').val());
                $('#bkAuction-category-form-element')[0].reset();
                $('#bkAuction-category-form').slideDown();
            });

            // Cancel category form
            $('#bkAuction-cancel-category').on('click', function() {
                $('#bkAuction-category-form').slideUp();
                $('#bkAuction-category-form-element')[0].reset();
            });

            // Submit category form
            $('#bkAuction-category-form-element').on('submit', function(e) {
                e.preventDefault();
                saveCategory();
            });

            // Edit category
            $(document).on('click', '.bkAuction-edit-category', function(e) {
                e.preventDefault();
                var categoryId = $(this).data('id');
                editCategory(categoryId);
            });

            // Delete category
            $(document).on('click', '.bkAuction-delete-category', function(e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to delete this category?')) {
                    return;
                }

                var categoryId = $(this).data('id');
                deleteCategory(categoryId);
            });

            // Auto-generate slug from name
            $('#category-name').on('blur', function() {
                if ($('#category-slug').val() === '') {
                    var slug = $(this).val().toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    $('#category-slug').val(slug);
                }
            });
        }

        function loadCategories(categoryType) {
            $.ajax({
                url: bkAuctionAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'bkAuction_get_categories',
                    nonce: bkAuctionAdmin.nonce,
                    category_type: categoryType
                },
                success: function(response) {
                    if (response.success) {
                        displayCategories(response.data.categories);
                    }
                }
            });
        }

        function displayCategories(categories) {
            var html = '';

            if (categories.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#666;">No categories found. Add your first category above.</td></tr>';
            } else {
                categories.forEach(function(cat) {
                    html += '<tr>';
                    html += '<td><strong>' + cat.name + '</strong></td>';
                    html += '<td><code>' + cat.slug + '</code></td>';
                    html += '<td>' + (cat.description || '-') + '</td>';
                    html += '<td>' + cat.sort_order + '</td>';
                    html += '<td>';
                    html += '<a href="#" class="bkAuction-edit-category" data-id="' + cat.id + '" style="color:#2271b1;">Edit</a> | ';
                    html += '<a href="#" class="bkAuction-delete-category bkAuction-delete-btn" data-id="' + cat.id + '">Delete</a>';
                    html += '</td>';
                    html += '</tr>';
                });
            }

            $('#bkAuction-categories-list').html(html);
        }

        function saveCategory() {
            $.ajax({
                url: bkAuctionAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'bkAuction_save_category',
                    nonce: bkAuctionAdmin.nonce,
                    category_id: $('#category-id').val(),
                    category_type: $('#category-type').val(),
                    slug: $('#category-slug').val(),
                    name: $('#category-name').val(),
                    description: $('#category-description').val(),
                    sort_order: $('#category-sort-order').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $('#bkAuction-category-form').slideUp();
                        $('#bkAuction-category-form-element')[0].reset();
                        loadCategories($('#bkAuction-category-type-selector').val());
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }

        function editCategory(categoryId) {
            // Show the form and set ID
            $('#bkAuction-category-form-title').text('Edit Category');
            $('#category-id').val(categoryId);
            $('#category-type').val($('#bkAuction-category-type-selector').val());
            $('#bkAuction-category-form').slideDown();
        }

        function deleteCategory(categoryId) {
            $.ajax({
                url: bkAuctionAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'bkAuction_delete_category',
                    nonce: bkAuctionAdmin.nonce,
                    category_id: categoryId
                },
                success: function(response) {
                    if (response.success) {
                        loadCategories($('#bkAuction-category-type-selector').val());
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }

    });

})(jQuery);
