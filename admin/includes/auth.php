<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

const CIBO_ADMIN_LEGACY_BAD_PASSWORD_HASH = '$2y$10$qU64t2lW8jJfHkhGSK1BIe72YIkArvjESMOpZQQINRk.RLlJph52O';

function cibo_admin_login(array $admin): void
{
    if (session_status() === PHP_SESSION_NONE) {
        cibo_app_start_named_session('ADMIN_SESSION');
    }

    session_regenerate_id(true);
    $_SESSION['cibo_admin'] = [
        'id' => (int) $admin['id'],
        'name' => cibo_normalize_single_line((string) ($admin['name'] ?? ''), 120),
        'email' => strtolower(trim((string) ($admin['email'] ?? ''))),
    ];
}

function cibo_admin_refresh_session(array $admin): void
{
    if (!isset($_SESSION['cibo_admin']) || !is_array($_SESSION['cibo_admin'])) {
        return;
    }

    $_SESSION['cibo_admin']['id'] = (int) ($admin['id'] ?? $_SESSION['cibo_admin']['id'] ?? 0);
    $_SESSION['cibo_admin']['name'] = (string) ($admin['name'] ?? $_SESSION['cibo_admin']['name'] ?? '');
    $_SESSION['cibo_admin']['email'] = (string) ($admin['email'] ?? $_SESSION['cibo_admin']['email'] ?? '');
}

function cibo_admin_user(): ?array
{
    $admin = $_SESSION['cibo_admin'] ?? null;
    return is_array($admin) ? $admin : null;
}

function cibo_admin_guest_only(): void
{
    if (cibo_admin_user()) {
        cibo_redirect(CIBO_ADMIN_BASE . '/index.php');
    }
}

function cibo_admin_require_login(): void
{
    if (!cibo_admin_user()) {
        if (cibo_request_expects_json()) {
            cibo_json_response([
                'success' => false,
                'message' => 'Admin authentication required.',
            ], 401);
        }

        cibo_redirect(CIBO_ADMIN_BASE . '/login.php');
    }
}

function cibo_admin_logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        cibo_app_start_named_session('ADMIN_SESSION');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool) ($params['secure'] ?? false),
            (bool) ($params['httponly'] ?? true)
        );
    }

    session_destroy();
    session_write_close();
}

function cibo_admin_attempt_login(string $email, string $password): ?string
{
    $email = strtolower(trim($email));
    $password = trim($password);

    if ($email === '' || $password === '') {
        return 'Email and password are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }

    $db = cibo_db();

    if (!$db) {
        return 'Admin database is not ready yet. Import database/schema.sql first.';
    }

    $statement = $db->prepare('SELECT id, name, email, password_hash FROM admin_users WHERE email = ? LIMIT 1');

    if (!$statement) {
        return 'Unable to prepare login request.';
    }

    $statement->bind_param('s', $email);
    $statement->execute();
    $result = $statement->get_result();
    $admin = $result ? $result->fetch_assoc() : null;
    $statement->close();

    if (
        $admin
        && strtolower(trim((string) ($admin['email'] ?? ''))) === CIBO_ADMIN_FALLBACK_EMAIL
        && (string) ($admin['password_hash'] ?? '') === CIBO_ADMIN_LEGACY_BAD_PASSWORD_HASH
    ) {
        $repairedHash = password_hash(CIBO_ADMIN_FALLBACK_PASSWORD, PASSWORD_DEFAULT);
        $repairStatement = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ? LIMIT 1');

        if ($repairStatement) {
            $adminId = (int) ($admin['id'] ?? 0);
            $repairStatement->bind_param('si', $repairedHash, $adminId);
            $repairStatement->execute();
            $repairStatement->close();
            $admin['password_hash'] = $repairedHash;
        }
    }

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return 'Invalid email or password.';
    }

    cibo_admin_login($admin);
    return null;
}
