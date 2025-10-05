<?php

/**
 * Plugin Name: ANJ Users (User ⇄ CPT Sync)
 * Description: Registers a 'user_cpt' post type under /users/{slug} and keeps it in sync with WordPress users. Backfills existing users on activation.
 * Version: 1.0.1
 * Author: You
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit;
}

// --- Constants (kept from your original) --- //
if (!defined('ANJ_USERS_VERSION'))  define('ANJ_USERS_VERSION', '1.0.1');
if (!defined('ANJ_USERS_PATH'))     define('ANJ_USERS_PATH', plugin_dir_path(__FILE__));
if (!defined('ANJ_USERS_URL'))      define('ANJ_USERS_URL', plugin_dir_url(__FILE__));

// --- Modules --- //
require_once ANJ_USERS_PATH . 'inc/cpt/user-cpt.php';
require_once ANJ_USERS_PATH . 'inc/sync/user-sync.php';

/**
 * Activation: ensure CPT is registered, backfill posts for existing users, and flush rewrites.
 */
register_activation_hook(__FILE__, function () {
    // Make sure post type is registered before we create posts
    if (function_exists('anj_users_register_user_cpt')) {
        anj_users_register_user_cpt();
    }

    if (function_exists('anj_users_sync_backfill_all')) {
        anj_users_sync_backfill_all();
    }

    flush_rewrite_rules();
});

/**
 * Deactivation: flush rewrites (we keep data).
 */
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

// -------------------------------------------------------------------------
// Plugin-scoped templates + archive defaults + minimal CSS (no theme edits)
// -------------------------------------------------------------------------

/**
 * Use plugin-provided templates for user_cpt archive/single
 * Falls back to theme templates if they exist there.
 */
add_filter('single_template', function ($template) {
    if (is_singular('user_cpt')) {
        $plugin_tpl = ANJ_USERS_PATH . 'templates/single-user_cpt.php';
        if (file_exists($plugin_tpl)) return $plugin_tpl;
    }
    return $template;
});

add_filter('archive_template', function ($template) {
    if (is_post_type_archive('user_cpt')) {
        $plugin_tpl = ANJ_USERS_PATH . 'templates/archive-user_cpt.php';
        if (file_exists($plugin_tpl)) return $plugin_tpl;
    }
    return $template;
});

/**
 * Default ordering + page size for /users archive
 */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if ($q->is_post_type_archive('user_cpt')) {
        $q->set('orderby', 'title');
        $q->set('order', 'ASC');
        $q->set('posts_per_page', 24);
    }
});

/**
 * Enqueue minimal styles only on our archive/single
 */
add_action('wp_enqueue_scripts', function () {
    if (is_post_type_archive('user_cpt') || is_singular('user_cpt')) {
        // Safe to enqueue even if file is missing
        wp_enqueue_style('anj-users-styles', ANJ_USERS_URL . 'assets/anj-users.css', [], ANJ_USERS_VERSION);
    }
});
