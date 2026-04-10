<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookEndpointTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_service_from_another_tenant(): void
    {
        [$businessOne, , $staffOne] = $this->seedBusinessWithCatalog('studio-one', 'service-one@example.com');
        [, $serviceTwo] = $this->seedBusinessWithCatalog('studio-two', 'service-two@example.com');

        $response = $this->postJson("/api/b/{$businessOne->slug}/book", [
            'service_id' => $serviceTwo->id,
            'staff_id' => $staffOne->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('service_id');
    }


    public function test_it_creates_booking_for_current_tenant_with_success_status(): void
    {
        [$business, $service, $staff] = $this->seedBusinessWithCatalog('studio-three', 'service-three@example.com');

        $response = $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '10:00',
            'customer_name' => 'Client Success',
            'customer_email' => 'success@example.com',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'confirmed')
            ->assertJsonStructure(['id', 'status', 'confirmation_url']);

        $bookingId = (int) $response->json('id');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'business_id' => $business->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'customer_name' => 'Client Success',
            'status' => 'confirmed',
        ]);
    }

    public function test_it_rejects_staff_from_another_tenant(): void
    {
        [$businessOne, $serviceOne] = $this->seedBusinessWithCatalog('studio-one', 'service-one@example.com');
        [, , $staffTwo] = $this->seedBusinessWithCatalog('studio-two', 'service-two@example.com');

        $response = $this->postJson("/api/b/{$businessOne->slug}/book", [
            'service_id' => $serviceOne->id,
            'staff_id' => $staffTwo->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('staff_id');
    }

    /**
     * @return array{0: Business, 1: Service, 2: Staff}
     */
    private function seedBusinessWithCatalog(string $slug, string $email): array
    {
        $business = Business::query()->create([
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'timezone' => 'America/Montreal',
            'email' => $email,
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
            'email' => $email,
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
