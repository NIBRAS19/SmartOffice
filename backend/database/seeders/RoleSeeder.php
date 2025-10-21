<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Has full access to all system features and resources',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage department users and assign tasks',
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Can view and complete assigned tasks',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('Roles seeded successfully!');
    }
}
