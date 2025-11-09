<?php

namespace App\Policies;

use App\Models\Users\Profile;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class ProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Profile $profile): bool
    {
        if ($user->hasGroup('managers') || $user->hasGroup('admin')) {
            return true;
        }

        return $user->id == $profile->user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->profiles()->count() < 5;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Profile $profile): bool
    {
        if($user->hasGroup('managers') || $user->hasGroup('admin')) {
            return true;
        }
        return $user->id == $profile->user_id ;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Profile $profile): bool
    {
        if($user->hasGroup('managers') || $user->hasGroup('admin')) {
            return true;
        }
        return $user->id == $profile->user_id ;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Profile $profile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Profile $profile): bool
    {
        return false;
    }
}
