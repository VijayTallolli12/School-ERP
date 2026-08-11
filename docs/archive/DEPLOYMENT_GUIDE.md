# DEPLOYMENT GUIDE

**Status:** ⚠️ DRAFT — Deployment infrastructure not yet established

---

## Pre-Deployment Requirements

Before any deployment, the following must be completed:

1. ✅ Fix all 🔴 Critical and 🟠 High items from PRODUCTION_READINESS_REPORT.md
2. ❌ Set up production server (see INFRASTRUCTURE_CHECKLIST.md)
3. ❌ Generate APP_KEY
4. ❌ Configure production `.env`
5. ❌ Run `composer install --optimize-autoloader --no-dev`
6. ❌ Run `npm run build` (production build)
7. ❌ Set up database and run migrations
8. ❌ Set up Redis for cache/queue/session
9. ❌ Configure supervisor for queue workers
10. ❌ Set up SSL certificate
11. ❌ Configure monitoring (Telescope/Sentry)

---

## Deployment Steps (To Be Automated)

```bash
# 1. Maintenance mode
php artisan down --retry=60

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Clear caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue
php artisan queue:restart

# 7. Permission cache
php artisan permission:cache-reset

# 8. Storage link
php artisan storage:link

# 9. Bring site up
php artisan up
```

---

## Post-Deployment Verification

1. ✅ Login as Super Admin
2. ✅ Verify dashboard loads
3. ✅ Verify all module pages load
4. ✅ Verify API endpoints respond
5. ✅ Verify queue processes jobs
6. ✅ Verify scheduler runs
7. ✅ Check logs for errors
8. ✅ Monitor performance metrics

---

## Environment File Template

```env
APP_NAME="School ERP"
APP_ENV=production
APP_KEY=base64:...GENERATE_ME...
APP_DEBUG=false
APP_URL=https://school.domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_erp
DB_USERNAME=school_erp
DB_PASSWORD=********

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=school-erp-production
```

---

## Supervisor Configuration (Queue Worker)

```ini
[program:school-erp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

---

## Scheduler Cron Entry

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
