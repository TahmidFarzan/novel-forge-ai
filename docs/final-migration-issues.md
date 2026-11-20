# Final Migration Issues Register

> **Project:** Novel Forge AI — Docker → DDEV development-environment migration
> **Register created:** 17-Nov-2026 (Step 7 — Final Migration Verification, Integration Audit & Handover)
> **Scope:** every issue still open after Steps 1–6 were closed, plus issues discovered by the Step 7 static
> audit and by Step 8 controlled runtime verification.
> **Verification basis:** Step 7 static inspection plus Step 8 observed runtime evidence. Configuration alone
> is not recorded as runtime proof.
>
> **Runtime status: STEP 8 COMPLETE — final classification PARTIALLY VERIFIED. See
> `docs/runtime-verification-report.md` and `docs/final-acceptance-report.md`.**

## How to read this register

| Field | Meaning |
| --- | --- |
| ID | Stable identifier (`FMI-nn`), referenced from the handover and the tracker. |
| Severity | `Critical` blocks any use, `High` blocks a stated requirement, `Medium` degrades a feature or leaves a required verification open, `Low` is cosmetic/documentation, `Informational` is an accepted design trade-off. |
| Status | `OPEN`, `ACCEPTED`, `ACCEPTED WITH WARNINGS`, `BLOCKED` (cannot be resolved under the current constraints), `DEFERRED` (explicitly belongs to a later step). |

Severity here describes **static** risk. Nothing in this register is a runtime failure report — no runtime
testing was authorised in Step 7.

## Summary

| ID | Component | Severity | Status |
| --- | --- | --- | --- |
| FMI-01 | Runtime verification (all components) | Medium | ACCEPTED WITH WARNINGS — core stack verified; Vite, GPU and visible AI output remain limited |
| FMI-02 | `.env.example` defaults vs DDEV runtime | Low | ACCEPTED (protected file) |
| FMI-03 | Vite HMR over HTTPS | Medium | BLOCKED (protected `vite.config.js`; runtime install also blocked) |
| FMI-04 | Retained `docker/php/startup.dev.sh` | Low | BLOCKED (production Dockerfile) |
| FMI-05 | GPU acceleration | Medium | BLOCKED — no compatible GPU on this host |
| FMI-06 | Compose file conventions (Redis/Memcached) | Informational | ACCEPTED |
| FMI-07 | DDEV `omit_containers: [db]` trade-off | Informational | ACCEPTED |
| FMI-08 | Frozen archive README relative links | Informational | ACCEPTED |
| FMI-09 | Archive restore procedure not exercised | Low | DEFERRED — optional, not required |
| FMI-10 | No healthcheck on Redis/Memcached services | Informational | ACCEPTED |
| FMI-11 | Ollama publish scope is loopback-only | Informational | ACCEPTED — loopback 200; two LAN addresses refused |
| FMI-12 | Production `docker/` packaging status | Informational | OPEN — product decision |
| FMI-13 | Extension availability proven from upstream package list | Low | ACCEPTED — runtime-confirmed 17-Nov-2026 |
| FMI-14 | SHA-256 baseline is git-ignored (local-only evidence) | Informational | ACCEPTED |
| FMI-15 | DDEV commands must be run from `src/` | Low | ACCEPTED — checklist, handover and active docs corrected |
| FMI-16 | DDEV Mutagen daemon lock on this host | Medium | ACCEPTED WITH WARNING — `performance_mode: none` applied |
| FMI-17 | Qwen3 default 8192-context inference is not viable on this host | Medium | BLOCKED — host memory/runtime limitation; reduced-context API generation only |
| FMI-18 | Vite native binding mismatch inside Linux container | Medium | BLOCKED — container dependency install not authorised |

No `Critical` issue was found. No tracked secret, no protected-source modification and no archive checksum
mismatch was found.

---

## FMI-01 — Runtime verification of every component is outstanding

- **Component:** whole stack (Docker Desktop, DDEV, PHP/OPcache, SQLite, Redis, Memcached, Vite, Ollama/Qwen3).
- **Severity:** Medium.
- **Status:** ACCEPTED WITH WARNINGS — Step 8 controlled runtime verification completed 17-Nov-2026; see
  `docs/runtime-verification-report.md` and `docs/final-acceptance-report.md`.
- **Description:** Steps 1–7 were executed under a configuration-only policy. No container was built or
  started, no dependency was installed, no migration or test was run and no model was downloaded. Every
  "works?" claim in the documentation is therefore a configuration claim, not a runtime claim.
- **Evidence:** the whole repository state is static; `src/.ddev/` contains only the eight authored files and
  no DDEV-generated artefacts, which confirms DDEV has never run for this project.
- **Dependency:** user must start Docker Desktop and DDEV.
- **Recommended resolution:** execute `docs/final-runtime-verification-checklist.md` end to end and record the
  actual results. Do not convert any checklist item to "verified" without observing it.
- **Tracking:** Step 8 in `docs/temp-development-requirement-task.md`.
- **Resolution note (17-Nov-2026):** Docker/DDEV, PHP/OPcache, SQLite, Redis, Memcached, Laravel HTTP, queue,
  Ollama API/model/connectivity, loopback-only exposure, preservation and final hashes were verified.
  Vite/HMR, GPU and visible AI generation remain blocked/unverified, so acceptance is warning-limited.

## FMI-02 — `src/.env.example` defaults do not match the DDEV runtime values

- **Component:** Laravel environment configuration vs DDEV/Laravel startup.
- **Severity:** Low (mitigated), would be Medium if the overrides were removed.
- **Status:** ACCEPTED — `src/.env.example` is a protected file (baseline entry
  `b9a6161…` `src/.env.example`) and may not be edited by this migration.
- **Description:** the example file still carries the legacy/local defaults:

  | Key | `.env.example` | Effective DDEV value | Provided by |
  | --- | --- | --- | --- |
  | `APP_URL` | `http://localhost:8000/` | `https://novel-forge-ai.ddev.site` | `web_environment` |
  | `REDIS_HOST` | `127.0.0.1` | `redis` | `web_environment` |
  | `MEMCACHED_HOST` | `127.0.0.1` | `memcached` | `web_environment` |
  | `AI_BRAIN_URL` | `http://localhost:11434` | `http://host.docker.internal:11434` | local `src/.env` |
  | `AI_BRAIN_CONTEXT_WINDOW` | `32768` (config fallback) | `8192` | local `src/.env` |
  | `AI_BRAIN_TIMEOUT_SECONDS` | `600` (config fallback) | `1500` | local `src/.env` |

- **Evidence:** `src/config/ai-brain.php` lines 13–23 (fallbacks), `src/.env` (untracked, actual values),
  `src/.ddev/config.yaml` `web_environment` (lines 39–52), `docs/cache-services.md` and
  `docs/development-environment.md` (document the effective values).
- **Why it is safe today:** Laravel's dotenv repository is immutable, so real container environment variables
  win over `.env`; `src/.env` already holds the DDEV and AI values; and the checklist tells the new developer
  to copy `.env.example` and then apply the documented overrides.
- **Dependency:** changing `.env.example` requires lifting source protection — a separate decision.
- **Recommended resolution:** leave as-is for now. If source protection is ever lifted, align
  `APP_URL`, `REDIS_HOST`, `MEMCACHED_HOST` and the AI defaults with the DDEV values and re-baseline the hash.

## FMI-03 — Full Vite HMR over HTTPS is not possible without editing protected source

- **Component:** Vite 8 / `laravel-vite-plugin` / DDEV `web_extra_exposed_ports`.
- **Severity:** Medium (developer experience only; production build is unaffected).
- **Status:** BLOCKED — `src/vite.config.js` is protected (baseline `adcdf4a…`). Step 8 additionally found
  that the vendored `node_modules/@rolldown` has no Linux native binding, so installing/running Vite inside
  the DDEV container is blocked without an authorised dependency install.
- **Description:** `src/vite.config.js` hardcodes `server.hmr.protocol: 'ws'` while the page is served over
  `https://novel-forge-ai.ddev.site`. Browsers block a `ws://` socket opened from an HTTPS page as mixed
  content, so the primary Vite origin `https://novel-forge-ai.ddev.site:5173` cannot deliver a working HMR
  socket. Step 2 mitigated this by publishing the extra HTTP port 5172 (`VITE_HMR_CLIENT_PORT=5172`) and by
  documenting the `npm run build` fallback.
- **Evidence:** `src/vite.config.js` (`port: 5173`, `strictPort: true`, `protocol: 'ws'`),
  `src/.ddev/config.yaml` `web_extra_exposed_ports` and `web_environment`,
  `docs/development-environment.md` lines 101–110.
- **Dependency:** requires a one-line source change (`protocol: 'wss'` or a conditional) or the build fallback.
- **Recommended resolution:** prefer the documented HTTP-port or build fallback during Step 8. Only after an
  explicit user decision to edit protected source should `wss` be introduced; if that happens, re-baseline
  `src/vite.config.js`.

## FMI-04 — `docker/php/startup.dev.sh` remains active

- **Component:** retained production Docker packaging.
- **Severity:** Low.
- **Status:** BLOCKED.
- **Description:** Step 6 archived the two development-only compose/nginx files. `startup.dev.sh` could not be
  removed because the retained production `docker/php/Dockerfile` copies it into the image; deleting it would
  break a production build. It therefore stays in `docker/php/` **and** in
  `archive/legacy-docker/docker/php/startup.dev.sh`.
- **Evidence:** `docker/php/Dockerfile` (`COPY` of the script), `archive/legacy-docker/ARCHIVE-MANIFEST.md`.
- **Dependency:** production stack retirement or a Dockerfile change — a separate, user-owned decision.
- **Recommended resolution:** no action during the migration. Remove it only when the production Dockerfile is
  retired or rewritten; the archive copy already preserves the file.

## FMI-05 — GPU acceleration for Ollama is unverified

- **Component:** Ollama container / host GPU.
- **Severity:** Medium.
- **Status:** BLOCKED — this host has an Intel UHD integrated GPU only; no NVIDIA/CUDA or ROCm device was
  observed. GPU acceleration cannot be verified here. CPU fallback is the applicable runtime path.
- **Description:** the base `ollama/compose.yaml` deliberately declares no GPU reservation so that CPU-only
  hosts can start the stack. GPU support is an opt-in override documented in `ollama/README.md`. The host GPU
  model, driver version and Docker GPU passthrough were not inspected at runtime; even the presence of a
  compatible GPU has not been confirmed by the agent.
- **Evidence:** `ollama/compose.yaml` (no `deploy.resources.reservations.devices`, no `runtime`),
  `ollama/README.md` (GPU profile section), `docs/development-environment.md`.
- **Dependency:** host hardware and driver availability.
- **Recommended resolution:** user runs the documented GPU override and records whether inference lands on GPU
  or CPU. CPU fallback is the expected default and must be confirmed independently.

## FMI-06 — Minor Compose conventions differ between the two cache services

- **Component:** `src/.ddev/docker-compose.redis.yaml`, `src/.ddev/docker-compose.memcached.yaml`.
- **Severity:** Informational.
- **Status:** ACCEPTED.
- **Description:** two cosmetic inconsistencies, neither functional:
  1. Redis declares `volumes:` → `redis:` (a `null` value = Compose anonymous/default named volume).
     The Step 7 assertion that used `isset()` reported a false failure; `array_key_exists()` returns `true`
     and Compose treats the key as a project-scoped volume. No change needed.
  2. Memcached uses the unbraced form `$DDEV_APPROOT` in its `com.ddev.approot` label while Redis uses
     `${DDEV_APPROOT}`. Compose interpolation accepts both forms and expands the same value.
- **Evidence:** both compose files; Step 7 semantic check output; `array_key_exists()` re-check.
- **Recommended resolution:** none. If the files are ever re-generated from the upstream add-ons, both will
  converge automatically.

## FMI-07 — `omit_containers: [db]` removes DDEV database helper features

- **Component:** DDEV configuration / SQLite.
- **Severity:** Informational.
- **Status:** ACCEPTED (documented trade-off).
- **Description:** the application uses SQLite at `src/database/database.sqlite`, so the database container is
  omitted to save memory on this 7.68 GB host. Consequence: `ddev snapshot`, `ddev import-db`,
  `ddev describe` database rows and similar helpers are unavailable; backups must be plain file copies of the
  `.sqlite` file.
- **Evidence:** `src/.ddev/config.yaml` lines 17–21; `docs/installation.md`, `docs/development-environment.md`,
  `docs/troubleshooting.md`.
- **Recommended resolution:** keep the file-copy backup procedure documented in the handover. Re-enable the db
  container only if a future requirement needs MySQL/PostgreSQL.

## FMI-08 — Seven relative links are unresolved inside the frozen archive README

- **Component:** `archive/legacy-docker/docker/README.md`.
- **Severity:** Informational.
- **Status:** ACCEPTED.
- **Description:** the Step 7 link check scanned 25 Markdown files and 101 relative links. Seven do not resolve,
  and all seven live in the byte-for-byte archive snapshot of the legacy `docker/README.md`, which referenced
  paths that only exist in the original pre-migration layout. The archive is intentionally frozen; editing it
  would invalidate the archived SHA-256 record.
- **Evidence:** archive manifest hash comparison (14/14 files match); link check output.
- **Recommended resolution:** none inside the archive. `archive/legacy-docker/README.md` explains the snapshot
  nature and points readers to the live docs.

## FMI-09 — Archive restoration has not been exercised

- **Component:** `archive/legacy-docker/`.
- **Severity:** Low.
- **Status:** DEFERRED — optional Step 8 activity.
- **Description:** restoration is documented (copy file back to its original path, or `git checkout` the
  pre-migration revision), but no restore was performed, because that would recreate legacy development
  configuration in an active tree.
- **Evidence:** `archive/legacy-docker/README.md` (restore section), `ARCHIVE-MANIFEST.md` (original paths).
- **Recommended resolution:** leave untested. If a restore is ever needed, do it on a branch and verify the
  hashes against the manifest afterwards.

## FMI-10 — Redis and Memcached services declare no healthcheck

- **Component:** `docker-compose.redis.yaml`, `docker-compose.memcached.yaml`.
- **Severity:** Informational.
- **Status:** ACCEPTED.
- **Description:** this mirrors the upstream DDEV add-ons, which rely on Compose start ordering plus
  `restart: "no"` and on Laravel tolerating a cold cache backend. There is no readiness probe.
- **Evidence:** both compose files.
- **Recommended resolution:** if cold-start cache errors appear in Step 8, add a healthcheck in a later
  configuration-only step; not required for handover.

## FMI-11 — Ollama is reachable only from the local machine

- **Component:** `ollama/compose.yaml` port publishing.
- **Severity:** Informational.
- **Status:** ACCEPTED.
- **Description:** the API is published as `${OLLAMA_HOST_BIND:-127.0.0.1}:11434`, i.e. loopback-only on the
  host. Any Laravel/DDEV container on the **same** machine reaches it through
  `http://host.docker.internal:11434`, which is the configured and documented path. A developer on another
  machine cannot reach it, and the stack exposes no authentication. This is the intended security posture:
  never publish the API publicly as a workaround.
- **Evidence:** `ollama/compose.yaml`, `ollama/README.md` (networking/security sections),
  `src/.env` (`AI_BRAIN_URL`).
- **Recommended resolution:** for remote developers, run their own Ollama stack locally. Do not widen the
  bind address.
- **Runtime confirmation (17-Nov-2026):** `127.0.0.1:11434` returned HTTP 200; `172.26.208.1` and
  `192.168.0.101` returned no response; no host listener remained after controlled shutdown.

## FMI-12 — The retained production `docker/` stack is unaudited for retirement

- **Component:** active `docker/` directory (12 files).
- **Severity:** Informational.
- **Status:** OPEN — product decision, not a migration defect.
- **Description:** Step 6 retained 12 production/shared files (`docker-compose.yml`, the four Dockerfiles,
  `php.ini`, `www.conf`, `entrypoint.sh`, `startup.sh`, `startup.dev.sh`, `nginx.conf`, `.env.example`,
  `README.md`). They are no longer used for development but may still be used for production packaging. The
  agent did not build or validate them.
- **Evidence:** `docker/` file inventory; `.dockerignore`; archive manifest.
- **Recommended resolution:** keep them. Retire them in a separate, explicitly authorised production task.

## FMI-13 — PHP extension availability is proven from upstream packaging, not a running container

- **Component:** DDEV `ddev-webserver` image / PHP 8.4 extensions.
- **Severity:** Low.
- **Status:** ACCEPTED — runtime confirmation completed 17-Nov-2026; `redis`, `memcached`, `opcache`,
  `pdo_sqlite`, `sqlite3`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`, `exif` and `pcntl` were observed.
- **Description:** the required extensions (`redis`, `memcached`, `opcache`, `sqlite3`, `pdo_sqlite`,
  `mbstring`, `intl`, `gd`, `imagick`, `zip`, `bcmath`, `exif`, `pcntl`) were confirmed against the upstream
  DDEV PHP package list for PHP 8.4 (`containers/ddev-webserver/generic-files/etc/php-packages.yaml`), not
  from `php -m` inside a started container. The project does not add or remove extensions itself.
- **Evidence:** upstream `php-packages.yaml`; `docs/php-configuration.md`; `docs/troubleshooting.md`
  ("Missing extension" section, already labelled NOT RUNTIME TESTED).
- **Recommended resolution:** Step 8 checklist section B (`ddev exec php -m`). If an extension is missing,
  add it through the supported DDEV mechanism in a later configuration-only step.

## FMI-14 — The protected-source baseline is git-ignored, so it is local-only evidence

- **Component:** `docs/temp-development-source-baseline.sha256`.
- **Severity:** Informational.
- **Status:** ACCEPTED (deliberate: it is a working artefact, not project documentation).
- **Description:** the 351-entry SHA-256 baseline is excluded by `.gitignore:227`. Integrity can therefore be
  established on this machine but not independently re-verified from a fresh clone, because the baseline file
  would not be present. The Step 7 comparison (351/351 match) is valid for this working copy.
- **Evidence:** `git check-ignore -v docs/temp-development-source-baseline.sha256` → `.gitignore:227`.
- **Recommended resolution:** none required for handover. If a permanent integrity record is wanted later,
  commit a copy under a non-ignored path **with explicit user approval**; do not silently un-ignore it.

## FMI-15 — DDEV commands must be run from `src/`

- **Component:** runtime checklist and active developer documentation.
- **Severity:** Low.
- **Status:** ACCEPTED — checklist, handover and active DDEV docs now state `src/`; root README,
  `docs/command-reference.md` and `docs/troubleshooting.md` were already correct.
- **Description:** the Step 7 checklist told the operator to run `ddev describe` from the repository root.
  Runtime verification showed that this fails with `no .ddev\config.yaml file was found in this directory or
  any parent`; the DDEV configuration lives at `src/.ddev/config.yaml`.
- **Evidence:** root-level `ddev describe`/`ddev start` failure; successful `ddev start`, `ddev restart` and
  `ddev describe` from `C:\development\Laravel\novel-forge-ai\src`.
- **Resolution so far:** `docs/final-runtime-verification-checklist.md` A4,
  `docs/final-migration-handover.md` and `docs/development-environment.md` now state that commands run from
  `src/`. `docs/troubleshooting.md` and `docs/command-reference.md` were already correct.
- **Recommended resolution:** final Markdown sweep for any remaining repository-root DDEV instructions.

## FMI-16 — Mutagen daemon lock prevented project sync

- **Component:** DDEV performance mode.
- **Severity:** Medium (blocks startup without the workaround).
- **Status:** ACCEPTED WITH WARNING — configuration-only mitigation applied.
- **Description:** the first DDEV start never completed Mutagen synchronization. `ddev mutagen logs` showed
  `unable to acquire daemon lock: The process cannot access the file because another process has locked a
  portion of the file`.
- **Evidence:** failed sync before the change; successful start after adding `performance_mode: none`; DDEV
  restart later rebuilt the project and reached ready state.
- **Resolution:** added `performance_mode: none` to `src/.ddev/config.yaml` (CA-01). This uses a direct bind
  mount instead of Mutagen on this host. It is a DDEV-only setting; no protected application source or
  persistent data changed.
- **Warning:** the host-specific lock remains unexplained and may return if performance mode is changed.

## FMI-17 — Default 8192-context Qwen3 inference is not viable on this host

- **Component:** independent Ollama/Qwen3 stack on CPU.
- **Severity:** Medium (AI generation requirement is only partly satisfied).
- **Status:** BLOCKED — reduced-context API generation is the only working path on this host; resolving the
  default-context failure requires a hardware/configuration change approved separately.
- **Description:** `ollama run qwen3:4b 'Say OK'` at the configured 8192 context failed after 322.39 s because
  the llama-server process was killed. A 2048-context request timed out, a 1024-context API request completed
  with an empty response after 515.86 s, and a second 1024-context `think=false` request timed out after
  600.9 s.
- **Evidence:** exact timings and log excerpts are recorded in `docs/runtime-verification-report.md`.
- **Recommended resolution:** treat this host as reduced-context CPU-only, lower the application context
  setting through an explicit user decision, and retest visible generation on a host with more memory or a
  supported GPU. Do not silently change `src/.env` or `ollama/compose.yaml`.

## FMI-18 — Vite cannot start from the existing Windows-only `node_modules`

- **Component:** Vite 8 / Rolldown native bindings inside the DDEV Linux container.
- **Severity:** Medium.
- **Status:** BLOCKED — container-side dependency install is not authorised.
- **Description:** Node/npm are present in DDEV, but `node_modules/@rolldown` contains only
  `binding-win32-x64-msvc`. Starting Vite in the Linux container fails with `Cannot find native binding`.
- **Evidence:** `ddev exec node -v` → v24.20.0; `ddev exec npm -v` → 11.19.0; Vite start error; directory
  listing of `node_modules/@rolldown`.
- **Dependency:** explicit authorisation to run `ddev npm install` (or equivalent) is required.
- **Recommended resolution:** after approval, install dependencies inside the container and rerun F3–F10;
  keep FMI-03 (HTTPS HMR) separate because it still requires a protected-source decision.

---

## Issues deliberately **not** raised

- **Redis/Memcached are not the default cache/session/queue backend** — this is the application's existing
  configuration (`CACHE_STORE=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`) and a user
  decision, not a migration defect. Both services are available and documented.
- **No `.ddev/.gitignore`** — DDEV never ran, so no generated artefacts exist to ignore. DDEV creates the
  file on first start; it is not needed in the repository beforehand.
- **Committed `.env.example` files** — these are templates with placeholder values; the real `src/.env` and
  `ollama/.env` are ignored and untracked (verified).
- **Composer/NPM install state** — `src/vendor/` and `src/node_modules/` exist from earlier developer work and
  are ignored; no dependency change was made by the migration.

---

## Change control for this register

This document is append-and-refine, not a rewrite log. When an issue is closed, change its **Status** and add a
one-line resolution note with the date. Never delete a row: the history of an accepted trade-off is itself
useful to the next developer.
