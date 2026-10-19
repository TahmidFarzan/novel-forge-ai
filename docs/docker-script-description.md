# Docker Script Description

The application container is provisioned by two POSIX shell scripts that are
copied into the image by `docker/php/Dockerfile`:

| Script | Runs as | Responsibility |
| --- | --- | --- |
| `docker/php/entrypoint.sh` | `root` | Prepare writable paths, create the missing environment/database files, then hand off to the selected startup script |
| `docker/php/startup.sh` | `www-data` | Idempotent production provisioning (the 11 deployment steps) |
| `docker/php/startup.dev.sh` | `www-data` | Development provisioning without asset build or framework cache |

`entrypoint.sh` selects the script from `${STARTUP_SCRIPT:-/usr/local/bin/startup.sh}`;
the development override sets `STARTUP_SCRIPT=/usr/local/bin/startup.dev.sh`.
All scripts start with `set -eu`, so any unexpected command failure aborts the
boot instead of silently starting a half-configured container.

## `entrypoint.sh`

Runs as PID 1 on every container start, before PHP-FPM:

1. `cd /var/www/html/novel-forge-ai` (the project root inside the container).
2. Creates the required directory tree: `storage/framework/{cache/data,sessions,views}`,
   `storage/logs`, `storage/app/public`, `bootstrap/cache`, `database`, and
   `public/build`.
3. Creates `src/.env` from `src/.env.example` when it does not exist. An
   existing `.env` is never overwritten, so `APP_KEY` and user settings persist
   across restarts.
4. Creates `database/database.sqlite` when it does not exist.
5. Repairs ownership on any of `storage`, `storage/logs`, `bootstrap/cache`,
   `database`, `public`, `vendor`, and `node_modules` that `www-data` cannot
   write to. This handles bind mounts created on Linux hosts and named volumes
   created by an older image.
6. Runs the selected startup script (`startup.sh`, or `startup.dev.sh` in
   development) as `www-data` via `su-exec`.
7. `exec "$@"` replaces the shell with the container command (`php-fpm`), so
   `SIGTERM`/`SIGINT` from `docker stop` reach PHP-FPM directly. No supervisor or
   extra process manager is needed.

## `startup.sh`

Runs as `www-data` on every boot. Every step is guarded, so re-running the
script is safe and cheap once the application is provisioned.

### STEP 1 - Environment setup

`entrypoint.sh` already guarantees `src/.env` exists. `startup.sh` then
normalizes `APP_URL` in place.

### STEP 1b - APP_URL normalization

`APP_URL` may be a bare origin or carry a sub-folder path. The script ensures it
ends with a single `/` without touching the scheme, host, port, or path:

```env
APP_URL=http://example.com           -> http://example.com/
APP_URL=http://example.com/          -> http://example.com/
APP_URL=http://example.com/site      -> http://example.com/site/
APP_URL=http://example.com/site/     -> http://example.com/site/
```

The value is read from the `APP_URL=` line in `src/.env`, rewritten only when a
trailing slash is missing, and never hardcodes a domain. Laravel then uses the
normalized URL as the application origin (see
[APP_URL normalization](#app_url-normalization-in-laravel) below).

### STEP 2 - Application key

Generates `APP_KEY` with `php artisan key:generate --force` only when the `.env`
file has no `APP_KEY=base64:` value. The key is written to the bind-mounted
`src/.env`, so it persists across restarts.

### STEP 3 - Composer

Runs `composer install --no-interaction --prefer-dist --no-progress` when
`vendor/autoload.php` is missing. Skipped when dependencies already exist.

### STEP 4 - Node

Runs `npm install --no-audit --no-fund` when `node_modules/` is missing or does
not contain the container marker `node_modules/.novel-forge-ai-container`. The
marker distinguishes a Linux-native `node_modules` installed in the volume from
a host copy that may contain the wrong platform binaries.

### STEP 5 - Frontend build

Hashes `package.json`, `package-lock.json`, and `vite.config.js`. If
`public/build/manifest.json` exists and the stored hash matches, the build is
skipped; otherwise `npm run build` runs and the new hash is written to
`public/build/.novel-forge-ai-build`. A stale `public/hot` file is removed in
both cases so production never points at a dev server.

### STEP 6 - Storage link

Runs `php artisan storage:link` when `public/storage` does not exist.

### STEP 7 - Database

The SQLite file `database/database.sqlite` is created by `entrypoint.sh`. No
database container is used.

### STEP 8 - Migration

Runs `php artisan migrate --force`. Laravel only applies pending migrations, so
this is idempotent and safe on every boot.

### STEP 9 - Seeder

Bootstraps the framework and checks whether the `users` table exists and has
rows. Seeders run only when the database is empty, so seed data is inserted
exactly once.

### STEP 10 - Framework optimization

Runs `php artisan optimize:clear` and then `php artisan optimize`, which caches
config, routes, events, and views for production performance.

### STEP 11 - Start PHP-FPM

`startup.sh` exits successfully and `entrypoint.sh` `exec`s PHP-FPM. The
container stays running in the foreground.

## `startup.dev.sh`

Runs instead of `startup.sh` in development. It requires `src/.env` to exist
(it is bind-mounted into `app`), then:

1. Runs `composer install` when `vendor/autoload.php` is missing.
2. Generates `APP_KEY` when the `.env` file has no key.
3. Runs `npm install` when `node_modules/` is missing or lacks the container
   marker.
4. Creates the `public/storage` link when missing.
5. Clears cached configuration, routes, events, and views.
6. Runs pending migrations and seeds only when the database has no users.
7. Skips the production asset build and `php artisan optimize`; Vite serves the
   frontend in development.

The script does not update the source tree. Source changes reach `app` through
Compose Watch, while `queue` and `vite` read the host bind mount directly.

## APP_URL normalization in Laravel

Normalizing the `.env` value is not enough on its own: during HTTP requests
Laravel derives URLs from the incoming request rather than from `APP_URL`. To
make `APP_URL` the single source of truth (including any sub-folder prefix),
`AppServiceProvider` pins the URL origin:

```php
URL::useOrigin(rtrim(config('app.url'), '/'));
```

Combined with `$middleware->trustProxies(at: '*')` in `bootstrap/app.php`, this
means `asset()`, `url()`, `route()`, `Storage::url()`, and the Vite manifest
assets all honor the configured origin and path prefix:

| Helper | `APP_URL=http://example.com/site/` |
| --- | --- |
| `asset('uploads/logo.png')` | `http://example.com/site/uploads/logo.png` |
| `url('dashboard')` | `http://example.com/site/dashboard` |
| `route('home')` | `http://example.com/site/home` |
| `Storage::disk('public')->url('a.png')` | `http://example.com/site/storage/a.png` |

## Nginx hand-off

Nginx talks to PHP-FPM over FastCGI, not HTTP `proxy_pass`, so the forwarded
headers are passed as equivalent FastCGI parameters in
`docker/nginx/nginx.conf`:

```nginx
fastcgi_param HTTP_HOST $host;
fastcgi_param SERVER_NAME $host;
fastcgi_param HTTP_X_REAL_IP $remote_addr;
fastcgi_param HTTP_X_FORWARDED_FOR $proxy_add_x_forwarded_for;
fastcgi_param HTTP_X_FORWARDED_HOST $host;
fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;
```

This lets Laravel detect the client IP, host, and scheme behind an additional
reverse proxy. A container `502 Bad Gateway` means Nginx cannot reach
`app:9000`; check that the `app` container is `healthy` and running PHP-FPM.
