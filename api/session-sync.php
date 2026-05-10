<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/user-auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    cibo_method_not_allowed(['POST']);
}

$user = cibo_current_user();

if (!$user) {
    cibo_json_response([
        'success' => false,
        'message' => 'Not authenticated.',
    ], 401);
}

cibo_json_response([
    'success' => true,
    'user' => $user,
]);
