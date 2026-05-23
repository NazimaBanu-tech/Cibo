<?php

declare(strict_types=1);

require_once __DIR__ . '/user-auth.php';
require_once __DIR__ . '/account.php';

function cibo_order_status_options(): array
{
    return [
        'placed' => 'Placed',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];
}

function cibo_normalize_order_status(string $status): string
{
    $normalized = strtolower(trim($status));

    return match ($normalized) {
        'pending' => 'placed',
        'out-for-delivery' => 'out_for_delivery',
        default => $normalized,
    };
}

function cibo_order_status_label(string $status): string
{
    $normalized = cibo_normalize_order_status($status);
    return cibo_order_status_options()[$normalized] ?? 'Placed';
}

function cibo_order_status_transitions(): array
{
    return [
        'placed' => ['preparing', 'cancelled'],
        'preparing' => ['out_for_delivery'],
        'out_for_delivery' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];
}

function cibo_order_next_statuses(string $status): array
{
    $normalized = cibo_normalize_order_status($status);
    $transitions = cibo_order_status_transitions();

    return $transitions[$normalized] ?? [];
}

function cibo_order_status_is_final(string $status): bool
{
    return cibo_order_next_statuses($status) === [];
}

function cibo_order_progression_rank(string $status): int
{
    $normalized = cibo_normalize_order_status($status);
    $ranks = [
        'placed' => 0,
        'preparing' => 1,
        'out_for_delivery' => 2,
        'delivered' => 3,
        'cancelled' => 4,
    ];

    return $ranks[$normalized] ?? 0;
}

function cibo_demo_order_progression_thresholds(): array
{
    return [
        'preparing' => 12,
        'out_for_delivery' => 24,
        'delivered' => 36,
    ];
}

function cibo_demo_target_order_status_for_elapsed(int $elapsedSeconds): string
{
    $elapsedSeconds = max(0, $elapsedSeconds);
    $thresholds = cibo_demo_order_progression_thresholds();

    if ($elapsedSeconds >= (int) ($thresholds['delivered'] ?? 18)) {
        return 'delivered';
    }

    if ($elapsedSeconds >= (int) ($thresholds['out_for_delivery'] ?? 12)) {
        return 'out_for_delivery';
    }

    if ($elapsedSeconds >= (int) ($thresholds['preparing'] ?? 6)) {
        return 'preparing';
    }

    return 'placed';
}

function cibo_demo_target_order_status(string $placedAt): string
{
    $placedAt = trim($placedAt);

    if ($placedAt === '') {
        return 'placed';
    }

    $placedTimestamp = strtotime($placedAt);

    if ($placedTimestamp === false) {
        return 'placed';
    }

    return cibo_demo_target_order_status_for_elapsed(time() - $placedTimestamp);
}

function cibo_order_available_status_options(string $status): array
{
    $normalized = cibo_normalize_order_status($status);
    $options = cibo_order_status_options();
    $allowedStatuses = array_values(array_unique(array_merge([$normalized], cibo_order_next_statuses($normalized))));
    $availableOptions = [];

    foreach ($allowedStatuses as $allowedStatus) {
        if (!isset($options[$allowedStatus])) {
            continue;
        }

        $availableOptions[$allowedStatus] = $options[$allowedStatus];
    }

    return $availableOptions;
}

function cibo_assert_valid_order_status_transition(string $currentStatus, string $nextStatus): void
{
    $current = cibo_normalize_order_status($currentStatus);
    $next = cibo_normalize_order_status($nextStatus);

    if ($current === $next) {
        return;
    }

    $allowedNextStatuses = cibo_order_next_statuses($current);

    if (in_array($next, $allowedNextStatuses, true)) {
        return;
    }

    throw new RuntimeException(sprintf(
        'Invalid order status transition from %s to %s.',
        cibo_order_status_label($current),
        cibo_order_status_label($next)
    ));
}

function cibo_payment_status_label(string $paymentStatus, string $paymentMethod = ''): string
{
    $normalizedStatus = strtolower(trim($paymentStatus));
    $normalizedMethod = strtolower(trim($paymentMethod));

    if ($normalizedMethod === 'cod' && $normalizedStatus === 'paid') {
        return 'Paid (COD Collected)';
    }

    return match ($normalizedStatus) {
        'paid' => 'Paid',
        'failed' => 'Failed',
        default => 'Pending',
    };
}

function cibo_payment_method_label(string $paymentMethod): string
{
    $normalizedMethod = strtolower(trim($paymentMethod));

    return match ($normalizedMethod) {
        'cod' => 'Cash on Delivery',
        'upi' => 'UPI Payment',
        'card' => 'Card Payment',
        default => 'Payment',
    };
}

function cibo_receipt_signing_key(): string
{
    static $key = null;

    if (is_string($key) && $key !== '') {
        return $key;
    }

    $seed = implode('|', [
        __DIR__,
        CIBO_DB_HOST,
        (string) CIBO_DB_PORT,
        CIBO_DB_NAME,
        CIBO_DB_USER,
        php_uname('n') ?: 'cibo',
    ]);

    $key = hash('sha256', $seed);
    return $key;
}

function cibo_receipt_token_payload(array $order, ?array $receiptRecord = null): string
{
    return implode('|', [
        (string) ($order['order_number'] ?? ''),
        (string) ($order['id'] ?? 0),
        (string) ($receiptRecord['receipt_number'] ?? ''),
        (string) ($receiptRecord['generated_at'] ?? ''),
    ]);
}

function cibo_receipt_token_for_order(array $order, ?array $receiptRecord = null): string
{
    return hash_hmac('sha256', cibo_receipt_token_payload($order, $receiptRecord), cibo_receipt_signing_key());
}

function cibo_receipt_token_is_valid(array $order, ?array $receiptRecord, string $token): bool
{
    $safeToken = strtolower(trim($token));

    if (!preg_match('/^[a-f0-9]{64}$/', $safeToken)) {
        return false;
    }

    return hash_equals(cibo_receipt_token_for_order($order, $receiptRecord), $safeToken);
}

function cibo_receipt_url_for_order(array $order, ?array $receiptRecord = null, string $format = 'html'): string
{
    $orderNumber = trim((string) ($order['order_number'] ?? ''));

    if ($orderNumber === '') {
        return '#';
    }

    $query = [
        'order' => $orderNumber,
        'token' => cibo_receipt_token_for_order($order, $receiptRecord),
    ];

    if (strtolower(trim($format)) === 'pdf') {
        $query['format'] = 'pdf';
    }

    return 'receipt.php?' . http_build_query($query);
}

function cibo_normalize_lookup_value(string $value): string
{
    $normalized = strtolower(trim($value));
    return preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';
}

function cibo_slugify_value(string $value): string
{
    $normalized = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? '';
    return trim($slug, '-');
}

function cibo_order_debug_log(string $message, array $context = []): void
{
    $debugEnabled = filter_var(
        cibo_setting('CIBO_ENABLE_ORDER_DEBUG', '0'),
        FILTER_VALIDATE_BOOLEAN
    );

    if (!$debugEnabled) {
        return;
    }

    $suffix = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
    error_log('[Cibo Orders] ' . $message . $suffix);
}

function cibo_execute_statement_or_fail(mysqli_stmt $statement, string $message): void
{
    if (!$statement->execute()) {
        throw new RuntimeException($message);
    }
}

function cibo_max_cart_subtotal(): float
{
    return 10000.0;
}

function cibo_max_item_quantity_per_order(): int
{
    return 5;
}

function cibo_max_total_items_per_order(): int
{
    return 15;
}

function cibo_threshold_discount_rate(float $subtotal): float
{
    if ($subtotal >= 2000.0) {
        return 0.10;
    }

    if ($subtotal >= 1000.0) {
        return 0.05;
    }

    return 0.0;
}

function cibo_normalize_promo_code(string $promoCode): string
{
    return strtoupper(trim($promoCode));
}

function cibo_is_first_time_customer(?mysqli $db = null): bool
{
    cibo_start_user_session();

    $currentUser = cibo_current_user();
    $userId = (int) ($currentUser['id'] ?? 0);

    if ($userId > 0 && $db instanceof mysqli) {
        $statement = $db->prepare('SELECT COUNT(*) AS order_count FROM orders WHERE user_id = ?');

        if ($statement instanceof mysqli_stmt) {
            $statement->bind_param('i', $userId);
            $statement->execute();
            $result = $statement->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $statement->close();

            return (int) ($row['order_count'] ?? 0) === 0;
        }

        return false;
    }

    $guestOrders = $_SESSION['session_order_numbers'] ?? [];
    $safeGuestOrders = is_array($guestOrders) ? $guestOrders : [];

    return count($safeGuestOrders) === 0;
}

function cibo_resolve_discount_details(float $subtotal, string $promoCode = '', ?mysqli $db = null): array
{
    $normalizedPromoCode = cibo_normalize_promo_code($promoCode);
    $autoDiscountRate = cibo_threshold_discount_rate($subtotal);
    $autoDiscountAmount = round($subtotal * $autoDiscountRate, 2);
    $isFirstTimeCustomer = cibo_is_first_time_customer($db);

    $baseDiscount = [
        'discount_type' => $autoDiscountAmount > 0 ? 'auto' : 'none',
        'discount_label' => $autoDiscountAmount > 0
            ? 'Auto Discount (' . (int) round($autoDiscountRate * 100) . '%)'
            : 'Discount',
        'discount_rate' => round($autoDiscountRate, 4),
        'discount_amount' => round($autoDiscountAmount, 2),
        'promo_code' => $normalizedPromoCode,
        'promo_status' => $normalizedPromoCode !== '' ? 'invalid' : 'none',
        'promo_message' => '',
        'promo_applied' => false,
    ];

    if ($normalizedPromoCode === '') {
        return $baseDiscount;
    }

    if ($normalizedPromoCode === 'CIBO50') {
        if (!$isFirstTimeCustomer) {
            $baseDiscount['promo_status'] = 'ineligible';
            $baseDiscount['promo_message'] = 'Only for first-time users';
            return $baseDiscount;
        }

        return [
            'discount_type' => 'promo',
            'discount_label' => 'Promo Discount (CIBO50)',
            'discount_rate' => 0.0,
            'discount_amount' => round(min(50.0, $subtotal), 2),
            'promo_code' => $normalizedPromoCode,
            'promo_status' => 'applied',
            'promo_message' => 'CIBO50 applied successfully',
            'promo_applied' => true,
        ];
    }

    if ($normalizedPromoCode === 'CIBO100') {
        if ($subtotal <= 500.0) {
            $baseDiscount['promo_status'] = 'ineligible';
            $baseDiscount['promo_message'] = 'Order must be above ₹500';
            return $baseDiscount;
        }

        return [
            'discount_type' => 'promo',
            'discount_label' => 'Promo Discount (CIBO100)',
            'discount_rate' => 0.0,
            'discount_amount' => round(min(100.0, $subtotal), 2),
            'promo_code' => $normalizedPromoCode,
            'promo_status' => 'applied',
            'promo_message' => 'CIBO100 applied successfully',
            'promo_applied' => true,
        ];
    }

    if ($normalizedPromoCode === 'CIBO5') {
        if ($subtotal <= 1000.0) {
            $baseDiscount['promo_status'] = 'ineligible';
            $baseDiscount['promo_message'] = 'Order must be above Rs1000';
            return $baseDiscount;
        }

        return [
            'discount_type' => 'promo',
            'discount_label' => 'Promo Discount (CIBO5)',
            'discount_rate' => 0.05,
            'discount_amount' => round($subtotal * 0.05, 2),
            'promo_code' => $normalizedPromoCode,
            'promo_status' => 'applied',
            'promo_message' => 'CIBO5 applied successfully',
            'promo_applied' => true,
        ];
    }

    if ($normalizedPromoCode === 'CIBO10') {
        if ($subtotal <= 2000.0) {
            $baseDiscount['promo_status'] = 'ineligible';
            $baseDiscount['promo_message'] = 'Order must be above Rs2000';
            return $baseDiscount;
        }

        return [
            'discount_type' => 'promo',
            'discount_label' => 'Promo Discount (CIBO10)',
            'discount_rate' => 0.10,
            'discount_amount' => round($subtotal * 0.10, 2),
            'promo_code' => $normalizedPromoCode,
            'promo_status' => 'applied',
            'promo_message' => 'CIBO10 applied successfully',
            'promo_applied' => true,
        ];
    }

    $baseDiscount['promo_status'] = 'invalid';
    $baseDiscount['promo_message'] = 'Invalid promo code';
    return $baseDiscount;
}

function cibo_validate_customer_details(array $customer): array
{
    $customerName = trim((string) ($customer['name'] ?? ''));
    $customerPhone = preg_replace('/\D+/', '', (string) ($customer['phone'] ?? '')) ?? '';
    $addressLine = trim((string) ($customer['address'] ?? ''));
    $city = trim((string) ($customer['city'] ?? ''));
    $pincode = preg_replace('/\D+/', '', (string) ($customer['pincode'] ?? '')) ?? '';

    if ($customerName === '' || $customerPhone === '' || $addressLine === '' || $city === '' || $pincode === '') {
        throw new RuntimeException('Customer and delivery details are required.');
    }

    if (strlen($customerPhone) !== 10) {
        throw new RuntimeException('Phone number must be 10 digits.');
    }

    if (strlen($addressLine) < 10) {
        throw new RuntimeException('Please enter a complete delivery address.');
    }

    if (!preg_match('/^[a-zA-Z][a-zA-Z\s.-]{1,}$/', $city)) {
        throw new RuntimeException('Please enter a valid city name.');
    }

    if (strlen($pincode) !== 6) {
        throw new RuntimeException('Pincode must be 6 digits.');
    }

    return [
        'name' => $customerName,
        'phone' => $customerPhone,
        'address' => $addressLine,
        'city' => $city,
        'pincode' => $pincode,
    ];
}

function cibo_normalize_order_items_payload(array $items): array
{
    $normalizedItems = [];
    $totalQuantity = 0;

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $itemName = trim((string) ($item['name'] ?? ''));
        $quantity = (int) ($item['quantity'] ?? 0);

        if ($itemName === '') {
            throw new RuntimeException('Each order item must include a valid name.');
        }

        if ($quantity <= 0) {
            throw new RuntimeException('Each cart item must have a valid quantity.');
        }

        if ($quantity > cibo_max_item_quantity_per_order()) {
            throw new RuntimeException('Max ' . cibo_max_item_quantity_per_order() . ' per item allowed.');
        }

        $totalQuantity += $quantity;

        $normalizedItems[] = [
            'id' => (string) ($item['id'] ?? ''),
            'restaurant_id' => (string) ($item['restaurantId'] ?? $item['restaurant_id'] ?? ''),
            'name' => $itemName,
            'slug' => trim((string) ($item['slug'] ?? $item['itemSlug'] ?? '')),
            'price' => round((float) ($item['price'] ?? 0), 2),
            'quantity' => $quantity,
            'restaurant' => trim((string) ($item['restaurant'] ?? '')),
            'restaurant_slug' => trim((string) ($item['restaurantSlug'] ?? '')),
            'restaurant_page' => trim((string) ($item['restaurantPage'] ?? '')),
            'description' => trim((string) ($item['description'] ?? '')),
            'food_type' => trim((string) ($item['food_type'] ?? $item['foodType'] ?? '')),
            'image' => trim((string) ($item['image'] ?? '')),
            'image_path' => trim((string) ($item['image_path'] ?? '')),
        ];
    }

    if (!$normalizedItems) {
        throw new RuntimeException('No order items were provided.');
    }

    if ($totalQuantity > cibo_max_total_items_per_order()) {
        throw new RuntimeException('Order limit reached (Max ' . cibo_max_total_items_per_order() . ' items per order).');
    }

    return $normalizedItems;
}

function cibo_collect_restaurant_context(array $restaurantContext, array $items): array
{
    $contexts = [];
    $pushContext = static function (array $context) use (&$contexts): void {
        $normalizedCandidates = cibo_restaurant_lookup_candidates($context);

        if (!$normalizedCandidates) {
            return;
        }

        $contexts[] = [
            'id' => trim((string) ($context['id'] ?? $context['restaurant_id'] ?? '')),
            'name' => trim((string) ($context['name'] ?? '')),
            'slug' => trim((string) ($context['slug'] ?? '')),
            'page' => trim((string) ($context['page'] ?? '')),
            'candidates' => $normalizedCandidates,
        ];
    };

    $pushContext($restaurantContext);

    foreach ($items as $item) {
        $pushContext([
            'id' => (string) ($item['restaurant_id'] ?? ''),
            'name' => (string) ($item['restaurant'] ?? ''),
            'slug' => (string) ($item['restaurant_slug'] ?? ''),
            'page' => (string) ($item['restaurant_page'] ?? ''),
        ]);
    }

    if (!$contexts) {
        throw new RuntimeException('Restaurant information is missing for this order.');
    }

    $primaryContext = array_shift($contexts);

    foreach ($contexts as $context) {
        if (array_intersect($primaryContext['candidates'], $context['candidates'])) {
            if ($primaryContext['name'] === '' && $context['name'] !== '') {
                $primaryContext['name'] = $context['name'];
            }

            if ($primaryContext['slug'] === '' && $context['slug'] !== '') {
                $primaryContext['slug'] = $context['slug'];
            }

            if ($primaryContext['page'] === '' && $context['page'] !== '') {
                $primaryContext['page'] = $context['page'];
            }

            $primaryContext['candidates'] = array_values(array_unique(array_merge($primaryContext['candidates'], $context['candidates'])));
            continue;
        }

        throw new RuntimeException('Cart items from multiple restaurants cannot be ordered together.');
    }

    return [
        'id' => $primaryContext['id'],
        'name' => $primaryContext['name'],
        'slug' => $primaryContext['slug'],
        'page' => $primaryContext['page'],
    ];
}

function cibo_calculate_order_summary(array $items, array $options = []): array
{
    $subtotal = 0.0;

    foreach ($items as $item) {
        $subtotal += (float) ($item['line_total'] ?? 0);
    }

    if ($subtotal > cibo_max_cart_subtotal()) {
        throw new RuntimeException('Cart total exceeds the allowed limit for a single order.');
    }

    $deliveryFee = $subtotal >= 199 ? 0.0 : 40.0;
    $discountDetails = cibo_resolve_discount_details(
        $subtotal,
        (string) ($options['promo_code'] ?? ''),
        $options['db'] ?? null
    );
    $discountRate = (float) ($discountDetails['discount_rate'] ?? 0);
    $discountAmount = (float) ($discountDetails['discount_amount'] ?? 0);
    $taxableAmount = max(0.0, $subtotal - $discountAmount);
    $taxAmount = round($taxableAmount * 0.05, 2);

    $totalAmount = max(0.0, $taxableAmount + $taxAmount + $deliveryFee);

    return [
        'subtotal' => round($subtotal, 2),
        'delivery_fee' => round($deliveryFee, 2),
        'tax_amount' => round($taxAmount, 2),
        'discount_rate' => round($discountRate, 4),
        'discount_amount' => round($discountAmount, 2),
        'discount_type' => (string) ($discountDetails['discount_type'] ?? 'none'),
        'discount_label' => (string) ($discountDetails['discount_label'] ?? 'Discount'),
        'promo_code' => (string) ($discountDetails['promo_code'] ?? ''),
        'promo_status' => (string) ($discountDetails['promo_status'] ?? 'none'),
        'promo_message' => (string) ($discountDetails['promo_message'] ?? ''),
        'promo_applied' => (bool) ($discountDetails['promo_applied'] ?? false),
        'is_free_delivery' => $deliveryFee <= 0.0,
        'tax_label' => 'GST (5%)',
        'total_amount' => round($totalAmount, 2),
    ];
}

function cibo_calculate_order_summary_from_payload(array $payload): array
{
    $resolvedCheckout = cibo_resolve_checkout_payload($payload);
    return $resolvedCheckout['summary'];
}

function cibo_resolve_checkout_payload(array $payload): array
{
    $db = cibo_app_db();

    if (!$db instanceof mysqli) {
        throw new RuntimeException('Unable to connect to the database.');
    }

    $items = cibo_normalize_order_items_payload(is_array($payload['items'] ?? null) ? $payload['items'] : []);
    $restaurantContext = $payload['restaurant'] ?? [];

    if (is_string($restaurantContext) || !is_array($restaurantContext)) {
        $restaurantContext = [
            'name' => (string) $restaurantContext,
            'slug' => (string) ($payload['restaurant_slug'] ?? ''),
            'page' => (string) ($payload['restaurant_page'] ?? ''),
        ];
    }

    $restaurantContext = cibo_collect_restaurant_context($restaurantContext, $items);
    $restaurantId = cibo_find_restaurant_id($db, $restaurantContext);
    $resolvedItems = cibo_resolve_order_items($db, $restaurantId, $items);
    $summary = cibo_calculate_order_summary($resolvedItems, [
        'promo_code' => (string) ($payload['promo_code'] ?? ''),
        'db' => $db,
    ]);

    return [
        'summary' => $summary,
        'cart_items' => array_map(static function (array $item) use ($restaurantId, $restaurantContext): array {
            $restaurantSlug = trim((string) ($restaurantContext['slug'] ?? ''));
            $restaurantName = trim((string) ($restaurantContext['name'] ?? '')) ?: 'Cibo Order';
            $restaurantPage = $restaurantSlug !== ''
                ? 'menu.php?restaurant=' . rawurlencode($restaurantSlug)
                : trim((string) ($restaurantContext['page'] ?? ''));

            return [
                'id' => 'menu-item-' . (int) ($item['menu_item_id'] ?? 0),
                'menuItemId' => (int) ($item['menu_item_id'] ?? 0),
                'restaurantId' => $restaurantId,
                'restaurant' => $restaurantName,
                'restaurantSlug' => $restaurantSlug,
                'restaurantPage' => $restaurantPage,
                'name' => (string) ($item['name'] ?? 'Item'),
                'slug' => cibo_slugify_value((string) ($item['name'] ?? 'item')),
                'price' => round((float) ($item['price'] ?? 0), 2),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'line_total' => round((float) ($item['line_total'] ?? 0), 2),
                'image' => trim((string) ($item['image'] ?? '')),
            ];
        }, $resolvedItems),
    ];
}

function cibo_order_store_guest_reference(string $orderNumber): void
{
    cibo_start_user_session();

    $orderNumbers = $_SESSION['session_order_numbers'] ?? [];
    $safeOrderNumbers = is_array($orderNumbers) ? $orderNumbers : [];

    if (!in_array($orderNumber, $safeOrderNumbers, true)) {
        $safeOrderNumbers[] = $orderNumber;
    }

    $_SESSION['session_order_numbers'] = array_values($safeOrderNumbers);
    $_SESSION['last_order_number'] = $orderNumber;
}

function cibo_order_table_columns(mysqli $db): array
{
    static $columns = null;

    if (is_array($columns)) {
        return $columns;
    }

    $columns = [];
    $result = $db->query('SHOW COLUMNS FROM orders');

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $field = strtolower((string) ($row['Field'] ?? ''));
            if ($field !== '') {
                $columns[$field] = true;
            }
        }
        $result->free();
    }

    return $columns;
}

function cibo_order_column_exists(mysqli $db, string $column): bool
{
    $columns = cibo_order_table_columns($db);
    return !empty($columns[strtolower($column)]);
}

function cibo_restaurant_lookup_candidates(array|string $restaurant): array
{
    if (is_string($restaurant)) {
        $restaurant = ['name' => $restaurant];
    }

    $rawCandidates = [];
    $name = trim((string) ($restaurant['name'] ?? ''));
    $slug = trim((string) ($restaurant['slug'] ?? ''));
    $page = trim((string) ($restaurant['page'] ?? ''));

    if ($name !== '') {
        $rawCandidates[] = $name;
        $rawCandidates[] = cibo_slugify_value($name);
    }

    if ($slug !== '') {
        $rawCandidates[] = $slug;
        $rawCandidates[] = str_replace('-', ' ', $slug);
    }

    if ($page !== '') {
        $pageBasename = strtolower(pathinfo($page, PATHINFO_FILENAME));
        if ($pageBasename !== '') {
            $rawCandidates[] = $pageBasename;
            $rawCandidates[] = str_replace('-', ' ', $pageBasename);
        }
    }

    $candidates = [];

    foreach ($rawCandidates as $candidate) {
        $candidate = trim((string) $candidate);

        if ($candidate === '') {
            continue;
        }

        $normalizedCandidate = cibo_normalize_lookup_value($candidate);

        if ($normalizedCandidate === '') {
            continue;
        }

        $candidates[$normalizedCandidate] = $candidate;
    }

    return array_keys($candidates);
}

function cibo_find_restaurant_id(mysqli $db, array|string $restaurant): int
{
    if (is_array($restaurant)) {
        $explicitRestaurantId = (int) ($restaurant['id'] ?? $restaurant['restaurant_id'] ?? 0);

        if ($explicitRestaurantId > 0) {
            $statement = $db->prepare('SELECT id FROM restaurants WHERE id = ? LIMIT 1');

            if ($statement instanceof mysqli_stmt) {
                $statement->bind_param('i', $explicitRestaurantId);
                $statement->execute();
                $record = $statement->get_result()?->fetch_assoc();
                $statement->close();

                if ($record) {
                    cibo_order_debug_log('Resolved restaurant by explicit id.', [
                        'restaurant_id' => $explicitRestaurantId,
                    ]);
                    return $explicitRestaurantId;
                }
            }
        }
    }

    $candidates = cibo_restaurant_lookup_candidates($restaurant);

    if (!$candidates) {
        throw new RuntimeException('Restaurant information is missing for this order.');
    }

    $result = $db->query('SELECT id, name, slug FROM restaurants ORDER BY id ASC');

    if (!$result instanceof mysqli_result) {
        throw new RuntimeException('Unable to resolve the restaurant.');
    }

    $matchedRestaurantId = 0;
    $bestScore = -1;

    while ($row = $result->fetch_assoc()) {
        $restaurantId = (int) ($row['id'] ?? 0);
        $normalizedName = cibo_normalize_lookup_value((string) ($row['name'] ?? ''));
        $normalizedSlug = cibo_normalize_lookup_value((string) ($row['slug'] ?? ''));

        foreach ($candidates as $candidate) {
            $score = 0;

            if ($candidate === $normalizedSlug) {
                $score = 120;
            } elseif ($candidate === $normalizedName) {
                $score = 110;
            } elseif ($normalizedSlug !== '' && (str_contains($candidate, $normalizedSlug) || str_contains($normalizedSlug, $candidate))) {
                $score = 80;
            } elseif ($normalizedName !== '' && (str_contains($candidate, $normalizedName) || str_contains($normalizedName, $candidate))) {
                $score = 70;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $matchedRestaurantId = $restaurantId;
            }
        }
    }

    $result->free();

    if ($matchedRestaurantId <= 0) {
        cibo_order_debug_log('Restaurant lookup failed before auto-create.', [
            'restaurant' => $restaurant,
            'candidates' => $candidates,
        ]);

        if (is_array($restaurant)) {
            $restaurantName = trim((string) ($restaurant['name'] ?? ''));
            $restaurantSlug = cibo_slugify_value((string) ($restaurant['slug'] ?? ''));
            $restaurantPage = trim((string) ($restaurant['page'] ?? ''));

            if ($restaurantName === '' && $restaurantPage !== '') {
                $restaurantName = ucwords(str_replace(['-', '_'], ' ', strtolower(pathinfo($restaurantPage, PATHINFO_FILENAME))));
            }

            if ($restaurantSlug === '' && $restaurantName !== '') {
                $restaurantSlug = cibo_slugify_value($restaurantName);
            }

            if ($restaurantName !== '' && $restaurantSlug !== '') {
                $defaultCategory = 'general';
                $defaultLocation = 'Bangalore';
                $insertStatement = $db->prepare('
                    INSERT INTO restaurants (name, slug, category, cuisine, location, is_active)
                    VALUES (?, ?, ?, ?, ?, 1)
                ');

                if ($insertStatement instanceof mysqli_stmt) {
                    $insertStatement->bind_param('sssss', $restaurantName, $restaurantSlug, $defaultCategory, $defaultCategory, $defaultLocation);

                    if ($insertStatement->execute()) {
                        $createdRestaurantId = (int) $insertStatement->insert_id;
                        $insertStatement->close();

                        if ($createdRestaurantId > 0) {
                            cibo_order_debug_log('Created restaurant from checkout payload.', [
                                'restaurant_id' => $createdRestaurantId,
                                'name' => $restaurantName,
                                'slug' => $restaurantSlug,
                            ]);
                            return $createdRestaurantId;
                        }
                    } else {
                        cibo_order_debug_log('Restaurant auto-create failed.', [
                            'name' => $restaurantName,
                            'slug' => $restaurantSlug,
                            'error' => $insertStatement->error,
                        ]);
                    }

                    $insertStatement->close();
                }
            }
        }

        throw new RuntimeException('Unable to match this order to a restaurant in the database.');
    }

    cibo_order_debug_log('Resolved restaurant by candidate match.', [
        'restaurant_id' => $matchedRestaurantId,
        'restaurant' => $restaurant,
        'candidates' => $candidates,
        'score' => $bestScore,
    ]);

    return $matchedRestaurantId;
}

function cibo_find_menu_item_match(mysqli $db, int $restaurantId, array $item): ?array
{
    if ($restaurantId <= 0) {
        return null;
    }

    static $menuItemsByRestaurant = [];

    if (!array_key_exists($restaurantId, $menuItemsByRestaurant)) {
        $statement = $db->prepare('
            SELECT id, name, slug, price, is_available, image
            FROM menu_items
            WHERE restaurant_id = ?
        ');

        if (!$statement) {
            $menuItemsByRestaurant[$restaurantId] = [];
        } else {
            $statement->bind_param('i', $restaurantId);
            $statement->execute();
            $menuItemsByRestaurant[$restaurantId] = $statement->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
            $statement->close();
        }
    }

    $itemName = trim((string) ($item['name'] ?? ''));
    $itemSlug = trim((string) ($item['slug'] ?? ''));
    $nameCandidate = cibo_normalize_lookup_value($itemName);
    $slugCandidate = cibo_normalize_lookup_value($itemSlug !== '' ? $itemSlug : cibo_slugify_value($itemName));

    foreach ($menuItemsByRestaurant[$restaurantId] as $menuItem) {
        $normalizedMenuName = cibo_normalize_lookup_value((string) ($menuItem['name'] ?? ''));
        $normalizedMenuSlug = cibo_normalize_lookup_value((string) ($menuItem['slug'] ?? ''));

        if (
            ($slugCandidate !== '' && ($slugCandidate === $normalizedMenuSlug || $slugCandidate === $normalizedMenuName))
            || ($nameCandidate !== '' && ($nameCandidate === $normalizedMenuName || $nameCandidate === $normalizedMenuSlug))
        ) {
            return $menuItem;
        }
    }

    return null;
}

function cibo_ensure_menu_item_for_order(mysqli $db, int $restaurantId, array $item): ?array
{
    if ($restaurantId <= 0) {
        return null;
    }

    $itemName = trim((string) ($item['name'] ?? ''));
    $itemPrice = (float) ($item['price'] ?? 0);

    if ($itemName === '' || $itemPrice <= 0) {
        return null;
    }

    $baseSlug = trim((string) ($item['slug'] ?? ''));
    $baseSlug = $baseSlug !== '' ? cibo_slugify_value($baseSlug) : cibo_slugify_value($itemName);
    $baseSlug = $baseSlug !== '' ? $baseSlug : 'item';
    $candidateSlug = $baseSlug;
    $counter = 2;

    while (true) {
        $lookupStatement = $db->prepare('
            SELECT id, name, slug, price
            FROM menu_items
            WHERE restaurant_id = ? AND slug = ?
            LIMIT 1
        ');

        if (!$lookupStatement) {
            break;
        }

        $lookupStatement->bind_param('is', $restaurantId, $candidateSlug);
        $lookupStatement->execute();
        $existing = $lookupStatement->get_result()?->fetch_assoc();
        $lookupStatement->close();

        if (!$existing) {
            break;
        }

        if (cibo_normalize_lookup_value((string) ($existing['name'] ?? '')) === cibo_normalize_lookup_value($itemName)) {
            return $existing;
        }

        $candidateSlug = $baseSlug . '-' . $counter;
        $counter++;
    }

    $description = trim((string) ($item['description'] ?? ''));
    $foodType = strtolower(trim((string) ($item['food_type'] ?? 'none')));
    $allowedFoodTypes = ['veg', 'nonveg', 'egg', 'none'];
    if (!in_array($foodType, $allowedFoodTypes, true)) {
        $foodType = 'none';
    }

    $imagePath = trim((string) ($item['image_path'] ?? $item['image'] ?? ''));
    $insertStatement = $db->prepare('
        INSERT INTO menu_items (restaurant_id, name, slug, description, price, food_type, image, is_available)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ');

    if (!$insertStatement) {
        return null;
    }

    $insertStatement->bind_param('isssdss', $restaurantId, $itemName, $candidateSlug, $description, $itemPrice, $foodType, $imagePath);

    if (!$insertStatement->execute()) {
        $insertStatement->close();
        return null;
    }

    $menuItemId = (int) $insertStatement->insert_id;
    $insertStatement->close();

    if ($menuItemId <= 0) {
        return null;
    }

    return [
        'id' => $menuItemId,
        'name' => $itemName,
        'slug' => $candidateSlug,
        'price' => $itemPrice,
        'image' => $imagePath,
    ];
}

function cibo_restaurant_has_menu_items(mysqli $db, int $restaurantId): bool
{
    static $cache = [];

    if (array_key_exists($restaurantId, $cache)) {
        return $cache[$restaurantId];
    }

    $statement = $db->prepare('SELECT COUNT(*) AS total FROM menu_items WHERE restaurant_id = ?');

    if (!$statement) {
        $cache[$restaurantId] = false;
        return false;
    }

    $statement->bind_param('i', $restaurantId);
    $statement->execute();
    $row = $statement->get_result()?->fetch_assoc() ?? [];
    $statement->close();

    $cache[$restaurantId] = (int) ($row['total'] ?? 0) > 0;
    return $cache[$restaurantId];
}

function cibo_resolve_order_items(mysqli $db, int $restaurantId, array $items): array
{
    $resolvedItems = [];
    $restaurantHasCatalog = cibo_restaurant_has_menu_items($db, $restaurantId);
    cibo_order_debug_log('Resolving order items for restaurant.', [
        'restaurant_id' => $restaurantId,
        'restaurant_has_catalog' => $restaurantHasCatalog,
        'items_count' => count($items),
    ]);

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $itemName = trim((string) ($item['name'] ?? ''));
        $quantity = (int) ($item['quantity'] ?? 0);

        if ($itemName === '') {
            throw new RuntimeException('Each order item must include a valid name.');
        }

        if ($quantity <= 0) {
            throw new RuntimeException('Each cart item must have a valid quantity.');
        }

        $matchedMenuItem = cibo_find_menu_item_match($db, $restaurantId, $item);

        if ($matchedMenuItem && !(bool) ($matchedMenuItem['is_available'] ?? true)) {
            throw new RuntimeException('One or more cart items are currently unavailable.');
        }

        if (!$matchedMenuItem) {
            if ($restaurantHasCatalog) {
                throw new RuntimeException('One or more cart items could not be validated against the restaurant menu.');
            }

            $matchedMenuItem = cibo_ensure_menu_item_for_order($db, $restaurantId, $item);

            if (!$matchedMenuItem) {
                throw new RuntimeException('Unable to link one or more order items to the restaurant menu.');
            }
        }

        $itemPrice = $matchedMenuItem
            ? (float) ($matchedMenuItem['price'] ?? 0)
            : (float) ($item['price'] ?? 0);

        if ($itemPrice <= 0) {
            throw new RuntimeException('Unable to determine a valid price for one or more order items.');
        }

        $resolvedItems[] = [
            'menu_item_id' => $matchedMenuItem ? (int) ($matchedMenuItem['id'] ?? 0) : null,
            'name' => $matchedMenuItem ? (string) ($matchedMenuItem['name'] ?? $itemName) : $itemName,
            'price' => round($itemPrice, 2),
            'quantity' => $quantity,
            'line_total' => round($itemPrice * $quantity, 2),
            'image' => trim((string) ($matchedMenuItem['image'] ?? $item['image_path'] ?? $item['image'] ?? '')),
        ];
    }

    if (!$resolvedItems) {
        throw new RuntimeException('No order items were provided.');
    }

    return $resolvedItems;
}

function cibo_fetch_orders_by_filters(?int $userId = null, array $orderNumbers = [], bool $allowAll = false): array
{
    $db = cibo_app_db();

    if (!$db) {
        return [];
    }

    cibo_demo_progress_orders($db);
    $placedAtSelect = cibo_order_column_exists($db, 'placed_at')
        ? 'o.placed_at'
        : 'o.created_at AS placed_at';
    $deliveredAtSelect = cibo_order_column_exists($db, 'delivered_at')
        ? 'o.delivered_at'
        : 'NULL AS delivered_at';

    $conditions = [];
    $types = '';
    $params = [];

    if ($userId !== null && $userId > 0) {
        $conditions[] = 'o.user_id = ?';
        $types .= 'i';
        $params[] = $userId;
    }

    $orderNumbers = array_values(array_filter(array_map('strval', $orderNumbers), static fn ($value) => trim($value) !== ''));

    if ($orderNumbers) {
        $placeholders = implode(', ', array_fill(0, count($orderNumbers), '?'));
        $conditions[] = "o.order_number IN ({$placeholders})";
        $types .= str_repeat('s', count($orderNumbers));
        array_push($params, ...$orderNumbers);
    }

    if (!$conditions && !$allowAll) {
        return [];
    }

    if (!$conditions) {
        $query = "
            SELECT
                o.id,
                o.restaurant_id,
                o.order_number,
                o.customer_name,
                o.customer_phone,
                o.delivery_address,
                o.payment_method,
                o.payment_status,
                o.order_status,
                o.total_amount AS subtotal,
                o.delivery_fee,
                o.tax AS tax_amount,
                o.discount AS discount_amount,
                o.final_amount AS total_amount,
                o.created_at,
                {$placedAtSelect},
                {$deliveredAtSelect},
                r.name AS restaurant_name
            FROM orders o
            INNER JOIN restaurants r ON r.id = o.restaurant_id
            ORDER BY o.created_at DESC, o.id DESC
        ";
        $result = $db->query($query);
    } else {
        $query = "
            SELECT
                o.id,
                o.restaurant_id,
                o.order_number,
                o.customer_name,
                o.customer_phone,
                o.delivery_address,
                o.payment_method,
                o.payment_status,
                o.order_status,
                o.total_amount AS subtotal,
                o.delivery_fee,
                o.tax AS tax_amount,
                o.discount AS discount_amount,
                o.final_amount AS total_amount,
                o.created_at,
                {$placedAtSelect},
                {$deliveredAtSelect},
                r.name AS restaurant_name
            FROM orders o
            INNER JOIN restaurants r ON r.id = o.restaurant_id
            WHERE " . implode(' OR ', $conditions) . "
            ORDER BY o.created_at DESC, o.id DESC
        ";
        $statement = $db->prepare($query);

        if (!$statement) {
            return [];
        }

        $statement->bind_param($types, ...$params);
        $statement->execute();
        $result = $statement->get_result();
    }

    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    if (isset($statement) && $statement instanceof mysqli_stmt) {
        $statement->close();
    }

    if (!$orders) {
        return [];
    }

    $orderIds = array_map(static fn (array $order): int => (int) $order['id'], $orders);
    $itemPlaceholders = implode(', ', array_fill(0, count($orderIds), '?'));
    $itemTypes = str_repeat('i', count($orderIds));
    $itemStatement = $db->prepare("
        SELECT
            oi.order_id,
            oi.menu_item_id,
            oi.item_name,
            oi.price AS item_price,
            oi.quantity,
            oi.line_total,
            mi.image
        FROM order_items oi
        LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id
        WHERE oi.order_id IN ({$itemPlaceholders})
        ORDER BY oi.id ASC
    ");

    $itemsByOrderId = [];
    $receiptsByOrderId = [];

    if ($itemStatement) {
        $itemStatement->bind_param($itemTypes, ...$orderIds);
        $itemStatement->execute();
        $itemRows = $itemStatement->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
        $itemStatement->close();

        foreach ($itemRows as $itemRow) {
            $itemsByOrderId[(int) $itemRow['order_id']][] = [
                'menu_item_id' => isset($itemRow['menu_item_id']) ? (int) $itemRow['menu_item_id'] : null,
                'name' => (string) $itemRow['item_name'],
                'price' => (float) $itemRow['item_price'],
                'quantity' => (int) $itemRow['quantity'],
                'line_total' => (float) $itemRow['line_total'],
                'image' => trim((string) ($itemRow['image'] ?? '')),
            ];
        }
    }

    $receiptPlaceholders = implode(', ', array_fill(0, count($orderIds), '?'));
    $receiptTypes = str_repeat('i', count($orderIds));
    $receiptStatement = $db->prepare("
        SELECT order_id, receipt_number, generated_at
        FROM receipts
        WHERE order_id IN ({$receiptPlaceholders})
    ");

    if ($receiptStatement) {
        $receiptStatement->bind_param($receiptTypes, ...$orderIds);
        $receiptStatement->execute();
        $receiptRows = $receiptStatement->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
        $receiptStatement->close();

        foreach ($receiptRows as $receiptRow) {
            $receiptsByOrderId[(int) $receiptRow['order_id']] = [
                'receipt_number' => (string) ($receiptRow['receipt_number'] ?? ''),
                'generated_at' => (string) ($receiptRow['generated_at'] ?? ''),
            ];
        }
    }

    return array_map(static function (array $order) use ($itemsByOrderId, $receiptsByOrderId): array {
        $status = cibo_normalize_order_status((string) ($order['order_status'] ?? 'placed'));
        $orderId = (int) $order['id'];
        $receiptRecord = $receiptsByOrderId[$orderId] ?? null;

        $mappedOrder = [
            'id' => $orderId,
            'order_number' => (string) $order['order_number'],
            'restaurant_id' => isset($order['restaurant_id']) ? (int) $order['restaurant_id'] : 0,
            'user_name' => (string) $order['customer_name'],
            'customer_phone' => (string) $order['customer_phone'],
            'delivery_address' => (string) $order['delivery_address'],
            'payment_method' => (string) $order['payment_method'],
            'payment_status' => (string) $order['payment_status'],
            'payment_status_label' => cibo_payment_status_label(
                (string) $order['payment_status'],
                (string) $order['payment_method']
            ),
            'order_status' => $status,
            'order_status_label' => cibo_order_status_label($status),
            'available_order_statuses' => cibo_order_available_status_options($status),
            'allowed_next_order_statuses' => cibo_order_next_statuses($status),
            'is_order_status_locked' => cibo_order_status_is_final($status),
            'restaurant_name' => (string) ($order['restaurant_name'] ?? 'Cibo Order'),
            'subtotal' => (float) $order['subtotal'],
            'delivery_fee' => (float) $order['delivery_fee'],
            'tax_amount' => (float) $order['tax_amount'],
            'discount_amount' => (float) $order['discount_amount'],
            'total_amount' => (float) $order['total_amount'],
            'created_at' => $order['created_at'],
            'placed_at' => $order['placed_at'] ?? $order['created_at'],
            'delivered_at' => $order['delivered_at'] ?? null,
            'items' => $itemsByOrderId[$orderId] ?? [],
            'receipt_number' => (string) ($receiptRecord['receipt_number'] ?? ''),
            'receipt_generated_at' => (string) ($receiptRecord['generated_at'] ?? ''),
        ];

        $mappedOrder['receipt_view_url'] = cibo_receipt_url_for_order($mappedOrder, $receiptRecord);
        $mappedOrder['receipt_download_url'] = cibo_receipt_url_for_order($mappedOrder, $receiptRecord, 'pdf');

        return $mappedOrder;
    }, $orders);
}

function cibo_fetch_all_orders(): array
{
    return cibo_fetch_orders_by_filters(null, [], true);
}

function cibo_fetch_orders_for_current_session(): array
{
    cibo_start_user_session();

    $currentUser = cibo_current_user();
    $guestOrders = $_SESSION['session_order_numbers'] ?? [];
    $safeGuestOrders = is_array($guestOrders) ? $guestOrders : [];

    return cibo_fetch_orders_by_filters(
        (int) ($currentUser['id'] ?? 0) ?: null,
        $safeGuestOrders
    );
}

function cibo_fetch_order_by_number_for_session(string $orderNumber): ?array
{
    $orderNumber = trim($orderNumber);

    if ($orderNumber === '') {
        return null;
    }

    $orders = cibo_fetch_orders_for_current_session();

    foreach ($orders as $order) {
        if ((string) ($order['order_number'] ?? '') === $orderNumber) {
            return $order;
        }
    }

    return null;
}

function cibo_fetch_order_by_number_public(string $orderNumber): ?array
{
    $orderNumber = trim($orderNumber);

    if ($orderNumber === '') {
        return null;
    }

    $orders = cibo_fetch_orders_by_filters(null, [$orderNumber]);

    foreach ($orders as $order) {
        if ((string) ($order['order_number'] ?? '') === $orderNumber) {
            return $order;
        }
    }

    return null;
}

function cibo_fetch_receipt_record_by_order_id(int $orderId): ?array
{
    if ($orderId <= 0) {
        return null;
    }

    $db = cibo_db();

    if (!$db) {
        return null;
    }

    $statement = $db->prepare('
        SELECT id, receipt_number, generated_at
        FROM receipts
        WHERE order_id = ?
        LIMIT 1
    ');

    if (!$statement) {
        return null;
    }

    $statement->bind_param('i', $orderId);
    $statement->execute();
    $record = $statement->get_result()?->fetch_assoc();
    $statement->close();

    return $record ?: null;
}

function cibo_fetch_receipt_context(string $orderNumber, string $token = ''): ?array
{
    cibo_start_user_session();

    $orderNumber = trim($orderNumber);

    if ($orderNumber === '') {
        return null;
    }

    $currentUser = cibo_current_user();
    $guestOrders = $_SESSION['session_order_numbers'] ?? [];
    $hasGuestSessionOrders = is_array($guestOrders) && $guestOrders !== [];
    $useSessionScope = (int) ($currentUser['id'] ?? 0) > 0 || $hasGuestSessionOrders;

    if ($useSessionScope) {
        $order = cibo_fetch_order_by_number_for_session($orderNumber);
    } else {
        $order = cibo_fetch_order_by_number_public($orderNumber);

        if (!$order) {
            return null;
        }

        $receiptRecord = cibo_fetch_receipt_record_by_order_id((int) ($order['id'] ?? 0));

        if (!cibo_receipt_token_is_valid($order, $receiptRecord, $token)) {
            return null;
        }
    }

    if (!$order) {
        return null;
    }

    $receiptRecord = cibo_fetch_receipt_record_by_order_id((int) ($order['id'] ?? 0));
    $subtotal = (float) ($order['subtotal'] ?? 0);
    $deliveryFee = (float) ($order['delivery_fee'] ?? 0);
    $taxAmount = (float) ($order['tax_amount'] ?? 0);
    $discountAmount = (float) ($order['discount_amount'] ?? 0);
    $totalAmount = (float) ($order['total_amount'] ?? 0);
    $netBeforeDiscount = $subtotal + $deliveryFee + $taxAmount;

    return [
        'order' => $order,
        'receipt' => [
            'receipt_number' => (string) ($receiptRecord['receipt_number'] ?? ('RCT-' . date('Ymd') . '-' . str_pad((string) ((int) ($order['id'] ?? 0)), 6, '0', STR_PAD_LEFT))),
            'generated_at' => (string) ($receiptRecord['generated_at'] ?? ($order['created_at'] ?? date('Y-m-d H:i:s'))),
            'token' => cibo_receipt_token_for_order($order, $receiptRecord),
        ],
        'summary' => [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'net_before_discount' => $netBeforeDiscount,
        ],
        'links' => [
            'view' => cibo_receipt_url_for_order($order, $receiptRecord),
            'download' => cibo_receipt_url_for_order($order, $receiptRecord, 'pdf'),
        ],
    ];
}

function cibo_order_is_customer_cancellable(array $order): bool
{
    return cibo_normalize_order_status((string) ($order['order_status'] ?? '')) === 'placed';
}

function cibo_cancel_order_for_session(string $orderNumber): array
{
    cibo_start_user_session();

    $orderNumber = trim($orderNumber);

    if ($orderNumber === '') {
        throw new RuntimeException('Unable to find the order.');
    }

    $order = cibo_fetch_order_by_number_for_session($orderNumber);

    if (!$order) {
        throw new RuntimeException('Unable to find the order.');
    }

    if (!cibo_order_is_customer_cancellable($order)) {
        throw new RuntimeException('Only newly placed orders can be cancelled.');
    }

    return cibo_update_order_status($orderNumber, 'cancelled');
}

function cibo_require_owned_address(mysqli $db, int $userId, int $addressId): array
{
    if ($userId <= 0) {
        throw new CiboHttpException('Please log in to use a saved delivery address.', 401);
    }

    if ($addressId <= 0) {
        throw new CiboHttpException('Please save and select a delivery address before placing your order.', 422);
    }

    $statement = $db->prepare('
        SELECT id, label, recipient_name, recipient_phone, address_line, landmark, city, state, pincode, is_default, created_at
        FROM addresses
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ');

    if (!$statement) {
        throw new CiboHttpException('Unable to validate the selected address.', 500);
    }

    $statement->bind_param('ii', $addressId, $userId);
    $statement->execute();
    $record = $statement->get_result()?->fetch_assoc();
    $statement->close();

    if (!$record) {
        throw new CiboHttpException('Please select a valid saved address before placing your order.', 422);
    }

    return cibo_address_from_record($record);
}

function cibo_normalize_address_value(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[.,-]+/', ' ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return trim($value);
}

function cibo_customer_matches_saved_address(array $customer, array $savedAddress): bool
{
    $customerAddress = cibo_normalize_address_value((string) ($customer['address'] ?? ''));
    $customerCity = cibo_normalize_address_value((string) ($customer['city'] ?? ''));
    $customerPincode = preg_replace('/\D+/', '', (string) ($customer['pincode'] ?? '')) ?? '';

    $savedAddressLine = cibo_normalize_address_value((string) ($savedAddress['address'] ?? $savedAddress['full_address'] ?? ''));
    $savedCity = cibo_normalize_address_value((string) ($savedAddress['city'] ?? ''));
    $savedPincode = preg_replace('/\D+/', '', (string) ($savedAddress['pincode'] ?? $savedAddress['postal_code'] ?? '')) ?? '';

    if ($customerAddress === '' || $savedAddressLine === '') {
        return false;
    }

    return $customerAddress === $savedAddressLine
        && $customerCity === $savedCity
        && $customerPincode === $savedPincode;
}

function cibo_create_order(array $payload): array
{
    cibo_start_user_session();

    $db = cibo_app_db();

    if (!$db) {
    throw new CiboHttpException('Order database is not ready yet. Please verify the cibo_db_v2 connection.', 500);
    }
    $items = cibo_normalize_order_items_payload(is_array($payload['items'] ?? null) ? $payload['items'] : []);
    $customer = cibo_validate_customer_details(is_array($payload['customer'] ?? null) ? $payload['customer'] : []);
    $currentUser = cibo_current_user();
    $userId = (int) ($currentUser['id'] ?? 0);
    $addressId = (int) ($payload['address_id'] ?? ($payload['customer']['address_id'] ?? 0));
    $savedAddress = null;

    if ($userId > 0 && $addressId > 0) {
        $savedAddress = cibo_require_owned_address($db, $userId, $addressId);

        if (!cibo_customer_matches_saved_address($customer, $savedAddress)) {
            $addressId = 0;
        }
    } else {
        $addressId = 0;
    }

    $customerName = $customer['name'];
    $customerPhone = $customer['phone'];
    $addressLine = $customer['address'];
    $city = $customer['city'];
    $pincode = $customer['pincode'];
    $paymentMethod = trim((string) ($payload['payment_method'] ?? 'cod'));

    $allowedPaymentMethods = ['cod', 'upi', 'card'];

    if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
        $paymentMethod = 'cod';
    }

    $restaurantContext = $payload['restaurant'] ?? [];

    if (is_string($restaurantContext) || !is_array($restaurantContext)) {
        $restaurantContext = [
            'name' => (string) $restaurantContext,
            'slug' => (string) ($payload['restaurant_slug'] ?? ''),
            'page' => (string) ($payload['restaurant_page'] ?? ''),
        ];
    }

    $restaurantContext = cibo_collect_restaurant_context($restaurantContext, $items);

    $restaurantId = cibo_find_restaurant_id($db, $restaurantContext);
    $resolvedItems = cibo_resolve_order_items($db, $restaurantId, $items);
    $calculatedSummary = cibo_calculate_order_summary($resolvedItems, [
        'promo_code' => (string) ($payload['promo_code'] ?? ''),
        'db' => $db,
    ]);
    $orderNumber = 'CB' . (string) round(microtime(true) * 1000);
    $subtotal = (float) $calculatedSummary['subtotal'];
    $deliveryFee = (float) $calculatedSummary['delivery_fee'];
    $taxAmount = (float) $calculatedSummary['tax_amount'];
    $discountAmount = (float) $calculatedSummary['discount_amount'];
    $totalAmount = (float) $calculatedSummary['total_amount'];
    $paymentStatus = 'pending';
    $deliveryAddress = implode(', ', array_filter([$addressLine, $city, $pincode], static fn ($value) => trim((string) $value) !== ''));

    $db->begin_transaction();

    try {
        $statement = $db->prepare('
            INSERT INTO orders (
                user_id,
                restaurant_id,
                address_id,
                order_number,
                customer_name,
                customer_phone,
                delivery_address,
                total_amount,
                delivery_fee,
                discount,
                tax,
                final_amount,
                payment_method,
                payment_status,
                order_status,
                placed_at
            ) VALUES (
                NULLIF(?, 0),
                ?,
                NULLIF(?, 0),
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                CURRENT_TIMESTAMP
            )
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to create the order.');
        }

        $normalizedPaymentMethod = strtoupper($paymentMethod);
        $normalizedOrderStatus = 'placed';
        $statement->bind_param(
            'iiissssdddddsss',
            $userId,
            $restaurantId,
            $addressId,
            $orderNumber,
            $customerName,
            $customerPhone,
            $deliveryAddress,
            $subtotal,
            $deliveryFee,
            $discountAmount,
            $taxAmount,
            $totalAmount,
            $normalizedPaymentMethod,
            $paymentStatus,
            $normalizedOrderStatus
        );
        cibo_execute_statement_or_fail($statement, 'Unable to create the order.');
        $orderId = (int) $statement->insert_id;
        $statement->close();

        $itemStatementWithMenuItem = $db->prepare('
            INSERT INTO order_items (order_id, menu_item_id, item_name, price, quantity, line_total)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $itemStatementWithoutMenuItem = $db->prepare('
            INSERT INTO order_items (order_id, menu_item_id, item_name, price, quantity, line_total)
            VALUES (?, NULL, ?, ?, ?, ?)
        ');

        if (!$itemStatementWithMenuItem || !$itemStatementWithoutMenuItem) {
            throw new RuntimeException('Unable to save the order items.');
        }

        foreach ($resolvedItems as $resolvedItem) {
            $menuItemId = $resolvedItem['menu_item_id'];
            $itemName = (string) $resolvedItem['name'];
            $itemPrice = (float) $resolvedItem['price'];
            $quantity = (int) $resolvedItem['quantity'];
            $lineTotal = (float) $resolvedItem['line_total'];

            if ((int) $menuItemId <= 0) {
                throw new RuntimeException('Unable to link one or more order items to the menu.');
            }

            if ($menuItemId) {
                $itemStatementWithMenuItem->bind_param('iisdid', $orderId, $menuItemId, $itemName, $itemPrice, $quantity, $lineTotal);
                cibo_execute_statement_or_fail($itemStatementWithMenuItem, 'Unable to save the order items.');
            } else {
                $itemStatementWithoutMenuItem->bind_param('isdid', $orderId, $itemName, $itemPrice, $quantity, $lineTotal);
                cibo_execute_statement_or_fail($itemStatementWithoutMenuItem, 'Unable to save the order items.');
            }
        }

        $itemStatementWithMenuItem->close();
        $itemStatementWithoutMenuItem->close();

        if ($paymentMethod !== 'cod') {
            $paymentUpdateStatement = $db->prepare('
                UPDATE orders
                SET payment_status = ?
                WHERE id = ?
            ');

            if (!$paymentUpdateStatement) {
                throw new RuntimeException('Unable to finalize the payment status.');
            }

            $paidStatus = 'paid';
            $paymentUpdateStatement->bind_param('si', $paidStatus, $orderId);
            cibo_execute_statement_or_fail($paymentUpdateStatement, 'Unable to finalize the payment status.');
            $paymentUpdateStatement->close();
        }

        if ($userId > 0) {
            cibo_sync_order_address_for_user($db, $userId, [
                'name' => $customerName,
                'phone' => $customerPhone,
                'address' => $addressLine,
                'city' => $city,
                'pincode' => $pincode,
            ]);
        }

        $receiptStatement = $db->prepare('
            INSERT INTO receipts (order_id, receipt_number)
            VALUES (?, ?)
        ');

        if (!$receiptStatement) {
            throw new RuntimeException('Unable to generate the receipt record.');
        }

        $receiptNumber = 'RCT-' . date('Ymd') . '-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
        $receiptStatement->bind_param('is', $orderId, $receiptNumber);
        cibo_execute_statement_or_fail($receiptStatement, 'Unable to generate the receipt record.');
        $receiptStatement->close();

        $db->commit();
    } catch (Throwable $exception) {
        $db->rollback();
        throw $exception;
    }

    cibo_order_store_guest_reference($orderNumber);

    $createdOrder = cibo_fetch_order_by_number_for_session($orderNumber);

    if (!$createdOrder) {
        throw new RuntimeException('The order was created, but it could not be loaded again.');
    }

    return $createdOrder;
}

function cibo_update_order_status(string $orderNumber, string $status): array
{
    $orderNumber = trim($orderNumber);
    $status = cibo_normalize_order_status($status);

    $allowedStatuses = array_keys(cibo_order_status_options());

    if ($orderNumber === '' || !in_array($status, $allowedStatuses, true)) {
        throw new RuntimeException('Please select a valid order status.');
    }

    $db = cibo_app_db();

    if (!$db) {
        throw new RuntimeException('Order database is not ready yet. Import database/schema.sql first.');
    }

    $orderLookupStatement = $db->prepare('
        SELECT payment_method
             , order_status
        FROM orders
        WHERE order_number = ?
        LIMIT 1
    ');

    if (!$orderLookupStatement) {
        throw new RuntimeException('Unable to load the order before updating the status.');
    }

    $orderLookupStatement->bind_param('s', $orderNumber);
    $orderLookupStatement->execute();
    $orderRecord = $orderLookupStatement->get_result()?->fetch_assoc();
    $orderLookupStatement->close();

    if (!$orderRecord) {
        throw new RuntimeException('Unable to find the order.');
    }

    $paymentMethod = strtolower(trim((string) ($orderRecord['payment_method'] ?? '')));
    $currentStatus = cibo_normalize_order_status((string) ($orderRecord['order_status'] ?? 'placed'));
    cibo_assert_valid_order_status_transition($currentStatus, $status);

    if ($currentStatus === $status) {
        $existingOrder = cibo_fetch_orders_by_filters(null, [$orderNumber]);

        if (!$existingOrder) {
            throw new RuntimeException('Unable to load the updated order.');
        }

        return $existingOrder[0];
    }
    cibo_apply_order_status_update($db, $orderNumber, $paymentMethod, $status);

    $updatedOrder = cibo_fetch_orders_by_filters(null, [$orderNumber]);

    if (!$updatedOrder) {
        throw new RuntimeException('Unable to load the updated order.');
    }

    return $updatedOrder[0];
}

function cibo_apply_order_status_update(mysqli $db, string $orderNumber, string $paymentMethod, string $status): void
{
    $hasDeliveredAt = cibo_order_column_exists($db, 'delivered_at');

    if ($hasDeliveredAt) {
        if ($status === 'delivered') {
            if ($paymentMethod === 'cod') {
                $statement = $db->prepare('
                    UPDATE orders
                    SET order_status = ?, payment_status = ?, delivered_at = CURRENT_TIMESTAMP
                    WHERE order_number = ?
                ');
            } else {
                $statement = $db->prepare('
                    UPDATE orders
                    SET order_status = ?, delivered_at = CURRENT_TIMESTAMP
                    WHERE order_number = ?
                ');
            }
        } else {
            if ($paymentMethod === 'cod') {
                $statement = $db->prepare('
                    UPDATE orders
                    SET order_status = ?, payment_status = ?, delivered_at = NULL
                    WHERE order_number = ?
                ');
            } else {
                $statement = $db->prepare('
                    UPDATE orders
                    SET order_status = ?, delivered_at = NULL
                    WHERE order_number = ?
                ');
            }
        }
    } else {
        if ($paymentMethod === 'cod') {
            $statement = $db->prepare('
                UPDATE orders
                SET order_status = ?, payment_status = ?
                WHERE order_number = ?
            ');
        } else {
            $statement = $db->prepare('
                UPDATE orders
                SET order_status = ?
                WHERE order_number = ?
            ');
        }
    }

    if (!$statement) {
        throw new RuntimeException('Unable to update the order status.');
    }

    if ($paymentMethod === 'cod') {
        $paymentStatus = $status === 'delivered' ? 'paid' : 'pending';
        $statement->bind_param('sss', $status, $paymentStatus, $orderNumber);
    } else {
        $statement->bind_param('ss', $status, $orderNumber);
    }

    cibo_execute_statement_or_fail($statement, 'Unable to update the order status.');
    $statement->close();
}

function cibo_demo_progress_orders(?mysqli $db = null): void
{
    static $alreadyRan = false;

    if ($alreadyRan) {
        return;
    }

    $alreadyRan = true;
    $db = $db instanceof mysqli ? $db : cibo_app_db();

    if (!$db instanceof mysqli) {
        return;
    }

    $placedAtColumn = cibo_order_column_exists($db, 'placed_at') ? 'placed_at' : 'created_at';
    $statement = $db->prepare("
        SELECT order_number,
               order_status,
               payment_method,
               {$placedAtColumn} AS placed_at,
               TIMESTAMPDIFF(SECOND, {$placedAtColumn}, CURRENT_TIMESTAMP) AS age_seconds
        FROM orders
        WHERE order_status IN ('placed', 'preparing', 'out_for_delivery')
    ");

    if (!$statement) {
        return;
    }

    $statement->execute();
    $orders = $statement->get_result()?->fetch_all(MYSQLI_ASSOC) ?? [];
    $statement->close();

    foreach ($orders as $order) {
        $currentStatus = cibo_normalize_order_status((string) ($order['order_status'] ?? 'placed'));

        if ($currentStatus === 'cancelled' || cibo_order_status_is_final($currentStatus)) {
            continue;
        }

        $targetStatus = cibo_demo_target_order_status_for_elapsed((int) ($order['age_seconds'] ?? 0));

        if (
            $targetStatus === $currentStatus
            || cibo_order_progression_rank($targetStatus) <= cibo_order_progression_rank($currentStatus)
        ) {
            continue;
        }

        $nextStatus = $currentStatus;

        while ($nextStatus !== $targetStatus) {
            $allowedNextStatuses = cibo_order_next_statuses($nextStatus);

            if (!$allowedNextStatuses) {
                break;
            }

            $nextStatus = (string) $allowedNextStatuses[0];
            cibo_apply_order_status_update(
                $db,
                (string) ($order['order_number'] ?? ''),
                strtolower(trim((string) ($order['payment_method'] ?? ''))),
                $nextStatus
            );
        }
    }
}

function cibo_clear_all_orders(): void
{
    cibo_start_user_session();

    $db = cibo_app_db();

    if ($db instanceof mysqli) {
        $db->begin_transaction();

        try {
            $db->query('DELETE FROM order_items');
            $db->query('DELETE FROM orders');
            $db->commit();
        } catch (Throwable $exception) {
            $db->rollback();
            throw new RuntimeException('Unable to clear the orders.');
        }
    }

    $_SESSION['session_order_numbers'] = [];
    unset($_SESSION['last_order_number']);
}
