<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/orders.php';

cibo_admin_require_login();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    cibo_json_response([
        'success' => true,
        'orders' => cibo_fetch_all_orders(),
        'status_options' => cibo_order_status_options(),
    ]);
}

if ($method !== 'POST') {
    cibo_method_not_allowed(['GET', 'POST']);
}

$input = cibo_json_input();

try {
    cibo_request_guard_begin('admin-order-status-update');
    $order = cibo_update_order_status(
        (string) ($input['order_number'] ?? ''),
        (string) ($input['status'] ?? '')
    );
    cibo_request_guard_finish('admin-order-status-update', true);

    cibo_json_response([
        'success' => true,
        'order' => $order,
    ]);
} catch (Throwable $exception) {
    cibo_request_guard_finish('admin-order-status-update', false);
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
