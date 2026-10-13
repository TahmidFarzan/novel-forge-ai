# Command Reference

Common commands for developing Novel Forge AI. Docker commands are run from the
`docker/` directory; application commands are run through `docker compose exec`
against the `app` service.

For the live-reload development workflow, prefix the Compose commands with the
development override:

```sh
cd docker
alias nfdocker='docker compose -f docker-compose.yml -f docker-compose.dev.yml'
nfdocker up -d --build
nfdocker logs -f app
nfdocker exec app php artisan test
```

See [docker-development.md](docker-development.md).

## Docker Compose

| Command | Description |
| --- | --- |
| `docker compose up -d --build` | Build images and start the stack in the background |
| `docker compose up` | Start the stack in the foreground (logs streamed) |
| `docker compose ps` | Show container status and health |
| `docker compose logs -f <service>` | Follow logs (`app`, `nginx`, `ollama`) |
| `docker compose restart app` | Restart the application container |
| `docker compose stop` | Stop containers, keep volumes |
| `docker compose start` | Start previously stopped containers |
| `docker compose down` | Stop and remove containers and the network |
| `docker compose down -v` | Stop and remove containers, network, and volumes |
| `docker compose build --no-cache` | Rebuild images without cache |
| `docker compose exec <service> <cmd>` | Run a command inside a running container |
| `docker compose run --rm <service> <cmd>` | Run a one-off command in a new container |

## Application (inside `app`)

| Command | Description |
| --- | --- |
| `docker compose exec app php artisan migrate` | Run pending migrations |
| `docker compose exec app php artisan migrate --force` | Run migrations without prompting |
| `docker compose exec app php artisan migrate:status` | Show migration status |
| `docker compose exec app php artisan migrate:fresh --seed` | Rebuild schema and reseed (destructive) |
| `docker compose exec app php artisan db:seed --force` | Run all seeders |
| `docker compose exec app php artisan db:seed --class=AiBrainSeeder --force` | Run one seeder |
| `docker compose exec app php artisan optimize` | Cache config, routes, events, and views |
| `docker compose exec app php artisan optimize:clear` | Clear all caches |
| `docker compose exec app php artisan route:list` | List registered routes |
| `docker compose exec app php artisan storage:link` | Recreate the storage symlink |
| `docker compose exec app php artisan key:generate` | Generate an application key |
| `docker compose exec app php artisan tinker` | Open an interactive REPL |
| `docker compose exec app sh` | Open a shell in the container |

## Frontend (inside `app`)

Vite runs inside the application container; there is no separate frontend
service.

| Command | Description |
| --- | --- |
| `docker compose exec app npm run build` | Build production assets into `public/build` |
| `docker compose exec app npm install` | Install/refresh dependencies |

## Composer (inside `app`)

| Command | Description |
| --- | --- |
| `docker compose exec app composer install` | Install PHP dependencies |
| `docker compose exec app composer dump-autoload` | Regenerate the autoloader |
| `docker compose exec app composer test` | Run the test suite via Composer |

## Ollama

| Command | Description |
| --- | --- |
| `docker compose exec ollama ollama list` | List installed models |
| `docker compose exec ollama ollama pull qwen3:4b` | Download a model |
| `docker compose exec ollama ollama show qwen3:4b` | Show model details |
| `docker compose exec ollama wget -qO- http://127.0.0.1:11434/api/tags` | Query the Ollama API from inside the container |

## Health checks

| Command | Description |
| --- | --- |
| `docker compose exec app php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'` | Check PHP-FPM |
| `curl http://127.0.0.1/up` | Check the app through Nginx |
| `docker compose exec ollama ollama list` | Check the Ollama API |

## Tests

Run from `src/` locally or inside the container:

```sh
docker compose exec app php artisan test
docker compose exec app php artisan test --filter=NovelGenerationPipelineTest
docker compose exec app php artisan test --testsuite=Unit
docker compose exec app php artisan test --testsuite=Feature
```

See [testing.md](testing.md) for details.

## Useful one-liners

```sh
# Clear all caches after editing src/.env
docker compose exec app php artisan optimize:clear

# Rebuild assets after editing Vue/CSS
docker compose exec app npm run build

# Tail the Laravel log (stored in the storage volume)
docker compose exec app tail -f storage/logs/laravel.log
```
