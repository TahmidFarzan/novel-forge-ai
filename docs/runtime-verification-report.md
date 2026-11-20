# Runtime Verification Report — Step 8

> **Project:** Novel Forge AI — Docker → DDEV development environment + independent Ollama/Qwen3 stack
> **Verification date:** 17-Nov-2026
> **Scope:** controlled runtime execution of `docs/final-runtime-verification-checklist.md`
> **Basis:** observed command output, HTTP responses, container state and persisted file state. Configuration files
> alone are not treated as runtime evidence.
>
> **Completion status:** all Step 8 runtime work authorised for this session has been executed and recorded.
> The final classification is in `docs/final-acceptance-report.md`.

## Environment and evidence baseline

| Field | Observed value |
| --- | --- |
| Host OS | Windows 11 / Docker Desktop backend |
| Docker client | 29.7.2 |
| Docker Desktop | 4.87.0 |
| Docker Engine | 29.7.2 |
| Engine resources reported by Docker | `CPUs=8`, `MemTotal=3933859840` bytes (≈3.66 GiB visible to containers) |
| DDEV | v1.25.4 |
| DDEV project root | `C:\development\Laravel\novel-forge-ai\src` |
| DDEV URL | `https://novel-forge-ai.ddev.site` |
| Host GPU | Intel UHD integrated graphics; no NVIDIA/CUDA or ROCm evidence |
| PHP CLI / FPM | PHP 8.4.24 |
| Node / npm in DDEV | Node v24.20.0, npm 11.19.0 |
| Ollama image | `ollama/ollama:0.40.16` |
| Ollama API | `0.40.16` |
| Model | `qwen3:4b`, ID `359d7dd4bcda`, 2,497,293,931 bytes (2.33 GiB / 2.5 GB) |
| Model volume | `novel-forge-ai-ollama-models` |

### Initial state

- Docker initially had no containers, images or volumes for this work.
- `src/database/database.sqlite` and `src/database/database.sqlite.bak` were both 700,416 bytes.
- `src/.env` was 2,543 bytes; `ollama/.env` was absent, so the pinned defaults applied.
- Step 7 changes were staged; nothing had been committed.
- 351/351 protected SHA-256 hashes matched the Step 1 baseline before runtime work.

### Authorised runtime actions

The user explicitly authorised Docker/DDEV start, image downloads/builds, Ollama start, `qwen3:4b`
download and inference testing. Dependency installs/upgrades, migrations, seeders, production builds and
destructive cleanup remained unauthorised.

## Corrective actions taken during runtime verification

| ID | Finding | Action | Data impact |
| --- | --- | --- | --- |
| CA-01 | DDEV Mutagen sync never completed; `ddev mutagen logs` showed a stale daemon lock (`unable to acquire daemon lock...`). | Added `performance_mode: none` to `src/.ddev/config.yaml` and started DDEV from `src/`. | Configuration only; direct bind mount used. |
| CA-02 | Stale `bootstrap/cache/config.php` had `APP_ENV=production`, `APP_URL=novel-forge-ai.localhost/` and an obsolete DB path. | Ran `ddev exec php artisan optimize:clear`. | Laravel caches only; no source or database change. |
| CA-03 | Laravel reported `attempt to write a readonly database`. | Changed ownership/permissions of the SQLite database as root inside the web container (`chown 1000:1000`, `chmod 664`). | File metadata only; size and data preserved. |
| CA-04 | `public/storage` pointed at the obsolete `/var/www/html/novel-forge-ai/storage/app/public`. | Ran `ddev exec php artisan storage:link --force`. | Symlink only. |

No destructive command was run. No migration, seeder, dependency install/upgrade, production build,
`migrate:fresh`, `db:wipe`, `migrate:reset`, `docker system prune`, volume deletion or `ddev delete` was run.

## Test register

Status legend: **PASS** · **PASS WITH LIMITATION** · **FAIL** · **NOT VERIFIED** · **BLOCKED** · **N/A**.

### A. Docker and DDEV

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| A1 | Docker Desktop opens | PASS | Docker Desktop 4.87.0 with Engine 29.7.2 available. |
| A2 | Docker Engine available | PASS | `docker info` returned server data with `CPUs=8`, `MemTotal=3933859840`. |
| A3 | DDEV installed | PASS | `ddev version` → v1.25.4. |
| A4 | DDEV recognises the project | FAIL (checklist wording) | From the repository root, `ddev describe` failed: `no .ddev\config.yaml file was found in this directory or any parent`. From `src/`, DDEV recognised project `novel-forge-ai`, root `src`. The checklist command must be corrected to run from `src/`. |
| A5 | Project starts | PASS WITH LIMITATION | Started after CA-01; URL `https://novel-forge-ai.ddev.site` was printed. Mutagen was disabled for this host. |
| A6 | Site responds | PASS | HTTPS `/up` returned 200 after a transient initial `000`; `/` returned 302 → `/home`; `/login` and `/home` returned 200; HTTP `/up` returned 200. |
| A7 | Laravel boots | PASS | `php artisan about` / runtime responses succeeded after CA-02. Laravel v13.29.0, `Environment local`. |
| A8 | Health route | PASS | `/up` returned HTTP 200 over HTTPS and HTTP. |
| A9 | PHP version | PASS | PHP 8.4.24. |
| A10 | Expected database | PASS | `config('database.default')` = `sqlite`. |
| A11 | Document root | PASS | `public/index.php` exists in the project. |
| A12 | Queue daemon | PASS | `webextradaemons:queue` reported `RUNNING`. |
| A13 | Project isolation | PASS | `ddev list` showed `ddev-sqlite-test` (a separate project with a missing `.ddev/config.yaml`) and `novel-forge-ai` (`OK`, `https://novel-forge-ai...`). The unrelated project was not started, stopped or modified. |
| A14 | Stop/start cycle | PASS WITH LIMITATION | An explicit `ddev restart` stopped and recreated the project containers, rebuilt the custom image, started `web_extra_daemons`, reached ready in 15.6 s and restored the URL. Hash data was verified before and after. A full `ddev stop` → `ddev start` cycle was not separately timed. |

### B. PHP and OPcache

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| B1 | Version | PASS | PHP 8.4.24. |
| B2 | `memory_limit` | PASS | FPM `php-fpm -i`: `memory_limit=1024M`. |
| B3 | `post_max_size` | PASS | FPM `php-fpm -i`: `post_max_size=128M`. |
| B4 | `upload_max_filesize` | PASS | FPM `php-fpm -i`: `upload_max_filesize=128M`. |
| B5 | `max_file_uploads` | PASS | FPM `php-fpm -i`: `max_file_uploads=50`. |
| B6 | `max_execution_time` | PASS | FPM `php-fpm -i`: `max_execution_time=1800`. DDEV CLI intentionally reports `0`, so FPM output is the applicable web-request value. |
| B7 | `max_input_time` | PASS | FPM `php-fpm -i`: `max_input_time=1800`. DDEV CLI intentionally reports `-1`. |
| B8 | `max_input_vars` | PASS | FPM `php-fpm -i`: `max_input_vars=5000`. |
| B9 | `date.timezone` | PASS | FPM `php-fpm -i`: `date.timezone=UTC`. |
| B10 | OPcache enabled (FPM) | PASS | FPM `opcache.enable=On`. |
| B11 | OPcache enabled (CLI) | PASS | `opcache.enable_cli=On` was reported by both CLI and FPM output. |
| B12 | Timestamp validation | PASS | FPM `opcache.validate_timestamps=On`, `opcache.revalidate_freq=0`. |
| B13 | OPcache sizing | PASS | FPM `opcache.memory_consumption=256`, `opcache.max_accelerated_files=20000`. |
| B14 | Changes picked up without restart | NOT VERIFIED | No Blade/Vue edit-and-reload test was performed. |
| B15 | Redis extension | PASS | `redis` present in `php -m`. |
| B16 | Memcached extension | PASS | `memcached` present in `php -m`; not legacy `memcache`. |
| B17 | Other required extensions | PASS | `pdo_sqlite`, `sqlite3`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif`, `pcntl` observed (with `pdo_mysql` also present). |
| B18 | 100 MB upload end-to-end | NOT VERIFIED | No media UI upload was performed. |

### C. Database

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| C1 | File present and intact | PASS | `src/database/database.sqlite` remained 700,416 bytes; backup also 700,416 bytes. |
| C2 | Connection works | PASS | SQLite 3.46.1; `DB::select('select 1')` succeeded. |
| C3 | Existing data readable | PASS | `users=16`, `novels=0`; 30 tables and 30 migration records observed. |
| C4 | Migrations pending-safe | PASS | `ddev exec php artisan migrate:status` listed all 30 migrations as `[1] Ran`; no pending migration and no destructive output. No migration was executed. |
| C5 | Backup works | PASS | Dated `.sqlite.bak` copy exists with the same size. |
| C6 | Restore works | N/A | Optional; not run. |
| C7 | Migrations run | N/A | Not authorised. |
| C8 | Queue backlog | PASS | After restart, `jobs=0` and `failed_jobs=0`; the queue daemon was `RUNNING`. |

### D. Redis

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| D1 | Container runs | PASS | `ddev-novel-forge-ai-redis` up with the project. |
| D2 | Service name resolves | PASS | `-h redis` probes succeeded. |
| D3 | Redis answers | PASS | Ping succeeded (`PING=1` captured in the probe output). |
| D4 | No password required | PASS | Probe worked without `-a`; no `NOAUTH` error. |
| D5 | PHP extension | PASS | See B15. |
| D6 | Laravel config resolves | PASS | `config("database.redis.default.host")` = `redis`. |
| D7 | Cache round-trip | PASS | `Cache::store('redis')->put/get` returned `OK` and the temporary key was deleted. |
| D8 | Config values applied | PASS | `maxmemory=2147483648` bytes (2048 MB), `maxmemory-policy=allkeys-lfu`. |
| D9 | Persistence writes | PASS | `loading:0`, `rdb_bgsave_in_progress:0`, `rdb_last_bgsave_status:ok`, `aof_enabled:0`. |
| D10 | Isolation | PASS | Named volume `ddev_novel-forge-ai_redis` is project-scoped; container is `ddev-novel-forge-ai-redis`. |
| D11 | Not exposed to host | PASS | No TCP listener on host port 6379; container ports show `6379/tcp` only (no host mapping). |
| D12 | Survives `ddev restart` | PASS | After explicit `ddev restart`, Redis was up and the configuration/persistence probes above succeeded. |

### E. Memcached

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| E1 | Container runs | PASS | `ddev-novel-forge-ai-memcached` up with the project. |
| E2 | Service name resolves | PASS | `-h memcached` probes succeeded. |
| E3 | Port reachable | PASS | set/get/delete probe succeeded; `STATS_SERVERS=1`, `UPTIME=1611`. |
| E4 | PHP extension | PASS | See B16. |
| E5 | Laravel config resolves | PASS | `config("cache.stores.memcached.servers")` = `[{"host":"memcached","port":"11211","weight":100}]`. |
| E6 | Cache round-trip | PASS | `Cache::store('memcached')->put/get` returned `OK`. |
| E7 | Memory limit applied | PASS | Memcached stats `limit_maxbytes=134217728` bytes (128 MiB). |
| E8 | Isolation | PASS | Only `ddev-novel-forge-ai-memcached` was created/published for this project; no host port mapping. |
| E9 | Not exposed to host | PASS | No TCP listener on host port 11211; container ports show `11211/tcp` only. |
| E10 | Survives `ddev restart` | PASS | After explicit `ddev restart`, Memcached was up and config/stats probes succeeded. |

### F. Frontend / Vite

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| F1 | Node version | PASS | Node v24.20.0 inside DDEV. |
| F2 | npm version | PASS | npm 11.19.0 inside DDEV. |
| F3 | Dependencies install | BLOCKED | `Cannot find native binding`; `node_modules/@rolldown` contains only `binding-win32-x64-msvc`, no Linux binding. Installing dependencies inside the container was not authorised. |
| F4 | Vite starts | BLOCKED | Blocked by F3. |
| F5 | Asset page loads | NOT VERIFIED | Build assets exist, but the Vite dev server did not start. |
| F6 | HMR over HTTPS | BLOCKED | Known protected-source limitation FMI-03 plus F3. |
| F7 | HMR over HTTP fallback | BLOCKED | Port 5172 is configured, but Vite could not start. |
| F8 | Production build | BLOCKED | Not authorised and blocked by the same missing native binding. |
| F9 | Built app works | NOT VERIFIED | Existing `public/build` assets were served, but a fresh build was not run. |
| F10 | Console clean | NOT VERIFIED | Browser console check not performed. |

### G. Ollama and Qwen3

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| G1 | Ollama starts independently | PASS | `.\scripts\start.ps1` started `novel-forge-ai-ollama-standalone`; container became healthy on `127.0.0.1:11434`. |
| G2 | DDEV did not start it | PARTIAL | Initial state had no Ollama container and Ollama was started explicitly by its own script after DDEV; a dedicated negative re-test was not retained. |
| G3 | API answers on loopback | PASS | `curl http://127.0.0.1:11434/api/tags` and `/api/version` succeeded. |
| G4 | API not on LAN | PASS | Loopback `http://127.0.0.1:11434/api/version` returned 200; `172.26.208.1` and `192.168.0.101` both returned `REFUSED_OR_NO_RESPONSE`. Container port mapping is `127.0.0.1:11434->11434/tcp`. |
| G5 | Model storage volume | PASS | `novel-forge-ai-ollama-models` exists and is mounted at `/root/.ollama`. |
| G6 | Download Qwen3 | PASS | `pull-qwen3.ps1 -Model qwen3:4b -Force` completed successfully. |
| G7 | Model listed | PASS | `list-models.ps1` and API tags show `qwen3:4b`, ID `359d7dd4bcda`. |
| G8 | Direct inference | FAIL (default context) / PASS WITH LIMITATION (reduced context) | Default `ollama run qwen3:4b 'Say OK'` at `n_ctx=8192` failed after 322.39 s: `llama-server process has terminated: signal: killed`. A reduced-context API request (`num_ctx=1024`, `num_predict=4`) returned HTTP 200 after 515.86 s. |
| G9 | Application connectivity | PASS | From DDEV: `curl http://host.docker.internal:11434/api/version` returned `{"version":"0.40.16"}`; Ollama logs showed a request from `172.19.0.1`. |
| G10 | Real generation in UI | FAIL (no visible response) | The first reduced-context API request completed with HTTP 200 but an empty `response` string. A second request with `think=false`, `keep_alive=5m`, `num_ctx=1024`, `num_predict=16` and `temperature=0` timed out after 600.9 s (`HttpClient.Timeout`). No visible `OK` response was produced. |
| G11 | Long request timeout | PASS WITH LIMITATION | A generation request ran for 515.86 s and returned HTTP 200, so no premature 504 was observed. It was an API request, not a browser/nginx UI request. |
| G12 | GPU path | NOT VERIFIED | No NVIDIA/CUDA/ROCm hardware evidence; host has Intel UHD iGPU only. |
| G13 | CPU fallback | PASS WITH LIMITATION | CPU inference works, but default context is not viable here; reduced context is very slow (≈516 s for 4 generated tokens). |
| G14 | Multiple clients | NOT VERIFIED | A second DDEV project was not used to call Ollama. |
| G15 | Stop without affecting DDEV | PASS WITH LIMITATION | `scripts/stop.ps1` stopped the Ollama container (`Exited (0)`) while preserving the model volume and did not touch DDEV. DDEV had already been stopped deliberately, so no post-Ollama-stop HTTP request was made. |
| G16 | Models survive container removal | NOT VERIFIED | Container removal/recreation was not performed because it was not needed and would add risk. |
| G17 | Purge safeguard | NOT VERIFIED | Destructive purge script not executed, even with a declined confirmation. |

### H. Documentation and Git

| # | Check | Status | Evidence / note |
| --- | --- | --- | --- |
| H1–H7 | Documentation accuracy and links | PASS (static) | Step 7 reviewed 25 Markdown files / 101 links; 7 unresolved links are confined to the frozen archive README. No new runtime doc drift was found. |
| H8 | Required configs tracked | PASS (static) | Step 7 confirmed configuration files are trackable; no secret or SQLite file is tracked. |
| H9 | Secrets excluded | PASS (static) | `src/.env` and `ollama/.env` ignored/untracked; `ollama/.env` was absent at runtime. |
| H10 | Legacy archive intact | PASS (static) | 14/14 archived files matched the manifest in Step 7. |
| H11 | Final protected hash check | PASS | Recomputed after runtime work and controlled shutdown: `entries=351 match=351 mismatch=0 missing=0`. |
| H12 | Working tree understood | PASS WITH LIMITATION | Final `git status --porcelain` contains only the expected Step 7 change set, Step 8 documentation edits, the new untracked report, and the unstaged CA-01 addition to `src/.ddev/config.yaml`. Nothing was committed. |

## Qwen3 inference evidence

| Attempt | Context | Result |
| --- | --- | --- |
| CLI `ollama run qwen3:4b 'Say OK'` | default 8192 | Failed after 322.39 s, exit 1: `llama-server process has terminated: signal: killed`. Logs showed `n_ctx = 8192` and `n_ctx_seq (8192) < n_ctx_train (262144)`. |
| API `/api/generate`, `num_predict=8`, first attempt | 2048 | Timed out after 421.36 s; logs showed `499 | 7m0s | POST "/api/generate"` and `client connection closed before llama-server finished loading, aborting load`. |
| API `/api/generate`, `num_predict=4`, `temperature=0` | 1024 | HTTP 200 after 515.86 s; `LoadMs=292097`, `PromptEvalMs=36698`, `EvalMs=187871`, `TotalMs=516877`, `EvalCount=4`, `Done=True`, `response=''`. |
| API `/api/generate`, `think=false`, `keep_alive=5m`, `num_predict=16`, `temperature=0` | 1024 | HTTP client timeout after 600.9 s: `The request was canceled due to the configured HttpClient.Timeout of 600 seconds elapsing.` |

This is enough evidence that the model can load and generate through the API with a reduced context on CPU,
but not enough evidence for a successful default-context inference or a visible textual answer.

## Resource snapshot

Taken after the inference failures, explicit `ddev restart` and all post-restart probes:

| Container | Memory used / limit | CPU |
| --- | --- | --- |
| `ddev-novel-forge-ai-redis` | 19.46 MiB / 3.664 GiB | 0.24% |
| `ddev-novel-forge-ai-memcached` | 6.234 MiB / 3.664 GiB | 0.01% |
| `ddev-novel-forge-ai-web` | 250.9 MiB / 3.664 GiB | 13.98% |
| `novel-forge-ai-ollama-standalone` | 209.4 MiB / 3.664 GiB | 0.00% |
| `ddev-router` | 155.3 MiB / 3.664 GiB | 0.00% |
| `ddev-ssh-agent` | 4.75 MiB / 3.664 GiB | 4.36% |

An earlier snapshot after the inference failures was lower (Redis 7.188 MiB, web 100.9 MiB, Memcached
5.547 MiB, Ollama 192.6 MiB, router 46.77 MiB, ssh-agent 5.863 MiB). These are point-in-time values, not a
sustained-load benchmark.

## Controlled shutdown and preservation

- `ddev stop` stopped `ddev-novel-forge-ai-web`, `ddev-novel-forge-ai-redis` and
  `ddev-novel-forge-ai-memcached` without deleting containers or volumes.
- `ollama/scripts/stop.ps1` stopped `novel-forge-ai-ollama-standalone` (`Exited (0)`) and explicitly preserved
  `novel-forge-ai-ollama-models`.
- Final volumes: `ddev_novel-forge-ai_redis` and `novel-forge-ai-ollama-models` remain.
- Final host listener check: no listener on port 11434.
- `ddev list` showed `novel-forge-ai` as `stopped` and the unrelated `ddev-sqlite-test` project unchanged.
  `ddev-router` and `ddev-ssh-agent` remain running because they are shared DDEV infrastructure, not resources
  started by this session.
- SQLite database and backup both remained 700,416 bytes; no persistent data was deleted.
- Final protected hash check after shutdown: 351/351 match, 0 mismatch, 0 missing.

## Known limitations carried into acceptance

1. **Vite cannot run on this host as installed.** The vendored `node_modules/@rolldown` has only a Windows
   native binding. Container-side npm install and production build require explicit authorisation.
2. **GPU acceleration is not verified.** The host has Intel UHD integrated graphics only; CPU fallback is
   the applicable path.
3. **Default 8192-context Qwen3 inference fails here.** The llama-server process is killed. A reduced-context
   API request completed with an empty response string after 515.86 s; a second reduced-context request with
   `think=false` timed out after 600.9 s. No visible `OK` response was produced.
4. **The checklist's A4 command is wrong.** DDEV commands must be run from `src/`, not the repository root.
5. **Some checklist probes remain unexecuted**: 100 MB UI upload, browser console check, HMR reload test,
   second-project Ollama client, container removal/recreation model persistence, and the declined purge
   safeguard test. Final protected hashes are 351/351; final Git review remains pending.

## Preservation statement

At the time of writing, no persistent data had been deleted or overwritten: the SQLite file remained
700,416 bytes; its backup was intact; the Ollama model volume contained the newly pulled `qwen3:4b`;
Redis/Memcached probes used temporary keys that were removed; `src/.env` was not changed; uploads, media and
storage were not removed. Final post-shutdown verification is still required.
