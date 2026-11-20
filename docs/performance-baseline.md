# Performance Baseline — Step 9

> **Project:** Novel Forge AI — DDEV + Redis + Memcached + independent Ollama/Qwen3
> **Recorded:** 17-Nov-2026, 10:45 PM → 11:00 PM +06:00
> **Source:** fresh Step 9 measurements plus reusable Step 8 runtime evidence where explicitly noted
> **Rule:** every number below was observed. No benchmark was estimated or invented.

## Test conditions

| Condition | Value |
| --- | --- |
| DDEV projects running | `novel-forge-ai` only; unrelated `ddev-sqlite-test` not started |
| DDEV services | `web`, `redis`, `memcached`; `db` omitted by design |
| Shared DDEV services | `ddev-router`, `ddev-ssh-agent` running |
| Ollama | started independently for Step 9 baseline; container healthy |
| Qwen3 model state | present in volume, **not loaded** during Step 9 baseline (`ollama ps` empty) |
| Vite | **not running**; start attempt failed (see below) |
| Workload | idle service baseline plus short local probes; no stress test, no concurrency test |
| Persistent data | untouched; no cleanup, migration, dependency install or model change |

## Hardware profile

| Item | Observed value |
| --- | --- |
| OS | Microsoft Windows 11 Home Single Language, build 10.0.26300 |
| CPU | 12th Gen Intel(R) Core(TM) i3-1215U |
| CPU cores / logical processors | 6 / 8 |
| Total host RAM | 8,245,456,896 bytes (7.68 GiB) |
| Free RAM before Step 9 service start | 825,229,312 bytes (≈787 MiB) |
| Free RAM after DDEV + Ollama start | 857,567,232 bytes (≈818 MiB) |
| GPU | Intel(R) UHD Graphics, driver 32.0.101.5542 |
| GPU memory reported by WMI | 4,122,728,448 bytes (shared adapter memory, not dedicated VRAM) |
| GPU acceleration | none; no NVIDIA/CUDA/ROCm device or runtime evidence |
| System disk C: | 511,069,646,848 bytes total; 329,771,057,152 bytes free; fixed disk |
| WSL2 distros | `Ubuntu` stopped, `docker-desktop` running, both WSL version 2 |
| WSL config | no `%USERPROFILE%\.wslconfig` found |
| Docker Desktop settings file | `settings-store.json` present; contains no CPU/memory allocation keys |
| Docker effective allocation | `NCPU=8`, `MemTotal=3,933,859,840` bytes (3.66 GiB) |
| Docker storage driver | `overlayfs` |

The host is memory-constrained. The effective Docker limit is roughly half of physical RAM, which leaves room
for the Windows host and shared WSL/Docker processes. No arbitrary CPU or memory limit was applied.

## Docker storage baseline

`docker system df`:

| Type | Total | Active | Size | Reclaimable |
| --- | --- | --- | --- | --- |
| Images | 9 | 3 | 11.37 GB | 299.4 MB (2%) |
| Containers | 3 | 2 | 53.25 kB | 24.58 kB (46%) |
| Local volumes | 5 | 4 | 2.497 GB | 89 B (0%) |
| Build cache | 16 | 0 | 594.3 MB | 24.58 kB |

Project volumes: `ddev_novel-forge-ai_redis` and `novel-forge-ai-ollama-models`.
No image, volume, container or build-cache cleanup was performed.

## Startup baseline

| Measurement | Observed value | Notes |
| --- | --- | --- |
| DDEV start | **33.17 s** | Custom image build 8 s, web ready 8.1 s, Redis/Memcached ready 0.6 s. |
| Ollama start | **2.78 s** | Image already cached; container reached `healthy` shortly after. `restart: "no"` preserved. |
| Ollama API | `{"version":"0.40.16"}` | Loopback `http://127.0.0.1:11434/api/version`. |
| Vite start attempt | **4.46 s to failure** | `Cannot find native binding`; missing `@rolldown/binding-linux-x64-gnu`. |

## Application and runtime latency

| Measurement | Observed value | Notes |
| --- | --- | --- |
| HTTPS `/up`, cold, 5 requests | 7.614, 7.253, 5.676, 3.607, 2.361 s; median **5.676 s** | First requests after DDEV start. |
| HTTP `/up`, cold, 5 requests | 2.757, 2.498, 2.420, 3.001, 2.437 s; median **2.498 s** | Compare cautiously: different protocol/path through router. |
| HTTPS `/up`, warm, 10 requests | min 1.787 s, max 3.147 s, median **1.997 s** | Measured after warm-up; high variance remains. |
| PHP CLI memory | 0.34 MiB current, 0.38 MiB peak | `php -r` baseline, not FPM per-request memory. |
| SQLite `select 1` × 20 | **0.13 ms total** (~0.0065 ms/query) | Direct PDO probe inside web container; excludes Laravel bootstrap. |
| Redis 100 SET+GET cycles | **45.59 ms** (~0.456 ms/cycle) | Temporary unique key deleted; no flush. |
| Memcached 100 SET+GET cycles | **42.13 ms** (~0.421 ms/cycle) | Temporary unique key deleted; no flush. |

## Container resource baseline

Idle after DDEV start:

| Container | CPU | Memory |
| --- | --- | --- |
| `ddev-novel-forge-ai-web` | 12.16% | 248.9 MiB / 3.664 GiB |
| `ddev-novel-forge-ai-redis` | 0.21% | 10.18 MiB / 3.664 GiB |
| `ddev-novel-forge-ai-memcached` | 0.02% | 5.828 MiB / 3.664 GiB |
| `ddev-router` | 0.00% | 112.8 MiB / 3.664 GiB |
| `ddev-ssh-agent` | 0.00% | 5.621 MiB / 3.664 GiB |

After Ollama start (shortly after the HTTP probes):

| Container | CPU | Memory |
| --- | --- | --- |
| `novel-forge-ai-ollama-standalone` | 0.00% | 327.8 MiB / 3.664 GiB |
| `ddev-novel-forge-ai-web` | 80.99% | 250.5 MiB / 3.664 GiB |
| `ddev-novel-forge-ai-redis` | 0.20% | 10.14 MiB / 3.664 GiB |
| `ddev-novel-forge-ai-memcached` | 0.01% | 5.828 MiB / 3.664 GiB |
| `ddev-router` | 0.00% | 113.1 MiB / 3.664 GiB |
| `ddev-ssh-agent` | 0.00% | 5.234 MiB / 3.664 GiB |

These are point-in-time `docker stats` samples, not sustained averages.

## Ollama / Qwen3 baseline reused from Step 8

These values were not repeated in Step 9 because repeating them would require another long CPU inference run.
They remain valid Step 8 evidence for the same host, image, model and configuration.

| Measurement | Step 8 observed value |
| --- | --- |
| Model | `qwen3:4b`, ID `359d7dd4bcda`, 2,497,293,931 bytes (2.33 GiB / 2.5 GB) |
| Model load duration | 292,097 ms at `num_ctx=1024` |
| Prompt evaluation | 36,698 ms |
| Evaluation | 187,871 ms for 4 tokens (~46.97 s/token generated, excluding load and prompt) |
| Total API request | 515,886 ms (~516 s) |
| Default 8192 context | failed after 322.39 s, `llama-server process has terminated: signal: killed` |
| 2048 context | timed out after 421.36 s |
| Reduced 1024 context, second request | timed out after 600.9 s with `think=false` |
| Visible response | empty string on the only completed API request; no `OK` observed |
| GPU memory usage | no GPU path; not applicable |
| Host RAM during inference | not sampled continuously; container idle memory was 192.6–327.8 MiB |

## Interpretive limits

- HTTP timing includes Windows curl, DDEV router, nginx, PHP-FPM, Laravel boot and the `/up` route; it is a
  practical developer-experience latency, not a pure Laravel benchmark.
- PHP CLI memory is not FPM request memory; no authenticated profiling endpoint was available.
- Redis/Memcached cycle timings include PHP extension and network overhead on the project network.
- Vite performance is **not measurable** until the container-side native dependency is installed.
- GPU metrics are **not applicable** on this host.
- “Free RAM” on Windows/WSL is a noisy indicator because cache and WSL ballooning are not fully represented.
