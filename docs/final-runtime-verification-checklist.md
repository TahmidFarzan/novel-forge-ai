# Final Runtime Verification Checklist (User-Executed)

> **Project:** Novel Forge AI — DDEV development environment + independent Ollama/Qwen3 stack
> **Prepared:** 17-Nov-2026, Step 7. **Belongs to:** Step 8.
>
> This is the runtime test procedure that follows the Step 7 static audit. The migration agent did **not**
> execute it and did not execute any of these commands.
>
> **Every command below is USER MUST EXECUTE MANUALLY.**
> Record the actual observed result. If you cannot observe it, leave it unticked and write why. Do not convert
> a checkbox to "verified" on the basis of the configuration files alone.
>
> Runtime rule: never delete the SQLite database, `src/storage/`, `src/.env`, the Ollama model volume, or any
> Docker volume to make a test pass. Back up before anything destructive.

## Result recording

Use this table per section, or copy the checklist into your own notes.

| Field | Value |
| --- | --- |
| Run date/time | |
| DDEV version | |
| Docker Desktop version | |
| Host OS / GPU | |
| Overall result | PASS / PASS WITH LIMITATIONS / FAIL |

Legend: `[ ]` not run · `[P]` pass · `[F]` fail · `[L]` pass with documented limitation · `[N]` not applicable

---

## A. Docker and DDEV

| # | Check | How | Expected |
| --- | --- | --- | --- |
| A1 | Docker Desktop opens | Start it from the Start menu | UI reaches "Engine running" |
| A2 | Docker Engine available | `docker info` | Server section present, no error |
| A3 | DDEV installed and current | `ddev version` | v1.25.x or newer |
| A4 | DDEV recognises the project | from `src/`: `ddev describe` | project `novel-forge-ai`, root `src` |
| A5 | Project starts | `ddev start` | exits 0, prints `https://novel-forge-ai.ddev.site` |
| A6 | Site responds | open the URL; also `curl -I https://novel-forge-ai.ddev.site` | HTTP 200/302, valid certificate |
| A7 | Laravel boots | `ddev artisan about` | application info prints, no exception |
| A8 | Health route | open `https://novel-forge-ai.ddev.site/up` | 200 |
| A9 | Expected PHP version | `ddev exec php -v` | PHP 8.4.x |
| A10 | Expected database | `ddev exec php artisan tinker --execute="echo config('database.default');"` | `sqlite` |
| A11 | Document root correct | `ddev exec ls public/index.php` | file exists |
| A12 | Queue daemon running | `ddev logs -s web` | supervisord shows the `queue` program running |
| A13 | Project isolation (if a second DDEV project exists) | `ddev list` | both listed, different URLs, independent start/stop |
| A14 | Stop/start cycle is clean | `ddev stop` then `ddev start` | no data loss, site returns |

Notes / observed:

```
```

---

## B. PHP

| # | Check | How | Expected |
| --- | --- | --- | --- |
| B1 | Version | `ddev exec php -v` | 8.4.x |
| B2 | `memory_limit` | `ddev exec php -i \| findstr memory_limit` | `1024M` |
| B3 | `post_max_size` | `ddev exec php -i \| findstr post_max_size` | `128M` |
| B4 | `upload_max_filesize` | `ddev exec php -i \| findstr upload_max_filesize` | `128M` |
| B5 | `max_file_uploads` | `ddev exec php -i \| findstr max_file_uploads` | `50` |
| B6 | `max_execution_time` | `ddev exec php -i \| findstr max_execution_time` | `1800` |
| B7 | `max_input_time` | `ddev exec php -i \| findstr max_input_time` | `1800` |
| B8 | `max_input_vars` | `ddev exec php -i \| findstr max_input_vars` | `5000` |
| B9 | `date.timezone` | `ddev exec php -i \| findstr date.timezone` | `UTC` |
| B10 | OPcache enabled (FPM) | `ddev exec php -i \| findstr opcache.enable` | `On` |
| B11 | OPcache enabled (CLI) | same output section for CLI | `On` |
| B12 | OPcache validates timestamps | `ddev exec php -i \| findstr validate_timestamps` | `On`, `revalidate_freq` = 0 |
| B13 | OPcache sizing | `ddev exec php -i \| findstr opcache.memory_consumption` | `256` |
| B14 | Changes are picked up without restart | edit a Blade/Vue file through the normal dev flow | change appears immediately (timestamp validation) |
| B15 | Redis extension loads | `ddev exec php -m \| findstr /i redis` | `redis` (and not only `redis` inside another name) |
| B16 | Memcached extension loads | `ddev exec php -m \| findstr /i memcached` | `memcached` (never `memcache`) |
| B17 | Other required extensions | `ddev exec php -m` | `pdo_sqlite`, `sqlite3`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif`, `pcntl` present |
| B18 | Upload limit end-to-end | upload a file > 100 MB through the media UI | succeeds, no 413 |

Terminal note: inside `ddev ssh` use `grep` instead of `findstr`.

---

## C. Database

| # | Check | How | Expected |
| --- | --- | --- | --- |
| C1 | File present and intact | `dir src\database\database.sqlite` | size > 0 (≈684 KB at handover) |
| C2 | Connection works | `ddev artisan db:show` (or `ddev exec php artisan tinker` + `DB::select('select 1')`) | query returns |
| C3 | Existing data readable | `ddev artisan tinker` → count a known table | existing rows returned, no migration run yet |
| C4 | Migrations are pending-safe | `ddev artisan migrate:status` | no destructive output; note pending migrations |
| C5 | Backup works | copy `src\database\database.sqlite` to a dated file **before** migrating | copy exists, same size |
| C6 | Restore works (optional) | stop DDEV, restore the copy over the file, start DDEV | data matches the copy |
| C7 | Migrations run (only if authorised) | `ddev artisan migrate` | succeeds; C5 backup still available |
| C8 | Queue table healthy | `ddev artisan queue:monitor` or check `jobs` table | no stuck backlog |

Do not run `migrate:fresh`, `db:wipe`, or `ddev delete` — they destroy user data.

---

## D. Redis

| # | Check | How | Expected |
| --- | --- | --- | --- |
| D1 | Container runs with the project | `ddev describe` / `docker ps --filter name=ddev-novel-forge-ai-redis` | `ddev-novel-forge-ai-redis` up |
| D2 | Service name resolves inside DDEV | `ddev exec ping -c1 redis` (or `getent hosts redis`) | resolves |
| D3 | Redis answers | `ddev exec redis-cli -h redis -p 6379 ping` | `PONG` |
| D4 | Password not required | same command without `-a` | no `NOAUTH` error |
| D5 | PHP extension present | checklist B15 | `redis` listed |
| D6 | Laravel config resolves | `ddev artisan tinker` → `config('database.redis.default.host')` | `redis` |
| D7 | Cache round-trip | `ddev artisan tinker` → `Cache::store('redis')->put('ddev-probe','ok',60); Cache::store('redis')->get('ddev-probe');` | `"ok"` |
| D8 | Config values applied | `ddev exec redis-cli -h redis config get maxmemory` / `maxmemory-policy` | `2048mb` (or bytes), `allkeys-lfu` |
| D9 | Persistence writes | `ddev exec redis-cli -h redis info persistence` | RDB enabled, no errors |
| D10 | Isolation | `docker volume ls \| findstr redis` | volume prefixed with the project name |
| D11 | Not exposed to host | `netstat -ano \| findstr 6379` | no listener on the host from the DDEV service |
| D12 | Survives `ddev restart` | restart, repeat D3 | works; data per the configured RDB policy |

Not required for this project: making Redis the default cache/session backend (a separate product decision).

---

## E. Memcached

| # | Check | How | Expected |
| --- | --- | --- | --- |
| E1 | Container runs with the project | `docker ps --filter name=ddev-novel-forge-ai-memcached` | up |
| E2 | Service name resolves inside DDEV | `ddev exec ping -c1 memcached` | resolves |
| E3 | Port reachable | `ddev exec bash -lc "echo stats \| nc -w1 memcached 11211"` (or `php -r` probe) | `STAT` lines |
| E4 | PHP extension present | checklist B16 | `memcached`, not `memcache` |
| E5 | Laravel config resolves | `ddev artisan tinker` → `config('cache.stores.memcached.servers')` | host `memcached`, port `11211` |
| E6 | Cache round-trip | `ddev artisan tinker` → `Cache::store('memcached')->put('ddev-probe','ok',60); Cache::store('memcached')->get('ddev-probe');` | `"ok"` |
| E7 | Memory limit applied | `docker exec ddev-novel-forge-ai-memcached memcached -h` / check stats `limit_maxbytes` | 128 MB |
| E8 | Isolation | `docker ps` / volumes | only this project's container |
| E9 | Not exposed to host | `netstat -ano \| findstr 11211` | no host listener |
| E10 | Survives `ddev restart` | restart, repeat E3 | works (cache is empty, as expected) |

---

## F. Frontend

| # | Check | How | Expected |
| --- | --- | --- | --- |
| F1 | Node version | `ddev exec node -v` | v24.x |
| F2 | Package manager | `ddev exec npm -v` | npm 10/11 |
| F3 | Dependencies install | `ddev npm install` | completes, no blocking peer errors |
| F4 | Vite starts | `ddev npm run dev` | "ready" on port 5173 |
| F5 | Asset page loads | open the site with the dev server running | page renders with built-in dev assets |
| F6 | HMR over HTTPS | edit a Vue component | see F7/F8: either HMR updates, or the limitation is confirmed |
| F7 | HMR over HTTP fallback | open `http://novel-forge-ai.ddev.site:5172`, edit a component | hot update applies |
| F8 | Production build | `ddev npm run build` | completes; `public/build` populated |
| F9 | Built app works | load the site after the build | Vue app boots, Inertia navigation works |
| F10 | Console clean | browser devtools | no CORS/mixed-content errors beyond the known HMR limitation |

Record which HMR path actually worked (HTTPS 5173, HTTP 5172, or build-only), because this is the known
limitation FMI-03.

---

## G. Ollama and Qwen3

All commands are manual, from `C:\development\Laravel\novel-forge-ai\ollama`.

| # | Check | How | Expected |
| --- | --- | --- | --- |
| G1 | Ollama starts independently | `.\scripts\start.ps1` | container `novel-forge-ai-ollama-standalone` starts |
| G2 | DDEV did not start it | start DDEV **without** running G1 | no Ollama container appears |
| G3 | API answers on loopback | `curl http://127.0.0.1:11434/api/version` | JSON `version` |
| G4 | API not on LAN | `curl http://<host-lan-ip>:11434/api/version` | connection refused |
| G5 | Model storage volume | `docker volume inspect novel-forge-ai-ollama-models` | volume exists, mounted at `/root/.ollama` |
| G6 | Download Qwen3 | `.\scripts\pull-qwen3.ps1` | model `qwen3:4b` pulled, confirmation prompt shown |
| G7 | Model listed | `.\scripts\list-models.ps1` | `qwen3:4b` present |
| G8 | Direct inference | `docker exec novel-forge-ai-ollama-standalone ollama run qwen3:4b "Say OK"` | a response is produced |
| G9 | Application connectivity | in DDEV: `ddev exec php artisan tinker` → call the AI service/health action | request reaches Ollama, no connection error |
| G10 | Real generation | trigger one short generation in the UI | completes within the 1500 s timeout |
| G11 | Long request timeout | observe a request > 60 s | nginx does not return 504 before 1800 s |
| G12 | GPU path (if supported) | use the GPU override from `ollama/README.md` | `ollama ps` shows GPU usage |
| G13 | CPU fallback | stop the GPU override, restart on CPU | inference still works, slower |
| G14 | Multiple clients | a second DDEV project reaches `host.docker.internal:11434` | works (single host only) |
| G15 | Stop without affecting DDEV | `.\scripts\stop.ps1` | DDEV site still responds |
| G16 | Models survive container removal | `.\scripts\remove-container.ps1`, then recreate/start | `qwen3:4b` still listed, no re-download |
| G17 | Purge safeguard | run `purge-all.ps1` and **decline** the confirmation | nothing deleted, script exits safely |

GPU check: if you cannot determine GPU capability, record **NOT VERIFIED — HARDWARE RUNTIME TEST REQUIRED.**
If the host has no supported GPU, CPU fallback (G13) is the acceptance criterion.

---

## H. Documentation and Git

| # | Check | How | Expected |
| --- | --- | --- | --- |
| H1 | Root README accurate | read `README.md` | matches the running environment |
| H2 | Installation guide accurate | follow `docs/installation.md` steps on a scratch clone | steps work as written |
| H3 | Development guide accurate | read `docs/development-environment.md` | commands match `ddev` behaviour |
| H4 | PHP guide accurate | compare `docs/php-configuration.md` with B2–B13 | values agree |
| H5 | Cache guide accurate | compare `docs/cache-services.md` with D/E | values agree |
| H6 | Ollama guide accurate | compare `ollama/README.md` with G | values agree |
| H7 | Internal links work | open the links in `docs/*.md` and `README.md` | resolve (7 legacy-archive README links are known and accepted) |
| H8 | Required configs tracked | `git ls-files src/.ddev ollama` | config, php.ini, compose files, scripts, docs listed |
| H9 | Secrets excluded | `git check-ignore -v src/.env ollama/.env` | both ignored; `git ls-files` shows no real `.env`/`.sqlite` |
| H10 | Legacy archive intact | `git status archive/legacy-docker` and re-hash the 14 files | unchanged, hashes match the manifest |
| H11 | No protected source drift after runtime work | recompute `docs/temp-development-source-baseline.sha256` | 351/351 match |
| H12 | Working tree is understood | `git status --porcelain` | only expected files; nothing surprising |

If H11 shows drift, stop and identify the file before committing anything. Only runtime-generated artefacts
(`public/build`, `storage/logs`, caches) should appear, and they are ignored.

---

## Closing the loop

1. Record the overall result and any `[F]`/`[L]` items.
2. Add new findings to `docs/final-migration-issues.md` with a stable `FMI-nn` ID; update the status of
   existing issues you resolved.
3. Update `docs/temp-development-requirement-task.md` Step 8 with the observed evidence and the real
   completion date, then append the closure record to `docs/temp-development-ai-development-process.md`.
4. Only after runtime evidence exists may the project be described as runtime verified. Until then the
   correct label is **READY FOR MANUAL RUNTIME VERIFICATION**.

**Runtime disclaimer:** NOT STARTED BY AGENT — DOCKER AND DDEV WERE NOT STARTED. NO CONTAINERS WERE BUILT OR
STARTED. NO DEPENDENCIES OR AI MODELS WERE DOWNLOADED. NO DATABASE MIGRATIONS OR RUNTIME TESTS WERE EXECUTED.
