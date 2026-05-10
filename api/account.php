<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/account.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        cibo_json_response([
            'success' => true,
            'user' => cibo_current_user_profile(),
        ]);
    }

    if ($method === 'POST') {
        $input = cibo_json_input();
        cibo_start_user_session();
        cibo_request_guard_begin('customer-account-update');
        $user = cibo_update_current_user_profile($input);
        cibo_request_guard_finish('customer-account-update', true);

        cibo_json_response([
            'success' => true,
            'user' => $user,
        ]);
    }

    cibo_method_not_allowed(['GET', 'POST']);
} catch (Throwable $exception) {
    cibo_request_guard_finish('customer-account-update', false);
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
