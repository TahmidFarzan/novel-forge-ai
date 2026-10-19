# Docker Architecture

This page explains how the Docker deployment is structured and what each file
under `docker/` does.

## Design principles

The deployment models a Laravel application on a production Linux server, not a
microservice platform:

- One application container owns PHP, Composer, the build toolchain, and the
  Laravel codebase. A queue worker uses the same image.
- Nginx is a thin web tier: it serves `public/` and proxies PHP. It does not run
  application code.
- Ollama is an independent service reachable over the internal network.
- Frontend assets are built into `public/build` at boot and served statically.
  There is no Node runtime service in production and no database container.
  Development adds a dedicated Vite container.

## Naming convention

| Kind | Pattern | Examples |
| --- | --- | --- |
| Compose service | `<service>` | `app`, `queue`, `nginx`, `ollama`, `vite` (dev) |
| Container | `novel-forge-ai-<service>` | `novel-forge-ai-app` |
| Image | `novel-forge-ai/<service>` | `novel-forge-ai/nginx` |
| Volume | `novel-forge-ai-<purpose>` | `novel-forge-ai-storage` |
| Network | `novel-forge-ai-network` | — |

Internal traffic always uses Compose service names (`app:9000`,
`ollama:11434`), never `localhost`.

## `docker-compose.yml`

Defines the `novel-forge-ai` project with four services on the
`novel-forge-ai-network` bridge network:

| Service | Container | Image | Notes |
| --- | --- | --- | --- |
| `app` | `novel-forge-ai-app` | `novel-forge-ai/app` | PHP-FPM running Laravel; bind-mounts `../src` |
| `queue` | `novel-forge-ai-queue` | `novel-forge-ai/app` | Queue worker; bind-mounts `../src` |
| `nginx` | `novel-forge-ai-nginx` | `novel-forge-ai/nginx` | Publishes `${NGINX_HTTP_PORT:-80}`; serves `../src/public` |
| `ollama` | `novel-forge-ai-ollama` | `novel-forge-ai/ollama` | Ollama server and model download |

It declares four named volumes: the two persistent data volumes
(`novel-forge-ai-storage` and `novel-forge-ai-ollama-models`) plus
`novel-forge-ai-vendor` and `novel-forge-ai-node-modules`, which keep the
Linux-native Composer and npm dependencies out of the Windows/macOS bind mount
for file-sync performance. Both `app` and `queue` additionally bind-mount
`../src/storage/logs` over the storage volume, so Laravel log files are written
to the host project directory and survive container recreation. It publishes
only the Nginx port and defines a health check for the long-running services.
The `queue` service depends on `app` becoming healthy; `nginx` depends on `app`
becoming healthy.

The application build context is the repository root, so the image contains a
snapshot of `src/`. In production the source bind mount remains authoritative.
In development the `app` source bind is removed and Compose Watch syncs the host
tree into the container.

## `docker/.env.example`

Template for `docker/.env`. It documents the tunable Compose variables
(HTTP port, application URL, Ollama connection, Ollama runtime tuning). Copy it
to `docker/.env` to override the built-in defaults.

## `docker/php/Dockerfile`

Builds the application runtime image:

- Base image `php:8.4-fpm-alpine`.
- Copies Composer from the official `composer:2` image.
- Copies the Node 22 binary and its bundled npm from `node:22-alpine`, so npm
  and Vite run inside the application container without a separate service.
- Installs the system libraries and PHP extensions the app needs: `bcmath`,
  `exif`, `gd`, `intl`, `mbstring`, `opcache`, `pcntl`, `pdo_sqlite`, `zip`.
- Creates the `storage/` skeleton owned by `www-data`, so the named volume is
  initialized with the correct directories and ownership on first use.
- Copies `php.ini`, `www.conf`, `entrypoint.sh`, `startup.sh`, and
  `startup.dev.sh`; normalizes CRLF line endings and marks the scripts
  executable.
- Copies `src/` into the image after `WORKDIR` as `www-data`, so the layer is
  reused until the source changes and the image is complete without a bind
  mount. The root `.dockerignore` excludes host secrets, dependencies,
  runtime storage, SQLite files, and built assets from the build context.
- Runs `entrypoint.sh` as `ENTRYPOINT` and `php-fpm` as `CMD`.

## `docker/php/php.ini`

PHP runtime tuning for long-running AI generation: 1 GB memory limit, 30-minute
execution time, 128 MB upload/post limits, OPcache enabled with timestamp
validation (so edits and `git pull` are picked up), and error logging to stderr.

## `docker/php/www.conf`

PHP-FPM pool configuration: listens on `0.0.0.0:9000`, runs workers as
`www-data`, uses dynamic process management, and clears no environment variables
(`clear_env = no`) so container-provided variables reach PHP.

## `docker/php/entrypoint.sh`

The root entrypoint. It prepares writable paths before handing off to PHP-FPM:

1. Creates the `storage/` and `bootstrap/cache` directory trees.
2. Creates `src/.env` from `src/.env.example` when missing (never overwrites an
   existing `.env`, so `APP_KEY` persists).
3. Creates `database/database.sqlite` if it does not exist.
4. Ensures `storage/`, `storage/logs/`, `bootstrap/cache/`, and the SQLite file
   are owned by `www-data`, so PHP-FPM workers can write logs, cache, sessions,
   and the database.
5. Runs `${STARTUP_SCRIPT:-/usr/local/bin/startup.sh}` as `www-data`; the
   development override selects `startup.dev.sh`.
6. `exec`s the container command (`php-fpm`), so the real process receives stop
   signals directly.

## `docker/php/startup.sh`

The idempotent provisioning script, run as `www-data` on every boot. It only
performs work that is missing:

1. Normalizes `APP_URL` in `src/.env` so it ends with a single `/`, preserving
   scheme, host, port, and any sub-folder path.
2. Generates an application key when none is set (persisted in `src/.env`).
3. `composer install` when `vendor/autoload.php` is absent.
4. `npm install` when `node_modules/` is absent or lacks the container marker.
5. `npm run build` only when the `package.json`/lockfile/`vite.config.js` hash
   changed, producing `public/build`; removes a stale `public/hot` file.
6. `storage:link` when `public/storage` is absent.
7. `migrate --force` (pending migrations only) and `db:seed --force` only when
   the database has no records.
8. `php artisan optimize:clear` followed by `php artisan optimize` to cache
   config, routes, events, and views.

See [Docker Script Description](docker-script-description.md) for the full
step-by-step reference.

## `docker/nginx/Dockerfile`

Builds the `novel-forge-ai/nginx` image from `nginx:1.27-alpine`, copying
`nginx.conf` into the image as the default site. Because the configuration is
baked in, changing it requires rebuilding the image (`docker compose build nginx`).

## `docker/nginx/nginx.conf`

The Nginx site configuration:

- Serves `public/` as the document root.
- Routes `~ \.php$` requests to the `app` service over FastCGI with generous
  timeouts for AI generation.
- Passes `HTTP_HOST`, `SERVER_NAME`, and the `X-Real-IP` / `X-Forwarded-*`
  headers to PHP as FastCGI parameters, so Laravel detects the real host,
  scheme, client IP, and sub-folder prefix.
- Caches `public/build/*` assets for one year with immutable headers.
- Enables gzip for text assets.
- Denies dotfile access and silences logging for `favicon.ico` / `robots.txt`.

There is no Vite dev-server proxy and no HMR websocket: assets are static in a
production deployment.

## APP_URL handling

`APP_URL` is the single source of truth for the public URL, including any
sub-folder deployment. `startup.sh` normalizes it to end with `/`, and
`AppServiceProvider` pins Laravel's URL origin to that value so `asset()`,
`url()`, `route()`, `Storage::url()`, and Vite assets all include the correct
origin and path prefix. `TrustProxies` is enabled so forwarded headers are
honored behind an additional reverse proxy. See
[Docker Script Description](docker-script-description.md).

## `docker/ollama/Dockerfile`

Extends `ollama/ollama` and installs a custom entrypoint that:

1. Starts `ollama serve`.
2. Waits until the server answers `ollama list`.
3. Pulls `OLLAMA_MODEL` (default `qwen3:4b`) if it is not already present.
4. Waits on the server process so container signals propagate correctly.

The model is stored in the `novel-forge-ai-ollama-models` volume, so it is
downloaded only once.

## Lifecycle summary

```
docker compose up
  -> ollama starts, pulls model, stays healthy
  -> app entrypoint: prepare storage/db -> startup.sh (composer, npm, build,
       key, storage:link, migrate, seed, optimize) -> exec php-fpm
  -> queue starts after app is healthy and consumes the database queue
  -> nginx starts and proxies PHP to app:9000
```

## Development override

`docker/docker-compose.dev.yml` layers on top of this file for local
development without changing the production services:

- `app` runs `startup.dev.sh` instead of `startup.sh`, so it installs
  dependencies, generates the key, links storage, and migrates/seeds, but does
  not build assets or cache the framework. It receives `APP_ENV=local` and
  `APP_DEBUG=true`.
- The `app` source bind is replaced with `volumes: !override`, so the container
  runs from the baked source snapshot. One Compose Watch rule syncs `../src`
  into `/var/www/html/novel-forge-ai`, ignoring `.env`, `vendor/`,
  `node_modules/`, `storage/`, `database/`, `bootstrap/cache/`, `public/build/`,
  and `public/storage`.
- `storage/`, `database/`, and `.env` are bind-mounted from the host into
  `app`, so logs, uploads, SQLite, and secrets stay on the host.
- `queue` runs `php artisan queue:listen`, feeding the queue-monitor dashboard;
  it keeps the source bind mount.
- A `vite` service serves assets with HMR from the source bind; `nginx.dev.conf`
  proxies Vite and the `/vite-hmr` websocket through the single nginx origin,
  and serves `/storage/` from the host storage bind.
- `nginx` keeps its read-only source and storage binds.

`volumes: !override` requires Compose 2.24.4 or newer. The `vendor` and
`node_modules` named volumes are kept in both modes for performance. See
[docker-development.md](docker-development.md).
