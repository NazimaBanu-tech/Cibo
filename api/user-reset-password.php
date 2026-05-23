<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/user-auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
    cibo_method_not_allowed(['POST']);
}

$input = cibo_json_input();

try {
    cibo_reset_user_password(
        (string) ($input['email'] ?? ''),
        (string) ($input['phone'] ?? ''),
        (string) ($input['password'] ?? '')
    );

    cibo_json_response([
        'success' => true,
        'message' => 'Password updated successfully. You can now sign in with your new password.',
    ]);
} catch (Throwable $exception) {
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
