<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\CustomerBookingConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_twilio_driver_sends_the_confirmation_sms(): void
    {
        config()->set('services.sms', [
            'driver' => 'twilio',
            'twilio' => [
                'account_sid' => 'AC_TEST',
                'auth_token' => 'secret-token',
                'from' => '+14185550000',
                'messaging_service_sid' => null,
            ],
        ]);
        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM_TEST'], 201),
        ]);

        $booking = $this->makeBooking();
        $notifiable = (new AnonymousNotifiable)->route('sms', '+14185550123');

        app(SmsChannel::class)->send($notifiable, new CustomerBookingConfirmed($booking));

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/AC_TEST/Messages.json'
            && $request['To'] === '+14185550123'
            && $request['From'] === '+14185550000'
            && str_contains($request['Body'], 'rendez-vous confirmé chez Studio SMS'));
    }

    private function makeBooking(): Booking
    {
        $business = Business::query()->create([
            'name' => 'Studio SMS',
            'slug' => 'studio-sms',
            'timezone' => 'America/Montreal',
        ]);
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
            'is_active' => true,
        ]);

        return Booking::query()->create([
            'business_id' => $business->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => '2026-03-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'customer_name' => 'Client SMS',
            'customer_email' => 'client@example.com',
            'customer_phone' => '+14185550123',
            'status' => 'confirmed',
            'cancellation_token' => str_repeat('a', 48),
        ])->load(['business', 'service', 'staff']);
    }
}
