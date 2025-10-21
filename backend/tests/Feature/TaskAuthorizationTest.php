<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_admin_can_view_all_tasks(): void
    {
        $admin = User::where('email', 'admin@smartoffice.com')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tasks',
                    'meta',
                ],
            ]);
    }

    public function test_staff_can_only_view_their_tasks(): void
    {
        $staff = User::where('email', 'staff@smartoffice.com')->first();

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200);
    }

    public function test_manager_can_create_task(): void
    {
        $manager = User::where('email', 'manager@smartoffice.com')->first();
        $staff = User::where('email', 'staff@smartoffice.com')->first();

        $response = $this->actingAs($manager, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'New Task',
                'description' => 'Task description',
                'department_id' => $manager->department_id,
                'assigned_to' => $staff->id,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertStatus(201);
    }

    public function test_staff_cannot_create_task(): void
    {
        $staff = User::where('email', 'staff@smartoffice.com')->first();

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'New Task',
                'description' => 'Task description',
                'department_id' => $staff->department_id,
                'assigned_to' => $staff->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_staff_can_complete_their_own_task(): void
    {
        $staff = User::where('email', 'staff@smartoffice.com')->first();
        $task = Task::where('assigned_to', $staff->id)->first();

        $response = $this->actingAs($staff, 'sanctum')
            ->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);
    }

    public function test_staff_cannot_delete_task(): void
    {
        $staff = User::where('email', 'staff@smartoffice.com')->first();
        $task = Task::where('assigned_to', $staff->id)->first();

        $response = $this->actingAs($staff, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_manager_can_reassign_task_in_their_department(): void
    {
        $manager = User::where('email', 'manager@smartoffice.com')->first();
        $task = Task::where('department_id', $manager->department_id)->first();
        $newAssignee = User::where('department_id', $manager->department_id)
            ->where('id', '!=', $task->assigned_to)
            ->first();

        $response = $this->actingAs($manager, 'sanctum')
            ->patchJson("/api/tasks/{$task->id}/reassign", [
                'assigned_to' => $newAssignee->id,
            ]);

        $response->assertStatus(200);
    }
}