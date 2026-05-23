<?php

declare(strict_types=1);

header('Location: index.php', true, 302);
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chinese Wok - Cibo</title>
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
          <a href="index.php">Home</a> / Chinese Wok
        </p>

        <h1>Chinese Wok</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.2</span>
          <span>30–35 mins</span>
          <span>Chinese, Asian, Fast Food</span>
        </div>

        <p class="restaurant-address">HSR Layout</p>
        <p class="restaurant-offer">Free delivery on orders above ₹199</p>
      </div>

      <div class="restaurant-hero-right">
        <img src="images/restaurant-heroes/chinese-wok-hero.jpg" alt="Chinese Wok hero image">
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="noodles">Noodles</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="chicken">Chicken</button>
      <button class="chip" data-filter="paneer">Paneer</button>
      <button class="chip" data-filter="snacks">Snacks</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="noodles,chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/chicken-hakka-noodles.jpg" alt="Chicken Hakka Noodles">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Hakka Noodles</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">Wok-tossed noodles with tender chicken, vegetables and classic Chinese seasoning.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/veg-fried-rice.jpg" alt="Veg Fried Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Fried Rice</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Fluffy rice stir-fried with fresh vegetables, sauces and aromatic seasonings.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/chilli-chicken.jpg" alt="Chilli Chicken">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chilli Chicken</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Spicy and flavorful chicken tossed with capsicum, onion and Chinese sauces.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="paneer">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/paneer-manchurian.jpg" alt="Paneer Manchurian">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Paneer Manchurian</h3>
          <p class="food-price">₹219</p>
          <p class="food-desc">Crispy paneer cubes coated in a rich Indo-Chinese sauce with crunchy veggies.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="snacks,chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/chicken-spring-roll.jpg" alt="Chicken Spring Roll">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Spring Roll</h3>
          <p class="food-price">₹179</p>
          <p class="food-desc">Crispy golden rolls stuffed with juicy chicken and savory oriental flavours.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="snacks">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/veg-momos.jpg" alt="Veg Momos">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Momos</h3>
          <p class="food-price">₹169</p>
          <p class="food-desc">Soft steamed dumplings filled with seasoned vegetables and served as a light snack.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/chocolate-brownie.jpg" alt="Chocolate Brownie">
        </div>
        <div class="food-card-body">
          <h3>Chocolate Brownie</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Rich and fudgy chocolate brownie for a sweet finish after your meal.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/chinese-wok/peach-iced-tea.jpg" alt="Peach Iced Tea">
        </div>
        <div class="food-card-body">
          <h3>Peach Iced Tea</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Refreshing chilled iced tea with a sweet peach flavour to complement your meal.</p>
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

