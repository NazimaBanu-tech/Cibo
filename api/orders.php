<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/orders.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        $db = cibo_app_db();
        cibo_json_response([
            'success' => true,
            'orders' => cibo_fetch_orders_for_current_session(),
            'last_order_number' => $_SESSION['last_order_number'] ?? null,
            'order_context' => [
                'is_first_time_customer' => cibo_is_first_time_customer($db instanceof mysqli ? $db : null),
            ],
        ]);
    } catch (Throwable $exception) {
        cibo_json_response([
            'success' => false,
            'message' => $exception->getMessage(),
        ], cibo_exception_status($exception, 422));
    }
}

if ($method !== 'POST') {
    cibo_method_not_allowed(['GET', 'POST']);
}

$input = cibo_json_input();

try {
    if (($input['action'] ?? '') === 'summary') {
        $resolvedCheckout = cibo_resolve_checkout_payload($input);
        cibo_json_response([
            'success' => true,
            'summary' => $resolvedCheckout['summary'],
            'cart_items' => $resolvedCheckout['cart_items'],
        ]);
    }

    cibo_start_user_session();
    cibo_request_guard_begin('customer-order-create');
    $order = cibo_create_order($input);
    cibo_request_guard_finish('customer-order-create', true);

    cibo_json_response([
        'success' => true,
        'order' => $order,
    ], 201);
} catch (Throwable $exception) {
    cibo_request_guard_finish('customer-order-create', false);
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
