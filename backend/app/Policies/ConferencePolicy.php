<?php

namespace App\Policies;

use App\Models\Conference;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConferencePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can acquire a lock on the conference.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conference  $conference
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function acquireLock(User $user, Conference $conference)
    {
        // Super admins and admins can lock any conference
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Editors can only lock conferences they're assigned to
        if ($user->hasRole('editor')) {
            return $conference->editors()
                ->where('users.id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can force release a lock on the conference.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceReleaseLock(User $user)
    {
        // Only super admins and admins can force release locks
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view any conferences.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('access.admin');
    }

    /**
     * Determine whether the user can view the conference.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conference  $conference
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Conference $conference)
    {
        // If the conference is published, anyone can view it
        if ($conference->is_published) {
            return true;
        }

        // Admin users can view any conference
        if ($user->hasPermissionTo('access.admin')) {
            return true;
        }

        // Editors can view conferences they're assigned to
        return $conference->editors()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create conferences.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('access.admin');
    }

    /**
     * Determine whether the user can update the conference.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conference  $conference
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Conference $conference)
    {
        // Admin users can update any conference
        if ($user->hasPermissionTo('access.admin')) {
            return true;
        }

        // Editors can update conferences they're assigned to
        return $conference->editors()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the conference.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conference  $conference
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Conference $conference)
    {
        return $user->hasPermissionTo('access.admin');
    }
}