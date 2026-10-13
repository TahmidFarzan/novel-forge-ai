# Docker Deployment

This guide covers deploying and updating Novel Forge AI with Docker. The stack
follows a standard Laravel production layout: Nginx serves the `public/`
directory and proxies PHP requests to a PHP-FPM application container, and a
separate Ollama container provides local AI inference.

## Requirements

- Docker Engine 24+ with the Compose v2 plugin (`docker compose version`)
- On Windows and macOS: Docker Desktop
- At least 8 GB RAM free (16 GB+ recommended for AI generation)
- ~6 GB free disk for the container images and the `qwen3:4b` model

## Local development

For day-to-day development use the development override instead of the plain
production compose file. It bind-mounts the source, serves assets with Vite HMR,
runs a queue worker, and makes `storage/` visible on the host:

```sh
cd docker
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

See [docker-development.md](docker-development.md) for the full workflow.

## 1. Configure the environment

```sh
cd docker
cp .env.example .env
```

`docker/.env` is optional because every variable has a default in
`docker-compose.yml`. The most commonly changed values are `NGINX_HTTP_PORT`
(change from `80` if that port is taken) and `APP_URL`.

Application settings live in `src/.env`. On the first boot the entrypoint copies
`src/.env.example` to `src/.env` and generates an application key if none is set.

## 2. Start the stack

```sh
docker compose up -d --build
```

The order of operations on first boot:

1. `novel-forge-ai-ollama` starts and downloads `qwen3:4b`.
2. `novel-forge-ai-app` runs its entrypoint and startup script:
   1. normalizes `APP_URL` in `src/.env` (adds the trailing slash),
   2. generates the application key when missing,
   3. installs Composer dependencies when `vendor/` is missing,
   4. installs npm dependencies and builds the frontend into `public/build`,
   5. creates the `public/storage` link when missing,
   6. migrates and seeds the database when the schema is missing,
   7. runs `php artisan optimize` to cache config, routes, events, and views,
   8. starts PHP-FPM and reports `healthy`.
3. `novel-forge-ai-nginx` starts only after `novel-forge-ai-app` is healthy and
   serves the application on port `80`, forwarding PHP requests to `app:9000`.

The project is mounted as a named site directory, `novel-forge-ai/`, underneath
`/var/www/html`, so the same Nginx/PHP-FPM pair can host additional sites later:

```
/var/www/html/
  novel-forge-ai/
    .env
    app/
    public/
    routes/
    storage/
```

Follow progress with `docker compose logs -f`. Wait until `docker compose ps`
reports every container as `healthy`.

## 3. Add a hosts entry

`*.localhost` names resolve to `127.0.0.1` on most systems. If
`novel-forge-ai.localhost` does not resolve, add:

```
127.0.0.1  novel-forge-ai.localhost
```

- Windows: `C:\Windows\System32\drivers\etc\hosts`
- macOS / Linux: `/etc/hosts`

You can always use <http://localhost/> instead.

## 4. Open the application

- Application: <http://novel-forge-ai.localhost/>
- Health endpoint: <http://novel-forge-ai.localhost/up>

## Configuration model

Container environment variables override the values in `src/.env`. Laravel's
Dotenv loader is immutable, so once a variable is present in the process
environment it cannot be overridden by the `.env` file. The Compose stack only
sets the values it owns:

| Variable | Default |
| --- | --- |
| `APP_URL` | `http://novel-forge-ai.localhost/` |
| `AI_BRAIN_URL` | `http://ollama:11434` |
| `AI_BRAIN_API_KEY` | `ollama` |
| `OLLAMA_URL` / `OLLAMA_HOST` | `http://ollama:11434` |

`APP_URL` always resolves to a trailing slash. It may include a sub-folder path
(`http://example.com/site/`); the path is preserved and used by `asset()`,
`url()`, `route()`, `Storage::url()`, and Vite assets. To deploy under a
sub-folder, set `APP_URL` accordingly and place this Nginx behind a reverse
proxy that maps the public prefix to the container's port `80`.

Everything else (environment, key, mail, database) is read from `src/.env`. The
database is SQLite at `src/database/database.sqlite`, which persists through the
source bind mount.

The application requires every AI Brain to carry a non-empty API key value.
Ollama does not check it, so `AI_BRAIN_API_KEY=ollama` is used as a placeholder.

## Updating the application

When code changes, pull and redeploy. No image rebuild is required because the
source is bind-mounted into the running container:

```sh
git pull
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app npm run build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

If a new seeder must run, add
`docker compose exec app php artisan db:seed --force`.

## Migrating existing storage

The stack stores `storage/` in the `novel-forge-ai-storage` volume. If you are
upgrading from a setup that kept `storage/` inside `src/` and want to carry the
existing logs or uploaded media across, copy them into the volume once:

```sh
cd docker
docker compose up -d
docker run --rm \
  -v novel-forge-ai-storage:/var/www/html/novel-forge-ai/storage \
  -v "$PWD/../src/storage:/seed:ro" \
  alpine sh -c 'cp -a /seed/. /var/www/html/novel-forge-ai/storage/'
```

The bind-mounted `src/storage` is left untouched.

## Switching models

1. Set `OLLAMA_MODEL` in `docker/.env` (for example `qwen3:8b`).
2. Update the catalog entry in `src/config/ai-brain.php`, then reseed:

   ```sh
   docker compose exec app php artisan db:seed --class=AiBrainSeeder --force
   ```

3. Pull the model into the volume:

   ```sh
   docker compose exec ollama ollama pull qwen3:8b
   ```

## Using an Ollama instance on the host

To use an Ollama server running directly on your machine instead of the
bundled container:

1. Stop the bundled service: `docker compose stop ollama`.
2. In `docker/.env`, point the app at the host:

   ```env
   AI_BRAIN_URL=http://host.docker.internal:11434
   OLLAMA_URL=http://host.docker.internal:11434
   OLLAMA_HOST=http://host.docker.internal:11434
   ```
3. Recreate the app container: `docker compose up -d app`.

`host.docker.internal` is wired up automatically for Linux via the
`host-gateway` mapping in `docker-compose.yml`.

## Stopping and resetting

```sh
docker compose down        # stop containers, keep volumes
docker compose down -v     # stop and delete volumes (full reset of state)
```

To rebuild an image after changing a Dockerfile:

```sh
docker compose build --no-cache
docker compose up -d
```

## Verifying a healthy stack

```sh
docker compose ps
docker compose exec app php artisan migrate:status
docker compose exec app php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'
curl http://127.0.0.1/up
```

See [docker-architecture.md](docker-architecture.md) for how each container is
built, and [troubleshooting.md](troubleshooting.md) if a container does not
become healthy.
