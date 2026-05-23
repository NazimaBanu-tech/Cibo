<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/catalog.php';

function cibo_homepage_restaurant_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function cibo_homepage_restaurant_href(string $restaurantName): string
{
    static $restaurantHrefMap = null;

    if (!is_array($restaurantHrefMap)) {
        $restaurantHrefMap = [];

        foreach (cibo_catalog_fetch_restaurants() as $restaurant) {
            $name = strtolower(trim((string) ($restaurant['name'] ?? '')));
            $href = trim((string) ($restaurant['href'] ?? ''));

            if ($name !== '' && $href !== '') {
                $restaurantHrefMap[$name] = $href;
            }
        }
    }

    $normalizedName = strtolower(trim($restaurantName));

    if ($normalizedName !== '' && isset($restaurantHrefMap[$normalizedName])) {
        return $restaurantHrefMap[$normalizedName];
    }

    return 'menu.php?restaurant=' . rawurlencode(cibo_homepage_restaurant_slug($restaurantName));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cibo</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
:root {
      --bg: #f1ece4;
      --card: #fffdf9;
      --text: #1f1f1b;
      --muted: #6f685f;
      --line: #e7dfd3;
      --accent: #5f7c3a;
      --accent-soft: #8aa35c;
      --highlight: color-mix(in srgb, var(--card) 82%, var(--bg) 18%);
      --heart: #6b8f3a;
      --shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* NAVBAR */
    .navbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      height: 100px;
      background: var(--bg);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--line);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 48px;
      gap: 32px;
    }

   .nav-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 255px;
  flex-shrink: 0;
}

.logo-img {
  width: 82px;
  height: 82px;
  object-fit: contain;
  display: block;
  background: transparent;
}
.brand-name {
  font-family: 'Manrope', sans-serif;
  font-size: 39px;
  font-weight: 800;
  letter-spacing: -1.5px;
  line-height: 1;
  color: #1d1d19;
}
    .nav-location {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 170px;
      font-size: 19px;
      font-weight: 600;
      color: #222;
      flex-shrink: 0;
    }

    .nav-location svg {
      width: 22px;
      height: 22px;
      fill: var(--accent);
      flex-shrink: 0;
    }

    .nav-search {
      flex: 1;
      display: flex;
      justify-content: center;
    }

    .search-box {
      width: 100%;
      max-width: 760px;
      height: 64px;
      background: #fbfaf7;
      border: 1.5px solid #ddd4c8;
      border-radius: 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 0 20px;
      transition: 0.25s ease;
    }

    .search-box:hover,
    .search-box:focus-within {
      border-color: #ccbfae;
      background: #fffdf9;
    }

    .search-box svg {
      width: 24px;
      height: 24px;
      stroke: #7a746b;
      stroke-width: 2.2;
      fill: none;
      flex-shrink: 0;
    }

    .search-box input {
      width: 100%;
      border: none;
      outline: none;
      background: transparent;
      font-size: 18px;
      color: var(--text);
    }

    .search-box input::placeholder {
      color: #857b70;
      font-size: 18px;
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 26px;
      min-width: 235px;
      justify-content: flex-end;
      flex-shrink: 0;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 19px;
      font-weight: 600;
      color: #1f1f1b;
      cursor: pointer;
      transition: 0.2s ease;
    }

    .nav-item:hover {
      color: var(--accent);
    }

    .nav-item svg {
      width: 23px;
      height: 23px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      flex-shrink: 0;
    }

    /* HERO */
    .hero {
  position: relative;
  height: 420px;
  background: url('images/hero.jpg') right center / cover no-repeat;
  display: flex;
  align-items: center;
}

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to right,
        rgba(0, 0, 0, 0.72) 0%,
        rgba(0, 0, 0, 0.55) 28%,
        rgba(0, 0, 0, 0.25) 52%,
        rgba(0, 0, 0, 0.05) 72%,
        rgba(0, 0, 0, 0) 100%
      );
    }

    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 620px;
      margin-left: 64px;
      color: white;
    }

    .hero-content h1 {
      font-family: 'Manrope', sans-serif;
      font-size: 58px;
      line-height: 1.05;
      font-weight: 800;
      letter-spacing: -1.2px;
      margin-bottom: 16px;
    }

    .hero-content p {
      font-size: 20px;
      line-height: 1.5;
      color: rgba(255, 255, 255, 0.92);
      max-width: 500px;
    }

    /* MAIN */
    .container {
      padding: 34px 48px 56px;
    }

    .section-title {
      font-size: 28px;
      font-weight: 700;
      color: #111;
      margin-bottom: 24px;
    }

    /* CATEGORIES */
    .categories {
      display: flex;
      gap: 22px;
      overflow-x: auto;
      padding-bottom: 12px;
      margin-bottom: 52px;
      scroll-behavior: smooth;
    }

    .categories::-webkit-scrollbar {
      display: none;
    }

    .category {
      min-width: 96px;
      text-align: center;
      cursor: pointer;
      flex-shrink: 0;
    }

    .category-circle {
      width: 84px;
      height: 84px;
      border-radius: 50%;
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .category:hover .category-circle {
      transform: translateY(-3px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    }

    .category-circle img {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
      }

    .category-circle img.all-category-image {
      object-position: center 28%;
      transform: scale(1.18);
    }

    .category p {
        font-size: 14px;
        font-weight: 600;
        color: #4a443d;
        line-height: 1.3;
      }

    .category.active .category-circle {
      border: 2px solid var(--accent);
      box-shadow: 0 10px 20px rgba(95, 124, 58, 0.16);
    }

    .category.active p {
      color: var(--accent);
    }

    /* RESTAURANTS */
    .restaurants {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 22px;
      margin-top: 10px;
      margin-bottom: 56px;
    }

    .card {
      background: linear-gradient(180deg, color-mix(in srgb, var(--card) 92%, var(--bg) 8%), var(--card));
      border: 1px solid var(--line);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--accent) 16%, var(--line) 84%), 0 10px 24px rgba(31, 31, 27, 0.07);
      transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, border-color 0.25s ease;
      cursor: pointer;
    }

    .card:hover {
      transform: translateY(-5px);
      background: linear-gradient(180deg, color-mix(in srgb, var(--highlight) 90%, var(--card) 10%), var(--card));
      border-color: color-mix(in srgb, var(--accent-soft) 52%, var(--line) 48%);
      box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--accent) 68%, var(--line) 32%), 0 18px 34px rgba(31, 31, 27, 0.12);
    }

    .card img {
      width: 100%;
      height: 190px;
      object-fit: cover;
      display: block;
      transition: transform 0.25s ease;
    }

    .card:hover img {
      transform: scale(1.05);
    }

    .card-content {
      padding: 14px 16px 18px;
    }

    .card h3 {
      font-size: 19px;
      font-weight: 700;
      margin-bottom: 8px;
      color: #171715;
    }

    .rating-time {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
      color: #55514c;
      margin-bottom: 6px;
    }

    .rating-time .star {
      color: #c19122;
      font-size: 15px;
    }

    .cuisine {
      font-size: 14px;
      color: #70695f;
      margin-bottom: 4px;
    }

    .location {
      font-size: 13px;
      color: #9a9185;
    }

    /* FOOTER */
    .footer {
     background: #ece5da;
      border-top: 1px solid var(--line);
      padding: 48px 48px 28px;
    }

    .footer-top {
      display: grid;
      grid-template-columns: 1.3fr 1fr 1fr 1fr;
      gap: 40px;
      margin-bottom: 28px;
    }

    .footer-brand {
      max-width: 300px;
    }

    .footer-brand-top {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 16px;
    }

    .footer-logo {
      width: 54px;
      height: 54px;
      object-fit: contain;
    }

    .footer-brand-name {
      font-family: 'Manrope', sans-serif;
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -0.8px;
      color: #1d1d19;
    }

    .footer-desc {
      font-size: 16px;
      line-height: 1.7;
      color: var(--muted);
    }

    .footer-col h4 {
      font-size: 19px;
      margin-bottom: 16px;
      color: #171715;
    }

    .footer-col a,
    .footer-col p {
      display: block;
      font-size: 16px;
      color: var(--muted);
      margin-bottom: 12px;
      line-height: 1.7;
    }

    .footer-socials {
      display: flex;
      gap: 14px;
      margin-top: 10px;
    }

    .footer-socials a {
      width: 44px;
      height: 44px;
      border-radius: 15px;
      background: #fffdfa;
      border: 1px solid #d8cebe;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6d8a3e;
      transition: transform 0.22s ease, border-color 0.22s ease, background-color 0.22s ease, color 0.22s ease, box-shadow 0.22s ease;
      box-shadow: 0 8px 18px rgba(31, 31, 27, 0.07);
    }

    .footer-socials a:hover {
      transform: translateY(-2px);
      border-color: #b7c993;
      background: #f7f2e9;
      color: #587527;
      box-shadow: 0 12px 22px rgba(31, 31, 27, 0.1);
    }

    .footer-socials svg {
      width: 20px;
      height: 20px;
      display: block;
      flex-shrink: 0;
    }

    .footer-socials .icon-instagram {
      width: 20px;
      height: 20px;
      stroke: currentColor;
      stroke-width: 2.1;
      fill: none;
    }

    .footer-socials .icon-facebook {
      fill: currentColor;
      stroke: none;
      width: 18px;
      height: 18px;
    }

    .footer-socials .icon-x {
      width: 18px;
      height: 18px;
      fill: currentColor;
      stroke: none;
    }

    .footer-bottom {
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      padding-top: 18px;
      margin-top: 14px;
      text-align: center;
    }

    .footer-copy {
      font-size: 15px;
      color: var(--muted);
      margin-bottom: 10px;
    }

    .made-by {
      font-size: 16px;
      color: #5b554e;
      font-weight: 600;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 6px;
    }

    .made-by .heart {
      color: var(--heart);
      font-size: 16px;
      line-height: 1;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
      .restaurants {
        grid-template-columns: repeat(3, 1fr);
      }

      .hero-content h1 {
        font-size: 50px;
      }

      .footer-top {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 900px) {
      .navbar {
        padding: 16px 20px;
        flex-wrap: wrap;
        height: auto;
      }

      .nav-left,
      .nav-location,
      .nav-right {
        min-width: auto;
      }

      .nav-search {
        width: 100%;
        order: 4;
      }

      .search-box {
        max-width: 700%;
      }

      .hero {
        height: 360px;
      }

      .hero-content {
        margin-left: 28px;
        max-width: 500px;
      }

      .hero-content h1 {
        font-size: 42px;
      }

      .hero-content p {
        font-size: 18px;
      }

      .container,
      .footer {
        padding-left: 20px;
        padding-right: 20px;
      }

      .restaurants {
        grid-template-columns: repeat(2, 1fr);
      }

      .footer-top {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 600px) {
      .hero {
        height: 300px;
      }

      .hero-content h1 {
        font-size: 34px;
      }

      .hero-content p {
        font-size: 15px;
      }

      .restaurants {
        grid-template-columns: 1fr;
      }

      .categories {
        gap: 18px;
      }

      .category {
        min-width: 90px;
      }

      .category-circle {
        width: 78px;
        height: 78px;
      }

      .category-circle img {
        width: 70px;
        height: 70px;
      }

      .category-circle img.all-category-image {
        transform: scale(1.16);
      }

      .brand-name {
        font-size: 34px;
      }

      .nav-right {
        gap: 18px;
      }

      .footer-top {
        grid-template-columns: 1fr;
      }

    }
    .logo-img {
  width: 72px;
  height: 72px;
  object-fit: contain;
  display: block;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
   .restaurant-card {
  transition: all 0.25s ease;
}

.restaurant-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 14px 30px rgba(0, 0, 0, 0.12);
} 
.category {
  transition: all 0.2s ease;
}

.category:hover {
  transform: translateY(-4px) scale(1.05);
}
.nav-right a {
  transition: all 0.2s ease;
}

.nav-right a:hover {
  color: var(--accent);
}
.footer a {
  transition: all 0.2s ease;
}

.footer a:hover {
  color: var(--accent);
}
.search-box {
  transition: all 0.2s ease;
}

.search-box:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.15);
}
.search-box {
  transition: all 0.2s ease;
}

.search-box:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.15);
} 
.restaurant-card {
  text-decoration: none;
  color: inherit;
  display: block;
}
.restaurant-card img {
    width: 100%;
    height: 190px;
    object-fit: cover;
    border-radius: 20px 20px 0 0;
    display: block;
    transition: transform 0.25s ease;
}
.restaurant-card {
    display: block;
    background: linear-gradient(180deg, color-mix(in srgb, var(--card) 92%, var(--bg) 8%), var(--card));
    border: 1px solid var(--line);
    border-radius: 20px;
    overflow: hidden;
    text-decoration: none;
    color: var(--text);
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--accent) 16%, var(--line) 84%), 0 10px 24px rgba(31, 31, 27, 0.07);
    transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, border-color 0.25s ease;
}
.restaurant-card:hover {
    transform: translateY(-5px);
    background: linear-gradient(180deg, color-mix(in srgb, var(--highlight) 90%, var(--card) 10%), var(--card));
    border-color: color-mix(in srgb, var(--accent-soft) 52%, var(--line) 48%);
    box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--accent) 68%, var(--line) 32%), 0 18px 34px rgba(31, 31, 27, 0.12);
}
.restaurant-card:hover img {
    transform: scale(1.05);
}
.restaurants {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.restaurant-card {
    min-width: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.restaurant-card img {
    background: #f6f1e8;
}

.restaurant-card .card-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.restaurant-card h3 {
    min-height: 48px;
}

.restaurant-card .cuisine {
    min-height: 44px;
}
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>
<?php include 'header.php'; ?>

  <!-- HERO -->
  <div class="hero">
    <div class="hero-content">
      <h1>Order from trusted restaurants near you</h1>
      <p>Browse menus, place your order, and track every step in one clean Cibo flow.</p>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="container">

    <h2 class="section-title">What's on your mind?</h2>

    <div class="categories">
      <div class="category active" data-category="all">
        <div class="category-circle">
          <img src="images/categories/all-category.png" alt="All" class="all-category-image">
        </div>
        <p>All</p>
      </div>

      <div class="category" data-category="north indian">
        <div class="category-circle">
          <img src="images/categories/north.jpg" alt="North Indian">
        </div>
        <p>North Indian</p>
      </div>

      <div class="category" data-category="south indian">
        <div class="category-circle">
          <img src="images/categories/south.jpg" alt="South Indian">
        </div>
        <p>South Indian</p>
      </div>

      <div class="category" data-category="desserts">
        <div class="category-circle">
          <img src="images/categories/dessert.jpg" alt="Desserts">
        </div>
        <p>Desserts</p>
      </div>

      <div class="category" data-category="biryani">
        <div class="category-circle">
          <img src="images/categories/biryani.jpg" alt="Biryani">
        </div>
        <p>Biryani</p>
      </div>

      <div class="category" data-category="chinese">
        <div class="category-circle">
          <img src="images/categories/chinese.jpg" alt="Chinese">
        </div>
        <p>Chinese</p>
      </div>

      <div class="category" data-category="pizza">
        <div class="category-circle">
          <img src="images/categories/pizza.jpg" alt="Pizza">
        </div>
        <p>Pizza</p>
      </div>

      <div class="category" data-category="burgers">
        <div class="category-circle">
          <img src="images/categories/burger.jpg" alt="Burgers">
        </div>
        <p>Burgers</p>
      </div>

      <div class="category" data-category="salad">
        <div class="category-circle">
          <img src="images/categories/salad.jpg" alt="Salad">
        </div>
        <p>Salad</p>
      </div>

      <div class="category" data-category="korean">
        <div class="category-circle">
          <img src="images/categories/korean.jpg" alt="Korean">
        </div>
        <p>Korean</p>
      </div>
    </div>

    <h2 class="section-title">Top restaurants near you</h2>

    <div class="restaurants">

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href("McDonald's"), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="burgers">
        <img src="images/restaurants/mcd.jpg" alt="McDonald's">
        <div class="card-content">
          <h3>McDonald's</h3>
          <div class="rating-time"><span class="star">★</span> 4.3 • 30-35 mins</div>
          <div class="cuisine">Burgers, Fast Food</div>
          <div class="location">JP Nagar</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Burger King'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="burgers">
        <img src="images/restaurants/burger-king.jpg" alt="Burger King">
        <div class="card-content">
          <h3>Burger King</h3>
          <div class="rating-time"><span class="star">★</span> 4.2 • 25-30 mins</div>
          <div class="cuisine">Burgers, American</div>
          <div class="location">BTM Layout</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href("Domino's"), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="pizza">
        <img src="images/restaurants/dominos.jpg" alt="Domino's">
        <div class="card-content">
          <h3>Domino's</h3>
          <div class="rating-time"><span class="star">★</span> 4.2 • 25-30 mins</div>
          <div class="cuisine">Pizza, Italian</div>
          <div class="location">Jayanagar</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Pizza Hut'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="pizza">
        <img src="images/restaurants/pizza-hut.jpg" alt="Pizza Hut">
        <div class="card-content">
          <h3>Pizza Hut</h3>
          <div class="rating-time"><span class="star">★</span> 4.1 • 30-35 mins</div>
          <div class="cuisine">Pizza, Italian</div>
          <div class="location">Banashankari</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Meghana Foods'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="biryani">
        <img src="images/restaurants/meghana.jpg" alt="Meghana Foods">
        <div class="card-content">
          <h3>Meghana Foods</h3>
          <div class="rating-time"><span class="star">★</span> 4.5 • 30-40 mins</div>
          <div class="cuisine">Biryani, Andhra</div>
          <div class="location">Koramangala</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Paradise'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="biryani">
        <img src="images/restaurants/paradise.jpg" alt="Paradise">
        <div class="card-content">
          <h3>Paradise</h3>
          <div class="rating-time"><span class="star">★</span> 4.3 • 30-40 mins</div>
          <div class="cuisine">Biryani, Hyderabadi</div>
          <div class="location">MG Road</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Chinese Wok'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="chinese">
        <img src="images/restaurants/chinese-wok.jpg" alt="Chinese Wok">
        <div class="card-content">
          <h3>Chinese Wok</h3>
          <div class="rating-time"><span class="star">★</span> 4.1 • 25-30 mins</div>
          <div class="cuisine">Chinese, Noodles</div>
          <div class="location">HSR Layout</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Mainland China'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="chinese">
        <img src="images/restaurants/mainland-china.jpg" alt="Mainland China">
        <div class="card-content">
          <h3>Mainland China</h3>
          <div class="rating-time"><span class="star">★</span> 4.4 • 35-40 mins</div>
          <div class="cuisine">Chinese, Asian</div>
          <div class="location">Indiranagar</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Empire'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="north indian">
        <img src="images/restaurants/empire.jpg" alt="Empire">
        <div class="card-content">
          <h3>Empire</h3>
          <div class="rating-time"><span class="star">★</span> 4.2 • 30-35 mins</div>
          <div class="cuisine">Grill, North Indian</div>
          <div class="location">Rajajinagar</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Punjab Grill'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="north indian">
        <img src="images/restaurants/punjab-grill.jpg" alt="Punjab Grill">
        <div class="card-content">
          <h3>Punjab Grill</h3>
          <div class="rating-time"><span class="star">★</span> 4.3 • 35-40 mins</div>
          <div class="cuisine">North Indian, Punjabi</div>
          <div class="location">Malleshwaram</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Udupi'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="south indian">
        <img src="images/restaurants/udupi.jpg" alt="Udupi">
        <div class="card-content">
          <h3>Udupi</h3>
          <div class="rating-time"><span class="star">★</span> 4.1 • 20-25 mins</div>
          <div class="cuisine">South Indian</div>
          <div class="location">Basavanagudi</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Vidyarthi Bhavan'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="south indian">
        <img src="images/restaurants/vidyarthi.jpg" alt="Vidyarthi Bhavan">
        <div class="card-content">
          <h3>Vidyarthi Bhavan</h3>
          <div class="rating-time"><span class="star">★</span> 4.6 • 20-25 mins</div>
          <div class="cuisine">Dosa, South Indian</div>
          <div class="location">Basavanagudi</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Polar Bear'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="desserts">
        <img src="images/restaurants/polar-bear.jpg" alt="Polar Bear">
        <div class="card-content">
          <h3>Polar Bear</h3>
          <div class="rating-time"><span class="star">★</span> 4.4 • 20-25 mins</div>
          <div class="cuisine">Ice Cream, Desserts</div>
          <div class="location">Jayanagar</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Corner House'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="desserts">
        <img src="images/restaurants/corner-house.jpg" alt="Corner House">
        <div class="card-content">
          <h3>Corner House</h3>
          <div class="rating-time"><span class="star">★</span> 4.5 • 20-25 mins</div>
          <div class="cuisine">Desserts, Sundaes</div>
          <div class="location">Indiranagar</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('FreshMenu'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="salad">
        <img src="images/restaurants/freshmenu.jpg" alt="FreshMenu">
        <div class="card-content">
          <h3>FreshMenu</h3>
          <div class="rating-time"><span class="star">★</span> 4.2 • 25-30 mins</div>
          <div class="cuisine">Healthy, Continental</div>
          <div class="location">Bellandur</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('EatFit'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="salad">
        <img src="images/restaurants/eatfit.jpg" alt="EatFit">
        <div class="card-content">
          <h3>EatFit</h3>
          <div class="rating-time"><span class="star">★</span> 4.3 • 25-30 mins</div>
          <div class="cuisine">Salads, Healthy Food</div>
          <div class="location">HSR Layout</div>
        </div>
      </a>

      <a href="<?= htmlspecialchars(cibo_homepage_restaurant_href('Hae Kum Gang'), ENT_QUOTES, 'UTF-8') ?>" class="restaurant-card" data-category="korean">
        <img src="images/restaurants/hae-kum-gang.jpg" alt="Hae Kum Gang">
        <div class="card-content">
          <h3>Hae Kum Gang</h3>
          <div class="rating-time"><span class="star">★</span> 4.2 • 35-40 mins</div>
          <div class="cuisine">Korean, Asian</div>
          <div class="location">Koramangala</div>
        </div>
      </a>

    </div>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-top">
      <div class="footer-brand">
        <div class="footer-brand-top">
          <img src="images/logo.png" class="footer-logo" alt="Cibo Logo">
          <div class="footer-brand-name">Cibo</div>
        </div>
        <p class="footer-desc">
          Discover delicious food from your favourite restaurants, all in one cozy and modern place.
        </p>
      </div>

      <div class="footer-col">
        <h4>Company</h4>
        <a href="#">About</a>
        <a href="#">Restaurants</a>
        <a href="#">Offers</a>
        <a href="#">Contact</a>
      </div>

      <div class="footer-col">
        <h4>Support</h4>
        <a href="#">Help Center</a>
        <a href="#">Terms & Conditions</a>
        <a href="#">Privacy Policy</a>
        <a href="#">FAQs</a>
      </div>

      <div class="footer-col">
        <h4>Connect</h4>
        <p>Bangalore, India</p>
        <p>support@cibo.com</p>
        <div class="footer-socials">
          <a href="#" aria-label="Facebook">
            <svg class="icon-facebook" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M13.62 21v-7.3h2.46l.37-2.85h-2.83V9.03c0-.83.23-1.4 1.42-1.4H16.2V5.08c-.2-.03-.9-.08-1.71-.08-1.7 0-2.87 1.04-2.87 2.95v1.9H9.7v2.85h1.92V21h2z"></path>
            </svg>
          </a>
          <a href="#" aria-label="Instagram">
            <svg class="icon-instagram" viewBox="0 0 24 24" aria-hidden="true">
              <rect x="4" y="4" width="16" height="16" rx="4.5"></rect>
              <circle cx="12" cy="12" r="3.6"></circle>
              <circle cx="17.1" cy="6.9" r="1.1" fill="currentColor" stroke="none"></circle>
            </svg>
          </a>
          <a href="#" aria-label="X">
            <svg class="icon-x" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M18.9 4H21l-4.58 5.24L21.8 20h-4.2l-3.28-6.43L8.7 20H6.6l4.9-5.6L6.2 4h4.31l2.96 5.91L18.9 4zm-1.47 14.37h1.16L9.88 5.56H8.64l8.79 12.81z"></path>
            </svg>
          </a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-copy">© 2026 Cibo. All rights reserved.</div>
      <div class="made-by">
        <span>Made by Nazima</span>
        <span class="heart">♥</span>
      </div>
    </div>
  </footer>
  <script src="auth-display.js"></script>
  <script src="cart-manager.js"></script>
  <script src="index.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

