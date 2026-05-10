<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/user-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cibo_method_not_allowed(['POST']);
}

$input = cibo_json_input();

try {
    $user = cibo_attempt_user_login(
        (string) ($input['email'] ?? ''),
        (string) ($input['password'] ?? '')
    );

    cibo_json_response([
        'success' => true,
        'user' => $user,
    ]);
} catch (Throwable $exception) {
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
