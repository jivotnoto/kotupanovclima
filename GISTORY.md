# Gistory

This file is a lightweight project history log for local development.
It complements Git commits with short human-readable context about what changed and why.

## 2026-06-22

### Repo bootstrap

- Initialized a local Git repository for this project.
- Added `GISTORY.md` and `CONTEXT.md` to preserve working history and current context across sessions.

### Project state at bootstrap

- PHP application for `Котупановклима ЕООД`
- Public catalog and admin panel
- JSON-backed storage in `storage/data`
- Dockerized Apache + PHP runtime
- Logs written under `storage/logs`

### Recent work before repo init

- Added XFF-aware client IP context detection in [src/helpers.php](src/helpers.php).
- Added allowlist matching for exact IPs and CIDR ranges.
- Updated admin auth checks to use normalized allowlist matching.
- Expanded app/security logging with:
  - `clientIp`
  - `clientIpSource`
  - `remoteAddr`
  - `trustedProxy`
  - `xForwardedFor`
  - `xForwardedChain`
  - `xRealIp`
- Updated the admin settings screen to show the currently detected client IP and source.
- Updated Apache log format in the Docker vhost to include `X-Forwarded-For` and `X-Real-IP`.
- Switched the site theme to a consistent white/light palette and removed the dark-mode palette override.

### Runtime note

- In the current local Docker setup (`localhost:3001` via direct port mapping), the app sees `REMOTE_ADDR` as the Docker bridge peer, for example `172.21.0.1`.
- There is currently no upstream reverse proxy adding `X-Forwarded-For`, so allowlist entries must match the container-visible IP or CIDR unless a reverse proxy is introduced.

## How to use this file

- Add one dated entry per meaningful task or milestone.
- Keep entries short and decision-focused.
- Use Git commits for the exact code diff and `CONTEXT.md` for the latest working state.

## 2026-06-25

### Admin product ordering

- Added up/down controls to the admin product list for both `Климатици` and `Термопомпи`.
- Added a CSRF-protected `/admin/products/reorder` POST action.
- Reordering updates `storage/data/seed-products.json` order while preserving product data and category membership.

### Admin hardening review

- Regenerated the PHP session ID after successful admin login.
- Tightened forwarded-header trust so `X-Forwarded-For` is used only for explicit `TRUSTED_PROXY_RANGES`.
- Added safe URL rendering for admin-managed public links to block unsafe schemes such as `javascript:`.
- Made the admin login page itself IP-aware: disallowed IPs now receive `403` instead of the login form.
