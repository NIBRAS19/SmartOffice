<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    /**
     * Determine if the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks.view');
    }

    /**
     * Determine if the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin can view all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can view tasks in their department
        if ($user->hasRole('manager') && $user->department_id === $task->department_id) {
            return true;
        }

        // Staff can view their own assigned tasks
        return $task->assigned_to === $user->id;
    }

    /**
     * Determine if the user can create tasks.
     */
    public function create(User $user): bool
    {
        // Admin and Manager can create tasks
        return $user->hasPermission('tasks.create');
    }

    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin can update all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can update tasks in their department
        if ($user->hasRole('manager') && $user->department_id === $task->department_id) {
            return true;
        }

        // Staff can update their own tasks (status only)
        return $task->assigned_to === $user->id;
    }

    /**
     * Determine if the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // Admin can delete all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can delete tasks they created in their department
        return $user->hasRole('manager') && 
               $task->assigned_by === $user->id &&
               $user->department_id === $task->department_id;
    }

    /**
     * Determine if the user can assign the task to someone.
     */
    public function assign(User $user): bool
    {
        return $user->hasPermission('tasks.assign');
    }

    /**
     * Determine if the user can complete the task.
     */
    public function complete(User $user, Task $task): bool
    {
        // Admin and Manager can complete any task in their scope
        if ($user->hasRole('admin') || 
            ($user->hasRole('manager') && $user->department_id === $task->department_id)) {
            return true;
        }

        // Staff can complete their own assigned tasks
        return $task->assigned_to === $user->id;
    }

    /**
     * Determine if the user can reassign the task.
     */
    public function reassign(User $user, Task $task): bool
    {
        // Admin can reassign all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Manager can reassign tasks in their department
        return $user->hasRole('manager') && $user->department_id === $task->department_id;
    }
}