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
  <title>Pizza Hut - Cibo</title>
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
          <a href="index.php">Home</a> / Pizza Hut
        </p>

        <h1>Pizza Hut</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.2</span>
          <span>25–30 mins</span>
          <span>Pizzas, Italian</span>
        </div>

        <p class="restaurant-address">Banashankari</p>
        <p class="restaurant-offer">Free delivery on orders above ₹199</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/pizza-hut-hero.jpg" alt="Pizza Hut hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="pizzas">Pizzas</button>
      <button class="chip" data-filter="sides">Sides</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="pizzas">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/Margherita Pizza.jpg" alt="Margherita Pizza">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Margherita Pizza</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Classic cheese pizza with rich tomato sauce and a soft baked crust.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="pizzas">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/Veggie Supreme Pizza.jpg" alt="Veggie Supreme Pizza">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veggie Supreme Pizza</h3>
          <p class="food-price">₹289</p>
          <p class="food-desc">Loaded with fresh vegetables, cheese and flavourful pizza sauce.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="pizzas">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/Chicken Supreme pizza.jpg" alt="Chicken Supreme Pizza">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Supreme Pizza</h3>
          <p class="food-price">₹349</p>
          <p class="food-desc">Loaded with juicy chicken, veggies and melted cheese on a crispy base.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="pizzas">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/Tandoori Paneer Pizza.jpg" alt="Tandoori Paneer Pizza">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Tandoori Paneer Pizza</h3>
          <p class="food-price">₹329</p>
          <p class="food-desc">Paneer chunks with smoky tandoori flavour, onions and cheesy topping.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="pizzas">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/Chicken Tikka pizza.jpg" alt="Chicken Tikka Pizza">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Tikka Pizza</h3>
          <p class="food-price">₹359</p>
          <p class="food-desc">Spicy chicken tikka pizza with rich cheese and Indian-style flavours.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="sides">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/garlic-bread-cheese.jpg" alt="Garlic Bread with Cheese">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Garlic Bread with Cheese</h3>
          <p class="food-price">₹159</p>
          <p class="food-desc">Crispy garlic bread topped with melted cheese, perfect as a side.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/choco-chip-cookie.jpg" alt="Choco Chip Cookie">
        </div>
        <div class="food-card-body">
          <h3>Choco Chip Cookie</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Warm and soft cookie loaded with delicious chocolate chips.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/pizza-hut/iced-tea.jpg" alt="Iced Tea">
        </div>
        <div class="food-card-body">
          <h3>Iced Tea</h3>
          <p class="food-price">₹79</p>
          <p class="food-desc">Refreshing chilled iced tea to pair with your pizza meal.</p>
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

