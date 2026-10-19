#!/bin/sh
set -eu

cd /var/www/html/novel-forge-ai

log()  { printf '[entrypoint] %s\n' "$*"; }
fail() { printf '[entrypoint] ERROR: %s\n' "$*" >&2; exit 1; }

log "Preparing application filesystem"

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    database \
    public/build \
    || fail "unable to create the required directory tree"

if [ ! -f .env ]; then
    [ -f .env.example ] || fail ".env is missing and .env.example was not found"
    cp .env.example .env || fail "unable to create .env from .env.example"
    log "Created .env from .env.example"
fi

if [ ! -f database/database.sqlite ]; then
    : > database/database.sqlite || fail "unable to create database/database.sqlite"
    log "Created database/database.sqlite"
fi

chown www-data:www-data .env database/database.sqlite 2>/dev/null || true

ensure_writable() {
    target="$1"
    [ -e "$target" ] || return 0
    if su-exec www-data test -w "$target"; then
        return 0
    fi
    log "Repairing ownership of $target"
    chown -R www-data:www-data "$target" || fail "unable to set ownership on $target"
}

ensure_writable storage
ensure_writable storage/logs
ensure_writable bootstrap/cache
ensure_writable database
ensure_writable public
ensure_writable public/build
ensure_writable vendor
ensure_writable node_modules

log "Running application provisioning as www-data"

if ! su-exec www-data "${STARTUP_SCRIPT:-/usr/local/bin/startup.sh}"; then
    fail "application provisioning failed, php-fpm will not start"
fi

log "Provisioning complete, starting: $*"

exec "$@"
