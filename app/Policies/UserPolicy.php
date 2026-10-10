<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return hasRole(Roles::ADMIN, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return hasRole(Roles::ADMIN, $user) || $user->is($model);
    }

    /**
     * Determine whether the user can delete the model. Nobody can delete themselves from here,
     * as the profile has its own, password protected, account deletion.
     */
    public function delete(User $user, User $model): Response|bool
    {
        if ($user->is($model)) {
            return Response::deny(trans('exceptions.cannot_delete_yourself'));
        }

        return hasRole(Roles::ADMIN, $user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return hasRole(Roles::ADMIN, $user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return hasRole(Roles::ADMIN, $user);
    }
}
