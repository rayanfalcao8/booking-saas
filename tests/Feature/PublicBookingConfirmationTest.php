<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_displays_public_confirmation_page_with_valid_token(): void
    {
        [$business, $booking] = $this->seedBooking();

        $response = $this->get(route('public.booking.confirmation', [
            'business' => $business->slug,
            'booking' => $booking->id,
            'token' => $booking->cancellation_token,
        ]));

        $response
            ->assertOk()
            ->assertSee('Réservation confirmée')
            ->assertSee('Annuler cette réservation');
    }

    public function test_it_returns_not_found_for_invalid_confirmation_token(): void
    {
        [$business, $booking] = $this->seedBooking();

        $response = $this->get(route('public.booking.confirmation', [
            'business' => $business->slug,
            'booking' => $booking->id,
            'token' => 'invalid',
        ]));

        $response->assertNotFound();
    }

    private function seedBooking(): array
    {
        $business = Business::query()->create([
            'name' => 'Confirm Studio',
            'slug' => 'confirm-studio',
            'timezone' => 'America/Montreal',
            'email' => 'confirm@example.com',
        ]);

        TenantManager::set($business);

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

        $booking = app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client Confirm',
            'customer_email' => 'client@example.com',
        ]);

        return [$business, $booking];
    }
}
