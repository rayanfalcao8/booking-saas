<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\DTO\AvailabilityQuery;
use App\Domain\Booking\Services\AvailabilityService;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_rejects_an_inactive_service_on_public_booking(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        $service->forceFill(['is_active' => false])->save();

        $response = $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'one@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertInvalid(['service_id' => 'Le service sélectionné est indisponible.']);
    }

    public function test_it_rejects_an_inactive_staff_member_on_public_booking(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        $staff->forceFill(['is_active' => false])->save();

        $response = $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'one@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertInvalid(['staff_id' => 'Le prestataire sélectionné est indisponible.']);
    }

    public function test_it_rejects_a_booking_in_the_past(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        $response = $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-02-24',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'one@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertInvalid(['start_time' => 'Le créneau doit être dans le futur.']);
    }

    public function test_it_returns_no_availability_for_a_past_date(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        $response = $this->getJson("/api/b/{$business->slug}/availability?".http_build_query([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-02-24',
        ]));

        $response
            ->assertOk()
            ->assertExactJson(['slots' => []]);
    }

    public function test_it_returns_no_availability_for_an_inactive_staff_member(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        TenantManager::set($business);
        $staff->forceFill(['is_active' => false])->save();

        $slots = app(AvailabilityService::class)->slots(new AvailabilityQuery(
            serviceId: $service->id,
            staffId: $staff->id,
            date: '2026-03-10',
            stepMin: 15,
        ));

        $this->assertSame([], $slots);
    }

    /**
     * @return array{0: Business, 1: Service, 2: Staff}
     */
    private function seedCatalog(): array
    {
        $business = Business::query()->create([
            'name' => 'Validation Studio',
            'slug' => 'validation-studio',
            'timezone' => 'America/Montreal',
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

        return [$business, $service, $staff];
    }
}
