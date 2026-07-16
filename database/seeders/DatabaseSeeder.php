<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ReservixDemoSeeder::class,
        ]);

        if ($this->command) {
            $this->command->info('Reservix demo data created.');
            $this->command->line('Super admin: admin@reservix.test / password');
            $this->command->line('- owners@maisonkinks.test / password');
            $this->command->line('- owners@northsidefade.test / password');
            $this->command->line('- owners@sparklemove.test / password');
        }
    }
}
