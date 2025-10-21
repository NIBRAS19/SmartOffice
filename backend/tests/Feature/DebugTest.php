<?php

// tests/Feature/DebugTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_debug_admin_view_users(): void
    {
        $admin = User::where('email', 'admin@smartoffice.com')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/users');

        // This will show the actual error
        if ($response->status() === 500) {
            dump($response->json());
        }

        $response->assertStatus(200);
    }

    public function test_debug_admin_view_tasks(): void
    {
        $admin = User::where('email', 'admin@smartoffice.com')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/tasks');

        // This will show the actual error
        if ($response->status() === 500) {
            dump($response->json());
        }

        $response->assertStatus(200);
    }
}