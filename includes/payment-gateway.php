<?php

declare(strict_types=1);

require_once __DIR__ . '/orders.php';

function cibo_razorpay_is_test_mode(): bool
{
    $keyId = trim((string) CIBO_RAZORPAY_KEY_ID);
    $keySecret = trim((string) CIBO_RAZORPAY_KEY_SECRET);

    return $keyId !== ''
        && $keySecret !== ''
        && str_starts_with($keyId, 'rzp_test_');
}

function cibo_require_razorpay_test_mode(): void
{
    $keyId = trim((string) CIBO_RAZORPAY_KEY_ID);
    $keySecret = trim((string) CIBO_RAZORPAY_KEY_SECRET);

    if ($keyId !== '' && $keySecret !== '') {
        return;
    }

    throw new RuntimeException('Secure prepaid payments are not configured yet. Please add Razorpay credentials and try again.');
}

function cibo_ensure_order_payment_metadata_columns(mysqli $db): void
{
    $columns = [
        'gateway_name' => "ALTER TABLE orders ADD COLUMN gateway_name VARCHAR(40) NULL AFTER payment_status",
        'gateway_order_id' => "ALTER TABLE orders ADD COLUMN gateway_order_id VARCHAR(80) NULL AFTER gateway_name",
        'gateway_payment_id' => "ALTER TABLE orders ADD COLUMN gateway_payment_id VARCHAR(80) NULL AFTER gateway_order_id",
        'payment_verified_at' => "ALTER TABLE orders ADD COLUMN payment_verified_at DATETIME NULL AFTER gateway_payment_id",
    ];

    foreach ($columns as $column => $query) {
        if (cibo_order_column_exists($db, $column)) {
            continue;
        }

        $db->query($query);
    }
}

function cibo_ensure_payment_intents_table(mysqli $db): void
{
    $db->query("
        CREATE TABLE IF NOT EXISTS payment_intents (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            intent_token VARCHAR(80) NOT NULL,
            user_id INT UNSIGNED DEFAULT NULL,
            gateway_name VARCHAR(40) NOT NULL DEFAULT 'razorpay',
            payment_method VARCHAR(20) NOT NULL,
            gateway_order_id VARCHAR(80) NOT NULL,
            gateway_payment_id VARCHAR(80) DEFAULT NULL,
            amount_paise INT UNSIGNED NOT NULL,
            currency CHAR(3) NOT NULL DEFAULT 'INR',
            status VARCHAR(20) NOT NULL DEFAULT 'created',
            customer_name VARCHAR(120) NOT NULL DEFAULT '',
            customer_phone VARCHAR(20) NOT NULL DEFAULT '',
            customer_email VARCHAR(190) NOT NULL DEFAULT '',
            checkout_payload_json LONGTEXT NOT NULL,
            summary_json LONGTEXT NOT NULL,
            cibo_order_number VARCHAR(50) DEFAULT NULL,
            payment_verified_at DATETIME DEFAULT NULL,
            last_error VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_payment_intents_token (intent_token),
            UNIQUE KEY uk_payment_intents_gateway_order (gateway_order_id),
            KEY idx_payment_intents_status_created (status, created_at),
            KEY idx_payment_intents_gateway_payment (gateway_payment_id),
            KEY idx_payment_intents_cibo_order (cibo_order_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function cibo_payment_intent_token(): string
{
    return 'payint_' . bin2hex(random_bytes(16));
}

function cibo_razorpay_api_request(string $method, string $path, ?array $payload = null): array
{
    cibo_require_razorpay_test_mode();

    $method = strtoupper(trim($method));
    $url = 'https://api.razorpay.com/v1/' . ltrim($path, '/');
    $payloadJson = $payload !== null ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL is required for Razorpay integration.');
    }

    $curl = curl_init($url);

    if ($curl === false) {
        throw new RuntimeException('Unable to initialize the Razorpay request.');
    }

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_USERPWD => CIBO_RAZORPAY_KEY_ID . ':' . CIBO_RAZORPAY_KEY_SECRET,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
        ],
    ]);

    if ($payloadJson !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payloadJson);
    }

    $rawResponse = curl_exec($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if (!is_string($rawResponse)) {
        throw new RuntimeException($curlError !== '' ? $curlError : 'Unable to contact Razorpay right now.');
    }

    $decoded = json_decode($rawResponse, true);

    if ($statusCode < 200 || $statusCode >= 300) {
        $apiMessage = is_array($decoded)
            ? (string) ($decoded['error']['description'] ?? $decoded['error']['reason'] ?? $decoded['error']['code'] ?? '')
            : '';

        throw new RuntimeException($apiMessage !== '' ? $apiMessage : 'Razorpay request failed.');
    }

    return is_array($decoded) ? $decoded : [];
}

function cibo_prepare_prepaid_checkout(array $payload): array
{
    cibo_start_user_session();
    cibo_require_razorpay_test_mode();

    $paymentMethod = strtolower(trim((string) ($payload['payment_method'] ?? '')));

    if (!in_array($paymentMethod, ['upi', 'card'], true)) {
        throw new RuntimeException('Only secure UPI and card payments can use Razorpay.');
    }

    $resolvedCheckout = cibo_resolve_checkout_payload($payload);
    $customer = cibo_validate_customer_details(is_array($payload['customer'] ?? null) ? $payload['customer'] : []);
    $summary = is_array($resolvedCheckout['summary'] ?? null) ? $resolvedCheckout['summary'] : [];
    $cartItems = is_array($resolvedCheckout['cart_items'] ?? null) ? $resolvedCheckout['cart_items'] : [];
    $currentUser = cibo_current_user() ?? [];
    $userId = (int) ($currentUser['id'] ?? 0);
    $customerEmail = strtolower(trim((string) ($currentUser['email'] ?? '')));
    $intentToken = cibo_payment_intent_token();
    $amountPaise = (int) round(((float) ($summary['total_amount'] ?? 0)) * 100);

    if ($amountPaise <= 0) {
        throw new RuntimeException('Unable to start a payment for an empty order total.');
    }

    $canonicalPayload = [
        'address_id' => (int) ($payload['address_id'] ?? ($payload['customer']['address_id'] ?? 0)),
        'restaurant' => is_array($payload['restaurant'] ?? null) ? $payload['restaurant'] : [],
        'promo_code' => (string) ($payload['promo_code'] ?? ''),
        'payment_method' => $paymentMethod,
        'customer' => [
            'address_id' => (int) ($payload['customer']['address_id'] ?? 0),
            'name' => $customer['name'],
            'phone' => $customer['phone'],
            'address' => $customer['address'],
            'city' => $customer['city'],
            'pincode' => $customer['pincode'],
        ],
        'items' => array_map(static function (array $item): array {
            return [
                'id' => (string) ($item['id'] ?? ''),
                'name' => (string) ($item['name'] ?? 'Item'),
                'restaurantId' => (string) ($item['restaurantId'] ?? ''),
                'slug' => (string) ($item['slug'] ?? ''),
                'price' => round((float) ($item['price'] ?? 0), 2),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'image' => (string) ($item['image'] ?? ''),
                'restaurant' => (string) ($item['restaurant'] ?? ''),
                'restaurantSlug' => (string) ($item['restaurantSlug'] ?? ''),
                'restaurantPage' => (string) ($item['restaurantPage'] ?? ''),
            ];
        }, $cartItems),
    ];

    $gatewayOrder = cibo_razorpay_api_request('POST', 'orders', [
        'amount' => $amountPaise,
        'currency' => 'INR',
        'receipt' => $intentToken,
        'notes' => [
            'intent_token' => $intentToken,
            'payment_method' => $paymentMethod,
            'customer_phone' => $customer['phone'],
        ],
    ]);

    $gatewayOrderId = trim((string) ($gatewayOrder['id'] ?? ''));

    if ($gatewayOrderId === '') {
        throw new RuntimeException('Unable to create the secure payment order right now.');
    }

    $db = cibo_app_db();

    if (!$db instanceof mysqli) {
        throw new RuntimeException('Payment database is not ready yet. Please verify the cibo_db_v2 connection.');
    }

    cibo_ensure_payment_intents_table($db);

    $statement = $db->prepare('
        INSERT INTO payment_intents (
            intent_token,
            user_id,
            gateway_name,
            payment_method,
            gateway_order_id,
            amount_paise,
            currency,
            status,
            customer_name,
            customer_phone,
            customer_email,
            checkout_payload_json,
            summary_json
        ) VALUES (?, NULLIF(?, 0), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    if (!$statement) {
        throw new RuntimeException('Unable to save the secure payment attempt.');
    }

    $gatewayName = 'razorpay';
    $status = 'created';
    $checkoutPayloadJson = json_encode($canonicalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $summaryJson = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $statement->bind_param(
        'sisssisssssss',
        $intentToken,
        $userId,
        $gatewayName,
        $paymentMethod,
        $gatewayOrderId,
        $amountPaise,
        $gatewayOrder['currency'],
        $status,
        $customer['name'],
        $customer['phone'],
        $customerEmail,
        $checkoutPayloadJson,
        $summaryJson
    );
    cibo_execute_statement_or_fail($statement, 'Unable to save the secure payment attempt.');
    $statement->close();

    return [
        'intent_token' => $intentToken,
        'key_id' => (string) CIBO_RAZORPAY_KEY_ID,
        'gateway_order_id' => $gatewayOrderId,
        'amount' => $amountPaise,
        'currency' => (string) ($gatewayOrder['currency'] ?? 'INR'),
        'payment_method' => $paymentMethod,
        'prefill' => [
            'name' => $customer['name'],
            'contact' => $customer['phone'],
            'email' => $customerEmail,
        ],
        'display' => [
            'name' => 'Cibo',
            'description' => 'Secure prepaid checkout',
            'theme_color' => '#5f7c3a',
        ],
        'summary' => [
            'total_amount' => round((float) ($summary['total_amount'] ?? 0), 2),
        ],
    ];
}

function cibo_fetch_payment_intent_by_token(mysqli $db, string $intentToken): ?array
{
    $statement = $db->prepare('
        SELECT *
        FROM payment_intents
        WHERE intent_token = ?
        LIMIT 1
    ');

    if (!$statement) {
        return null;
    }

    $statement->bind_param('s', $intentToken);
    $statement->execute();
    $record = $statement->get_result()?->fetch_assoc();
    $statement->close();

    return $record ?: null;
}

function cibo_razorpay_verify_signature(string $gatewayOrderId, string $paymentId, string $signature): bool
{
    $payload = $gatewayOrderId . '|' . $paymentId;
    $generatedSignature = hash_hmac('sha256', $payload, (string) CIBO_RAZORPAY_KEY_SECRET);
    return hash_equals($generatedSignature, trim($signature));
}

function cibo_razorpay_fetch_payment(string $paymentId): array
{
    return cibo_razorpay_api_request('GET', 'payments/' . rawurlencode($paymentId));
}

function cibo_razorpay_capture_payment(string $paymentId, int $amountPaise, string $currency = 'INR'): array
{
    return cibo_razorpay_api_request('POST', 'payments/' . rawurlencode($paymentId) . '/capture', [
        'amount' => $amountPaise,
        'currency' => $currency,
    ]);
}

function cibo_store_order_payment_gateway_metadata(string $orderNumber, array $metadata): void
{
    $db = cibo_app_db();

    if (!$db instanceof mysqli || trim($orderNumber) === '') {
        return;
    }

    cibo_ensure_order_payment_metadata_columns($db);

    $gatewayName = cibo_normalize_single_line((string) ($metadata['gateway_name'] ?? ''), 40);
    $gatewayOrderId = cibo_normalize_single_line((string) ($metadata['gateway_order_id'] ?? ''), 80);
    $gatewayPaymentId = cibo_normalize_single_line((string) ($metadata['gateway_payment_id'] ?? ''), 80);
    $verifiedAt = trim((string) ($metadata['payment_verified_at'] ?? ''));

    $statement = $db->prepare('
        UPDATE orders
        SET gateway_name = ?, gateway_order_id = ?, gateway_payment_id = ?, payment_verified_at = ?
        WHERE order_number = ?
        LIMIT 1
    ');

    if (!$statement) {
        return;
    }

    $verifiedAt = $verifiedAt !== '' ? $verifiedAt : date('Y-m-d H:i:s');
    $statement->bind_param('sssss', $gatewayName, $gatewayOrderId, $gatewayPaymentId, $verifiedAt, $orderNumber);
    $statement->execute();
    $statement->close();
}

function cibo_verify_prepaid_checkout(array $input): array
{
    cibo_start_user_session();
    cibo_require_razorpay_test_mode();

    $intentToken = trim((string) ($input['intent_token'] ?? ''));
    $paymentId = trim((string) ($input['razorpay_payment_id'] ?? ''));
    $signature = trim((string) ($input['razorpay_signature'] ?? ''));

    if ($intentToken === '' || $paymentId === '' || $signature === '') {
        throw new RuntimeException('Unable to verify the payment because the confirmation details were incomplete.');
    }

    $db = cibo_app_db();

    if (!$db instanceof mysqli) {
        throw new RuntimeException('Payment database is not ready yet. Please verify the cibo_db_v2 connection.');
    }

    cibo_ensure_payment_intents_table($db);
    cibo_ensure_order_payment_metadata_columns($db);

    $db->begin_transaction();

    try {
        $statement = $db->prepare('
            SELECT *
            FROM payment_intents
            WHERE intent_token = ?
            LIMIT 1
            FOR UPDATE
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to load the secure payment attempt.');
        }

        $statement->bind_param('s', $intentToken);
        $statement->execute();
        $intent = $statement->get_result()?->fetch_assoc();
        $statement->close();

        if (!$intent) {
            throw new RuntimeException('Unable to find that secure payment attempt. Please try again from checkout.');
        }

        if ((string) ($intent['cibo_order_number'] ?? '') !== '') {
            $db->commit();
            $existingOrder = cibo_fetch_order_by_number_for_session((string) $intent['cibo_order_number']);

            if (!$existingOrder) {
                throw new RuntimeException('Payment was already verified, but the order could not be loaded again.');
            }

            return $existingOrder;
        }

        if ((string) ($intent['status'] ?? '') === 'processing') {
            throw new RuntimeException('Payment confirmation is already in progress. Please wait a moment and try again.');
        }

        if (!cibo_razorpay_verify_signature((string) ($intent['gateway_order_id'] ?? ''), $paymentId, $signature)) {
            throw new RuntimeException('Unable to verify the payment signature.');
        }

        $markProcessing = $db->prepare('
            UPDATE payment_intents
            SET status = ?, gateway_payment_id = ?, payment_verified_at = CURRENT_TIMESTAMP, last_error = NULL
            WHERE intent_token = ?
            LIMIT 1
        ');

        if (!$markProcessing) {
            throw new RuntimeException('Unable to lock the secure payment attempt.');
        }

        $processingStatus = 'processing';
        $markProcessing->bind_param('sss', $processingStatus, $paymentId, $intentToken);
        cibo_execute_statement_or_fail($markProcessing, 'Unable to lock the secure payment attempt.');
        $markProcessing->close();
        $db->commit();
    } catch (Throwable $exception) {
        $db->rollback();
        throw $exception;
    }

    try {
        $payment = cibo_razorpay_fetch_payment($paymentId);
        $paymentStatus = strtolower(trim((string) ($payment['status'] ?? '')));
        $paymentOrderId = trim((string) ($payment['order_id'] ?? ''));
        $paymentAmount = (int) ($payment['amount'] ?? 0);
        $expectedOrderId = (string) ($intent['gateway_order_id'] ?? '');
        $expectedAmount = (int) ($intent['amount_paise'] ?? 0);

        if ($paymentOrderId !== $expectedOrderId) {
            throw new RuntimeException('The payment does not match the expected Razorpay order.');
        }

        if ($paymentAmount !== $expectedAmount) {
            throw new RuntimeException('The payment amount does not match the backend order total.');
        }

        if ($paymentStatus === 'authorized') {
            $payment = cibo_razorpay_capture_payment($paymentId, $expectedAmount, (string) ($intent['currency'] ?? 'INR'));
            $paymentStatus = strtolower(trim((string) ($payment['status'] ?? '')));
        }

        if ($paymentStatus !== 'captured') {
            throw new RuntimeException('The payment is not fully captured yet. Please try again in a moment.');
        }

        $checkoutPayload = json_decode((string) ($intent['checkout_payload_json'] ?? ''), true);

        if (!is_array($checkoutPayload)) {
            throw new RuntimeException('Unable to restore the verified checkout payload.');
        }

        $order = cibo_create_order($checkoutPayload);
        $orderNumber = trim((string) ($order['order_number'] ?? ''));

        if ($orderNumber === '') {
            throw new RuntimeException('The payment was verified, but the Cibo order number was missing.');
        }

        cibo_store_order_payment_gateway_metadata($orderNumber, [
            'gateway_name' => 'razorpay',
            'gateway_order_id' => $expectedOrderId,
            'gateway_payment_id' => $paymentId,
            'payment_verified_at' => date('Y-m-d H:i:s'),
        ]);

        $completeStatement = $db->prepare('
            UPDATE payment_intents
            SET status = ?, cibo_order_number = ?, gateway_payment_id = ?, payment_verified_at = CURRENT_TIMESTAMP, last_error = NULL
            WHERE intent_token = ?
            LIMIT 1
        ');

        if ($completeStatement) {
            $consumedStatus = 'consumed';
            $completeStatement->bind_param('ssss', $consumedStatus, $orderNumber, $paymentId, $intentToken);
            $completeStatement->execute();
            $completeStatement->close();
        }

        return $order;
    } catch (Throwable $exception) {
        $resetStatement = $db->prepare('
            UPDATE payment_intents
            SET status = ?, gateway_payment_id = ?, payment_verified_at = CURRENT_TIMESTAMP, last_error = ?
            WHERE intent_token = ?
            LIMIT 1
        ');

        if ($resetStatement) {
            $verifiedStatus = 'verified';
            $errorMessage = cibo_normalize_single_line($exception->getMessage(), 255);
            $resetStatement->bind_param('ssss', $verifiedStatus, $paymentId, $errorMessage, $intentToken);
            $resetStatement->execute();
            $resetStatement->close();
        }

        throw $exception;
    }
}

function cibo_verify_razorpay_webhook_signature(string $rawBody, string $signature): bool
{
    $secret = trim((string) CIBO_RAZORPAY_WEBHOOK_SECRET);

    if ($secret === '' || trim($signature) === '') {
        return false;
    }

    $generated = hash_hmac('sha256', $rawBody, $secret);
    return hash_equals($generated, trim($signature));
}

function cibo_handle_razorpay_webhook(string $rawBody, string $signature): void
{
    if (!cibo_verify_razorpay_webhook_signature($rawBody, $signature)) {
        throw new RuntimeException('Invalid Razorpay webhook signature.');
    }

    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        throw new RuntimeException('Invalid Razorpay webhook payload.');
    }

    $eventName = trim((string) ($payload['event'] ?? ''));
    $paymentEntity = is_array($payload['payload']['payment']['entity'] ?? null) ? $payload['payload']['payment']['entity'] : [];
    $orderEntity = is_array($payload['payload']['order']['entity'] ?? null) ? $payload['payload']['order']['entity'] : [];
    $gatewayOrderId = trim((string) ($paymentEntity['order_id'] ?? $orderEntity['id'] ?? ''));
    $paymentId = trim((string) ($paymentEntity['id'] ?? ''));

    if ($gatewayOrderId === '') {
        return;
    }

    $db = cibo_app_db();

    if (!$db instanceof mysqli) {
        return;
    }

    cibo_ensure_payment_intents_table($db);
    cibo_ensure_order_payment_metadata_columns($db);

    $status = 'created';
    $errorMessage = null;

    if (in_array($eventName, ['payment.captured', 'order.paid'], true)) {
        $status = 'verified';
    } elseif ($eventName === 'payment.failed') {
        $status = 'failed';
        $errorMessage = cibo_normalize_single_line((string) ($paymentEntity['error_description'] ?? 'Payment failed on Razorpay.'), 255);
    } else {
        return;
    }

    $statement = $db->prepare('
        UPDATE payment_intents
        SET status = CASE
                WHEN cibo_order_number IS NOT NULL THEN status
                ELSE ?
            END,
            gateway_payment_id = COALESCE(NULLIF(?, \'\'), gateway_payment_id),
            payment_verified_at = CASE
                WHEN ? = \'verified\' THEN CURRENT_TIMESTAMP
                ELSE payment_verified_at
            END,
            last_error = ?
        WHERE gateway_order_id = ?
        LIMIT 1
    ');

    if ($statement) {
        $statement->bind_param('sssss', $status, $paymentId, $status, $errorMessage, $gatewayOrderId);
        $statement->execute();
        $statement->close();
    }

    if ($paymentId !== '') {
        $intentStatement = $db->prepare('
            SELECT cibo_order_number
            FROM payment_intents
            WHERE gateway_order_id = ?
            LIMIT 1
        ');

        if ($intentStatement) {
            $intentStatement->bind_param('s', $gatewayOrderId);
            $intentStatement->execute();
            $intent = $intentStatement->get_result()?->fetch_assoc();
            $intentStatement->close();

            $orderNumber = trim((string) ($intent['cibo_order_number'] ?? ''));

            if ($orderNumber !== '') {
                cibo_store_order_payment_gateway_metadata($orderNumber, [
                    'gateway_name' => 'razorpay',
                    'gateway_order_id' => $gatewayOrderId,
                    'gateway_payment_id' => $paymentId,
                    'payment_verified_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
