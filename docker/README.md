# Docker Environment

Docker packaging for Novel Forge AI. It runs the application the way it would
run on a Linux production server: one PHP-FPM application container, one queue
worker, one Nginx container in front of it, and one Ollama container for local
AI inference. There is no database container. Development adds a Vite container
for HMR; production builds static assets inside the application container.

## Services

| Service | Container | Image | Role | Host port |
| --- | --- | --- | --- | --- |
| `app` | `novel-forge-ai-app` | `novel-forge-ai/app` | PHP-FPM, Composer, Node build tooling, Laravel | internal `9000` |
| `queue` | `novel-forge-ai-queue` | `novel-forge-ai/app` | Laravel queue worker | none |
| `nginx` | `novel-forge-ai-nginx` | `novel-forge-ai/nginx` | Reverse proxy, serves `public/` and built assets | `80` |
| `ollama` | `novel-forge-ai-ollama` | `novel-forge-ai/ollama` | Local AI inference (`qwen3:4b`) | internal `11434` |
| `vite` (dev) | `novel-forge-ai-vite` | `node:22-alpine` | Vite dev server and HMR | internal `5173` |

The containers share the `novel-forge-ai-network` bridge network and communicate
by service name: Nginx proxies PHP to `app:9000`, Laravel calls Ollama at
`http://ollama:11434`, and the queue worker uses the `database` queue.

## Quick start

```sh
cd docker
cp .env.example .env
docker compose up -d --build
```

Open <http://novel-forge-ai.localhost/> (or <http://localhost/>).

For development (Compose Watch, Vite HMR, queue worker, host-visible logs),
layer the development override instead:

```sh
[ -f ../src/.env ] || cp ../src/.env.example ../src/.env   # required before the first dev start
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build --watch
```

`docker compose up -d --watch` is not supported. To keep the stack in the
background, start it with `up -d --build` and run
`docker compose -f docker-compose.yml -f docker-compose.dev.yml watch` in a
separate terminal. See [../docs/docker-development.md](../docs/docker-development.md).

The image build installs Composer and npm dependencies and builds the production
frontend into `public/build`. The first boot pulls the Ollama model, migrates and
seeds SQLite, and caches the framework. Later boots skip everything that already
exists.

## Directory layout

```
docker/
  docker-compose.yml     Stack definition (4 services, 1 network, 4 volumes)
  docker-compose.dev.yml Development override (Watch, Vite, queue tuning)
  .env.example           Copy to docker/.env to override defaults
  README.md              This file
  php/
    Dockerfile           PHP 8.4-FPM image with Composer and Node 22
    entrypoint.sh        Root entrypoint: prepares writable paths, drops to www-data
    startup.sh           Idempotent production provisioning
    startup.dev.sh       Development provisioning (no asset build, no config cache)
    php.ini              PHP runtime tuning
    www.conf             PHP-FPM pool configuration
  nginx/
    Dockerfile           Nginx image with the baked-in site config
    nginx.conf           Serves public/ and proxies PHP to app:9000
    nginx.dev.conf       Proxies Vite/HMR and serves /storage/ in development
  ollama/
    Dockerfile           Ollama image that auto-pulls the model
../.dockerignore         Keeps host secrets, dependencies, and runtime data out of the build context
```

## Volumes

| Volume | Mount | Purpose |
| --- | --- | --- |
| `novel-forge-ai-vendor` | `app`, `queue` | Composer dependencies (Linux-native, kept off the host filesystem) |
| `novel-forge-ai-node-modules` | `app`, `queue`, `vite` | npm dependencies (Linux-native) |
| `novel-forge-ai-storage` | `app`, `queue`, `nginx:ro` | Laravel storage: cache, sessions, uploads (`storage/logs` is bind-mounted from the host) |
| `novel-forge-ai-ollama-models` | `/root/.ollama` | Downloaded Ollama models |

In production, `../src` is bind-mounted into `app` and `queue` at the multi-site
path `/var/www/html/novel-forge-ai`, so `git pull` and file edits are live.
Nginx mounts the same project read-only and points its document root at
`/var/www/html/novel-forge-ai/public`; additional projects can be added as
sibling folders under `/var/www/html`. The `vendor`, `node_modules`, and
`storage` directories inside the bind mount are shadowed by named volumes, so
they are never synced over the host/VM boundary, except for `storage/logs`,
which is bind-mounted back to the host so Laravel logs appear in
`src/storage/logs`. `public/build` and `database/database.sqlite` stay in
`src/`, so they are visible to Nginx and survive restarts.

The `app` image also contains a snapshot of `src/` (the build context is the
repository root). In development the source bind mount is removed from `app`
and Compose Watch syncs `../src` into the container; the baked snapshot lets the
container start with a complete tree. `queue`, `vite`, and `nginx` keep their
source bind mounts in development. The development override also binds
`src/storage`, `src/database`, and `src/.env` into `app`, so uploads, logs,
SQLite, and secrets stay on the host.

## Configuration

Every value in `.env.example` has a default in `docker-compose.yml`, so
`docker/.env` is optional. The variables that matter:

| Variable | Default | Purpose |
| --- | --- | --- |
| `NGINX_HTTP_PORT` | `80` | Host port for the web entrypoint |
| `APP_URL` | `http://novel-forge-ai.localhost/` | Public application URL (trailing slash, optional sub-folder) |
| `AI_BRAIN_URL` | `http://ollama:11434` | App-to-Ollama endpoint (service name) |
| `AI_BRAIN_API_KEY` | `ollama` | Placeholder key (Ollama ignores it) |
| `OLLAMA_URL` / `OLLAMA_HOST` | `http://ollama:11434` | Fallback Ollama endpoints |
| `OLLAMA_MODEL` | `qwen3:4b` | Model pulled and used by the app |

Application settings (environment, key, database, mail) live in `src/.env`.
Container values only override the variables listed above.

## Common operations

```sh
cd docker

docker compose up -d --build            # build images and start the stack
docker compose ps                       # show container health
docker compose logs -f app              # follow Laravel/PHP-FPM logs
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build --watch
docker compose -f docker-compose.yml -f docker-compose.dev.yml watch
docker compose exec app php artisan     # run any artisan command
docker compose exec app sh              # open a shell in the app container
docker compose exec app npm run build   # rebuild production assets
docker compose exec ollama ollama list  # list installed models
docker compose down                     # stop containers (keep volumes)
docker compose down -v                  # stop and delete volumes (full reset)
```

## Updating the application

```sh
git pull
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app npm run build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

In development, run Compose Watch alongside the stack. After switching branches
or pulling while Watch was stopped, rebuild the app image (`up -d --build` or
restart with `--build --watch`) so the baked source snapshot matches the
worktree.

See [../docs/docker-installation.md](../docs/docker-installation.md) for the
full deployment flow, [../docs/docker-script-description.md](../docs/docker-script-description.md)
for the entrypoint/startup steps, and [../docs/troubleshooting.md](../docs/troubleshooting.md)
when something does not come up.
