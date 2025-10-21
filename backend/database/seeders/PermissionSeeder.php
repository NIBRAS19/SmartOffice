<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // User permissions
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View all users'],
            ['name' => 'Create User', 'slug' => 'users.create', 'description' => 'Create new users'],
            ['name' => 'Update User', 'slug' => 'users.update', 'description' => 'Update existing users'],
            ['name' => 'Delete User', 'slug' => 'users.delete', 'description' => 'Delete users'],

            // Department permissions
            ['name' => 'View Departments', 'slug' => 'departments.view', 'description' => 'View all departments'],
            ['name' => 'Create Department', 'slug' => 'departments.create', 'description' => 'Create new departments'],
            ['name' => 'Update Department', 'slug' => 'departments.update', 'description' => 'Update existing departments'],
            ['name' => 'Delete Department', 'slug' => 'departments.delete', 'description' => 'Delete departments'],

            // Task permissions
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'description' => 'View tasks'],
            ['name' => 'Create Task', 'slug' => 'tasks.create', 'description' => 'Create new tasks'],
            ['name' => 'Update Task', 'slug' => 'tasks.update', 'description' => 'Update existing tasks'],
            ['name' => 'Delete Task', 'slug' => 'tasks.delete', 'description' => 'Delete tasks'],
            ['name' => 'Assign Task', 'slug' => 'tasks.assign', 'description' => 'Assign tasks to users'],

            // Role permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View all roles'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Create, update, and delete roles'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('Permissions seeded and assigned successfully!');
    }

    private function assignPermissionsToRoles(): void
    {
        $admin = Role::where('slug', 'admin')->first();
        $manager = Role::where('slug', 'manager')->first();
        $staff = Role::where('slug', 'staff')->first();

        // Admin gets all permissions
        $allPermissions = Permission::all();
        $admin->permissions()->sync($allPermissions);

        // Manager permissions - EXPANDED
        $managerPermissions = Permission::whereIn('slug', [
            // User permissions
            'users.view',
            'users.create',      // Added
            'users.update',      // Added
            
            // Department permissions
            'departments.view',
            'departments.update', // Added
            
            // Task permissions
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',      // Added
            'tasks.assign',
        ])->get();
        $manager->permissions()->sync($managerPermissions);

        // Staff permissions
        $staffPermissions = Permission::whereIn('slug', [
            'tasks.view',
            'tasks.update', // Only their own tasks
        ])->get();
        $staff->permissions()->sync($staffPermissions);
    }
}