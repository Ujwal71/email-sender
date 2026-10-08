# Laravel Email Sender (internal tool)

Upload a CSV → compose → preview → confirm → send → see results. Nothing else.

## Files in this package (copy over a fresh Laravel project)

```
app/
  Http/Controllers/CampaignController.php   upload/store, show, preview, send, history
  Http/Controllers/DashboardController.php
  Http/Middleware/ToolBasicAuth.php          HTTP Basic login from .env
  Jobs/SendCampaignJob.php                   loops recipients, records sent/failed
  Mail/CampaignMail.php                      Laravel Mailable (SMTP)
  Models/EmailCampaign.php, EmailRecipient.php
  Services/RecipientImporter.php             CSV parsing + validation + de-duplication
config/emailsender.php
database/migrations/…email_campaigns, …email_recipients
resources/views/{layouts,campaigns,emails}/…
routes/web.php
.env.example.snippet   sample-recipients.csv
```

## Install (Laravel 11 or 12, PHP 8.2+, MySQL, Composer)

```bash
composer create-project laravel/laravel email-sender
cd email-sender

# copy this package's app/, config/, database/, resources/, routes/ into the project
# (overwrite routes/web.php), and delete resources/views/welcome.blade.php if you like

php artisan key:generate    # only if APP_KEY in .env is empty
```

1. Create the database: `CREATE DATABASE email_sender CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
2. Copy the values from `.env.example.snippet` into `.env` and fill in MySQL, SMTP and tool login.
3. Run:

```bash
php artisan migrate
php artisan serve
```

Open http://127.0.0.1:8000 and log in with `TOOL_USERNAME` / `TOOL_PASSWORD`.

**Test safely first:** point SMTP at Mailpit or Mailtrap and use `sample-recipients.csv`.

## Queue (optional)

Default is `QUEUE_CONNECTION=sync`: sending happens inside the request, which is fine for
tens or a few hundred recipients. For bigger lists:

```
QUEUE_CONNECTION=database
php artisan queue:work --timeout=3600     # keep running, e.g. under Supervisor
```
The campaign page auto-refreshes while status is "Sending". (The `jobs` table migration ships with Laravel.)

## Notes

* PHP's `upload_max_filesize` / `post_max_size` must allow the 5 MB attachment limit.
* Attachments are stored in `storage/app/private/attachments` (not web-accessible).
* Many SMTP providers rate-limit; failures are recorded per recipient with the SMTP error.
* Before deploying: set `APP_ENV=production`, `APP_DEBUG=false`, serve over HTTPS (Basic auth sends credentials on every request), then `php artisan config:cache`.
