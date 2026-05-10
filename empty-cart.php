<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Empty Cart - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">

  <style>
    .empty-cart-page {
      min-height: calc(100vh - 92px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px 70px;
    }

    .empty-cart-card {
      width: 100%;
      max-width: 720px;
      background: linear-gradient(135deg, #fbf8f3, #fffdf9);
      border: 1px solid var(--line);
      border-radius: 30px;
      box-shadow: var(--shadow);
      padding: 42px 36px;
      text-align: center;
    }

    .empty-cart-illustration {
      width: 180px;
      height: 180px;
      margin: 0 auto 24px;
      border-radius: 50%;
      background: #f6f1e8;
      border: 1px solid #e7dfd3;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .empty-cart-illustration svg {
      width: 88px;
      height: 88px;
      stroke: var(--accent);
      stroke-width: 1.8;
      fill: none;
    }

    .empty-cart-card h1 {
      font-size: 36px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 12px;
      letter-spacing: -1px;
    }

    .empty-cart-card p {
      font-size: 16px;
      line-height: 1.8;
      color: var(--muted);
      max-width: 500px;
      margin: 0 auto 28px;
    }

    .empty-cart-actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .empty-btn {
      min-width: 210px;
      height: 52px;
      border-radius: 16px;
      font-size: 15px;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: 0.2s ease;
      cursor: pointer;
    }

    .empty-btn.primary {
      background: var(--accent);
      color: white;
      border: none;
    }

    .empty-btn.primary:hover {
      background: #4e682e;
    }

    .empty-btn.secondary {
      background: white;
      color: var(--accent);
      border: 1px solid #d9d0c3;
    }

    .empty-btn.secondary:hover {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
    }

    .empty-cart-note {
      margin-top: 24px;
      font-size: 14px;
      color: #7a746b;
    }

    @media (max-width: 640px) {
      .empty-cart-card {
        padding: 30px 20px;
      }

      .empty-cart-card h1 {
        font-size: 30px;
      }

      .empty-cart-card p {
        font-size: 15px;
      }

      .empty-btn {
        width: 100%;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <main class="empty-cart-page">
    <section class="empty-cart-card">

      <div class="empty-cart-illustration">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="9" cy="20" r="1"></circle>
          <circle cx="18" cy="20" r="1"></circle>
          <path d="M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 7H7"></path>
          <path d="M8 11h8"></path>
        </svg>
      </div>

      <h1>Your cart is empty</h1>
      <p>
        Looks like you haven’t added anything yet. Explore restaurants, discover your favourite dishes, and start your Cibo order journey.
      </p>

      <div class="empty-cart-actions">
        <a href="index.php" class="empty-btn primary">See Restaurants Near You</a>
        <a href="signup.php" class="empty-btn secondary">Create an Account</a>
      </div>

      <div class="empty-cart-note">
        Sign up to save your details and enjoy a faster checkout later.
      </div>

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

  <script src="auth-display.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>
