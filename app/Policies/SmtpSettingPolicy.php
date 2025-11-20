<?php

namespace App\Policies;

use App\Models\SmtpSetting;
use App\Models\User;

class SmtpSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, SmtpSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, SmtpSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, SmtpSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, SmtpSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, SmtpSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

