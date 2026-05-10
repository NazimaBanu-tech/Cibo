<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/common.php';
require_once __DIR__ . '/../includes/catalog.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    cibo_method_not_allowed(['GET']);
}

cibo_json_response([
    'success' => true,
    'restaurants' => cibo_catalog_fetch_restaurants(),
    'menu_items' => cibo_catalog_fetch_menu_items(),
]);
