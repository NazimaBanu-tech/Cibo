<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-data.php';

cibo_admin_require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    cibo_method_not_allowed(['GET']);
}

$format = strtolower(trim((string) ($_GET['format'] ?? 'json')));
$report = cibo_admin_sales_report([
    'format' => $format,
]);

if ($format === 'pdf') {
    $filename = 'cibo-sales-report-' . date('Ymd-His') . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
    echo cibo_admin_sales_report_pdf_bytes($report);
    exit;
}

if ($format === 'csv') {
    $filename = 'cibo-sales-report-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');

    $stream = fopen('php://output', 'wb');

    if ($stream === false) {
        cibo_json_response([
            'success' => false,
            'message' => 'Unable to create the CSV export stream.',
        ], 500);
    }

    fwrite($stream, "\xEF\xBB\xBF");

    $summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
    fputcsv($stream, ['Cibo Sales Report']);
    fputcsv($stream, ['Generated At', (string) ($report['generated_at'] ?? '')]);
    fputcsv($stream, ['Total Orders', (string) (int) ($summary['total_orders'] ?? 0)]);
    fputcsv($stream, ['Total Revenue', (string) round((float) ($summary['total_revenue'] ?? 0), 2)]);
    fputcsv($stream, ['Today Revenue', (string) round((float) ($summary['today_revenue'] ?? 0), 2)]);
    fputcsv($stream, ['Paid Orders', (string) (int) ($summary['paid_orders'] ?? 0)]);
    fputcsv($stream, ['Cancelled Orders', (string) (int) ($summary['cancelled_orders'] ?? 0)]);
    fputcsv($stream, ['Average Order Value', (string) round((float) ($summary['average_order_value'] ?? 0), 2)]);
    fputcsv($stream, []);
    fputcsv($stream, [
        'Order Number',
        'Placed At',
        'Delivered At',
        'Restaurant',
        'Customer',
        'Phone',
        'Delivery Address',
        'Payment Method',
        'Payment Status',
        'Order Status',
        'Subtotal',
        'Delivery Fee',
        'Tax',
        'Discount',
        'Total',
        'Included In Revenue',
        'Item Count',
        'Items',
    ]);

    foreach (is_array($report['orders'] ?? null) ? $report['orders'] : [] as $order) {
        fputcsv($stream, [
            (string) ($order['order_number'] ?? '--'),
            (string) ($order['placed_at'] ?? ''),
            (string) ($order['delivered_at'] ?? ''),
            (string) ($order['restaurant_name'] ?? 'Cibo Order'),
            (string) ($order['customer_name'] ?? '--'),
            (string) ($order['customer_phone'] ?? '--'),
            (string) ($order['delivery_address'] ?? '--'),
            (string) ($order['payment_method'] ?? '--'),
            (string) ($order['payment_status_label'] ?? '--'),
            (string) ($order['order_status_label'] ?? '--'),
            number_format((float) ($order['subtotal'] ?? 0), 2, '.', ''),
            number_format((float) ($order['delivery_fee'] ?? 0), 2, '.', ''),
            number_format((float) ($order['tax_amount'] ?? 0), 2, '.', ''),
            number_format((float) ($order['discount_amount'] ?? 0), 2, '.', ''),
            number_format((float) ($order['total_amount'] ?? 0), 2, '.', ''),
            !empty($order['included_in_revenue']) ? 'Yes' : 'No',
            (string) (int) ($order['item_count'] ?? 0),
            (string) ($order['items_summary'] ?? ''),
        ]);
    }

    fclose($stream);
    exit;
}

cibo_json_response([
    'success' => true,
    'report' => $report,
]);
