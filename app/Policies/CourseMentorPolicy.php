<?php

namespace App\Policies;

use App\Models\CourseMentor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CourseMentorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('view mentors');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CourseMentor $courseMentor): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) || $user->can('view mentors');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CourseMentor $courseMentor): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CourseMentor $courseMentor): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CourseMentor $courseMentor): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CourseMentor $courseMentor): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
