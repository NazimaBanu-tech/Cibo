<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Udupi - Cibo</title>
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
          <a href="index.php">Home</a> / Udupi
        </p>

        <h1>Udupi</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.4</span>
          <span>20–25 mins</span>
          <span>South Indian, Breakfast</span>
        </div>

        <p class="restaurant-address">Basavanagudi</p>
        <p class="restaurant-offer">Free delivery on orders above ₹149</p>
      </div>

      <div class="restaurant-hero-right">
        <img src="images/restaurant-heroes/udupi-hero.jpg" alt="Udupi hero image">
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="dosa">Dosa</button>
      <button class="chip" data-filter="idli">Idli</button>
      <button class="chip" data-filter="breakfast">Breakfast</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="bath">Bath</button>
      <button class="chip" data-filter="desserts">Desserts</button>
      <button class="chip" data-filter="beverages">Beverages</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="dosa,breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/masala-dosa.jpg" alt="Masala Dosa">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Masala Dosa</h3>
          <p class="food-price">₹95</p>
          <p class="food-desc">Golden crispy dosa filled with spiced potato masala, served with chutney and sambar.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="idli,breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/idli-vada.jpg" alt="Idli Vada">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Idli Vada</h3>
          <p class="food-price">₹85</p>
          <p class="food-desc">Soft idlis paired with crispy medu vada, served with coconut chutney and hot sambar.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="bath,breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/khara-bath.jpg" alt="Khara Bath">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Khara Bath</h3>
          <p class="food-price">₹80</p>
          <p class="food-desc">Flavourful semolina breakfast dish cooked with vegetables, spices and a rich South Indian touch.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="bath">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/bisibele-bath.jpg" alt="Bisibele Bath">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Bisibele Bath</h3>
          <p class="food-price">₹110</p>
          <p class="food-desc">A comforting Karnataka-style rice dish made with lentils, vegetables and aromatic spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/poori-saagu.jpg" alt="Poori Saagu">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Poori Saagu</h3>
          <p class="food-price">₹90</p>
          <p class="food-desc">Puffed soft pooris served with mildly spiced vegetable saagu for a hearty meal.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="beverages">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/filter-coffee.jpg" alt="Filter Coffee">
        </div>
        <div class="food-card-body">
          <h3>Filter Coffee</h3>
          <p class="food-price">₹35</p>
          <p class="food-desc">Classic strong South Indian filter coffee with a rich aroma and smooth taste.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="desserts,bath">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/kesari-bath.jpg" alt="Kesari Bath">
        </div>
        <div class="food-card-body">
          <h3>Kesari Bath</h3>
          <p class="food-price">₹70</p>
          <p class="food-desc">Sweet semolina dessert with ghee, saffron notes and crunchy dry fruits.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/udupi/curd-rice.jpg" alt="Curd Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Curd Rice</h3>
          <p class="food-price">₹75</p>
          <p class="food-desc">Cool and comforting curd rice tempered with mustard, curry leaves and mild seasoning.</p>
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

