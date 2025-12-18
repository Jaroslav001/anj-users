<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permission system (Mode 2): role baseline + per-user overrides.
 *
 * Storage (user_meta):
 * - anj_permissions_extra : array<string>
 * - anj_permissions_deny  : array<string>
 */

/**
 * Baseline permissions per role.
 * Adjust here as your vocabulary grows.
 */
function anj_permissions_role_map(): array
{
    return [
        'administrator' => [
            'drive.modify.any',
        ],
        'admin' => [ // optional alias
            'drive.modify.any',
        ],
        'manager' => [
            'drive.school.modify.all',
            'drive.page.modify.own',
            'drive.user.modify.self',
        ],
        'lector' => [
            'drive.user.modify.self',
        ],
    ];
}

/**
 * Role precedence: higher wins for baseline.
 */
function anj_permissions_role_precedence(): array
{
    return [
        'administrator',
        'admin',
        'manager',
        'lector',
    ];
}

/**
 * Determine the primary role for baseline permission mapping.
 * If multiple roles exist, pick the highest precedence.
 */
function anj_permissions_primary_role(?WP_User $user): string
{
    if (!$user instanceof WP_User) {
        return '';
    }
    $roles = is_array($user->roles) ? $user->roles : [];
    if (!$roles) return '';
    $precedence = anj_permissions_role_precedence();
    foreach ($precedence as $r) {
        if (in_array($r, $roles, true)) return $r;
    }
    return (string)$roles[0];
}

/**
 * Baseline permissions derived from role.
 */
function anj_permissions_get_baseline(int $user_id): array
{
    $user = get_user_by('id', $user_id);
    if (!$user) return [];
    $role = anj_permissions_primary_role($user);
    $map = anj_permissions_role_map();
    $baseline = $map[$role] ?? [];
    return anj_permissions_normalize($baseline);
}

/**
 * Get per-user overrides.
 */
function anj_permissions_get_extra(int $user_id): array
{
    $v = get_user_meta($user_id, 'anj_permissions_extra', true);
    return anj_permissions_normalize(is_array($v) ? $v : []);
}

function anj_permissions_get_deny(int $user_id): array
{
    $v = get_user_meta($user_id, 'anj_permissions_deny', true);
    return anj_permissions_normalize(is_array($v) ? $v : []);
}

/**
 * Compute effective permissions.
 */
function anj_permissions_get_effective(int $user_id): array
{
    $baseline = anj_permissions_get_baseline($user_id);
    $extra = anj_permissions_get_extra($user_id);
    $deny = anj_permissions_get_deny($user_id);

    $effective = array_unique(array_merge($baseline, $extra));
    if ($deny) {
        $deny_lookup = array_fill_keys($deny, true);
        $effective = array_values(array_filter($effective, fn($p) => !isset($deny_lookup[$p])));
    }
    sort($effective);
    return $effective;
}

/**
 * Simple membership check.
 */
function anj_user_has_permission(int $user_id, string $perm): bool
{
    $perm = trim($perm);
    if ($perm === '') return false;

    // Admin override
    $effective = anj_permissions_get_effective($user_id);
    if (in_array('drive.modify.any', $effective, true)) {
        return true;
    }
    return in_array($perm, $effective, true);
}

/**
 * Explain permissions for debugging.
 */
function anj_permissions_explain(int $user_id): array
{
    $user = get_user_by('id', $user_id);
    $role = $user ? anj_permissions_primary_role($user) : '';
    $baseline = anj_permissions_get_baseline($user_id);
    $extra = anj_permissions_get_extra($user_id);
    $deny = anj_permissions_get_deny($user_id);
    $effective = anj_permissions_get_effective($user_id);

    return [
        'user_id' => $user_id,
        'primary_role' => $role,
        'roles' => $user ? $user->roles : [],
        'baseline' => $baseline,
        'extra' => $extra,
        'deny' => $deny,
        'effective' => $effective,
    ];
}

/**
 * All known permissions from role map (+ optional custom keys).
 */
function anj_permissions_known_keys(): array
{
    $map = anj_permissions_role_map();
    $keys = [];
    foreach ($map as $role => $perms) {
        foreach ((array)$perms as $p) $keys[] = (string)$p;
    }
    $keys = anj_permissions_normalize($keys);
    sort($keys);
    return $keys;
}

function anj_permissions_normalize(array $perms): array
{
    $out = [];
    foreach ($perms as $p) {
        if (!is_string($p)) continue;
        $p = trim($p);
        if ($p === '') continue;
        $out[] = $p;
    }
    $out = array_values(array_unique($out));
    sort($out);
    return $out;
}
