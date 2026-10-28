<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\SystemNotification;

class NotificationTarget
{
    /**
     * The dashboard route each role owns.
     */
    private const ROLE_DASHBOARD = [
        User::ROLE_ADMIN => 'admin.dashboard',
        User::ROLE_OPERATIONS => 'operations.dashboard',
        User::ROLE_SUPERVISOR => 'supervisor.dashboard',
        User::ROLE_FIELD_PERSONNEL => 'field.dashboard',
    ];

    /**
     * The path prefix each role is allowed to browse.
     */
    private const ROLE_PATH = [
        User::ROLE_ADMIN => '/admin',
        User::ROLE_OPERATIONS => '/operations',
        User::ROLE_SUPERVISOR => '/supervisor',
        User::ROLE_FIELD_PERSONNEL => '/field',
    ];

    /**
     * Resolve the link a notification should open for the given account.
     *
     * Notifications are raised for a specific audience, so the stored target is
     * only used when the recipient is allowed to open it. Otherwise the link
     * falls back to the recipient's own dashboard.
     *
     * @param  array<string, mixed>  $data
     */
    public static function resolve(array $data, User $user): ?string
    {
        $fallback = self::ROLE_DASHBOARD[$user->role] ?? null;

        if ($fallback === null) {
            return null;
        }

        $route = $data['route'] ?? null;
        $target = null;

        if (is_string($route) && $route !== '') {
            if (self::isAccessible($route, $user)) {
                $target = route($route);
            }
        } else {
            $target = self::legacyUrl($data, $user);
        }

        $target ??= route($fallback);

        if (($data['type'] ?? null) === SystemNotification::TYPE_LOW_INVENTORY) {
            if (str_contains($target, '/operations/dashboard')) {
                return $target.'#material-inventory-heading';
            }

            if (str_contains($target, '/admin/dashboard')) {
                return $target.'#material-risk-heading';
            }
        }

        return $target;
    }

    /**
     * Determine whether the signed-in account may open the given route.
     */
    private static function isAccessible(string $route, User $user): bool
    {
        return ($user->role !== null) && (self::ROLE_DASHBOARD[$user->role] ?? null) === $route;
    }

    /**
     * Read notifications created before targets were stored as route names.
     *
     * @param  array<string, mixed>  $data
     */
    private static function legacyUrl(array $data, User $user): ?string
    {
        $url = $data['url'] ?? null;

        if (! is_string($url) || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $allowed = self::ROLE_PATH[$user->role] ?? null;

        if ($allowed === null || ! is_string($path) || ! str_starts_with($path, $allowed)) {
            return null;
        }

        return $url;
    }
}
