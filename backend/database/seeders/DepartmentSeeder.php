<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Engineering',
                'description' => 'Software development and technical operations',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Marketing campaigns and brand management',
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales operations and customer acquisition',
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Employee management and recruitment',
            ],
            [
                'name' => 'Finance',
                'description' => 'Financial planning and accounting',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('Departments seeded successfully!');
    }
}
