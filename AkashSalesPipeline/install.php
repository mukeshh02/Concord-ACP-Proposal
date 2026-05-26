<?php

/**
 * AkashSalesPipeline — First-Time Installer
 *
 * Usage (from your Laravel project root):
 *   php install.php
 *
 * What it does:
 *   1. Checks requirements (PHP version, extensions)
 *   2. Downloads latest release ZIP from GitHub
 *   3. Extracts module to /modules/AkashSalesPipeline/
 *   4. Runs composer dump-autoload
 *   5. Runs migrations
 *   6. Enables the module
 *   7. Clears cache
 */

define('GITHUB_REPO',    'mukeshh02/akash-sales-pipeline');
define('MODULES_DIR',    __DIR__ . '/modules');
define('MODULE_NAME',    'AkashSalesPipeline');
define('MIN_PHP',        '8.1.0');

// ─── Helpers ─────────────────────────────────────────────────────────────────

function info(string $msg): void  { echo "\033[32m[INFO]\033[0m  $msg\n"; }
function warn(string $msg): void  { echo "\033[33m[WARN]\033[0m  $msg\n"; }
function error(string $msg): void { echo "\033[31m[ERROR]\033[0m $msg\n"; }
function step(string $msg): void  { echo "\n\033[36m▶ $msg\033[0m\n"; }

function ask(string $question): bool
{
    echo "\033[33m$question [y/N]: \033[0m";
    $input = trim(fgets(STDIN));
    return strtolower($input) === 'y';
}

function runArtisan(string $command): void
{
    $artisan = __DIR__ . '/artisan';
    if (!file_exists($artisan)) {
        error("artisan file not found. Are you in the Laravel root directory?");
        exit(1);
    }
    $output = shell_exec("php artisan $command 2>&1");
    echo $output;
}

// ─── Banner ───────────────────────────────────────────────────────────────────

echo "\n";
echo "\033[36m╔══════════════════════════════════════════════════╗\033[0m\n";
echo "\033[36m║     AkashSalesPipeline — Module Installer        ║\033[0m\n";
echo "\033[36m║     github.com/" . GITHUB_REPO . "  ║\033[0m\n";
echo "\033[36m╚══════════════════════════════════════════════════╝\033[0m\n";
echo "\n";

// ─── Step 1: Requirements Check ──────────────────────────────────────────────

step("Checking requirements...");

// PHP version
if (version_compare(PHP_VERSION, MIN_PHP, '<')) {
    error("PHP " . MIN_PHP . "+ required. You have: " . PHP_VERSION);
    exit(1);
}
info("PHP " . PHP_VERSION . " ✓");

// Required extensions
$required = ['zip', 'curl', 'json', 'pdo'];
foreach ($required as $ext) {
    if (!extension_loaded($ext)) {
        error("PHP extension '$ext' is required but not loaded.");
        exit(1);
    }
}
info("PHP extensions (zip, curl, json, pdo) ✓");

// Must be in Laravel root
if (!file_exists(__DIR__ . '/artisan') || !file_exists(__DIR__ . '/composer.json')) {
    error("Run this script from your Laravel project root directory!");
    error("Example: php modules/AkashSalesPipeline/install.php");
    exit(1);
}
info("Laravel root directory ✓");

// Already installed?
if (is_dir(MODULES_DIR . '/' . MODULE_NAME)) {
    warn("Module already exists at: modules/" . MODULE_NAME);
    if (!ask("Reinstall / overwrite?")) {
        echo "Aborted.\n";
        exit(0);
    }
}

// ─── Step 2: Fetch Latest Release Info ───────────────────────────────────────

step("Fetching latest release from GitHub...");

$apiUrl  = "https://api.github.com/repos/" . GITHUB_REPO . "/releases/latest";
$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => "User-Agent: AkashInstaller/1.0\r\nAccept: application/vnd.github+json\r\n",
        'timeout' => 15,
    ]
]);

$json = @file_get_contents($apiUrl, false, $context);

if (!$json) {
    error("Could not reach GitHub API. Check your internet connection.");
    exit(1);
}

$release = json_decode($json, true);

if (empty($release['tag_name'])) {
    error("No releases found on GitHub repo: " . GITHUB_REPO);
    exit(1);
}

$version    = ltrim($release['tag_name'], 'v');
$zipUrl     = $release['zipball_url'];   // GitHub source ZIP (always available)
$changelog  = $release['body'] ?? '';

info("Latest version: v{$version}");
if ($changelog) {
    echo "\n\033[90m--- Changelog ---\n{$changelog}\n-----------------\033[0m\n\n";
}

if (!ask("Install v{$version} now?")) {
    echo "Aborted.\n";
    exit(0);
}

// ─── Step 3: Download ZIP ─────────────────────────────────────────────────────

step("Downloading v{$version}...");

$tmpZip = sys_get_temp_dir() . "/akash_sales_pipeline_{$version}.zip";

$ch = curl_init($zipUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,   // GitHub redirects
    CURLOPT_USERAGENT      => 'AkashInstaller/1.0',
    CURLOPT_TIMEOUT        => 60,
]);
$data = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code !== 200 || !$data) {
    error("Download failed (HTTP $code). URL: $zipUrl");
    exit(1);
}

file_put_contents($tmpZip, $data);
info("Downloaded to: $tmpZip (" . round(filesize($tmpZip) / 1024) . " KB)");

// ─── Step 4: Extract ──────────────────────────────────────────────────────────

step("Extracting module...");

$tmpExtract = sys_get_temp_dir() . "/akash_extract_{$version}";
@mkdir($tmpExtract, 0755, true);

$zip = new ZipArchive();
if ($zip->open($tmpZip) !== true) {
    error("Could not open ZIP file.");
    exit(1);
}

$zip->extractTo($tmpExtract);
$zip->close();

// GitHub zipball has a subfolder like: mukeshh02-akash-sales-pipeline-abc1234/
$entries    = scandir($tmpExtract);
$subFolder  = '';
foreach ($entries as $entry) {
    if ($entry !== '.' && $entry !== '..' && is_dir($tmpExtract . '/' . $entry)) {
        $subFolder = $tmpExtract . '/' . $entry;
        break;
    }
}

if (!$subFolder) {
    error("Could not find extracted module folder.");
    exit(1);
}

// Copy to modules/AkashSalesPipeline/
$dest = MODULES_DIR . '/' . MODULE_NAME;
if (is_dir($dest)) {
    // Remove old version
    $it  = new RecursiveDirectoryIterator($dest, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }
    rmdir($dest);
}

// Recursive copy
function copyDir(string $src, string $dst): void {
    @mkdir($dst, 0755, true);
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $s = "$src/$file";
        $d = "$dst/$file";
        is_dir($s) ? copyDir($s, $d) : copy($s, $d);
    }
    closedir($dir);
}

copyDir($subFolder, $dest);

// Cleanup
unlink($tmpZip);
array_map('unlink', glob("$tmpExtract/*"));
rmdir($tmpExtract);

info("Extracted to: modules/" . MODULE_NAME . " ✓");

// ─── Step 5: Composer autoload ────────────────────────────────────────────────

step("Running composer dump-autoload...");
$output = shell_exec('composer dump-autoload 2>&1');
echo $output;

// ─── Step 6: Run migrations ───────────────────────────────────────────────────

step("Running database migrations...");
runArtisan('migrate --force');

// ─── Step 7: Enable module ────────────────────────────────────────────────────

step("Enabling module...");
runArtisan('module:enable AkashSalesPipeline');

// ─── Step 8: Clear cache ──────────────────────────────────────────────────────

step("Clearing cache...");
runArtisan('core:clear-cache');
runArtisan('optimize:clear');

// ─── Done ─────────────────────────────────────────────────────────────────────

echo "\n";
echo "\033[32m╔══════════════════════════════════════════════════╗\033[0m\n";
echo "\033[32m║   ✅  AkashSalesPipeline v{$version} Installed!      ║\033[0m\n";
echo "\033[32m║                                                  ║\033[0m\n";
echo "\033[32m║   Next steps:                                    ║\033[0m\n";
echo "\033[32m║   1. npm run build  (rebuild frontend assets)    ║\033[0m\n";
echo "\033[32m║   2. Open CRM → Settings → Sales Pipeline        ║\033[0m\n";
echo "\033[32m║   3. Configure stage mappings & templates        ║\033[0m\n";
echo "\033[32m╚══════════════════════════════════════════════════╝\033[0m\n";
echo "\n";
