<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function cibo_trimmed(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function cibo_redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}
