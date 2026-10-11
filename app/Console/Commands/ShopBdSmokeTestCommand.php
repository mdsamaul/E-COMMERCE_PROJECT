<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ShopBdSmokeTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopbd:smoke-test {--strict : Exit with non-zero code on any warnings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform production deployment smoke test on ShopBD core systems, database, storage, and security';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('================================================================');
        $this->info('           ShopBD Shared Hosting Production Smoke Test          ');
        $this->info('================================================================');
        $this->newLine();

        $results = [];
        $hasFailures = false;
        $hasWarnings = false;

        // 1. Database Connection & Schema Verification
        try {
            DB::connection()->getPdo();
            $requiredTables = ['users', 'categories', 'brands', 'products', 'orders', 'order_items', 'settings', 'coupons'];
            $missingTables = [];
            foreach ($requiredTables as $table) {
                if (!Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                $results[] = ['Database Connectivity & Schema', 'PASS', 'MySQL connected, all core tables exist'];
            } else {
                $results[] = ['Database Connectivity & Schema', 'FAIL', 'Missing tables: ' . implode(', ', $missingTables)];
                $hasFailures = true;
            }
        } catch (\Throwable $e) {
            $results[] = ['Database Connectivity & Schema', 'FAIL', 'Connection error: ' . $e->getMessage()];
            $hasFailures = true;
        }

        // 2. Encryption Key & Security
        $appKey = config('app.key');
        if (!empty($appKey) && str_starts_with($appKey, 'base64:')) {
            $results[] = ['Application Encryption Key', 'PASS', 'APP_KEY is securely configured'];
        } else {
            $results[] = ['Application Encryption Key', 'FAIL', 'APP_KEY is missing or invalid'];
            $hasFailures = true;
        }

        // 3. Environment & Debug Mode
        $env = config('app.env');
        $debug = config('app.debug');
        if ($env === 'production' && $debug === false) {
            $results[] = ['Production Environment & Debug', 'PASS', 'APP_ENV=production, APP_DEBUG=false'];
        } elseif ($debug === true) {
            $results[] = ['Production Environment & Debug', 'WARN', 'APP_DEBUG=true (must be FALSE in production)'];
            $hasWarnings = true;
        } else {
            $results[] = ['Production Environment & Debug', 'WARN', "APP_ENV={$env} (recommended: production)"];
            $hasWarnings = true;
        }

        // 4. SSL & URL Configuration
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            $results[] = ['HTTPS / SSL Web Scheme', 'PASS', "APP_URL is using HTTPS ({$appUrl})"];
        } else {
            $results[] = ['HTTPS / SSL Web Scheme', 'WARN', "APP_URL is not HTTPS ({$appUrl}). Ensure SSL is enabled in cPanel."];
            $hasWarnings = true;
        }

        // 5. Storage Directory Permissions & Public Symlink
        $storageApp = storage_path('app/public');
        $storageLogs = storage_path('logs');
        $bootstrapCache = app()->bootstrapPath('cache');

        $isStorageAppWritable = File::isDirectory($storageApp) && is_writable($storageApp);
        $isLogsWritable = File::isDirectory($storageLogs) && is_writable($storageLogs);
        $isBootstrapWritable = File::isDirectory($bootstrapCache) && is_writable($bootstrapCache);

        if ($isStorageAppWritable && $isLogsWritable && $isBootstrapWritable) {
            $results[] = ['Storage & Cache Permissions', 'PASS', 'storage/app/public, logs, and bootstrap/cache are writable'];
        } else {
            $results[] = ['Storage & Cache Permissions', 'FAIL', 'Write permission denied. Run: chmod -R 775 storage bootstrap/cache'];
            $hasFailures = true;
        }

        // Storage Symlink
        $publicStorage = public_path('storage');
        if (File::exists($publicStorage)) {
            $results[] = ['Public Storage Symlink', 'PASS', 'public/storage is present'];
        } else {
            $results[] = ['Public Storage Symlink', 'WARN', 'public/storage is missing. Run: php artisan storage:link'];
            $hasWarnings = true;
        }

        // 6. Cache System Test
        try {
            $testKey = 'shopbd_smoke_test_' . time();
            Cache::put($testKey, 'operational', 60);
            if (Cache::get($testKey) === 'operational') {
                Cache::forget($testKey);
                $results[] = ['Cache & Session Subsystem', 'PASS', 'Cache read and write confirmed (' . config('cache.default') . ')'];
            } else {
                $results[] = ['Cache & Session Subsystem', 'WARN', 'Cache failed to read back test value'];
                $hasWarnings = true;
            }
        } catch (\Throwable $e) {
            $results[] = ['Cache & Session Subsystem', 'FAIL', 'Cache exception: ' . $e->getMessage()];
            $hasFailures = true;
        }

        // 7. Store Administration Account
        try {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount > 0) {
                $results[] = ['Admin Authentication Account', 'PASS', "{$adminCount} active administrator account(s) detected"];
            } else {
                $results[] = ['Admin Authentication Account', 'FAIL', 'No admin accounts exist. Run: php artisan shopbd:create-admin'];
                $hasFailures = true;
            }
        } catch (\Throwable $e) {
            $results[] = ['Admin Authentication Account', 'FAIL', 'Query error: ' . $e->getMessage()];
            $hasFailures = true;
        }

        // 8. Catalog Data & Inventory Integrity
        try {
            $categoryCount = Category::count();
            $productCount = Product::count();
            $publishedProductCount = Product::where('is_active', true)->count();

            if ($productCount > 0 && $categoryCount > 0) {
                $results[] = ['Product Catalog & Categories', 'PASS', "{$publishedProductCount}/{$productCount} products published across {$categoryCount} categories"];
            } else {
                $results[] = ['Product Catalog & Categories', 'WARN', 'Catalog is empty. Seed initial demo data or create items via admin.'];
                $hasWarnings = true;
            }
        } catch (\Throwable $e) {
            $results[] = ['Product Catalog & Categories', 'FAIL', 'Catalog query error: ' . $e->getMessage()];
            $hasFailures = true;
        }

        // 9. Website Configuration Settings
        try {
            $settingsCount = Setting::count();
            if ($settingsCount > 0) {
                $results[] = ['Website System Settings', 'PASS', "{$settingsCount} settings configured in database"];
            } else {
                $results[] = ['Website System Settings', 'WARN', 'Default settings not found in database'];
                $hasWarnings = true;
            }
        } catch (\Throwable $e) {
            $results[] = ['Website System Settings', 'FAIL', 'Settings error: ' . $e->getMessage()];
            $hasFailures = true;
        }

        // 10. Document Root & Sensitive File Leak Prevention
        $envPathInPublic = public_path('.env');
        $gitPathInPublic = public_path('.git');
        if (!File::exists($envPathInPublic) && !File::exists($gitPathInPublic)) {
            $results[] = ['Web Root Security & Isolation', 'PASS', '.env and .git are NOT exposed inside public directory'];
        } else {
            $results[] = ['Web Root Security & Isolation', 'FAIL', 'CRITICAL SECURITY LEAK: .env or .git exists inside web root!'];
            $hasFailures = true;
        }

        // Display summary table
        $this->table(['Check Name', 'Status', 'Diagnostic Details'], $results);

        $this->newLine();
        if ($hasFailures) {
            $this->error('✘ SMOKE TEST FAILED: Resolve the critical issues marked FAIL before serving live traffic.');
            return 1;
        } elseif ($hasWarnings && $this->option('strict')) {
            $this->warn('⚠ SMOKE TEST COMPLETED WITH WARNINGS (--strict enabled).');
            return 1;
        } elseif ($hasWarnings) {
            $this->warn('⚠ SMOKE TEST PASSED WITH WARNINGS: Review items marked WARN for optimal production operation.');
            return 0;
        } else {
            $this->info('✔ ALL SMOKE TESTS PASSED: ShopBD is fully operational and production-ready!');
            return 0;
        }
    }
}
