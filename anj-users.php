<?php

/**
 * Plugin Name: ANJ Users (User ⇄ CPT Sync)
 * Description: Registers a 'user_cpt' post type under /users/{slug} and keeps it in sync with WordPress users. Backfills existing users on activation.
 * Version: 1.0.2
 * Author: You
 * License: GPL-2.0+
 */
if (!defined('ABSPATH')) {
    exit;
}

// Constants
if (!defined('ANJ_USERS_VERSION'))  define('ANJ_USERS_VERSION', '1.0.2');
if (!defined('ANJ_USERS_PATH'))     define('ANJ_USERS_PATH', plugin_dir_path(__FILE__));
if (!defined('ANJ_USERS_URL'))      define('ANJ_USERS_URL', plugin_dir_url(__FILE__));

// Modules
require_once ANJ_USERS_PATH . 'inc/cpt/user-cpt.php';
require_once ANJ_USERS_PATH . 'inc/sync/user-sync.php';

// Template loader (plugin-scoped)
require_once ANJ_USERS_PATH . 'inc/anj-users-template-loader.php';
// Temporary trace panel for debugging (toggle with ?anj_trace=1)
require_once ANJ_USERS_PATH . 'inc/anj-users-trace.php';

// Activation: register CPT, backfill, flush
register_activation_hook(__FILE__, function () {
    if (function_exists('anj_users_register_user_cpt')) {
        anj_users_register_user_cpt();
    }
    if (function_exists('anj_users_sync_backfill_all')) {
        anj_users_sync_backfill_all();
    }
    flush_rewrite_rules();
});

// Deactivation: flush only
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
