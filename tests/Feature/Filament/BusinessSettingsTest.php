<?php

namespace Tests\Feature\Filament;

use App\Core\Tenancy\TenantManager;
use App\Filament\App\Pages\BusinessSettings;
use App\Models\Business;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_an_owner_can_update_their_booking_rules(): void
    {
        $business = Business::query()->create([
            'name' => 'Studio Pilote',
            'slug' => 'studio-pilote',
            'timezone' => 'America/Montreal',
            'email' => 'owner@studio-pilote.test',
        ])->refresh();
        $user = User::factory()->create(['business_id' => $business->id]);

        TenantManager::set($business);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        $this->actingAs($user);

        Livewire::test(BusinessSettings::class)
            ->fillForm([
                'name' => 'Studio Pilote Québec',
                'email' => 'notifications@studio-pilote.test',
                'phone' => '+14185550123',
                'timezone' => 'America/Montreal',
                'is_booking_enabled' => true,
                'booking_min_notice_minutes' => 120,
                'booking_max_advance_days' => 30,
                'slot_interval_minutes' => 30,
                'cancellation_notice_hours' => 24,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Studio Pilote Québec',
            'booking_min_notice_minutes' => 120,
            'booking_max_advance_days' => 30,
            'slot_interval_minutes' => 30,
            'cancellation_notice_hours' => 24,
        ]);
    }
}
