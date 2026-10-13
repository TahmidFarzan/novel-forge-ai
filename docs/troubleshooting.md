# Troubleshooting

Common problems when running Novel Forge AI with Docker, and how to fix them.

## 502 Bad Gateway

Nginx returns `502` when it cannot reach PHP-FPM on `app:9000`. The most common
cause is that the application container is still provisioning (Composer install
or the first asset build) and PHP-FPM is not listening yet; Nginx logs show
`connect() failed (111: Connection refused)`.

The stack prevents this: the `app` entrypoint finishes installing dependencies,
building assets, migrating, and caching before PHP-FPM starts, and Nginx only
starts after the `app` health check succeeds. If you still see a `502`:

```sh
docker compose ps
docker compose logs --tail=50 nginx
docker compose logs --tail=50 app
docker compose exec app php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'
```

- If the `app` container is not `healthy`, wait for provisioning to finish or
  inspect `docker compose logs app`.
- If the editor changed a file outside `src/`, confirm Nginx's root points at
  `/var/www/html/novel-forge-ai/public`.

## The site does not load

- Confirm the hosts entry `127.0.0.1 novel-forge-ai.localhost` exists, or open
  <http://localhost/> instead.
- Check container health: `docker compose ps`. Nginx starts only once the
  application container is healthy and PHP-FPM is accepting connections.
- Watch the boot log: `docker compose logs -f app`.

## Port 80 is already in use

Set a different host port in `docker/.env` and recreate the Nginx container:

```env
NGINX_HTTP_PORT=8080
```

```sh
docker compose up -d nginx
```

Then open <http://localhost:8080/>.

## The app container stays unhealthy for a long time

The first boot runs a Composer install into the `novel-forge-ai-vendor` volume,
an npm install into `novel-forge-ai-node-modules`, and the Vite production
build, all before PHP-FPM starts. The health check allows a 15-minute start
period for this. Follow progress with `docker compose logs -f app`. Later boots
skip the install steps and are fast.

## Ollama is still downloading the model

The model pull happens on first boot only and is stored in the
`novel-forge-ai-ollama-models` volume. Follow it with
`docker compose logs -f ollama` and check availability with:

```sh
docker compose exec ollama ollama list
```

## Assets are missing or stale (`404` on `/build/...`)

The production build output must exist in `public/build`:

```sh
docker compose exec app npm run build
```

If a previous dev run left a `public/hot` file, Laravel tries to load assets
from a dev server. The startup script removes it automatically; remove it
manually if needed:

```sh
docker compose exec app rm -f public/hot
```

## Frontend build fails with a native binding error

Vite (via Rolldown) ships platform-specific native binaries. If `node_modules`
was installed on the host (for example Windows), the Linux container cannot use
those binaries and the build fails with `Cannot find native binding` or
`Cannot find module '@rolldown/binding-linux-x64-musl'`.

The startup script detects this with a marker file and re-runs `npm install` on
the first container boot, which pulls the correct Linux binaries. If you ever
install dependencies on the host again, force the container to re-sync them:

```sh
docker compose exec app sh -c "rm -f node_modules/.novel-forge-ai-container && npm install"
docker compose restart app
```

## Uploaded media or logs are missing after the upgrade

Because `storage/` now lives in the `novel-forge-ai-storage` volume, existing
files kept in `src/storage` are shadowed. Copy them into the volume once, as
described in
[docker-installation.md](docker-installation.md#migrating-existing-storage).

## Permission errors on logs, cache, or the SQLite database

The entrypoint makes `storage/`, `bootstrap/cache/`, `public/build/`, and
`database/database.sqlite` writable by `www-data` on every boot. If a file was
created by a manual `docker compose exec` command running as root, restart the
app container to re-apply ownership:

```sh
docker compose restart app
```

## Ollama connection refused

Laravel must reach Ollama by service name, never `localhost`. Confirm the app
container has the right endpoint:

```sh
docker compose exec app env | grep -i ollama
```

Expected value: `OLLAMA_URL=http://ollama:11434`. If you moved Ollama to the
host, point `AI_BRAIN_URL` and `OLLAMA_URL` at `http://host.docker.internal:11434`
and recreate the app container.

## Clearing everything and starting over

```sh
docker compose down -v
docker compose up -d --build
```

This deletes the storage and model volumes. The model is re-downloaded on the
next boot.

## Windows and macOS performance

Only the application source is bind-mounted, and only for editing. Everything
that is written at runtime lives in Linux-native named volumes:

- `../src` -> `/var/www/html/novel-forge-ai` (bind mount, live edits)
- `novel-forge-ai-vendor` -> `.../vendor` (named volume)
- `novel-forge-ai-node-modules` -> `.../node_modules` (named volume)
- `novel-forge-ai-storage` -> `.../storage` (named volume)

This keeps the tens of thousands of files in `vendor/` and `node_modules/` off
the host/VM filesystem bridge. To get the best performance:

- Keep the repository inside the Linux filesystem. On Windows, clone it into the
  WSL2 filesystem (for example `\\wsl$\Ubuntu\home\<user>\novel-forge-ai`)
  instead of `C:\`.
- Use the development override for HMR. It runs Vite in the dedicated `vite`
  service; the production stack builds assets with `npm run build` during
  provisioning and serves them statically.
- Only the Nginx and Ollama service ports are published, and Nginx mounts the
  project read-only.

## Development stack issues

The development workflow uses
`docker compose -f docker-compose.yml -f docker-compose.dev.yml`. If PHP changes
do not appear, confirm `app` is using the dev entrypoint:

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app env | grep STARTUP_SCRIPT
```

Expected value: `STARTUP_SCRIPT=/usr/local/bin/startup.dev.sh`. The dev
provisioning intentionally skips `php artisan optimize`; if a cached
configuration is suspected, clear it:

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app php artisan optimize:clear
```

If Vite assets or HMR do not load, check the `vite` service and open the app on
the same origin the Vite config uses (`http://novel-forge-ai.localhost/` by
default):

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f vite
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app cat public/hot
```

The `public/hot` file must contain `http://novel-forge-ai.localhost`. If the app
is opened on a different host, set `VITE_DEV_SERVER_ORIGIN` and `VITE_HMR_HOST`
for the `vite` service and `APP_URL` for `app` to the same origin.

Laravel logs for the development stack are written to `src/storage/logs` on the
host because `storage` is bind-mounted (not a named volume) in dev mode.
