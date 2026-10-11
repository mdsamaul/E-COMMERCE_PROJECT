#!/usr/bin/env bash
# ==============================================================================
# ShopBD cPanel Backup Automation Script
# Backs up MySQL database and storage/app/public directory
# Usage: bash scripts/backup-cpanel.sh
# Can be run manually or via cPanel Cron Job
# ==============================================================================

set -e

BACKUP_DIR="${HOME}/shopbd_backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p "${BACKUP_DIR}"

echo "=========================================================="
echo "          ShopBD cPanel Production Backup Script          "
echo "=========================================================="
echo "Backup destination: ${BACKUP_DIR}"

# 1. Parse .env for database credentials
if [ -f ".env" ]; then
    DB_NAME=$(grep '^DB_DATABASE=' .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
    DB_USER=$(grep '^DB_USERNAME=' .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
    DB_PASS=$(grep '^DB_PASSWORD=' .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
    DB_HOST=$(grep '^DB_HOST=' .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
    DB_HOST=${DB_HOST:-127.0.0.1}
else
    echo "ERROR: .env file not found. Run this script from the application root."
    exit 1
fi

# 2. Database Backup via mysqldump
DB_BACKUP_FILE="${BACKUP_DIR}/db_${DB_NAME}_${TIMESTAMP}.sql.gz"
echo "[1/3] Dumping database '${DB_NAME}'..."
mysqldump -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" | gzip > "${DB_BACKUP_FILE}"
echo "✔ Database saved: ${DB_BACKUP_FILE} ($(du -h "${DB_BACKUP_FILE}" | cut -f1))"

# 3. User Uploads / Media Files Backup
MEDIA_BACKUP_FILE="${BACKUP_DIR}/uploads_${TIMESTAMP}.tar.gz"
echo "[2/3] Archiving user uploads in storage/app/public..."
if [ -d "storage/app/public" ]; then
    tar -czf "${MEDIA_BACKUP_FILE}" -C "storage/app" public
    echo "✔ Uploads saved: ${MEDIA_BACKUP_FILE} ($(du -h "${MEDIA_BACKUP_FILE}" | cut -f1))"
else
    echo "Note: storage/app/public does not exist or is empty."
fi

# 4. Rotate Backups: Remove archives older than 14 days
echo "[3/3] Rotating backups older than 14 days..."
find "${BACKUP_DIR}" -name "*.gz" -mtime +14 -exec rm -f {} \;

echo "=========================================================="
echo "✔ Backup completed successfully!"
echo "=========================================================="
