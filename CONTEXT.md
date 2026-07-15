# Context

This file is the current working memory for the project.
Update it whenever architecture, deployment assumptions, security behavior, or unfinished work changes.

## Project Overview

- Stack: plain PHP application without a framework.
- Production host: SuperHosting shared hosting.
- Canonical domain: `https://kotupanovclima.eu`.
- Secondary domain: `kotupanovklima.bg`, redirected to the matching `.eu` URL.
- Storage: JSON files in `storage/data`.
- Admin auth: code-based login plus optional IP allowlist mode.
- Theme: forced light/white UI palette.

## Current GitHub/Deployment Model

- GitHub remote: `git@github.com:jivotnoto/kotupanovclima.git`.
- Gitea remote: `ssh://git@gitea.localdomain:3333/madh/kotupanovclima.git`.
- Branch: `main`.
- The repo is now the deployment source for SuperHosting.
- Docker runtime files were removed from the repo.
- `.cpanel.yml` contains copy-only deployment tasks for panels that support cPanel-style Git deploys.
- Deployment guide: `DEPLOY_SUPERHOSTING.md`.
- `.gitattributes` enforces LF for source/text files and binary handling for images/documents so Windows Git and WSL Git report the same status.

## Production Layout

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
      data/
      logs/
```

## Key Paths

- Production public entry/template: `public/index.php`.
- Main app/router: `src/App.php`.
- Auth and allowlist logic: `src/Auth.php`.
- IP detection helpers: `src/helpers.php`.
- Admin settings view: `views/admin/settings.php`.
- Admin product ordering UI: `views/admin/products-list.php`.
- Live JSON data in repo: `storage/data/`.
- Uploaded product images in repo: `public/uploads/`.

## Protected Data Rule

- Products are stored in `storage/data/seed-products.json`.
- Promotions are stored in `storage/data/promotions.json`.
- Admin settings are stored in `storage/data/admin-settings.json`.
- The live server catalog/pricing/admin data is the source of truth when the admin panel has been used.
- Before deploying after admin panel edits, fetch current live `storage/data/` and `public_html/uploads/`, commit them, then deploy.
- Do not overwrite product/model/price files from stale local copies.

## Current Product UI Behavior

- Catalog product images link to their product detail pages.
- Product detail images open in a lightweight overlay controlled by `public/assets/site.js`.
- Admin product upload writes images to the public document root `uploads/` folder.
- Public product lists support combined brand, technology, size/power, and EUR price filters.
- Catalog results are paginated at 12 products per page; pagination preserves active filters and links directly to `#modeli`.
- Product descriptions are clamped to three lines with an accessible expand/collapse control when content overflows.
- Product badges are generated only from confirmed installation, warranty, and operating-temperature fields.
- Optional admin fields `installationMode` and `warrantyYears` remain unset for existing products until confirmed.
- Fujitsu catalog entries are normalized as `Fujitsu Airstage` / `KJCA` and use the official KJ Series 2025 specs for ASEH07/09/12/14KJCAL.

## Current Branding Behavior

- The public and admin headers use `public/images/kotupanovclima-logo-transparent.png`.
- Browser icons use `public/images/site-icon.png`.
- The default Open Graph/Twitter preview image uses `public/images/site-og-image.png`.

## Current SEO Behavior

- Public pages render canonical and Open Graph URLs on `https://kotupanovclima.eu`.
- Public pages include page-specific meta descriptions, Open Graph/Twitter metadata, and JSON-LD for the HVAC business, products, breadcrumbs, and FAQs where applicable.
- `kotupanovklima.bg` redirects with 301 to the matching path on `https://kotupanovclima.eu`.
- Admin pages render `noindex, nofollow`.
- `robots.txt` and `sitemap.xml` are served dynamically through PHP and point to `https://kotupanovclima.eu`.
- Static host-specific `robots-*` and `sitemap-*` files remain in `public/` as deployment fallbacks.

## Current Allowlist Behavior

- Access modes: `open`, `allowlist_only`.
- Allowed entries support exact IPs and CIDR ranges.
- Client IP resolution prefers proxy headers only when `REMOTE_ADDR` is treated as a trusted proxy.
- Trusted proxies are recognized only from optional `TRUSTED_PROXY_RANGES`.

## Current Logging Behavior

- App access logs include request and IP context.
- Security logs include admin login and allowlist denial events.
- Runtime logs live under `storage/logs/` on the server and are not committed.

## Current UX Behavior

- Desktop header keeps the logo, navigation, and phone on one row; the logo is capped at 70 px.
- Mobile header uses a hamburger menu with the phone as its final item.
- The homepage brand strip uses duplicated fixed-width groups for continuous motion and shows at least three brands on narrow screens.
- The homepage includes three original service images for air conditioners, heat pumps, and repair/maintenance.
- Promotion images are bounded inside their responsive image stages so square and portrait assets are not clipped on mobile.
- Promotion images are capped at 250 px and promotion CTA buttons are centered with 10 px vertical spacing.
- Catalog product-card images use the same bounded containment on both air-conditioner and heat-pump listing pages.
- Promotion and product image stages are transparent, with subtle gradients applied to their card bodies instead.
- Product CTAs use the warm cream gradient and orange border.
- The homepage H1 has an explicit break after "Перник —" so the desktop heading stays on two lines.
- Public prices are displayed in EUR only; BGN values remain stored internally for admin compatibility.
- The contact page is single-column and supports `/kontakti?topic=repair#barzo-zapitvane`.
- A mobile scroll-to-top button appears after 500 px and stays hidden while cookie consent is open.

## Latest Local Verification

- On 2026-07-14 the current live `storage/data` and `public_html/uploads` were downloaded to `backups/pre-ux-live-20260714-124240` and matched the local copies by SHA-256 before development.
- The UX work includes and preserves the three newer GitHub commits ending at `c4790ee` (`SEO update`, symlink cleanup, and company email update).
- PHP 8.3 lint, JSON validation, JavaScript syntax, 67 responsive browser checks, and isolated admin regression tests passed locally.
- No live upload or production deployment was performed for this UX work. Deploy only after an explicit user request and a fresh live backup/sync.

## Session Guidance For Future Work

- Read this file and `GISTORY.md` before continuing project work.
- Treat live catalog data and uploads as protected customer/admin content.
- For live sync use SFTP only; shell access on the hosting account is disabled.
- When syncing live data into GitHub, fetch:
  - `/home/kotupano/kotupanovklima-app/storage/data/`
  - `/home/kotupano/public_html/uploads/`
- Keep deployment changes copy-only unless the user explicitly approves deleting live files.
