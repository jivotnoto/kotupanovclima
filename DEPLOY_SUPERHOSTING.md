# SuperHosting Auto Deploy Guide

This repo is prepared so SuperHosting can deploy the live site from GitHub.

## 1. GitHub Repo

Use:

```text
git@github.com:jivotnoto/kotupanovclima.git
```

Branch:

```text
main
```

## 2. Recommended Panel Setup

In the SuperHosting management panel, create or connect a Git deployment for this repository.

Use a non-public checkout directory if the panel asks where to clone the repo, for example:

```text
/home/kotupano/repositories/kotupanovclima
```

Do not use `/home/kotupano/public_html` as the Git checkout directory.

## 3. Auto Deploy Tasks

The repo includes `.cpanel.yml`.

If the panel supports cPanel-style deployment tasks, it will copy:

- app files to `/home/kotupano/kotupanovklima-app`
- public files to `/home/kotupano/public_html`

The deployment tasks are intentionally copy-only and do not delete files.

## 4. Expected Live Layout

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

## 5. After Deploy Checks

Open these URLs:

```text
https://kotupanovclima.eu/
https://kotupanovclima.eu/admin/login
https://kotupanovclima.eu/sitemap.xml
https://kotupanovclima.eu/uploads/gree-amber-amber-9-1782746359.jpg
```

Expected result: all return `200`, except secondary `.bg` URLs should redirect to `.eu`.

## 6. Very Important Data Rule

`storage/data/` is committed to this repo because the live products, prices, promotions, admin settings, and image paths are part of the deployment package.

Before deploying after admin panel edits:

1. Download current live files from `/home/kotupano/kotupanovklima-app/storage/data/`.
2. Download current live uploads from `/home/kotupano/public_html/uploads/`.
3. Commit those changes to GitHub.
4. Deploy from the panel.

If you skip this, an auto deploy can overwrite newer admin panel changes with older GitHub data.

## 7. PHP Settings

Use PHP 8.3 with:

```text
memory_limit = 256M
upload_max_filesize = 8M
post_max_size = 16M
max_execution_time = 60
max_input_time = 180
max_input_vars = 2000
display_errors = Off
allow_url_fopen = On
allow_url_include = Off
```

## 8. Manual Fallback

If auto deploy tasks are not available, copy manually:

- `bootstrap.php`, `src/`, `views/`, `storage/data/` into `/home/kotupano/kotupanovklima-app/`
- contents of `public/` into `/home/kotupano/public_html/`

Do not upload `storage/logs/` from local backups.
