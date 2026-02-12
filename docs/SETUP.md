# Setup (Local + Hostinger)

This repo will be a Laravel 11+ app deployed on a subdomain:

- `calendar.puertoaventurasliving.com`

## 1) Local development (recommended)

### Requirements

- PHP 8.2+
- Composer 2.x
- MySQL (or SQLite for local dev)

### Create the Laravel app in this repo

From the repo root:

```bash
# If the repo is empty except /docs, you can scaffold Laravel into it
composer create-project laravel/laravel .

cp .env.example .env
php artisan key:generate
```

### Install Filament (admin + organizer dashboards)

```bash
composer require filament/filament:"^3.0"
php artisan filament:install --panels
```

### Configure database

Edit `.env` for MySQL, then:

```bash
php artisan migrate
```

### Run locally

```bash
php artisan serve
```

## 2) Stripe

Create a Stripe account and set:

- `STRIPE_KEY`
- `STRIPE_SECRET`
- `STRIPE_WEBHOOK_SECRET`

Webhook endpoint (proposed):

- `https://calendar.puertoaventurasliving.com/stripe/webhook`

## 3) Hostinger deployment notes

Hostinger typically supports Laravel, but the key requirement is:

- Subdomain document root must point to Laravel `/public`

If Hostinger can't point the docroot correctly, a workaround is to place the Laravel app one level up and symlink or rewrite—**but docroot-to-/public is strongly preferred**.

Also required:

- SMTP credentials for verification emails
- Cron (optional but recommended)

