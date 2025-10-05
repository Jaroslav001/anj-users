<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the CPT: user_cpt
 * Public URL: /users/{slug}
 * REST: /wp-json/wp/v2/user-profiles (avoid conflict with core /wp/v2/users)
 */
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

            // REST support & safe base
            'show_in_rest'       => true,
            'rest_namespace'     => 'wp/v2',
            'rest_base'          => 'user-profiles', // DO NOT use "users" (conflicts with core)

            'has_archive'        => 'users',   // /users
            'hierarchical'       => false,
            'supports'           => ['title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'],
            'rewrite'            => [
                'slug'       => 'users',
                'with_front' => false,
                'feeds'      => false,
                'pages'      => true,
            ],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-admin-users',
        ]);
    }
}
add_action('init', 'anj_users_register_user_cpt', 0);
