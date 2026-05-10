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
            'users' => 0,
            'restaurants' => 0,
        ];
    }

    $safeOrders = is_array($orders) ? $orders : cibo_admin_fetch_orders();
    $orderCount = count($safeOrders);
    $revenue = 0.0;

    foreach ($safeOrders as $order) {
        $status = strtolower(trim((string) ($order['order_status'] ?? 'pending')));

        if ($status === 'cancelled') {
            continue;
        }

        $revenue += (float) ($order['total_amount'] ?? 0);
    }

    $users = (int) ($db->query('SELECT COUNT(*) AS total FROM users')?->fetch_assoc()['total'] ?? 0);
    $restaurants = (int) ($db->query('SELECT COUNT(*) AS total FROM restaurants')?->fetch_assoc()['total'] ?? 0);

    return [
        'orders' => $orderCount,
        'revenue' => round($revenue, 2),
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
            cuisine,
            location,
            rating,
            delivery_time,
            image_path,
            hero_image_path,
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
    $image = trim((string) ($input['image_path'] ?? ''));
    $heroImage = trim((string) ($input['hero_image_path'] ?? $input['heroImage'] ?? $image));
    $location = trim((string) ($input['location'] ?? ''));
    $category = trim((string) ($input['cuisine'] ?? $input['cuisines'] ?? ''));
    $rating = max(0.0, (float) ($input['rating'] ?? 4.3));
    $deliveryTime = trim((string) ($input['delivery_time'] ?? $input['deliveryTime'] ?? '25-30 mins')) ?: '25-30 mins';
    $offerText = trim((string) ($input['offer_text'] ?? $input['offerText'] ?? 'Free delivery above â‚¹199')) ?: 'Free delivery above â‚¹199';
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
            SET name = ?, slug = ?, cuisine = ?, location = ?, rating = ?, delivery_time = ?, image_path = ?, hero_image_path = ?, address = ?, offer_text = ?
            WHERE id = ?
        ');

        if (!$statement) {
            throw new RuntimeException('Unable to update the restaurant.');
        }

        $offerText = 'Free delivery above ₹199';
        $statement->bind_param('ssssdsssssi', $name, $slug, $category, $location, $rating, $deliveryTime, $image, $heroImage, $address, $offerText, $id);
        $statement->execute();
        $statement->close();
        return;
    }

    $statement = $db->prepare('
        INSERT INTO restaurants (name, slug, cuisine, location, rating, delivery_time, image_path, hero_image_path, address, offer_text)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    if (!$statement) {
        throw new RuntimeException('Unable to add the restaurant.');
    }

    $offerText = 'Free delivery above ₹199';
    $statement->bind_param('ssssdsssss', $name, $slug, $category, $location, $rating, $deliveryTime, $image, $heroImage, $address, $offerText);
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
            mi.image_path,
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
    $image = trim((string) ($input['image_path'] ?? ''));
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
            SET restaurant_id = ?, name = ?, slug = ?, description = ?, price = ?, food_type = ?, image_path = ?
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
        INSERT INTO menu_items (restaurant_id, name, slug, description, price, food_type, image_path)
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
    return [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
    ];
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
    $roleColumn = $db->query("SHOW COLUMNS FROM admins LIKE 'role'");
    $hasRoleColumn = $roleColumn && $roleColumn->fetch_assoc();

    if ($roleColumn instanceof mysqli_result) {
        $roleColumn->free();
    }

    if (!$hasRoleColumn) {
        $db->query("ALTER TABLE admins ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'Admin' AFTER email");
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

    $existingStatement = $db->prepare('SELECT id, name, email, role, created_at FROM admins WHERE email = ? LIMIT 1');

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

    $insertStatement = $db->prepare('INSERT INTO admins (name, email, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?)');

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
        $statement = $db->prepare('SELECT id, name, email, role, created_at FROM admins WHERE id = ? LIMIT 1');
        if ($statement) {
            $adminId = (int) $sessionAdmin['id'];
            $statement->bind_param('i', $adminId);
        }
    } else {
        $statement = $db->prepare('SELECT id, name, email, role, created_at FROM admins WHERE email = ? LIMIT 1');
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

    $duplicateStatement = $db->prepare('SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1');
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

    $statement = $db->prepare('UPDATE admins SET name = ?, email = ?, role = ?, created_at = ? WHERE id = ?');
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
    $statement = $db->prepare('SELECT password_hash FROM admins WHERE id = ? LIMIT 1');
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
    $updateStatement = $db->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');

    if (!$updateStatement) {
        throw new RuntimeException('Unable to change the password.');
    }

    $updateStatement->bind_param('si', $newHash, $adminId);
    $updateStatement->execute();
    $updateStatement->close();
}
