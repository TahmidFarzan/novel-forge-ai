# Command Reference

Common commands for developing Novel Forge AI with DDEV. Run all commands from
the DDEV project root:

```text
src/
```

> **USER MUST EXECUTE MANUALLY.** The migration agent did not run Docker, DDEV,
> Composer, npm, Artisan, or Ollama commands.

## Project lifecycle

| Command | Description |
| --- | --- |
| `ddev start` | Start the project containers |
| `ddev stop` | Stop the project containers |
| `ddev restart` | Recreate containers after configuration changes |
| `ddev describe` | Show URLs, PHP/Node versions, services, and ports |
| `ddev launch` | Open the project URL in a browser |
| `ddev list` | List DDEV projects (read-only) |
| `ddev poweroff` | Stop all running DDEV projects |
| `ddev ssh` | Open a shell in the web container |
| `ddev logs -s web` | Show web/nginx/PHP-FPM/queue logs |
| `ddev logs -f` | Follow all project logs |

The primary URL is <https://novel-forge-ai.ddev.site>; health is `/up`.

## Application (Artisan)

| Command | Description |
| --- | --- |
| `ddev artisan migrate` | Run pending migrations |
| `ddev artisan migrate:status` | Show migration status |
| `ddev artisan migrate --seed` | Migrate and seed |
| `ddev artisan db:seed --force` | Run all seeders |
| `ddev artisan db:seed --class=AiBrainSeeder --force` | Run one seeder |
| `ddev artisan optimize:clear` | Clear cached config, routes, events, views |
| `ddev artisan route:list` | List registered routes |
| `ddev artisan storage:link` | Recreate the storage symlink |
| `ddev artisan key:generate` | Generate an application key (only when empty) |
| `ddev artisan about` | Show the Laravel environment summary |
| `ddev artisan tinker` | Open an interactive REPL |
| `ddev artisan queue:failed` | List failed queue jobs |
| `ddev artisan queue:retry all` | Retry failed queue jobs |

Do not run `migrate:fresh` against a database you need to keep.

## Composer

| Command | Description |
| --- | --- |
| `ddev composer install` | Install PHP dependencies |
| `ddev composer dump-autoload` | Regenerate the autoloader |
| `ddev composer test` | Clear config cache and run the test suite |

## Frontend (npm)

Node.js 24 is supplied by DDEV. The package manager is npm.

| Command | Description |
| --- | --- |
| `ddev npm install` | Install frontend dependencies |
| `ddev npm run build` | Build production assets into `public/build` |
| `ddev npm run dev` | Start the Vite dev server (foreground) |

Vite is exposed at `https://novel-forge-ai.ddev.site:5173` and
`http://novel-forge-ai.ddev.site:5172`. HMR may be blocked on the HTTPS origin
because the frozen `vite.config.js` hardcodes `ws://`; use `npm run build` as the
fallback. See [Development environment](development-environment.md#frontend).

## Database (SQLite)

The database container is omitted. The database is a file:

```text
src/database/database.sqlite
```

| Task | Command |
| --- | --- |
| Check the file | `ls -lh database/database.sqlite` (host or `ddev ssh`) |
| Inspect migrations | `ddev artisan migrate:status` |
| Back up | `ddev stop`, then copy `src/database/database.sqlite` |
| Restore | `ddev stop`, copy the backup into place, then `ddev start` |

`ddev snapshot`, `ddev export-db`, `ddev import-db`, and `ddev mysql` are not
available with an omitted database container.

## Tests and code style

| Command | Description |
| --- | --- |
| `ddev artisan test` | Run the PHPUnit suite |
| `ddev artisan test --testsuite=Unit` | Run the Unit suite |
| `ddev artisan test --testsuite=Feature` | Run the Feature suite |
| `ddev artisan test --filter=NovelGenerationPipelineTest` | Run one test class |
| `ddev exec vendor/bin/pint --test` | Check code style |
| `ddev exec vendor/bin/pint` | Fix code style |

See [Testing](testing.md).

## Cache services

| Command | Description |
| --- | --- |
| `ddev logs -s redis` | Show Redis logs |
| `ddev logs -s memcached` | Show Memcached logs |
| `ddev exec php -m` | Confirm `redis` and `memcached` extensions |
| `ddev artisan tinker` | Run cache probes (see below) |

Cache probes inside Tinker:

```php
Cache::store('redis')->put('ddev-probe', 'ok', 60);
Cache::store('redis')->get('ddev-probe');

Cache::store('memcached')->put('ddev-probe', 'ok', 60);
Cache::store('memcached')->get('ddev-probe');
```

See [Redis and Memcached](cache-services.md).

## PHP diagnostics

| Command | Description |
| --- | --- |
| `ddev exec php -v` | PHP version |
| `ddev exec php --ini` | Loaded INI files |
| `ddev exec php -m` | Loaded extensions |
| `ddev exec php -i` | Full PHP configuration |
| `ddev exec php -r "var_dump(opcache_get_status(false));"` | OPcache status |

See [PHP and OPcache](php-configuration.md).

## Health checks

| Command | Description |
| --- | --- |
| `curl https://novel-forge-ai.ddev.site/up` | Application health endpoint |
| `ddev describe` | Project status and URLs |
| `ddev logs -s web` | Web and queue logs |

## Ollama (independent stack)

These commands are **not** DDEV commands. Run them from `ollama/` in PowerShell
on Windows:

```powershell
.\scripts\start.ps1
.\scripts\stop.ps1
.\scripts\list-models.ps1
.\scripts\pull-qwen3.ps1
```

Removal and purge scripts require exact names or the typed confirmation phrase.
See [ollama/README.md](../ollama/README.md). The AI stack is optional and is not
started by DDEV.

## Useful one-liners

```sh
# Clear caches after editing src/.env
ddev artisan optimize:clear

# Rebuild assets after editing Vue/CSS
ddev npm run build

# Tail today's Laravel log
ddev exec sh -c 'tail -f storage/logs/laravel-$(date +%F).log'

# Confirm the queue daemon is logging
ddev logs -s web
```

## Related documentation

- [Installation](installation.md)
- [Development environment](development-environment.md)
- [Troubleshooting](troubleshooting.md)
