# PHP and OPcache Configuration

Novel Forge AI runs PHP 8.4 inside the DDEV web container. This guide documents
the project-specific settings; it does not replace the DDEV/PHP documentation.

> **USER MUST EXECUTE MANUALLY.** Commands shown here are diagnostic examples
> for the developer. They were not executed during the migration.

## PHP version

- Version: **8.4**
- Source: `php_version: "8.4"` in `src/.ddev/config.yaml`
- Compatibility: `composer.json` requires PHP `^8.3`; Laravel 13 supports PHP
  8.4. DDEV supplies the runtime, so no host PHP installation is used.

Check the effective version:

```sh
ddev exec php -v
```

## Configuration file

All project PHP and OPcache settings live in **one** file:

```text
src/.ddev/php/php.ini
```

DDEV copies every `.ddev/php/*.ini` file into the CLI and FPM `conf.d`
directories, so these values override the image defaults. Do **not** add a
separate `opcache.ini`; this project intentionally keeps both sections in the
single file.

## PHP settings

| Directive | Value | Purpose |
| --- | --- | --- |
| `memory_limit` | `1024M` | Long AI generation and large media handling |
| `max_execution_time` | `1800` | 30-minute request ceiling |
| `max_input_time` | `1800` | Matches the execution ceiling |
| `max_input_vars` | `5000` | Large forms and structured prompts |
| `post_max_size` | `128M` | Upload/post limit |
| `upload_max_filesize` | `128M` | Upload limit |
| `max_file_uploads` | `50` | Multi-file uploads |
| `date.timezone` | `UTC` | Consistent timestamps (`APP_TIMEZONE=UTC`) |
| `display_errors` | `Off` | Do not leak errors to responses |
| `display_startup_errors` | `Off` | Do not leak startup errors |
| `log_errors` | `On` | Log errors to the FPM log |
| `error_reporting` | `E_ALL` | Full diagnostics |
| `expose_php` | `Off` | Do not advertise the PHP version |
| `allow_url_fopen` | `On` | Required by application/media integrations |
| `realpath_cache_size` | `4096K` | Path resolution performance |
| `realpath_cache_ttl` | `600` | Cache lifetime in seconds |

Error output goes to the container log because DDEV's PHP-FPM configuration
uses `error_log = /dev/stdout` with `catch_workers_output = yes`. View it with:

```sh
ddev logs -s web
```

## OPcache settings

| Directive | Value | Purpose |
| --- | --- | --- |
| `opcache.enable` | `1` | Enabled for FPM |
| `opcache.enable_cli` | `1` | Enabled for Artisan/CLI |
| `opcache.validate_timestamps` | `1` | Pick up edited files |
| `opcache.revalidate_freq` | `0` | Validate on every request in development |
| `opcache.memory_consumption` | `256` | OPcache memory in MB |
| `opcache.interned_strings_buffer` | `32` | Interned strings in MB |
| `opcache.max_accelerated_files` | `20000` | File cache size |
| `opcache.save_comments` | `1` | Preserve annotations/docblocks |

JIT is **not** enabled. Development behaviour prioritises immediate file
changes over maximum throughput. Because timestamps are validated on every
request, PHP edits normally need no restart.

Inspect the effective OPcache state:

```sh
ddev exec php -i
ddev exec php -r "var_dump(opcache_get_status(false));"
```

Static review confirms the file contents; the effective values inside the
container are **NOT RUNTIME TESTED — USER MANUAL VERIFICATION REQUIRED.**

## PHP extensions

The DDEV PHP 8.4 image provides the extensions the application needs. No custom
Dockerfile or `webimage_extra_packages` entry is used.

| Extension | Purpose |
| --- | --- |
| `pdo_sqlite` | SQLite database |
| `mbstring` | Multibyte strings |
| `intl` | Localisation and formatting |
| `gd` | Image processing and thumbnails |
| `zip` | Archive handling |
| `bcmath` | Arbitrary-precision arithmetic |
| `exif` | Image metadata |
| `opcache` | PHP bytecode cache |
| `pcntl` | Process control for queue/CLI work |
| `redis` | Redis client (`phpredis`) |
| `memcached` | Memcached client |

`redis` and `memcached` are needed only when the corresponding cache service is
selected. The PHP client extension must match the PHP version; DDEV's PHP 8.4
image ships compatible builds.

Check loaded extensions:

```sh
ddev exec php -m
```

Extension loading and service connectivity are **NOT RUNTIME TESTED — USER
MANUAL VERIFICATION REQUIRED.** They are on the Step 8 checklist.

## PHP configuration for tests

`src/phpunit.xml` overrides the runtime environment for tests (in-memory SQLite,
array cache/session, sync queue, array mail). Tests do not require Redis,
Memcached, or a running Ollama server. See [Testing](testing.md).

## Related documentation

- [Development environment](development-environment.md)
- [Redis and Memcached](cache-services.md)
- [Troubleshooting](troubleshooting.md)
