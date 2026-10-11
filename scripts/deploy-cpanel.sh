#!/usr/bin/env bash
# ==============================================================================
# ShopBD cPanel Terminal / SSH Deployment Automation Script
# Usage: bash scripts/deploy-cpanel.sh
# ==============================================================================

set -e

echo "=========================================================="
echo "          ShopBD cPanel Production Deployment Script      "
echo "=========================================================="

# 1. Identify PHP binary (supports standard cPanel MultiPHP versions)
PHP_BIN="php"
if [ -f "/usr/local/bin/ea-php82" ]; then
    PHP_BIN="/usr/local/bin/ea-php82"
elif [ -f "/opt/cpanel/ea-php82/root/usr/bin/php" ]; then
    PHP_BIN="/opt/cpanel/ea-php82/root/usr/bin/php"
elif [ -f "/usr/local/bin/ea-php83" ]; then
    PHP_BIN="/usr/local/bin/ea-php83"
fi

echo "[1/7] Using PHP binary: $($PHP_BIN -v | head -n 1)"

# 2. Fix Directory Permissions for Shared Hosting
echo "[2/7] Setting permissions on storage and bootstrap/cache..."
chmod -R 775 storage bootstrap/cache
chmod -R 755 public

# 3. Install/Optimize Composer Dependencies
if [ -f "composer.json" ]; then
    echo "[3/7] Optimizing Composer dependencies..."
    if command -v composer &> /dev/null; then
        composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
    else
        echo "Note: Composer CLI not in global PATH; skipping local composer install."
    fi
fi

# 4. Storage Symlink
echo "[4/7] Ensuring storage symlink..."
$PHP_BIN artisan storage:link || true

# 5. Execute Database Migrations
echo "[5/7] Running database migrations..."
$PHP_BIN artisan migrate --force

# 6. Production Cache Optimization
echo "[6/7] Optimizing configuration, routes, and views cache..."
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# 7. Execute Deployment Smoke Test
echo "[7/7] Running deployment smoke test..."
$PHP_BIN artisan shopbd:smoke-test

echo "=========================================================="
echo "✔ Deployment completed successfully on cPanel!"
echo "=========================================================="
