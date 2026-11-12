# Camalaniugan Public Market Stall & Rental Management

A Laravel 12 application for public stall browsing, applicant registration, application review, stall assignment, lease monitoring, and payment receipts for the Municipality of Camalaniugan.

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+ and npm
- MySQL 8+ (or a compatible MariaDB release)

## Install

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
```

Set the MySQL database name, user, and password in `.env`, then create that database before migrating.

## Secure administrator setup

Set these values in the private local `.env` file before seeding:

```dotenv
ADMIN_NAME="Market Administrator"
ADMIN_EMAIL=market-admin@example.gov.ph
ADMIN_USERNAME=market-admin
ADMIN_PASSWORD=use-a-unique-password-of-at-least-12-characters
```

Set these values in `.env`, not `.env.example`. `ADMIN_USERNAME` must be a handle of 3–40 letters, numbers, dashes, or underscores; use `ADMIN_EMAIL` for the email address. If the username is left blank or mistakenly set to the email, the seeder derives a handle from the email prefix and appends `-admin`. `AdminUserSeeder` requires a unique password of at least 12 characters. It never stores a password in source control, leaves an existing administrator's credentials unchanged when rerun, and refuses to promote a non-administrator account. Do not commit `.env`. The administrator can sign in using the configured username or email.

## Database setup

```powershell
php artisan migrate --force
php artisan db:seed --force
```

`MarketReferenceSeeder` loads the five requested building categories and 208 source records. It preserves original stall numbers, individual rents and frequencies, printed occupant names, shared layout group labels, and uncertainty notes. It includes the explicitly labeled Building D rice/gilingan stalls 237–246 and Building E stalls 247–272 visible in the supplied images, in addition to the ranges listed in the brief. Rates follow each source section (including ₱20 daily-cash tickets, ₱400/₱450, and individual ₱600/₱1,000/₱1,200 monthly records). Records whose occupancy or handwriting is unclear are marked inactive and require administrator verification. Reference highlighting colors are not used to determine occupancy. Seeders use `firstOrCreate`, so re-running them does not overwrite later administrative edits.

Other supplied pages show meat, fish, eatery, and main-building sections without an unambiguous Building A–E mapping. They are not silently assigned to a building or marked available; verify their building mapping and enter them through the Buildings/Stalls administrator pages before publishing them.

Control numbers are allocated from a transaction-locked annual sequence and have a unique database constraint. The migration initializes the sequence from existing `YYYY-NNN` records.

## Run locally

```powershell
php artisan serve
```

Open `http://127.0.0.1:8000`. For frontend development, run `npm run dev` in a second terminal.

## Market operation

- Visitors can select Buildings A–E and inspect individual stall status and rental rate without an account.
- Applicants register with a username/email and must be 18 or older. They apply with their generated control number, business purpose, and optional PDF/JPG/PNG supporting document (up to 5 MB).
- Administrators sign in at `/admin/login`; applicant accounts cannot use this endpoint. The dedicated `/admin/dashboard` and transaction screens are protected by the administrator role.
- Administrator and applicant dashboards use live, role-scoped records; administrators can review the audit history at `/admin/activity-logs`.
- Pending applications reserve one stall-occupancy space. Administrators record `Under Review`, `Approved`, `Rejected`, or `Completed` decisions with a status history and the responsible user. Approval and contract creation run in one database transaction; each approved application links to its generated lease. Exclusive-stall competitors are closed after approval. Configured shared capacity is tracked through separate occupant/lease records.
- Lease contracts snapshot tenant details and contract terms, and can be previewed, printed, or downloaded as PDFs. Administrators may update, renew, or terminate contracts; renewals create a linked new contract without overwriting the old one. Contract and application changes are retained in the activity and status histories.
- Lease expiration and warning status are calculated from dates. Configure the warning threshold with `LEASE_WARNING_DAYS`.
- Record payments explicitly in the Payments module; active leases do not imply that rent was paid. Pending transactions can be approved or rejected with an audit entry and applicant notification; receipts are available only for paid transactions.
- Historical applications, leases, occupants, payments, and administrator actions are retained. Stalls with transaction history cannot be deleted.
- Database notifications are created for registration, applications, decisions, and lease expiration. Configure Laravel's scheduler to run daily for expiration notifications:

```powershell
php artisan schedule:run
```

On a production host, run the Laravel scheduler every minute using the host's normal scheduler/cron service.

## Validation

```powershell
php artisan test
npm run build
```
