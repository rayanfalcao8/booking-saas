# Booking SaaS (Laravel 12 + Filament v3)

Multi-tenant booking SaaS using `business_id` (single database).
Two Filament panels:
- App panel: /app (tenants)
- Admin panel: /admin (super admin)

## Setup
Use Node.js `20.19+` and npm `10+`.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

If you use `nvm`:

```bash
nvm use
```

To build frontend assets for production:

```bash
npm run build
```

If you switch from `npm run dev` to `npm run build`, make sure the stale Vite hot file is gone:

```bash
rm -f public/hot
```

## Demo Accounts
After `php artisan migrate:fresh --seed`:

- Super admin: `admin@reservix.test` / `password`
- Maison Kinks & Braids owner: `owners@maisonkinks.test` / `password`
- Northside Fade Club owner: `owners@northsidefade.test` / `password`
- Sparkle Move Services owner: `owners@sparklemove.test` / `password`

## Demo Public Booking Pages
- `/b/maison-kinks-braids/book`
- `/b/northside-fade-club/book`
- `/b/sparkle-move-services/book`

## Dev email notifications
For booking emails in development, use:
- `QUEUE_CONNECTION=sync`
- `MAIL_MAILER=log`

Then booking notification emails are written to `storage/logs/laravel.log`.

If you use Mailpit or Mailtrap, configure the mailer variables in `.env`.

For the complete local email/SMS setup and the staging-to-production gates, see [`RELEASE_PLAN.md`](RELEASE_PLAN.md).

## Troubleshooting Styles
If the public booking page appears unstyled:

1. Confirm you are using Node.js `20.19+` with `node -v`.
2. Run `npm install`.
3. Start assets with `npm run dev`, or build them with `npm run build`.
4. If you are using the built assets, confirm that `public/build/manifest.json` exists and delete any stale `public/hot`.
5. If you are using the dev server, keep `npm run dev` running and reload the booking page.

The public booking pages load their compiled CSS through Vite. If Vite is not running and no build artifacts exist yet, Tailwind styles will not be applied.
