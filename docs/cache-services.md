# Redis and Memcached

Novel Forge AI ships two optional, project-scoped cache services in DDEV. They
are available to the application but are **not** the default cache, session, or
queue backend.

> **USER MUST EXECUTE MANUALLY.** Commands in this guide are developer
> diagnostics. The migration agent did not start DDEV or either service.

## Default backend

`src/.env` keeps Laravel on database-backed defaults:

```env
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

Redis and Memcached are opt-in. Do not enable both as the default cache store at
the same time.

## Redis

| Item | Value |
| --- | --- |
| Compose file | `src/.ddev/docker-compose.redis.yaml` |
| Container | `ddev-novel-forge-ai-redis` |
| Image | `redis:8` (`REDIS_DOCKER_IMAGE`) |
| Internal hostname | `redis` |
| Internal port | `6379` |
| Host ports | none (`expose` only) |
| Config | `src/.ddev/redis/redis.conf` |
| Data volume | `redis:/data` (project-scoped) |
| Memory policy | `maxmemory 2048mb`, `allkeys-lfu` |
| Persistence | RDB snapshots; AOF disabled |
| Authentication | none (internal network only) |

The service is based on the official `ddev/ddev-redis` add-on and is
hand-maintained so `ddev add-on get` does not overwrite it.

PHP wiring is provided by DDEV `web_environment`:

```env
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
```

Laravel's dotenv repository is immutable, so these container variables override
the `127.0.0.1` placeholders in `src/.env` and `src/.env.example`.

### Select Redis

Edit `src/.env` locally:

```env
CACHE_STORE=redis
# Optional:
SESSION_DRIVER=redis
# Optional, only if you intentionally move queue jobs to Redis:
QUEUE_CONNECTION=redis
```

Then reload the application. `ddev restart` is the safe way to re-apply the
environment if a cached configuration is suspected.

### Verify Redis

```sh
ddev exec php -m
ddev logs -s redis
ddev artisan tinker
```

Inside Tinker:

```php
Cache::store('redis')->put('ddev-probe', 'ok', 60);
Cache::store('redis')->get('ddev-probe');
```

Expected result: `ok`. This is **NOT RUNTIME TESTED — USER MANUAL VERIFICATION
REQUIRED.**

## Memcached

| Item | Value |
| --- | --- |
| Compose file | `src/.ddev/docker-compose.memcached.yaml` |
| Container | `ddev-novel-forge-ai-memcached` |
| Image | `memcached:1.6` (`MEMCACHED_DOCKER_IMAGE`) |
| Internal hostname | `memcached` (service name) |
| Internal port | `11211` |
| Host ports | none (`expose` only) |
| Memory | `-m 128` (128 MB) |
| Persistence | none (memory-only) |

PHP wiring:

```env
MEMCACHED_HOST=memcached
MEMCACHED_PORT=11211
```

The service is based on the official `ddev/ddev-memcached` add-on and is
hand-maintained so `ddev add-on get` does not overwrite it.

### Select Memcached

Edit `src/.env` locally:

```env
CACHE_STORE=memcached
# Optional:
SESSION_DRIVER=memcached
```

Do not use Memcached for queues; it has no persistence and is intended for
short-lived cache entries.

### Verify Memcached

```sh
ddev exec php -m
ddev logs -s memcached
ddev artisan tinker
```

Inside Tinker:

```php
Cache::store('memcached')->put('ddev-probe', 'ok', 60);
Cache::store('memcached')->get('ddev-probe');
```

Expected result: `ok`. This is **NOT RUNTIME TESTED — USER MANUAL VERIFICATION
REQUIRED.**

## Redis versus Memcached

| Aspect | Redis | Memcached |
| --- | --- | --- |
| Data structures | Rich (strings, lists, sets, hashes, sorted sets) | Key/value only |
| Persistence | RDB snapshots | None |
| Memory eviction | `allkeys-lfu` policy configured | LRU-style, fixed 128 MB |
| Typical use here | Cache, sessions, optional queue backend | Fast ephemeral cache |
| Queue support | Yes (Laravel Redis queue) | No |

For this project, Redis is the more capable option when persistence or queue
support matters. Memcached is suitable only for disposable cache data.

## Isolation and lifecycle

- Both services are declared in `src/.ddev/docker-compose.*.yaml` and are
  project-scoped with `com.ddev.site-name` / `com.ddev.approot` labels.
- Neither service publishes a host port; they are reachable only inside the
  DDEV network by hostname.
- `restart: "no"` keeps their lifecycle tied to DDEV; they do not auto-start
  outside the project.
- Each DDEV project gets its own Redis container and `redis` volume. A second
  project does not share this project's cache data.
- Redis data survives `ddev stop` / `ddev start` through its named volume.
  Memcached data does not survive a container restart.

## Sessions, queues, and rate limiting

- **Sessions**: `SESSION_DRIVER=database` by default. `SESSION_DRIVER=redis`
  or `memcached` is optional; Redis is the safer choice if persistence matters.
- **Queues**: `QUEUE_CONNECTION=database` by default. The DDEV queue daemon
  runs `php artisan queue:listen`. Redis can be selected explicitly; Memcached
  cannot.
- **Rate limiting**: Laravel's rate limiter uses the default cache store. If
  `CACHE_STORE` changes, rate-limit counters move with it.
- **Cache**: `CACHE_STORE=database` by default; Redis or Memcached are opt-in.

No setting above is claimed to be runtime-verified. See the Step 8 checklist in
[Development environment](development-environment.md#manual-runtime-verification-checklist-step-8).

## Related documentation

- [Development environment](development-environment.md)
- [PHP and OPcache](php-configuration.md)
- [Troubleshooting](troubleshooting.md)
