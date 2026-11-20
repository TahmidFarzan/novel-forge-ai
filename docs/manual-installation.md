# Manual Installation

Use this guide to run the Laravel application directly on your machine without
DDEV. The application runtime is bare-metal; the AI runtime remains Docker-only.
Do not install Ollama or Qwen3 natively.

> **USER MUST EXECUTE MANUALLY.** The migration agent did not run these
> commands.

For the standard development environment, use [Installation](installation.md)
instead. This guide is an alternative for developers who cannot run DDEV.

## Requirements

- PHP 8.4 or newer with the `pdo_sqlite`, `mbstring`, `intl`, `gd`, `zip`,
  `bcmath`, `exif`, `opcache`, and `pcntl` extensions
- Composer 2
- Node.js 22 or newer with npm
- Docker Desktop only when you want the optional local AI runtime

Verify the toolchain:

```sh
php -v
composer -V
node -v
npm -v
docker version
```

## 1. Install PHP dependencies

```sh
cd src
composer install
```

## 2. Create the environment file

```sh
cp .env.example .env
php artisan key:generate
```

The default `src/.env.example` uses SQLite and points the AI brain at
`http://localhost:11434`.

## 3. Create the database

Laravel expects the SQLite file to exist before migrating:

```sh
php artisan migrate --seed
```

Migrations create the schema and seeders populate permissions, users, genres,
languages, AI brains, prompts, and generation steps.

## 4. Create the storage link

```sh
php artisan storage:link
```

This creates `public/storage -> storage/app/public`, required for serving
uploaded media.

## 5. Install frontend dependencies

```sh
npm install
```

## 6. Start the Docker-only AI runtime (optional)

The AI stack is independent of the application and is Docker-only. From the
repository root, in PowerShell:

```powershell
cd ollama
.\scripts\start.ps1
.\scripts\pull-qwen3.ps1
.\scripts\list-models.ps1
```

The host endpoint is `http://127.0.0.1:11434`. See
[ollama/README.md](../ollama/README.md) for model management, security, and
cleanup. Do not install Ollama natively and do not expose port 11434 publicly.

If you do not need AI generation, skip this step; the application still boots.

## 7. Run the application

Start the combined development command (Laravel server, queue listener, log
tail, and Vite):

```sh
php artisan dev
```

Alternatively run the pieces separately:

```sh
php artisan serve     # http://127.0.0.1:8000
npm run dev           # Vite dev server with HMR
```

Open <http://127.0.0.1:8000/>.

## Configuration

Relevant `src/.env` variables:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
AI_BRAIN_URL=http://127.0.0.1:11434
OLLAMA_URL=http://127.0.0.1:11434
AI_BRAIN_CONTEXT_WINDOW=8192
AI_BRAIN_TIMEOUT_SECONDS=1500
```

If you leave `DB_DATABASE` unset, Laravel uses `database/database.sqlite`.

To use a non-default model or endpoint, update the entry in
`src/config/ai-brain.php` and re-seed the AI brain records:

```sh
php artisan db:seed --class=AiBrainSeeder --force
```

The value in `src/.env` overrides the catalog default at runtime.

A fresh copy of `src/.env.example` uses a 32768 context window and a 600-second
timeout. For CPU-only local generation, set the values shown above (8192 context,
1500 seconds), matching the DDEV setup.

## Production build

To compile frontend assets for a production deployment:

```sh
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not run `php artisan serve` in production; use PHP-FPM behind a web server
(such as the Nginx configuration in `docker/nginx/nginx.conf`) or Laravel
Octane.
