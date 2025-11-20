<?php

namespace App\Policies;

use App\Models\WhatsappMessageTemplate;
use App\Models\User;

class WhatsappMessageTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, WhatsappMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, WhatsappMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, WhatsappMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, WhatsappMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, WhatsappMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

