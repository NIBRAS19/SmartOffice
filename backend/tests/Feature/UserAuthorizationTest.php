<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling(); // Shows actual errors
        $this->artisan('db:seed');
    }

    public function test_admin_can_view_all_users(): void
    {
        $admin = User::where('email', 'admin@smartoffice.com')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'users',
                    'meta',
                ],
            ]);
    }

    public function test_manager_can_view_users_in_their_department(): void
    {
        $manager = User::where('email', 'manager@smartoffice.com')->first();

        $response = $this->actingAs($manager, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
    }

    public function test_staff_cannot_view_all_users(): void
    {
        $staff = User::where('email', 'staff@smartoffice.com')->first();

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::where('email', 'admin@smartoffice.com')->first();
        $department = Department::first();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'department_id' => $department->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    public function test_staff_cannot_create_user(): void
    {
        $staff = User::where('email', 'staff@smartoffice.com')->first();
        $department = Department::first();

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser2@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'department_id' => $department->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::where('email', 'admin@smartoffice.com')->first();
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $this->withExceptionHandling(); // Re-enable exception handling for this specific test
        
        $admin = User::where('email', 'admin@smartoffice.com')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/users/{$admin->id}");

        // Policy denies self-deletion with 403
        $response->assertStatus(403);
    }
}