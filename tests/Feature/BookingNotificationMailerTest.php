<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Notifications\BusinessBookingCreated;
use App\Notifications\CustomerBookingConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingNotificationMailerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_booking_confirmation_uses_the_environment_mailer(): void
    {
        $booking = $this->makeBooking();

        $message = (new CustomerBookingConfirmed($booking))->toMail(new \stdClass);
        $notification = new CustomerBookingConfirmed($booking);

        $this->assertNull($message->mailer);
        $this->assertSame(3, $notification->tries);
        $this->assertSame([60, 300], $notification->backoff());
        $this->assertSame('mail.bookings.customer-confirmed', $message->markdown);
        $this->assertSame('Confirmation de votre réservation', $message->subject);
        $this->assertArrayHasKey('confirmationUrl', $message->viewData);
        $this->assertArrayHasKey('cancelUrl', $message->viewData);
        $this->assertStringContainsString("/b/{$booking->business->slug}/book/{$booking->id}/confirmation/{$booking->cancellation_token}", $message->viewData['confirmationUrl']);
        $this->assertStringContainsString("/b/{$booking->business->slug}/book/{$booking->id}/cancel/{$booking->cancellation_token}", $message->viewData['cancelUrl']);
    }

    public function test_business_booking_notification_uses_the_environment_mailer(): void
    {
        $booking = $this->makeBooking();

        $message = (new BusinessBookingCreated($booking))->toMail(new \stdClass);
        $notification = new BusinessBookingCreated($booking);

        $this->assertNull($message->mailer);
        $this->assertSame(3, $notification->tries);
        $this->assertSame([60, 300], $notification->backoff());
        $this->assertSame('mail.bookings.business-created', $message->markdown);
        $this->assertSame('Nouvelle réservation confirmée', $message->subject);
        $this->assertSame('Studio Demo', $message->viewData['businessName']);
        $this->assertSame('Cut', $message->viewData['serviceName']);
        $this->assertSame('Jane', $message->viewData['staffName']);
    }

    private function makeBooking(): Booking
    {
        $business = Business::query()->create([
            'name' => 'Studio Demo',
            'slug' => 'studio-demo',
            'timezone' => 'America/Montreal',
            'email' => 'business@example.com',
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

        return Booking::query()->create([
            'business_id' => $business->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'customer_name' => 'Client One',
            'customer_email' => 'client@example.com',
            'status' => 'confirmed',
            'cancellation_token' => str_repeat('a', 48),
        ]);
    }
}
