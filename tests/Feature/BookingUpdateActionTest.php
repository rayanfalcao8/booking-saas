<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\Actions\UpdateBookingDetailsAction;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_rejects_updating_a_booking_to_an_already_taken_slot(): void
    {
        [$service, $staff] = $this->seedContext();

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

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Ce créneau n’est plus disponible.');

        app(UpdateBookingDetailsAction::class)->run($secondBooking, [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client Two',
            'customer_email' => 'two@example.com',
            'customer_phone' => null,
            'notes' => null,
        ]);
    }

    public function test_it_rejects_updating_a_confirmed_booking_to_an_inactive_service(): void
    {
        [$service, $staff] = $this->seedContext();

        $inactiveService = Service::query()->create([
            'business_id' => TenantManager::id(),
            'name' => 'Inactive Service',
            'duration_min' => 30,
            'buffer_min' => 0,
            'is_active' => false,
        ]);

        $booking = app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'one@example.com',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Le service sélectionné est indisponible.');

        app(UpdateBookingDetailsAction::class)->run($booking, [
            'service_id' => $inactiveService->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:30',
            'customer_name' => 'Client One',
            'customer_email' => 'one@example.com',
            'customer_phone' => null,
            'notes' => null,
        ]);
    }

    /**
     * @return array{0: Service, 1: Staff}
     */
    private function seedContext(): array
    {
        $business = Business::query()->create([
            'name' => 'Update Studio',
            'slug' => 'update-studio',
            'timezone' => 'America/Montreal',
            'email' => 'update@example.com',
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

        return [$service, $staff];
    }
}
