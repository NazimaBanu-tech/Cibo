<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/catalog.php';

$restaurantSlug = trim((string) ($_GET['restaurant'] ?? ''));

if ($restaurantSlug === '' || !cibo_catalog_has_active_restaurant_slug($restaurantSlug)) {
    header('Location: index.php', true, 302);
    exit;
}

$restaurantRecord = null;

foreach (cibo_catalog_fetch_restaurants() as $restaurant) {
    if (trim((string) ($restaurant['slug'] ?? '')) === $restaurantSlug) {
        $restaurantRecord = $restaurant;
        break;
    }
}

if (!$restaurantRecord) {
    header('Location: index.php', true, 302);
    exit;
}

$restaurantName = trim((string) ($restaurantRecord['name'] ?? 'Restaurant')) ?: 'Restaurant';
$restaurantLocation = trim((string) ($restaurantRecord['location'] ?? ''));
$restaurantRatingMeta = trim((string) ($restaurantRecord['ratingMeta'] ?? ''));
$restaurantOfferText = trim((string) ($restaurantRecord['offerText'] ?? '')) ?: 'Free delivery on orders above ₹199';
$restaurantCardImage = trim((string) ($restaurantRecord['image'] ?? ''));
$restaurantHeroImage = trim((string) ($restaurantRecord['heroImage'] ?? ''));

$normalizedRatingMeta = str_replace(['Ã¢â‚¬Â¢', 'â€¢'], '•', $restaurantRatingMeta);
$restaurantMetaParts = array_values(array_filter(array_map(
    static fn (string $part): string => trim($part),
    explode('•', $normalizedRatingMeta)
)));

function cibo_menu_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cibo_menu_public_asset_exists(string $path): bool
{
    $normalizedPath = ltrim(str_replace('\\', '/', trim($path)), '/');

    if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
        return false;
    }

    $absolutePath = realpath(__DIR__ . '/' . $normalizedPath);
    $projectRoot = realpath(__DIR__);

    return $absolutePath !== false
        && $projectRoot !== false
        && str_starts_with(str_replace('\\', '/', $absolutePath), str_replace('\\', '/', $projectRoot))
        && is_file($absolutePath);
}

function cibo_menu_hero_candidates(string $heroImagePath, string $cardImagePath, string $slug): array
{
    $candidates = [];
    $normalizedHeroPath = trim(str_replace('\\', '/', $heroImagePath));
    $normalizedCardPath = trim(str_replace('\\', '/', $cardImagePath));
    $cardBaseName = pathinfo($normalizedCardPath, PATHINFO_FILENAME);
    $normalizedSlug = trim($slug);

    if (
        $normalizedHeroPath !== ''
        && (
            str_contains($normalizedHeroPath, 'images/restaurant-heroes/')
            || str_contains(pathinfo($normalizedHeroPath, PATHINFO_FILENAME), '-hero')
        )
    ) {
        $candidates[] = $normalizedHeroPath;
    }

    foreach (array_filter([$cardBaseName, $normalizedSlug]) as $baseName) {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $extension) {
            $candidates[] = 'images/restaurant-heroes/' . $baseName . '-hero.' . $extension;
        }
    }

    if ($normalizedHeroPath !== '' && $normalizedHeroPath !== $normalizedCardPath) {
        $candidates[] = $normalizedHeroPath;
    }

    if ($normalizedCardPath !== '') {
        $candidates[] = $normalizedCardPath;
    }

    return array_values(array_unique(array_filter($candidates, static fn (string $candidate): bool => trim($candidate) !== '')));
}

$restaurantResolvedHeroImage = 'images/hero.jpg';

foreach (cibo_menu_hero_candidates($restaurantHeroImage, $restaurantCardImage, $restaurantSlug) as $candidatePath) {
    if (cibo_menu_public_asset_exists($candidatePath)) {
        $restaurantResolvedHeroImage = $candidatePath;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= cibo_menu_h($restaurantName) ?> - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <main class="menu-page">

    <section class="restaurant-hero-card">
      <div class="restaurant-hero-left">
        <p class="restaurant-breadcrumb">
          <a href="index.php">Home</a> / <?= cibo_menu_h($restaurantName) ?>
        </p>

        <h1><?= cibo_menu_h($restaurantName) ?></h1>

        <div class="restaurant-meta">
          <?php foreach ($restaurantMetaParts as $metaPart): ?>
            <span><?= cibo_menu_h($metaPart) ?></span>
          <?php endforeach; ?>
        </div>

        <p class="restaurant-address"><?= cibo_menu_h($restaurantLocation) ?></p>
        <p class="restaurant-offer"><?= cibo_menu_h($restaurantOfferText) ?></p>
      </div>

      <div class="restaurant-hero-right">
       <img
         src="<?= cibo_menu_h($restaurantResolvedHeroImage) ?>"
         alt="<?= cibo_menu_h($restaurantName) ?> hero image"
         data-hero-fallback="<?= cibo_menu_h($restaurantCardImage !== '' ? $restaurantCardImage : 'images/hero.jpg') ?>"
       > 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="burgers">Burgers</button>
      <button class="chip" data-filter="fries">Fries</button>
      <button class="chip" data-filter="nuggets">Nuggets</button>
      <button class="chip" data-filter="wraps">Wraps</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
      <button class="chip" data-filter="combos">Combos</button>
    </section>
    <section class="food-grid">

      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/burger.jpg" alt="Chicken Burger">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Burger</h3>
          <p class="food-price">₹139</p>
          <p class="food-desc">Juicy chicken patty with cheese, lettuce and a soft burger bun.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/mcaloo.jpg" alt="McAloo Tikki">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>McAloo Tikki</h3>
          <p class="food-price">₹79</p>
          <p class="food-desc">Classic crispy aloo patty burger with fresh onions and sauce.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="fries">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/fries.jpg" alt="French Fries">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>French Fries</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Crispy golden fries with light seasoning, perfect as a side.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="nuggets">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/nuggets.jpg" alt="Chicken Nuggets">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Nuggets</h3>
          <p class="food-price">₹159</p>
          <p class="food-desc">Crunchy chicken nuggets served with a tasty dip.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="wraps">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/wrap.jpg" alt="Chicken Wrap">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Wrap</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Soft wrap filled with chicken, sauce and fresh vegetables.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/coke.jpg" alt="Coke">
        </div>
        <div class="food-card-body">
          <h3>Coke</h3>
          <p class="food-price">₹60</p>
          <p class="food-desc">Refreshing chilled coke to pair with your meal.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/mcflurry.jpg" alt="McFlurry">
        </div>
        <div class="food-card-body">
          <h3>McFlurry</h3>
          <p class="food-price">₹119</p>
          <p class="food-desc">Creamy dessert topped with delicious chocolate mix.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="combos">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/combo.jpg" alt="Burger Combo">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Burger Combo</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Chicken burger served with fries and a chilled drink.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

    </section>
  </main>

  <footer class="footer">
    <div class="footer-main">
      <div class="footer-brand-block">
        <div class="footer-brand-top">
          <img src="images/logo.png" class="footer-logo" alt="Cibo Logo">
          <span class="footer-brand-name">Cibo</span>
        </div>
        <p class="footer-brand-text">
          Fresh flavours, fast delivery, and your favourite meals in one clean and cozy food experience.
        </p>
      </div>

      <div class="footer-links-block">
        <div class="footer-col">
          <h4>Explore</h4>
          <a href="index.php">Home</a>
          <a href="#">Restaurants</a>
          <a href="#">Categories</a>
        </div>

        <div class="footer-col">
          <h4>Support</h4>
          <a href="#">Help Center</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms</a>
        </div>

        <div class="footer-col">
          <h4>Contact</h4>
          <p>Bangalore, India</p>
          <p>support@cibo.com</p>
          <p>+91 00000 00000</p>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2026 Cibo. Crafted for better food moments.</p>
    </div>
  </footer>
  <script src="favorites.js"></script>
  <script src="auth-display.js"></script>
  <script src="cart-manager.js"></script>
  <script src="menu.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>
