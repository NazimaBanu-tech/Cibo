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
  <title>Empire - Cibo</title>
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
          <a href="index.php">Home</a> / Empire
        </p>

        <h1>Empire</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.3</span>
          <span>30–35 mins</span>
          <span>Biryani, North Indian</span>
        </div>

        <p class="restaurant-address">Rajajinagar</p>
        <p class="restaurant-offer">Free delivery on orders above ₹199</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/empire-hero.jpg" alt="Empire hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="biryani">Biryani</button>
      <button class="chip" data-filter="chicken">Chicken</button>
      <button class="chip" data-filter="tandoor">Tandoor</button>
      <button class="chip" data-filter="rolls">Rolls</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="biryani">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/chicken-biryani.jpg" alt="Chicken Biryani">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Biryani</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Aromatic basmati rice cooked with juicy chicken and rich spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="biryani">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/mutton-biryani.jpg" alt="Mutton Biryani">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Mutton Biryani</h3>
          <p class="food-price">₹299</p>
          <p class="food-desc">Flavorful mutton cooked with basmati rice and authentic spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/butter-chicken.jpg" alt="Butter Chicken">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Butter Chicken</h3>
          <p class="food-price">₹279</p>
          <p class="food-desc">Creamy tomato-based curry with tender chicken pieces.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/chicken-kebab.jpg" alt="Chicken Kebab">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Kebab</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Juicy grilled chicken kebabs with smoky flavor.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="tandoor">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/tandoori-chicken.jpg" alt="Tandoori Chicken">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Tandoori Chicken</h3>
          <p class="food-price">₹259</p>
          <p class="food-desc">Classic tandoori chicken marinated and roasted to perfection.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rolls">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/chicken-shawarma.jpg" alt="Chicken Shawarma">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Shawarma</h3>
          <p class="food-price">₹179</p>
          <p class="food-desc">Soft wrap filled with spiced chicken and creamy sauce.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/gulab-jamun.jpg" alt="Gulab Jamun">
        </div>
        <div class="food-card-body">
          <h3>Gulab Jamun</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Soft and sweet syrup-soaked dessert balls.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/empire/lassi.jpg" alt="Lassi">
        </div>
        <div class="food-card-body">
          <h3>Lassi</h3>
          <p class="food-price">₹79</p>
          <p class="food-desc">Refreshing yogurt-based drink, perfect with spicy meals.</p>
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

