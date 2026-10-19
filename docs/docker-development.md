# Docker Development

This guide describes the local development workflow. It layers
`docker/docker-compose.dev.yml` on top of the production stack in
`docker/docker-compose.yml`, so the production image and configuration are
untouched while development gets live code, hot reload, logs, and a queue
worker.

## Overview

The host `src/` directory is the single source of truth. The `app` image
contains a snapshot of `src/`, and Compose Watch keeps the running `app`
container in sync with host edits. `queue`, `vite`, and `nginx` bind-mount the
host project directly.

```
Host src/  ──Compose Watch──►  app (php-fpm)  ──FastCGI──►  nginx :80  ──►  browser
           ──bind mount────►  queue (queue:listen)
           ──bind mount────►  vite  (Vite dev server, HMR)  ◄──proxy──  nginx
                              ollama (:11434)
```

Only one Watch rule is needed: the whole `src/` directory is synced to the same
path inside `app`. Dependencies, runtime storage, the SQLite database, and
`.env` are excluded from the sync because they are provided by dedicated mounts
or named volumes.

## Quick start

```sh
cd docker
cp .env.example .env                 # optional Compose values
[ -f ../src/.env ] || cp ../src/.env.example ../src/.env   # required before the first dev start
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build --watch
```

Watch must run in the foreground. `docker compose up -d --watch` is rejected by
Compose. If you want the stack detached, start it with `up -d --build` and run
the watcher separately:

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
docker compose -f docker-compose.yml -f docker-compose.dev.yml watch
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
| Source delivery | host `src/` bind-mounted into `app` and `queue` | `app` uses its baked snapshot plus Compose Watch; `queue`, `vite`, and `nginx` bind-mount the host |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | `local` / `true` |
| Laravel config/routes/views | cached (`php artisan optimize`) | not cached (`optimize:clear` only) |
| Frontend assets | built into `public/build` | served by the `vite` service (HMR) |
| `storage/` | named volume, `storage/logs` bind-mounted to host | bind-mounted from host `src/storage` |
| Database / `.env` in `app` | inside the host source bind | explicit host binds (`src/database`, `src/.env`) |
| Queue worker | `novel-forge-ai-queue` runs `queue:work` | `novel-forge-ai-queue` runs `queue:listen` |
| Vite | not running | `novel-forge-ai-vite` on `:5173`, proxied by nginx |

`vendor/` and `node_modules/` stay on Linux-native named volumes in both modes,
so dependency installs never cross the host/VM boundary. The `app` container in
development has no source bind; Watch syncs `src/` into it.

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

- **PHP / Blade / routes**: edit on the host; Watch copies the file into `app`,
  then refresh. OPcache validates timestamps and configuration is not cached, so
  no restart is required.
- **Vue / CSS**: Vite HMR updates the browser without a full reload. Vite runs
  from the host bind mount, so it does not depend on Watch.
- **New or deleted files**: Watch syncs creations and deletions while it is
  running.
- **Queue code**: the queue service reads from its host bind mount, so worker
  code changes are picked up without a rebuild.
- Rebuild images when `Dockerfile` or `docker-compose*.yml` changes. When
  `composer.json` or `package.json` changes, run `composer install` or
  `npm install` in the `app` container (and restart the queue worker).

The single Watch rule ignores paths that are provided by another mount or are
runtime-generated:

```
.env
vendor/
node_modules/
storage/
database/
bootstrap/cache/
public/build/
public/storage
```

`public/hot` is deliberately not ignored, so the URL written by Vite reaches
`app` through Watch. If Watch was stopped during a branch switch or `git pull`,
restart with `up -d --build` or `up --build --watch`; the baked snapshot is not
updated by an offline sync.

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
- `src/.env` holds Laravel settings (key, database, mail). It is bind-mounted
  into `app` and is excluded from Watch. If an editor replaces the file
  atomically, recreate `app` so the bind points at the new file.
- `src/database` is bind-mounted into `app`, so SQLite data persists on the host
  and is excluded from Watch.

## Common commands

```sh
cd docker
DEV="docker compose -f docker-compose.yml -f docker-compose.dev.yml"

$DEV up --build --watch
$DEV up -d --build
$DEV watch
$DEV logs -f vite
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
