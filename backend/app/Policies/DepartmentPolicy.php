<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;

class DepartmentPolicy
{
    /**
     * Determine if the user can view any departments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('departments.view');
    }

    /**
     * Determine if the user can view the department.
     */
    public function view(User $user, Department $department): bool
    {
        // Admin can view all departments
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can view their own department
        if ($user->hasRole('manager') && $user->department_id === $department->id) {
            return true;
        }

        // Staff can view their own department
        return $user->department_id === $department->id;
    }

    /**
     * Determine if the user can create departments.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('departments.create');
    }

    /**
     * Determine if the user can update the department.
     */
    public function update(User $user, Department $department): bool
    {
        // Admin can update all departments
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can update their own department (limited fields)
        return $user->hasRole('manager') && $user->department_id === $department->id;
    }

    /**
     * Determine if the user can delete the department.
     */
    public function delete(User $user, Department $department): bool
    {
        // Only admin can delete departments
        return $user->hasPermission('departments.delete');
    }

    /**
     * Determine if the user can assign a manager to the department.
     */
    public function assignManager(User $user, Department $department): bool
    {
        return $user->hasRole('admin');
    }
}