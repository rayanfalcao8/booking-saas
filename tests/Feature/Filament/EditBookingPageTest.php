<?php

namespace Tests\Feature\Filament;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Filament\App\Resources\BookingResource\Pages\EditBooking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditBookingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_displays_a_validation_error_when_editing_to_a_taken_slot(): void
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

        $firstBooking = app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'one@example.com',
        ]);

        $secondBooking = app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '10:00',
            'customer_name' => 'Client Two',
            'customer_email' => 'two@example.com',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('app'));

        $this->actingAs($user);

        Livewire::test(EditBooking::class, [
            'record' => $secondBooking->getRouteKey(),
        ])
            ->fillForm([
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'date' => '2026-03-10',
                'start_time' => '09:00',
                'end_time' => '09:30',
                'status' => 'confirmed',
                'customer_name' => 'Client Two',
                'customer_email' => 'two@example.com',
                'customer_phone' => null,
                'notes' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'start_time' => 'Ce créneau n’est plus disponible.',
            ]);
    }
}
