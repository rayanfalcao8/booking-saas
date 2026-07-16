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
        config()->set('mail.mailers.failover.mailers', ['smtp', 'log']);
        config()->set('queue.default', 'database');

        $this->artisan('reservix:health-check')
            ->expectsOutputToContain('PASS')
            ->expectsOutputToContain('Reservix staging health check passed.')
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
        config()->set('mail.mailers.failover.mailers', ['smtp', 'log']);
        config()->set('queue.default', 'database');

        $this->artisan('reservix:health-check')
            ->expectsOutputToContain('FAIL')
            ->assertFailed();
    }
}
