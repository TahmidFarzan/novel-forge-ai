#!/bin/sh
set -eu

cd /var/www/html/novel-forge-ai

log()  { printf '[startup.dev] %s\n' "$*"; }
fail() { printf '[startup.dev] ERROR: %s\n' "$*" >&2; exit 1; }

export HOME=/tmp
export COMPOSER_HOME="${COMPOSER_HOME:-/tmp/composer}"
export npm_config_cache="${npm_config_cache:-/tmp/.npm}"

[ -f .env ] || fail ".env is missing"

if [ ! -f vendor/autoload.php ]; then
    log "Installing Composer dependencies"
    composer install --no-interaction --prefer-dist --no-progress \
        || fail "composer install failed"
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    log "Generating application key"
    php artisan key:generate --force >/dev/null || fail "php artisan key:generate failed"
fi

node_marker="node_modules/.novel-forge-ai-container"
if [ ! -d node_modules ] || [ ! -f "$node_marker" ]; then
    log "Installing Node dependencies"
    npm install --no-audit --no-fund || fail "npm install failed"
    : > "$node_marker" || fail "unable to write the node_modules marker"
fi

if [ ! -e public/storage ]; then
    log "Creating storage symlink"
    php artisan storage:link || fail "php artisan storage:link failed"
fi

log "Clearing cached configuration"
php artisan optimize:clear >/dev/null 2>&1 || true

log "Checking migrations"
php artisan migrate --force || fail "php artisan migrate failed"

seed_state="$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! Illuminate\Support\Facades\Schema::hasTable("users")) { echo "empty"; exit(0); }
echo App\Models\User::query()->exists() ? "seeded" : "empty";
' 2>>storage/logs/startup.log)" || fail "unable to determine the database seed state"

case "$seed_state" in
    seeded)
        log "Seed data already present, skipping seeders"
        ;;
    empty)
        log "Seeding database"
        php artisan db:seed --force || fail "php artisan db:seed failed"
        ;;
    *)
        fail "unexpected seed state: '$seed_state'"
        ;;
esac

log "Development provisioning complete; frontend assets are served by Vite"
