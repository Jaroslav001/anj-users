(function () {
	function uncheckAll(selectors) {
		selectors.forEach(function (selector) {
			var els = document.querySelectorAll(selector);
			if (!els || !els.length) return;
			els.forEach(function (el) {
				if (el && el.type === 'checkbox') {
					el.checked = false;
				}
			});
		});
	}

	function run() {
		uncheckAll([
			// "Send the new user an email about their account"
			'input#send_user_notification',
			'input[name="send_user_notification"]',

			// "Show Toolbar when viewing site"
			'input#admin_bar_front',
			'input[name="admin_bar_front"]',
			'input#show_admin_bar_front',
			'input[name="show_admin_bar_front"]',
		]);
	}

	function runLater() {
		// WP sometimes re-checks defaults via other admin scripts; run a few times.
		setTimeout(run, 50);
		setTimeout(run, 250);
		setTimeout(run, 1000);
	}

	function attachRoleListener() {
		var role = document.querySelector('#role');
		if (!role) return;
		role.addEventListener('change', function () {
			run();
			runLater();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			run();
			runLater();
			attachRoleListener();
		});
	} else {
		run();
		runLater();
		attachRoleListener();
	}

	window.addEventListener('load', function () {
		run();
		runLater();
	});
})();
