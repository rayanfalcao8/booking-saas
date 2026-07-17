<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\Actions\UpdateBookingStatusAction;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateBookingStatusActionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_rejects_no_show_before_appointment_time(): void
    {
        [, $booking] = $this->seedBooking('2030-03-12', '10:00');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Le statut no-show est autorisé uniquement après l’heure prévue.');

        app(UpdateBookingStatusAction::class)->run($booking, 'no_show');
    }

    public function test_it_allows_no_show_after_appointment_time(): void
    {
        [, $booking] = $this->seedBooking('2026-03-10', '09:00');
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'America/Montreal'));

        $updated = app(UpdateBookingStatusAction::class)->run($booking, 'no_show');

        $this->assertSame('no_show', $updated->status);
    }

    public function test_it_allows_marking_a_booking_as_completed_after_end_time(): void
    {
        [, $booking] = $this->seedBooking('2026-03-10', '09:00');
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'America/Montreal'));

        $updated = app(UpdateBookingStatusAction::class)->run($booking, 'completed');

        $this->assertSame('completed', $updated->status);
    }

    public function test_it_rejects_transition_from_canceled_to_no_show(): void
    {
        [, $booking] = $this->seedBooking('2026-03-10', '09:00');

        $action = app(UpdateBookingStatusAction::class);
        $action->run($booking, 'canceled');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Une réservation annulée ne peut plus changer de statut.');

        $action->run($booking->refresh(), 'no_show');
    }

    public function test_it_allows_confirming_a_no_show_booking_again(): void
    {
        [, $booking] = $this->seedBooking('2026-03-10', '09:00');
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'America/Montreal'));

        $action = app(UpdateBookingStatusAction::class);
        $booking = $action->run($booking, 'no_show');
        $booking = $action->run($booking, 'confirmed');

        $this->assertSame('confirmed', $booking->status);
    }

    public function test_it_rejects_a_booking_from_another_tenant(): void
    {
        [, $booking] = $this->seedBooking('2026-03-10', '09:00');

        $otherBusiness = Business::query()->create([
            'name' => 'Other Status Studio',
            'slug' => 'other-status-studio',
            'timezone' => 'America/Montreal',
        ]);

        TenantManager::set($otherBusiness);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La réservation sélectionnée est invalide pour ce business.');

        app(UpdateBookingStatusAction::class)->run($booking, 'canceled');
    }

    private function seedBooking(string $date, string $startTime): array
    {
        $business = Business::query()->create([
            'name' => 'Status Studio',
            'slug' => 'status-studio',
            'timezone' => 'America/Montreal',
            'email' => 'status@example.com',
            'booking_max_advance_days' => 3650,
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
            'date' => $date,
            'start_time' => $startTime,
            'customer_name' => 'Client Status',
            'customer_email' => 'client@example.com',
        ]);

        return [$business, $booking];
    }
}
