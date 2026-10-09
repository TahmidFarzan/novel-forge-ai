# Novel Forge AI

Novel Forge AI is a Laravel-based, AI-assisted writing system for generating
novel foundations, chapter plans, and chapter content. Generation runs against a
local Ollama server using the Qwen3 4B Q4 model, so no writing data leaves your
machine.

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.4, Laravel 13 |
| Frontend | Vue 3, Inertia.js, Ziggy, Tailwind CSS 4, Vite 8 |
| Database | SQLite |
| AI runtime | Ollama (`qwen3:4b`) |
| Deployment | Docker Compose (Nginx, PHP-FPM, Ollama) |

## Architecture

The production stack is a standard Laravel layout with three containers on one
bridge network (`novel-forge-ai-network`):

```
Browser -> nginx (novel-forge-ai-nginx, :80)
             |  serves public/ and public/build
             v  FastCGI
          app (novel-forge-ai-app, php-fpm :9000)
             |  Laravel + Composer + Vite build, SQLite
             v
          ollama (novel-forge-ai-ollama, :11434)  qwen3:4b
```

- `novel-forge-ai/app` - PHP-FPM, Composer, Node build tooling, and the Laravel
  project at `/var/www/html/novel-forge-ai`.
- `novel-forge-ai/nginx` - reverse proxy that serves `public/` and forwards PHP
  to `app:9000`.
- `novel-forge-ai/ollama` - local inference with a persistent model volume.

Vue/Inertia/Ziggy assets are compiled inside the app container into
`public/build`; there is no frontend or database container. `APP_URL` (in
`src/.env`) is the single source of truth for the public URL, including
sub-folder deployments.

## Quick start (Docker)

```sh
cd docker
cp .env.example .env
docker compose up -d --build
```

Open <http://novel-forge-ai.localhost/> (or <http://localhost/>). On the first
boot the stack pulls the model, installs dependencies, builds the frontend,
migrates and seeds the database, and caches the framework. Later boots are
idempotent.

See [Docker deployment](docs/docker-installation.md) for details.

## Quick start (manual)

Requires PHP 8.4+, Composer, Node.js 22+, and Ollama.

```sh
cd src
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate --seed
php artisan dev
```

See [Manual installation](docs/manual-installation.md) for details.

## Documentation

- [Docker deployment](docs/docker-installation.md) — deployment flow and configuration
- [Docker architecture](docs/docker-architecture.md) — containers, volumes, startup scripts
- [Docker script description](docs/docker-script-description.md) — entrypoint and startup steps
- [Troubleshooting](docs/troubleshooting.md) — common problems and fixes
- [Command reference](docs/command-reference.md)
- [Manual installation](docs/manual-installation.md)
- [Testing](docs/testing.md)
- [System architecture](docs/system-architecture.md)
- [Docker environment README](docker/README.md)

## Repository layout

```
.
├── docker/                 Production-style deployment (3 containers)
│   ├── docker-compose.yml
│   ├── php/  nginx/  ollama/
│   └── README.md
├── docs/                   Project documentation
└── src/                    Laravel application
    ├── app/                Application code (services, models, controllers)
    ├── config/ai-brain.php AI model catalog (Ollama endpoints, limits)
    ├── database/           Migrations, factories, seeders, SQLite database
    ├── resources/          Vue pages/components, CSS, Inertia entrypoint
    ├── routes/             web.php and console.php
    └── tests/              Unit and Feature tests
```

## AI model configuration

The model catalog lives in `src/config/ai-brain.php` and is seeded into the
database. The active Ollama endpoint is read from `AI_BRAIN_URL` (with
`OLLAMA_URL` as a fallback). Docker sets these to the in-network service name
`http://ollama:11434`; manual installs default to `http://localhost:11434`.
