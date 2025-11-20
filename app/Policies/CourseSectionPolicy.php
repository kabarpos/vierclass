<?php

namespace App\Policies;

use App\Models\CourseSection;
use App\Models\User;

class CourseSectionPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin penuh; mentor diizinkan, listing akan difilter oleh context resource
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('view courses') || $user->can('view content');
    }

    public function view(User $user, CourseSection $section): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }
        if ($user->hasRole('mentor')) {
            $course = $section->course;
            return $course && $course->courseMentors()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    public function create(User $user): bool
    {
        // Mentor dengan izin edit courses boleh menambah section pada course-nya
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('edit courses');
    }

    public function update(User $user, CourseSection $section): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }
        if ($user->can('edit courses')) {
            $course = $section->course;
            return $course && $course->courseMentors()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    public function delete(User $user, CourseSection $section): bool
    {
        // Hanya admin/super-admin yang boleh delete section (aksi destruktif)
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, CourseSection $section): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, CourseSection $section): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
