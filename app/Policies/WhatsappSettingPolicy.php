<?php

namespace App\Policies;

use App\Models\WhatsappSetting;
use App\Models\User;

class WhatsappSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, WhatsappSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, WhatsappSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, WhatsappSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, WhatsappSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, WhatsappSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

