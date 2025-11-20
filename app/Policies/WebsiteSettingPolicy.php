<?php

namespace App\Policies;

use App\Models\WebsiteSetting;
use App\Models\User;

class WebsiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, WebsiteSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, WebsiteSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, WebsiteSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, WebsiteSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, WebsiteSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

