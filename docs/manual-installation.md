# Manual Installation

Use this guide to run Novel Forge AI directly on your machine without Docker.

## Requirements

- PHP 8.4 or newer with the `pdo_sqlite`, `mbstring`, `intl`, `gd`, and `zip`
  extensions
- Composer 2
- Node.js 22 or newer with npm
- Ollama (see the [Ollama docs](https://ollama.com/download))

Verify the toolchain:

```sh
php -v
composer -V
node -v
npm -v
ollama --version
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

## 6. Start Ollama and pull the model

In a dedicated terminal:

```sh
ollama serve
```

In another terminal:

```sh
ollama pull qwen3:4b
ollama list
```

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
APP_URL=http://novel-forge-ai.localhost/
DB_CONNECTION=sqlite
AI_BRAIN_URL=http://localhost:11434
OLLAMA_URL=http://localhost:11434
OLLAMA_CONTEXT_WINDOW=32768
```

If you leave `DB_DATABASE` unset, Laravel uses `database/database.sqlite`.

To use a non-default model or endpoint, update the entry in
`src/config/ai-brain.php` and re-seed the AI brain records:

```sh
php artisan db:seed --class=AiBrainSeeder --force
```

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
