<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * New user defaults
 *
 * Goals:
 * 1) In wp-admin → Users → Add New (user-new.php), default BOTH checkboxes to unchecked:
 *    - "Send the new user an email about their account"
 *    - "Show Toolbar when viewing site"
 * 2) Ensure the toolbar preference is OFF by default at the data level,
 *    while still allowing it to be enabled later per user.
 */

/**
 * Admin UI: uncheck defaults on Users → Add New.
 */
$anj_users_enqueue_new_user_defaults = function ($hook) {
	// "user-new.php" is the usual hook suffix, but use screen id as a fallback.
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	$screen_id = $screen && isset($screen->id) ? $screen->id : '';

	if ($hook !== 'user-new.php' && $screen_id !== 'user-new') {
		return;
	}

	wp_enqueue_script(
		'anj-users-new-user-defaults',
		ANJ_USERS_URL . 'assets/admin/anj-users-new-user-defaults.js',
		[],
		ANJ_USERS_VERSION,
		true
	);
};

add_action('admin_enqueue_scripts', $anj_users_enqueue_new_user_defaults);
add_action('network_admin_enqueue_scripts', $anj_users_enqueue_new_user_defaults);

/**
 * Data level: default toolbar OFF for new users.
 *
 * Important: we only set the meta when it's missing AND it wasn't explicitly
 * set during creation (e.g., admin checked the box on user-new.php).
 */
add_action('user_register', function ($user_id) {
	// If the toolbar preference was explicitly submitted, respect it.
	if (is_admin() && !empty($_POST)) {
		if (isset($_POST['admin_bar_front']) || isset($_POST['show_admin_bar_front'])) {
			return;
		}
	}

	$current = get_user_meta($user_id, 'show_admin_bar_front', true);
	if ($current === '') {
		update_user_meta($user_id, 'show_admin_bar_front', 'false');
	}
}, 20);
