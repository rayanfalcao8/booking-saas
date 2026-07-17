<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ModelBusinessIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_rejects_creating_a_staff_schedule_with_staff_from_another_business(): void
    {
        $tenant = Business::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'timezone' => 'America/Montreal',
        ]);

        $otherTenant = Business::query()->create([
            'name' => 'Tenant Two',
            'slug' => 'tenant-two',
            'timezone' => 'America/Montreal',
        ]);

        TenantManager::set($tenant);

        $foreignStaff = Staff::withoutGlobalScopes()->create([
            'business_id' => $otherTenant->id,
            'name' => 'Foreign Staff',
            'email' => 'foreign@example.com',
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Le prestataire sélectionné est invalide pour ce business.');

        StaffSchedule::query()->create([
            'staff_id' => $foreignStaff->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    public function test_it_rejects_creating_a_booking_with_a_service_from_another_business(): void
    {
        $tenant = Business::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'timezone' => 'America/Montreal',
        ]);

        $otherTenant = Business::query()->create([
            'name' => 'Tenant Two',
            'slug' => 'tenant-two',
            'timezone' => 'America/Montreal',
        ]);

        TenantManager::set($tenant);

        $localStaff = Staff::query()->create([
            'business_id' => $tenant->id,
            'name' => 'Local Staff',
            'email' => 'local@example.com',
            'is_active' => true,
        ]);

        $foreignService = Service::withoutGlobalScopes()->create([
            'business_id' => $otherTenant->id,
            'name' => 'Foreign Service',
            'duration_min' => 30,
            'buffer_min' => 0,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Le service sélectionné est invalide pour ce business.');

        Booking::query()->create([
            'service_id' => $foreignService->id,
            'staff_id' => $localStaff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'customer_name' => 'Client One',
            'status' => 'confirmed',
        ]);
    }

    public function test_it_rejects_overlapping_staff_schedules(): void
    {
        [$business, $staff] = $this->seedScheduleContext();

        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cet horaire chevauche une plage existante pour ce prestataire.');

        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 1,
            'start_time' => '11:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    public function test_it_allows_adjacent_staff_schedules(): void
    {
        [$business, $staff] = $this->seedScheduleContext();

        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 1,
            'start_time' => '12:00:00',
            'end_time' => '17:00:00',
        ]);

        $this->assertDatabaseCount('staff_schedules', 2);
    }

    public function test_it_rejects_a_staff_schedule_that_ends_before_it_starts(): void
    {
        [$business, $staff] = $this->seedScheduleContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La fin doit être après le début.');

        StaffSchedule::query()->create([
            'business_id' => $business->id,
            'staff_id' => $staff->id,
            'day_of_week' => 1,
            'start_time' => '17:00:00',
            'end_time' => '09:00:00',
        ]);
    }

    /**
     * @return array{0: Business, 1: Staff}
     */
    private function seedScheduleContext(): array
    {
        $business = Business::query()->create([
            'name' => 'Schedule Studio',
            'slug' => 'schedule-studio',
            'timezone' => 'America/Montreal',
        ]);

        TenantManager::set($business);

        $staff = Staff::query()->create([
            'business_id' => $business->id,
            'name' => 'Local Staff',
            'email' => 'local-schedule@example.com',
            'is_active' => true,
        ]);

        return [$business, $staff];
    }
}
