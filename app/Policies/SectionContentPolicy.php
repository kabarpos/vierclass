<?php

namespace App\Policies;

use App\Models\SectionContent;
use App\Models\User;

class SectionContentPolicy
{
    public function viewAny(User $user): bool
    {
        // Izinkan admin & mentor untuk masuk ke resource; listing akan difilter di Resource
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('view content');
    }

    public function view(User $user, SectionContent $content): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        // Mentor boleh melihat jika konten berada di course yang di-mentori.
        $course = optional($content->section)->course;
        if ($course && $user->hasRole('mentor')) {
            return $course->mentors()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Izinkan admin & mentor membuat konten
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('create content');
    }

    public function update(User $user, SectionContent $content): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }
        // Mentor boleh update jika course yang di-mentori
        $course = optional($content->section)->course;
        if ($course && $user->hasRole('mentor')) {
            return $course->mentors()->where('users.id', $user->id)->exists();
        }
        return false;
    }

    public function delete(User $user, SectionContent $content): bool
    {
        // Hanya admin/super-admin
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, SectionContent $content): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, SectionContent $content): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
