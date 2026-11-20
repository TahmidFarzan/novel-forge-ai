# Troubleshooting

Common problems when running Novel Forge AI with DDEV, and how to investigate
them. Commands are for the developer.

> **USER MUST EXECUTE MANUALLY.** Nothing in this guide was executed by the
> migration agent. Solutions are based on the implemented configuration, not on
> runtime verification.

## Docker and DDEV

### Docker Desktop is not running

`ddev start` cannot create containers when the Docker engine is unavailable.
Start Docker Desktop, wait until it reports running, then verify:

```sh
docker version
ddev version
```

### `ddev` is not recognised

Install DDEV and reopen the terminal so `PATH` is refreshed. See
<https://ddev.com/get-started/>.

### Wrong directory

DDEV commands must run from the project root that contains `.ddev/`:

```text
repository/src/
```

Running `ddev start` from the repository root fails because `.ddev/` is inside
`src/`.

### Project will not start

```sh
ddev describe
ddev logs
ddev restart
```

Check `src/.ddev/config.yaml` for invalid YAML or unsupported values. The
project uses `webserver_type: nginx-fpm`, PHP 8.4, Node.js 24, and
`omit_containers: [db]`. A database container is deliberately absent; do not add
one expecting SQLite to use it.

### Port 80 or 443 is already in use

The DDEV router binds host ports 80 and 443. A legacy Docker stack, IIS, or
another web server may already own them. Stop the conflicting service, then:

```sh
ddev poweroff
ddev start
```

The legacy `docker/` stack publishes Nginx on port 80; do not run it at the same
time as DDEV.

### Vite port conflict

Vite is exposed on 5172 (HTTP) and 5173 (HTTPS). If another process or DDEV
project owns those ports, stop it or change the exposed ports in
`src/.ddev/config.yaml`, then `ddev restart`.

### Hostname or certificate warning

Use <https://novel-forge-ai.ddev.site>. Run `ddev launch` to open the URL DDEV
considers authoritative. If the certificate is not trusted, run
`ddev restart`; DDEV manages project certificates through `mkcert`. Do not
switch to plain `localhost` without updating `APP_URL` and the Vite origin.

### Containers start but the site does not load

```sh
ddev describe
ddev logs -s web
curl -I https://novel-forge-ai.ddev.site/up
```

Confirm the web container is running and nginx is listening. The document root
is `src/public`.

## Database (SQLite)

### `Database file at path [...] does not exist`

Create the file and migrate:

```sh
touch database/database.sqlite
ddev artisan migrate --seed
```

On Windows PowerShell, `New-Item database/database.sqlite` is equivalent.

### Database is locked or writes fail

SQLite allows one writer at a time. A long queue job or a second process may
hold the lock. Check:

```sh
ddev logs -s web
ddev artisan migrate:status
```

Stop the project, confirm no second writer is running, then restart. Do not
delete `database.sqlite` to "fix" a lock.

### Permission errors on the database or storage

DDEV normally manages ownership. If a file was created with the wrong owner,
inspect it inside the container:

```sh
ddev ssh
ls -l database storage bootstrap/cache
```

Fix ownership for the web user, then `ddev restart`. Do not run broad
`chmod -R 777` commands on the project.

### DDEV database commands are unavailable

`ddev snapshot`, `ddev export-db`, `ddev import-db`, and `ddev mysql` require the
database container, which is omitted. Back up the SQLite file directly; see
[Development environment](development-environment.md#backup-and-restore).

## PHP

### Wrong PHP version

```sh
ddev exec php -v
```

The project pins `php_version: "8.4"`. Change it in `src/.ddev/config.yaml` and
run `ddev restart`. Do not install a host PHP version and expect DDEV to use it.

### Missing extension

```sh
ddev exec php -m
```

The DDEV PHP 8.4 image should provide `redis`, `memcached`, `pdo_sqlite`,
`mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif`, `opcache`, and `pcntl`.
If one is missing, check the DDEV version and the web image before adding
packages. Extension loading is **NOT RUNTIME TESTED — USER MANUAL VERIFICATION
REQUIRED.**

### Memory limit exhausted

Look for `Allowed memory size ... exhausted`. Check the effective value:

```sh
ddev exec php -i
```

`src/.ddev/php/php.ini` sets `memory_limit = 1024M`. After editing it, run
`ddev restart`. Raising it further is possible but this host has 7.68 GB total,
so leave headroom for Docker and the AI model.

### Upload rejected (413 or "file too large")

Three limits must agree:

- `upload_max_filesize = 128M`
- `post_max_size = 128M`
- nginx `client_max_body_size 128m` in `src/.ddev/nginx_full/nginx-site.conf`

After editing nginx or PHP settings, run `ddev restart` and check with a file
near the limit.

### Request times out (504)

The project allows long AI requests:

- PHP `max_execution_time = 1800`
- nginx `fastcgi_read_timeout` / `fastcgi_send_timeout` = `1800s`
- Application timeout `AI_BRAIN_TIMEOUT_SECONDS=1500`

A 504 usually means one layer is shorter than the request. Confirm the effective
nginx config with `ddev exec nginx -t` and restart. Do not raise limits blindly;
long CPU-only generations are expected to be slow.

### PHP changes do not appear

OPcache validates timestamps on every request in development
(`validate_timestamps=1`, `revalidate_freq=0`). If a change still does not
appear:

```sh
ddev artisan optimize:clear
ddev restart
```

## Redis

### Redis service is unavailable

```sh
ddev describe
ddev logs -s redis
```

The service is defined in `src/.ddev/docker-compose.redis.yaml` and is
project-scoped. If it is not listed, the compose file may not have been applied;
run `ddev restart`.

### Connection refused or wrong host

Inside DDEV the hostname is `redis` and the port is `6379`. Do not use
`127.0.0.1`; that points at the web container itself. DDEV supplies:

```env
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
```

If you run the application outside DDEV, set the host explicitly to the
published location (normally `127.0.0.1`) — but Redis is not published to the
host in this project, so external access is not configured.

### PHP Redis extension missing

```sh
ddev exec php -m
```

If `redis` is absent, see [Missing extension](#missing-extension). Do not switch
to `predis` unless a dependency decision is made; the project selects
`phpredis`.

## Memcached

### Memcached service is unavailable

```sh
ddev describe
ddev logs -s memcached
```

The service is defined in `src/.ddev/docker-compose.memcached.yaml`. It exposes
port `11211` internally only.

### Connection refused or wrong host

Use `memcached` and `11211`. DDEV supplies:

```env
MEMCACHED_HOST=memcached
MEMCACHED_PORT=11211
```

`127.0.0.1` is wrong inside the web container.

### PHP Memcached extension missing

```sh
ddev exec php -m
```

If `memcached` is absent, see [Missing extension](#missing-extension).

## Frontend

### Node.js or npm mismatch

DDEV supplies Node.js 24. Check:

```sh
ddev exec node -v
ddev exec npm -v
```

Do not run host `npm install` and expect the Linux container to use those
binaries. Install dependencies with `ddev npm install`.

### Native binding error during build

If `node_modules` was produced on the host (for example Windows), platform
binaries may not match the Linux container. Reinstall inside the container:

```sh
ddev exec rm -rf node_modules
ddev npm install
ddev npm run build
```

This only removes dependency files, not application data.

### Vite does not start

```sh
ddev npm run dev
ddev logs -s web
```

Confirm port 5173 is free and the project is running. Vite uses
`strictPort: true`, so it fails rather than choosing another port.

### HMR does not connect / mixed content

The frozen `src/vite.config.js` hardcodes `protocol: 'ws'`. On the HTTPS origin,
browsers may block the insecure WebSocket. The configured HTTP fallback is
`http://novel-forge-ai.ddev.site:5172`. Test both origins and, if HMR remains
unavailable, use:

```sh
ddev npm run build
```

and refresh the page. Fixing this properly requires editing protected Vite
source, which is out of scope. Record the actual result in Step 8.

### Stale `public/hot`

A leftover `public/hot` makes Laravel look for a dev server that is not running.
Remove it and rebuild:

```sh
ddev exec rm -f public/hot
ddev npm run build
```

## Ollama and Qwen3

Ollama is independent of DDEV. See [ollama/README.md](../ollama/README.md) for
the authoritative operational guide.

### Ollama is unavailable

Start the stack manually:

```powershell
cd ollama
.\scripts\start.ps1
.\scripts\list-models.ps1
```

Check the host endpoint: <http://127.0.0.1:11434/api/tags>.

### Model not found

```powershell
cd ollama
.\scripts\pull-qwen3.ps1
```

The default is `qwen3:4b`. Confirm `AI_BRAIN_MODEL` in `src/.env` matches the
pulled tag.

### Insufficient RAM or slow generation

This host has 7.68 GB RAM and no supported GPU. `qwen3:4b` needs roughly 2.5 GB
for the model plus runtime overhead, while the application and Docker also use
memory. Close other workloads, keep `OLLAMA_NUM_PARALLEL=1` and
`OLLAMA_MAX_LOADED_MODELS=1`, and expect CPU-only latency. A smaller model can
be selected manually in `ollama/.env`, but update `AI_BRAIN_MODEL` and the AI
brain catalog consistently.

### GPU unavailable

There is no NVIDIA GPU or ROCm support on this host. CPU is the implemented
fallback. GPU acceleration is documented for other hosts only; do not add a GPU
override here.

### Model storage or volume issues

Models live in the Docker volume `novel-forge-ai-ollama-models`, mounted at
`/root/.ollama`. If models disappear after recreation, confirm the compose file
still uses the explicit volume name. Do not delete the volume unless you intend
to re-download models. Full purge requires the typed phrase
`PURGE OLLAMA DATA`.

### DDEV cannot reach Ollama

The application uses:

```env
AI_BRAIN_URL=http://host.docker.internal:11434
```

A fresh clone copied from `src/.env.example` still has
`AI_BRAIN_URL=http://localhost:11434`; inside the web container that points at
the container itself. Change it to `host.docker.internal` as shown above.

Verify the host endpoint first. From inside the web container, test
`host.docker.internal:11434` with `ddev ssh` and an available HTTP client. If the
name does not resolve, the Docker Desktop configuration may not expose the host
gateway; this is **NOT RUNTIME TESTED — USER MANUAL VERIFICATION REQUIRED.**
Never publish Ollama on a LAN interface to work around it.

## Timeouts and long requests

- AI request timeout: `AI_BRAIN_TIMEOUT_SECONDS=1500` in `src/.env`.
- Connect timeout: `AI_BRAIN_CONNECT_TIMEOUT_SECONDS=10`.
- PHP and FastCGI: 1800 seconds.
- Queue retry window: `DB_QUEUE_RETRY_AFTER=1800`.

If a request fails before these limits, check the application log, the nginx
log, and the Ollama container log rather than increasing every timeout.

## Migration from the legacy Docker stack

1. Stop the legacy stack before starting DDEV; both may want ports 80/443.
2. Keep `src/database/database.sqlite`; it is the live application database.
3. Uploaded media under `src/storage/app/public` remains on the host.
4. The legacy Docker storage volume is not used by DDEV. Copy any files that
   only exist in that volume before removing it (Step 6 territory).
5. Do not delete `docker/`, volumes, or backups during troubleshooting.

## Resetting safely

```sh
ddev stop
ddev start
```

`ddev restart` recreates containers without deleting the SQLite database.
Avoid `ddev delete` unless you intentionally want to remove project containers;
it is not part of normal troubleshooting. Never delete `src/database`,
`src/storage`, uploads, or the Ollama volume to fix a configuration problem.

## Related documentation

- [Installation](installation.md)
- [Development environment](development-environment.md)
- [PHP and OPcache](php-configuration.md)
- [Redis and Memcached](cache-services.md)
- [Ollama and Qwen3](../ollama/README.md)
