<?php

use App\Models\User;

if (!function_exists('resolve_post_login_redirect')) {
    /**
     * Tentukan URL redirect setelah login/verifikasi berdasarkan role.
     * Admin, super-admin, dan mentor diarahkan ke panel admin Filament.
     * Selain itu diarahkan ke dashboard kursus front-end.
     */
    function resolve_post_login_redirect(?User $user): string
    {
        // Default front dashboard
        $frontDashboard = route('dashboard', absolute: false);

        if (!$user) {
            return $frontDashboard;
        }

        // Deteksi role case-insensitive untuk mencegah mismatch
        $roleNames = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->map(fn ($n) => strtolower($n))
            : collect();

        if ($roleNames->isEmpty() && method_exists($user, 'roles')) {
            $roleNames = $user->roles->pluck('name')->map(fn ($n) => strtolower($n));
        }

        if ($roleNames->contains('super-admin') || $roleNames->contains('admin') || $roleNames->contains('mentor')) {
            return '/admin';
        }

        return $frontDashboard;
    }
}

