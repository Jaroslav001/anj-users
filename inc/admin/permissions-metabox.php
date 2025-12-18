<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permissions meta box for user_cpt edit screen.
 * Allows admins to add/remove per-user permission overrides.
 */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'anj_users_permissions',
        __('User Permissions', 'anj-users'),
        'anj_users_permissions_metabox_render',
        'user_cpt',
        'normal',
        'default'
    );
});

function anj_users_permissions_metabox_render(WP_Post $post)
{
    $user_id = (int) $post->post_author;
    if ($user_id <= 0) {
        echo '<p>' . esc_html__('No linked user found for this profile.', 'anj-users') . '</p>';
        return;
    }

    $explain = function_exists('anj_permissions_explain') ? anj_permissions_explain($user_id) : null;
    $known = function_exists('anj_permissions_known_keys') ? anj_permissions_known_keys() : [];
    $extra = $explain ? ($explain['extra'] ?? []) : [];
    $deny  = $explain ? ($explain['deny'] ?? []) : [];

    wp_nonce_field('anj_users_permissions_save', 'anj_users_permissions_nonce');

    echo '<p><strong>' . esc_html__('Linked WP User ID:', 'anj-users') . '</strong> ' . esc_html((string)$user_id) . '</p>';

    if ($explain) {
        $role = $explain['primary_role'] ?? '';
        echo '<p><strong>' . esc_html__('Primary role:', 'anj-users') . '</strong> ' . esc_html($role ?: '-') . '</p>';
    }

    echo '<hr />';

    echo '<h4 style="margin:12px 0 6px;">' . esc_html__('Overrides', 'anj-users') . '</h4>';
    echo '<p style="margin:0 0 10px;color:#666;">' . esc_html__('Baseline permissions come from role. Use these overrides to add or deny specific permissions for this user.', 'anj-users') . '</p>';

    // Build checkbox grid
    if (!$known) {
        echo '<p>' . esc_html__('No known permissions are configured yet.', 'anj-users') . '</p>';
        return;
    }

    echo '<table class="widefat striped" style="max-width:900px;">';
    echo '<thead><tr><th style="width:45%;">' . esc_html__('Permission', 'anj-users') . '</th><th style="width:20%;">' . esc_html__('Add', 'anj-users') . '</th><th style="width:20%;">' . esc_html__('Deny', 'anj-users') . '</th><th>' . esc_html__('Baseline', 'anj-users') . '</th></tr></thead>';
    echo '<tbody>';

    $baseline = $explain ? ($explain['baseline'] ?? []) : [];
    $baseline_lookup = array_fill_keys((array)$baseline, true);
    $extra_lookup = array_fill_keys((array)$extra, true);
    $deny_lookup = array_fill_keys((array)$deny, true);

    foreach ($known as $perm) {
        $is_base = isset($baseline_lookup[$perm]);
        $is_extra = isset($extra_lookup[$perm]);
        $is_deny = isset($deny_lookup[$perm]);

        echo '<tr>';
        echo '<td><code>' . esc_html($perm) . '</code></td>';

        echo '<td>';
        echo '<label><input type="checkbox" name="anj_perm_extra[]" value="' . esc_attr($perm) . '"' . checked($is_extra, true, false) . ' /> ' . esc_html__('Enabled', 'anj-users') . '</label>';
        echo '</td>';

        echo '<td>';
        echo '<label><input type="checkbox" name="anj_perm_deny[]" value="' . esc_attr($perm) . '"' . checked($is_deny, true, false) . ' /> ' . esc_html__('Denied', 'anj-users') . '</label>';
        echo '</td>';

        echo '<td>' . ($is_base ? '<span style="color:#2271b1;font-weight:600;">' . esc_html__('Yes', 'anj-users') . '</span>' : '-') . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    if ($explain) {
        $effective = $explain['effective'] ?? [];
        echo '<h4 style="margin:16px 0 6px;">' . esc_html__('Effective permissions', 'anj-users') . '</h4>';
        echo '<div style="background:#fff;border:1px solid #ccd0d4;padding:10px;max-width:900px;">';
        if (!$effective) {
            echo '<em>' . esc_html__('None', 'anj-users') . '</em>';
        } else {
            echo '<ul style="margin:0;padding-left:18px;">';
            foreach ($effective as $p) {
                echo '<li><code>' . esc_html($p) . '</code></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }
}

add_action('save_post_user_cpt', function ($post_id, WP_Post $post, $update) {
    if (!isset($_POST['anj_users_permissions_nonce']) || !wp_verify_nonce($_POST['anj_users_permissions_nonce'], 'anj_users_permissions_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_users')) return;

    $user_id = (int) $post->post_author;
    if ($user_id <= 0) return;

    $extra = isset($_POST['anj_perm_extra']) && is_array($_POST['anj_perm_extra']) ? array_map('sanitize_text_field', $_POST['anj_perm_extra']) : [];
    $deny  = isset($_POST['anj_perm_deny']) && is_array($_POST['anj_perm_deny']) ? array_map('sanitize_text_field', $_POST['anj_perm_deny']) : [];

    // Normalize
    if (function_exists('anj_permissions_normalize')) {
        $extra = anj_permissions_normalize($extra);
        $deny  = anj_permissions_normalize($deny);
    } else {
        $extra = array_values(array_unique(array_filter(array_map('trim', $extra))));
        $deny  = array_values(array_unique(array_filter(array_map('trim', $deny))));
    }

    update_user_meta($user_id, 'anj_permissions_extra', $extra);
    update_user_meta($user_id, 'anj_permissions_deny', $deny);
}, 10, 3);
