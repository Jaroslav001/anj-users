(function () {
	function uncheck(selector) {
		var el = document.querySelector(selector);
		if (el && el.type === 'checkbox') {
			el.checked = false;
		}
	}

	function run() {
		// "Send the new user an email about their account"
		uncheck('input#send_user_notification');
		uncheck('input[name="send_user_notification"]');

		// "Show Toolbar when viewing site"
		uncheck('input#admin_bar_front');
		uncheck('input[name="admin_bar_front"]');
		uncheck('input#show_admin_bar_front');
		uncheck('input[name="show_admin_bar_front"]');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
