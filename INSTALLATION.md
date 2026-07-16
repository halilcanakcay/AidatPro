# AidatPro Installation Guide

This guide explains how to run AidatPro in local development, shared hosting and VPS/server environments.

Demo: [https://aidat.halilcan.dev](https://aidat.halilcan.dev)

Türkçe kurulum rehberi: [KURULUM.md](KURULUM.md)

## Requirements

- PHP 8.3 or newer
- PHP extensions: BCMath, Ctype, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer and XML
- MySQL 8+, MariaDB 10.6+ or SQLite 3
- Apache `mod_rewrite` or equivalent Nginx routing
- Ability to create cron jobs
- A valid SSL certificate in production
- Composer 2, Node.js and npm for source-based development

Generated dependency folders such as `vendor` and `public/build` are not committed to the GitHub repository. Run `composer install` and `npm run build` on the server or locally. If you are using a ZIP release package that already includes those folders, you can start faster without Composer or Node.js.

## Directory Layout

The recommended document root is the `public` directory:

```text
/home/user/aidatpro
/home/user/aidatpro/public  -> domain document root
```

If shared hosting does not allow changing the document root to `public`, the root-level `index.php` and `.htaccess` files support running the application from the package root on Apache.

## Quick SQLite Setup

SQLite is suitable for small buildings, demos and quick tests.

1. Upload the files to the server.
2. Copy `.env.example` to `.env`.
3. Configure the main environment values:

```env
APP_NAME=AidatPro
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Europe/Istanbul

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/server/path/database/aidatpro.sqlite
```

4. Generate an application key:

```bash
php artisan key:generate
```

5. Make sure the SQLite database is writable:

```bash
chmod 664 database/aidatpro.sqlite
```

6. Prepare Laravel links and caches:

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
php artisan optimize:clear
php artisan optimize
```

## MySQL / MariaDB Setup

MySQL or MariaDB is recommended for medium and larger installations.

1. Create an empty database and user.
2. Configure the database values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aidatpro
DB_USERNAME=aidatpro
DB_PASSWORD=strong_database_password
```

3. Install with demo data:

```bash
php artisan migrate --seed --force
php artisan optimize
```

4. For an empty installation, do not use `--seed`:

```bash
php artisan migrate --force
php artisan optimize
```

## Default Demo Account

Seeded installations include this administrator account:

```text
E-mail: admin@aidat.local
Password: Admin123!
```

Change this password immediately after first login. Remove demo users, residents and financial records before production use.

## Mail Settings

Fill the `MAIL_*` values in `.env` with your SMTP provider details:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=mail@example.com
MAIL_PASSWORD=secret_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mail@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

After changing mail settings:

```bash
php artisan config:cache
```

## Scheduler

Add a cron entry that runs every minute:

```cron
* * * * * cd /absolute/project/path && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler is required for active bank integrations, planned jobs and queue-related maintenance tasks.

## Queue Worker

For simple installations, `QUEUE_CONNECTION=database` is enough. For longer mail or integration jobs, run a separate worker:

```bash
php artisan queue:work --tries=3 --timeout=120
```

Use Supervisor or your hosting panel's service manager to keep the worker running permanently.

## VakifBank Integration

AidatPro includes VakifBank transaction monitoring, settings and manual matching screens. To import real transactions:

- VakifBank Online Account Movements must be enabled for your organization account.
- Service URL, customer number, user details and other SOAP credentials provided to your organization must be configured in the panel.
- The demo package does not connect to a real bank account.
- Test in a low-risk environment before going live.

## File Permissions

Only grant write access where Laravel needs it:

```bash
chmod -R 775 storage bootstrap/cache
```

Do not make source code, configuration or application files writable by the web server.

## Security Checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` points to the real HTTPS address
- `.env` is not publicly accessible
- The `admin@aidat.local` password has been changed
- The production database password is strong and unique
- Real personal data is not published to GitHub or public demo environments
- Database and file backups are taken regularly
- SSL is active

## Development Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

Development server:

```bash
composer run dev
```

## Deployment Updates

After code changes, these commands are usually enough:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

## Troubleshooting

- Blank page or 500 error: check `storage/logs/laravel.log`.
- Missing styles: run `npm run build` and `php artisan optimize:clear`.
- Session issues after login: check `APP_URL`, `SESSION_DOMAIN` and `SESSION_SECURE_COOKIE`.
- SQLite write error: check the permissions and owner of `database/aidatpro.sqlite`.
- Mail is not delivered: verify SMTP values and your hosting provider's outbound port restrictions.
