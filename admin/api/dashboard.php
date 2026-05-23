<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-data.php';

cibo_admin_require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    cibo_method_not_allowed(['GET']);
}

$orders = cibo_admin_fetch_orders();

cibo_json_response([
    'success' => true,
    'stats' => cibo_admin_dashboard_stats($orders),
    'sales_report' => cibo_admin_sales_report(),
    'restaurants' => cibo_admin_fetch_restaurants(),
    'menu_items' => cibo_admin_fetch_menu_items(),
    'orders' => $orders,
    'users' => cibo_admin_fetch_users(),
    'status_options' => cibo_admin_order_status_options(),
]);
