# Gistory

## 2026-07-14 - GitHub/SuperHosting Deployment Source

- Synced live SuperHosting app files, public files, `storage/data`, and `public_html/uploads` back into the local repo before committing.
- Removed Docker-only runtime files from the GitHub project: `.dockerignore`, `Dockerfile`, `docker-compose.yml`, and `docker/`.
- Added `.cpanel.yml` with copy-only SuperHosting/cPanel-style deployment tasks.
- Rewrote `DEPLOY_SUPERHOSTING.md` as the easy auto-deploy guide.
- Updated `README.md` and `CONTEXT.md` so the repo is documented as the SuperHosting deployment source, not a Docker local runtime.
- Important: `storage/data` is committed, so future deploys must first sync live admin edits back into Git if products, prices, promotions, admin settings, or image paths changed on the live site.

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

### HTTPS redirects

- Added `.htaccess` redirects from HTTP to HTTPS for `kotupanovklima.bg` and `kotupanovclima.eu`, including `www` variants.
- Updated the SuperHosting deployment template so future uploads keep the same HTTPS behavior.

### SEO canonical and sitemap

- Set `kotupanovklima.bg` as the canonical SEO domain across public pages.
- Added `noindex, nofollow` metadata for admin pages.
- Added dynamic `robots.txt` and `sitemap.xml` endpoints.

### Product image interactions

- Made catalog product images clickable links to their product detail pages.
- Added a lightweight product image viewer so detail-page images open larger in an overlay.

### SEO Open Graph

- Added Open Graph and Twitter Card metadata across public pages.
- Added product-specific SEO titles, descriptions and social preview images.
- Extended the dynamic sitemap with `lastmod` values.

### Host-aware sitemap fix

- Made `robots.txt` and `sitemap.xml` generate same-domain URLs for `kotupanovklima.bg` and `kotupanovclima.eu`.
- Avoided starting a PHP session for `robots.txt` and `sitemap.xml`, so crawlers receive clean cacheable responses.
- Added static host-specific sitemap and robots files mapped by `.htaccess` to make Search Console fetches independent from PHP sessions.
- Made public page canonical and Open Graph URLs same-domain for `.bg` and `.eu`.
- Added an explicit homepage-only 301 redirect from `kotupanovklima.bg` to `https://kotupanovclima.eu/`.

### EU canonical domain migration

- Made `kotupanovclima.eu` the canonical SEO base for public pages, Open Graph URLs, dynamic `robots.txt`, and dynamic `sitemap.xml`.
- Changed `.htaccess` so all `kotupanovklima.bg` requests redirect with 301 to the same path on `https://kotupanovclima.eu`.
- Updated static sitemap and robots files so no SEO file advertises `kotupanovklima.bg` as canonical.
- Served `sitemap.xml` and `robots.txt` directly on both `kotupanovclima.eu` and `www.kotupanovclima.eu` using Apache `[END]` rewrites to support either Search Console URL-prefix property.
- Routed public `sitemap.xml` and `robots.txt` back through PHP so the sitemap reflects the live product catalog instead of stale static files.

### Catalog data protection rule

- Added a standing project rule: do not overwrite configured product, model, and price data from stale local files; fetch current live catalog files first and migrate/update those contents when changes are needed.

### Homepage brand logo strip

- Moved the official brands card directly under the top site header card and changed it into a logo-only strip with each logo linking to the relevant product category.
- Added continuous logo strip motion and a small decorative air-conditioner mark inside the main homepage hero card.

### Decorative page marks

- Added lightweight decorative SVG marks for catalog, heat pump, promotions, contacts, and repair/service pages.
- Applied the marks to public page hero/surface cards and product detail side cards without touching catalog product/model/price data.

### Friend-recommended site essentials

- Added original draft pages for Общи условия and Политика за поверителност, plus sitemap/footer links.
- Added cookie consent banner, responsive hamburger menu, richer footer, and a public contact form with CSRF, honeypot, basic rate limiting, and mail delivery via `CONTACT_FORM_TO` or the configured company email.
- Reworked the legal pages into a fuller numbered format inspired by the reviewed reference text, but adapted to this site as an inquiry/service website rather than an online checkout store.

### Local phone testing port

- Added Docker compose port `9090:80` for local LAN testing from a phone. Windows already has a portproxy for `0.0.0.0:9090` to the current WSL IP, while `3001` remains loopback-only on the Windows side without elevated portproxy setup.

### Site logo branding

- Added cropped local logo assets from the provided Kotupanov Clima image: full header logo, standalone mark, favicon icon, and social preview image.
- Replaced the old `KK` header badge with the new logo and switched the default Open Graph image to the branded preview.
- Added a transparent header logo variant and removed the inner logo card styling so the logo sits directly on the main header panel.

### Fujitsu Airstage catalog cleanup

- Fetched the current live `seed-products.json` before editing and confirmed it matched local data.
- Replaced stale Fujitsu General/K6TE/KETE references with Fujitsu Airstage KJCA mappings.
- Filled ASEH07/09/12/14KJCAL specs from the official Fujitsu Airstage KJ Series 2025 catalog while preserving existing live prices.
- Added Fujitsu Airstage brand logo and KJCA product image mapping for catalog cards, product pages, Open Graph images, and the homepage brand slider.

## 2026-07-14

### Responsive UX and catalog optimization

- Rebuilt the desktop/mobile header, hamburger navigation, phone placement, continuous brand slider, and mobile scroll-to-top control.
- Added three original optimized WebP service visuals and linked homepage cards for climate systems, heat pumps, and repair/maintenance.
- Added product images to promotion cards, a distinct promotion CTA style, and EUR-only public pricing.
- Added three-line product descriptions with accessible expansion, confirmed-data badges, and combined EUR price filtering.
- Added optional admin fields for installation mode and warranty years without changing existing product values.
- Streamlined the contact page, added the repair deep link/topic selection, and updated the repair CTA.
- Added the company logo to the footer and reduced its bottom row to copyright plus the two legal links.

### Friend SEO changes integrated

- Fast-forwarded and preserved the GitHub commits for SEO content, structured data, local symlink cleanup, and the company email update.
- Kept HVAC business, product, breadcrumb, and FAQ JSON-LD plus the new SEO copy, NAP block, image alt text, and lazy loading.
- Reconciled the SEO copy with the UX rules by keeping public prices in EUR and avoiding blanket installation or warranty promises.

### Local verification and data safety

- Downloaded a read-only live snapshot to `backups/pre-ux-live-20260714-124240`; live product data and uploads matched local by SHA-256.
- Passed PHP 8.3 lint, JSON/JavaScript/CSS checks, 67 responsive browser assertions, and isolated admin tests for IP allowlisting, login, CSRF, product save, image upload, XSS escaping, and reordering.
- Kept all testing local and did not upload or deploy these changes to the live site.

### Mobile promotion image containment

- Fixed square and portrait promotion images being clipped by the fixed-ratio card stage on mobile.
- Verified every homepage and promotions-page image at 320, 360, 390, 430, and 1280 px, including the full LG Therma V image.

### Mobile catalog image containment

- Applied the same responsive containment fix to product cards on the air-conditioner and heat-pump catalog pages.
- Verified all 52 air-conditioner cards and all 3 heat-pump cards at 320, 360, 390, 430, and 1280 px.

### Promotion card CSS refinement

- Added the suggested 250 px promotion-image cap without removing responsive stage containment.
- Centered promotion CTA buttons with 10 px bottom spacing while preserving automatic top spacing.
- Initially deferred the proposed global `margin: auto` rule because it affects spacing in unrelated decorated layouts; it was later applied after an explicit user request.

### Catalog pagination

- Added server-rendered pagination with 12 products per page to the shared air-conditioner and heat-pump catalog template.
- Preserved search, brand, technology, power, and EUR price filters across page links; submitting new filters resets to page 1.
- Added an accessible current-page state, previous/next controls, compact page links, and direct `#modeli` navigation.
- Verified 5 air-conditioner pages, one-page heat-pump behavior, invalid page handling, filter persistence, and layouts at 320 and 1280 px.

### Cross-platform Git line endings

- Added `.gitattributes` to normalize source files to LF and explicitly classify binary assets.
- Aligned the shared repository's local `core.autocrlf` setting to `input` so Windows Git and WSL Git no longer disagree about unchanged files.

### Requested card styling and homepage heading

- Made promotion and product image stages transparent and moved the visual gradients into the card bodies.
- Centered promotion CTAs with 10 px vertical spacing and styled product CTAs with the requested cream gradient, orange border, dark text, and soft shadow.
- Applied the requested automatic margins to direct content children of decorated surfaces.
- Added an explicit line break to keep the homepage H1 in the requested two-line desktop layout.

### Mobile copy and catalog filters

- Limited long catalog, homepage, repair-service, and product-detail copy to four lines on mobile with accessible `Виж още` / `Скрий` controls.
- Reused the same labels for product-card description disclosures and kept short text free from unnecessary buttons.
- Collapsed catalog filters behind a funnel-icon button below 720 px, including an active-filter count and synchronized `aria-expanded` state.
- Kept all long text and the full filter form visible on desktop, with no changes to product data or uploaded images.
