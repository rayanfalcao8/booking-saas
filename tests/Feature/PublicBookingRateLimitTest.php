<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rate_limits_public_booking_attempts_per_business_and_ip(): void
    {
        $business = Business::query()->create([
            'name' => 'Rate Limited Studio',
            'slug' => 'rate-limited-studio',
            'timezone' => 'America/Montreal',
        ]);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->postJson("/api/b/{$business->slug}/book", [])
                ->assertUnprocessable();
        }

        $this->postJson("/api/b/{$business->slug}/book", [])
            ->assertStatus(429);
    }
}
