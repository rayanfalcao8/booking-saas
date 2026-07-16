<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservixHealthCheckCommand extends Command
{
    protected $signature = 'reservix:health-check';

    protected $description = 'Verify staging readiness for app boot, database, mail, and queue configuration.';

    public function handle(): int
    {
        $checks = [
            ['App boot', true, sprintf('Laravel booted in the "%s" environment.', app()->environment())],
            ['Database', ...$this->databaseCheck()],
            ['Mail config', ...$this->mailCheck()],
            ['Queue config', ...$this->queueCheck()],
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
            $this->info('Reservix staging health check passed.');

            return self::SUCCESS;
        }

        $this->error('Reservix staging health check failed.');

        return self::FAILURE;
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
        $requiredKeys = [
            'mail.default' => config('mail.default'),
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

        $failoverMailers = config('mail.mailers.failover.mailers', []);

        if (! in_array('smtp', $failoverMailers, true) || ! in_array('log', $failoverMailers, true)) {
            return [false, 'Failover mailer must include both smtp and log mailers.'];
        }

        return [true, sprintf('Mailer "%s" is configured with SMTP failover.', config('mail.default'))];
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
}
