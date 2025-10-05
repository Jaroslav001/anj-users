<?php
/**
 * inc/anj-users-trace.php
 * Toggle with ?anj_trace=1 to see a footer panel ONLY on /users and single user pages.
 */
if (!defined('ABSPATH')) { exit; }

add_filter('template_include', function ($template) {
    $GLOBALS['anj_users__current_template'] = $template;
    return $template;
}, 999);

add_action('wp_footer', function () {
    if (!isset($_GET['anj_trace']) || '1' !== $_GET['anj_trace']) return;
    if (!( is_post_type_archive('user_cpt') || is_singular('user_cpt') )) return;

    $base = defined('ANJ_USERS_PATH') ? trailingslashit(ANJ_USERS_PATH) : trailingslashit(dirname(__DIR__));
    $archive = $base . 'templates/archive-user_cpt.php';
    $single  = $base . 'templates/single-user_cpt.php';
    $chosen  = isset($GLOBALS['anj_users__current_template']) ? (string) $GLOBALS['anj_users__current_template'] : '';

    echo '<div style="position:fixed;left:12px;right:12px;bottom:12px;z-index:999999;padding:10px 12px;background:#111;color:#fff;font:12px/1.4 monospace;border-radius:8px;opacity:0.95">';
    echo '<div><strong>ANJ Users Trace</strong></div>';
    echo '<div>Archive file expected: <code>' . esc_html($archive) . '</code> (exists: ' . (file_exists($archive) ? '1' : '0') . ')</div>';
    echo '<div>Single file expected: <code>' . esc_html($single) . '</code> (exists: ' . (file_exists($single) ? '1' : '0') . ')</div>';
    echo '<div>Chosen template: <code>' . esc_html($chosen) . '</code></div>';
    echo '<div>is_post_type_archive(user_cpt): <code>' . (int) is_post_type_archive('user_cpt') . '</code> | is_singular(user_cpt): <code>' . (int) is_singular('user_cpt') . '</code></div>';
    echo '<div>post_type query var: <code>' . esc_html( (string) get_query_var('post_type') ) . '</code> | is_404: <code>' . (int) is_404() . '</code></div>';
    echo '</div>';
}, 9999);
