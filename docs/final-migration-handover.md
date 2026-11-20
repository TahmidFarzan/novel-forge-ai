# Final Migration Handover

> **Project:** Novel Forge AI — Laravel 13 + Vue 3 + Vite 8 + DDEV
> **Migration:** legacy Docker Compose development stack → DDEV-managed development environment,
> with Ollama/Qwen3 moved to an independent Docker-only stack.
> **Handover prepared:** 17-Nov-2026, Step 7 (final planned development step).
> **Audience:** a developer who has never seen this repository.
>
> **Verification status of this document:** the statements below were checked **statically** against the
> repository when this handover was written. Step 8 runtime verification has since been completed; see
> `docs/runtime-verification-report.md` for observed results and `docs/final-acceptance-report.md` for the
> final classification. `docs/final-runtime-verification-checklist.md` contains the runtime procedure.

---

## A. Migration Summary

### Original architecture

Development ran on a hand-maintained Docker Compose stack in `docker/`:

- `docker/docker-compose.dev.yml` started nginx, PHP-FPM, a Node/Vite container, and a legacy Ollama
  container; all shared one Compose network.
- `docker/nginx/nginx.dev.conf` carried the development vhost; `docker/php/php.ini` carried PHP settings.
- The Laravel app lived in `novel-forge-ai/` (now `src/`), bind-mounted into the containers.
- Every project on the machine needed its own hand-written compose stack, PHP version, and ports, and
  nothing isolated two projects from each other.

### New architecture

- **DDEV** owns the development environment for this project (`src/.ddev/`). DDEV provisions the web
  container, PHP, Node, nginx, and the SQLite-backed Laravel app, and exposes it at
  `https://novel-forge-ai.ddev.site`.
- **Redis and Memcached** are project-scoped DDEV services declared in `src/.ddev/` — they start and stop
  with *this* project and share nothing with other projects.
- **Ollama + Qwen3** live in `ollama/`, a separate Compose project with its own lifecycle. DDEV never starts
  it and the Laravel app never manages it.
- The old development compose and nginx files are preserved verbatim under `archive/legacy-docker/`.

### Why

Project isolation, reproducible PHP/Node versions per project, no hand-maintained container plumbing for
ordinary development work, and a clear separation between the development environment and the AI runtime.

### Main changes

| Area | Change |
| --- | --- |
| Development runtime | Docker Compose dev stack → DDEV (`src/.ddev/config.yaml`) |
| PHP settings | `docker/php/php.ini` → `src/.ddev/php/php.ini` (protected as a new file; production copy untouched) |
| Web server config | `docker/nginx/nginx.dev.conf` → `src/.ddev/nginx_full/nginx-site.conf` |
| Cache services | none → project-scoped `redis` and `memcached` DDEV services |
| AI runtime | legacy `docker/ollama` container → `ollama/compose.yaml` + lifecycle scripts |
| Documentation | Docker-first docs → DDEV-first docs + preserved legacy docs |
| Legacy config | active → archived snapshot with a SHA-256 manifest |

### Preserved

- The production Docker packaging in `docker/` (12 files, unmodified in behaviour).
- The SQLite database, `src/.env`, `src/storage/`, uploads, and all Ollama model volumes
  (`novel-forge-ai-ollama-models`).
- The 351 protected application source files — verified byte-identical to the Step 1 baseline.

---

## B. Final Project Architecture

```
C:\development\Laravel\novel-forge-ai\
│
├── README.md                     entry point (DDEV-first)
├── .gitignore / .dockerignore
├── docs\                         developer documentation + this handover
│   ├── installation.md
│   ├── development-environment.md
│   ├── php-configuration.md
│   ├── cache-services.md
│   ├── command-reference.md
│   ├── troubleshooting.md
│   ├── docker-*.md               legacy Docker reference (kept, clearly marked)
│   ├── final-migration-handover.md        ← this file
│   ├── final-migration-issues.md
│   └── final-runtime-verification-checklist.md
│
├── src\                          ← DDEV project root (Laravel application root)
│   ├── artisan, composer.json, package.json, vite.config.js
│   ├── app/ resources/ routes/ tests/      protected source
│   ├── .env                      local secrets, untracked
│   ├── database\database.sqlite  live SQLite database
│   ├── public\                   document root
│   └── .ddev\
│       ├── config.yaml
│       ├── php\php.ini           all PHP + OPcache settings (single file)
│       ├── nginx_full\nginx-site.conf
│       ├── redis\redis.conf
│       ├── docker-compose.redis.yaml
│       └── docker-compose.memcached.yaml
│
├── ollama\                       ← independent Docker Compose project
│   ├── compose.yaml
│   ├── .env.example
│   ├── README.md
│   └── scripts\ (start, stop, pull-qwen3, list-models, remove-model,
│                 remove-container, remove-image, purge-all, _common)
│
├── docker\                       retained production packaging (12 files)
└── archive\legacy-docker\        frozen snapshot of the retired dev stack
    ├── README.md
    ├── ARCHIVE-MANIFEST.md
    └── docker\ (14 files)
```

### Component facts

| Component | Final configuration |
| --- | --- |
| DDEV project | name `novel-forge-ai`, type `laravel`, root `src/`, docroot `public` |
| URL | `https://novel-forge-ai.ddev.site` |
| PHP | 8.4 (`php_version: "8.4"`), settings in `src/.ddev/php/php.ini` |
| Node | 24 (`nodejs_version: "24"`) |
| Web server | nginx-fpm, custom site config (128 MB uploads, 1800 s FastCGI timeouts) |
| Database | SQLite `src/database/database.sqlite`; DDEV db container omitted |
| Redis | `redis:8` container `ddev-novel-forge-ai-redis`, hostname `redis`, port 6379 |
| Memcached | `memcached:1.6` container `ddev-novel-forge-ai-memcached`, hostname `memcached`, port 11211 |
| Queue | supervisord daemon in the web container: `php artisan queue:listen --tries=1 --timeout=0` |
| Ollama | `ollama/ollama:0.40.16`, container `novel-forge-ai-ollama-standalone`, `127.0.0.1:11434` |
| Qwen3 | `qwen3:4b` (see `ollama/.env.example` for alternatives) |
| Model storage | Docker volume `novel-forge-ai-ollama-models` → `/root/.ollama` |
| Legacy archive | `archive/legacy-docker/`, 14 files, SHA-256 manifest |

### Network paths

- Browser → nginx in the DDEV web container → Laravel (PHP-FPM).
- Browser → Vite dev server, published on the **web** container at 5173 (HTTPS) / 5172 (HTTP).
- Laravel (web **and** queue containers) → `redis:6379` and `memcached:11211` over the project network.
- Laravel → Ollama at `http://host.docker.internal:11434`, i.e. out of the project network to the host's
  loopback-published port. Ollama is reachable *only* because it runs on the same machine.

---

## C. Developer Prerequisites

| Requirement | Version / note | Verified how |
| --- | --- | --- |
| Windows 10/11 (or macOS/Linux) | DDEV is cross-platform; this project was configured on Windows | static |
| Docker Desktop | current release, WSL2 backend on Windows, **started manually** | static |
| DDEV | v1.25.x (project verified against v1.25.5) | static |
| Git | any current release | static |
| PowerShell 7+ | only for `ollama/scripts/*.ps1` on Windows | static |
| Host PHP / Composer / Node / Redis / Memcached | **not required** — DDEV supplies them | static |
| Disk | Docker images for DDEV, plus ~3–5 GB per pulled Qwen3 model | static estimate |

The agent did not install, upgrade, or verify any of these at runtime.

---

## D. First-Time Setup

Run these **in order**, from a fresh clone of the repository.

```
USER MUST EXECUTE MANUALLY.
```

1. **Start Docker Desktop** and wait until it reports the engine is running.
2. **Open the DDEV project root** in a terminal:
   `cd C:\development\Laravel\novel-forge-ai\src`
3. **Start DDEV for this project** (DDEV is invoked from `src/`, which contains `.ddev`):
   ```
   ddev start
   ```
   The first start downloads the DDEV images, so expect several minutes. DDEV also trusts the local HTTPS
   certificate via `mkcert`.
4. **Confirm the URL** printed by DDEV is `https://novel-forge-ai.ddev.site`, then open it in a browser.
   Laravel's `/up` health route should answer.
5. **Install PHP dependencies** (only if `src/vendor/` is absent):
   ```
   ddev composer install
   ```
6. **Create the local environment file** (only if `src/.env` is absent):
   ```
   copy src\.env.example src\.env
   ```
   Then set at minimum:
   ```
   APP_KEY=                       # fill via: ddev artisan key:generate
   APP_URL=https://novel-forge-ai.ddev.site
   DB_CONNECTION=sqlite
   AI_BRAIN_URL=http://host.docker.internal:11434
   AI_BRAIN_MODEL=qwen3:4b
   AI_BRAIN_CONTEXT_WINDOW=8192
   AI_BRAIN_MAX_OUTPUT_TOKENS=8192
   AI_BRAIN_TIMEOUT_SECONDS=1500
   ```
   `REDIS_HOST`, `REDIS_PORT`, `REDIS_CLIENT`, `MEMCACHED_HOST` and `MEMCACHED_PORT` are supplied by
   `src/.ddev/config.yaml` (`web_environment`) and override whatever the `.env` file says.
   See FMI-02 in `docs/final-migration-issues.md` for why the example file still says `127.0.0.1`.
7. **Generate the app key** if `.env` was newly created:
   ```
   ddev artisan key:generate
   ```
8. **Install front-end dependencies**:
   ```
   ddev npm install
   ```
9. **Run the Vite dev server** in a second terminal:
   ```
   ddev npm run dev
   ```
   Open `https://novel-forge-ai.ddev.site:5173`. If the HMR socket is blocked (see FMI-03), use
   `http://novel-forge-ai.ddev.site:5172`, or fall back to a production build:
   ```
   ddev npm run build
   ```
10. **Optional cache services** — they start automatically with `ddev start`. Verify only if you intend to
    use them; the app defaults to database cache/session/queue.
11. **Optional AI runtime** — start Ollama separately (section F). DDEV does not need it.

Full alternative procedures: `docs/installation.md`, `docs/manual-installation.md`.

---

## E. Daily Development Workflow

```
USER MUST EXECUTE MANUALLY.
```

| Task | Command |
| --- | --- |
| Start the project | `ddev start` (from `src/`) |
| Open the site | `https://novel-forge-ai.ddev.site` |
| Stop the project | `ddev stop` |
| Stop everything DDEV-wide | `ddev poweroff` |
| Project status | `ddev describe` |
| Open a shell in the web container | `ddev ssh` |
| Run artisan | `ddev artisan <command>` (e.g. `ddev artisan migrate`) |
| Composer | `ddev composer <command>` |
| npm | `ddev npm <command>` |
| Vite dev server | `ddev npm run dev` |
| Production assets | `ddev npm run build` |
| Logs | `ddev logs` / `ddev logs -s web` / `ddev logs -s redis` |
| Database file | `src/database/database.sqlite` — edit/inspect with any SQLite tool |
| Back up the database | copy `src/database/database.sqlite` while the app is idle |
| Restore the database | stop DDEV, copy the backup over the file, start DDEV |

### Working with Laravel

- Migrations: `ddev artisan migrate` (they write to the SQLite file).
- Queues: the `queue` supervisord daemon is part of the web container, so `ddev logs -s web` shows worker
  output. Do not also run `queue:work` manually unless you intentionally want a second worker.
- Settings: `src/.ddev/php/php.ini` (PHP + OPcache), `src/.ddev/nginx_full/nginx-site.conf` (uploads and
  timeouts), `src/.ddev/config.yaml` (everything else). After editing any of them, `ddev restart` applies it.

### Working with Vue.js / Vite

- `ddev npm run dev` serves on 5173 (HTTPS) and 5172 (HTTP fallback).
- `src/vite.config.js` is protected: it hardcodes `protocol: 'ws'`, which is why HTTPS HMR can fail.
  Do not edit it casually; see FMI-03.
- Production builds need no special configuration: `ddev npm run build`.

### Using Redis / Memcached

Both services are **optional** and project-scoped; the app currently uses the database for cache, sessions
and queues. To move the cache onto Redis, set `CACHE_STORE=redis` (and only then consider
`SESSION_DRIVER=redis`) inside the container environment, then clear caches. Details and verification
commands are in `docs/cache-services.md`.

---

## F. AI Workflow

Ollama is **not** part of DDEV and is never started by it.

```
USER MUST EXECUTE MANUALLY.
```

```
cd C:\development\Laravel\novel-forge-ai\ollama
.\scripts\start.ps1          # start the Ollama container
.\scripts\pull-qwen3.ps1     # download qwen3:4b into the persistent volume (asks for confirmation)
.\scripts\list-models.ps1    # list models present in the volume
.\scripts\stop.ps1           # stop the container; the model volume is preserved
```

- The API listens on `http://127.0.0.1:11434` on the host and is reached from Laravel at
  `http://host.docker.internal:11434`.
- Do not change the port publishing to `0.0.0.0`. The stack has no authentication; loopback-only is the
  security boundary.
- Model data lives in the Docker volume `novel-forge-ai-ollama-models` (`/root/.ollama` in the container).
  Stopping the container, removing the container, and even removing the image keep the models.
- Removing one model: `.\scripts\remove-model.ps1 -Model <name>` (requires an explicit target).
- Removing the container/image: `remove-container.ps1`, `remove-image.ps1` — both preserve the volume.
- Full purge: `purge-all.ps1` deletes the model volume and requires typing an unrecoverable confirmation
  phrase. There is no bypass flag. Treat it as destructive.
- GPU acceleration is opt-in and unverified (FMI-05). CPU inference on `qwen3:4b` is the default path.

`ollama/README.md` is the authoritative reference for options, resource guidance and troubleshooting.

---

## G. Multiple Projects

| Concern | How it is isolated |
| --- | --- |
| Configuration | each project has its own `<project>/src/.ddev/` directory |
| PHP version | `php_version` is per project (`8.4` here) |
| Node version | `nodejs_version` is per project (`24` here) |
| Database | SQLite file inside each project; no shared server |
| Redis / Memcached | per project: container names are prefixed `ddev-<sitename>-`, volumes are project-scoped |
| Hostnames | `https://<project>.ddev.site` |
| Vite | per project ports are allocated by DDEV's `web_extra_exposed_ports` |
| AI runtime | **shared**: one Ollama stack per machine, reached via `host.docker.internal` |

Notes:

- The request template's optional `sc/**` path does not exist in this repository; the single Laravel project
  is `src/`.
- Redis/Memcached volumes are namespaced by DDEV, so two projects cannot collide. Do not rename the services
  to shared names.
- A second project needs its own AI variables pointing at the same `host.docker.internal:11434`. Give each
  project its own model choice and keep `OLLAMA_NUM_PARALLEL=1` unless the host has spare memory.
- Never add the Ollama stack to a DDEV compose override: it would tie two lifecycles together.

---

## H. Troubleshooting

Start at `docs/troubleshooting.md`. It covers, in order:

- DDEV start failures, port conflicts, certificate trust.
- PHP settings not applied, OPcache questions, missing extensions.
- SQLite locking and file permissions.
- Redis and Memcached connectivity from inside DDEV.
- Vite/HMR problems and the build fallback.
- Ollama reachability from a container and model storage questions.

Command cheat-sheet: `docs/command-reference.md`.
Ollama-specific problems: `ollama/README.md` (troubleshooting section).

---

## I. Legacy Docker Archive

| Item | Location |
| --- | --- |
| Explainer | `archive/legacy-docker/README.md` |
| Inventory + hashes | `archive/legacy-docker/ARCHIVE-MANIFEST.md` |
| Snapshot | `archive/legacy-docker/docker/` (14 files) |

- **Why it exists:** the retired development Compose stack is kept as evidence and as a rollback reference
  without leaving runnable legacy configuration in active paths.
- **What is in it:** `docker-compose.yml`, `docker-compose.dev.yml`, `.env.example`, `README.md`,
  `nginx/{Dockerfile,nginx.conf,nginx.dev.conf}`, `ollama/Dockerfile`,
  `php/{Dockerfile,entrypoint.sh,php.ini,startup.dev.sh,startup.sh,www.conf}`.
- **Integrity:** all 14 files match the SHA-256 values in the manifest. Do not edit the snapshot; the hashes
  are the integrity record. The archive's own `README.md` intentionally keeps its original relative links,
  which is why a repository-wide link check reports seven unresolved links there (FMI-08).
- **How to inspect:** read the files directly; they are plain text.
- **How to restore safely:** copy the single file you need back to its original path (the manifest lists it),
  or check out the pre-migration revision in a branch. Then verify the restored file against the manifest.
  Never restore the whole development stack into an active DDEV project.
- **Not archived:** the production packaging in `docker/`. It remains active (FMI-12).

---

## J. Security and Data Protection

### Secrets and Git

- `src/.env` is ignored (`src/.gitignore`) and untracked. `ollama/.env` is ignored (root `.gitignore`).
- Only `.env.example` templates are tracked; they contain placeholders, not live credentials.
- The Step 7 scan of all non-ignored, non-binary files found no AWS keys, private keys, `sk-`/`ghp_`/`xox*`
  tokens. `git ls-files` = 414 tracked files; no real `.env`, no `.sqlite` file, no temporary tracking file
  is tracked.
- Never commit `src/.env`, `src/database/*.sqlite`, `src/storage/**`, `ollama/.env`, or model files.

### Network exposure

- Redis 6379 and Memcached 11211 are **exposed internally only** (`expose:`, no host port mapping). They are
  not reachable from the host or the LAN, and they have no authentication of their own.
- nginx and Vite are published by DDEV on the local machine only.
- Ollama is bound to `127.0.0.1:11434`; DDEV containers reach it through `host.docker.internal`. There is no
  authentication on the Ollama API — that is why it must stay loopback-only.

### Data

- SQLite database: `src/database/database.sqlite` (≈684 KB at handover, last modified 10-Nov-2026).
  Back up by copying the file while the app is idle. DDEV snapshots are unavailable because the db container
  is omitted (FMI-07).
- Uploads/media and logs live under `src/storage/` (present, untouched).
- Ollama models: Docker volume `novel-forge-ai-ollama-models`. Ordinary stop/remove operations preserve it;
  only `purge-all.ps1` destroys it, behind a typed confirmation.
- No migration step deleted, moved, or truncated any database, backup, upload, or model volume.

### Destructive script safeguards

`ollama/scripts/` contains no `docker prune`, no `docker system prune`, and no wildcard removal. Each removal
script targets one exact, named resource. `purge-all.ps1` requires the user to type an unrecoverable
confirmation phrase and offers no `-Force` bypass. Review the scripts before running them; they were not
executed during the migration.

---

## K. Known Limitations

1. **No runtime verification yet.** Everything in this handover is a static-configuration claim (FMI-01).
2. **HTTPS HMR is constrained.** `src/vite.config.js` hardcodes `protocol: 'ws'`; use port 5172 or a build
   (FMI-03).
3. **GPU acceleration unverified.** CPU inference is the default; the GPU path is documented but untested
   (FMI-05).
4. **No DDEV database helpers.** `omit_containers: [db]` disables snapshots/import for the SQLite setup
   (FMI-07).
5. **Ollama is single-host only**, loopback-bound, unauthenticated by design (FMI-11).
6. **`.env.example` defaults are stale** relative to DDEV; the live `.env` and DDEV `web_environment`
   override them (FMI-02).
7. **`docker/php/startup.dev.sh` is still present** because the production Dockerfile copies it (FMI-04).
8. **Redis/Memcached have no healthchecks**, mirroring the upstream add-ons (FMI-10).
9. **Archive restore is documented but never exercised** (FMI-09).
10. **Production packaging in `docker/` is not validated** by this migration (FMI-12).

---

## L. Verification Status

| Area | Static verification | Runtime verification |
| --- | --- | --- |
| Repository architecture | Complete — structure, roots, docroot, tracked files reviewed | Pending |
| DDEV configuration | Complete — YAML parsed, 62 assertions satisfied | Pending (`ddev start`) |
| PHP + OPcache | Complete — all 16 required values present in one file | Pending (`php -i`, `php -m`) |
| Redis | Complete — service, config, env wiring, isolation | Pending (`redis-cli ping`, Laravel cache) |
| Memcached | Complete — service, port, env wiring | Pending (`Cache::store('memcached')`) |
| SQLite | Complete — file present, untouched, connection configured | Pending (queries, migrations) |
| Vite / Vue | Complete — ports, strictPort, plugin versions reviewed | Pending (dev server, HMR, build) |
| Ollama / Qwen3 | Complete — pin, volume, lifecycle, ports, scripts reviewed | Pending (start, pull, inference) |
| GPU | Not verifiable statically | Pending — hardware test |
| Legacy archive | Complete — 14/14 hashes match the manifest | Not applicable |
| Documentation | Complete — links/fences checked; 7 known archive-only link gaps | Pending (reading accuracy) |
| Security | Complete — secret scan, ignore rules, exposure review | Pending |
| Source integrity | Complete — 351/351 protected hashes match | Not applicable |
| Git | Complete — 14 modified, 36 added, 2 renames, 0 deleted, 0 untracked | Not applicable |

**Runtime disclaimer:** NOT STARTED BY AGENT — Docker and DDEV were not started, no container was built or
started, no dependency or AI model was downloaded, and no migration, test, or inference was executed.

**Recommended entry point for the next developer:** read sections C–F of this document, then work through
`docs/final-runtime-verification-checklist.md`. Record the observed results there or in the tracker; do not
assume any checkbox.

---

## Related documents

- [Installation](installation.md)
- [Development environment](development-environment.md)
- [PHP and OPcache](php-configuration.md)
- [Redis and Memcached](cache-services.md)
- [Command reference](command-reference.md)
- [Troubleshooting](troubleshooting.md)
- [Final migration issues](final-migration-issues.md)
- [Runtime verification checklist](final-runtime-verification-checklist.md)
- [Ollama and Qwen3](../ollama/README.md)
- [Legacy Docker archive](../archive/legacy-docker/README.md)
