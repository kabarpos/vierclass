<?php

namespace App\Policies;

use App\Models\MidtransSetting;
use App\Models\User;

class MidtransSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, MidtransSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, MidtransSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, MidtransSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, MidtransSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, MidtransSetting $setting): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

