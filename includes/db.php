<?php

declare(strict_types=1);

if (!defined('CIBO_DB_HOST')) {
    define('CIBO_DB_HOST', '127.0.0.1');
}

if (!defined('CIBO_DB_PORT')) {
    define('CIBO_DB_PORT', 3306);
}

if (!defined('CIBO_DB_NAME')) {
    define('CIBO_DB_NAME', 'cibo_db');
}

if (!defined('CIBO_DB_USER')) {
    define('CIBO_DB_USER', 'root');
}

if (!defined('CIBO_DB_PASS')) {
    define('CIBO_DB_PASS', '');
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
