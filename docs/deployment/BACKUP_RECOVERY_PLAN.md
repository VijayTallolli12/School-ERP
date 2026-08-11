# BACKUP & RECOVERY PLAN

**Status:** ❌ NOT IMPLEMENTED

---

## 1. DATABASE BACKUP

### Daily Backup Script
```bash
#!/bin/bash
# /usr/local/bin/backup-database.sh

BACKUP_DIR="/var/backups/school-erp/database"
DATE=$(date +%Y-%m-%d-%H%M)
DB_NAME="school_erp"
DB_USER="school_erp"
DB_PASS="********"

mkdir -p "$BACKUP_DIR"

# Dump database
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  | gzip > "$BACKUP_DIR/$DB_NAME-$DATE.sql.gz"

# Delete backups older than 30 days
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +30 -delete
```

### Retention Policy
| Type | Frequency | Retention |
|------|-----------|-----------|
| Full database | Daily | 30 days |
| Monthly archive | Monthly | 12 months |
| Yearly archive | Yearly | 7 years |

---

## 2. FILE STORAGE BACKUP

### Assets to Back Up
- `storage/app/` — uploaded files (documents, photos)
- `storage/logs/` — application logs (rotate separately)
- `public/uploads/` — public uploads

### Backup Script
```bash
#!/bin/bash
# /usr/local/bin/backup-files.sh

BACKUP_DIR="/var/backups/school-erp/files"
DATE=$(date +%Y-%m-%d)
PROJECT_DIR="/path/to/project"

mkdir -p "$BACKUP_DIR"

tar -czf "$BACKUP_DIR/storage-$DATE.tar.gz" \
  -C "$PROJECT_DIR" \
  storage/app \
  public/uploads

# Sync to off-site (AWS S3)
aws s3 sync "$BACKUP_DIR" s3://school-erp-backups/files/
```

---

## 3. RESTORE PROCEDURES

### Database Restore
```bash
# Find backup
ls -la /var/backups/school-erp/database/
# Restore
gunzip -c /var/backups/school-erp/database/school_erp-2026-07-08-0000.sql.gz | mysql -u school_erp -p school_erp
```

### Full Restore
1. Put application in maintenance mode: `php artisan down`
2. Restore database from latest backup
3. Restore storage files from latest backup
4. Clear caches: `php artisan optimize:clear`
5. Verify data integrity
6. Bring site up: `php artisan up`

---

## 4. DISASTER RECOVERY

| Scenario | RTO | RPO | Recovery Steps |
|----------|-----|-----|----------------|
| Database corruption | 4 hours | 24 hours | Restore from latest daily backup |
| Server failure | 8 hours | 24 hours | Provision new server, restore from backups |
| Data center outage | 24 hours | 24 hours | Activate DR site (if configured) |
| Accidental data deletion | 2 hours | Point-in-time | Restore specific table or file |
| Security breach | 4 hours | 24 hours | Restore from pre-breach backup, patch vulnerability |

---

## 5. AUTOMATION REQUIREMENTS

- [ ] Set up cron job for daily database backup
- [ ] Set up cron job for file storage backup
- [ ] Configure off-site sync to S3/cloud storage
- [ ] Test restore procedure quarterly
- [ ] Document restore runbook
- [ ] Set up backup monitoring/alerting
