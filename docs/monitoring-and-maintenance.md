# Monitoring and Maintenance — Step 9

> **Project:** Novel Forge AI — DDEV + Redis + Memcached + independent Ollama/Qwen3
> **Recorded:** 17-Nov-2026
> **Principle:** use existing Docker, DDEV, Laravel and service tools; do not add Prometheus, Grafana or other
> infrastructure for this single-developer development environment.
> **Change status:** documentation only; no monitoring service was installed in Step 9.

---

## 1. Scope

Monitor the components that can realistically fail or grow on this host:

| Area | What to watch | Existing tool |
| --- | --- | --- |
| Host | free RAM, disk C: free space | PowerShell `Get-CimInstance` |
| Docker | container state, CPU, memory, image/volume/cache size | `docker ps`, `docker stats`, `docker system df` |
| DDEV | project state, web logs, startup time | `ddev list`, `ddev logs`, `ddev start` |
| PHP / OPcache | FPM values, PHP error log | `php-fpm -i`, `storage/logs` |
| SQLite | file size, integrity, table/migration counts | PDO `PRAGMA`, `artisan tinker` |
| Redis | ping, memory, hits/misses/evictions | `redis-cli` |
| Memcached | uptime, bytes, hits/misses/evictions | PHP Memcached `getStats()` |
| Laravel | `/up`, queue state, cache config, storage link | `curl`, `artisan about`, `ddev logs` |
| Vite | whether dev server starts; native binding error | `ddev npm run dev` |
| Ollama | health, API version, loaded model, container logs | `docker inspect`, `curl`, `ollama ps` |
| Qwen3 | load/inference latency and visible output | API `/api/generate` with measured timeouts |
| GPU | none on this host | no GPU monitoring is applicable |

All DDEV commands must be run from `src/`. Ollama commands must be run from `ollama/`.

---

## 2. Baseline anchors

Investigate deviations from these observed Step 9/Step 8 values rather than applying arbitrary thresholds.

| Check | Baseline anchor |
| --- | --- |
| DDEV start | 33.17 s |
| DDEV stop | 4.63 s |
| Ollama start / stop | 2.78 s / 1.43 s |
| HTTPS `/up` cold / warm median | 5.676 s / 1.997 s |
| Web container memory | 248.9–250.5 MiB |
| Redis container memory | ≈10.1 MiB |
| Memcached container memory | ≈5.8 MiB |
| Ollama idle memory | 327.8 MiB |
| Redis used memory | 1,495,336 bytes |
| Redis hits / misses / evictions | 100 / 0 / 0 |
| Memcached limit | 134,217,728 bytes |
| Memcached hits / misses / evictions | 100 / 0 / 0 |
| SQLite file | 700,416 bytes; `integrity_check=ok` |
| Users / jobs / migrations | 16 / 0 / 30 |
| Ollama API | 0.40.16 |
| Qwen3 model | `qwen3:4b`, 2,497,293,931 bytes |
| Docker images / volumes / build cache | 11.37 GB / 2.497 GB / 594.3 MB |
| Disk C: free | 329,771,057,152 bytes |

---

## 3. Routine checks

### 3.1 Daily development check

Run after starting the stack, from `src/`:

```powershell
ddev list
curl.exe -k -sS -o NUL -w 'HTTPS_UP=%{http_code} TIME=%{time_total}s\n' https://novel-forge-ai.ddev.site/up
curl.exe -sS -o NUL -w 'HTTP_UP=%{http_code} TIME=%{time_total}s\n' http://novel-forge-ai.ddev.site/up
docker stats --no-stream --format '{{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}'
```

Expected: project `OK`; both `/up` responses `200`; no container near its 3.664 GiB limit; queue daemon
still RUNNING.

Queue and Laravel state:

```powershell
ddev logs -s web | Select-String 'queue entered RUNNING state' | Select-Object -Last 1
ddev exec php artisan about
```

### 3.2 Weekly or after a restart

```powershell
docker system df
docker ps -a --format '{{.Names}}\t{{.Status}}'
docker volume ls --format '{{.Name}}'
```

Expected: only expected project/shared containers; volumes `ddev_novel-forge-ai_redis` and
`novel-forge-ai-ollama-models` still present; no unexplained sharp growth in images or volumes.

---

## 4. Component health commands

### PHP and OPcache

```powershell
ddev exec php -v
docker exec ddev-novel-forge-ai-web php-fpm -i | Select-String '^(memory_limit|max_execution_time|opcache.enable|opcache.validate_timestamps)'
docker exec ddev-novel-forge-ai-web php -r '$s=opcache_get_status(false); echo "used=".round($s["memory_usage"]["used_memory"]/1048576,2)."MiB free=".round($s["memory_usage"]["free_memory"]/1048576,2)."MiB\n";'
```

Expected: PHP 8.4.24; `memory_limit=1024M`, `max_execution_time=1800`, OPcache enabled and timestamp
validation on; ample free OPcache memory.

### SQLite

```powershell
Get-Item src/database/database.sqlite, src/database/database.sqlite.bak | Select-Object Name,Length
docker exec ddev-novel-forge-ai-web php -r '$p=new PDO("sqlite:/var/www/html/database/database.sqlite"); echo "integrity=".$p->query("PRAGMA integrity_check")->fetchColumn()."\n";'
ddev exec php artisan tinker --execute='echo "users=".DB::table("users")->count()." jobs=".DB::table("jobs")->count()." migrations=".DB::table("migrations")->count()."\n";'
```

Expected: both files 700,416 bytes; `integrity=ok`; 16 users, 0 jobs, 30 migrations.

### Redis

```powershell
docker exec ddev-novel-forge-ai-redis redis-cli ping
docker exec ddev-novel-forge-ai-redis redis-cli info memory | Select-String '^(used_memory|used_memory_rss|maxmemory|mem_fragmentation_ratio):'
docker exec ddev-novel-forge-ai-redis redis-cli info stats | Select-String '^(keyspace_hits|keyspace_misses|evicted_keys):'
```

Expected: `PONG`; no evictions; memory near the observed small baseline. If a probe is needed, use a unique
temporary key and delete that key. Never `FLUSHALL` or `FLUSHDB`.

### Memcached

```powershell
docker exec ddev-novel-forge-ai-web php -r '$m=new Memcached(); $m->addServer("memcached",11211); $s=reset($m->getStats()); echo "uptime=".$s["uptime"]." bytes=".$s["bytes"]." limit=".$s["limit_maxbytes"]." hits=".$s["get_hits"]." misses=".$s["get_misses"]." evictions=".$s["evictions"]."\n";'
```

Expected: growing uptime, no evictions, limit 134,217,728. Do not flush Memcached.

### Laravel and queue

```powershell
ddev exec php artisan about
ddev logs -s web | Select-String -Pattern 'queue|ERROR|CRITICAL|exception' | Select-Object -Last 30
```

Expected: environment `local`, storage link OK, queue RUNNING, no repeated new exception pattern.

### Ollama and Qwen3

```powershell
cd C:\development\Laravel\novel-forge-ai\ollama
docker inspect --format '{{.State.Health.Status}}' novel-forge-ai-ollama-standalone
curl.exe -sS http://127.0.0.1:11434/api/version
docker exec novel-forge-ai-ollama-standalone ollama ps
docker logs novel-forge-ai-ollama-standalone --tail 100
```

Expected when started: health `healthy`; version `0.40.16`; model list empty when idle. Do not expose the
API beyond `127.0.0.1`; it has no authentication.

Qwen3 should only be retested with an explicit timeout and a recorded context value. Step 8 established that
8192 context is killed and 1024 context returned an empty response after a very long load. Treat any change
as a new experiment, not routine monitoring.

### Host resources

```powershell
$os = Get-CimInstance Win32_OperatingSystem
"FREE_RAM_BYTES=$($os.FreePhysicalMemory * 1024)"
Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'" | Select-Object Size,FreeSpace
```

Windows/WSL free-RAM figures are noisy. Use them as a trend signal, not a precise container metric.

---

## 5. Log inspection and rotation

### Locations

| Log | Command / path |
| --- | --- |
| Laravel application log | `src/storage/logs/laravel-YYYY-MM-DD.log` |
| DDEV web/queue | `ddev logs -s web` from `src/` |
| Redis | `ddev logs -s redis` from `src/` |
| Memcached | `ddev logs -s memcached` from `src/` |
| Ollama | `docker logs novel-forge-ai-ollama-standalone --tail 100` |

Step 9 observed `laravel-2026-11-17.log` at 21,087 bytes. This is not a storage problem. Inspect the newest
errors first; do not delete logs as a performance shortcut.

Ollama's `compose.yaml` already limits container logs with `max-size: 10m` and `max-file: 3`. No additional
rotation service was added.

---

## 6. Storage checks and backup recommendations

### Checks

```powershell
docker system df
Get-ChildItem src/storage/logs -File | Sort-Object LastWriteTime -Descending | Select-Object -First 10 Name,Length
Get-ChildItem src/public/build -File -Recurse | Measure-Object Length -Sum
Get-Item src/database/database.sqlite, src/database/database.sqlite.bak
docker exec novel-forge-ai-ollama-standalone du -sh /root/.ollama
```

The last command requires Ollama to be running. Step 9 observed 2.4G. A volume-level probe reported 2.3G;
the difference is rounding/tooling, not evidence of deletion.

### Backup guidance

- Keep `src/database/database.sqlite` and `src/database/database.sqlite.bak` together. Before any authorized
  operation that can write data, make a new timestamped copy of the live file.
- Keep `src/.env` local and untracked. Do not overwrite it with `.env.example` as a troubleshooting step.
- The Ollama model volume is persistent across stop/recreate, but Step 9 did not create a separate backup of
  it. Treat `novel-forge-ai-ollama-models` as irreplaceable unless a separate backup is explicitly made.
- Do not remove Docker images, volumes, build cache, Qwen3 models, `public/build` or logs for routine
  "cleanup." Step 9 performed no cleanup.

---

## 7. Troubleshooting playbook

| Symptom | Safe first checks | Recovery |
| --- | --- | --- |
| DDEV command says no `.ddev/config.yaml` | current directory | Run all DDEV commands from `src/`. |
| DDEV start hangs on Mutagen sync | `ddev mutagen logs`; stale daemon lock | Keep CA-01 `performance_mode: none`; do not delete sync state blindly. |
| `/up` returns 500 or stale config values | `ddev exec php artisan about`; inspect cache | Run `ddev exec php artisan optimize:clear`; no source edit. |
| Laravel says database is readonly | SQLite file permissions inside container | Only if the known error returns: back up file, then restore `chown 1000:1000` / `chmod 664` inside web container (Step 8 CA-03). |
| Public images missing | `public/storage` symlink | Run `ddev exec php artisan storage:link --force` (Step 8 CA-04). |
| Redis or Memcached unreachable | `ddev list`; container state; ping/getStats | `ddev restart` from `src/`; preserve volumes; do not flush. |
| Cache hit ratio looks wrong | confirm configured cache store with `artisan about` | Laravel reports `database` as cache/session/queue; Redis and Memcached are configured stores verified in Step 8. Do not change default driver without authorization. |
| Vite fails with native binding error | `ddev npm run dev` output | Known blocker FMI-18. Use existing `public/build`. Dependency repair requires explicit authorization. |
| HTTPS HMR blocked | browser mixed-content error | Known blocker FMI-03; protected `src/vite.config.js` hardcodes `ws`. Use HTTP fallback only if Vite itself can start. |
| Qwen3 killed or empty | Ollama logs, context value, free RAM | Known blockers FMI-17/FMI-05. Do not delete model or raise context blindly. |
| Disk usage grows | `docker system df`, model volume size | Record growth and ask before cleanup. Never prune or delete volumes automatically. |

---

## 8. Maintenance workflow

1. Record the symptom and the exact command output.
2. Compare with the baseline anchors in section 2.
3. Check logs in section 5.
4. Apply the smallest safe recovery from section 7.
5. Rerun the daily check from section 3.
6. If a configuration is changed, follow the change-control loop in `docs/resource-management.md` section 14
   and append a record to `docs/temp-development-ai-development-process.md`.

No monitoring agent, scheduler or new service was introduced in Step 9.
