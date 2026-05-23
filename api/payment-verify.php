<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/payment-gateway.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    cibo_method_not_allowed(['POST']);
}

$input = cibo_json_input();

try {
    cibo_start_user_session();
    cibo_request_guard_begin('customer-payment-verify');
    $order = cibo_verify_prepaid_checkout($input);
    cibo_request_guard_finish('customer-payment-verify', true);

    cibo_json_response([
        'success' => true,
        'order' => $order,
    ]);
} catch (Throwable $exception) {
    cibo_request_guard_finish('customer-payment-verify', false);
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
