<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('anj_users_register_user_cpt')) {
    function anj_users_register_user_cpt()
    {
        $labels = [
            'name'               => __('Users', 'anj-users'),
            'singular_name'      => __('User', 'anj-users'),
            'menu_name'          => __('Users (CPT)', 'anj-users'),
            'name_admin_bar'     => __('User', 'anj-users'),
            'add_new'            => __('Add New', 'anj-users'),
            'add_new_item'       => __('Add New User', 'anj-users'),
            'new_item'           => __('New User', 'anj-users'),
            'edit_item'          => __('Edit User', 'anj-users'),
            'view_item'          => __('View User', 'anj-users'),
            'all_items'          => __('All Users', 'anj-users'),
            'search_items'       => __('Search Users', 'anj-users'),
            'not_found'          => __('No users found.', 'anj-users'),
            'not_found_in_trash' => __('No users found in Trash.', 'anj-users'),
        ];

        register_post_type('user_cpt', [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,           // enable Gutenberg/blocks/API
            'rest_base'          => 'users',        // optional, nicer REST URL
            'has_archive'        => 'users',        // /users
            'hierarchical'       => false,
            'supports'           => ['title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'],
            'rewrite'            => [
                'slug'       => 'users',
                'with_front' => false,
                'feeds'      => false,
                'pages'      => true,
            ],
            'query_var'          => true,           // helpful for debugging (?user_cpt=foo)
            'exclude_from_search' => false,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-admin-users',
            'show_in_nav_menus'  => true,
        ]);
    }
}
add_action('init', 'anj_users_register_user_cpt', 0);
