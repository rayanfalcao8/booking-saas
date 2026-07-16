<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingRequestValidationTest extends TestCase
{
    use RefreshDatabase;

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
