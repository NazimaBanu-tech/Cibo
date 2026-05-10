<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Burger King - Cibo</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="menu.css">
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <!-- NAVBAR SAME -->
  <?php include 'header.php'; ?>

  <main class="menu-page">

    <!-- HERO -->
    <section class="restaurant-hero-card">
      <div class="restaurant-hero-left">

        <p class="restaurant-breadcrumb">
          <a href="index.php">Home</a> / Burger King
        </p>

        <h1>Burger King</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.4</span>
          <span>25–30 mins</span>
          <span>Burgers, Fast Food</span>
        </div>

        <p class="restaurant-address">BTM Layout</p>
        <p class="restaurant-offer">Free delivery above ₹199</p>

      </div>

      <div class="restaurant-hero-right">
        <img src="images/restaurant-heroes/burger-king-hero.jpg">
      </div>
    </section>

    <!-- MENU TITLE -->
    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <!-- CHIPS -->
    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="burgers">Burgers</button>
      <button class="chip" data-filter="fries">Fries</button>
      <button class="chip" data-filter="sides">Sides</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <!-- FOOD GRID -->
    <section class="food-grid">

      <!-- 1 -->
      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/whopper-burger.jpg">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Whopper Burger</h3>
          <p class="food-price">₹219</p>
          <p class="food-desc">Flame-grilled burger with fresh veggies and signature sauces.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 2 -->
      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/crispy-veg-burger.jpg">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Crispy Veg Burger</h3>
          <p class="food-price">₹169</p>
          <p class="food-desc">Crunchy veg patty with lettuce and creamy sauce.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 3 -->
      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/chicken-royale-burger.jpg">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Royale Burger</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">Juicy chicken patty burger with soft bun and mayo.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 4 -->
      <article class="food-big-card" data-category="fries">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/peri-peri-fries.jpg">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Peri Peri Fries</h3>
          <p class="food-price">₹119</p>
          <p class="food-desc">Crispy fries tossed in spicy peri peri seasoning.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 5 -->
      <article class="food-big-card" data-category="fries,sides">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/cheesy-loaded-fries.jpg">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Cheesy Loaded Fries</h3>
          <p class="food-price">₹149</p>
          <p class="food-desc">Loaded fries with creamy cheese topping.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 6 -->
      <article class="food-big-card" data-category="sides">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/veggie-nuggets.jpg">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veggie Nuggets</h3>
          <p class="food-price">₹139</p>
          <p class="food-desc">Crispy bite-sized nuggets perfect as a snack.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 7 -->
      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/chocolate-sundae.jpg">
        </div>
        <div class="food-card-body">
          <h3>Chocolate Sundae</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Creamy dessert topped with chocolate syrup.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <!-- 8 -->
      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/burger-king/mojito-lime-cooler.jpg">
        </div>
        <div class="food-card-body">
          <h3>Mojito Lime Cooler</h3>
          <p class="food-price">₹89</p>
          <p class="food-desc">Refreshing minty lime cooler drink.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

    </section>

  </main>

  <!-- FOOTER SAME -->
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

