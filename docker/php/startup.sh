#!/bin/sh
set -eu

cd /var/www/html/novel-forge-ai

log()  { printf '[startup] %s\n' "$*"; }
fail() { printf '[startup] ERROR: %s\n' "$*" >&2; exit 1; }

export HOME=/tmp
export COMPOSER_HOME="${COMPOSER_HOME:-/tmp/composer}"
export npm_config_cache="${npm_config_cache:-/tmp/.npm}"

[ -f .env ] || fail ".env is missing"

normalize_app_url() {
    current="$(sed -n 's/^APP_URL=//p' .env | head -n 1 | tr -d '\r' | sed 's/^"//; s/"$//')"
    [ -n "$current" ] || return 0

    case "$current" in
        */) normalized="$current" ;;
        *)  normalized="$current/" ;;
    esac

    [ "$normalized" = "$current" ] && return 0

    tmp="$(mktemp)"
    awk -v value="$normalized" '
        BEGIN { done = 0 }
        /^APP_URL=/ {
            if (!done) { print "APP_URL=" value; done = 1; next }
        }
        { sub(/\r$/, ""); print }
    ' .env > "$tmp"
    cat "$tmp" > .env
    rm -f "$tmp"
    log "Normalized APP_URL to $normalized"
}

normalize_app_url

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

asset_marker="public/build/.novel-forge-ai-build"
asset_hash="$(cat package.json package-lock.json vite.config.js 2>/dev/null | md5sum | cut -d' ' -f1)"

if [ -f public/build/manifest.json ] \
    && [ -f "$asset_marker" ] \
    && [ "$(cat "$asset_marker")" = "$asset_hash" ]; then
    log "Frontend assets are up to date"
    rm -f public/hot
else
    log "Building frontend assets"
    rm -f public/hot
    rm -rf node_modules/.vite-temp
    npm run build || fail "npm run build failed"
    [ -f public/build/manifest.json ] \
        || fail "frontend build did not produce public/build/manifest.json"
    printf '%s' "$asset_hash" > "$asset_marker" || fail "unable to write the build marker"
fi

if [ ! -e public/storage ]; then
    log "Creating storage symlink"
    php artisan storage:link || fail "php artisan storage:link failed"
fi

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

log "Caching framework"
php artisan optimize:clear >/dev/null 2>&1 || true
php artisan optimize || fail "php artisan optimize failed"

log "Application ready"
