<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Notifications\BusinessBookingCreated;
use App\Notifications\CustomerBookingConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateBookingActionNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_it_sends_business_and_customer_notifications_when_customer_email_is_present(): void
    {
        Notification::fake();

        [$business, $service, $staff] = $this->seedBookingContext();

        $booking = app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
            'customer_phone' => '555-0100',
            'notes' => 'Test booking',
        ]);

        Notification::assertSentOnDemand(BusinessBookingCreated::class, function (object $notification, array $channels, object $notifiable) use ($booking, $business): bool {
            return in_array('mail', $channels, true)
                && $notification->booking->is($booking)
                && ($notifiable->routes['mail'] ?? null) === $business->email;
        });

        Notification::assertSentOnDemand(CustomerBookingConfirmed::class, function (object $notification, array $channels, object $notifiable) use ($booking): bool {
            return in_array('mail', $channels, true)
                && $notification->booking->is($booking)
                && ($notifiable->routes['mail'] ?? null) === 'client@example.com';
        });
    }

    public function test_it_only_sends_business_notification_when_customer_email_is_null(): void
    {
        Notification::fake();

        [, $service, $staff] = $this->seedBookingContext();

        app(CreateBookingAction::class)->run([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00',
            'customer_name' => 'Client One',
            'customer_email' => null,
            'customer_phone' => '555-0100',
            'notes' => 'Test booking',
        ]);

        Notification::assertSentOnDemandTimes(BusinessBookingCreated::class, 1);
        Notification::assertSentOnDemandTimes(CustomerBookingConfirmed::class, 0);
    }

    private function seedBookingContext(): array
    {
        $business = Business::query()->create([
            'name' => 'Studio Demo',
            'slug' => 'studio-demo',
            'timezone' => 'America/Montreal',
            'email' => 'business@example.com',
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

        return [$business, $service, $staff];
    }
}
