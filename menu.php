<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>McDonald's - Cibo</title>
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
          <a href="index.php">Home</a> / McDonald's
        </p>

        <h1>McDonald's</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.3</span>
          <span>25–30 mins</span>
          <span>Burgers, Fast Food</span>
        </div>

        <p class="restaurant-address">JP Nagar</p>
        <p class="restaurant-offer">Free delivery on orders above ₹199</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/mcd-hero.jpg" alt="McDonald's hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="burgers">Burgers</button>
      <button class="chip" data-filter="fries">Fries</button>
      <button class="chip" data-filter="nuggets">Nuggets</button>
      <button class="chip" data-filter="wraps">Wraps</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
      <button class="chip" data-filter="combos">Combos</button>
    </section>
    <section class="food-grid">

      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/burger.jpg" alt="Chicken Burger">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Burger</h3>
          <p class="food-price">₹139</p>
          <p class="food-desc">Juicy chicken patty with cheese, lettuce and a soft burger bun.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="burgers">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/mcaloo.jpg" alt="McAloo Tikki">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>McAloo Tikki</h3>
          <p class="food-price">₹79</p>
          <p class="food-desc">Classic crispy aloo patty burger with fresh onions and sauce.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="fries">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/fries.jpg" alt="French Fries">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>French Fries</h3>
          <p class="food-price">₹99</p>
          <p class="food-desc">Crispy golden fries with light seasoning, perfect as a side.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="nuggets">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/nuggets.jpg" alt="Chicken Nuggets">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Nuggets</h3>
          <p class="food-price">₹159</p>
          <p class="food-desc">Crunchy chicken nuggets served with a tasty dip.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="wraps">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/wrap.jpg" alt="Chicken Wrap">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Wrap</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Soft wrap filled with chicken, sauce and fresh vegetables.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/coke.jpg" alt="Coke">
        </div>
        <div class="food-card-body">
          <h3>Coke</h3>
          <p class="food-price">₹60</p>
          <p class="food-desc">Refreshing chilled coke to pair with your meal.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/mcflurry.jpg" alt="McFlurry">
        </div>
        <div class="food-card-body">
          <h3>McFlurry</h3>
          <p class="food-price">₹119</p>
          <p class="food-desc">Creamy dessert topped with delicious chocolate mix.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="combos">
        <div class="food-image-wrap">
          <img src="images/food-items/mcdonalds/combo.jpg" alt="Burger Combo">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Burger Combo</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Chicken burger served with fries and a chilled drink.</p>
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
