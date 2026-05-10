<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hae Kum Gang - Cibo</title>
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
          <a href="index.php">Home</a> / Hae Kum Gang
        </p>

        <h1>Hae Kum Gang</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.4</span>
          <span>30–35 mins</span>
          <span>Korean, Asian</span>
        </div>

        <p class="restaurant-address">Koramangala</p>
        <p class="restaurant-offer">Free delivery on orders above ₹249</p>
      </div>

      <div class="restaurant-hero-right">
       <img src="images/restaurant-heroes/hae-kum-gang-hero.jpg" alt="Hae Kum Gang hero image"> 
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="rice-bowls">Rice Bowls</button>
      <button class="chip" data-filter="chicken">Chicken</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="rolls">Rolls</button>
      <button class="chip" data-filter="noodles">Noodles</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="drinks">Drinks</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="rice-bowls">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/bibimbap.jpg" alt="Bibimbap">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Bibimbap</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Korean rice bowl with vegetables, sauces and authentic flavours.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/chicken-bulgogi.jpg" alt="Chicken Bulgogi">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Chicken Bulgogi</h3>
          <p class="food-price">₹269</p>
          <p class="food-desc">Sweet and savory Korean marinated chicken grilled to perfection.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/chocolate-mochi.jpg" alt="Chocolate Mochi">
        </div>
        <div class="food-card-body">
          <h3>Chocolate Mochi</h3>
          <p class="food-price">₹149</p>
          <p class="food-desc">Soft and chewy dessert with rich chocolate filling.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/kimchi-fried-rice.jpg" alt="Kimchi Fried Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Kimchi Fried Rice</h3>
          <p class="food-price">₹229</p>
          <p class="food-desc">Spicy fried rice tossed with kimchi and Korean seasoning.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="chicken">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/korean-fried-chicken.jpg" alt="Korean Fried Chicken">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Korean Fried Chicken</h3>
          <p class="food-price">₹279</p>
          <p class="food-desc">Crispy chicken coated in spicy Korean glaze.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="noodles,rice-bowls">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/korean-ramen-bowl.jpg" alt="Korean Ramen Bowl">
        </div>
        <div class="food-card-body">
          <span class="food-tag nonveg">● Non-Veg</span>
          <h3>Korean Ramen Bowl</h3>
          <p class="food-price">₹249</p>
          <p class="food-desc">Hot and spicy ramen with rich broth and toppings.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="drinks">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/lemon-ade.jpg" alt="Korean Lemon Ade">
        </div>
        <div class="food-card-body">
          <h3>Korean Lemon Ade</h3>
          <p class="food-price">₹129</p>
          <p class="food-desc">Refreshing sparkling lemon drink with a sweet and tangy Korean twist.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rolls">
        <div class="food-image-wrap">
          <img src="images/food-items/hae-kum-gang/veg-kimbap.jpg" alt="Veg Kimbap">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Veg Kimbap</h3>
          <p class="food-price">₹199</p>
          <p class="food-desc">Korean sushi rolls filled with vegetables and rice.</p>
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

