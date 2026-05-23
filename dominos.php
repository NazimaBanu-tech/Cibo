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
  <title>Domino's - Cibo</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="menu.css">
  <link rel="stylesheet" href="global.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="menu-page">

<!-- HERO -->
<section class="restaurant-hero-card">

  <div class="restaurant-hero-left">
    <p class="restaurant-breadcrumb">
      <a href="index.php">Home</a> / Domino's
    </p>

    <h1>Domino's</h1>

    <div class="restaurant-meta">
      <span>⭐ 4.2</span>
      <span>25–30 mins</span>
      <span>Pizza, Italian</span>
    </div>

    <p class="restaurant-address">Jayanagar</p>

    <p class="restaurant-offer">
      Free delivery on orders above ₹199
    </p>
  </div>

  <div class="restaurant-hero-right">
    <img src="images/restaurant-heroes/dominos-hero.jpg" alt="Domino's">
  </div>

</section>

<!-- MENU -->
<section class="menu-topbar">
  <h2>Menu</h2>
</section>

<section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="pizza">Pizza</button>
      <button class="chip" data-filter="sides">Sides</button>
      <button class="chip" data-filter="garlic-bread">Garlic Bread</button>
      <button class="chip" data-filter="drinks">Drinks</button>
      <button class="chip" data-filter="desserts">Desserts</button>
</section>

<section class="food-grid">

      <article class="food-big-card" data-category="pizza">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/margherita.jpg">
    </div>
    <div class="food-card-body">
      <span class="food-tag veg">● Veg</span>
      <h3>Margherita Pizza</h3>
      <p class="food-price">₹199</p>
      <p class="food-desc">Classic cheese pizza with rich tomato sauce.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="pizza">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/farmhouse.jpg">
    </div>
    <div class="food-card-body">
      <span class="food-tag veg">● Veg</span>
      <h3>Farmhouse Pizza</h3>
      <p class="food-price">₹299</p>
      <p class="food-desc">Loaded with veggies like capsicum, onion and tomato.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="pizza">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/veg-extravaganza.jpg">
    </div>
    <div class="food-card-body">
      <span class="food-tag veg">● Veg</span>
      <h3>Veg Extravaganza</h3>
      <p class="food-price">₹349</p>
      <p class="food-desc">Premium loaded pizza with exotic vegetables.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="pizza">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/pepper-bbq-chicken.jpg">
    </div>
    <div class="food-card-body">
      <span class="food-tag nonveg">● Non-Veg</span>
      <h3>Pepper BBQ Chicken</h3>
      <p class="food-price">₹399</p>
      <p class="food-desc">Spicy BBQ chicken with cheese loaded crust.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="garlic-bread,sides">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/garlic-breadsticks.jpg">
    </div>
    <div class="food-card-body">
      <span class="food-tag veg">● Veg</span>
      <h3>Garlic Breadsticks</h3>
      <p class="food-price">₹149</p>
      <p class="food-desc">Freshly baked bread with garlic and herbs.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="garlic-bread,sides">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/stuffed-garlic-bread.jpg">
    </div>
    <div class="food-card-body">
      <span class="food-tag veg">● Veg</span>
      <h3>Stuffed Garlic Bread</h3>
      <p class="food-price">₹179</p>
      <p class="food-desc">Cheesy stuffed bread with herbs and seasoning.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="drinks">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/pepsi.jpg">
    </div>
    <div class="food-card-body">
      <h3>Pepsi</h3>
      <p class="food-price">₹60</p>
      <p class="food-desc">Chilled soft drink.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

      <article class="food-big-card" data-category="desserts">
    <div class="food-image-wrap">
      <img src="images/food-items/dominos/choco-lava-cake.jpg">
    </div>
    <div class="food-card-body">
      <h3>Choco Lava Cake</h3>
      <p class="food-price">₹99</p>
      <p class="food-desc">Warm chocolate cake with molten center.</p>
      <button class="food-btn">Add</button>
    </div>
  </article>

</section>

</main>

<!-- ✅ ONLY ONE FOOTER -->
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

