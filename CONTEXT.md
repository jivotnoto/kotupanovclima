# Context

This file is the current working memory for the project.
It should be updated whenever architecture, deployment assumptions, security behavior, or unfinished work changes.

## Project overview

- Stack: plain PHP application without a framework
- Web server: Apache in Docker
- Runtime: PHP 8.3 Apache image
- Storage: JSON files in `storage/data`
- Admin auth: code-based login plus optional IP allowlist mode
- Theme: forced light/white UI palette

## Key paths

- Public entry: [public/index.php](public/index.php)
- Main app/router: [src/App.php](src/App.php)
- Auth and allowlist logic: [src/Auth.php](src/Auth.php)
- IP detection helpers: [src/helpers.php](src/helpers.php)
- Docker Apache vhost: [docker/apache/vhost.conf](docker/apache/vhost.conf)
- Admin settings view: [views/admin/settings.php](views/admin/settings.php)
- Admin product ordering UI: [views/admin/products-list.php](views/admin/products-list.php)

## Current admin catalog behavior

- Products are stored in `storage/data/seed-products.json`.
- Admin product lists for `Климатици` and `Термопомпи` include `Нагоре` and `Надолу` controls per entry.
- The reorder action is handled by `POST /admin/products/reorder` with CSRF validation.
- Reordering preserves the visible product order even when a move crosses series boundaries.

## Current product UI behavior

- Catalog product images link to their product detail pages.
- Product detail images open in a lightweight overlay controlled by `public/assets/site.js`.

## Current networking behavior

- Local access is currently through Docker port mapping: `localhost:3001 -> container:80`
- Because requests are hitting the container directly, Apache currently sees the Docker-side peer IP such as `172.21.0.1`
- No reverse proxy is currently injecting trusted `X-Forwarded-For`
- Result: admin allowlist checks must allow the container-visible IP or a matching CIDR
- Live domains `kotupanovklima.bg` and `kotupanovclima.eu` have trusted HTTPS certificates and `public/.htaccess` forces HTTP requests for both domains to HTTPS.

## Current SEO behavior

- `kotupanovklima.bg` is the canonical SEO domain.
- `kotupanovclima.eu` remains reachable, but public pages render canonical tags pointing to `https://kotupanovklima.bg`.
- Admin pages render `noindex, nofollow`.
- `robots.txt` and `sitemap.xml` are served dynamically by `src/App.php` as a fallback.
- Static host-specific `robots.txt` and `sitemap.xml` files are mapped through `.htaccess` for `kotupanovklima.bg` and `kotupanovclima.eu`.
- Public pages render Open Graph and Twitter Card tags; product pages use their product image as the social preview image when available.

## Current allowlist behavior

- Access modes:
  - `open`
  - `allowlist_only`
- Allowed entries support:
  - exact IPs like `192.168.1.50`
  - CIDR ranges like `192.168.1.0/24`
- Client IP resolution prefers proxy headers only when `REMOTE_ADDR` is treated as a trusted proxy
- Trusted proxies are recognized only from the optional `TRUSTED_PROXY_RANGES` environment variable
- Private Docker/proxy IPs are not trusted for `X-Forwarded-For` by default, so spoofed forwarded headers cannot bypass the admin allowlist

## Current logging behavior

- App access logs include request and IP context
- Security logs include admin login and allowlist denial events
- Apache access logs are configured to include:
  - `X-Forwarded-For`
  - `X-Real-IP`
- Note: Apache config changes require container rebuild/restart to take effect

## Open follow-up options

- Keep the current direct Docker exposure and allow container-visible IP/CIDR in admin settings
- Add a reverse proxy in front of the container and set `TRUSTED_PROXY_RANGES` before relying on forwarded client IP headers
- Add a small troubleshooting page or admin diagnostics block if repeated IP confusion continues

## Session guidance for future work

- Read this file and `GISTORY.md` before continuing project work
- Check recent lines in:
  - `storage/logs/access.log`
  - `storage/logs/security.log`
  - `storage/logs/apache-access.log`
- If admin access fails in local Docker, verify the detected IP shown on `/admin/settings` after login
- If Apache header logging seems missing, rebuild the container image before debugging further
