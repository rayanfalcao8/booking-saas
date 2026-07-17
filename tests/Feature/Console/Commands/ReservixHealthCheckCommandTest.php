<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservixHealthCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_passes_when_database_mail_and_queue_configuration_are_ready(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.from.address', 'staging@reservix.test');
        config()->set('mail.from.name', 'Reservix Staging');
        config()->set('mail.mailers.smtp.host', 'sandbox.smtp.mailtrap.io');
        config()->set('mail.mailers.smtp.port', 2525);
        config()->set('mail.mailers.smtp.username', 'mailtrap-user');
        config()->set('mail.mailers.smtp.password', 'mailtrap-password');
        config()->set('queue.default', 'database');

        $this->artisan('reservix:health-check')
            ->expectsOutputToContain('PASS')
            ->expectsOutputToContain('Reservix health check passed.')
            ->assertSuccessful();
    }

    public function test_it_fails_when_required_mail_configuration_is_missing(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.from.address', 'staging@reservix.test');
        config()->set('mail.from.name', 'Reservix Staging');
        config()->set('mail.mailers.smtp.host', '');
        config()->set('mail.mailers.smtp.port', 2525);
        config()->set('mail.mailers.smtp.username', '');
        config()->set('mail.mailers.smtp.password', '');
        config()->set('queue.default', 'database');

        $this->artisan('reservix:health-check')
            ->expectsOutputToContain('FAIL')
            ->assertFailed();
    }

    public function test_it_fails_when_required_twilio_configuration_is_missing(): void
    {
        $this->configureDeliverableMail();
        config()->set('queue.default', 'database');
        config()->set('services.sms.driver', 'twilio');
        config()->set('services.sms.twilio.account_sid', '');
        config()->set('services.sms.twilio.auth_token', '');

        $this->artisan('reservix:health-check', ['--require-sms' => true])
            ->expectsOutputToContain('FAIL')
            ->assertFailed();
    }

    public function test_it_passes_when_required_twilio_configuration_is_complete(): void
    {
        $this->configureDeliverableMail();
        config()->set('queue.default', 'database');
        config()->set('services.sms.driver', 'twilio');
        config()->set('services.sms.twilio.account_sid', 'AC_TEST');
        config()->set('services.sms.twilio.auth_token', 'secret-token');
        config()->set('services.sms.twilio.messaging_service_sid', 'MG_TEST');

        $this->artisan('reservix:health-check', ['--require-sms' => true])
            ->expectsOutputToContain('Twilio is configured for SMS delivery.')
            ->assertSuccessful();
    }

    private function configureDeliverableMail(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.from.address', 'staging@reservix.test');
        config()->set('mail.from.name', 'Reservix Staging');
        config()->set('mail.mailers.smtp.host', 'sandbox.smtp.mailtrap.io');
        config()->set('mail.mailers.smtp.port', 2525);
        config()->set('mail.mailers.smtp.username', 'mailtrap-user');
        config()->set('mail.mailers.smtp.password', 'mailtrap-password');
    }
}
