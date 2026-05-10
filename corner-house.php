<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Corner House - Cibo</title>
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
          <a href="index.php">Home</a> / Corner House
        </p>

        <h1>Corner House</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.5</span>
          <span>20–25 mins</span>
          <span>Desserts, Ice Cream</span>
        </div>

        <p class="restaurant-address">Indiranagar</p>
        <p class="restaurant-offer">Free delivery on orders above ₹149</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/corner-house-hero.jpg" alt="Corner House hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="sundaes">Sundaes</button>
      <button class="chip" data-filter="ice-cream">Ice Cream</button>
      <button class="chip" data-filter="brownies">Brownies</button>
      <button class="chip" data-filter="milkshakes">Milkshakes</button>
      <button class="chip" data-filter="drinks">Drinks</button>
      <button class="chip" data-filter="desserts">Desserts</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="desserts,sundaes">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/death-by-chocolate.jpg" alt="Death By Chocolate">
        </div>
        <div class="food-card-body">
          <h3>Death By Chocolate</h3>
          <p class="food-price">₹189</p>
          <p class="food-desc">Signature layered chocolate dessert loaded with ice cream and rich fudge.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts,sundaes">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/hot-chocolate-fudge.jpg" alt="Hot Chocolate Fudge">
        </div>
        <div class="food-card-body">
          <h3>Hot Chocolate Fudge</h3>
          <p class="food-price">₹179</p>
          <p class="food-desc">Warm chocolate sauce poured over vanilla ice cream and brownie.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="sundaes">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/caramel-sundae.jpg" alt="Caramel Sundae">
        </div>
        <div class="food-card-body">
          <h3>Caramel Sundae</h3>
          <p class="food-price">₹159</p>
          <p class="food-desc">Creamy ice cream topped with rich caramel drizzle and crunchy bits.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="brownies,desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/brownie-with-ice-cream.jpg" alt="Brownie With Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Brownie With Ice Cream</h3>
          <p class="food-price">₹169</p>
          <p class="food-desc">Warm brownie served with a scoop of vanilla ice cream.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="ice-cream">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/vanilla-ice-cream.jpg" alt="Vanilla Ice Cream">
        </div>
        <div class="food-card-body">
          <h3>Vanilla Ice Cream</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Classic creamy vanilla ice cream loved by all.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="milkshakes,drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/strawberry-milkshake.jpg" alt="Strawberry Milkshake">
        </div>
        <div class="food-card-body">
          <h3>Strawberry Milkshake</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Sweet strawberry blended milkshake with a smooth texture.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="milkshakes,drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/chocolate-milkshake.jpg" alt="Chocolate Milkshake">
        </div>
        <div class="food-card-body">
          <h3>Chocolate Milkshake</h3>
          <p class="food-price">₹139</p>
          <p class="food-desc">Rich chocolate milkshake topped with creamy goodness.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/corner-house/cold-coffee.jpg" alt="Cold Coffee">
        </div>
        <div class="food-card-body">
          <h3>Cold Coffee</h3>
          <p class="food-price">₹119</p>
          <p class="food-desc">Chilled coffee blended with milk and ice for a refreshing drink.</p>
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
  <script src="menu.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

