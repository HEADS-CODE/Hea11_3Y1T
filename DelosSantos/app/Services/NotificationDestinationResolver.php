<?php

namespace App\Services;

use App\Models\User;

class NotificationDestinationResolver
{
    public function resolve(User $user, ?string $url): string
    {
        $fallback = $user->isAdmin() ? route('admin.dashboard') : route('tasks.index');

        if (! $url) {
            return $fallback;
        }

        $parts = parse_url($url);
        $path = $parts['path'] ?? null;
        $host = $parts['host'] ?? null;

        // Notifications may only point to this application. External or malformed
        // destinations are never rendered as a portal link.
        $knownHosts = array_values(array_filter([
            request()->getHost(),
            parse_url((string) config('app.url'), PHP_URL_HOST),
        ]));

        if (! is_string($path) || ($host !== null && $knownHosts !== [] && ! in_array($host, $knownHosts, true))) {
            return $fallback;
        }

        $allowedPrefixes = $user->isAdmin()
            ? [
                '/login/admin', '/verify-email', '/approval-pending', '/account/inactive',
                '/admin/dashboard', '/admin/profile', '/members', '/membership-categories',
                '/membership-levels', '/admin/approvals', '/admin/transactions',
                '/admin/requests', '/admin/activity', '/admin/reports', '/admin/tasks',
            ]
            : [
                '/login/member', '/verify-email', '/approval-pending', '/account/inactive',
                '/tasks', '/my-tasks', '/my-membership', '/membership/categories',
                '/membership/levels', '/my-transactions', '/my-requests', '/membership/renew',
                '/membership/level-change', '/membership/category-change',
                '/membership/profile-change', '/membership/payment', '/portal',
            ];

        $allowed = collect($allowedPrefixes)->contains(fn (string $prefix): bool =>
            $path === $prefix || str_starts_with($path, $prefix.'/')
        );

        if (! $allowed) {
            return $fallback;
        }

        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return url($path.$query);
    }
}
