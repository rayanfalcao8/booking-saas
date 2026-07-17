<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservixHealthCheckCommand extends Command
{
    protected $signature = 'reservix:health-check
        {--production : Require production-safe application settings}
        {--require-sms : Require a production SMS provider}';

    protected $description = 'Verify Reservix environment, database, mail, queue, and SMS readiness.';

    public function handle(): int
    {
        $checks = [
            ['Application', ...$this->applicationCheck()],
            ['Database', ...$this->databaseCheck()],
            ['Mail config', ...$this->mailCheck()],
            ['Queue config', ...$this->queueCheck()],
            ['SMS config', ...$this->smsCheck()],
        ];

        $this->table(
            ['Check', 'Status', 'Details'],
            array_map(
                fn (array $check): array => [
                    $check[0],
                    $check[1] ? 'PASS' : 'FAIL',
                    $check[2],
                ],
                $checks,
            ),
        );

        if (collect($checks)->every(fn (array $check): bool => $check[1])) {
            $this->info('Reservix health check passed.');

            return self::SUCCESS;
        }

        $this->error('Reservix health check failed.');

        return self::FAILURE;
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function applicationCheck(): array
    {
        $environment = app()->environment();

        if (! $this->option('production')) {
            return [true, sprintf('Laravel booted in the "%s" environment.', $environment)];
        }

        if ($environment !== 'production') {
            return [false, sprintf('APP_ENV must be "production"; current value is "%s".', $environment)];
        }

        if (config('app.debug')) {
            return [false, 'APP_DEBUG must be false in production.'];
        }

        $appUrl = (string) config('app.url');

        if (! str_starts_with($appUrl, 'https://')) {
            return [false, 'APP_URL must use HTTPS in production.'];
        }

        if (trim((string) config('app.key')) === '') {
            return [false, 'APP_KEY is not configured.'];
        }

        return [true, sprintf('Production settings are safe for %s.', $appUrl)];
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function databaseCheck(): array
    {
        try {
            DB::connection()->select('select 1');
        } catch (\Throwable $exception) {
            return [false, sprintf('Database connection failed: %s', $exception->getMessage())];
        }

        return [true, sprintf('Connected using the "%s" database connection.', config('database.default'))];
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function mailCheck(): array
    {
        $mailer = (string) config('mail.default');

        if (! in_array($mailer, ['smtp', 'failover'], true)) {
            return [false, sprintf('MAIL_MAILER must deliver through SMTP; current value is "%s".', $mailer)];
        }

        $requiredKeys = [
            'mail.from.address' => config('mail.from.address'),
            'mail.from.name' => config('mail.from.name'),
            'mail.mailers.smtp.host' => config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => config('mail.mailers.smtp.password'),
        ];

        $missingKeys = collect($requiredKeys)
            ->filter(function (mixed $value): bool {
                if (is_int($value)) {
                    return false;
                }

                if (is_string($value)) {
                    return trim($value) === '';
                }

                return true;
            })
            ->keys()
            ->values()
            ->all();

        if ($missingKeys !== []) {
            return [false, 'Missing mail configuration values: '.implode(', ', $missingKeys)];
        }

        if ($mailer === 'failover') {
            $failoverMailers = config('mail.mailers.failover.mailers', []);

            if (! in_array('smtp', $failoverMailers, true)) {
                return [false, 'Failover mailer must include the smtp mailer.'];
            }

            if ($this->option('production') && in_array('log', $failoverMailers, true)) {
                return [false, 'Production failover must not hide delivery failures with the log mailer.'];
            }
        }

        return [true, sprintf('Mailer "%s" is configured for SMTP delivery.', $mailer)];
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function queueCheck(): array
    {
        $defaultConnection = (string) config('queue.default');

        if ($defaultConnection === '') {
            return [false, 'QUEUE_CONNECTION is not configured.'];
        }

        $connectionConfig = config("queue.connections.{$defaultConnection}");

        if (! is_array($connectionConfig)) {
            return [false, sprintf('Queue connection "%s" is not defined.', $defaultConnection)];
        }

        if ($defaultConnection === 'database') {
            $jobsTable = (string) ($connectionConfig['table'] ?? 'jobs');

            if (! Schema::hasTable($jobsTable)) {
                return [false, sprintf('Queue connection "database" is configured, but the "%s" table is missing.', $jobsTable)];
            }
        }

        return [true, sprintf('Queue connection "%s" is configured.', $defaultConnection)];
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function smsCheck(): array
    {
        $driver = (string) config('services.sms.driver', 'off');

        if (! in_array($driver, ['off', 'log', 'twilio'], true)) {
            return [false, sprintf('Unsupported SMS driver "%s".', $driver)];
        }

        if (! $this->option('require-sms')) {
            return [true, sprintf('SMS driver is "%s" (not required for this check).', $driver)];
        }

        if ($driver !== 'twilio') {
            return [false, 'SMS_DRIVER must be "twilio" when SMS delivery is required.'];
        }

        $accountSid = trim((string) config('services.sms.twilio.account_sid'));
        $authToken = trim((string) config('services.sms.twilio.auth_token'));
        $from = trim((string) config('services.sms.twilio.from'));
        $messagingServiceSid = trim((string) config('services.sms.twilio.messaging_service_sid'));

        if ($accountSid === '' || $authToken === '' || ($from === '' && $messagingServiceSid === '')) {
            return [false, 'Twilio requires an account SID, auth token, and either a sender or Messaging Service SID.'];
        }

        return [true, 'Twilio is configured for SMS delivery.'];
    }
}
