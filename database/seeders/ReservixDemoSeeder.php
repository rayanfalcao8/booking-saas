<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReservixDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBusiness(
            business: [
                'name' => 'Maison Kinks & Braids',
                'slug' => 'maison-kinks-braids',
                'timezone' => 'America/Toronto',
                'email' => 'hello@maisonkinks.test',
                'phone' => '+1 647 555 0148',
            ],
            owner: [
                'name' => 'Aminata Owner',
                'email' => 'owners@maisonkinks.test',
            ],
            services: [
                ['name' => 'Knotless Braids', 'duration_min' => 180, 'buffer_min' => 15],
                ['name' => 'Silk Press', 'duration_min' => 90, 'buffer_min' => 15],
                ['name' => 'Wash & Blow Dry', 'duration_min' => 60, 'buffer_min' => 10],
                ['name' => 'Cornrows', 'duration_min' => 75, 'buffer_min' => 10],
            ],
            staffDefinitions: [
                [
                    'name' => 'Awa',
                    'email' => 'awa@maisonkinks.test',
                    'schedules' => [
                        2 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        3 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        4 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        5 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        6 => [['08:30:00', '12:30:00'], ['13:30:00', '17:00:00']],
                    ],
                ],
                [
                    'name' => 'Naya',
                    'email' => 'naya@maisonkinks.test',
                    'schedules' => [
                        3 => [['10:00:00', '14:00:00'], ['15:00:00', '19:00:00']],
                        4 => [['10:00:00', '14:00:00'], ['15:00:00', '19:00:00']],
                        5 => [['10:00:00', '14:00:00'], ['15:00:00', '19:00:00']],
                        6 => [['09:00:00', '13:00:00'], ['14:00:00', '18:00:00']],
                        0 => [['11:00:00', '16:00:00']],
                    ],
                ],
            ],
            bookingTemplates: [
                ['staff' => 'Awa', 'service' => 'Silk Press', 'status' => 'completed', 'date' => now('America/Toronto')->subWeek()->next(Carbon::WEDNESDAY)->format('Y-m-d'), 'start' => '09:00:00', 'customer' => 'Jade Martin'],
                ['staff' => 'Awa', 'service' => 'Wash & Blow Dry', 'status' => 'no_show', 'date' => now('America/Toronto')->subDays(2)->format('Y-m-d'), 'start' => '13:00:00', 'customer' => 'Melissa Brown'],
                ['staff' => 'Naya', 'service' => 'Cornrows', 'status' => 'canceled', 'date' => now('America/Toronto')->addDays(3)->format('Y-m-d'), 'start' => '10:00:00', 'customer' => 'Nina Smith'],
                ['staff' => 'Awa', 'service' => 'Knotless Braids', 'status' => 'confirmed', 'date' => now('America/Toronto')->addWeek()->next(Carbon::TUESDAY)->format('Y-m-d'), 'start' => '09:00:00', 'customer' => 'Chloe Davis'],
                ['staff' => 'Naya', 'service' => 'Silk Press', 'status' => 'confirmed', 'date' => now('America/Toronto')->addWeek()->next(Carbon::WEDNESDAY)->format('Y-m-d'), 'start' => '15:00:00', 'customer' => 'Tara Wilson'],
            ],
        );

        $this->seedBusiness(
            business: [
                'name' => 'Northside Fade Club',
                'slug' => 'northside-fade-club',
                'timezone' => 'America/Toronto',
                'email' => 'bookings@northsidefade.test',
                'phone' => '+1 416 555 0117',
            ],
            owner: [
                'name' => 'Marcus Owner',
                'email' => 'owners@northsidefade.test',
            ],
            services: [
                ['name' => 'Skin Fade', 'duration_min' => 45, 'buffer_min' => 5],
                ['name' => 'Beard Sculpt', 'duration_min' => 30, 'buffer_min' => 5],
                ['name' => 'Cut & Beard Combo', 'duration_min' => 60, 'buffer_min' => 10],
                ['name' => 'Kids Cut', 'duration_min' => 30, 'buffer_min' => 5],
            ],
            staffDefinitions: [
                [
                    'name' => 'Marcus',
                    'email' => 'marcus@northsidefade.test',
                    'schedules' => [
                        1 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        2 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        3 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        4 => [['09:00:00', '12:00:00'], ['13:00:00', '18:00:00']],
                        5 => [['09:00:00', '12:00:00'], ['13:00:00', '19:00:00']],
                    ],
                ],
                [
                    'name' => 'Leo',
                    'email' => 'leo@northsidefade.test',
                    'schedules' => [
                        2 => [['11:00:00', '15:00:00'], ['16:00:00', '20:00:00']],
                        3 => [['11:00:00', '15:00:00'], ['16:00:00', '20:00:00']],
                        4 => [['11:00:00', '15:00:00'], ['16:00:00', '20:00:00']],
                        6 => [['09:00:00', '13:00:00'], ['14:00:00', '18:00:00']],
                        0 => [['10:00:00', '15:00:00']],
                    ],
                ],
            ],
            bookingTemplates: [
                ['staff' => 'Marcus', 'service' => 'Skin Fade', 'status' => 'completed', 'date' => now('America/Toronto')->subDays(8)->format('Y-m-d'), 'start' => '09:00:00', 'customer' => 'Daniel Cole'],
                ['staff' => 'Leo', 'service' => 'Beard Sculpt', 'status' => 'no_show', 'date' => now('America/Toronto')->subDays(3)->format('Y-m-d'), 'start' => '16:00:00', 'customer' => 'Isaac Reed'],
                ['staff' => 'Marcus', 'service' => 'Kids Cut', 'status' => 'canceled', 'date' => now('America/Toronto')->addDays(2)->format('Y-m-d'), 'start' => '13:00:00', 'customer' => 'Oliver Green'],
                ['staff' => 'Leo', 'service' => 'Cut & Beard Combo', 'status' => 'confirmed', 'date' => now('America/Toronto')->addWeek()->next(Carbon::THURSDAY)->format('Y-m-d'), 'start' => '16:00:00', 'customer' => 'Sam Hunter'],
                ['staff' => 'Marcus', 'service' => 'Skin Fade', 'status' => 'confirmed', 'date' => now('America/Toronto')->addWeek()->next(Carbon::TUESDAY)->format('Y-m-d'), 'start' => '10:00:00', 'customer' => 'Joel Banks'],
            ],
        );

        $this->seedBusiness(
            business: [
                'name' => 'Sparkle Move Services',
                'slug' => 'sparkle-move-services',
                'timezone' => 'America/Montreal',
                'email' => 'ops@sparklemove.test',
                'phone' => '+1 514 555 0192',
            ],
            owner: [
                'name' => 'Nadia Owner',
                'email' => 'owners@sparklemove.test',
            ],
            services: [
                ['name' => 'Studio Cleaning', 'duration_min' => 60, 'buffer_min' => 15],
                ['name' => 'Deep Cleaning', 'duration_min' => 120, 'buffer_min' => 20],
                ['name' => 'Local Delivery Run', 'duration_min' => 45, 'buffer_min' => 15],
                ['name' => 'Move-out Refresh', 'duration_min' => 180, 'buffer_min' => 20],
            ],
            staffDefinitions: [
                [
                    'name' => 'Chloe',
                    'email' => 'chloe@sparklemove.test',
                    'schedules' => [
                        1 => [['08:00:00', '12:00:00'], ['13:00:00', '17:00:00']],
                        2 => [['08:00:00', '12:00:00'], ['13:00:00', '17:00:00']],
                        3 => [['08:00:00', '12:00:00'], ['13:00:00', '17:00:00']],
                        4 => [['08:00:00', '12:00:00'], ['13:00:00', '17:00:00']],
                        5 => [['08:00:00', '12:00:00'], ['13:00:00', '16:00:00']],
                    ],
                ],
                [
                    'name' => 'Malik',
                    'email' => 'malik@sparklemove.test',
                    'schedules' => [
                        2 => [['09:00:00', '12:30:00'], ['13:30:00', '18:00:00']],
                        3 => [['09:00:00', '12:30:00'], ['13:30:00', '18:00:00']],
                        4 => [['09:00:00', '12:30:00'], ['13:30:00', '18:00:00']],
                        5 => [['09:00:00', '12:30:00'], ['13:30:00', '18:00:00']],
                        6 => [['09:00:00', '14:00:00']],
                    ],
                ],
            ],
            bookingTemplates: [
                ['staff' => 'Chloe', 'service' => 'Studio Cleaning', 'status' => 'completed', 'date' => now('America/Montreal')->subWeek()->next(Carbon::MONDAY)->format('Y-m-d'), 'start' => '08:00:00', 'customer' => 'Lucas Tremblay'],
                ['staff' => 'Malik', 'service' => 'Local Delivery Run', 'status' => 'no_show', 'date' => now('America/Montreal')->subDays(2)->format('Y-m-d'), 'start' => '09:00:00', 'customer' => 'Noah Parent'],
                ['staff' => 'Chloe', 'service' => 'Deep Cleaning', 'status' => 'canceled', 'date' => now('America/Montreal')->addDays(4)->format('Y-m-d'), 'start' => '13:00:00', 'customer' => 'Emma Roy'],
                ['staff' => 'Malik', 'service' => 'Move-out Refresh', 'status' => 'confirmed', 'date' => now('America/Montreal')->addWeek()->next(Carbon::WEDNESDAY)->format('Y-m-d'), 'start' => '13:30:00', 'customer' => 'Camille Bouchard'],
                ['staff' => 'Chloe', 'service' => 'Studio Cleaning', 'status' => 'confirmed', 'date' => now('America/Montreal')->addWeek()->next(Carbon::TUESDAY)->format('Y-m-d'), 'start' => '08:00:00', 'customer' => 'Sophie Leclerc'],
            ],
        );

        User::query()->create([
            'name' => 'Reservix Admin',
            'email' => 'admin@reservix.test',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'business_id' => null,
        ]);
    }

    private function seedBusiness(array $business, array $owner, array $services, array $staffDefinitions, array $bookingTemplates): void
    {
        $businessModel = Business::query()->create($business);

        User::query()->create([
            'name' => $owner['name'],
            'email' => $owner['email'],
            'password' => Hash::make('password'),
            'business_id' => $businessModel->id,
            'is_super_admin' => false,
        ]);

        $serviceModels = [];

        foreach ($services as $service) {
            $serviceModels[$service['name']] = Service::query()->create([
                'business_id' => $businessModel->id,
                'name' => $service['name'],
                'duration_min' => $service['duration_min'],
                'buffer_min' => $service['buffer_min'],
                'is_active' => true,
            ]);
        }

        $staffModels = [];

        foreach ($staffDefinitions as $staffDefinition) {
            $staff = Staff::query()->create([
                'business_id' => $businessModel->id,
                'name' => $staffDefinition['name'],
                'email' => $staffDefinition['email'],
                'is_active' => true,
            ]);

            $staffModels[$staffDefinition['name']] = $staff;

            foreach ($staffDefinition['schedules'] as $dayOfWeek => $periods) {
                foreach ($periods as [$startTime, $endTime]) {
                    StaffSchedule::query()->create([
                        'business_id' => $businessModel->id,
                        'staff_id' => $staff->id,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ]);
                }
            }
        }

        foreach ($bookingTemplates as $template) {
            $service = $serviceModels[$template['service']];
            $staff = $staffModels[$template['staff']];
            $startAt = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                sprintf('%s %s', $template['date'], $template['start']),
                $businessModel->timezone
            );
            $endAt = $startAt->copy()->addMinutes((int) $service->duration_min + (int) $service->buffer_min);

            Booking::query()->create([
                'business_id' => $businessModel->id,
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'date' => $template['date'],
                'start_time' => $startAt->format('H:i:s'),
                'end_time' => $endAt->format('H:i:s'),
                'customer_name' => $template['customer'],
                'customer_email' => Str::slug($template['customer']).'@example.com',
                'customer_phone' => '+1 555 '.random_int(100, 999).'-'.random_int(1000, 9999),
                'status' => $template['status'],
                'notes' => 'Réservation de démonstration générée par le seeder.',
                'cancellation_token' => $template['status'] === 'canceled' ? null : Str::random(48),
                'canceled_at' => $template['status'] === 'canceled' ? now() : null,
                'cancellation_expires_at' => $startAt,
            ]);
        }
    }
}
