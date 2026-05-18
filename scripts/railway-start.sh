#!/usr/bin/env bash
set -Eeuo pipefail

log() {
    printf '[railway-start] %s\n' "$1"
}

trap 'log "startup failed on line ${LINENO}"' ERR

export PORT="${PORT:-8080}"

log "preflight checks started"
php scripts/railway-preflight.php

log "clearing cached Laravel config"
php artisan config:clear --no-interaction

log "waiting for MySQL"
php scripts/railway-wait-for-db.php

log "migration started: php artisan migrate:fresh --seed --force"
php artisan migrate:fresh --seed --force --no-interaction -vvv
log "migration completed"
log "seeding completed"

log "verifying required tables"
php scripts/railway-verify-tables.php users migrations roles permissions

log "caching Laravel config"
php artisan config:cache --no-interaction

log "server started on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
