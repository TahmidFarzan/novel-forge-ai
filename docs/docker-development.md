# Docker Development

This guide describes the local development workflow. It layers
`docker/docker-compose.dev.yml` on top of the production stack in
`docker/docker-compose.yml`, so the production image and configuration are
untouched while development gets live code, hot reload, logs, and a queue
worker.

## Overview

The host `src/` directory is the single source of truth. Containers are runtime
environments that bind-mount the host project, so edits are picked up
immediately without rebuilding images.

```
Host src/  ──bind mount──►  app (php-fpm)  ──FastCGI──►  nginx :80  ──►  browser
                            queue (queue:listen)
                            vite  (Vite dev server, HMR)  ◄──proxy──  nginx
                            ollama (:11434)
```

## Quick start

```sh
cd docker
cp .env.example .env        # optional; every value has a default
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

Open <http://novel-forge-ai.localhost/> (or add `127.0.0.1 novel-forge-ai.localhost`
to your hosts file). The first boot installs Composer/npm dependencies, migrates,
and seeds; later boots are fast.

To stop the stack while keeping volumes:

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
```

## Development vs production

| Aspect | Production (`docker-compose.yml`) | Development (`+ docker-compose.dev.yml`) |
| --- | --- | --- |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | `local` / `true` |
| Laravel config/routes/views | cached (`php artisan optimize`) | not cached (`optimize:clear` only) |
| Frontend assets | built into `public/build` | served by the `vite` service (HMR) |
| `storage/` | named volume, `storage/logs` bind-mounted to host | bind-mounted from host `src/storage` |
| Queue worker | none (sync dispatch) | `novel-forge-ai-queue` runs `queue:listen` |
| Vite | not running | `novel-forge-ai-vite` on `:5173`, proxied by nginx |

`vendor/` and `node_modules/` stay on Linux-native named volumes in both modes,
so dependency installs never cross the host/VM boundary.

## Services

| Service | Container | Role |
| --- | --- | --- |
| `app` | `novel-forge-ai-app` | PHP-FPM, Laravel application |
| `nginx` | `novel-forge-ai-nginx` | Web tier; serves `public/`, proxies PHP and Vite |
| `queue` | `novel-forge-ai-queue` | `php artisan queue:listen --tries=1 --timeout=0` |
| `vite` | `novel-forge-ai-vite` | Vite dev server with HMR |
| `ollama` | `novel-forge-ai-ollama` | Local inference (`qwen3:4b`) |

Only nginx (`:80`) is published. Vite and HMR are reached through nginx, so the
browser keeps a single origin. The Vite dev server config in `src/vite.config.js`
writes `public/hot` pointing at `http://novel-forge-ai.localhost`, and nginx
proxies `/@vite/`, `/@id/`, `/@fs/`, `/resources/`, `/node_modules/`, and the
`/vite-hmr` websocket to `vite:5173`.

If you prefer to open the app on `http://localhost/` instead, set
`VITE_DEV_SERVER_ORIGIN=http://localhost`, `VITE_HMR_HOST=localhost` for the
`vite` service and `APP_URL=http://localhost/` for `app`.

## Live code changes

- **PHP / Blade / routes**: edit on the host, then refresh. OPcache validates
  timestamps and configuration is not cached, so no restart is required.
- **Vue / CSS**: Vite HMR updates the browser without a full reload.
- **New or deleted files**: reflected immediately through the bind mount.
- Rebuild images only when a `Dockerfile` changes.

## Logs and debugging

```sh
cd docker
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f app
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f queue
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f vite
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f ollama
```

Because `storage/` is bind-mounted, Laravel logs are also written to
`src/storage/logs` on the host (the `daily` channel creates
`laravel-YYYY-MM-DD.log` files) and are readable with any local tool, or through
the in-app log viewer (`opcodesio/log-viewer`).

## Queue monitoring and AI debugging

The `queue` service consumes the `database` queue and reports to the
`romanzipp/laravel-queue-monitor` dashboard at
`/back-office/queue-monitor` (super-admin only). AI generation itself runs
synchronously inside the web request; its failures are written to
`storage/logs/laravel-YYYY-MM-DD.log` with the step name, model, and reason.
Ollama traffic is logged by `docker compose ... logs -f ollama`.

## Environment configuration

- `docker/.env` holds Compose variables and defaults to production values.
- The dev override forces `APP_ENV=local` and `APP_DEBUG=true` for `app` and
  `queue`, and `LOG_LEVEL=debug`, without editing `src/.env`.
- `src/.env` holds Laravel settings (key, database, mail) and is created from
  `src/.env.example` on first boot if missing.

## Common commands

```sh
cd docker
DEV="docker compose -f docker-compose.yml -f docker-compose.dev.yml"

$DEV exec app php artisan migrate
$DEV exec app php artisan db:seed --force
$DEV exec app php artisan tinker
$DEV exec app php artisan test
$DEV exec app sh
$DEV exec app sh -c 'tail -f storage/logs/laravel-$(date +%F).log'
$DEV exec ollama ollama list
$DEV restart app
$DEV down
$DEV down -v
```

See [command-reference.md](command-reference.md) for the full command set.
