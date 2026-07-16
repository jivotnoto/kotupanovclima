# Kotupanovclima PHP

Plain PHP website for `kotupanovclima.eu`, prepared for SuperHosting shared hosting.

## What Is In The Repo

- `bootstrap.php`, `src/`, `views/` - PHP application code.
- `storage/data/` - live JSON data for products, promotions, company profile, and admin settings.
- `public/` - files that must be served from `/home/kotupano/public_html`.
- `public/uploads/` - uploaded product images from the live site.
- `.cpanel.yml` - optional SuperHosting/cPanel auto-deploy task file.
- `DEPLOY_SUPERHOSTING.md` - simple deployment guide.

## Production Layout

The live hosting account uses this layout:

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

`public/index.php` is the production entrypoint and expects the app at:

```php
$appBasePath = dirname(__DIR__) . '/kotupanovklima-app';
```

## Deployment

Use the instructions in [DEPLOY_SUPERHOSTING.md](DEPLOY_SUPERHOSTING.md).

Important: because `storage/data/` is committed, deploying this repo can overwrite live admin edits. If products, prices, promotions, admin settings, or uploaded image paths were changed from the admin panel, sync live files back into the repo and commit before deploying again.

## Requirements

- PHP 8.3
- Apache with `mod_rewrite`
- PHP extensions: `mbstring`, `fileinfo`
- Recommended PHP extension: `curl` for Cloudflare Turnstile verification (`allow_url_fopen=On` is used as a fallback)

## Admin Notes

- Product and promotion data is stored in `storage/data/`.
- Uploaded product images are stored in `public/uploads/` on GitHub and `/home/kotupano/public_html/uploads/` on live.
- Admin pages are protected by code login and optional IP allowlist.
- Logs live under `/home/kotupano/kotupanovklima-app/storage/logs/` and are not committed.
- The contact form uses Cloudflare Turnstile when both keys are configured; otherwise it falls back to a timed, one-time six-character graphical CAPTCHA.
- Contact submissions are additionally limited per client IP to eight attempts per 15-minute window.
- Keep production Turnstile keys in `storage/config/turnstile.php` outside Git. Start from `storage/config/turnstile.example.php`.
