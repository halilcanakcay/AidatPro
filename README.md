# AidatPro

Free and open-source apartment, residential site and dues management panel built with Laravel, Vite and Tabler.

Demo: [https://aidat.halilcan.dev](https://aidat.halilcan.dev)

Languages: [Türkçe](README.tr.md) | [English](README.en.md)

## What It Does

AidatPro helps apartment and site managers track residents, flats, dues, payments, expenses, announcements, reports and bank transactions from a single web panel. It ships with an MIT license so communities, agencies and developers can use, modify and improve it freely.

## Main Features

- Multi-site, block, apartment and resident management
- Dues accrual, partial/full payment tracking and expense records
- Dashboard summaries for collections, debts, expenses and cash flow
- PDF receipts and management reports
- Announcement workflows with e-mail and WhatsApp Web handoff
- Time-limited owner report links
- Authorized staff accounts with role-like permissions
- VakifBank transaction monitoring screens, manual matching and scheduler-ready integration structure
- System settings for mail, Telegram and protected package downloads

## Quick Start

Detailed setup guides:

- Turkish: [KURULUM.md](KURULUM.md)
- English: [INSTALLATION.md](INSTALLATION.md)

Default demo database path:

```text
database/aidatpro.sqlite
```

Example admin account:

```text
E-mail: admin@aidat.local
Password: Admin123!
```

Change the administrator password immediately after first login and remove demo data before using the application in production.

## Repository Notes

The repository intentionally excludes generated dependency folders such as `vendor`, `node_modules` and `public/build`. Install dependencies locally or on the server by following the installation guide.

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening an issue or pull request.

## Security

Please do not publish secrets, real resident data, bank credentials or production `.env` files. See [SECURITY.md](SECURITY.md).

## License

AidatPro is released under the MIT License. See [LICENSE](LICENSE).
