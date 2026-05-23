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
  <title>Punjab Grill - Cibo</title>
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
          <a href="index.php">Home</a> / Punjab Grill
        </p>

        <h1>Punjab Grill</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.4</span>
          <span>30–35 mins</span>
          <span>North Indian, Punjabi</span>
        </div>

        <p class="restaurant-address">Malleshwaram</p>
        <p class="restaurant-offer">Free delivery on orders above ₹249</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/punjab-grill-hero.jpg" alt="Punjab Grill hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="starters">Starters</button>
      <button class="chip" data-filter="curries">Curries</button>
      <button class="chip" data-filter="breads">Breads</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="starters">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/amritsari-fish-tikka.jpg" alt="Amritsari Fish Tikka">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Amritsari Fish Tikka</h3>
          <p class="food-price">₹299</p>
          <p class="food-desc">Crispy and flavorful Punjabi-style fish tikka with bold spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="curries">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/dal-makhani.jpg" alt="Dal Makhani">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Dal Makhani</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">Slow-cooked black lentils in a creamy buttery Punjabi gravy.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="starters">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/paneer-tikka.jpg" alt="Paneer Tikka">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Paneer Tikka</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Smoky grilled paneer cubes marinated in spiced yogurt.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="breads">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/butter-naan.jpg" alt="Butter Naan">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Butter Naan</h3>
          <p class="food-price">₹69</p>
          <p class="food-desc">Soft naan brushed with butter, perfect with rich curries.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="curries">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/chicken-tikka-masala.jpg" alt="Chicken Tikka Masala">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Tikka Masala</h3>
          <p class="food-price">₹279</p>
          <p class="food-desc">Tender chicken tikka cooked in a creamy tomato gravy.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/jeera-rice.jpg" alt="Jeera Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Jeera Rice</h3>
          <p class="food-price">₹149</p>
          <p class="food-desc">Fragrant basmati rice tempered with cumin and light spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/phirni.jpg" alt="Phirni">
        </div>
        <div class="food-card-body">
          <h3>Phirni</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Traditional Punjabi rice pudding served chilled and creamy.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/punjab-grill/sweet-lime-soda.jpg" alt="Sweet Lime Soda">
        </div>
        <div class="food-card-body">
          <h3>Sweet Lime Soda</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Refreshing sparkling lime drink with a sweet citrus taste.</p>
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

