# Final Acceptance Report — Step 8

> **Project:** Novel Forge AI — Docker → DDEV development-environment migration
> **Verification date:** 17-Nov-2026
> **Runtime basis:** `docs/runtime-verification-report.md`
> **Checklist:** `docs/final-runtime-verification-checklist.md`
> **Issue register:** `docs/final-migration-issues.md`

## Final classification

**PARTIALLY VERIFIED**

The migration is not `NOT ACCEPTED`: the core DDEV runtime, Laravel application, SQLite database, PHP/OPcache,
Redis, Memcached, queue worker, HTTP routing, Ollama service/model/connectivity, isolation, loopback-only AI
exposure and data preservation were verified with runtime evidence.

It is not `ACCEPTED` or `ACCEPTED WITH WARNINGS` because two requested developer capabilities remain
unverified or failed under the current constraints: Vite/HMR cannot start from the existing Windows-only
native bindings, and Qwen3 did not produce a visible textual response on this host (default-context inference
was killed; reduced-context API generation returned an empty string or timed out). GPU acceleration is also
not verifiable on this hardware.

## Scope of the decision

This acceptance covers the delivered migration under the constraints explicitly in force during Step 8:

- Runtime testing, image pulls/builds, Ollama start, model download and inference were authorised.
- Dependency installs/upgrades, migrations, seeders, production builds and destructive cleanup were not
  authorised.
- Protected application source was not edited.
- Persistent data was not deleted or overwritten.

The classification therefore records what was actually verified, not what would be possible after additional
authorisation or a different host.

## Verified component summary

| Component | Result | Decisive evidence |
| --- | --- | --- |
| Docker / Docker Desktop | PASS | Client 29.7.2, Desktop 4.87.0, Engine 29.7.2, `CPUs=8`, 3.66 GiB container memory visible. |
| DDEV project | PASS | v1.25.4; project started from `src/`; URL `https://novel-forge-ai.ddev.site`; explicit `ddev restart` rebuilt and restored it. |
| HTTP / Laravel boot | PASS | HTTPS and HTTP `/up` = 200, `/` = 302 → `/home`, `/login` = 200, `/home` = 200; Laravel 13.29.0 in `local`. |
| PHP / OPcache | PASS | FPM `php-fpm -i` confirmed all required values, including 1800 s timeouts, 1024M memory, 128M uploads, 5000 input vars, UTC, OPcache On, timestamp validation and 256 MB cache. |
| Extensions | PASS | `redis`, `memcached`, `pdo_sqlite`, `sqlite3`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif`, `pcntl` observed. |
| SQLite | PASS | SQLite 3.46.1, 30 tables, 30 applied migrations, `users=16`, `novels=0`, `jobs=0`, `failed_jobs=0`, file 700,416 bytes. No migration was run. |
| Redis | PASS | Container up; ping and Laravel cache round-trip OK; `maxmemory=2147483648`, `allkeys-lfu`, RDB last save `ok`; project-scoped volume; no host listener; survived restart. |
| Memcached | PASS | Container up; set/get/delete and Laravel cache round-trip OK; `limit_maxbytes=134217728`; project-scoped; no host listener; survived restart. |
| Queue worker | PASS | Supervisord reported `success: queue entered RUNNING state` after restart; no queued or failed jobs. |
| Node/npm | PASS | Node v24.20.0 and npm 11.19.0 inside DDEV. |
| Ollama stack | PASS | `ollama/ollama:0.40.16`, healthy container, API version 0.40.16, loopback 200, LAN addresses refused. |
| Qwen3 model / persistence | PASS | `qwen3:4b` pulled and listed; model volume `novel-forge-ai-ollama-models` preserved on stop. |
| DDEV → Ollama connectivity | PASS | `host.docker.internal:11434/api/version` returned 0.40.16 from DDEV. |
| Project isolation | PASS | `ddev list` showed `novel-forge-ai` and separate `ddev-sqlite-test`; the unrelated project was untouched. |
| Corrective actions | PASS | CA-01–CA-04 resolved Mutagen lock, stale config cache, SQLite write permissions and stale storage symlink without source/data changes. |
| Preservation | PASS | SQLite file and backup unchanged in size; `.env` not edited; no destructive command or data deletion; protected hashes 351/351 after shutdown. |

## Items preventing full acceptance

### 1. Vite / HMR — BLOCKED

- `ddev npm install` was not authorised.
- The existing `node_modules/@rolldown` contains only `binding-win32-x64-msvc`, so Vite fails in the Linux
  container with `Cannot find native binding`.
- F3–F10 of the checklist could not be verified.
- FMI-03 remains independently blocked: `src/vite.config.js` hardcodes `protocol: 'ws'`, so full HMR from an
  HTTPS page requires a protected-source decision.
- Related issue: FMI-18 (and FMI-03).

### 2. Qwen3 inference — PARTIAL / FAILED

- Default 8192-context CLI inference failed after 322.39 s with `llama-server process has terminated:
  signal: killed`.
- A 2048-context API attempt timed out after 421.36 s.
- A 1024-context API request completed after 515.86 s with `Done=True`, `EvalCount=4` but an empty `response`.
- A second 1024-context request with `think=false` timed out after 600.9 s.
- There is therefore no evidence of a visible `OK` response or a successful UI generation.
- Related issue: FMI-17.

### 3. GPU acceleration — NOT VERIFIED / BLOCKED BY HARDWARE

- The host has an Intel UHD integrated GPU only; no NVIDIA/CUDA or ROCm device was observed.
- CPU fallback was verified, but it is slow and requires reduced context on this host.
- Related issue: FMI-05.

### 4. Remaining non-critical checklist gaps

The following were not exercised: 100 MB media upload, browser console check, Blade/Vue hot-reload test,
second-DDEV-project Ollama client, model persistence across container removal/recreation, and the declined
purge-safeguard test. These do not invalidate the verified core stack but prevent an unqualified acceptance.

## Configuration change carried by Step 8

`src/.ddev/config.yaml` gained `performance_mode: none` (CA-01 / FMI-16) to bypass a stale Mutagen daemon
lock. This is a DDEV performance setting only. The file remains staged as a new file from Step 7 with this
addition unstaged; nothing was committed during Step 8.

## Final state

- DDEV project `novel-forge-ai`: stopped; containers and volumes preserved.
- Ollama container: stopped (`Exited (0)`); model volume preserved.
- Redis and Ollama volumes remain; no host listener on 11434.
- `ddev-sqlite-test`: untouched.
- `ddev-router` and `ddev-ssh-agent`: left running as shared DDEV infrastructure.
- Final protected hash check: **351/351 match, 0 mismatch, 0 missing**.
- Final Git review: expected Step 7 change set plus Step 8 documentation edits, a new untracked
  `docs/runtime-verification-report.md`, and the unstaged CA-01 change; no unexpected protected-source drift.
  This report and the runtime report are also new untracked documentation at the time of writing.

## Required decisions before full acceptance

1. Authorise container-side dependency installation, then verify Vite start, HMR and production build.
2. Decide whether to edit protected `src/vite.config.js` for HTTPS HMR or retain the documented fallback.
3. Decide whether to lower the application/Ollama context window for this host, or retest on a host with more
   memory and a supported GPU.
4. Confirm whether `performance_mode: none` should be kept permanently and staged/committed with the DDEV
   configuration.

## Conclusion

Step 8 was executed and recorded. The development environment is operational for the Laravel/DDEV, database,
cache, queue, service-isolation and Ollama connectivity requirements. It is **PARTIALLY VERIFIED** until the
Vite and visible AI-generation gaps are resolved or explicitly accepted by the user.
