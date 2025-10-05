<?php
/**
 * ANJ Users — Template Loader (drop-in)
 *
 * Purpose: Force WordPress to use the plugin's templates for the 'user_cpt' archive and single views.
 * Place this file in your plugin and include it from your main plugin file OR drop it into mu-plugins.
 */
if (!defined('ABSPATH')) { exit; }

// Resolve plugin directory even if constants differ
if (defined('ANJ_USERS_PATH')) {
    $anj_users_base = rtrim(ANJ_USERS_PATH, '/\') . '/';
} elseif (defined('ANJ_USERS_PLUGIN_DIR')) {
    $anj_users_base = rtrim(ANJ_USERS_PLUGIN_DIR, '/\') . '/';
} else {
    // fallback: current file's directory
    $anj_users_base = trailingslashit( dirname(__FILE__) );
}

add_filter('single_template', function ($template) use ($anj_users_base) {
    if (is_singular('user_cpt')) {
        $plugin_tpl = $anj_users_base . 'templates/single-user_cpt.php';
        if (file_exists($plugin_tpl)) {
            return $plugin_tpl;
        }
    }
    return $template;
}, 20);

add_filter('archive_template', function ($template) use ($anj_users_base) {
    if (is_post_type_archive('user_cpt')) {
        $plugin_tpl = $anj_users_base . 'templates/archive-user_cpt.php';
        if (file_exists($plugin_tpl)) {
            return $plugin_tpl;
        }
    }
    return $template;
}, 20);
