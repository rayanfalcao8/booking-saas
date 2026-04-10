<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateBookingActionScheduleValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_rejects_booking_outside_staff_schedule(): void
    {
        [, $service, $staff] = $this->seedBookingContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Ce créneau est hors horaires du prestataire.');

        app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-09',
            'start_time' => '07:00',
            'customer_name' => 'Client Outside',
            'customer_email' => 'outside@example.com',
        ]);
    }

    public function test_it_allows_booking_inside_staff_schedule(): void
    {
        [$business, $service, $staff] = $this->seedBookingContext();

        $booking = app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-09',
            'start_time' => '09:00',
            'customer_name' => 'Client Inside',
            'customer_email' => 'inside@example.com',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'status' => 'confirmed',
        ]);
    }

    private function seedBookingContext(): array
    {
        $business = Business::query()->create([
            'name' => 'Studio Schedule',
            'slug' => 'studio-schedule',
            'timezone' => 'America/Montreal',
            'email' => 'schedule@example.com',
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
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        return [$business, $service, $staff];
    }
}
