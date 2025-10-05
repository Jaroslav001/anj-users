<?php

/**
 * Plugin Name: ANJ Users (User ⇄ CPT Sync)
 * Description: Registers a 'user_cpt' post type under /users/{slug} and keeps it in sync with WordPress users. Backfills existing users on activation.
 * Version: 1.0.0
 * Author: You
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ANJ_USERS_VERSION', '1.0.0');
define('ANJ_USERS_PATH', plugin_dir_path(__FILE__));
define('ANJ_USERS_URL', plugin_dir_url(__FILE__));

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
