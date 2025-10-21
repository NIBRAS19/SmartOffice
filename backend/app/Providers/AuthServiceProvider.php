<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Policies\UserPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Department::class => DepartmentPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for specific actions
        
        // Admin gate - check if user is admin
        Gate::define('admin', function (User $user) {
            return $user->hasRole('admin');
        });

        // Manager gate - check if user is manager
        Gate::define('manager', function (User $user) {
            return $user->hasRole('manager');
        });

        // Staff gate - check if user is staff
        Gate::define('staff', function (User $user) {
            return $user->hasRole('staff');
        });

        // View reports gate
        Gate::define('view-reports', function (User $user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        // Manage users gate
        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('users.create') || 
                   $user->hasPermission('users.update') || 
                   $user->hasPermission('users.delete');
        });

        // Manage departments gate
        Gate::define('manage-departments', function (User $user) {
            return $user->hasPermission('departments.create') || 
                   $user->hasPermission('departments.update') || 
                   $user->hasPermission('departments.delete');
        });

        // Assign tasks gate
        Gate::define('assign-tasks', function (User $user) {
            return $user->hasPermission('tasks.assign');
        });

        // Super Admin gate - before any other authorization check
        Gate::before(function (User $user, string $ability) {
            // Admin has all permissions except self-deletion
            if ($user->hasRole('admin') && $ability !== 'delete') {
                return true;
            }
        });

        // Additional custom gates

        // Check if user can manage specific department
        Gate::define('manage-department', function (User $user, Department $department) {
            return $user->hasRole('admin') || 
                   ($user->hasRole('manager') && $user->department_id === $department->id);
        });

        // Check if user can view specific user's profile
        Gate::define('view-user-profile', function (User $user, User $targetUser) {
            // Users can view their own profile
            if ($user->id === $targetUser->id) {
                return true;
            }

            // Admin can view all profiles
            if ($user->hasRole('admin')) {
                return true;
            }

            // Manager can view profiles in their department
            if ($user->hasRole('manager') && $user->department_id === $targetUser->department_id) {
                return true;
            }

            return false;
        });

        // Check if user can assign task to specific user
        Gate::define('assign-task-to-user', function (User $user, User $targetUser) {
            // Admin can assign to anyone
            if ($user->hasRole('admin')) {
                return true;
            }

            // Manager can assign to users in their department
            if ($user->hasRole('manager') && $user->department_id === $targetUser->department_id) {
                return true;
            }

            return false;
        });

        // Check if user can view department statistics
        Gate::define('view-department-statistics', function (User $user, Department $department) {
            return $user->hasRole('admin') || 
                   ($user->hasRole('manager') && $user->department_id === $department->id);
        });

        // Check if user can export data
        Gate::define('export-data', function (User $user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });
    }
}