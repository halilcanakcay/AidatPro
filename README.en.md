# AidatPro

AidatPro is a free and open-source dues management panel for apartment buildings, residential sites and shared living communities. It is built with Laravel, Vite and Tabler.

Demo: [https://aidat.halilcan.dev](https://aidat.halilcan.dev)

Demo login:

```text
E-mail: skulloger@gmail.com
Password: asdasdasd
```

Languages: [Türkçe](README.tr.md) | [English](README.en.md)

## Purpose

AidatPro gives managers a single web panel for apartments, residents, dues, payments, expenses, announcements, reports and bank transactions. It is released under the MIT License so anyone can use it, adapt it and improve it for free.

## Features

- Multiple sites, blocks, apartments and resident records
- Dues accrual, payment collection, partial payments and debt tracking
- Dashboard summaries for income, expenses, collections and open balances
- PDF receipts and management reports
- Filters by site, block, year, month and payment source
- Announcement preparation, e-mail sending and WhatsApp Web handoff
- Time-limited owner report links
- Authorized staff accounts
- VakifBank transaction monitoring screens, manual matching and scheduler-ready integration structure
- Telegram, mail and protected package download settings

## Technology

- PHP 8.3+
- Laravel 13
- Vite 8
- Tabler UI
- MySQL/MariaDB or SQLite
- PDF output through Dompdf

## Quick Start

Read [INSTALLATION.md](INSTALLATION.md) for the full setup guide.

Bundled demo SQLite database:

```text
database/aidatpro.sqlite
```

Example administrator account:

```text
E-mail: skulloger@gmail.com
Password: asdasdasd
```

Change the administrator password immediately after first login and remove demo data before production use.

## Development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

Run the local development stack:

```bash
composer run dev
```

## VakifBank Integration

The user interface, settings, manual matching workflow and scheduler structure are included. Live account movement import requires VakifBank Online Account Movements access for the organization and valid SOAP service credentials. The demo package does not connect to a real bank account.

## Contributing

Bug reports, feature proposals and pull requests are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before starting.

## Security

Do not publish real personal data, bank details, production `.env` files or secret keys. See [SECURITY.md](SECURITY.md).

## License

AidatPro is released under the MIT License. See [LICENSE](LICENSE).
