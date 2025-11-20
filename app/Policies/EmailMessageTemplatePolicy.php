<?php

namespace App\Policies;

use App\Models\EmailMessageTemplate;
use App\Models\User;

class EmailMessageTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, EmailMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, EmailMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, EmailMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, EmailMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, EmailMessageTemplate $template): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}

