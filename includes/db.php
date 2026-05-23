<?php

declare(strict_types=1);

function cibo_parse_env_file(string $path): array
{
    if (!is_file($path) || !is_readable($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!is_array($lines)) {
        return [];
    }

    $values = [];

    foreach ($lines as $line) {
        $line = trim((string) $line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $separatorPosition = strpos($line, '=');

        if ($separatorPosition === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separatorPosition));
        $value = trim(substr($line, $separatorPosition + 1));
        $key = preg_replace('/^\xEF\xBB\xBF/', '', $key) ?? $key;

        if ($key === '') {
            continue;
        }

        if (
            strlen($value) >= 2
            && (($value[0] === '"' && $value[strlen($value) - 1] === '"')
            || ($value[0] === "'" && $value[strlen($value) - 1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}

function cibo_local_settings(): array
{
    static $settings = null;

    if (is_array($settings)) {
        return $settings;
    }

    $settings = [];
    $projectRoot = dirname(__DIR__);
    $localConfigFiles = [
        $projectRoot . '/config.local.php',
        $projectRoot . '/settings.local.php',
    ];

    foreach ($localConfigFiles as $configPath) {
        if (!is_file($configPath) || !is_readable($configPath)) {
            continue;
        }

        $loaded = require $configPath;

        if (is_array($loaded)) {
            $settings = array_merge($settings, $loaded);
        }
    }

    $envFiles = [
        $projectRoot . '/.env',
        $projectRoot . '/.env.local',
    ];

    foreach ($envFiles as $envPath) {
        $settings = array_merge($settings, cibo_parse_env_file($envPath));
    }

    return $settings;
}

function cibo_setting(string $key, string $default = ''): string
{
    $environmentValue = getenv($key);

    if (is_string($environmentValue) && $environmentValue !== '') {
        return $environmentValue;
    }

    $serverValue = $_SERVER[$key] ?? $_ENV[$key] ?? null;

    if (is_string($serverValue) && $serverValue !== '') {
        return $serverValue;
    }

    $localSettings = cibo_local_settings();
    $localValue = $localSettings[$key] ?? null;

    if (is_string($localValue) && $localValue !== '') {
        return $localValue;
    }

    return $default;
}

if (!defined('CIBO_DB_HOST')) {
    define('CIBO_DB_HOST', '127.0.0.1');
}

if (!defined('CIBO_DB_PORT')) {
    define('CIBO_DB_PORT', 3306);
}

if (!defined('CIBO_DB_NAME')) {
    define('CIBO_DB_NAME', 'cibo_db_v2');
}

if (!defined('CIBO_DB_USER')) {
    define('CIBO_DB_USER', 'root');
}

if (!defined('CIBO_DB_PASS')) {
    define('CIBO_DB_PASS', '');
}

if (!defined('CIBO_RAZORPAY_KEY_ID')) {
    define('CIBO_RAZORPAY_KEY_ID', cibo_setting('CIBO_RAZORPAY_KEY_ID', cibo_setting('RAZORPAY_KEY_ID', '')));
}

if (!defined('CIBO_RAZORPAY_KEY_SECRET')) {
    define('CIBO_RAZORPAY_KEY_SECRET', cibo_setting('CIBO_RAZORPAY_KEY_SECRET', cibo_setting('RAZORPAY_KEY_SECRET', '')));
}

if (!defined('CIBO_RAZORPAY_WEBHOOK_SECRET')) {
    define('CIBO_RAZORPAY_WEBHOOK_SECRET', cibo_setting('CIBO_RAZORPAY_WEBHOOK_SECRET', cibo_setting('RAZORPAY_WEBHOOK_SECRET', '')));
}

function cibo_db(): ?mysqli
{
    static $connection = null;
    static $attempted = false;

    if ($attempted) {
        return $connection instanceof mysqli ? $connection : null;
    }

    $attempted = true;

    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $candidate = @new mysqli(
            CIBO_DB_HOST,
            CIBO_DB_USER,
            CIBO_DB_PASS,
            CIBO_DB_NAME,
            CIBO_DB_PORT
        );
    } catch (Throwable $exception) {
        $connection = null;
        return null;
    }

    if ($candidate->connect_error) {
        $connection = null;
        return null;
    }

    $candidate->set_charset('utf8mb4');
    $connection = $candidate;

    return $connection;
}

function cibo_db_ready(): bool
{
    return cibo_db() instanceof mysqli;
}
