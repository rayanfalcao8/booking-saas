<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_the_route_tenant_and_clears_it_after_the_request(): void
    {
        $staleBusiness = Business::query()->create([
            'name' => 'Stale Studio',
            'slug' => 'stale-studio',
            'timezone' => 'America/Montreal',
        ]);

        $routeBusiness = Business::query()->create([
            'name' => 'Route Studio',
            'slug' => 'route-studio',
            'timezone' => 'America/Montreal',
        ]);

        TenantManager::set($staleBusiness);

        $this->getJson("/b/{$routeBusiness->slug}/ping")
            ->assertOk()
            ->assertJsonPath('business_id', $routeBusiness->id)
            ->assertJsonPath('business_slug', 'route-studio');

        $this->assertNull(TenantManager::get());
    }
}
