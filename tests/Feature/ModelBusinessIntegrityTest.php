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
}
