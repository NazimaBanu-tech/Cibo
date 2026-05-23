<?php

declare(strict_types=1);

require_once __DIR__ . '/common.php';

function cibo_catalog_build_restaurant_href(string $slug): string
{
    return 'menu.php?restaurant=' . rawurlencode($slug);
}

function cibo_catalog_normalize_location_filter(string $location): string
{
    return strtolower(cibo_normalize_single_line($location, 120));
}

function cibo_catalog_has_active_restaurant_slug(string $slug): bool
{
    $slug = trim($slug);

    if ($slug === '') {
        return false;
    }

    $db = cibo_db();

    if (!$db) {
        return false;
    }

    $statement = $db->prepare('SELECT id FROM restaurants WHERE slug = ? AND is_active = 1 LIMIT 1');

    if (!$statement) {
        return false;
    }

    $statement->bind_param('s', $slug);
    $statement->execute();
    $record = $statement->get_result()?->fetch_assoc();
    $statement->close();

    return is_array($record);
}

function cibo_catalog_fetch_restaurants(string $locationFilter = ''): array
{
    $db = cibo_db();

    if (!$db) {
        return [];
    }

    $normalizedLocation = cibo_catalog_normalize_location_filter($locationFilter);

    if ($normalizedLocation !== '') {
        $statement = $db->prepare("
            SELECT
                id,
                name,
                slug,
                category,
                cuisine,
                location,
                rating,
                delivery_time,
                image,
                hero_image,
                address,
                offer_text
            FROM restaurants
            WHERE is_active = 1
              AND LOWER(location) LIKE CONCAT('%', ?, '%')
            ORDER BY id ASC
        ");

        if (!$statement) {
            return [];
        }

        $statement->bind_param('s', $normalizedLocation);
        $statement->execute();
        $result = $statement->get_result();
    } else {
        $result = $db->query("
            SELECT
                id,
                name,
                slug,
                category,
                cuisine,
                location,
                rating,
                delivery_time,
                image,
                hero_image,
                address,
                offer_text
            FROM restaurants
            WHERE is_active = 1
            ORDER BY id ASC
        ");
    }

    if (!$result instanceof mysqli_result) {
        return [];
    }

    $restaurants = [];

    while ($row = $result->fetch_assoc()) {
        $slug = trim((string) ($row['slug'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $category = trim((string) ($row['category'] ?? ''));
        $cuisine = trim((string) ($row['cuisine'] ?? $category));
        $location = trim((string) ($row['location'] ?? ''));
        $rating = trim((string) ($row['rating'] ?? '4.0'));
        $deliveryTime = trim((string) ($row['delivery_time'] ?? '25-30 mins'));

        $restaurants[] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => $name,
            'slug' => $slug,
            'cuisines' => $cuisine,
            'category' => $category !== '' ? $category : $cuisine,
            'location' => $location,
            'rating' => $rating,
            'deliveryTime' => $deliveryTime,
            'ratingMeta' => trim($rating . ' • ' . $deliveryTime . ' • ' . $cuisine, " •"),
            'image' => trim((string) ($row['image'] ?? '')),
            'heroImage' => trim((string) ($row['hero_image'] ?? '')),
            'address' => trim((string) ($row['address'] ?? '')),
            'offerText' => trim((string) ($row['offer_text'] ?? '')) ?: 'Free delivery above Rs199',
            'href' => cibo_catalog_build_restaurant_href($slug !== '' ? $slug : $name),
        ];
    }

    $result->free();

    return $restaurants;
}

function cibo_catalog_fetch_menu_items(): array
{
    $db = cibo_db();

    if (!$db) {
        return [];
    }

    $result = $db->query("
        SELECT
            m.id,
            m.restaurant_id,
            m.name,
            m.slug,
            m.description,
            m.price,
            m.food_type,
            m.image,
            r.name AS restaurant_name,
            r.slug AS restaurant_slug
        FROM menu_items m
        INNER JOIN restaurants r ON r.id = m.restaurant_id
        WHERE m.is_available = 1
          AND r.is_active = 1
        ORDER BY m.restaurant_id ASC, m.id ASC
    ");

    if (!$result instanceof mysqli_result) {
        return [];
    }

    $items = [];

    while ($row = $result->fetch_assoc()) {
        $restaurantSlug = trim((string) ($row['restaurant_slug'] ?? ''));

        $items[] = [
            'id' => 'menu-item-' . (int) ($row['id'] ?? 0),
            'menuItemId' => (int) ($row['id'] ?? 0),
            'restaurantId' => (int) ($row['restaurant_id'] ?? 0),
            'restaurantName' => trim((string) ($row['restaurant_name'] ?? '')),
            'restaurantSlug' => $restaurantSlug,
            'restaurantHref' => cibo_catalog_build_restaurant_href($restaurantSlug),
            'name' => trim((string) ($row['name'] ?? '')),
            'slug' => trim((string) ($row['slug'] ?? '')),
            'price' => round((float) ($row['price'] ?? 0), 2),
            'foodType' => trim((string) ($row['food_type'] ?? 'none')),
            'image' => trim((string) ($row['image'] ?? '')),
            'description' => trim((string) ($row['description'] ?? '')),
            'filterTags' => array_values(array_filter([
                trim((string) ($row['food_type'] ?? '')),
            ])),
        ];
    }

    $result->free();

    return $items;
}
