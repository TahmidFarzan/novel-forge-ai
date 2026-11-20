# Resource Management — Step 9

> **Project:** Novel Forge AI — DDEV development environment + independent Ollama/Qwen3 stack
> **Recorded:** 17-Nov-2026
> **Basis:** measured Step 9 values in `docs/performance-baseline.md`; no arbitrary limits
> **Change status:** recommendations only; no Step 9 resource allocation was changed

## 1. Purpose and safety boundary

This document records how CPU, RAM, storage and AI inference resources are currently consumed and what
should be done during daily development. It deliberately separates:

- **Current / applied** — values observed or already present in the repository.
- **Recommended, not applied** — actions requiring a fresh authorization and a controlled change record.

Nothing in this guide authorizes dependency installation, protected-source edits, migrations, seeders,
production builds, Docker pruning, volume deletion, model removal or `.env` deletion.

---

## 2. Current allocation and measured usage

### Host and Docker

| Resource | Measured value | Interpretation |
| --- | --- | --- |
| Physical RAM | 8,245,456,896 bytes (7.68 GiB) | Small host; avoid parallel heavy workloads. |
| Free RAM at session start | 825,229,312 bytes (≈787 MiB) | Host already has limited headroom. |
| Free RAM after DDEV + Ollama | 857,567,232 bytes (≈818 MiB) | Windows/WSL cache makes this noisy, but no large headroom appeared. |
| Docker visible CPUs | 8 | Matches 8 logical processors; no cap needed. |
| Docker visible memory | 3,933,859,840 bytes (3.66 GiB) | About half of physical RAM; leaves room for the host. |
| Docker storage driver | `overlayfs` | Normal for this backend. |
| WSL config | no `%USERPROFILE%\.wslconfig` | Nothing to tune locally. |
| Docker Desktop settings | no CPU/memory keys in `settings-store.json` | No evidence-based allocation file edit was available. |

### Measured container footprint

| Container | CPU sample | Memory sample |
| --- | --- | --- |
| `ddev-novel-forge-ai-web` | 12.16% idle; 80.99% during later probe sample | 248.9 → 250.5 MiB |
| `ddev-novel-forge-ai-redis` | 0.21% | 10.18 MiB |
| `ddev-novel-forge-ai-memcached` | 0.02% | 5.828 MiB |
| `ddev-router` | 0.00% | 112.8 MiB |
| `ddev-ssh-agent` | 0.00% | 5.621 MiB |
| `novel-forge-ai-ollama-standalone` | 0.00% idle | 327.8 MiB |

These are point-in-time samples, not sustained averages. They do not justify CPU or memory limits.

---

## 3. Docker Desktop and WSL2

### Applied / current

- Docker sees 8 CPUs and 3.66 GiB.
- WSL2 has `docker-desktop` running and `Ubuntu` stopped.
- No local `.wslconfig` exists.
- No Docker Desktop CPU/memory setting key was found.

### Recommendation

Keep the current allocation. Do not allocate all 8 logical processors or raise container memory on this
8 GB host. If Docker Desktop's UI is changed later, change only one value at a time, remeasure DDEV start and
Ollama load, and retain the previous value if the change does not help.

Do not run `docker system prune` as a routine optimization. Storage growth is monitored in section 12.

---

## 4. DDEV multi-project management

### Applied / current

- One active project is needed: `novel-forge-ai`.
- `ddev-sqlite-test` exists but was not started or modified.
- The project runs `web`, `redis` and `memcached`; `db` is omitted because the application uses SQLite.
- Shared `ddev-router` and `ddev-ssh-agent` remain running.
- CA-01 `performance_mode: none` is retained to bypass the stale Mutagen daemon lock.

### Daily workflow

1. Run DDEV commands from `src/`, not the repository root.
2. Start only the project being worked on: `ddev start`.
3. Start Ollama only when AI work is needed: `cd ollama; .\scripts\start.ps1`.
4. Stop Ollama when finished: `.\scripts\stop.ps1`.
5. Stop the DDEV project when finished: `cd ..\src; ddev stop`.
6. Leave shared DDEV router/ssh-agent alone unless DDEV itself requires maintenance.

Do not merge independent projects into one DDEV project merely to save memory. Independent lifecycles are a
stated requirement.

### Measured lifecycle times

| Operation | Time |
| --- | --- |
| `ddev start` | 33.17 s |
| `ddev restart` (Step 8 evidence) | 15.6 s to ready |
| `ddev stop` | 4.63 s |
| `.\scripts\start.ps1` (Ollama) | 2.78 s |
| `.\scripts\stop.ps1` (Ollama) | 1.43 s |

The 33.17 s cold start includes an 8 s custom image build and 8.1 s web readiness wait. This is acceptable
for a development environment and does not justify changing required services.

---

## 5. PHP runtime

### Applied / current

| Setting | Value |
| --- | --- |
| PHP version | 8.4.24 |
| `memory_limit` | 1024M |
| `max_execution_time` / `max_input_time` | 1800 s |
| `post_max_size` / `upload_max_filesize` | 128M |
| `max_file_uploads` | 50 |
| `max_input_vars` | 5000 |
| `date.timezone` | UTC |
| `display_errors` / `display_startup_errors` | Off |
| `log_errors` | On |
| `realpath_cache_size` / TTL | 4096K / 600 |

Observed PHP CLI probe usage was 0.34 MiB current and 0.38 MiB peak. No memory pressure was measured.

### Recommendation

Keep the current limits. Do not lower `memory_limit` to save RAM: PHP allocates it as a ceiling, not a
resident allocation, and 1024M is needed for Composer/artisan and media operations. Do not raise it without
an observed fatal error or OOM message.

---

## 6. PHP OPcache

### Applied / current

| Setting | Value |
| --- | --- |
| `opcache.enable` / `opcache.enable_cli` | On |
| `opcache.validate_timestamps` | On |
| `opcache.revalidate_freq` | 0 |
| `opcache.memory_consumption` | 256 MB |
| `opcache.interned_strings_buffer` | 32 |
| `opcache.max_accelerated_files` | 20000 |
| `opcache.save_comments` | On |

Step 9 CLI status reported 33.59 MiB used, 222.41 MiB free and `max_cached_keys=32531`. CLI cache counters
are not FPM counters; the ample free memory is the useful signal.

### Recommendation

Keep timestamp validation enabled. Do not disable automatic code-change detection to gain speed. Do not
resize OPcache until FPM statistics show cache pressure or restarts caused by cache exhaustion.

---

## 7. SQLite database

### Applied / current

- Database file and backup: 700,416 bytes each.
- `journal_mode=delete`, `page_size=4096`, `page_count=171`, `busy_timeout=60000`.
- `integrity_check=ok`; 30 tables, 48 indexes, 30 migrations.
- 20 direct select probes completed in 0.13 ms.

### Recommendation

Keep the current SQLite settings. Back up `src/database/database.sqlite` before any authorized maintenance
that could write data. Do not change journal mode or page size on a measurement-only basis, and never run
destructive migration commands.

---

## 8. Redis

### Applied / current

| Metric | Value |
| --- | --- |
| `maxmemory` | 2,147,483,648 bytes (2 GiB) |
| `used_memory` | 1,495,336 bytes |
| `used_memory_rss` | 25,219,072 bytes |
| Fragmentation ratio | 17.07 |
| Connected clients | 1 |
| Hits / misses / evictions | 100 / 0 / 0 |
| 100 SET+GET cycles | 45.59 ms |
| Eviction policy (Step 8) | `allkeys-lfu` |

The high fragmentation ratio is not actionable by itself: absolute RSS is about 24 MiB and no eviction or
growth pattern was observed.

### Recommendation

Keep the project-scoped volume and current limits. Do not flush Redis, delete the volume, lower `maxmemory`
or change eviction policy without evidence. Use only temporary uniquely named probe keys and delete them.

---

## 9. Memcached

### Applied / current

| Metric | Value |
| --- | --- |
| `limit_maxbytes` | 134,217,728 bytes (128 MiB) |
| `bytes` used | 0 at measurement time |
| Hits / misses / evictions | 100 / 0 / 0 |
| Connections | 2 current / 4 total |
| 100 SET+GET cycles | 42.13 ms |

### Recommendation

Keep the 128 MiB allocation. Do not flush a shared Memcached service or raise the limit without hit/miss or
eviction evidence. Confirm Laravel's configured store with `php artisan about` before diagnosing cache misses.

---

## 10. Laravel application

### Applied / current

- Config and routes are not cached; views are cached.
- Debug mode is enabled and environment is `local`.
- Cache/session/queue driver shown by `about`: `database`; Redis and Memcached stores are configured and were
  verified in Step 8.
- Storage link is OK; queue worker is RUNNING.

### Recommendation

Keep development-friendly cache state. Do not run `config:cache` or `route:cache` during active development
unless explicitly authorized and retested; stale cached config caused a Step 8 issue before `optimize:clear`.
Use `ddev exec php artisan optimize:clear` as the safe corrective action for stale Laravel artifacts.

---

## 11. Node.js, Vite and Ollama/Qwen3

### Node / Vite

- Node v24.20.0 and npm 11.19.0 are present in DDEV.
- Vite cannot start: missing Linux native `@rolldown/binding-linux-x64-gnu`.
- Existing `public/build` contains 81 files totaling 1,679,821 bytes.
- No dependency install or production build was authorized.

**Recommendation:** keep using the existing built assets for non-Vite work. Authorize a container-side
dependency repair only as a separate, reversible change with its own baseline and retest. Do not edit the
protected `src/vite.config.js` without an explicit source-change decision.

### Ollama service

| Setting / metric | Value |
| --- | --- |
| Image | `ollama/ollama:0.40.16` |
| Lifecycle | manual; `restart: "no"` |
| Host binding | `127.0.0.1:11434` only |
| Keep alive | 10m |
| `OLLAMA_NUM_PARALLEL` | 1 |
| `OLLAMA_MAX_LOADED_MODELS` | 1 |
| `OLLAMA_FLASH_ATTENTION` | 1 |
| Configured context | 8192 |
| Idle memory | 327.8 MiB |
| Start / stop time | 2.78 s / 1.43 s |
| Model storage | ≈2.4 GiB (`qwen3:4b`) |

Keep concurrency at 1 and one loaded model on this host. Start Ollama only when needed. The API is
unauthenticated, so keep the loopback bind.

### Qwen3 inference

- Model `qwen3:4b` is the only local model used and must be preserved.
- Default 8192 context was killed after 322.39 s (Step 8).
- 1024 context loaded in 292 s and generated 4 tokens in 187.9 s but returned an empty response.
- No context value is verified to give a visible textual answer.

**Recommendation:** do not lower or raise `OLLAMA_CONTEXT_LENGTH` blindly. Any future context change must be
paired with a fresh inference test on the same host, with the old `.env` value backed up. Do not delete the
model or the `novel-forge-ai-ollama-models` volume.

### GPU

No supported GPU is present. Intel UHD integrated graphics cannot be claimed as CUDA/ROCm acceleration.
Treat all inference as CPU-only until runtime evidence on supported hardware says otherwise.

---

## 12. Storage management

| Area | Measured size | Action |
| --- | --- | --- |
| Docker images | 11.37 GB | Monitor only; no prune. |
| Docker local volumes | 2.497 GB | Preserve; contains Redis and Ollama data. |
| Docker build cache | 594.3 MB | Monitor only; no cleanup authorized. |
| Ollama model directory | 2.4 GiB | Preserve; `qwen3:4b` only. |
| SQLite database + backup | 700,416 bytes each | Preserve both. |
| Laravel log | `laravel-2026-11-17.log` 21,087 bytes | Inspect, do not delete as a performance tuning step. |
| `public/build` | 1,679,821 bytes / 81 files | Keep until an authorized rebuild is verified. |

Disk C: reported 511,069,646,848 bytes total and 329,771,057,152 bytes free. There is no storage emergency.
Do not delete images, volumes, models, logs or build artifacts to "optimize" this environment.

---

## 13. Startup and shutdown procedure

Recommended daily order:

```powershell
# Start application stack from the Laravel project root
cd C:\development\Laravel\novel-forge-ai\src
ddev start

# Start AI stack only when needed
cd ..\ollama
.\scripts\start.ps1

# Stop AI stack first
.\scripts\stop.ps1

# Stop application stack
cd ..\src
ddev stop
```

Measured result: DDEV start 33.17 s, stop 4.63 s; Ollama start 2.78 s, stop 1.43 s. Both shutdown scripts
preserve containers, volumes, SQLite files, `.env` and models. Shared DDEV router/ssh-agent remain running.

---

## 14. Change control reminder

For any future resource change:

1. Record the current value and a fresh baseline.
2. Identify one responsible configuration file.
3. Back up the original.
4. Apply only that one change.
5. Retest the affected component and the core regression set.
6. Compare before/after values.
7. Keep the change only if evidence supports it; otherwise restore the backup.

No such change was applied during Step 9.
