<?php

namespace Tests\Feature\Filament;

use App\Core\Tenancy\TenantManager;
use App\Filament\App\Resources\BookingResource\Pages\CreateBooking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Models\StaffSchedule;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateBookingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_a_validation_error_on_start_time_when_slot_is_already_taken(): void
    {
        $business = Business::query()->create([
            'name' => 'Studio Demo',
            'slug' => 'studio-demo',
            'timezone' => 'America/Montreal',
        ]);

        TenantManager::set($business);

        $user = User::factory()->create([
            'business_id' => $business->id,
            'is_super_admin' => false,
        ]);

        $service = Service::query()->create([
            'business_id' => $business->id,
            'name' => 'Cut',
            'duration_min' => 30,
            'buffer_min' => 0,
            'is_active' => true,
        ]);

        $staff = Staff::query()->create([
            'business_id' => $business->id,
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'is_active' => true,
        ]);

        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 2,
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('app'));

        $this->actingAs($user);

        $formData = [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'end_time' => '09:30',
            'status' => 'confirmed',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
            'customer_phone' => '555-0100',
            'notes' => 'Test booking',
        ];

        Livewire::test(CreateBooking::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateBooking::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasFormErrors([
                'start_time' => 'Ce créneau n’est plus disponible.',
            ]);
    }
}
