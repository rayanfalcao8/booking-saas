<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_cancels_booking_from_public_cancel_link(): void
    {
        [$business, $booking] = $this->seedBooking();

        $response = $this->get(route('public.booking.cancel', [
            'business' => $business->slug,
            'booking' => $booking->id,
            'token' => $booking->cancellation_token,
        ]));

        $response->assertOk()->assertSee('Annulation confirmée');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'canceled',
        ]);
    }

    public function test_it_rejects_invalid_cancellation_link(): void
    {
        [$business, $booking] = $this->seedBooking();

        $response = $this->get(route('public.booking.cancel', [
            'business' => $business->slug,
            'booking' => $booking->id,
            'token' => 'invalid-token',
        ]));

        $response->assertOk()->assertSee('Erreur d’annulation');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }


    public function test_it_prevents_canceling_booking_from_another_tenant_slug(): void
    {
        [$business, $booking] = $this->seedBooking();

        $otherBusiness = Business::query()->create([
            'name' => 'Other Studio',
            'slug' => 'other-studio',
            'timezone' => 'America/Montreal',
        ]);

        $response = $this->postJson("/api/b/{$otherBusiness->slug}/book/{$booking->id}/cancel", [
            'token' => $booking->cancellation_token,
        ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }


    public function test_it_rejects_expired_cancellation_token(): void
    {
        [$business, $booking] = $this->seedBooking();

        $booking->forceFill([
            'cancellation_expires_at' => now()->subMinute(),
        ])->save();

        $response = $this->postJson("/api/b/{$business->slug}/book/{$booking->id}/cancel", [
            'token' => $booking->cancellation_token,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'Lien d’annulation invalide ou expiré.');
    }

    public function test_it_cancels_booking_from_public_api_endpoint(): void
    {
        [$business, $booking] = $this->seedBooking();

        $response = $this->postJson("/api/b/{$business->slug}/book/{$booking->id}/cancel", [
            'token' => $booking->cancellation_token,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'canceled');
    }

    private function seedBooking(): array
    {
        $business = Business::query()->create([
            'name' => 'Cancel Studio',
            'slug' => 'cancel-studio',
            'timezone' => 'America/Montreal',
            'email' => 'cancel@example.com',
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
            'date' => Carbon::now('America/Montreal')->next(Carbon::TUESDAY)->format('Y-m-d'),
            'start_time' => '09:00',
            'customer_name' => 'Client Cancel',
            'customer_email' => 'client@example.com',
        ]);

        return [$business, $booking];
    }
}
