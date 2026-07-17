<?php

namespace Tests\Feature\Filament;

use App\Core\Tenancy\TenantManager;
use App\Filament\App\Widgets\BookingOverview;
use App\Filament\Widgets\PlatformOverview;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_the_client_dashboard_summarizes_the_current_business(): void
    {
        $business = Business::query()->create([
            'name' => 'Studio Pilote',
            'slug' => 'studio-pilote',
            'timezone' => 'America/Montreal',
        ]);
        $user = User::factory()->create(['business_id' => $business->id]);

        TenantManager::set($business);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        $this->actingAs($user);

        Service::query()->create([
            'business_id' => $business->id,
            'name' => 'Consultation',
            'duration_min' => 45,
            'buffer_min' => 0,
            'is_active' => true,
        ]);
        Staff::query()->create([
            'business_id' => $business->id,
            'name' => 'Maya',
            'email' => 'maya@studio-pilote.test',
            'is_active' => true,
        ]);

        Livewire::test(BookingOverview::class)
            ->assertSee("Rendez-vous aujourd'hui")
            ->assertSee('Rendez-vous à venir')
            ->assertSee('Services actifs')
            ->assertSee('1 collaborateurs actifs');
    }

    public function test_the_admin_dashboard_summarizes_the_platform(): void
    {
        $superadmin = User::factory()->create(['is_super_admin' => true]);

        Business::query()->create([
            'name' => 'Studio Pilote',
            'slug' => 'studio-pilote',
            'timezone' => 'America/Montreal',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($superadmin);

        Livewire::test(PlatformOverview::class)
            ->assertSee('Entreprises')
            ->assertSee('Espaces Reservix')
            ->assertSee('Utilisateurs')
            ->assertSee('Rendez-vous');
    }
}
