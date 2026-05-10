<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
}
error_reporting(E_ALL);

function cibo_is_https_request(): bool
{
    $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $https === 'on' || $https === '1' || $forwardedProto === 'https';
}

function cibo_app_start_named_session(string $name): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_trans_sid', '0');
        session_name($name);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => cibo_is_https_request(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function cibo_start_user_session(): void
{
    cibo_app_start_named_session('USER_SESSION');
}

function cibo_app_db(): ?mysqli
{
    return cibo_db();
}

function cibo_app_db_ready(): bool
{
    return cibo_db_ready();
}

function cibo_json_input(): array
{
    if (!array_key_exists('cibo_raw_request_body', $GLOBALS)) {
        $rawBody = file_get_contents('php://input');
        $GLOBALS['cibo_raw_request_body'] = is_string($rawBody) ? $rawBody : '';
    }

    $rawBody = (string) $GLOBALS['cibo_raw_request_body'];

    if (!is_string($rawBody) || trim($rawBody) === '') {
        return [];
    }

    $decoded = json_decode($rawBody, true);
    return is_array($decoded) ? $decoded : [];
}

function cibo_json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cibo_method_not_allowed(array $allowedMethods): never
{
    header('Allow: ' . implode(', ', $allowedMethods));
    cibo_json_response([
        'success' => false,
        'message' => 'Method not allowed.',
    ], 405);
}

function cibo_app_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cibo_limit_text(string $value, int $maxLength): string
{
    $value = trim($value);

    if ($maxLength <= 0) {
        return '';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength);
    }

    return substr($value, 0, $maxLength);
}

function cibo_normalize_single_line(string $value, int $maxLength = 255): string
{
    $value = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);
    return cibo_limit_text($value, $maxLength);
}

function cibo_normalize_multiline_text(string $value, int $maxLength = 1000): string
{
    $value = str_replace(["\r\n", "\r"], "\n", trim($value));
    $lines = array_map(static fn (string $line): string => preg_replace('/\s+/', ' ', trim($line)) ?? trim($line), explode("\n", $value));
    $lines = array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));
    return cibo_limit_text(implode("\n", $lines), $maxLength);
}

function cibo_normalize_phone_value(string $value): string
{
    return preg_replace('/\D+/', '', trim($value)) ?? '';
}

function cibo_normalize_postal_code_value(string $value): string
{
    return preg_replace('/\D+/', '', trim($value)) ?? '';
}

function cibo_request_expects_json(): bool
{
    $requestUri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return str_contains($requestUri, '/api/')
        || str_contains($accept, 'application/json')
        || $requestedWith === 'xmlhttprequest';
}

function cibo_request_guard_read_id(): string
{
    $requestId = trim((string) ($_SERVER['HTTP_X_CIBO_REQUEST_ID'] ?? ''));

    if ($requestId !== '' && preg_match('/^[A-Za-z0-9._:-]{8,120}$/', $requestId)) {
        return $requestId;
    }

    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        return '';
    }

    if (!array_key_exists('cibo_raw_request_body', $GLOBALS)) {
        $rawBody = file_get_contents('php://input');
        $GLOBALS['cibo_raw_request_body'] = is_string($rawBody) ? $rawBody : '';
    }

    $body = trim((string) ($GLOBALS['cibo_raw_request_body'] ?? ''));

    if ($body === '') {
        return '';
    }

    $scopeSeed = $method . '|' . (string) ($_SERVER['REQUEST_URI'] ?? '') . '|' . $body;
    return 'body-' . hash('sha256', $scopeSeed);
}

function cibo_request_guard_begin(string $scope, int $ttlSeconds = 20): void
{
    $requestId = cibo_request_guard_read_id();

    if ($requestId === '') {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        return;
    }

    $now = time();
    $scope = cibo_normalize_single_line($scope, 120);
    $guards = $_SESSION['cibo_request_guards'] ?? [];

    if (!is_array($guards)) {
        $guards = [];
    }

    foreach ($guards as $key => $entry) {
        $entryTime = (int) ($entry['time'] ?? 0);

        if ($entryTime + $ttlSeconds < $now) {
            unset($guards[$key]);
        }
    }

    $guardKey = $scope . ':' . $requestId;

    if (isset($guards[$guardKey])) {
        throw new RuntimeException('This action was already submitted. Please wait a moment.');
    }

    $guards[$guardKey] = [
        'time' => $now,
        'scope' => $scope,
        'request_id' => $requestId,
    ];

    $_SESSION['cibo_request_guards'] = $guards;
    $_SESSION['cibo_request_guard_active'][$scope] = $requestId;
}

function cibo_request_guard_finish(string $scope, bool $success): void
{
    $requestId = $_SESSION['cibo_request_guard_active'][$scope] ?? null;

    if (!is_string($requestId) || $requestId === '') {
        return;
    }

    $guardKey = cibo_normalize_single_line($scope, 120) . ':' . $requestId;
    $guards = $_SESSION['cibo_request_guards'] ?? [];

    if (!$success && is_array($guards) && isset($guards[$guardKey])) {
        unset($guards[$guardKey]);
        $_SESSION['cibo_request_guards'] = $guards;
    }

    unset($_SESSION['cibo_request_guard_active'][$scope]);
}
