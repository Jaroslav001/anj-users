<?php
/**
 * users-access-guard.php
 *
 * Protect the front-end `/users` route so ONLY Administrators can access it,
 * whether it's a normal Page OR a custom routed endpoint.
 *
 * Recommended location: your ANJ Users plugin, e.g.:
 *   wp-content/plugins/anj-users/inc/users-access-guard.php
 * and then in the plugin bootstrap:
 *   require_once __DIR__ . '/inc/users-access-guard.php';
 */

if (!defined('ABSPATH')) { exit; }

add_action('template_redirect', function () {

    // -------- CONFIG --------
    // Cap that grants access (default: Administrators only)
    $allow_cap = apply_filters('anj_users_guard_allow_cap', 'manage_options');

    // Targets to protect (either page IDs, slugs, or plain paths)
    $page_ids    = apply_filters('anj_users_guard_page_ids',    []);        // e.g., [123]
    $page_slugs  = apply_filters('anj_users_guard_page_slugs',  ['users']); // e.g., ['users']
    $route_paths = apply_filters('anj_users_guard_paths',       ['/users']); // e.g., ['/users']

    // Where to send guests (use null to fall back to wp-login.php)
    $login_url   = apply_filters('anj_users_guard_login_url', home_url('/login/'));

    // How to deny logged-in non-admins: 'redirect' or '404'
    $deny_mode   = apply_filters('anj_users_guard_deny_mode', 'redirect');
    $deny_redirect_url = apply_filters('anj_users_guard_deny_redirect', home_url('/'));

    // -------- FAST EXITS --------
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return; // Only guard front-end templates
    }

    // -------- TARGET MATCHING --------
    $is_target = false;

    // 1) If specific page IDs are provided
    if (!empty($page_ids) && is_page($page_ids)) {
        $is_target = true;
    }

    // 2) Page slugs (works for standard Pages)
    if (!$is_target && !empty($page_slugs) && is_page($page_slugs)) {
        $is_target = true;
    }

    // 3) Route path match (works for custom rewrites/endpoints)
    if (!$is_target && !empty($route_paths)) {
        // Get current request path, normalized and without the site subdirectory.
        $req_path = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $req_path = parse_url($req_path, PHP_URL_PATH) ?: '/';
        $site_base = parse_url(home_url('/'), PHP_URL_PATH) ?: '/';
        if ($site_base !== '/' && strpos($req_path, $site_base) === 0) {
            $req_path = substr($req_path, strlen($site_base));
            $req_path = '/' . ltrim($req_path, '/');
        }
        $req_path = untrailingslashit(strtolower($req_path));
        foreach ($route_paths as $p) {
            $p_norm = '/' . ltrim(strtolower($p), '/');
            $p_norm = untrailingslashit($p_norm);
            if ($req_path === $p_norm) {
                $is_target = true;
                break;
            }
        }
    }

    if (!$is_target) {
        return; // Not our page/route
    }

    // -------- ACCESS CONTROL --------
    if (current_user_can($allow_cap)) {
        return; // Admins (or allowed cap) can pass
    }

    // Not logged in: send to login, with redirect back
    if (!is_user_logged_in()) {
        $dest = $login_url ?: wp_login_url();
        // Compute absolute current URL for redirect_to
        $scheme = is_ssl() ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $current = $scheme . $host . $uri;
        wp_safe_redirect(add_query_arg('redirect_to', rawurlencode($current), $dest));
        exit;
    }

    // Logged-in but not allowed
    if ($deny_mode === '404') {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        return;
    }

    // Default: redirect away
    wp_safe_redirect($deny_redirect_url);
    exit;
}, 1);
