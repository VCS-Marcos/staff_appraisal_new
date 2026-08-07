# Deployment Guide — Staff Appraisal Application

Target: shared cPanel-style hosting. Three paths are covered below depending on what
your host gives you access to — pick the one that matches, skip the rest.

## 0. Before you start: confirm what your host actually gives you

| You have | Path |
|---|---|
| SSH access | **Path A** — fastest, cleanest, recommended |
| No SSH, but cPanel's "Setup Node.js/PHP App" + Git integration | **Path B** |
| Neither (plain FTP-only cPanel) | **Path C** — works, but more manual steps |

If you're not sure, check your hosting control panel for an "SSH Access" or "Terminal"
option, or ask your host's support. This matters a lot — Path A takes ~10 minutes,
Path C takes considerably longer and needs re-doing by hand on every update.

## 1. Requirements

- PHP 8.2+ with extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`,
  `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (most cPanel PHP builds already have these)
- MySQL 8 or MariaDB 10.4+
- Composer (only needed if you have SSH — see Path A/B)
- An SMTP provider for outbound mail (Mailgun, SendGrid, Postmark, etc.) — cPanel's
  built-in mail is frequently flagged as spam by receiving servers, so don't rely on it
  for the appraisal notifications this app sends

## 2. Build the app for production

These steps produce the artifact you'll upload/deploy. Run them wherever Composer
and Node are available — your machine, a CI runner, or the server itself if you have SSH.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

`--no-dev` strips testing/dev-only packages (phpunit, laravel/pail, etc.) — don't run
this against your local dev copy if you plan to keep developing there; use a separate
clone or a CI step instead.

## 3. Environment configuration

Copy `.env.production.example` to `.env` (on the server, not in the repo) and fill in:

- `APP_KEY` — generate with `php artisan key:generate --force` (once, never regenerate
  on an app with existing sessions/encrypted data — it'll invalidate them)
- `APP_URL` — your real domain, `https://`
- `DB_*` — cPanel MySQL databases are usually prefixed with your cPanel username,
  e.g. `cpaneluser_staff_appraisal`; same for the DB user
- `MAIL_*` — your SMTP provider's credentials
- `APP_DEBUG=false` — **do not deploy with this `true`**; it leaks stack traces,
  file paths, and env values to anyone who triggers an error

`QUEUE_CONNECTION=sync` is intentional — this app sends notifications synchronously
(no queue worker needed, which shared hosting usually can't run persistently anyway).

## 4. Database

### Path A/B (SSH or Git+SSH available)

```bash
php artisan migrate --force
php artisan db:seed --force   # optional: creates one admin/reviewer/employee test account each — see database/seeders/DatabaseSeeder.php. Skip this in a real go-live and create your real admin account manually instead (step 6).
```

### Path C (FTP only, no artisan access)

Import `database/schema.sql` via phpMyAdmin (cPanel → phpMyAdmin → your database →
Import). This creates every table `migrate` would have, including Laravel's own
(`sessions`, `cache`, `jobs`, etc.). **After importing this way, never run
`php artisan migrate`** on that database unless you also manually populate the
`migrations` table — otherwise Laravel will try to re-run every migration from
scratch. If you get artisan access later, prefer switching to Path A/B properly.

## 5. Deploy the files

### Path A (SSH)

```bash
git clone <your-repo-url> staff-appraisal && cd staff-appraisal
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.production.example .env   # then edit it, see step 3
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Point the domain's document root at `<project>/public`. This is the important part —
if you can't set the document root there, use the `.htaccess.root-fallback` file
instead (rename it to `.htaccess`, place it next to the `public/` folder at whatever
level your host treats as the document root).

### Path B (cPanel Git + Setup PHP App)

Same commands as Path A, but run through cPanel's "Setup Node.js/PHP App" terminal or
its deploy hook if it has one. cPanel's PHP App tool usually lets you set the
"Application Root" to the project folder and its own document root handling takes
care of routing — check your host's specific instructions for this feature.

### Path C (FTP only)

1. Run step 2's build commands **locally** so `vendor/` and `public/build/` are
   fully populated — FTP-only means nothing gets generated on the server.
2. Upload the entire project folder via FTP.
3. Point the domain's document root at `<project>/public` if your host allows it;
   otherwise use `.htaccess.root-fallback` (rename to `.htaccess`) at the level FTP
   drops you into.
4. Create `.env` by uploading a filled-in copy of `.env.production.example` — there's
   no CLI to run `key:generate`, so generate a key locally first:
   ```bash
   php artisan key:generate --show
   ```
   and paste the `base64:...` value into the uploaded `.env`.
5. Import `database/schema.sql` via phpMyAdmin (step 4, Path C).
6. Skip `config:cache`/`route:cache`/`view:cache` — those need artisan. The app runs
   fine without them, just slightly slower per request.

## 6. Create your first real admin account

Don't ship the seeded demo accounts (`admin@school.test` / `password` etc.) to a real
deployment — they're for local development only.

- **Path A/B:** `php artisan tinker` and create a user manually, or temporarily add a
  one-off admin-creation route/command, use it once, then remove it.
- **Path C:** insert directly via phpMyAdmin. The `password` column needs a bcrypt
  hash — generate one locally with `php artisan tinker` → `Hash::make('your-password')`
  — never store a plain-text password in that column.

Set `role = 'admin'` on that first user. From then on, all other accounts are created
through the app's own Admin → Users screen.

## 7. Scheduled reminders (cron)

The app has one scheduled task — `appraisals:remind-signoff`, which emails whoever
hasn't signed an appraisal pending sign-off (runs daily at 08:00, see
`routes/console.php`). This only fires if something calls Laravel's scheduler
periodically.

In cPanel → Cron Jobs, add (every 5–15 minutes is standard):

```
*/15 * * * * cd /home/youruser/staff-appraisal && php artisan schedule:run >> /dev/null 2>&1
```

Adjust the path to wherever the project actually lives on the server. Without this
cron entry, the reminder feature silently does nothing — it's not optional.

## 8. HTTPS

Enable AutoSSL / Let's Encrypt in cPanel (usually free, one click under SSL/TLS
Status). The app forces `https://` on generated URLs automatically in production
(`AppServiceProvider`) once `APP_ENV=production` — so get the certificate issued
before go-live, or links in emails will point to `https://` URLs that don't
resolve yet.

## 9. File permissions (Path A/B/C)

`storage/` and `bootstrap/cache/` need to be writable by the web server process:

```bash
chmod -R 775 storage bootstrap/cache
```

If your host runs PHP under a different user than your FTP/SSH user, you may need
`chown` instead/as well — ask your host if writes fail with permission errors.

## 10. Post-deploy checklist

- [ ] Visit the site over `https://` — confirm no mixed-content warnings, login page loads
- [ ] Log in as your real admin account (not a seeded demo account)
- [ ] Create a cycle, create/open one test appraisal, walk it through to completion
      (this exercises DB writes, notifications, and PDF generation in one pass)
- [ ] Confirm a real email arrives (not just logged) — check spam folder the first time
- [ ] Download a PDF and confirm it renders correctly (dompdf needs the `gd` extension;
      if the PDF download 500s, that's the first thing to check)
- [ ] Confirm the cron entry is actually running: `php artisan schedule:list` (Path A/B)
      or wait for the next 15-minute mark and check `storage/logs/laravel.log`
- [ ] Confirm `APP_DEBUG=false` — trigger a deliberate error (e.g. visit a nonexistent
      route) and make sure you see a generic error page, not a stack trace
- [ ] Set up a backup: cPanel's built-in backup tool, or a cron `mysqldump`, on a
      daily schedule — this holds real HR data once live

## 11. Redeploying updates

Path A/B, after `git pull`:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Path C: rebuild locally, re-upload changed files via FTP, manually apply any new
migrations' SQL changes via phpMyAdmin (there's no `migrate` to run them for you —
this is the main ongoing cost of the FTP-only path).
