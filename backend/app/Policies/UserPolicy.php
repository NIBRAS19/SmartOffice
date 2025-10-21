<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admin can view all users
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can view users in their department
        if ($user->hasRole('manager') && $user->department_id === $model->department_id) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin can update all users
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can update users in their department (but not other managers/admins)
        if ($user->hasRole('manager') && 
            $user->department_id === $model->department_id &&
            !$model->hasAnyRole(['admin', 'manager'])) {
            return true;
        }

        // Users can update their own profile (limited fields)
        return $user->id === $model->id;
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        // Only admin can delete users
        return $user->hasPermission('users.delete');
    }

    /**
     * Determine if the user can assign roles.
     */
    public function assignRoles(User $user, User $model): bool
    {
        // Only admin can assign roles
        return $user->hasRole('admin') && $user->id !== $model->id;
    }
}