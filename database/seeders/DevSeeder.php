<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::query()->create([
            'name' => 'Demo Business',
            'slug' => 'demo-business',
            'timezone' => 'America/Montreal',
            'email' => 'demo@business.com',
        ]);

        User::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'business_id' => null,
        ]);

        User::query()->create([
            'name' => 'Tenant User',
            'email' => 'user@demo.com',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'business_id' => $business->id,
        ]);
    }
}
