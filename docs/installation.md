# Installation

This guide covers the DDEV-based development installation of Novel Forge AI.
The application itself lives in `src/`; the DDEV project root is `src/`, not the
repository root.

> **USER MUST EXECUTE MANUALLY.** Every command in this guide is intended for
> the developer. The migration agent did not start Docker or DDEV, install
> dependencies, pull images, or run migrations.

## 1. Prerequisites

| Requirement | Notes |
| --- | --- |
| Docker Desktop | Running, with the Compose v2 plugin. On Windows, use the WSL2 backend. |
| DDEV | A current DDEV release. Provides PHP, Node.js, nginx, Composer, and the database/cache containers. |
| Git | To clone the repository. |
| Disk / memory | ~8 GB RAM for the application; more for local AI generation. |

Do **not** install PHP, Composer, Node.js, Redis, or Memcached on the host for
DDEV development. DDEV provides them inside the web container. A native
toolchain is only needed for the optional [manual installation](manual-installation.md).

Verify Docker and DDEV after installation:

```sh
docker version
ddev version
```

If Docker Desktop is not running, DDEV cannot start a project.

## 2. Clone the repository

```sh
git clone <repository-url> novel-forge-ai
cd novel-forge-ai/src
```

`src/` contains `.ddev/`, `composer.json`, `package.json`, and the Laravel
application.

## 3. Review the environment file

`src/.env` is Git-ignored. If it does not exist, create it from the example:

```sh
cp .env.example .env
```

Never overwrite an existing `src/.env`; it contains the local `APP_KEY` and
local settings. The DDEV project is configured with
`disable_settings_management: true`, so DDEV does not rewrite `APP_URL` or mail
values in your `.env`.

Key values for the DDEV environment:

```env
APP_URL=https://novel-forge-ai.ddev.site
DB_CONNECTION=sqlite
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
AI_BRAIN_URL=http://host.docker.internal:11434
AI_BRAIN_MODEL=qwen3:4b
AI_BRAIN_CONTEXT_WINDOW=8192
AI_BRAIN_MAX_OUTPUT_TOKENS=8192
AI_BRAIN_TIMEOUT_SECONDS=1500
```

**Fresh-clone note:** `src/.env.example` is a portable template and still uses
`AI_BRAIN_URL=http://localhost:11434`, a 32768 context window, and a 600-second
timeout. After copying it, update those values to the DDEV values above.
`localhost` inside the web container points at the container itself, not the
host, and 32768 context is too large for this CPU-only host. The existing local
`src/.env` used for this migration already has the DDEV values.

The DDEV configuration also supplies `APP_URL`, Vite/HMR variables, queue retry
settings, and the Redis/Memcached hostnames to the web container. See
[Development environment](development-environment.md) for details.

## 4. Start DDEV

```sh
ddev start
```

The project name and hostname are defined in `src/.ddev/config.yaml`:

- Project: `novel-forge-ai`
- URL: <https://novel-forge-ai.ddev.site>
- Health endpoint: <https://novel-forge-ai.ddev.site/up>
- PHP: 8.4
- Node.js: 24
- Web server: nginx-fpm
- Database container: omitted (SQLite is used)

The first start pulls the DDEV images. This requires network access and may take
several minutes.

## 5. Install application dependencies

```sh
ddev composer install
ddev npm install
```

Both commands run inside the web container, so host PHP/Node versions do not
matter.

## 6. Prepare the application

```sh
# Only when APP_KEY is empty.
ddev artisan key:generate

ddev artisan migrate --seed
ddev artisan storage:link
```

Migrations create the SQLite schema at `src/database/database.sqlite`. Seeders
populate permissions, users, genres, languages, AI brains, prompts, and
generation steps.

## 7. Build or serve frontend assets

Production-style build:

```sh
ddev npm run build
```

Live development server (separate terminal):

```sh
ddev npm run dev
```

See [Development environment](development-environment.md#frontend) for the
Vite/HMR limitation on this project.

## 8. Open the application

```sh
ddev launch
```

Or open <https://novel-forge-ai.ddev.site/> directly. Confirm `/up` returns a
healthy response.

## 9. Optional: start the AI runtime

The AI stack is independent of DDEV and optional for booting the application.
It is Docker-only; do not install Ollama natively.

```powershell
cd ..\ollama
.\scripts\start.ps1
.\scripts\pull-qwen3.ps1
```

Then confirm `src/.env` points at `http://host.docker.internal:11434`. Full
instructions, model management, and security notes are in
[ollama/README.md](../ollama/README.md).

## Environment configuration reference

| Variable | Source | Purpose |
| --- | --- | --- |
| `APP_URL` | DDEV `web_environment` | `https://novel-forge-ai.ddev.site` |
| `DB_CONNECTION` | `src/.env` | `sqlite` |
| `DB_DATABASE` | unset | Defaults to `database/database.sqlite` |
| `CACHE_STORE` | `src/.env` | `database` by default; Redis/Memcached opt-in |
| `SESSION_DRIVER` | `src/.env` | `database` by default |
| `QUEUE_CONNECTION` | `src/.env` | `database` by default |
| `REDIS_CLIENT` | DDEV `web_environment` | `phpredis` |
| `REDIS_HOST` | DDEV `web_environment` | `redis` |
| `REDIS_PORT` | DDEV `web_environment` | `6379` |
| `MEMCACHED_HOST` | DDEV `web_environment` | `memcached` |
| `MEMCACHED_PORT` | DDEV `web_environment` | `11211` |
| `AI_BRAIN_URL` | `src/.env` | `http://host.docker.internal:11434` |

Do not commit `src/.env` or `ollama/.env`; both are Git-ignored.

## Multiple projects

Each Laravel project has its own `src/.ddev/` directory and its own
`config.yaml`. DDEV keeps projects separate:

- Independent project names and hostnames.
- Independent PHP and Node.js versions.
- Independent services and containers.
- Independent databases and `src/.env` files.
- Independent start/stop commands, run from each project's `src/` directory.

A second project does not inherit this project's PHP version, extensions, or
Redis/Memcached services. To use the same AI service from several projects,
each project's `AI_BRAIN_URL` must point at the shared host endpoint
(`http://host.docker.internal:11434`); see
[ollama/README.md](../ollama/README.md#networking).

## Next steps

- [Development environment](development-environment.md)
- [PHP and OPcache](php-configuration.md)
- [Redis and Memcached](cache-services.md)
- [Troubleshooting](troubleshooting.md)

Runtime status: **NOT RUNTIME TESTED — USER MANUAL VERIFICATION REQUIRED.**
