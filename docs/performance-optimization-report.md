# Performance Optimization Report — Step 9

> **Project:** Novel Forge AI — DDEV + Redis + Memcached + independent Ollama/Qwen3
> **Execution date:** 17-Nov-2026, 10:45 PM → 11:00 PM +06:00
> **Baseline:** `docs/performance-baseline.md`
> **Runtime basis:** fresh Step 9 measurements and Step 8 evidence explicitly cited below
> **Final status:** **PARTIALLY OPTIMIZED**

---

## A. Executive Summary

Step 8 supplied sufficient runtime evidence to proceed (`PARTIALLY VERIFIED`). Step 9 measured the actual host,
Docker/DDEV startup, Laravel response latency, PHP/OPcache, SQLite, Redis, Memcached, Laravel cache state,
Vite, Ollama and Qwen3. Those measurements showed that the current development configuration is already
appropriately sized for this 8 GB, CPU-only host.

**No new runtime configuration change was applied in Step 9.** Every candidate change was checked against
observed values:

- Docker has 8 logical CPUs and 3.66 GiB visible to containers; no `.wslconfig` or Docker Desktop CPU/memory
  keys exist to justify an arbitrary cap.
- DDEV already omits the unused `db` container and uses a direct bind mount because Mutagen is unusable on
  this host (Step 8 CA-01).
- PHP `memory_limit=1024M`, `max_execution_time=1800`, 128 MB uploads and OPcache 256 MB / 20,000 files are
  not exhausted by measured usage; timestamp validation remains enabled for development.
- SQLite, Redis and Memcached show healthy latency, zero evictions and no corruption.
- Laravel debug mode and uncached config/routes are appropriate for development; production optimization
  commands were not authorized.
- Vite remains blocked by a missing Linux native binding, not by a tunable setting.
- Ollama starts quickly and is healthy, but Qwen3 inference is constrained by CPU-only hardware and low
  host RAM; default 8192 context was killed in Step 8.

Post-audit regression checks passed for all previously verified core functionality. Because no safe
optimization was justified, "before and after" values are effectively unchanged; this is a measured
no-change decision, not a claim of improvement. The final classification is **PARTIALLY OPTIMIZED** because
the audit, monitoring guidance and regression verification are complete, while Vite/HMR, visible Qwen3
generation and GPU acceleration remain blocked by constraints outside the authorized Step 9 scope.

---

## B. Hardware Profile

| Item | Observed value |
| --- | --- |
| OS | Microsoft Windows 11 Home Single Language, build 10.0.26300 |
| CPU | 12th Gen Intel(R) Core(TM) i3-1215U |
| CPU cores / logical processors | 6 / 8 |
| Total host RAM | 8,245,456,896 bytes (7.68 GiB) |
| Free RAM before Step 9 service start | 825,229,312 bytes (≈787 MiB) |
| Free RAM after DDEV + Ollama start | 857,567,232 bytes (≈818 MiB) |
| GPU | Intel(R) UHD Graphics, driver 32.0.101.5542 |
| GPU memory reported by WMI | 4,122,728,448 bytes shared adapter memory; not dedicated VRAM |
| GPU acceleration | none; no NVIDIA/CUDA/ROCm evidence |
| System disk C: | 511,069,646,848 bytes total; 329,771,057,152 bytes free; fixed disk |
| WSL2 | `Ubuntu` stopped; `docker-desktop` running; both WSL version 2 |
| WSL config | no `%USERPROFILE%\.wslconfig` found |
| Docker Desktop settings | `settings-store.json` present; no CPU/memory allocation keys |
| Docker effective allocation | `NCPU=8`; `MemTotal=3,933,859,840` bytes (3.66 GiB) |
| Docker storage driver | `overlayfs` |

The host is memory-constrained. The Docker limit is approximately half of physical RAM, which leaves the
Windows host and shared WSL/Docker processes room. No CPU or RAM limit was changed.

---

## C. Baseline Measurements

Full context and raw values are in `docs/performance-baseline.md`.

| Metric | Baseline |
| --- | --- |
| DDEV start | 33.17 s |
| Ollama start | 2.78 s |
| Vite start attempt | 4.46 s to failure (`Cannot find native binding`) |
| HTTPS `/up`, cold, median of 5 | 5.676 s |
| HTTP `/up`, cold, median of 5 | 2.498 s |
| HTTPS `/up`, warm, median of 10 | 1.997 s (min 1.787 s, max 3.147 s) |
| PHP CLI memory | 0.34 MiB current / 0.38 MiB peak |
| SQLite 20 `select 1` queries | 0.13 ms total |
| Redis 100 SET+GET cycles | 45.59 ms |
| Memcached 100 SET+GET cycles | 42.13 ms |
| Ollama idle memory | 327.8 MiB / 3.664 GiB |
| Qwen3 model load at `num_ctx=1024` | 292,097 ms (Step 8) |
| Qwen3 generated 4 tokens at `num_ctx=1024` | 187,871 ms evaluation (Step 8) |
| GPU memory usage | not applicable; no GPU path |

Test conditions: only `novel-forge-ai` DDEV project active; `ddev-router` and `ddev-ssh-agent` running;
Ollama started independently and healthy; Qwen3 model present but not loaded; Vite not running; no stress
test, no concurrency test, no cleanup and no persistent-data modification.

---

## D. Docker and DDEV

### Observed

- Docker: `NCPU=8`, `MemTotal=3,933,859,840`, `overlayfs`.
- Docker storage: images 11.37 GB, containers 53.25 kB, local volumes 2.497 GB, build cache 594.3 MB.
- 9 images, 3 containers, 5 local volumes; 4 volumes active.
- Project volumes `ddev_novel-forge-ai_redis` and `novel-forge-ai-ollama-models` are preserved.
- `ddev start` took 33.17 s: custom image build 8 s, web ready in 8.1 s, Redis/Memcached ready in 0.6 s.
- Idle project containers: web 248.9 MiB / 12.16%, Redis 10.18 MiB / 0.21%, Memcached 5.828 MiB / 0.02%.
- Shared containers: router 112.8 MiB, ssh-agent 5.621 MiB, both near 0% CPU.
- After Ollama start, its idle usage was 327.8 MiB / 0.00%.
- A later `docker stats` sample showed web at 80.99% CPU while probes were running; this is an instantaneous
  sample, not a sustained average, and is insufficient evidence for a PHP-FPM or CPU-limit change.
- `ddev list` showed `novel-forge-ai` OK; the separate `ddev-sqlite-test` project was not started.

### Decision

No Docker CPU/memory allocation change was applied. There is no host `.wslconfig` or Docker Desktop settings
key to edit, and the effective 8 CPU / 3.66 GiB allocation is the measured baseline. Adding arbitrary limits
would risk slowing the host or the containers without evidence of contention.

No DDEV structural change was applied. The existing `omit_containers: [db]` already saves a database-server
container because the application uses SQLite. CA-01 (`performance_mode: none`) from Step 8 is retained: it
bypasses the stale Mutagen daemon lock and is the only reason the project starts reliably on this host.

---

## E. PHP and OPcache

### Observed

- Verified runtime tools: PHP 8.4.24, Composer 2.11.2 inside DDEV. AGENTS.md was read; no Laravel Boost or
  dependency installation was performed because this step changes no application source and no such install
  was explicitly requested.
- FPM values: `max_execution_time=1800`, `memory_limit=1024M`, `opcache.enable=On`,
  `opcache.validate_timestamps=On`.
- Step 8 also verified `post_max_size=128M`, `upload_max_filesize=128M`, `max_file_uploads=50`,
  `max_input_vars=5000`, `date.timezone=UTC`, `opcache.revalidate_freq=0`,
  `opcache.memory_consumption=256`, `opcache.max_accelerated_files=20000`.
- Step 9 OPcache CLI status: enabled; 33.59 MiB used, 222.41 MiB free, `max_cached_keys=32531`.
- CLI OPcache probe showed 0 cached scripts; this CLI process does not share FPM cache state and is not a
  reason to resize FPM OPcache.
- PHP CLI probe used 0.34 MiB current / 0.38 MiB peak.

### Decision

No PHP or OPcache change was applied. The configured limits are development-appropriate, no evidence of
memory exhaustion or OPcache pressure exists, and disabling `validate_timestamps` would break the
development-friendly automatic code-change detection explicitly required by the prompt.

---

## F. Database

### Observed

- SQLite file and backup: 700,416 bytes each; untouched.
- `integrity_check=ok`.
- `journal_mode=delete`, `page_size=4096`, `page_count=171`, `busy_timeout=60000`.
- 30 application tables, 48 indexes, 30 applied migrations.
- 20 direct `select 1` queries took 0.13 ms total.
- Regression query: `users=16`, `jobs=0`, `migrations=30`.
- No migration, seeder, schema change or destructive database command ran.

### Decision

No database change was applied. There is no slow-query evidence, lock error, integrity problem or missing
index evidence. Changing journal mode, page size, busy timeout or schema would violate the evidence and
safety constraints.

---

## G. Redis and Memcached

### Redis — observed

- 100 SET+GET cycles: 45.59 ms (temporary unique key deleted; no flush).
- `used_memory=1,495,336`; `used_memory_rss=25,219,072`; `maxmemory=2,147,483,648`.
- Fragmentation ratio 17.07: high ratio, but absolute RSS is only about 24 MiB; no leak pattern was measured.
- 1 connected client, 0 blocked clients, 210 commands processed.
- 100 hits / 0 misses / 0 evictions; 0 expired keys.
- Step 8 verified `allkeys-lfu` and RDB last-save status `ok`.
- Regression check: `PONG`.

### Memcached — observed

- 100 SET+GET cycles: 42.13 ms (temporary unique key deleted; no flush).
- `limit_maxbytes=134,217,728`; `bytes=0`; 100 hits / 0 misses / 0 evictions.
- 2 current connections, 4 total connections.
- Regression check: `uptime=298`, `limit=134217728`.

### Decision

No Redis or Memcached change was applied. Both services are healthy, project-scoped, low-memory and show no
eviction or miss pressure. Changing Redis eviction policy or `maxmemory`, or raising Memcached's 128 MiB,
would be blind tuning. No flush, delete or persistent-data change was performed.

---

## H. Laravel and Vite

### Laravel — observed

`ddev exec php artisan about` reported:

- Laravel 13.29.0, PHP 8.4.24, Composer 2.11.2.
- Environment `local`, debug ENABLED.
- Config NOT CACHED, events NOT CACHED, routes NOT CACHED, views cached.
- Cache/session/queue: `database`.
- URL `https://novel-forge-ai.ddev.site`, maintenance OFF, storage link OK.
- Step 8 verified Laravel cache round-trips through Redis and Memcached and a RUNNING queue daemon.
- Step 9 regression queue log: `success: queue entered RUNNING state`.

### Vite — observed

`ddev npm run dev` failed after 4.46 s:

- `Cannot find native binding`
- missing `@rolldown/binding-linux-x64-gnu` / fallback binding unavailable.
- Existing `node_modules/@rolldown` contains the Windows binding only (Step 8 evidence).
- Dependency installation inside the container is not authorized.
- `src/vite.config.js` is protected; its hardcoded `ws` protocol also blocks full HTTPS HMR (FMI-03).

### Decision

No Laravel optimization command was run. `config:cache` and `route:cache` are production-oriented and would
be inappropriate while active development changes are expected; they were not authorized. The cached views
state is preserved. Vite is recorded as BLOCKED, not slow: the bottleneck is a missing native dependency and
a protected-source HMR limitation, not a tuning parameter.

---

## I. Ollama and Qwen3

### Ollama — observed

- Start time: 2.78 s with the image already cached.
- `restart: "no"` preserved; container is manually managed.
- Health status `healthy`; API `{"version":"0.40.16"}`.
- API remains loopback-only at `127.0.0.1:11434` (Step 8 verified LAN refusal).
- Idle container usage: 327.8 MiB / 3.664 GiB, 0.00% CPU.
- DDEV → `host.docker.internal:11434` connectivity was verified in Step 8.
- Model storage `/root/.ollama` is 2.4G inside the preserved `novel-forge-ai-ollama-models` volume.

### Qwen3 — observed (Step 8 evidence, not repeated)

- Model `qwen3:4b`, ID `359d7dd4bcda`, 2,497,293,931 bytes.
- Default `num_ctx=8192` failed after 322.39 s: `llama-server process has terminated: signal: killed`.
- `num_ctx=2048` timed out after 421.36 s.
- `num_ctx=1024`, `num_predict=4`: load 292,097 ms, prompt eval 36,698 ms, eval 187,871 ms for 4 tokens,
  total 515.9 s, `Done=True`, but `response=''`.
- A second `num_ctx=1024`, `think=false`, `num_predict=16` request timed out after 600.9 s.
- No visible `OK` response and no successful UI generation were observed.
- GPU acceleration is not applicable: Intel UHD iGPU only; no CUDA/ROCm runtime.

### Decision

No Ollama configuration was changed. The configured `OLLAMA_CONTEXT_LENGTH=8192` is not viable on this host
in practice, but no alternative context value was measured to produce visible output. Lowering it blindly
would trade one failure mode for another. The model, volume and image were preserved; no alternative model
was downloaded.

---

## J. Changes Made

| Change ID | Component | Step 9 action | Reason |
| --- | --- | --- | --- |
| — | All components | **No new configuration change applied** | Every measured value was already within an acceptable development range, or the blocker was outside configuration scope. |

Configuration carried in from Step 8 and intentionally retained:

| ID | File | Setting | Status |
| --- | --- | --- | --- |
| CA-01 | `src/.ddev/config.yaml` | `performance_mode: none` | Retained from Step 8; required to bypass the stale Mutagen daemon lock. No Step 9 backup/rollback was needed because Step 9 did not modify it. |

No dependency install/upgrade, protected-source edit, schema change, migration, seeder, production build,
image/volume cleanup, model removal or destructive command was executed.

---

## K. Before-and-After Comparison

No optimization was applied, so there is no legitimate improvement delta to claim. The table compares the
Step 9 baseline with the post-audit regression checks under the same services and configuration.

| Check | Baseline | Post-audit | Comparison |
| --- | --- | --- | --- |
| HTTPS `/up` status | 200 | 200 | unchanged / regression pass |
| HTTP `/up` status | 200 | 200 | unchanged / regression pass |
| Queue daemon | RUNNING | RUNNING | unchanged / regression pass |
| SQLite data | `users=16 jobs=0 migrations=30` | `users=16 jobs=0 migrations=30` | unchanged |
| Redis ping | PONG | PONG | unchanged |
| Memcached limit | 134,217,728 | 134,217,728 | unchanged |
| Ollama version | 0.40.16 | 0.40.16 | unchanged |
| DDEV project state | OK | OK | unchanged |
| Vite | failed in 4.46 s | not retested after failure | unchanged / BLOCKED |
| Qwen3 visible output | not observed in Step 8 | not repeated in Step 9 | unchanged / BLOCKED |

The only measured performance "improvement" was protocol warm-up: HTTPS `/up` median fell from 5.676 s cold
to 1.997 s warm before any change. This is a warm-cache effect, not an optimization result.

---

## L. Regression Results

All previously verified core runtime functionality remained intact after the audits:

| Component | Result |
| --- | --- |
| DDEV startup | PASS — project started; 33.17 s |
| HTTPS / HTTP `/up` | PASS — 200 / 200 |
| Queue | PASS — `queue entered RUNNING state` |
| PHP / FPM | PASS — expected values intact |
| OPcache | PASS — enabled; ample free cache memory |
| SQLite | PASS — integrity ok; data counts unchanged |
| Redis | PASS — PONG; no evictions |
| Memcached | PASS — alive; configured memory intact |
| Laravel | PASS — boots; storage link OK |
| Vite | BLOCKED — unchanged missing native binding |
| Ollama | PASS — healthy; API 0.40.16 |
| Qwen3 | BLOCKED — no repeated inference; Step 8 limitations remain |
| Project isolation | PASS — `ddev-sqlite-test` untouched |

No regression was introduced by Step 9. The service shutdown at the end of the step is part of the
documented daily workflow and preserves containers, volumes, database files and models.

---

## M. Outstanding Issues

| Issue | Status | Evidence / impact |
| --- | --- | --- |
| FMI-18 / Vite native binding | BLOCKED | Missing `@rolldown/binding-linux-x64-gnu`; dependency install not authorized. |
| FMI-03 / HTTPS HMR | BLOCKED | Protected `src/vite.config.js` hardcodes `ws`; mixed-content block from HTTPS. |
| FMI-17 / Qwen3 visible output | BLOCKED | Default context killed; reduced context empty or timed out. |
| FMI-05 / GPU acceleration | BLOCKED BY HARDWARE | Intel UHD iGPU only; no CUDA/ROCm. |
| FMI-16 / Mutagen stale lock | MITIGATED | CA-01 `performance_mode: none` retained; not a Step 9 change. |
| FMI-15 / DDEV working directory | DOCUMENTED | DDEV commands must run from `src/`, not repository root. |
| Cold HTTPS latency | WARNING | 5.676 s cold median; 1.997 s warm median. No evidence supports changing router, PHP or cache configuration. |
| Host RAM headroom | WARNING | ≈818 MiB free after services on an 8 GB host; Qwen3 load remains memory-sensitive. |
| Docker storage | MONITOR | 11.37 GB images, 2.497 GB volumes, 594.3 MB build cache; no cleanup authorized. |

---

## N. Rollback Instructions

**Step 9 rollback:** none is required. No runtime configuration, persistent data, dependency, schema or model
was changed.

If the carried-in Step 8 workaround must be reversed (not recommended on this host):

1. Edit `src/.ddev/config.yaml`.
2. Remove the CA-01 comment block and the `performance_mode: none` line.
3. From `src/`, run `ddev restart`.
4. Expected consequence: the stale Mutagen daemon lock may return and the project may fail to become ready.

If a future authorized change is made, follow the Step 9 change loop: record baseline, back up the file,
apply one change, retest, compare and restore the backup if evidence does not support the change. Do not
delete Docker volumes, Ollama models, SQLite files or `.env` as a rollback action.

---

## O. Final Status

### Final optimization scorecard

| Component | Status | Basis |
| --- | --- | --- |
| Docker | `[NO CHANGE REQUIRED]` | Measured 8 CPU / 3.66 GiB allocation; low idle usage; no settings file keys to tune. |
| DDEV | `[NO CHANGE REQUIRED]` | Starts successfully in 33.17 s; `db` omitted; CA-01 retained. |
| PHP | `[NO CHANGE REQUIRED]` | 8.4.24; 1024M / 1800 s / 128 M uploads; no pressure measured. |
| OPcache | `[NO CHANGE REQUIRED]` | Enabled; 222.41 MiB free; timestamp validation preserved. |
| Database | `[NO CHANGE REQUIRED]` | Integrity ok; fast direct probes; no schema/index change justified. |
| Redis | `[NO CHANGE REQUIRED]` | 1.5 MB used of 2 GB; 100 hits / 0 misses / 0 evictions. |
| Memcached | `[NO CHANGE REQUIRED]` | 128 MiB limit; 100 hits / 0 misses / 0 evictions. |
| Laravel | `[NO CHANGE REQUIRED]` | Stable response; development cache state appropriate. |
| Vite | `[BLOCKED]` | Missing Linux native binding; protected HMR source. |
| Ollama | `[WARNING]` | Healthy service, but no model loaded and CPU-only inference is slow. |
| Qwen3 | `[BLOCKED]` | No visible generated output under tested contexts. |
| GPU | `[BLOCKED]` | No supported GPU on this host. |
| Monitoring | `[NO CHANGE REQUIRED]` | Lightweight checks documented; no extra service introduced. |

### Classification

**PARTIALLY OPTIMIZED**

Rationale: hardware, baseline, all requested component audits, regression verification, resource guidance
and monitoring guidance are complete. No new change was applied because the evidence did not justify one
and unnecessary changes are explicitly prohibited. The classification is not
`OPTIMIZATION COMPLETE` because Vite/HMR, visible Qwen3 generation and GPU acceleration remain unresolved;
it is not `BLOCKED` because the evidence-based audit and stabilization work itself completed successfully.

---

## Closure

- Baseline: `docs/performance-baseline.md`
- Resource guidance: `docs/resource-management.md`
- Monitoring guide: `docs/monitoring-and-maintenance.md`
- Tracking: `docs/temp-development-requirement-task.md`, `docs/temp-development-ai-development-process.md`
- Stop point: Step 9 closes after service shutdown, final integrity checks and tracking updates.
