<?php

/**
 * New user defaults (wp-admin > Users > Add New).
 *
 * Goal: ensure the front-end WP toolbar (admin bar) is disabled by default
 * for newly created users.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Force the "Show Toolbar when viewing site" preference OFF for newly created users.
 *
 * Why this hook:
 * - `user_register` can run before wp-admin finishes processing all user options.
 * - `edit_user_created_user` fires after the admin "Add New User" flow completes.
 *
 * @param int|WP_Error $user_id
 * @param string       $notify
 */
function anj_users_force_toolbar_off_for_new_user($user_id, $notify = '')
{
	if (is_wp_error($user_id)) {
		return;
	}

	$user_id = (int) $user_id;
	if ($user_id <= 0) {
		return;
	}

	// Always disable the front-end admin bar for newly created users.
	update_user_meta($user_id, 'show_admin_bar_front', 'false');
}

// Admin-created users (Users → Add New).
add_action('edit_user_created_user', 'anj_users_force_toolbar_off_for_new_user', 20, 2);

/**
 * Admin UI: force the checkbox on Users → Add New to be unchecked.
 *
 * WordPress uses the *current user's* `show_admin_bar_front` as the default
 * when rendering the Add New User form. This filter overrides that value
 * just for that screen.
 */
add_filter('get_user_option_show_admin_bar_front', function ($value, $option, $user) {
	if (!is_admin()) {
		return $value;
	}

	global $pagenow;
	if ($pagenow === 'user-new.php') {
		return 'false';
	}

	return $value;
}, 20, 3);

/**
 * Admin UI: default the "Send User Notification" checkbox to unchecked.
 *
 * WordPress renders this checkbox checked by default on user-new.php (id: send_user_notification).
 * We flip it off on the Add New User screen so admins must opt-in to sending the email.
 */
function anj_users_uncheck_send_user_notification_checkbox_js()
{
	if (!is_admin()) {
		return;
	}

?>
	<script>
		(function() {
			function uncheck() {
				var el = document.getElementById('send_user_notification');
				if (!el) return;
				el.checked = false;
				el.defaultChecked = false;
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', uncheck);
			} else {
				uncheck();
			}
			window.addEventListener('load', uncheck);
			setTimeout(uncheck, 50);
			setTimeout(uncheck, 250);
			setTimeout(uncheck, 1000);
		})();
	</script>
<?php
}

add_action('admin_print_footer_scripts-user-new.php', 'anj_users_uncheck_send_user_notification_checkbox_js');
add_action('network_admin_print_footer_scripts-user-new.php', 'anj_users_uncheck_send_user_notification_checkbox_js');


// Multisite: new user created in Network Admin.
add_action('wpmu_new_user', function ($user_id) {
	$user_id = (int) $user_id;
	if ($user_id <= 0) {
		return;
	}

	update_user_meta($user_id, 'show_admin_bar_front', 'false');
}, 20);

// Other creation flows (custom registrations, imports, programmatic creation).
add_action('user_register', function ($user_id) {
	$user_id = (int) $user_id;
	if ($user_id <= 0) {
		return;
	}

	// Only set if missing; admin flow is handled by `edit_user_created_user` above.
	$existing = get_user_meta($user_id, 'show_admin_bar_front', true);
	if ($existing === '' || $existing === null) {
		update_user_meta($user_id, 'show_admin_bar_front', 'false');
	}
}, 20);
