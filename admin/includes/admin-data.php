<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../includes/orders.php';

function cibo_admin_profile_override(): ?array
{
    $profile = $_SESSION['cibo_admin_profile_override'] ?? null;
    return is_array($profile) ? $profile : null;
}

function cibo_admin_store_profile_override(array $profile): void
{
    $_SESSION['cibo_admin_profile_override'] = [
        'id' => (int) ($profile['id'] ?? 0),
        'name' => (string) ($profile['name'] ?? CIBO_ADMIN_FALLBACK_NAME),
        'email' => (string) ($profile['email'] ?? CIBO_ADMIN_FALLBACK_EMAIL),
        'role' => (string) ($profile['role'] ?? 'Admin'),
        'created_at' => $profile['created_at'] ?? null,
    ];
}

function cibo_admin_clear_profile_override(): void
{
    unset($_SESSION['cibo_admin_profile_override']);
}

function cibo_admin_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cibo_admin_asset_url(?string $path): string
{
    $path = trim((string) $path);

    if ($path === '') {
        return '';
    }

    if (
        preg_match('#^(?:[a-z]+:)?//#i', $path)
        || str_starts_with($path, '/')
        || str_starts_with($path, '../')
        || str_starts_with($path, './')
        || str_starts_with($path, 'data:')
    ) {
        return $path;
    }

    return '../' . ltrim($path, '/');
}

function cibo_admin_flash(string $type, string $message): void
{
    $_SESSION['cibo_admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function cibo_admin_pull_flash(): ?array
{
    $flash = $_SESSION['cibo_admin_flash'] ?? null;
    unset($_SESSION['cibo_admin_flash']);

    return is_array($flash) ? $flash : null;
}

function cibo_admin_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'item';
}

function cibo_admin_unique_slug(string $table, string $baseSlug, string $nameColumn = 'slug', ?int $ignoreId = null, ?int $restaurantId = null): string
{
    $db = cibo_db();

    if (!$db) {
        return $baseSlug !== '' ? $baseSlug : 'item';
    }

    $slug = $baseSlug !== '' ? $baseSlug : 'item';
    $candidate = $slug;
    $counter = 2;

    while (true) {
        $query = "SELECT id FROM {$table} WHERE {$nameColumn} = ?";
        $types = 's';
        $params = [$candidate];

        if ($restaurantId !== null) {
            $query .= ' AND restaurant_id = ?';
            $types .= 'i';
            $params[] = $restaurantId;
        }

        if ($ignoreId !== null) {
            $query .= ' AND id != ?';
            $types .= 'i';
            $params[] = $ignoreId;
        }

        $query .= ' LIMIT 1';
        $statement = $db->prepare($query);

        if (!$statement) {
            return $candidate;
        }

        $statement->bind_param($types, ...$params);
        $statement->execute();
        $exists = $statement->get_result()?->fetch_assoc();
        $statement->close();

        if (!$exists) {
            return $candidate;
        }

        $candidate = $slug . '-' . $counter;
        $counter++;
    }
}

function cibo_admin_dashboard_stats(?array $orders = null): array
{
    $db = cibo_db();

    if (!$db) {
        return [
            'orders' => 0,
            'revenue' => 0,
            'today_revenue' => 0,
            'placed_orders' => 0,
            'preparing_orders' => 0,
            'out_for_delivery_orders' => 0,
            'delivered_orders' => 0,
            'cancelled_orders' => 0,
            'users' => 0,
            'restaurants' => 0,
        ];
    }

    $safeOrders = is_array($orders) ? $orders : cibo_admin_fetch_orders();
    $orderCount = count($safeOrders);
    $revenue = 0.0;
    $todayRevenue = 0.0;
    $today = date('Y-m-d');
    $statusCounts = [
        'placed' => 0,
        'preparing' => 0,
        'out_for_delivery' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ];

    foreach ($safeOrders as $order) {
        $status = cibo_normalize_order_status((string) ($order['order_status'] ?? 'placed'));

        if (array_key_exists($status, $statusCounts)) {
            $statusCounts[$status]++;
        }

        if ($status === 'cancelled') {
            continue;
        }

        $orderTotal = (float) ($order['total_amount'] ?? 0);
        $revenue += $orderTotal;

        $orderDate = (string) ($order['placed_at'] ?? $order['created_at'] ?? '');
        if ($orderDate !== '' && str_starts_with($orderDate, $today)) {
            $todayRevenue += $orderTotal;
        }
    }

    $users = (int) ($db->query('SELECT COUNT(*) AS total FROM users')?->fetch_assoc()['total'] ?? 0);
    $restaurants = (int) ($db->query('SELECT COUNT(*) AS total FROM restaurants WHERE is_active = 1')?->fetch_assoc()['total'] ?? 0);

    return [
        'orders' => $orderCount,
        'revenue' => round($revenue, 2),
        'today_revenue' => round($todayRevenue, 2),
        'placed_orders' => $statusCounts['placed'],
        'preparing_orders' => $statusCounts['preparing'],
        'out_for_delivery_orders' => $statusCounts['out_for_delivery'],
        'delivered_orders' => $statusCounts['delivered'],
        'cancelled_orders' => $statusCounts['cancelled'],
        'users' => $users,
        'restaurants' => $restaurants,
    ];
}

function cibo_admin_fetch_restaurants(): array
{
    $db = cibo_db();
    if (!$db) {
        return [];
    }

    $result = $db->query('
        SELECT
            id,
            name,
            slug,
            category,
            COALESCE(cuisine, category) AS cuisine,
            location,
            rating,
            delivery_time,
            image AS image_path,
            hero_image AS hero_image_path,
            address,
            offer_text
        FROM restaurants
        ORDER BY updated_at DESC, id DESC
    ');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cibo_admin_fetch_restaurant_options(): array
{
    $db = cibo_db();
    if (!$db) {
        return [];
    }

    $result = $db->query('SELECT id, name FROM restaurants ORDER BY name ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cibo_admin_save_restaurant(array $input): void
{
    $id = (int) ($input['id'] ?? 0);
    $name = trim((string) ($input['name'] ?? ''));
    $image = trim((string) ($input['image_path'] ?? $input['image'] ?? ''));
    $heroImage = trim((string) ($input['hero_image_path'] ?? $input['heroImage'] ?? $input['hero_image'] ?? $image));
    $location = trim((string) ($input['location'] ?? ''));
    $category = trim((string) ($input['category'] ?? $input['cuisine'] ?? $input['cuisines'] ?? ''));
    $rating = max(0.0, (float) ($input['rating'] ?? 4.3));
    $deliveryTime = trim((string) ($input['delivery_time'] ?? $input['deliveryTime'] ?? '25-30 mins')) ?: '25-30 mins';
    $offerText = trim((string) ($input['offer_text'] ?? $input['offerText'] ?? 'Free delivery above Rs 199')) ?: 'Free delivery above Rs 199';
    $address = trim((string) ($input['address'] ?? $location));
    $name = cibo_normalize_single_line($name, 120);
    $image = cibo_normalize_single_line($image, 255);
    $heroImage = cibo_normalize_single_line($heroImage, 255);
    $location = cibo_normalize_single_line($location, 160);
    $category = cibo_normalize_single_line($category, 80);
    $rating = min(5.0, max(0.0, $rating));
    $deliveryTime = cibo_normalize_single_line($deliveryTime, 40) ?: '25-30 mins';
    $offerText = cibo_normalize_single_line($offerText, 120) ?: 'Free delivery above 199';
    $address = cibo_normalize_single_line($address, 200);

    if ($name === '' || $location === '' || $category === '') {
        throw new RuntimeException('Restaurant name, location, and category are required.');
    }

    $slug = cibo_admin_unique_slug('restaurants', cibo_admin_slugify($name), 'slug', $id ?: null);
    $db = cibo_db();

    if (!$db) {
        throw new RuntimeException('The admin database is not ready yet. Import database/schema.sql first.');
    }

    if ($id > 0) {
        $statement = $db->prepare('
            UPDATE restaurants
            SET name = ?, slug = ?, category = ?, cuisine = ?, location = ?, rating = ?, delivery_time = ?, image = ?, hero_image = ?, address = ?, offer_text = ?
            WHERE id = ?
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to update the restaurant.');
        }

        $offerText = 'Free delivery above ₹199';
        $statement->bind_param('sssssdsssssi', $name, $slug, $category, $category, $location, $rating, $deliveryTime, $image, $heroImage, $address, $offerText, $id);
        $statement->execute();
        $statement->close();
        return;
    }

    $statement = $db->prepare('
        INSERT INTO restaurants (name, slug, category, cuisine, location, rating, delivery_time, image, hero_image, address, offer_text)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    if (!$statement) {
        throw new RuntimeException('Unable to add the restaurant.');
    }

    $offerText = 'Free delivery above ₹199';
    $statement->bind_param('sssssdsssss', $name, $slug, $category, $category, $location, $rating, $deliveryTime, $image, $heroImage, $address, $offerText);
    $statement->execute();
    $statement->close();
}

function cibo_admin_delete_restaurant(int $id): void
{
    if ($id <= 0) {
        return;
    }

    $db = cibo_db();
    if (!$db) {
        throw new RuntimeException('The admin database is not ready yet. Import database/schema.sql first.');
    }

    $statement = $db->prepare('DELETE FROM restaurants WHERE id = ?');

    if ($statement) {
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}

function cibo_admin_fetch_menu_items(): array
{
    $query = '
        SELECT
            mi.id,
            mi.restaurant_id,
            mi.name,
            mi.slug,
            mi.description,
            mi.price,
            mi.food_type,
            mi.image AS image_path,
            r.name AS restaurant_name,
            r.slug AS restaurant_slug
        FROM menu_items mi
        INNER JOIN restaurants r ON r.id = mi.restaurant_id
        ORDER BY mi.updated_at DESC, mi.id DESC
    ';

    $db = cibo_db();
    if (!$db) {
        return [];
    }

    $result = $db->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cibo_admin_save_menu_item(array $input): void
{
    $id = (int) ($input['id'] ?? 0);
    $restaurantId = (int) ($input['restaurant_id'] ?? 0);
    $name = trim((string) ($input['name'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $price = (float) ($input['price'] ?? 0);
    $foodType = trim((string) ($input['food_type'] ?? 'veg'));
    $image = trim((string) ($input['image_path'] ?? $input['image'] ?? ''));
    $name = cibo_normalize_single_line($name, 120);
    $description = cibo_normalize_multiline_text($description, 600);
    $foodType = cibo_normalize_single_line($foodType, 20);
    $image = cibo_normalize_single_line($image, 255);

    if ($restaurantId <= 0 || $name === '' || $price <= 0) {
        throw new RuntimeException('Restaurant, item name, and price are required.');
    }

    if ($price > 50000) {
        throw new RuntimeException('Please enter a valid item price.');
    }

    $allowedTypes = ['veg', 'nonveg'];
    if (!in_array($foodType, $allowedTypes, true)) {
        $foodType = 'veg';
    }

    $slug = cibo_admin_unique_slug('menu_items', cibo_admin_slugify($name), 'slug', $id ?: null, $restaurantId);
    $db = cibo_db();

    if (!$db) {
        throw new RuntimeException('The admin database is not ready yet. Import database/schema.sql first.');
    }

    if ($id > 0) {
        $statement = $db->prepare('
            UPDATE menu_items
            SET restaurant_id = ?, name = ?, slug = ?, description = ?, price = ?, food_type = ?, image = ?
            WHERE id = ?
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to update the menu item.');
        }

        $statement->bind_param('isssdssi', $restaurantId, $name, $slug, $description, $price, $foodType, $image, $id);
        $statement->execute();
        $statement->close();
        return;
    }

    $statement = $db->prepare('
        INSERT INTO menu_items (restaurant_id, name, slug, description, price, food_type, image)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    if (!$statement) {
        throw new RuntimeException('Unable to add the menu item.');
    }

    $statement->bind_param('isssdss', $restaurantId, $name, $slug, $description, $price, $foodType, $image);
    $statement->execute();
    $statement->close();
}

function cibo_admin_delete_menu_item(int $id): void
{
    if ($id <= 0) {
        return;
    }

    $db = cibo_db();
    if (!$db) {
        throw new RuntimeException('The admin database is not ready yet. Import database/schema.sql first.');
    }

    $statement = $db->prepare('DELETE FROM menu_items WHERE id = ?');

    if ($statement) {
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}

function cibo_admin_fetch_orders(): array
{
    return array_map(static function (array $order): array {
        $itemSummary = array_map(
            static fn (array $item): string => trim((string) ($item['name'] ?? 'Item')) . ' x' . (int) ($item['quantity'] ?? 1),
            is_array($order['items'] ?? null) ? $order['items'] : []
        );

        $order['items_summary'] = $itemSummary ? implode(', ', $itemSummary) : 'No items listed';
        return $order;
    }, cibo_fetch_all_orders());
}

function cibo_admin_sales_report(array $filters = []): array
{
    $orders = cibo_admin_fetch_orders();
    $stats = cibo_admin_dashboard_stats($orders);

    if (!$orders) {
        return [
            'generated_at' => date('c'),
            'summary' => [
                'total_orders' => 0,
                'total_revenue' => 0.0,
                'today_revenue' => 0.0,
                'paid_orders' => 0,
                'cancelled_orders' => 0,
                'average_order_value' => 0.0,
            ],
            'daily_sales' => [],
            'top_restaurants' => [],
            'recent_activity' => [],
            'orders' => [],
            'filters' => $filters,
            'export' => [
                'pdf_endpoint' => 'api/sales-report.php?format=pdf',
                'csv_endpoint' => 'api/sales-report.php?format=csv',
                'status' => 'ready',
            ],
        ];
    }

    $sortedOrders = $orders;
    usort($sortedOrders, static function (array $first, array $second): int {
        return strtotime((string) ($second['placed_at'] ?? $second['created_at'] ?? '')) <=> strtotime((string) ($first['placed_at'] ?? $first['created_at'] ?? ''));
    });

    $dailySales = [];
    $topRestaurants = [];
    $paidOrders = 0;
    $nonCancelledOrders = 0;

    foreach ($sortedOrders as $order) {
        $status = cibo_normalize_order_status((string) ($order['order_status'] ?? 'placed'));
        $paymentStatus = strtolower(trim((string) ($order['payment_status'] ?? '')));
        $orderTotal = round((float) ($order['total_amount'] ?? 0), 2);
        $saleDate = substr((string) ($order['placed_at'] ?? $order['created_at'] ?? ''), 0, 10) ?: '--';
        $restaurantName = trim((string) ($order['restaurant_name'] ?? 'Cibo Order')) ?: 'Cibo Order';

        if ($paymentStatus === 'paid') {
            $paidOrders++;
        }

        if (!isset($dailySales[$saleDate])) {
            $dailySales[$saleDate] = [
                'date' => $saleDate,
                'total_orders' => 0,
                'total_revenue' => 0.0,
                'paid_orders' => 0,
                'cancelled_orders' => 0,
            ];
        }

        $dailySales[$saleDate]['total_orders']++;
        if ($paymentStatus === 'paid') {
            $dailySales[$saleDate]['paid_orders']++;
        }

        if ($status === 'cancelled') {
            $dailySales[$saleDate]['cancelled_orders']++;
        } else {
            $dailySales[$saleDate]['total_revenue'] += $orderTotal;
            $nonCancelledOrders++;

            if (!isset($topRestaurants[$restaurantName])) {
                $topRestaurants[$restaurantName] = [
                    'name' => $restaurantName,
                    'order_count' => 0,
                    'total_revenue' => 0.0,
                ];
            }

            $topRestaurants[$restaurantName]['order_count']++;
            $topRestaurants[$restaurantName]['total_revenue'] += $orderTotal;
        }
    }

    $dailySales = array_values(array_map(static function (array $row): array {
        $row['total_revenue'] = round((float) $row['total_revenue'], 2);
        return $row;
    }, $dailySales));

    usort($dailySales, static fn (array $first, array $second): int => strcmp((string) $second['date'], (string) $first['date']));
    $dailySales = array_slice($dailySales, 0, 60);

    $topRestaurants = array_values(array_map(static function (array $row): array {
        $row['total_revenue'] = round((float) $row['total_revenue'], 2);
        return $row;
    }, $topRestaurants));

    usort($topRestaurants, static function (array $first, array $second): int {
        $orderCountCompare = ((int) $second['order_count']) <=> ((int) $first['order_count']);
        if ($orderCountCompare !== 0) {
            return $orderCountCompare;
        }

        return ((float) $second['total_revenue']) <=> ((float) $first['total_revenue']);
    });
    $topRestaurants = array_slice($topRestaurants, 0, 5);

    $recentActivity = array_map(static function (array $order): array {
        return [
            'order_number' => (string) ($order['order_number'] ?? '--'),
            'restaurant_name' => (string) ($order['restaurant_name'] ?? 'Cibo Order'),
            'customer_name' => (string) ($order['user_name'] ?? '--'),
            'order_status_label' => (string) ($order['order_status_label'] ?? '--'),
            'payment_status_label' => (string) ($order['payment_status_label'] ?? '--'),
            'total_amount' => round((float) ($order['total_amount'] ?? 0), 2),
            'placed_at' => (string) ($order['placed_at'] ?? $order['created_at'] ?? ''),
        ];
    }, array_slice($sortedOrders, 0, 12));

    $exportOrders = array_map(static function (array $order): array {
        $items = is_array($order['items'] ?? null) ? $order['items'] : [];

        return [
            'order_number' => (string) ($order['order_number'] ?? '--'),
            'placed_at' => (string) ($order['placed_at'] ?? $order['created_at'] ?? ''),
            'delivered_at' => (string) ($order['delivered_at'] ?? ''),
            'restaurant_name' => (string) ($order['restaurant_name'] ?? 'Cibo Order'),
            'customer_name' => (string) ($order['user_name'] ?? '--'),
            'customer_phone' => (string) ($order['customer_phone'] ?? '--'),
            'delivery_address' => (string) ($order['delivery_address'] ?? '--'),
            'payment_method' => cibo_payment_method_label((string) ($order['payment_method'] ?? '')),
            'payment_status_label' => (string) ($order['payment_status_label'] ?? '--'),
            'order_status_label' => (string) ($order['order_status_label'] ?? '--'),
            'subtotal' => round((float) ($order['subtotal'] ?? 0), 2),
            'delivery_fee' => round((float) ($order['delivery_fee'] ?? 0), 2),
            'tax_amount' => round((float) ($order['tax_amount'] ?? 0), 2),
            'discount_amount' => round((float) ($order['discount_amount'] ?? 0), 2),
            'total_amount' => round((float) ($order['total_amount'] ?? 0), 2),
            'included_in_revenue' => cibo_normalize_order_status((string) ($order['order_status'] ?? '')) !== 'cancelled',
            'item_count' => array_sum(array_map(static fn (array $item): int => (int) ($item['quantity'] ?? 0), $items)),
            'items_summary' => implode('; ', array_map(
                static fn (array $item): string => trim((string) ($item['name'] ?? 'Item')) . ' x' . (int) ($item['quantity'] ?? 1),
                $items
            )),
        ];
    }, $sortedOrders);

    return [
        'generated_at' => date('c'),
        'summary' => [
            'total_orders' => (int) ($stats['orders'] ?? 0),
            'total_revenue' => round((float) ($stats['revenue'] ?? 0), 2),
            'today_revenue' => round((float) ($stats['today_revenue'] ?? 0), 2),
            'paid_orders' => $paidOrders,
            'cancelled_orders' => (int) ($stats['cancelled_orders'] ?? 0),
            'average_order_value' => $nonCancelledOrders > 0 ? round(((float) ($stats['revenue'] ?? 0)) / $nonCancelledOrders, 2) : 0.0,
        ],
        'daily_sales' => $dailySales,
        'top_restaurants' => $topRestaurants,
        'recent_activity' => $recentActivity,
        'orders' => $exportOrders,
        'filters' => $filters,
        'export' => [
            'pdf_endpoint' => 'api/sales-report.php?format=pdf',
            'csv_endpoint' => 'api/sales-report.php?format=csv',
            'status' => 'ready',
        ],
    ];
}

function cibo_admin_sales_report_format_money(float $amount): string
{
    return 'Rs ' . number_format($amount, 2);
}

function cibo_admin_sales_report_format_datetime(string $value): string
{
    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value !== '' ? $value : '--';
    }

    return date('d M Y, h:i A', $timestamp);
}

function cibo_admin_sales_report_format_date(string $value): string
{
    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value !== '' ? $value : '--';
    }

    return date('d M Y', $timestamp);
}

function cibo_admin_sales_report_pdf_safe_text(string $text): string
{
    $text = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    $text = trim($text);
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
    return $converted !== false ? $converted : $text;
}

function cibo_admin_sales_report_pdf_multiline(string $text, int $limit = 78): array
{
    $clean = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

    if ($clean === '') {
        return ['--'];
    }

    return str_split(wordwrap($clean, $limit, "\n", true), strpos(wordwrap($clean, $limit, "\n", true), "\n") !== false ? strlen(explode("\n", wordwrap($clean, $limit, "\n", true))[0]) : $limit);
}

function cibo_admin_sales_report_pdf_wrapped_lines(string $text, int $limit = 78): array
{
    $wrapped = wordwrap(trim(preg_replace('/\s+/', ' ', $text) ?? $text), $limit, "\n", true);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $wrapped)), static fn (string $line): bool => $line !== ''));
    return $lines ?: ['--'];
}

function cibo_admin_sales_report_pdf_bytes(array $report): string
{
    $summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
    $dailySales = array_slice(is_array($report['daily_sales'] ?? null) ? $report['daily_sales'] : [], 0, 20);
    $topRestaurants = array_slice(is_array($report['top_restaurants'] ?? null) ? $report['top_restaurants'] : [], 0, 5);
    $recentActivity = array_slice(is_array($report['recent_activity'] ?? null) ? $report['recent_activity'] : [], 0, 12);
    $generatedAt = cibo_admin_sales_report_format_datetime((string) ($report['generated_at'] ?? ''));

    $pages = [];
    $currentPage = [];
    $y = 792 - 48;

    $addLine = static function (string $text, float $x, float $yPos, int $fontSize = 11) use (&$currentPage): void {
        $safeText = cibo_admin_sales_report_pdf_safe_text($text);
        $currentPage[] = sprintf(
            "BT /F1 %d Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET",
            $fontSize,
            $x,
            $yPos,
            $safeText
        );
    };

    $rule = static function (float $fromX, float $toX, float $yPos) use (&$currentPage): void {
        $currentPage[] = sprintf("%.2f %.2f m %.2f %.2f l S", $fromX, $yPos, $toX, $yPos);
    };

    $startPage = static function (string $subtitle = '') use (&$pages, &$currentPage, &$y, $addLine, $rule, $generatedAt): void {
        if ($currentPage !== []) {
            $pages[] = $currentPage;
        }

        $currentPage = [];
        $y = 792 - 48;
        $addLine('Cibo Sales Report', 48, $y, 18);
        if ($subtitle !== '') {
            $addLine($subtitle, 370, $y + 2, 10);
        }
        $y -= 18;
        $addLine('Canonical admin export generated from backend order truth.', 48, $y, 10);
        $addLine('Generated: ' . $generatedAt, 330, $y, 10);
        $y -= 14;
        $rule(48, 548, $y);
        $y -= 18;
    };

    $ensureSpace = static function (float $neededHeight, string $subtitle = 'Continued') use (&$y, $startPage): void {
        if ($y - $neededHeight < 48) {
            $startPage($subtitle);
        }
    };

    $startPage();

    $addLine('Summary Metrics', 48, $y, 14);
    $y -= 18;

    $summaryLines = [
        'Total orders: ' . (int) ($summary['total_orders'] ?? 0),
        'Total revenue: ' . cibo_admin_sales_report_format_money((float) ($summary['total_revenue'] ?? 0)),
        'Today revenue: ' . cibo_admin_sales_report_format_money((float) ($summary['today_revenue'] ?? 0)),
        'Paid orders: ' . (int) ($summary['paid_orders'] ?? 0),
        'Cancelled orders: ' . (int) ($summary['cancelled_orders'] ?? 0),
        'Average order value: ' . cibo_admin_sales_report_format_money((float) ($summary['average_order_value'] ?? 0)),
    ];

    foreach ($summaryLines as $summaryLine) {
        $ensureSpace(14);
        $addLine($summaryLine, 56, $y, 11);
        $y -= 14;
    }

    $y -= 6;
    $rule(48, 548, $y);
    $y -= 20;

    $addLine('Top Restaurants', 48, $y, 14);
    $y -= 18;

    if ($topRestaurants) {
        foreach ($topRestaurants as $index => $restaurant) {
            $ensureSpace(28);
            $addLine(sprintf(
                '%d. %s',
                $index + 1,
                (string) ($restaurant['name'] ?? 'Cibo Order')
            ), 56, $y, 11);
            $addLine(
                sprintf(
                    '%d orders | %s revenue',
                    (int) ($restaurant['order_count'] ?? 0),
                    cibo_admin_sales_report_format_money((float) ($restaurant['total_revenue'] ?? 0))
                ),
                300,
                $y,
                11
            );
            $y -= 14;
        }
    } else {
        $addLine('No restaurant activity available yet.', 56, $y, 11);
        $y -= 14;
    }

    $y -= 6;
    $rule(48, 548, $y);
    $y -= 20;

    $addLine('Recent Activity', 48, $y, 14);
    $y -= 18;

    if ($recentActivity) {
        foreach ($recentActivity as $activity) {
            $activityLines = [
                '#' . (string) ($activity['order_number'] ?? '--') . ' | ' . (string) ($activity['restaurant_name'] ?? 'Cibo Order'),
                (string) ($activity['customer_name'] ?? '--') . ' | ' . (string) ($activity['order_status_label'] ?? '--') . ' | ' . (string) ($activity['payment_status_label'] ?? '--'),
                cibo_admin_sales_report_format_money((float) ($activity['total_amount'] ?? 0)) . ' | ' . cibo_admin_sales_report_format_datetime((string) ($activity['placed_at'] ?? '')),
            ];

            $ensureSpace(42, 'Recent Activity Continued');
            foreach ($activityLines as $line) {
                $addLine($line, 56, $y, 10);
                $y -= 12;
            }
            $y -= 4;
        }
    } else {
        $addLine('No recent activity available yet.', 56, $y, 11);
        $y -= 14;
    }

    $ensureSpace(40, 'Daily Sales');
    $rule(48, 548, $y);
    $y -= 20;
    $addLine('Daily Sales', 48, $y, 14);
    $y -= 18;
    $addLine('Date', 56, $y, 10);
    $addLine('Orders', 220, $y, 10);
    $addLine('Paid', 290, $y, 10);
    $addLine('Cancelled', 350, $y, 10);
    $addLine('Revenue', 460, $y, 10);
    $y -= 12;
    $rule(56, 540, $y);
    $y -= 14;

    if ($dailySales) {
        foreach ($dailySales as $day) {
            $ensureSpace(14, 'Daily Sales Continued');
            $addLine(cibo_admin_sales_report_format_date((string) ($day['date'] ?? '')), 56, $y, 10);
            $addLine((string) (int) ($day['total_orders'] ?? 0), 228, $y, 10);
            $addLine((string) (int) ($day['paid_orders'] ?? 0), 294, $y, 10);
            $addLine((string) (int) ($day['cancelled_orders'] ?? 0), 370, $y, 10);
            $addLine(cibo_admin_sales_report_format_money((float) ($day['total_revenue'] ?? 0)), 448, $y, 10);
            $y -= 14;
        }
    } else {
        $addLine('No daily sales data available yet.', 56, $y, 11);
        $y -= 14;
    }

    if ($currentPage !== []) {
        $pages[] = $currentPage;
    }

    $objects = [];
    $fontObjectId = 1;
    $pageObjectIds = [];
    $contentObjectIds = [];
    $nextId = 2;

    foreach ($pages as $pageContent) {
        $contentObjectIds[] = $nextId++;
        $pageObjectIds[] = $nextId++;
    }

    $pagesObjectId = $nextId++;
    $catalogObjectId = $nextId++;

    $objects[$fontObjectId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

    foreach ($pages as $index => $pageContent) {
        $contentStream = implode("\n", $pageContent);
        $contentObjectId = $contentObjectIds[$index];
        $pageObjectId = $pageObjectIds[$index];

        $objects[$contentObjectId] = sprintf(
            "<< /Length %d >>\nstream\n%s\nendstream",
            strlen($contentStream),
            $contentStream
        );

        $objects[$pageObjectId] = sprintf(
            "<< /Type /Page /Parent %d 0 R /MediaBox [0 0 595 842] /Contents %d 0 R /Resources << /Font << /F1 %d 0 R >> >> >>",
            $pagesObjectId,
            $contentObjectId,
            $fontObjectId
        );
    }

    $kids = implode(' ', array_map(static fn (int $id): string => $id . ' 0 R', $pageObjectIds));
    $objects[$pagesObjectId] = sprintf("<< /Type /Pages /Kids [%s] /Count %d >>", $kids, count($pageObjectIds));
    $objects[$catalogObjectId] = sprintf("<< /Type /Catalog /Pages %d 0 R >>", $pagesObjectId);

    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [];

    foreach ($objects as $id => $body) {
        $offsets[$id] = strlen($pdf);
        $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . ($catalogObjectId + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($id = 1; $id <= $catalogObjectId; $id++) {
        $offset = $offsets[$id] ?? 0;
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }

    $pdf .= "trailer\n";
    $pdf .= sprintf("<< /Size %d /Root %d 0 R >>\n", $catalogObjectId + 1, $catalogObjectId);
    $pdf .= "startxref\n";
    $pdf .= $xrefOffset . "\n%%EOF";

    return $pdf;
}

function cibo_admin_update_order_status(int $orderId, string $status): void
{
    $db = cibo_db();
    if (!$db) {
        throw new RuntimeException('The admin database is not ready yet. Import database/schema.sql first.');
    }

    $statement = $db->prepare('SELECT order_number FROM orders WHERE id = ? LIMIT 1');

    if (!$statement) {
        throw new RuntimeException('Unable to find the order.');
    }

    $statement->bind_param('i', $orderId);
    $statement->execute();
    $order = $statement->get_result()?->fetch_assoc();
    $statement->close();

    if (!$order) {
        throw new RuntimeException('Unable to find the order.');
    }

    cibo_update_order_status((string) $order['order_number'], $status);
}

function cibo_admin_order_status_options(): array
{
    return cibo_order_status_options();
}

function cibo_admin_fetch_users(): array
{
    $db = cibo_db();
    if (!$db) {
        return [];
    }

    $result = $db->query('SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC, id DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function cibo_admin_ensure_profile_columns(mysqli $db): void
{
    $roleColumn = $db->query("SHOW COLUMNS FROM admin_users LIKE 'role'");
    $hasRoleColumn = $roleColumn && $roleColumn->fetch_assoc();

    if ($roleColumn instanceof mysqli_result) {
        $roleColumn->free();
    }

    if (!$hasRoleColumn) {
        $db->query("ALTER TABLE admin_users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'Admin' AFTER email");
    }
}

function cibo_admin_ensure_profile_record(mysqli $db, array $profile, ?array $sessionAdmin = null): array
{
    if ((int) ($profile['id'] ?? 0) > 0) {
        return $profile;
    }

    $sessionAdmin = is_array($sessionAdmin) ? $sessionAdmin : (cibo_admin_user() ?? []);
    $name = trim((string) ($sessionAdmin['name'] ?? $profile['name'] ?? CIBO_ADMIN_FALLBACK_NAME));
    $email = strtolower(trim((string) ($sessionAdmin['email'] ?? $profile['email'] ?? CIBO_ADMIN_FALLBACK_EMAIL)));
    $role = trim((string) ($profile['role'] ?? 'Admin')) ?: 'Admin';
    $createdAt = (string) ($profile['created_at'] ?? date('Y-m-d H:i:s'));

    $existingStatement = $db->prepare('SELECT id, name, email, role, created_at FROM admin_users WHERE email = ? LIMIT 1');

    if ($existingStatement) {
        $existingStatement->bind_param('s', $email);
        $existingStatement->execute();
        $existing = $existingStatement->get_result()?->fetch_assoc();
        $existingStatement->close();

        if ($existing) {
            return [
                'id' => (int) $existing['id'],
                'name' => (string) $existing['name'],
                'email' => (string) $existing['email'],
                'role' => (string) ($existing['role'] ?? 'Admin'),
                'created_at' => $existing['created_at'] ?? null,
            ];
        }
    }

    $insertStatement = $db->prepare('INSERT INTO admin_users (name, email, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?)');

    if (!$insertStatement) {
        throw new RuntimeException('Unable to create the admin profile record.');
    }

    $passwordHash = password_hash(CIBO_ADMIN_FALLBACK_PASSWORD, PASSWORD_DEFAULT);
    $insertStatement->bind_param('sssss', $name, $email, $role, $passwordHash, $createdAt);
    $insertStatement->execute();
    $createdId = (int) $insertStatement->insert_id;
    $insertStatement->close();

    return [
        'id' => $createdId,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'created_at' => $createdAt,
    ];
}

function cibo_admin_profile(): array
{
    $sessionAdmin = cibo_admin_user();

    if (!$sessionAdmin) {
        throw new RuntimeException('Admin session not found.');
    }

    $fallbackProfile = [
        'id' => (int) ($sessionAdmin['id'] ?? 0),
        'name' => (string) ($sessionAdmin['name'] ?? CIBO_ADMIN_FALLBACK_NAME),
        'email' => (string) ($sessionAdmin['email'] ?? CIBO_ADMIN_FALLBACK_EMAIL),
        'role' => 'Admin',
        'created_at' => null,
    ];
    $overrideProfile = cibo_admin_profile_override();

    if ($overrideProfile) {
        $fallbackProfile = array_merge($fallbackProfile, $overrideProfile);
    }

    $db = cibo_db();

    if (!$db) {
        return $fallbackProfile;
    }

    cibo_admin_ensure_profile_columns($db);

    $statement = null;

    if ((int) ($sessionAdmin['id'] ?? 0) > 0) {
        $statement = $db->prepare('SELECT id, name, email, role, created_at FROM admin_users WHERE id = ? LIMIT 1');
        if ($statement) {
            $adminId = (int) $sessionAdmin['id'];
            $statement->bind_param('i', $adminId);
        }
    } else {
        $statement = $db->prepare('SELECT id, name, email, role, created_at FROM admin_users WHERE email = ? LIMIT 1');
        if ($statement) {
            $email = (string) ($sessionAdmin['email'] ?? '');
            $statement->bind_param('s', $email);
        }
    }

    if (!$statement) {
        return $fallbackProfile;
    }

    $statement->execute();
    $result = $statement->get_result();
    $admin = $result ? $result->fetch_assoc() : null;
    $statement->close();

    if (!$admin) {
        return $fallbackProfile;
    }

    $profile = [
        'id' => (int) $admin['id'],
        'name' => (string) $admin['name'],
        'email' => (string) $admin['email'],
        'role' => (string) ($admin['role'] ?? 'Admin'),
        'created_at' => $admin['created_at'] ?? null,
    ];

    cibo_admin_refresh_session($profile);
    cibo_admin_clear_profile_override();

    return $profile;
}

function cibo_admin_update_profile(array $input): array
{
    $profile = cibo_admin_profile();
    $db = cibo_db();

    $name = trim((string) ($input['name'] ?? ''));
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $role = trim((string) ($input['role'] ?? 'Admin'));
    $joinedDate = trim((string) ($input['created_at'] ?? ''));

    if ($name === '' || $email === '') {
        throw new RuntimeException('Name and email are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Please enter a valid email address.');
    }

    if ($role === '') {
        $role = 'Admin';
    }

    $joinedAt = $profile['created_at'] ?? null;

    if ($joinedDate !== '') {
        $parsedDate = date_create($joinedDate);

        if (!$parsedDate) {
            throw new RuntimeException('Please enter a valid joined date.');
        }

        $joinedAt = $parsedDate->format('Y-m-d 00:00:00');
    }

    if (!$db) {
        $updatedProfile = [
            'id' => (int) ($profile['id'] ?? 0),
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'created_at' => $joinedAt,
        ];

        cibo_admin_store_profile_override($updatedProfile);
        cibo_admin_refresh_session($updatedProfile);

        return $updatedProfile;
    }

    cibo_admin_ensure_profile_columns($db);
    $profile = cibo_admin_ensure_profile_record($db, $profile);

    $duplicateStatement = $db->prepare('SELECT id FROM admin_users WHERE email = ? AND id != ? LIMIT 1');
    if (!$duplicateStatement) {
        throw new RuntimeException('Unable to validate the email address.');
    }

    $adminId = (int) $profile['id'];
    $duplicateStatement->bind_param('si', $email, $adminId);
    $duplicateStatement->execute();
    $duplicate = $duplicateStatement->get_result()?->fetch_assoc();
    $duplicateStatement->close();

    if ($duplicate) {
        throw new RuntimeException('That email address is already in use by another admin.');
    }

    $statement = $db->prepare('UPDATE admin_users SET name = ?, email = ?, role = ?, created_at = ? WHERE id = ?');
    if (!$statement) {
        throw new RuntimeException('Unable to update the admin profile.');
    }

    $statement->bind_param('ssssi', $name, $email, $role, $joinedAt, $adminId);
    $statement->execute();
    $statement->close();

    $updatedProfile = [
        'id' => $adminId,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'created_at' => $joinedAt,
    ];

    cibo_admin_refresh_session($updatedProfile);
    cibo_admin_store_profile_override($updatedProfile);

    return $updatedProfile;
}

function cibo_admin_change_password(array $input): void
{
    $profile = cibo_admin_profile();
    $db = cibo_db();

    if (!$db) {
        throw new RuntimeException('Admin password can only be changed for a saved admin account.');
    }

    $profile = cibo_admin_ensure_profile_record($db, $profile);

    $currentPassword = (string) ($input['current_password'] ?? '');
    $newPassword = (string) ($input['new_password'] ?? '');
    $confirmPassword = (string) ($input['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        throw new RuntimeException('All password fields are required.');
    }

    if ($newPassword !== $confirmPassword) {
        throw new RuntimeException('New password and confirm password must match.');
    }

    if (strlen($newPassword) < 6) {
        throw new RuntimeException('New password must be at least 6 characters long.');
    }

    $adminId = (int) $profile['id'];
    $statement = $db->prepare('SELECT password_hash FROM admin_users WHERE id = ? LIMIT 1');
    if (!$statement) {
        throw new RuntimeException('Unable to verify the current password.');
    }

    $statement->bind_param('i', $adminId);
    $statement->execute();
    $record = $statement->get_result()?->fetch_assoc();
    $statement->close();

    $storedHash = (string) ($record['password_hash'] ?? '');

    if ($storedHash === '' || !password_verify($currentPassword, $storedHash)) {
        throw new RuntimeException('Current password is incorrect.');
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStatement = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');

    if (!$updateStatement) {
        throw new RuntimeException('Unable to change the password.');
    }

    $updateStatement->bind_param('si', $newHash, $adminId);
    $updateStatement->execute();
    $updateStatement->close();
}
