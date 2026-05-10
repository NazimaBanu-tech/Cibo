<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/account.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        cibo_json_response([
            'success' => true,
            'addresses' => cibo_fetch_current_user_addresses(),
        ]);
    }

    if ($method === 'POST') {
        $input = cibo_json_input();
        cibo_start_user_session();
        cibo_request_guard_begin('customer-address-save');
        $address = cibo_save_current_user_address($input);
        cibo_request_guard_finish('customer-address-save', true);

        cibo_json_response([
            'success' => true,
            'address' => $address,
        ]);
    }

    if ($method === 'DELETE') {
        $input = cibo_json_input();
        cibo_start_user_session();
        cibo_request_guard_begin('customer-address-delete');
        cibo_delete_current_user_address((int) ($input['id'] ?? 0));
        cibo_request_guard_finish('customer-address-delete', true);

        cibo_json_response([
            'success' => true,
        ]);
    }

    cibo_method_not_allowed(['GET', 'POST', 'DELETE']);
} catch (Throwable $exception) {
    cibo_request_guard_finish('customer-address-save', false);
    cibo_request_guard_finish('customer-address-delete', false);
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 422));
}
