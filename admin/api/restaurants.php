<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-data.php';

cibo_admin_require_login();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $input = cibo_json_input();

    try {
        cibo_request_guard_begin('admin-restaurant-save');
        cibo_admin_save_restaurant($input);
        cibo_request_guard_finish('admin-restaurant-save', true);

        cibo_json_response([
            'success' => true,
        ]);
    } catch (Throwable $exception) {
        cibo_request_guard_finish('admin-restaurant-save', false);
        cibo_json_response([
            'success' => false,
            'message' => $exception->getMessage(),
        ], cibo_exception_status($exception, 422));
    }
}

if ($method === 'DELETE') {
    $input = cibo_json_input();

    try {
        cibo_request_guard_begin('admin-restaurant-delete');
        cibo_admin_delete_restaurant((int) ($input['id'] ?? 0));
        cibo_request_guard_finish('admin-restaurant-delete', true);

        cibo_json_response([
            'success' => true,
        ]);
    } catch (Throwable $exception) {
        cibo_request_guard_finish('admin-restaurant-delete', false);
        cibo_json_response([
            'success' => false,
            'message' => $exception->getMessage(),
        ], cibo_exception_status($exception, 422));
    }
}

cibo_method_not_allowed(['POST', 'DELETE']);
