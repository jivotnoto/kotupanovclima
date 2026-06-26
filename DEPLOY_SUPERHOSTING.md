# SuperHosting Deployment

This project can be deployed to SuperHosting shared hosting in two ways:

- addon domain with document root pointed to this repo's `public/` directory
- main domain with fixed document root `public_html`

This guide covers the **main domain** case for:

- canonical domain: `kotupanovclima.eu`
- secondary redirect domain: `kotupanovklima.bg`
- document root: `/home/kotupano/public_html`

## Recommended production layout

Use this structure in the hosting account:

```text
/home/kotupano/
  public_html/
    index.php
    .htaccess
    assets/
    images/
    uploads/
  kotupanovklima-app/
    bootstrap.php
    src/
    views/
    storage/
```

## Domain setup

`kotupanovclima.eu` should be the primary/canonical domain for the hosting account.

`kotupanovklima.bg` must be configured in the hosting control panel as an Alias/Parked domain for the same document root, or as a panel-level permanent redirect to `https://kotupanovclima.eu`. If `http://kotupanovklima.bg/` shows `/cgi-sys/defaultwebpage.cgi`, Apache is not routing that domain to this account yet and the project `.htaccess` redirect cannot run.

When `kotupanovklima.bg` reaches this project document root, `public/.htaccess` redirects all `.bg` requests with 301 to the matching path on `https://kotupanovclima.eu`.

## What goes where

### In `/home/kotupano/public_html/`

Copy:

- `deploy/superhosting-main-domain/public_html/index.php`
- `deploy/superhosting-main-domain/public_html/.htaccess`
- everything from local `public/assets/`
- everything from local `public/images/`
- `public/uploads/` directory

### In `/home/kotupano/kotupanovklima-app/`

Copy:

- `bootstrap.php`
- `src/`
- `views/`
- `storage/`

## Important path note

The production entrypoint at `public_html/index.php` expects the app code here:

```php
$appBasePath = dirname(__DIR__) . '/kotupanovklima-app';
```

If you choose another folder name, edit only that one line in:

- `public_html/index.php`

## Required writable directories

Make sure these directories exist and are writable by PHP:

- `/home/kotupano/public_html/uploads`
- `/home/kotupano/kotupanovklima-app/storage/logs`
- `/home/kotupano/kotupanovklima-app/storage/data`

## PHP version

Use PHP `8.3`.

## Suggested PHP settings

- `memory_limit = 256M`
- `upload_max_filesize = 8M`
- `post_max_size = 16M`
- `max_execution_time = 60`
- `max_input_time = 180`
- `max_input_vars = 2000`
- `display_errors = Off`

## Deploy checklist

1. Upload the `public_html` files.
2. Upload the `kotupanovklima-app` files above `public_html`.
3. Confirm `storage/data/admin-settings.json` and the other JSON files are present.
4. Open `https://kotupanovclima.eu/`.
5. Open `https://kotupanovclima.eu/admin/login`.
6. Confirm `https://kotupanovklima.bg/` redirects to `https://kotupanovclima.eu/`.
6. Test image loading and admin login.

## Admin allowlist note

On SuperHosting the visible client IP will likely be your real remote IP, not the local Docker bridge IP.

After deployment:

- review `storage/data/admin-settings.json`
- replace local Docker allowlist entries if needed
- keep only the real IPs or CIDR ranges you want
