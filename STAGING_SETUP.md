# Reservix Staging Setup

This document prepares Reservix for realistic staging validation without adding new product scope.

## Objectives

- Run the current MVP with production-like environment flags.
- Seed realistic demo tenants and bookings.
- Deliver booking emails through a safe SMTP inbox such as Mailtrap.
- Verify application boot, database, mail, and queue readiness before manual QA.

## Required Environment Variables

Use these values as the baseline for staging:

```ini
APP_NAME=Reservix
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reservix_staging
DB_USERNAME=reservix
DB_PASSWORD=change-me

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=change-me
MAIL_PASSWORD=change-me
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Reservix"
```

Notes:

- `APP_ENV` must be `staging`.
- `APP_DEBUG` must be `false`.
- `APP_URL` must match the real staging domain because booking confirmation and cancellation links are generated from it.
- `QUEUE_CONNECTION=database` matches the current app defaults and keeps staging simple.
- This app reads `MAIL_SCHEME`; do not rely on `MAIL_ENCRYPTION`.

## Mailtrap Example

For Mailtrap SMTP, copy the inbox credentials into:

- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_SCHEME`

Recommended Mailtrap values:

```ini
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_FROM_ADDRESS=no-reply@staging.example.com
MAIL_FROM_NAME="Reservix Staging"
```

Any other SMTP provider is acceptable if it supports Laravel SMTP delivery and exposes the same `MAIL_*` values.

## Deployment Steps

1. Provision the staging database and make sure the app can connect with the `DB_*` credentials.
2. Copy the application code to staging and install dependencies:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

3. Create the staging environment file and set the required variables from the block above.
4. Generate an app key if the environment does not already have one:

```bash
php artisan key:generate --force
```

5. Run migrations:

```bash
php artisan migrate --force
```

6. Seed realistic demo data:

```bash
php artisan db:seed --force
```

7. Cache configuration for a production-like boot path:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

8. Start a queue worker because staging should process queued jobs and future async work reliably:

```bash
php artisan queue:work database --queue=default --tries=3 --timeout=90
```

9. Run the staging readiness checks:

```bash
php artisan reservix:health-check
php artisan test --compact tests/Feature/Console/Commands/ReservixHealthCheckCommandTest.php
php artisan test --compact tests/Feature/HealthCheckCommandTest.php
```

10. Confirm the HTTP boot probe responds successfully:

```text
GET /up
```

## Demo Accounts

The default seeder creates:

- Super admin: `admin@reservix.test` / `password`
- Maison Kinks owner: `owners@maisonkinks.test` / `password`
- Northside Fade owner: `owners@northsidefade.test` / `password`
- Sparkle Move owner: `owners@sparklemove.test` / `password`

## Demo Tenants Seeded

The seeder creates 3 businesses with realistic catalog and booking data:

- `maison-kinks-braids`
- `northside-fade-club`
- `sparkle-move-services`

Seed expectations:

- 3 businesses
- 12 services
- 6 staff members
- 28 staff schedule windows
- 15 bookings across `confirmed`, `completed`, `no_show`, and `canceled`

## Transactional Emails Covered

Current staging scope covers the already-implemented emails:

- Customer booking confirmation
- Business new booking notification

Cancellation emails are not added in this staging pass. Manual QA should verify cancellation flow and the cancellation link inside the confirmation email instead.

## Automated Test Commands

Run the focused suite used for staging readiness:

```bash
php artisan test --compact tests/Feature/DatabaseSeederTest.php
php artisan test --compact tests/Feature/CreateBookingActionNotificationsTest.php
php artisan test --compact tests/Feature/BookingNotificationMailerTest.php
php artisan test --compact tests/Feature/PublicBookingCancellationTest.php
php artisan test --compact tests/Feature/PublicBookingConfirmationTest.php
php artisan test --compact tests/Feature/Api/BookEndpointTenantIsolationTest.php
php artisan test --compact tests/Feature/PublicBookingRequestValidationTest.php
php artisan test --compact tests/Feature/CreateBookingActionScheduleValidationTest.php
php artisan test --compact tests/Feature/UpdateBookingStatusActionTest.php
php artisan test --compact tests/Feature/Filament/CreateBookingPageTest.php
php artisan test --compact tests/Feature/Filament/EditBookingPageTest.php
php artisan test --compact tests/Feature/Console/Commands/ReservixHealthCheckCommandTest.php
php artisan test --compact tests/Feature/HealthCheckCommandTest.php
```

If you want a broader regression pass after the focused suite is green:

```bash
php artisan test --compact
```
