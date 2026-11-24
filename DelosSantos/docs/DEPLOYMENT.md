# Puihaha Alwaysdata deployment guide

This guide prepares the local project for `https://puihaha.alwaysdata.net`. It does not deploy anything and never includes production secrets. Complete the local regression and manual QA checklist before uploading a package.

## Target layout

- Application URL: `https://puihaha.alwaysdata.net`
- PHP: 8.3
- Database: MariaDB/MySQL
- Database host: `mysql-puihaha.alwaysdata.net`
- Application directory: `/www/puihaha-app/`
- Web root/document root: `/www/puihaha-app/public/`
- Queue mode: `sync` on the free plan because there is no permanent queue worker.
- Private documents: stored under the application’s private local disk, never under `public/storage`.

## Before uploading

1. Export a fresh phpMyAdmin Custom backup of the local `tw31_tasks` database.
2. Copy the project folder to a separate backup location.
3. Confirm the local checks pass:

   ```powershell
   php artisan test --do-not-cache-result
   php artisan route:list
   npm run build
   git diff --check
   ```

4. Review the staged-file list. Do not upload or commit `.env`, SQL exports, ZIP archives, reset tokens, passwords, API keys, private documents, or local cache files.

## Build a clean local package

Run this PowerShell script from a trusted local machine. It creates `C:\deploy\puihaha` without changing the project and excludes local secrets, development dependencies, tests, documentation, database exports, private local uploads, Git metadata, and caches. Composer dependencies should be installed on the server with `--no-dev`.

```powershell
$source = 'C:\xampp\htdocs\Hea11_3Y1T\DelosSantos'
$target = 'C:\deploy\puihaha'

New-Item -ItemType Directory -Force -Path $target | Out-Null

robocopy $source $target /E `
    /XD "$source\.git" "$source\node_modules" "$source\vendor" "$source\tests" "$source\docs" `
        "$source\storage\app\private" "$source\storage\framework\cache" `
        "$source\storage\framework\sessions" "$source\storage\framework\views" `
        "$source\storage\logs" "$source\bootstrap\cache" `
    /XF '.env' '.env.*' '*.sql' '*.zip' '*.log' `
    /R:2 /W:2 /NFL /NDL

if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}

Write-Host "Clean deployment package created at $target"
```

The server must have writable `storage`, `bootstrap/cache`, and the private document directory. If existing private documents must be migrated, transfer them separately through a protected administrative process; do not place them in the public web root or in the clean package by accident.

## Alwaysdata setup sequence

1. Create the Alwaysdata web application for PHP 8.3 and point its document root to `/www/puihaha-app/public/`.
2. Create the MariaDB database and record the host `mysql-puihaha.alwaysdata.net`, database name, username, and password in Alwaysdata’s private environment configuration. Never place those values in Git.
3. Upload the clean package into `/www/puihaha-app/` and install production dependencies:

   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction
   ```

4. Create the server-side `.env` from the placeholder template, then set real values privately:

   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://puihaha.alwaysdata.net`
   - a real, stable `APP_KEY`
   - MariaDB connection values for `mysql-puihaha.alwaysdata.net`
   - `SESSION_DRIVER=database`
   - `SESSION_SECURE_COOKIE=true`
   - `CACHE_STORE=database`
   - `QUEUE_CONNECTION=sync`
   - `TRUSTED_PROXIES=*` only when Alwaysdata’s reverse proxy is confirmed to be the direct proxy for this application
   - real mail, Maps, and OpenWeather values in the server environment only

5. Ensure the database exists and run the additive schema setup:

   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

   Do not use `migrate:fresh`, `db:wipe`, `reset`, or a destructive import on the production database.

6. Cache configuration and routes only after `.env` is complete and verified:

   ```bash
   php artisan optimize
   ```

7. Confirm the web server serves only `/www/puihaha-app/public/`, not the project root. Do not create a public storage link for member supporting documents.

## Email and SMTP

The local fallback is `MAIL_MAILER=log`. For Alwaysdata, configure an SMTP provider accepted by the account and set the host, port, encryption, username, password, sender address, and sender name privately. Test:

- member email confirmation;
- administrator email confirmation;
- member and administrator password reset with the same email when applicable;
- email re-verification after a member email change;
- request, payment, task, and approval notifications.

The free plan does not provide a permanent queue worker for this application, so keep `QUEUE_CONNECTION=sync`. If a paid or scheduled worker is added later, switch deliberately after testing queued delivery and after-commit behavior.

## Google Maps and weather

- Restrict the browser Maps key by HTTPS referrer to `https://puihaha.alwaysdata.net/*` and the exact production domain variants that Alwaysdata uses.
- Keep the OpenWeather key server-side only. Never put it in Blade, JavaScript, public assets, a screenshot, or a repository file.
- Confirm member registration, profile updates, and payer checkout save coordinates when a map pin is placed or an address is successfully geocoded.
- Confirm the weather cards use saved coordinates and fall back safely when coordinates or the provider key are unavailable.

## Production smoke test

After the site is available, check the following in a private browser window:

1. `/login/member` and `/login/admin` load over HTTPS with no mixed-content warnings.
2. Member registration, email confirmation, administrator approval, and both role login paths work.
3. Forgot-password links preserve the selected role and do not cross into the other portal.
4. `/tasks`, `/my-tasks`, membership pages, payments, receipts, and notifications remain member-isolated.
5. Administrator dashboard, member documents, approvals, transactions, reports, and activity logs remain administrator-only.
6. A stale-session or expired-form request returns to the member login screen with a helpful message.
7. Error responses for 404, 419, 429, and 500 show the Puihaha pages without debug traces.
8. Mobile navigation, theme switch, receipt print output, and private document access behave correctly.
