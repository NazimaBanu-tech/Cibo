<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/user-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cibo_method_not_allowed(['POST']);
}

cibo_user_logout();

cibo_json_response([
    'success' => true,
]);
