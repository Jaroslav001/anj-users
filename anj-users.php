<?php

/**
 * Plugin Name: ANJ Users (User ⇄ CPT Sync)
 * Description: Registers a 'user_cpt' post type under /users/{slug} and keeps it in sync with WordPress users. Backfills existing users on activation.
 * Version: 1.0.4
 * Author: You
 * License: GPL-2.0+
 */
if (!defined('ABSPATH')) {
	exit;
}

// Constants
if (!defined('ANJ_USERS_VERSION'))  define('ANJ_USERS_VERSION', '1.0.4');
if (!defined('ANJ_USERS_PATH'))     define('ANJ_USERS_PATH', plugin_dir_path(__FILE__));
if (!defined('ANJ_USERS_URL'))      define('ANJ_USERS_URL', plugin_dir_url(__FILE__));

// Modules
require_once ANJ_USERS_PATH . 'inc/cpt/user-cpt.php';
require_once ANJ_USERS_PATH . 'inc/sync/user-sync.php';
require_once ANJ_USERS_PATH . 'inc/permissions/permissions.php';
require_once ANJ_USERS_PATH . 'inc/admin/permissions-metabox.php';
require_once ANJ_USERS_PATH . 'inc/anj-users-template-loader.php';
require_once ANJ_USERS_PATH . 'inc/users-access-guard.php';
if (file_exists(ANJ_USERS_PATH . 'inc/anj-users-trace.php')) {
	require_once ANJ_USERS_PATH . 'inc/anj-users-trace.php';
}

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

/**
 * Styles: one common stylesheet + per-page styles
 * - assets/anj-users-common.css  -> loaded on BOTH archive and single
 * - assets/anj-users-archive.css -> loaded ONLY on /users archive
 * - assets/anj-users-single.css  -> loaded ONLY on single user pages
 */
add_action('wp_enqueue_scripts', function () {
	if (is_post_type_archive('user_cpt') || is_singular('user_cpt')) {
		wp_enqueue_style('anj-users-common', ANJ_USERS_URL . 'assets/anj-users-common.css', [], ANJ_USERS_VERSION);
	}
	if (is_post_type_archive('user_cpt')) {
		wp_enqueue_style('anj-users-archive', ANJ_USERS_URL . 'assets/anj-users-archive.css', ['anj-users-common'], ANJ_USERS_VERSION);
	}
	if (is_singular('user_cpt')) {
		wp_enqueue_style('anj-users-single', ANJ_USERS_URL . 'assets/anj-users-single.css', ['anj-users-common'], ANJ_USERS_VERSION);
	}
});
