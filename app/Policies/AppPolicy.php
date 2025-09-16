<?php

namespace App\Policies;

use App\Models\App;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, App $app): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('app_owner')) {
            return $app->owner_email === $user->email;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, App $app): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('app_owner')) {
            return $app->owner_email === $user->email;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, App $app): bool
    {
        return $user->hasRole('admin');
    }
}
