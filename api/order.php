<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/orders.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        cibo_start_user_session();

        $orderNumber = (string) ($_GET['order'] ?? ($_GET['order_number'] ?? ($_SESSION['last_order_number'] ?? '')));
        $order = cibo_fetch_order_by_number_for_session($orderNumber);

        if (!$order) {
            cibo_json_response([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        cibo_json_response([
            'success' => true,
            'order' => $order,
        ]);
    } catch (Throwable $exception) {
        cibo_json_response([
            'success' => false,
            'message' => $exception->getMessage(),
        ], cibo_exception_status($exception, 422));
    }
}

cibo_method_not_allowed(['GET']);
