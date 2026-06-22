# Kotupanovklima PHP

Лек PHP вариант на сайта за `Котупановклима ЕООД`, направен без Node.js runtime и без външна framework зависимост. Данните се пазят в JSON файлове, а публичната част и админ панелът се рендерират от обикновен PHP.

## Какво включва

- публичен сайт с:
  - начална страница
  - страница с промоции
  - контакти
  - каталог за климатици
  - каталог за термопомпи
  - detail страница за всеки продукт
- админ панел с:
  - вход с код
  - IP allowlist режим с поддръжка за IP и CIDR записи
  - редакция на продукти
  - редакция на промоции
  - редакция на основни сайт настройки
- логове в `storage/logs`
  - `access.log`
  - `security.log`
  - `application.log`
  - `apache-access.log`
  - `apache-error.log`
  - `php-error.log`

## Изисквания

- PHP `8.2+` или `8.3+`
- разширения:
  - `mbstring`
  - `fileinfo`
- Apache с `mod_rewrite`

## Структура

- `public/`:
  - document root
  - `index.php`
  - статични assets
- `src/`:
  - routing
  - auth
  - JSON data access
  - logging
- `views/`:
  - публични и админ шаблони
- `storage/data/`:
  - JSON данни за сайта
- `storage/logs/`:
  - access/security/application логове

## Локално пускане с Docker в WSL

Отвори WSL терминал и изпълни:

```bash
cd "/mnt/c/Users/Jivotnoto/Documents/New project/kotupanovklima-php"
docker compose up -d --build
```

Сайтът ще бъде на:

- [http://localhost:3001](http://localhost:3001)
- [http://localhost:3001/admin/login](http://localhost:3001/admin/login)

Полезни команди:

```bash
docker compose ps
docker compose logs -f app
docker compose exec app bash
docker compose down
```

## Админ достъп

Текущият код в seed данните е този, който вече е записан в `storage/data/admin-settings.json`.

IP allowlist режимът също се чете от:

- `storage/data/admin-settings.json`

Позволени са:

- точни IP адреси, например `192.168.1.50`
- CIDR диапазони, например `192.168.1.0/24` или `172.16.0.0/12`

## Цени

В админ панела цените се въвеждат в евро. При запис:

- евро стойността се конвертира към лева по курс `1.95583`
- в сайта се показват и двете стойности

## Качване на изображения

- позволени формати:
  - `jpg`
  - `png`
  - `webp`
- максимален размер:
  - `5 MB`

## Логове и сигурност

Приложението записва:

- access лог за всяка заявка
- security събития:
  - login
  - logout
  - отказан IP достъп
  - upload откази
  - запис/триене на продукт
  - запис/триене на промоция
  - промяна на админ настройките
- application лог при необработени exception-и

В app логовете вече се записват и IP диагностични полета:

- `clientIp`
- `clientIpSource`
- `remoteAddr`
- `trustedProxy`
- `xForwardedFor`
- `xForwardedChain`
- `xRealIp`

`X-Forwarded-For` и `X-Real-IP` се ползват само когато `REMOTE_ADDR` изглежда като trusted proxy адрес
(например loopback/private мрежа или запис от `TRUSTED_PROXY_RANGES`).

По желание можеш да зададеш trusted proxy ranges през env:

```bash
TRUSTED_PROXY_RANGES="127.0.0.1/32 10.0.0.0/8 172.16.0.0/12 192.168.0.0/16"
```

Изпращат се и базови security headers:

- `Content-Security-Policy`
- `X-Frame-Options`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`

## Shared Hosting / SuperHosting

PHP вариантът е значително по-лесен за shared hosting от Node.js.

Най-добрият вариант е:

1. качваш целия проект на хостинга
2. document root на домейна сочи към `public/`
3. `storage/` остава извън публично достъпната част

Ако хостингът не позволява custom document root, тогава трябва да се направи отделен `public_html` deployment layout. Тази стъпка може да се подготви след като потвърдиш, че искаш точно target layout-а на хостинга.

## Ограничение в текущата сесия

В тази работна сесия не успях да стартирам реално Docker/WSL, защото достъпът до тях е блокиран от средата. Затова setup-ът е подготвен файлово, но не е runtime-validated тук.
