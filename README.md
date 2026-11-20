# Novel Forge AI

Novel Forge AI is a Laravel-based, AI-assisted writing system for generating
novel foundations, chapter plans, and chapter content. Generation runs against a
local Ollama server using Qwen3, so no writing data leaves your machine.

Development runs on **DDEV** (Docker under the hood). The AI runtime is an
optional, independently managed Docker stack.

## Tech stack

| Layer | Technology |
| --- | --- |
| Development environment | DDEV (current release), Docker Desktop |
| Backend | PHP 8.4, Laravel 13 |
| Frontend | Vue 3, Inertia.js, Ziggy, Tailwind CSS 4, Vite 8 |
| Runtime | Node.js 24 (managed by DDEV), npm |
| Database | SQLite (`src/database/database.sqlite`) |
| Cache / queue services | Redis 8 and Memcached 1.6 (optional, project-scoped) |
| AI runtime | Docker-only Ollama (`ollama/ollama:0.40.16`) with `qwen3:4b` |

No host PHP, Composer, Node.js, Redis, or Memcached installation is required for
DDEV development.

## Repository layout

```text
.
├── .ddev/                  Authoritative DDEV config (approot = repository root)
│   ├── config.yaml         DDEV project config (docroot: src/public)
│   ├── php/php.ini         PHP/OPcache settings
│   ├── nginx_full/         Nginx overrides
│   ├── redis/              Redis config
│   └── docker-compose.*    Redis/Memcached sidecars
├── scripts/
│   ├── ddev-start.ps1      Dynamic root discovery + safe stale-registration recovery
│   └── ddev-verify-root.ps1 Read-only verification
├── src/                    Laravel application source
│   ├── app/                Application code (services, models, controllers)
│   ├── config/ai-brain.php AI model catalog
│   ├── database/           Migrations, seeders, SQLite database
│   ├── public/             Web document root (served as src/public)
│   ├── resources/          Vue pages/components, CSS, Inertia entrypoint
│   ├── routes/             web.php and console.php
│   └── tests/              Unit and Feature tests
├── ollama/                 Independent Docker-only Ollama + Qwen3 stack
├── docs/                   Project documentation
├── archive/legacy-docker/  Step 6 snapshot of the retired Docker dev config
└── docker/                 Production-style Docker packaging (dev override archived)
```

**DDEV approot:** repository root (`.ddev/config.yaml`). **Laravel app:** `src/`. **Docroot:** `src/public`.
Run DDEV commands from the repository root, or use `.\scripts\ddev-start.ps1` (recommended). Do not start DDEV from `src/`.

## Prerequisites

- Docker Desktop (running) with the Compose v2 plugin.
- DDEV (current release).
- Git.
- At least 8 GB RAM for the application; more is recommended when running the
  local AI model (the host used for migration has 7.68 GB and runs CPU-only).

Install DDEV: <https://ddev.com/get-started/>. Install Docker Desktop:
<https://www.docker.com/products/docker-desktop/>.

## Quick start (DDEV)

> **USER MUST EXECUTE MANUALLY.** The migration agent did not run these
> commands. Do not hardcode an absolute project path; the helper resolves the root dynamically.

```powershell
git clone <repository-url> novel-forge-ai
cd novel-forge-ai   # repository root (authoritative DDEV approot)

# Only when src/.env does not exist yet; never overwrite an existing .env.
Copy-Item src\.env.example src\.env
# Fresh clones: in src\.env set AI_BRAIN_URL=http://host.docker.internal:11434,
# AI_BRAIN_CONTEXT_WINDOW=8192, AI_BRAIN_TIMEOUT_SECONDS=1500 (see Installation).

# Recommended: discovers the root, recovers stale DDEV registration if needed, and starts DDEV.
.\scripts\ddev-start.ps1

ddev composer install --working-dir=src
ddev npm --dir src install

# Only when APP_KEY is empty.
ddev exec php src/artisan key:generate

ddev exec php src/artisan migrate --seed
ddev exec php src/artisan storage:link
ddev npm --dir src run build
```

Use `.\scripts\ddev-start.ps1 -DryRun` to inspect discovery/registration without changing state.

Open <https://novel-forge-ai.ddev.site/> (or run `ddev launch`). The health
endpoint is <https://novel-forge-ai.ddev.site/up>.

For live frontend development use `ddev npm run dev` in a separate terminal.
See [Development environment](docs/development-environment.md) for the full
workflow and the Vite/HMR limitation.

## Development commands

Run from the **repository root** (discovered dynamically), or use the helper scripts:

| Command | Purpose |
| --- | --- |
| `.\scripts\ddev-start.ps1` | Discover root, recover stale DDEV registration if needed, start DDEV |
| `.\scripts\ddev-start.ps1 -DryRun` | Inspect discovery/registration/planned action (no changes) |
| `.\scripts\ddev-verify-root.ps1` | Read-only verification of root/config/registration markers |
| `ddev start` | Start the project containers |
| `ddev stop` | Stop the project containers |
| `ddev restart` | Recreate after configuration changes |
| `ddev describe` | Show URLs, PHP/Node versions, and services |
| `ddev launch` | Open the project in a browser |
| `ddev logs -s web` | Show web container logs, including the queue daemon |
| `ddev ssh` | Open a shell in the web container |
| `ddev composer --working-dir=src <args>` | Run Composer for the Laravel app in `src/` |
| `ddev npm --dir src <args>` | Run npm for the frontend in `src/` |
| `ddev exec php src/artisan <args>` | Run `php artisan` for the Laravel app in `src/` |
| `ddev exec <command>` | Run any command in the web container |
| `ddev exec php src/artisan test` | Run the PHPUnit suite |

Full list: [Command reference](docs/command-reference.md).

## Frontend

Vue, Inertia, Ziggy, Tailwind CSS 4, and Vite are built inside the web
container. `ddev npm run build` writes production assets to `src/public/build`.

For the dev server, run `ddev npm run dev` and open the project. Vite is exposed
on `https://novel-forge-ai.ddev.site:5173` (HTTP fallback `:5172`). Because the
frozen `src/vite.config.js` hardcodes the `ws://` HMR protocol, hot reload may be
blocked as mixed content on the HTTPS page; the build workflow above is the
reliable fallback. This is **NOT RUNTIME TESTED — USER MANUAL VERIFICATION
REQUIRED** (Step 8).

## Database

The application uses SQLite at `src/database/database.sqlite`. The DDEV database
container is intentionally omitted:

```ini
DB_CONNECTION=sqlite
```

The SQLite file lives inside the DDEV project, so it persists across
`ddev stop` / `ddev start`. Back it up by copying the file while the project is
stopped. DDEV database snapshots, `ddev export-db`, and `ddev mysql` are not
available with this configuration.

## PHP and cache services

- [PHP and OPcache configuration](docs/php-configuration.md)
- [Redis and Memcached](docs/cache-services.md)

Defaults remain database-backed (`CACHE_STORE=database`,
`SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`). Redis and Memcached are
available and can be selected per project in `src/.env`.

> **DDEV startup safety:** use `.\scripts\ddev-start.ps1` to avoid stale project-root registration after moving the folder. Never use `ddev delete`, `ddev stop --remove-data`, or Docker volume/image cleanup for this recovery.

## AI infrastructure (DDEV-managed Ollama)

Ollama runs as a DDEV service — service name `ollama`, image `ollama/ollama:0.40.16`,
model volume `novel-forge-ai-ollama-models` — so it starts automatically with `ddev start`
and is reached from Laravel at `http://ollama:11434` via the `web_environment` in
`.ddev/config.yaml`.

On every `ddev start`, a post-start hook (`.ddev/ollama-provision.sh`) waits for the
service, checks the configured model, and pulls `qwen3:4b` when it is missing. Existing
models are reused, so nothing is downloaded repeatedly.

After enabling this, refresh the stored endpoint so the AI status slider reports
correctly:

```powershell
ddev exec php src/artisan migrate --seed
```

The standalone stack under `ollama/` still exists and shares the same model volume, so
models downloaded there are reused — but do not run both stacks at the same time (the
standalone stack publishes `127.0.0.1:11434` on the host, as does the DDEV service).
See [ollama/README.md](ollama/README.md) for the standalone lifecycle, model management,
security, storage, and purge procedures.

## Documentation

- [Installation](docs/installation.md) — DDEV prerequisites and first-time setup
- [Development environment](docs/development-environment.md) — daily workflow, services, backups, runtime checklist
- [PHP and OPcache](docs/php-configuration.md)
- [Redis and Memcached](docs/cache-services.md)
- [Command reference](docs/command-reference.md)
- [Testing](docs/testing.md)
- [Troubleshooting](docs/troubleshooting.md)
- [System architecture](docs/system-architecture.md) — application, domain, and AI pipeline
- [Ollama and Qwen3](ollama/README.md)
- [Manual (bare-metal) installation](docs/manual-installation.md) — alternative to DDEV
- [Legacy Docker packaging](docker/README.md) — production/rollback reference only
- [Archived Docker development config](archive/legacy-docker/README.md) — Step 6 snapshot, not active

## Legacy Docker and production

`docker/` contains the previous production-style Nginx/PHP-FPM/queue/Ollama
packaging. It is preserved for production and rollback reference and is **not**
the development workflow. Step 6 removed the development-only Compose override
and Vite dev nginx config from `docker/` and archived the full snapshot under
[`archive/legacy-docker/`](archive/legacy-docker/README.md). Do not expose the
development Ollama port publicly; see [ollama/README.md](ollama/README.md).

## AI model configuration

The model catalog lives in `src/config/ai-brain.php` and is seeded into the
database. The active endpoint is read from `AI_BRAIN_URL` (with `OLLAMA_URL` as
a fallback). The current local setup uses a 8192-token context window and a
1500-second request timeout, sized for CPU-only generation on this host.

## License

Novel Forge AI is released under the MIT license.
