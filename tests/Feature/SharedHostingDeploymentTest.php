<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SharedHostingDeploymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic operational state for deployment smoke test verification
        User::factory()->create([
            'email' => 'admin@shopbd.com',
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Deployment Test Category',
            'slug' => 'deployment-test-category',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Deployment Test Product',
            'slug' => 'deployment-test-product',
            'sku' => 'DEP-001',
            'regular_price' => 1200.00,
            'stock' => 20,
            'is_active' => true,
        ]);

        Setting::set('store_name', 'ShopBD Test Store');
    }

    public function test_smoke_test_command_runs_successfully(): void
    {
        $this->artisan('shopbd:smoke-test')
            ->expectsOutputToContain('ShopBD Shared Hosting Production Smoke Test')
            ->expectsOutputToContain('Database Connectivity & Schema')
            ->expectsOutputToContain('PASS')
            ->assertExitCode(0);
    }

    public function test_production_env_template_exists_and_has_secure_defaults(): void
    {
        $envPath = base_path('.env.production.example');
        $this->assertTrue(File::exists($envPath), '.env.production.example must exist');

        $content = File::get($envPath);
        $this->assertStringContainsString('APP_ENV=production', $content);
        $this->assertStringContainsString('APP_DEBUG=false', $content);
        $this->assertStringContainsString('LOG_CHANNEL=daily', $content);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $content);
        $this->assertStringContainsString('APP_TIMEZONE=Asia/Dhaka', $content);
    }

    public function test_root_htaccess_protects_sensitive_files(): void
    {
        $htaccessPath = base_path('.htaccess');
        $this->assertTrue(File::exists($htaccessPath), 'Root .htaccess must exist for single-folder shared hosting fallbacks');

        $content = File::get($htaccessPath);
        $this->assertStringContainsString('RewriteRule ^\.env - [F,L,NC]', $content);
        $this->assertStringContainsString('RewriteRule ^\.git - [F,L,NC]', $content);
        $this->assertStringContainsString('RewriteRule ^(app|bootstrap|config|database|resources|routes|tests)/ - [F,L,NC]', $content);
        $this->assertStringContainsString('Options -Indexes', $content);
    }

    public function test_public_htaccess_contains_security_headers_and_expires(): void
    {
        $htaccessPath = public_path('.htaccess');
        $this->assertTrue(File::exists($htaccessPath), 'public/.htaccess must exist');

        $content = File::get($htaccessPath);
        $this->assertStringContainsString('X-Frame-Options "SAMEORIGIN"', $content);
        $this->assertStringContainsString('X-Content-Type-Options "nosniff"', $content);
        $this->assertStringContainsString('mod_expires.c', $content);
    }

    public function test_production_asset_build_manifest_exists(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertTrue(File::exists($manifestPath), 'Production build manifest must exist');

        $manifest = json_decode(File::get($manifestPath), true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);
    }

    public function test_storage_and_cache_directories_exist_and_are_writable(): void
    {
        $paths = [
            storage_path('app/public'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            app()->bootstrapPath('cache'),
        ];

        foreach ($paths as $path) {
            $this->assertTrue(File::isDirectory($path), "Directory $path must exist");
            $this->assertTrue(is_writable($path), "Directory $path must be writable");
        }
    }

    public function test_deployment_and_backup_automation_scripts_exist(): void
    {
        $deployScript = base_path('scripts/deploy-cpanel.sh');
        $backupScript = base_path('scripts/backup-cpanel.sh');
        $buildScript = base_path('scripts/build-production-package.ps1');

        $this->assertTrue(File::exists($deployScript), 'deploy-cpanel.sh must exist');
        $this->assertTrue(File::exists($backupScript), 'backup-cpanel.sh must exist');
        $this->assertTrue(File::exists($buildScript), 'build-production-package.ps1 must exist');

        $this->assertStringContainsString('#!/usr/bin/env bash', File::get($deployScript));
        $this->assertStringContainsString('#!/usr/bin/env bash', File::get($backupScript));
    }

    public function test_smoke_test_identifies_missing_admin_and_fails(): void
    {
        User::query()->delete();

        $this->artisan('shopbd:smoke-test')
            ->expectsOutputToContain('No admin accounts exist')
            ->assertExitCode(1);
    }
}
