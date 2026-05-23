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
  <title>Polar Bear - Cibo</title>
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
          <a href="index.php">Home</a> / Polar Bear
        </p>

        <h1>Polar Bear</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.5</span>
          <span>20–25 mins</span>
          <span>Ice Cream, Desserts</span>
        </div>

        <p class="restaurant-address">Jayanagar</p>
        <p class="restaurant-offer">Free delivery on orders above ₹149</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/polar-bear-hero.jpg" alt="Polar Bear hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="ice-cream">Ice Cream</button>
      <button class="chip" data-filter="sundaes">Sundaes</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
      <button class="chip" data-filter="specials">Specials</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="ice-cream">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/chocolate-ice-cream.jpg" alt="Chocolate Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Chocolate Ice Cream</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Rich and creamy chocolate ice cream for a classic dessert treat.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="ice-cream">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/butterscotch-ice-cream.jpg" alt="Butterscotch Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Butterscotch Ice Cream</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Creamy butterscotch ice cream with sweet caramel flavor and crunch.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="ice-cream">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/strawberry-ice-cream.jpg" alt="Strawberry Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Strawberry Ice Cream</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Sweet and fruity strawberry ice cream with a smooth refreshing taste.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="sundaes,desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/chocolate-sundae.jpg" alt="Chocolate Sundae">
        </div>
        <div class="food-card-body">
          <h3>Chocolate Sundae</h3>
          <p class="food-price">₹149</p>
          <p class="food-desc">Delicious sundae layered with rich chocolate sauce and creamy ice cream.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts,specials">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/brownie-with-ice-cream.jpg" alt="Brownie with Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Brownie with Ice Cream</h3>
          <p class="food-price">₹179</p>
          <p class="food-desc">Warm brownie served with a scoop of creamy ice cream and dessert sauce.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="specials,sundaes">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/banana-split.jpg" alt="Banana Split">
        </div>
        <div class="food-card-body">
          <h3>Banana Split</h3>
          <p class="food-price">₹169</p>
          <p class="food-desc">Classic banana split topped with ice cream, syrup and sweet crunchy toppings.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="ice-cream">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/mango-ice-cream.jpg" alt="Mango Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Mango Ice Cream</h3>
          <p class="food-price">₹109</p>
          <p class="food-desc">Refreshing mango ice cream with fruity flavor and creamy texture.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/polar-bear/cold-coffee.jpg" alt="Cold Coffee">
        </div>
        <div class="food-card-body">
          <h3>Cold Coffee</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Chilled coffee blended smooth for a refreshing cafe-style dessert drink.</p>
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

