# Booking SaaS (Laravel 12 + Filament v3)

Multi-tenant booking SaaS using `business_id` (single database).
Two Filament panels:
- App panel: /app (tenants)
- Admin panel: /admin (super admin)

## Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

Dev Seeder
Creates:
Super admin: admin@demo.com
 / password
Tenant user: user@demo.com
 / password
