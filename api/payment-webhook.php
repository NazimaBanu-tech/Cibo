<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/payment-gateway.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    cibo_method_not_allowed(['POST']);
}

$rawBody = file_get_contents('php://input');
$rawBody = is_string($rawBody) ? $rawBody : '';
$signature = trim((string) ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? ''));

try {
    cibo_handle_razorpay_webhook($rawBody, $signature);

    cibo_json_response([
        'success' => true,
    ]);
} catch (Throwable $exception) {
    cibo_json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], cibo_exception_status($exception, 400));
}
