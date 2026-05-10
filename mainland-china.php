<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mainland China - Cibo</title>
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
          <a href="index.php">Home</a> / Mainland China
        </p>

        <h1>Mainland China</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.5</span>
          <span>30–35 mins</span>
          <span>Chinese, Asian</span>
        </div>

        <p class="restaurant-address">Indiranagar</p>
        <p class="restaurant-offer">Free delivery on orders above ₹249</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/mainland-china-hero.jpg" alt="Mainland China hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="soups">Soups</button>
      <button class="chip" data-filter="starters">Starters</button>
      <button class="chip" data-filter="chicken">Chicken</button>
      <button class="chip" data-filter="noodles">Noodles</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="seafood">Seafood</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="soups,chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/chicken-manchow-soup.jpg" alt="Chicken Manchow Soup">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Manchow Soup</h3>
          <p class="food-price">₹189</p>
          <p class="food-desc">Hot and flavorful chicken soup with vegetables and crunchy toppings.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="starters">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/veg-spring-rolls.jpg" alt="Veg Spring Rolls">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Spring Rolls</h3>
          <p class="food-price">₹179</p>
          <p class="food-desc">Crispy rolls stuffed with seasoned vegetables and served as a light starter.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/kung-pao-chicken.jpg" alt="Kung Pao Chicken">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Kung Pao Chicken</h3>
          <p class="food-price">₹289</p>
          <p class="food-desc">Spicy stir-fried chicken tossed with peppers and rich Chinese sauces.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="noodles">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/schezwan-noodles.jpg" alt="Schezwan Noodles">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Schezwan Noodles</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">Wok-tossed noodles in spicy schezwan sauce with vegetables.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/veg-fried-rice.jpg" alt="Veg Fried Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Fried Rice</h3>
          <p class="food-price">₹219</p>
          <p class="food-desc">Classic fried rice tossed with fresh vegetables and Chinese seasoning.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="seafood">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/chili-garlic-prawns.jpg" alt="Chili Garlic Prawns">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chili Garlic Prawns</h3>
          <p class="food-price">₹319</p>
          <p class="food-desc">Juicy prawns cooked with chili, garlic and savory oriental flavors.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/sesame-balls.jpg" alt="Sesame Balls">
        </div>
        <div class="food-card-body">
          <h3>Sesame Balls</h3>
          <p class="food-price">₹149</p>
          <p class="food-desc">Crispy golden sesame balls filled with sweet paste, a classic Chinese dessert.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/mainland-china/jasmine-tea.jpg" alt="Jasmine Tea">
        </div>
        <div class="food-card-body">
          <h3>Jasmine Tea</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Light and aromatic jasmine tea that pairs perfectly with Chinese meals.</p>
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

