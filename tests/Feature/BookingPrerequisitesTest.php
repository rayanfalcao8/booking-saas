<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffTimeOff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingPrerequisitesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_public_booking_can_be_disabled_for_a_business(): void
    {
        [$business] = $this->seedCatalog();
        $business->update(['is_booking_enabled' => false]);

        $this->get("/b/{$business->slug}/book")->assertNotFound();
    }

    public function test_customer_email_is_required_for_a_public_booking(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
        ])->assertInvalid([
            'customer_email' => 'L’email client est obligatoire.',
        ]);
    }

    public function test_a_staff_member_cannot_be_booked_for_an_unassigned_service(): void
    {
        Notification::fake();
        [$business, $service, $staff] = $this->seedCatalog();
        $staff->services()->detach($service);

        $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
        ])->assertInvalid([
            'staff_id' => 'Ce prestataire ne propose pas le service sélectionné.',
        ]);
    }

    public function test_a_full_day_absence_removes_public_availability(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();

        TenantManager::set($business);
        StaffTimeOff::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'reason' => 'Vacances',
        ]);
        TenantManager::forget();

        $this->getJson("/api/b/{$business->slug}/availability?".http_build_query([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
        ]))->assertExactJson(['slots' => []]);
    }

    public function test_the_booking_horizon_is_enforced(): void
    {
        [$business, $service, $staff] = $this->seedCatalog();
        $business->update(['booking_max_advance_days' => 7]);

        $this->postJson("/api/b/{$business->slug}/book", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
        ])->assertInvalid([
            'date' => 'Cette date dépasse l’horizon de réservation autorisé.',
        ]);
    }

    /**
     * @return array{0: Business, 1: Service, 2: Staff}
     */
    private function seedCatalog(): array
    {
        $business = Business::query()->create([
            'name' => 'Readiness Studio',
            'slug' => 'readiness-studio',
            'timezone' => 'America/Montreal',
            'email' => 'business@example.com',
        ]);

        TenantManager::set($business);

        $service = Service::query()->create([
            'business_id' => $business->id,
            'name' => 'Consultation',
            'duration_min' => 30,
            'buffer_min' => 0,
            'is_active' => true,
        ]);
        $staff = Staff::query()->create([
            'business_id' => $business->id,
            'name' => 'Maya',
            'email' => 'maya@example.com',
            'is_active' => true,
        ]);
        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 2,
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
        ]);
        TenantManager::forget();

        return [$business->refresh(), $service, $staff];
    }
}
