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
  <title>Meghana - Cibo</title>
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
          <a href="index.php">Home</a> / Meghana
        </p>

        <h1>Meghana Foods</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.5</span>
          <span>30–35 mins</span>
          <span>Biryani, Andhra, North Indian</span>
        </div>

        <p class="restaurant-address">Koramangala</p>
        <p class="restaurant-offer">Free delivery on orders above ₹249</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/meghana-hero.jpg" alt="Meghana hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="curries">Curries</button>
      <button class="chip" data-filter="biryani">Biryani</button>
      <button class="chip" data-filter="starters">Starters</button>
      <button class="chip" data-filter="meals">Meals</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="curries">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/andhra-chicken-curry.jpg" alt="Andhra Chicken Curry">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Andhra Chicken Curry</h3>
          <p class="food-price">₹279</p>
          <p class="food-desc">Spicy Andhra-style chicken curry made with rich masala and bold flavours.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="biryani">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/chicken-fry-piece-biryani.jpg" alt="Chicken Fry Piece Biryani">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Fry Piece Biryani</h3>
          <p class="food-price">₹299</p>
          <p class="food-desc">Flavorful biryani served with spicy fried chicken pieces and aromatic rice.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="biryani">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/paneer-biryani.jpg" alt="Paneer Biryani">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Paneer Biryani</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Aromatic biryani rice cooked with paneer cubes and traditional spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="starters">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/apollo-fish.jpg" alt="Apollo Fish">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Apollo Fish</h3>
          <p class="food-price">₹289</p>
          <p class="food-desc">Spicy fried fish tossed in South Indian style masala for a fiery starter.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="starters">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/chicken-65.jpg" alt="Chicken 65">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken 65</h3>
          <p class="food-price">₹239</p>
          <p class="food-desc">Crispy spicy chicken bites tossed with curry leaves and bold seasoning.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="meals">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/veg-meals.jpg" alt="Veg Meals">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Meals</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Traditional South Indian veg meals served with rice, curry and sides.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/double-ka-meetha.jpg" alt="Double Ka Meetha">
        </div>
        <div class="food-card-body">
          <h3>Double Ka Meetha</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Classic Hyderabad-style bread dessert soaked in sweet rich syrup.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/meghana/buttermilk.jpg" alt="Buttermilk">
        </div>
        <div class="food-card-body">
          <h3>Buttermilk</h3>
          <p class="food-price">₹69</p>
          <p class="food-desc">Cool and refreshing buttermilk that pairs perfectly with spicy meals.</p>
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

