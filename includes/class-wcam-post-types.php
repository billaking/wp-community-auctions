<?php
/**
 * Register Custom Post Types and Taxonomies
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCAM_Post_Types {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_filter('post_updated_messages', array($this, 'updated_messages'));
    }

    /**
     * Register auction post type
     */
    public static function register_post_types() {
        $labels = array(
            'name'               => __('Auctions', 'wp-community-auction-manager'),
            'singular_name'      => __('Auction', 'wp-community-auction-manager'),
            'menu_name'          => __('Auctions', 'wp-community-auction-manager'),
            'add_new'            => __('Add New', 'wp-community-auction-manager'),
            'add_new_item'       => __('Add New Auction', 'wp-community-auction-manager'),
            'edit_item'          => __('Edit Auction', 'wp-community-auction-manager'),
            'new_item'           => __('New Auction', 'wp-community-auction-manager'),
            'view_item'          => __('View Auction', 'wp-community-auction-manager'),
            'search_items'       => __('Search Auctions', 'wp-community-auction-manager'),
            'not_found'          => __('No auctions found', 'wp-community-auction-manager'),
            'not_found_in_trash' => __('No auctions found in Trash', 'wp-community-auction-manager'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'auction'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-hammer',
            'supports'            => array('title', 'editor', 'thumbnail', 'author', 'comments'),
            'show_in_rest'        => true,
        );

        register_post_type('wcam_auction', $args);
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Auction Categories
        $labels = array(
            'name'              => __('Auction Categories', 'wp-community-auction-manager'),
            'singular_name'     => __('Auction Category', 'wp-community-auction-manager'),
            'search_items'      => __('Search Categories', 'wp-community-auction-manager'),
            'all_items'         => __('All Categories', 'wp-community-auction-manager'),
            'parent_item'       => __('Parent Category', 'wp-community-auction-manager'),
            'parent_item_colon' => __('Parent Category:', 'wp-community-auction-manager'),
            'edit_item'         => __('Edit Category', 'wp-community-auction-manager'),
            'update_item'       => __('Update Category', 'wp-community-auction-manager'),
            'add_new_item'      => __('Add New Category', 'wp-community-auction-manager'),
            'new_item_name'     => __('New Category Name', 'wp-community-auction-manager'),
            'menu_name'         => __('Categories', 'wp-community-auction-manager'),
        );

        register_taxonomy('wcam_category', array('wcam_auction'), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'auction-category'),
            'show_in_rest'      => true,
        ));

        // Auction Tags
        $tag_labels = array(
            'name'          => __('Auction Tags', 'wp-community-auction-manager'),
            'singular_name' => __('Auction Tag', 'wp-community-auction-manager'),
            'search_items'  => __('Search Tags', 'wp-community-auction-manager'),
            'all_items'     => __('All Tags', 'wp-community-auction-manager'),
            'edit_item'     => __('Edit Tag', 'wp-community-auction-manager'),
            'update_item'   => __('Update Tag', 'wp-community-auction-manager'),
            'add_new_item'  => __('Add New Tag', 'wp-community-auction-manager'),
            'new_item_name' => __('New Tag Name', 'wp-community-auction-manager'),
            'menu_name'     => __('Tags', 'wp-community-auction-manager'),
        );

        register_taxonomy('wcam_tag', array('wcam_auction'), array(
            'hierarchical'      => false,
            'labels'            => $tag_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'auction-tag'),
            'show_in_rest'      => true,
        ));
    }

    /**
     * Custom update messages
     */
    public function updated_messages($messages) {
        global $post;

        $messages['wcam_auction'] = array(
            0  => '',
            1  => __('Auction updated.', 'wp-community-auction-manager'),
            2  => __('Custom field updated.', 'wp-community-auction-manager'),
            3  => __('Custom field deleted.', 'wp-community-auction-manager'),
            4  => __('Auction updated.', 'wp-community-auction-manager'),
            5  => isset($_GET['revision']) ? sprintf(__('Auction restored to revision from %s', 'wp-community-auction-manager'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6  => __('Auction published.', 'wp-community-auction-manager'),
            7  => __('Auction saved.', 'wp-community-auction-manager'),
            8  => __('Auction submitted.', 'wp-community-auction-manager'),
            9  => sprintf(__('Auction scheduled for: <strong>%1$s</strong>.', 'wp-community-auction-manager'), date_i18n(__('M j, Y @ G:i', 'wp-community-auction-manager'), strtotime($post->post_date))),
            10 => __('Auction draft updated.', 'wp-community-auction-manager'),
        );

        return $messages;
    }
}
