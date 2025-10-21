<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        // Get departments
        $engineering = Department::where('name', 'Engineering')->first();
        $marketing = Department::where('name', 'Marketing')->first();
        $sales = Department::where('name', 'Sales')->first();

        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@smartoffice.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);

        // Create Manager Users
        $engineeringManager = User::create([
            'name' => 'John Manager',
            'email' => 'manager@smartoffice.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'department_id' => $engineering->id,
        ]);
        $engineeringManager->assignRole($managerRole);
        $engineering->update(['manager_id' => $engineeringManager->id]);

        $marketingManager = User::create([
            'name' => 'Sarah Marketing',
            'email' => 'sarah.marketing@smartoffice.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'department_id' => $marketing->id,
        ]);
        $marketingManager->assignRole($managerRole);
        $marketing->update(['manager_id' => $marketingManager->id]);

        // Create Staff Users
        $staff1 = User::create([
            'name' => 'Alice Developer',
            'email' => 'staff@smartoffice.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'department_id' => $engineering->id,
        ]);
        $staff1->assignRole($staffRole);

        $staff2 = User::create([
            'name' => 'Bob Developer',
            'email' => 'bob.dev@smartoffice.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'department_id' => $engineering->id,
        ]);
        $staff2->assignRole($staffRole);

        $staff3 = User::create([
            'name' => 'Carol Designer',
            'email' => 'carol.designer@smartoffice.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'department_id' => $marketing->id,
        ]);
        $staff3->assignRole($staffRole);

        // Create MORE sample tasks for staff
        $this->createTasksForDepartment($engineeringManager, [$staff1, $staff2], $engineering);
        $this->createTasksForDepartment($marketingManager, [$staff3], $marketing);

        $this->command->info('Users seeded successfully!');
    }

    private function createTasksForDepartment($manager, $staffMembers, $department): void
    {
        $taskTemplates = [
            [
                'title' => 'Complete API documentation',
                'description' => 'Write comprehensive API documentation for all endpoints',
                'status' => 'pending',
                'days' => 7
            ],
            [
                'title' => 'Fix authentication bugs',
                'description' => 'Resolve reported issues with user authentication flow',
                'status' => 'in_progress',
                'days' => 3
            ],
            [
                'title' => 'Review pull requests',
                'description' => 'Review and merge pending pull requests from team',
                'status' => 'pending',
                'days' => 2
            ],
            [
                'title' => 'Update database schema',
                'description' => 'Add new fields to users table for enhanced features',
                'status' => 'pending',
                'days' => 5
            ],
            [
                'title' => 'Write unit tests',
                'description' => 'Create comprehensive unit tests for core modules',
                'status' => 'in_progress',
                'days' => 10
            ],
            [
                'title' => 'Implement caching',
                'description' => 'Add Redis caching to improve application performance',
                'status' => 'pending',
                'days' => 4
            ]
        ];

        foreach ($taskTemplates as $index => $template) {
            // Assign tasks to staff members in round-robin fashion
            $assignedStaff = $staffMembers[$index % count($staffMembers)];
            
            Task::create([
                'title' => $template['title'],
                'description' => $template['description'],
                'status' => $template['status'],
                'department_id' => $department->id,
                'assigned_to' => $assignedStaff->id,
                'assigned_by' => $manager->id,
                'due_date' => now()->addDays($template['days']),
            ]);
        }
    }
}