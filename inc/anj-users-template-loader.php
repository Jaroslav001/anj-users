<?php
/**
 * inc/anj-users-template-loader.php (fixed)
 * Forces plugin templates for 'user_cpt' archive and single.
 */
if (!defined('ABSPATH')) { exit; }

// Resolve plugin base path
$anj_users_base = trailingslashit( defined('ANJ_USERS_PATH') ? ANJ_USERS_PATH : dirname(__DIR__) );

// Helper to resolve our template file if present
$anj_users_template = function(string $type, string $fallback) use ($anj_users_base) : string {
    $map = [
        'archive' => $anj_users_base . 'templates/archive-user_cpt.php',
        'single'  => $anj_users_base . 'templates/single-user_cpt.php',
    ];
    if (isset($map[$type]) && file_exists($map[$type])) {
        return $map[$type];
    }
    return $fallback;
};

// High-priority filters so we win over theme filters
add_filter('archive_template', function ($template) use ($anj_users_template) {
    if (is_post_type_archive('user_cpt')) {
        return $anj_users_template('archive', $template);
    }
    return $template;
}, 999);

add_filter('single_template', function ($template) use ($anj_users_template) {
    if (is_singular('user_cpt')) {
        return $anj_users_template('single', $template);
    }
    return $template;
}, 999);

// Final safety net
add_filter('template_include', function ($template) use ($anj_users_template) {
    if (is_post_type_archive('user_cpt')) {
        $t = $anj_users_template('archive', $template);
        if ($t !== $template) return $t;
    }
    if (is_singular('user_cpt')) {
        $t = $anj_users_template('single', $template);
        if ($t !== $template) return $t;
    }
    return $template;
}, 999);
