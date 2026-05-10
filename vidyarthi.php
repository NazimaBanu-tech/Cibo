<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vidyarthi Bhavan - Cibo</title>
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
          <a href="index.php">Home</a> / Vidyarthi
        </p>

        <h1>Vidyarthi Bhavan</h1>

        <div class="restaurant-meta">
          <span>⭐ 4.5</span>
          <span>15–20 mins</span>
          <span>South Indian, Traditional</span>
        </div>

        <p class="restaurant-address">Basavanagudi</p>
        <p class="restaurant-offer">Free delivery on orders above ₹149</p>
      </div>

      <div class="restaurant-hero-right">
        <img src="images/restaurant-heroes/vidyarthi-hero.jpg" alt="Vidyarthi hero image">
      </div>
    </section>

    <section class="menu-topbar">
      <h2>Menu</h2>
    </section>

    <section class="menu-chips">
      <button class="chip active" data-filter="all">All</button>
      <button class="chip" data-filter="dosa">Dosa</button>
      <button class="chip" data-filter="breakfast">Breakfast</button>
      <button class="chip" data-filter="rice">Rice</button>
      <button class="chip" data-filter="snacks">Snacks</button>
      <button class="chip" data-filter="beverages">Beverages</button>
      <button class="chip" data-filter="specials">Specials</button>
    </section>

    <section class="food-grid">

      <article class="food-big-card" data-category="dosa,specials">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/butter-masala-dosa.jpg" alt="Butter Masala Dosa">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Butter Masala Dosa</h3>
          <p class="food-price">₹110</p>
          <p class="food-desc">A crispy golden dosa layered with butter and filled with flavourful potato masala.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="dosa">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/plain-dosa.jpg" alt="Plain Dosa">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Plain Dosa</h3>
          <p class="food-price">₹75</p>
          <p class="food-desc">Classic thin and crispy dosa served with fresh chutney and hot sambar.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="dosa,breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/set-dosa.jpg" alt="Set Dosa">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Set Dosa</h3>
          <p class="food-price">₹90</p>
          <p class="food-desc">Soft, fluffy mini dosas served as a comforting South Indian breakfast favourite.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="snacks">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/rava-vada.jpg" alt="Rava Vada">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Rava Vada</h3>
          <p class="food-price">₹65</p>
          <p class="food-desc">Crispy semolina vada with a crunchy bite, served with chutney on the side.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/pongal.jpg" alt="Pongal">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Pongal</h3>
          <p class="food-price">₹85</p>
          <p class="food-desc">Warm and comforting rice-lentil dish cooked with ghee, pepper and mild spices.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="beverages">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/badam-milk.jpg" alt="Badam Milk">
        </div>
        <div class="food-card-body">
          <h3>Badam Milk</h3>
          <p class="food-price">₹55</p>
          <p class="food-desc">Sweet and creamy almond milk drink with a rich traditional flavour.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="specials,breakfast">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/chow-chow-bath.jpg" alt="Chow Chow Bath">
        </div>
        <div class="food-card-body">
          <h3>Chow Chow Bath</h3>
          <p class="food-price">₹95</p>
          <p class="food-desc">A beloved combo of khara bath and sweet kesari bath served together on one plate.</p>
          <button class="food-btn">Add</button>
        </div>
      </article>

      <article class="food-big-card" data-category="rice">
        <div class="food-image-wrap">
          <img src="images/food-items/vidyarthi/lemon-rice.jpg" alt="Lemon Rice">
        </div>
        <div class="food-card-body">
          <span class="food-tag veg">● Veg</span>
          <h3>Lemon Rice</h3>
          <p class="food-price">₹80</p>
          <p class="food-desc">Tangy rice tempered with mustard, curry leaves and peanuts for a light meal.</p>
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

