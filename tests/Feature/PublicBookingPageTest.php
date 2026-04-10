<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_only_current_tenant_catalog_in_public_booking_page(): void
    {
        $tenant = Business::query()->create([
            'name' => 'Studio One',
            'slug' => 'studio-one',
            'timezone' => 'America/Montreal',
        ]);

        $otherTenant = Business::query()->create([
            'name' => 'Studio Two',
            'slug' => 'studio-two',
            'timezone' => 'America/Montreal',
        ]);

        Service::query()->create([
            'business_id' => $tenant->id,
            'name' => 'Hair Cut',
            'duration_min' => 30,
            'buffer_min' => 0,
            'is_active' => true,
        ]);

        Staff::query()->create([
            'business_id' => $tenant->id,
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'is_active' => true,
        ]);

        Service::query()->create([
            'business_id' => $otherTenant->id,
            'name' => 'Massage',
            'duration_min' => 60,
            'buffer_min' => 0,
            'is_active' => true,
        ]);

        Staff::query()->create([
            'business_id' => $otherTenant->id,
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'is_active' => true,
        ]);

        $response = $this->get("/b/{$tenant->slug}/book");

        $response
            ->assertOk()
            ->assertSee('Hair Cut')
            ->assertSee('Alice')
            ->assertDontSee('Massage')
            ->assertDontSee('Bob')
            ->assertSee('Agenda semaine')
            ->assertSee('Par prestataire');
    }
}
