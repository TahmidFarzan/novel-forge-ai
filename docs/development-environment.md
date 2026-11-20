# Development Environment

This guide describes the daily DDEV workflow for Novel Forge AI.

> **Authoritative DDEV approot:** the **repository root** (the directory that contains `.ddev/config.yaml`).  
> **Laravel application directory:** `src/` inside that approot.  
> **Document root:** `src/public` (relative to the approot; served as `/var/www/html/src/public` in the container).  
> **Drive/location:** the project must be started from its actual location. Do not hardcode an absolute project path. The startup helper derives the allowed drive at runtime from its own location (this checkout is currently on `C:`, but the helper is move-resilient on that drive).

Run DDEV commands from the discovered repository root, or use the dynamic startup helper (recommended).

## Why DDEV root mismatch happens

DDEV stores each project's registered approot in its global registry (for example `~/.ddev/project_list.yaml` and `ddev list` output). If the project folder is moved, or if an older setup registered a different approot (such as a nested `src/` directory), DDEV can refuse to start with an error like:

```text
project root is already set to an old location,
refusing to change it to the current location.
```

The helper script recovers from this **safely**: it unlists only the confirmed stale registration for `novel-forge-ai` (`ddev stop --unlist novel-forge-ai`) and then starts DDEV from the verified current root. It never deletes data.

## Dynamic startup helper (recommended)

The helper scripts live in `scripts/` at the repository root. They resolve the project root dynamically from `$PSScriptRoot`, validate the authoritative `.ddev/config.yaml`, verify the Laravel markers in `src/` (`artisan`, `composer.json`, `public/index.php`), detect stale DDEV registration, and start the project.

From the discovered repository root:

```powershell
.\scripts\ddev-start.ps1
```

Dry-run (discovery, validation, registration status, and planned action only — no start/stop/unlist/config changes):

```powershell
.\scripts\ddev-start.ps1 -DryRun
```

Read-only verification of discovery, config files, nested `.ddev` presence, absolute-path prohibition, and protected-source Git status:

```powershell
.\scripts\ddev-verify-root.ps1
```

## After moving the project folder

1. Open the project at its new location (on the same drive the scripts were designed/validated for, or pass `-AllowedDrive` to the helper if you intentionally relocate across drives and re-validate).
2. Run `.\scripts\ddev-start.ps1` (or `.\scripts\ddev-start.ps1 -DryRun` first).
3. The script discovers the new authoritative root and validates markers.
4. If DDEV's global registry still points at the old approot, the script safely refreshes only that registration.
5. DDEV starts using the new root.

> Note: DDEV's global registry always stores an absolute approot path internally. The helper handles that automatically; it does **not** change DDEV's internal registry design.

## Runtime topology

```text
Browser
   │  https://novel-forge-ai.ddev.site
   ▼
DDEV router
   ▼
web container (nginx-fpm)
   ├─ nginx :80/:443  →  PHP-FPM 8.4 (Laravel application in src/)
   ├─ Node.js 24 / Vite dev server (exposed 5173 HTTPS, 5172 HTTP)
   ├─ queue daemon: php artisan queue:listen --tries=1 --timeout=0 (cwd /var/www/html/src)
   ├─ Redis 8 sidecar      (redis:6379, internal only)
   └─ Memcached 1.6 sidecar (memcached:11211, internal only)

src/database/database.sqlite  — SQLite, inside the project (persists across stop/start)
Ollama DDEV service (ollama:11434, started by `ddev start`, model provisioned on start)
```

DDEV manages the web container, the Redis/Memcached sidecars, and the Ollama service; the post-start hook provisions the configured Qwen3 model when it is missing.

## Project management

| Command | Purpose |
| --- | --- |
| `.\scripts\ddev-start.ps1` | Discover root, recover stale registration if needed, start DDEV (recommended) |
| `.\scripts\ddev-start.ps1 -DryRun` | Show discovery/registration/planned action without changing state |
| `ddev start` | Start the project (run from the discovered repository root) |
| `ddev stop` | Stop the project containers |
| `ddev restart` | Recreate containers after config changes |
| `ddev describe` | Show URLs, PHP/Node versions, services, and exposed ports |
| `ddev launch` | Open the project in the default browser |
| `ddev logs -s web` | Show web container logs (nginx, PHP-FPM, queue daemon) |
| `ddev logs -f` | Follow logs |
| `ddev ssh` | Open a shell in the web container |
| `ddev poweroff` | Stop all running DDEV projects |

Run `ddev` commands from the **repository root** (where `.ddev/config.yaml` lives), unless a command is explicitly executed inside the container/app directory. Use `ddev describe` first when a URL, port, or service is unclear. The primary URL is <https://novel-forge-ai.ddev.site>; the health endpoint is `/up`.

## PHP management

- PHP version: **8.4**, set by `php_version` in `.ddev/config.yaml`.
- Project PHP settings: `.ddev/php/php.ini` (PHP and OPcache together).
- Extensions: the DDEV PHP 8.4 image already includes `redis`, `memcached`, `pdo_sqlite`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif`, `opcache`, and `pcntl`. No extra package or Dockerfile is used.
- Inspect: `ddev exec php -v`, `ddev exec php --ini`, `ddev exec php -m`, `ddev exec php -i`.
- Laravel artisan from the repository root: `ddev exec php src/artisan --version` (or `ddev ssh` and `cd src`).

Full settings: [PHP and OPcache](php-configuration.md). Extension loading is **NOT RUNTIME TESTED - USER MANUAL VERIFICATION REQUIRED** unless recorded otherwise.

## Database management

The project uses SQLite, not a database server. The DDEV database container is omitted in `config.yaml`:

```ini
DB_CONNECTION=sqlite
```

- File: `src/database/database.sqlite`
- Default path: Laravel's `database/database.sqlite` when `DB_DATABASE` is unset.
- Persistence: the file is inside the project and survives `ddev stop` / `ddev start`.
- Migrations: `ddev exec php src/artisan migrate`, `ddev exec php src/artisan migrate:status`, `ddev exec php src/artisan migrate --seed`.
- Backup: stop the project, copy `src/database/database.sqlite` (and any `-wal` / `-shm` files) to a safe location.
- Restore: stop the project, copy the backup back to `src/database/database.sqlite`, then start the project.

Because the database container is omitted, these DDEV features are unavailable: `ddev snapshot`, `ddev restore-snapshot`, `ddev export-db`, `ddev import-db`, and `ddev mysql`.

Do not run destructive commands such as `migrate:fresh` against a database you need to keep.

## Frontend

Node.js 24 is provided by DDEV (`nodejs_version` in `.ddev/config.yaml`). The package manager is **npm** (`src/package-lock.json`).

| Command | Purpose |
| --- | --- |
| `ddev npm --dir src install` | Install frontend dependencies |
| `ddev npm --dir src run build` | Build production assets into `src/public/build` |
| `ddev npm --dir src run dev` | Start the Vite dev server |
| `ddev npm --dir src run dev -- --host` | Explicitly bind the dev server (already `0.0.0.0` in `vite.config.js`) |

(You may also `ddev ssh` and run npm from `/var/www/html/src`.)

Vite uses the frozen `src/vite.config.js`:

- Port `5173`, `strictPort: true`, host `0.0.0.0`.
- Origin from `VITE_DEV_SERVER_ORIGIN` (`https://novel-forge-ai.ddev.site:5173`).
- HMR host `novel-forge-ai.ddev.site`, client port `5172`, protocol `ws`, path `/vite-hmr`.

DDEV exposes the Vite port as:

- HTTPS: `https://novel-forge-ai.ddev.site:5173`
- HTTP fallback: `http://novel-forge-ai.ddev.site:5172`

**Known limitation:** the `ws://` protocol is hardcoded in the frozen `vite.config.js`. Browsers block insecure WebSockets from an HTTPS page as mixed content, so HMR may not connect on the HTTPS origin. The supported fallback is `ddev npm --dir src run build` plus a normal page refresh. Fixing HMR properly would require editing the protected Vite source, which is out of scope.

## Queue worker

`.ddev/config.yaml` defines a web-container daemon:

```yaml
web_extra_daemons:
  - name: queue
    command: php artisan queue:listen --tries=1 --timeout=0
    # Container path is relative to the DDEV approot; artisan lives in src/.
    directory: /var/www/html/src
```

- Connection: `QUEUE_CONNECTION=database`.
- Retry window: `DB_QUEUE_RETRY_AFTER=1800`, matching long AI requests.
- Logs: `ddev logs -s web` (the daemon is managed by supervisord).
- After changing queue-related configuration, run `ddev restart`.
- Failed jobs: `ddev exec php src/artisan queue:failed`; retry with `ddev exec php src/artisan queue:retry all`.

## Logs and debugging

```powershell
ddev logs -s web          # nginx, PHP-FPM, queue daemon
ddev logs -f              # follow all project logs
ddev ssh                  # inspect files inside the container
```

Laravel writes to `src/storage/logs/laravel-YYYY-MM-DD.log` (the `daily` channel).

Useful diagnostics:

```powershell
ddev describe
ddev exec php -v
ddev exec php --ini
ddev exec php -m
ddev exec php src/artisan about
ddev exec php src/artisan migrate:status
```

## Multiple projects

Run DDEV from each project's own discovered repository root. DDEV scopes containers, PHP versions, services, and hostnames per project. Changing one project's `config.yaml` does not affect another project. Use `ddev list` (read-only) to see running projects.

## Backup and restore

| Data | Location | Backup |
| --- | --- | --- |
| SQLite database | `src/database/database.sqlite` | Copy while the project is stopped |
| Uploaded media | `src/storage/app/public` | Copy the directory |
| Laravel logs | `src/storage/logs` | Copy if needed for diagnosis |
| Environment | `src/.env` | Copy securely; never commit |
| AI models | Docker volume `novel-forge-ai-ollama-models` | See [ollama/README.md](../ollama/README.md) |

Redis data is a cache/queue sidecar and is not part of the application backup strategy. Memcached is memory-only.

## Commands that must NEVER be used for this recovery

- `ddev delete` — destructive (can remove project data). **Never use.**
- `ddev stop --remove-data` — destructive. **Never use.**
- `docker volume rm`, `docker system prune`, or any Docker cleanup that can remove volumes/images — **never use.**
- Manual edits to global DDEV configuration/registry files — **do not do.**

For stale registration recovery, the only supported safe action used by the helper is: `ddev stop --unlist novel-forge-ai` (registration only; no data deleted), followed by `ddev start` from the verified current root.

## Manual runtime verification checklist

> **USER MUST EXECUTE MANUALLY.** Record actual results in the tracking documents. Do not mark a check as passed based on documentation alone.

1. `docker version` and `ddev version` succeed.
2. `.\scripts\ddev-start.ps1 -DryRun` shows the correct discovered root, project name, docroot, and registration status.
3. `.\scripts\ddev-start.ps1` completes and `ddev describe` shows the project URL and `Docroot: 'src/public'`.
4. `ddev list` shows `novel-forge-ai` registered at the current repository root.
5. `https://novel-forge-ai.ddev.site/up` returns a healthy response.
6. `ddev exec php src/artisan --version` succeeds.
7. `ddev exec php src/artisan migrate:status` shows no pending migrations.
8. `ddev npm --dir src run build` completes and `src/public/build` is populated.
9. `ddev npm --dir src run dev` starts Vite; test HMR and record the mixed-content limitation.
10. `ddev exec php src/artisan test` passes.
11. `ddev exec php -m` lists `redis` and `memcached`; cache read/write checks succeed.
12. `ddev logs -s web` shows the queue daemon running.
13. Start the Ollama stack if needed and test generation.
14. Stop and restart DDEV; confirm the SQLite database and uploads persist.
15. After moving the folder on the allowed drive, rerun `.\scripts\ddev-start.ps1` and confirm automatic stale-registration recovery.
