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
  <title>Paradise - Cibo</title>
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
          <a href="index.php">Home</a> / Paradise
        </p>

        <h1>Paradise</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.5</span>
          <span>30–35 mins</span>
          <span>Biryani, Hyderabadi</span>
        </div>

        <p class="restaurant-address">MG Road</p>
        <p class="restaurant-offer">Free delivery on orders above ₹249</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/paradise-hero.jpg" alt="Paradise hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="biryani">Biryani</button>
      <button class="chip" data-filter="curries">Curries</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
      <button class="chip" data-filter="specials">Specials</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="biryani">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/hyderabadi-chicken-dum-biryani.jpg" alt="Hyderabadi Chicken Dum Biryani">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Hyderabadi Chicken Dum Biryani</h3>
          <p class="food-price">₹299</p>
          <p class="food-desc">Authentic dum biryani cooked with aromatic spices and tender chicken.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="specials">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/mutton-haleem.jpg" alt="Mutton Haleem">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Mutton Haleem</h3>
          <p class="food-price">₹279</p>
          <p class="food-desc">Slow-cooked mutton stew with wheat and spices, rich and flavorful.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="curries">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/paneer-butter-masala.jpg" alt="Paneer Butter Masala">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Paneer Butter Masala</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Creamy paneer curry cooked in rich tomato gravy.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="curries">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/chicken-korma.jpg" alt="Chicken Korma">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Korma</h3>
          <p class="food-price">₹269</p>
          <p class="food-desc">Mild and creamy chicken curry with traditional spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="biryani">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/egg-biryani.jpg" alt="Egg Biryani">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Egg Biryani</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">Flavorful biryani made with boiled eggs and fragrant rice.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/veg-fried-rice.jpg" alt="Veg Fried Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Fried Rice</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Classic Indo-Chinese rice tossed with vegetables and light seasoning.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/qubani-ka-meetha.jpg" alt="Qubani Ka Meetha">
        </div>
        <div class="food-card-body">
          <h3>Qubani Ka Meetha</h3>
          <p class="food-price">₹149</p>
          <p class="food-desc">Traditional Hyderabadi dessert made from dried apricots.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/paradise/sweet-lassi.jpg" alt="Sweet Lassi">
        </div>
        <div class="food-card-body">
          <h3>Sweet Lassi</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Refreshing sweet yogurt drink that pairs well with spicy dishes.</p>
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

