<#
.SYNOPSIS
    Builds a clean, production-ready ZIP archive of ShopBD for cPanel shared hosting upload.
.DESCRIPTION
    1. Compiles Vite production frontend assets (npm run build).
    2. Packages the application excluding .git, node_modules, tests, local .env, and dev caches.
    3. Outputs a ready-to-upload shopbd-production-package.zip in the project root.
#>

param(
    [string]$OutputFile = "shopbd-production-package.zip"
)

$ErrorActionPreference = "Stop"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "     ShopBD Production Package Builder for cPanel        " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Compile Frontend Assets
Write-Host "`n[1/3] Building production assets with Vite..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Error "Vite build failed. Please resolve compilation issues first."
}

# 2. Verify Manifest Exists
$manifestPath = "public/build/manifest.json"
if (-not (Test-Path $manifestPath)) {
    Write-Error "Production manifest ($manifestPath) not found after build!"
}
Write-Host "✔ Vite build verified: $manifestPath exists." -ForegroundColor Green

# 3. Create Clean Archive
Write-Host "`n[2/3] Creating clean archive (excluding node_modules, .git, local cache)..." -ForegroundColor Yellow

$tempDir = Join-Path $env:TEMP ("shopbd_pkg_" + [guid]::NewGuid().ToString().Substring(0, 8))
New-Item -ItemType Directory -Path $tempDir | Out-Null

$excludePatterns = @(
    "\.git",
    "node_modules",
    "tests",
    "\.phpunit.*",
    "phpunit\.xml",
    "\.env$",
    "storage\\logs\\.*\.log",
    "storage\\framework\\cache\\data\\.*",
    "storage\\framework\\sessions\\.*",
    "storage\\framework\\views\\.*",
    "\.idea",
    "\.vscode"
)

# Copy files using robocopy for fast, clean transfer
$robocopyArgs = @(
    (Get-Location).Path,
    $tempDir,
    "/E",
    "/XD", ".git", "node_modules", "tests", ".idea", ".vscode",
    "/XF", ".env", "phpunit.xml", ".phpunit.result.cache", "*.log",
    "/NFL", "/NDL", "/NJH", "/NJS"
)

& robocopy @robocopyArgs | Out-Null

# Remove existing zip if present
if (Test-Path $OutputFile) {
    Remove-Item $OutputFile -Force
}

Write-Host "`n[3/3] Compressing into $OutputFile..." -ForegroundColor Yellow
Compress-Archive -Path "$tempDir\*" -DestinationPath $OutputFile -CompressionLevel Optimal

Remove-Item -Path $tempDir -Recurse -Force | Out-Null

$zipSize = (Get-Item $OutputFile).Length / 1MB
Write-Host "`n==========================================================" -ForegroundColor Green
Write-Host "✔ Package created: $OutputFile ($([math]::Round($zipSize, 2)) MB)" -ForegroundColor Green
Write-Host "You can now upload $OutputFile directly via cPanel File Manager!" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
