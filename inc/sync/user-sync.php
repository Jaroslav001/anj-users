<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Meta key to link CPT -> WP user ID
 */
if (!defined('ANJ_USERS_META_USER_ID')) {
    define('ANJ_USERS_META_USER_ID', '_user_cpt_user_id');
}

/**
 * Get the CPT post ID for a given WP user ID.
 */
if (!function_exists('anj_users_get_post_id_for_user')) {
    function anj_users_get_post_id_for_user($user_id)
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) return 0;

        $q = get_posts([
            'post_type'   => 'user_cpt',
            'meta_key'    => ANJ_USERS_META_USER_ID,
            'meta_value'  => $user_id,
            'fields'      => 'ids',
            'numberposts' => 1,
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'no_found_rows' => true,
        ]);
        if (!empty($q)) {
            return (int) $q[0];
        }
        return 0;
    }
}

/**
 * Create or update a CPT post for a specific WP user.
 */
if (!function_exists('anj_users_sync_single')) {
    function anj_users_sync_single($user_id)
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) return 0;

        $u = get_userdata($user_id);
        if (!$u) return 0;

        $title = $u->display_name ?: $u->user_nicename;
        $slug  = sanitize_title($u->user_nicename);

        $post_id = anj_users_get_post_id_for_user($user_id);

        if ($post_id) {
            // Update existing
            $current = get_post($post_id);
            if (!$current) return 0;

            $update = ['ID' => $post_id];

            if ($current->post_title !== $title) {
                $update['post_title'] = $title;
            }
            if ((int)$current->post_author !== $user_id) {
                $update['post_author'] = $user_id;
            }
            if ($current->post_name !== $slug) {
                $update['post_name'] = wp_unique_post_slug($slug, $post_id, $current->post_status, 'user_cpt', $current->post_parent);
            }

            if (count($update) > 1) {
                wp_update_post($update);
            }

            update_post_meta($post_id, ANJ_USERS_META_USER_ID, $user_id);
            return $post_id;
        }

        // Create new
        $postarr = [
            'post_type'   => 'user_cpt',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_title'  => $title,
            'post_name'   => $slug,
        ];

        $postarr['post_name'] = wp_unique_post_slug($postarr['post_name'], 0, 'publish', 'user_cpt', 0);
        $post_id = wp_insert_post($postarr);
        if (!is_wp_error($post_id) && $post_id) {
            update_post_meta($post_id, ANJ_USERS_META_USER_ID, $user_id);
            return (int) $post_id;
        }
        return 0;
    }
}

/**
 * Backfill: create/repair CPT posts for all existing users.
 */
if (!function_exists('anj_users_sync_backfill_all')) {
    function anj_users_sync_backfill_all()
    {
        // Ensure CPT exists
        if (!post_type_exists('user_cpt') && function_exists('anj_users_register_user_cpt')) {
            anj_users_register_user_cpt();
        }

        $users = get_users(['fields' => ['ID']]);
        foreach ($users as $u) {
            anj_users_sync_single((int)$u->ID);
        }
    }
}

/**
 * Hooks
 */
add_action('user_register', function ($user_id) {
    // Ensure CPT is registered
    if (function_exists('anj_users_register_user_cpt')) {
        anj_users_register_user_cpt();
    }
    anj_users_sync_single($user_id);
}, 10, 1);

add_action('profile_update', function ($user_id) {
    anj_users_sync_single($user_id);
}, 10, 1);

add_action('delete_user', function ($user_id) {
    $post_id = anj_users_get_post_id_for_user($user_id);
    if ($post_id) {
        wp_trash_post($post_id);
    }
}, 10, 1);
