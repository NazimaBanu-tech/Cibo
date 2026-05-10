<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function cibo_catalog_build_restaurant_href(string $slug): string
{
    return 'menu.php?restaurant=' . rawurlencode($slug);
}

function cibo_catalog_fetch_restaurants(): array
{
    $db = cibo_db();

    if (!$db) {
        return [];
    }

    $result = $db->query("
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
        WHERE is_active = 1
        ORDER BY id ASC
    ");

    if (!$result instanceof mysqli_result) {
        return [];
    }

    $restaurants = [];

    while ($row = $result->fetch_assoc()) {
        $slug = trim((string) ($row['slug'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $cuisine = trim((string) ($row['cuisine'] ?? ''));
        $location = trim((string) ($row['location'] ?? ''));
        $rating = trim((string) ($row['rating'] ?? '4.0'));
        $deliveryTime = trim((string) ($row['delivery_time'] ?? '25-30 mins'));

        $restaurants[] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => $name,
            'slug' => $slug,
            'cuisines' => $cuisine,
            'category' => $cuisine,
            'location' => $location,
            'rating' => $rating,
            'deliveryTime' => $deliveryTime,
            'ratingMeta' => trim($rating . ' • ' . $deliveryTime . ' • ' . $cuisine, " •"),
            'image' => trim((string) ($row['image_path'] ?? '')),
            'heroImage' => trim((string) ($row['hero_image_path'] ?? '')),
            'address' => trim((string) ($row['address'] ?? '')),
            'offerText' => trim((string) ($row['offer_text'] ?? '')) ?: 'Free delivery above ₹199',
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
            m.image_path,
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
            'image' => trim((string) ($row['image_path'] ?? '')),
            'description' => trim((string) ($row['description'] ?? '')),
            'filterTags' => array_values(array_filter([
                trim((string) ($row['food_type'] ?? '')),
            ])),
        ];
    }

    $result->free();

    return $items;
}
