<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FreshMenu - Cibo</title>
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
          <a href="index.php">Home</a> / FreshMenu
        </p>

        <h1>FreshMenu</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.2</span>
          <span>25–30 mins</span>
          <span>Healthy, Continental</span>
        </div>

        <p class="restaurant-address">Bellandur</p>
        <p class="restaurant-offer">Free delivery on orders above ₹199</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/freshmenu-hero.jpg" alt="FreshMenu hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="salads">Salads</button>
      <button class="chip" data-filter="bowls">Bowls</button>
      <button class="chip" data-filter="wraps">Wraps</button>
      <button class="chip" data-filter="sandwiches">Sandwiches</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="salads">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/chicken-avocado-salad.jpg" alt="Chicken Avocado Salad">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Avocado Salad</h3>
          <p class="food-price">₹259</p>
          <p class="food-desc">Fresh greens topped with grilled chicken, avocado, cherry tomatoes and seeds.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="salads">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/mediterranean-veg-salad.jpg" alt="Mediterranean Veg Salad">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Mediterranean Veg Salad</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">A vibrant mix of lettuce, olives, cucumber, tomatoes and fresh Mediterranean flavours.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="wraps">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/smoked-chicken-wrap.jpg" alt="Smoked Chicken Wrap">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Smoked Chicken Wrap</h3>
          <p class="food-price">₹219</p>
          <p class="food-desc">Soft wrap loaded with smoked chicken, crunchy veggies and creamy dressing.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="sandwiches">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/pesto-paneer-sandwich.jpg" alt="Pesto Paneer Sandwich">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Pesto Paneer Sandwich</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Grilled sandwich filled with paneer, fresh vegetables and a flavorful pesto spread.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="bowls">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/caesar-chicken-bowl.jpg" alt="Caesar Chicken Bowl">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Caesar Chicken Bowl</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">A hearty bowl with chicken, crisp lettuce, crunchy toppings and creamy Caesar dressing.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="bowls">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/roasted-veggie-bowl.jpg" alt="Roasted Veggie Bowl">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Roasted Veggie Bowl</h3>
          <p class="food-price">₹219</p>
          <p class="food-desc">A wholesome bowl of roasted vegetables, greens and grains with a fresh dressing.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/mango-yogurt-parfait.jpg" alt="Mango Yogurt Parfait">
        </div>
        <div class="food-card-body">
          <h3>Mango Yogurt Parfait</h3>
          <p class="food-price">₹139</p>
          <p class="food-desc">Layered mango yogurt parfait with fruit and crunchy toppings for a light dessert.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/freshmenu/watermelon-cooler.jpg" alt="Watermelon Cooler">
        </div>
        <div class="food-card-body">
          <h3>Watermelon Cooler</h3>
          <p class="food-price">₹109</p>
          <p class="food-desc">Refreshing watermelon-based cooler that pairs perfectly with fresh light meals.</p>
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

