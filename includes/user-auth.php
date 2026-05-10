<?php

declare(strict_types=1);

require_once __DIR__ . '/common.php';

final class CiboHttpException extends RuntimeException
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}

function cibo_exception_status(Throwable $exception, int $defaultStatusCode = 422): int
{
    if ($exception instanceof CiboHttpException) {
        return $exception->statusCode();
    }

    return $defaultStatusCode;
}

function cibo_user_session_payload(array $user): array
{
    return [
        'id' => (int) ($user['id'] ?? 0),
        'name' => trim((string) ($user['name'] ?? 'User')),
        'email' => strtolower(trim((string) ($user['email'] ?? ''))),
        'phone' => trim((string) ($user['phone'] ?? '')),
        'created_at' => $user['created_at'] ?? null,
    ];
}

function cibo_user_login(array $user): array
{
    cibo_start_user_session();
    session_regenerate_id(true);

    $_SESSION['user_logged_in'] = true;
    $_SESSION['user'] = cibo_user_session_payload($user);

    return $_SESSION['user'];
}

function cibo_current_user(): ?array
{
    cibo_start_user_session();

    if (!($_SESSION['user_logged_in'] ?? false)) {
        return null;
    }

    $user = $_SESSION['user'] ?? null;
    return is_array($user) ? $user : null;
}

function cibo_find_user_by_id(int $userId): ?array
{
    if ($userId <= 0) {
        return null;
    }

    $db = cibo_app_db();

    if (!$db) {
        return null;
    }

    $statement = $db->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ? LIMIT 1');

    if (!$statement) {
        return null;
    }

    $statement->bind_param('i', $userId);
    $statement->execute();
    $user = $statement->get_result()?->fetch_assoc();
    $statement->close();

    return is_array($user) ? $user : null;
}

function cibo_find_user_by_email(string $email, bool $includePasswordHash = false): ?array
{
    $email = strtolower(trim($email));

    if ($email === '') {
        return null;
    }

    $db = cibo_app_db();

    if (!$db) {
        return null;
    }

    $columns = $includePasswordHash
        ? 'id, name, email, phone, created_at, password_hash'
        : 'id, name, email, phone, created_at';

    $statement = $db->prepare("SELECT {$columns} FROM users WHERE email = ? LIMIT 1");

    if (!$statement) {
        return null;
    }

    $statement->bind_param('s', $email);
    $statement->execute();
    $user = $statement->get_result()?->fetch_assoc();
    $statement->close();

    return is_array($user) ? $user : null;
}

function cibo_restore_user_session(array $account): ?array
{
    $existingUser = cibo_current_user();

    if (is_array($existingUser) && (int) ($existingUser['id'] ?? 0) > 0) {
        return $existingUser;
    }

    $db = cibo_app_db();

    if (!$db) {
        return null;
    }

    $userId = (int) ($account['id'] ?? 0);
    $email = strtolower(trim((string) ($account['email'] ?? '')));

    if ($userId <= 0 && $email === '') {
        return null;
    }

    if ($userId > 0) {
        $statement = $db->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ? LIMIT 1');

        if (!$statement) {
            return null;
        }

        $statement->bind_param('i', $userId);
    } else {
        $statement = $db->prepare('SELECT id, name, email, phone, created_at FROM users WHERE email = ? LIMIT 1');

        if (!$statement) {
            return null;
        }

        $statement->bind_param('s', $email);
    }

    $statement->execute();
    $user = $statement->get_result()?->fetch_assoc();
    $statement->close();

    if (!$user) {
        return null;
    }

    return cibo_user_login($user);
}

function cibo_user_logout(): void
{
    cibo_start_user_session();

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

function cibo_register_user(string $name, string $email, string $password, string $phone = ''): array
{
    $name = cibo_normalize_single_line($name, 120);
    $email = strtolower(trim($email));
    $phone = cibo_normalize_phone_value($phone);
    $password = trim($password);

    if ($name === '' || $email === '' || $password === '') {
        throw new CiboHttpException('Name, email, and password are required.', 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new CiboHttpException('Please enter a valid email address.', 422);
    }

    if ($email === 'admin@cibo.local') {
        throw new CiboHttpException('Admin access is available only in the admin portal.', 422);
    }

    if (strlen($password) < 6) {
        throw new CiboHttpException('Password must be at least 6 characters long.', 422);
    }

    if (strlen($password) > 72) {
        throw new CiboHttpException('Password is too long.', 422);
    }

    if ($phone !== '' && strlen($phone) !== 10) {
        throw new CiboHttpException('Phone number must be 10 digits.', 422);
    }

    $db = cibo_app_db();

    if (!$db) {
        throw new CiboHttpException('User database is not ready yet. Please verify the cibo_db connection.', 500);
    }

    $existingUser = cibo_find_user_by_email($email);

    if ($existingUser) {
        throw new CiboHttpException('An account with this email already exists.', 422);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertStatement = $db->prepare('INSERT INTO users (name, email, password_hash, phone) VALUES (?, ?, ?, ?)');

    if (!$insertStatement) {
        throw new CiboHttpException('Unable to create the user account.', 500);
    }

    $insertStatement->bind_param('ssss', $name, $email, $passwordHash, $phone);

    if (!$insertStatement->execute()) {
        $insertStatement->close();
        throw new CiboHttpException('Unable to create the user account.', 500);
    }

    $createdId = (int) $insertStatement->insert_id;
    $insertStatement->close();

    $user = cibo_find_user_by_id($createdId) ?? [
        'id' => $createdId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    return cibo_user_login($user);
}

function cibo_attempt_user_login(string $email, string $password): array
{
    $email = strtolower(trim($email));
    $password = trim($password);

    if ($email === '' || $password === '') {
        throw new CiboHttpException('Email and password are required.', 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new CiboHttpException('Please enter a valid email address.', 422);
    }

    if ($email === 'admin@cibo.local') {
        throw new CiboHttpException('Admin access is available only in the admin portal.', 422);
    }

    $db = cibo_app_db();

    if (!$db) {
        throw new CiboHttpException('User database is not ready yet. Please verify the cibo_db connection.', 500);
    }

    $user = cibo_find_user_by_email($email, true);

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        throw new CiboHttpException('Email or password is incorrect.', 401);
    }

    return cibo_user_login($user);
}
