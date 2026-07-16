<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_demo_businesses_and_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('businesses', 3);
        $this->assertDatabaseHas('businesses', ['slug' => 'maison-kinks-braids']);
        $this->assertDatabaseHas('businesses', ['slug' => 'northside-fade-club']);
        $this->assertDatabaseHas('businesses', ['slug' => 'sparkle-move-services']);
        $this->assertDatabaseHas('users', ['email' => 'admin@reservix.test', 'is_super_admin' => true]);
        $this->assertDatabaseHas('users', ['email' => 'owners@maisonkinks.test', 'is_super_admin' => false]);
        $this->assertDatabaseHas('users', ['email' => 'owners@northsidefade.test', 'is_super_admin' => false]);
        $this->assertDatabaseHas('users', ['email' => 'owners@sparklemove.test', 'is_super_admin' => false]);
        $this->assertDatabaseCount('services', 12);
        $this->assertDatabaseCount('staff', 6);
        $this->assertDatabaseCount('bookings', 15);
        $this->assertDatabaseCount('staff_schedules', 57);
        $this->assertDatabaseHas('bookings', ['status' => 'confirmed']);
        $this->assertDatabaseHas('bookings', ['status' => 'completed']);
        $this->assertDatabaseHas('bookings', ['status' => 'no_show']);
        $this->assertDatabaseHas('bookings', ['status' => 'canceled']);
    }
}
