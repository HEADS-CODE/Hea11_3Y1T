<p align="center">
    <img src="public/assets/logo/puihaha-logo-light-mode.svg" width="180" alt="Puihaha E-Learning Platform">
</p>

# Puihaha E-Learning & Membership Platform

Puihaha is a Laravel-based Membership Management and Transaction Processing System with an e-learning task workspace. Administrators manage member records, categories, levels, payments, transactions, tasks, extension requests, reports, notifications, and supporting-document reviews. Members manage their own portal, complete administrator-assigned tasks, submit eligible requests, pay membership fees, and view their own history and receipts.

The project follows the professor’s Laravel conventions: migrations and Eloquent relationships, Form Request validation, named routes, Blade CSRF/error directives, atomic database transactions, role-aware authentication, queued-compatible notifications, Google Maps coordinates, and server-side OpenWeather requests.

## Roles and access boundaries

- **Administrator:** manages members, categories, levels, tasks, approvals, transactions, payments, reports, activity logs, and private supporting documents.
- **Member:** sees only their own portal, tasks, membership information, payments, receipts, requests, notifications, and weather location.
- The same email may exist once per role, but the login URL selects the role. Member and administrator password-reset brokers and notifications remain isolated.
- Pending, unverified, disabled, inactive, and archived states are handled by the account-state screens rather than bypassing role authorization.
- Supporting documents stay on the private `member_private` disk and are served only through authenticated administrator policy checks. They are never exposed through `public/storage`.

## Main features

- Member CRUD, search, status management, archiving, and confirmed permanent deletion.
- Membership categories and levels with fees, validity, benefits, and member assignment.
- Membership application, renewal, level-change, category-change, and front-desk payment flows.
- One full payment per transaction, exact level-fee validation, transaction statuses, immutable email snapshots, payer snapshots, redacted receipts, and transaction history.
- Administrator-owned learning tasks, priority filters, member completion toggles, deadline-extension requests, notifications, and Insights charts.
- Member and administrator weather cards using saved coordinates or the configured safe fallback location.
- Google Maps pin capture for member addresses and payer details, with typed-address fallback when Maps is unavailable.
- Responsive purple/violet portal styling, light/dark themes, mobile navigation, print views, and themed error pages.

## Local XAMPP setup

1. Start Apache and MySQL in XAMPP.
2. Create or select the local MySQL database named `tw31_tasks`.
3. In this project directory, install dependencies and prepare the local environment:

   ```powershell
   composer install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. Set the local database values in `.env` for XAMPP (`mysql`, `127.0.0.1`, port `3306`, database `tw31_tasks`, and the local MySQL username). Never commit `.env`.
5. Set `INITIAL_ADMIN_EMAIL` and `INITIAL_ADMIN_PASSWORD` locally if the initial administrator should be seeded. The password is read only from `.env`; it is not stored in this README or in source code.
6. Run additive migrations and seed the defaults:

   ```powershell
   php artisan migrate
   php artisan db:seed
   ```

7. Build the frontend and start the local server:

   ```powershell
   npm install
   npm run build
   php artisan serve
   ```

   Open `http://127.0.0.1:8000/login/member` or `http://127.0.0.1:8000/login/admin`.

Before any migration against an existing database, export a phpMyAdmin **Custom** SQL backup and copy the project folder. Do not use `migrate:fresh`, `db:wipe`, or a destructive reset against the project database.

## Maps, weather, mail, and queues

The local `.env` may contain:

- `GOOGLE_MAPS_API_KEY` for the browser-restricted Maps JavaScript key.
- `OPENWEATHER_KEY` and `OPENWEATHER_BASE_URL` for the server-side weather request.
- `WEATHER_DEFAULT_LATITUDE` and `WEATHER_DEFAULT_LONGITUDE` for the safe fallback location.
- `MAIL_MAILER=log` for a no-cost local mail fallback, or Mailpit/SMTP values for actual delivery.
- `QUEUE_CONNECTION=sync` for environments without a queue worker. Database notifications remain immediately visible. Queue-compatible notifications still use Laravel’s after-commit pattern.

The OpenWeather key is never passed to Blade or browser JavaScript. If Maps or weather configuration is missing, typed addresses and the rest of the portal continue to work with a clear fallback state.

## Testing and verification

Run the full checks from the project directory:

```powershell
php artisan test --do-not-cache-result
php artisan route:list
npm run build
git diff --check
```

The `--do-not-cache-result` option avoids the known Windows permission warning from the local Pest vendor cache. Production dependency validation can be previewed without modifying the working installation with:

```powershell
composer validate --strict
composer install --no-dev --dry-run --no-interaction
```

## Deployment documentation

The local-first Alwaysdata preparation guide is in [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md). It covers PHP 8.3, MariaDB, the `puihaha.alwaysdata.net` URL, the `/www/puihaha-app/public/` web root, the `mysql-puihaha.alwaysdata.net` database host, SMTP choices, Google Maps referrer restrictions, HTTPS, and a safe clean-package script.

## Privacy and source-control rules

- Do not display or commit `.env`, passwords, API keys, reset tokens, private SQL exports, uploaded documents, or production database dumps.
- Use `.env.example` and `.env.production.example` only as placeholder templates.
- Do not place private documents in `public/storage` or generate public URLs for them.
- Keep financial history snapshots independent of later member profile edits.
- Review `git diff --cached --name-only` before every commit and stage only the intended `DelosSantos` project files.
