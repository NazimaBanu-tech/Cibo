<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Order - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">

  <style>
    .track-page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 34px 48px 60px;
    }

    .track-card {
      max-width: 920px;
      margin: 28px auto 0;
      background: linear-gradient(135deg, #fbf8f3, #fffdf9);
      border: 1px solid var(--line);
      border-radius: 28px;
      box-shadow: var(--shadow);
      padding: 34px 30px;
    }

    .track-card h1 {
      font-size: 38px;
      font-weight: 800;
      letter-spacing: -1px;
      color: #171715;
      margin-bottom: 10px;
    }

    .track-subtext {
      font-size: 16px;
      line-height: 1.7;
      color: var(--muted);
      margin-bottom: 26px;
      max-width: 620px;
    }

    .track-meta {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .track-meta-box {
      background: white;
      border: 1px solid #e7dfd3;
      border-radius: 20px;
      padding: 18px 16px;
    }

    .track-meta-box h4 {
      font-size: 14px;
      font-weight: 700;
      color: #7a746b;
      margin-bottom: 8px;
    }

    .track-meta-box p {
      font-size: 17px;
      font-weight: 800;
      color: #171715;
    }

    .track-steps {
      position: relative;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }

    .track-steps::before {
      content: "";
      position: absolute;
      top: 22px;
      left: 12%;
      right: 12%;
      height: 3px;
      background: #e7dfd3;
      z-index: 0;
    }

    .track-step {
      position: relative;
      z-index: 1;
      text-align: center;
    }

    .track-step-dot {
      width: 46px;
      height: 46px;
      margin: 0 auto 12px;
      border-radius: 50%;
      border: 2px solid #d9d0c3;
      background: white;
      color: #8a8175;
      font-size: 18px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .track-step h3 {
      font-size: 16px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 6px;
    }

    .track-step p {
      font-size: 13px;
      line-height: 1.6;
      color: var(--muted);
      max-width: 180px;
      margin: 0 auto;
    }

    .track-step.completed .track-step-dot,
    .track-step.current .track-step-dot {
      background: #eef4e7;
      border-color: #cfe0b7;
      color: var(--accent);
    }

    .track-step.current h3 {
      color: var(--accent);
    }

    .track-note {
      background: #f6f1e8;
      border: 1px solid #e7dfd3;
      border-radius: 20px;
      padding: 16px 18px;
      font-size: 14px;
      line-height: 1.7;
      color: #5f584f;
      margin-bottom: 26px;
    }

    .track-actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .track-btn {
      min-width: 190px;
      height: 50px;
      border-radius: 16px;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      transition: 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .track-btn.primary {
      background: var(--accent);
      color: white;
      border: none;
    }

    .track-btn.primary:hover {
      background: #4e682e;
    }

    .track-btn.secondary {
      background: white;
      color: var(--accent);
      border: 1px solid #d9d0c3;
    }

    .track-btn.secondary:hover {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
    }

    @media (max-width: 900px) {
      .track-page {
        padding-left: 20px;
        padding-right: 20px;
      }

      .track-meta,
      .track-steps {
        grid-template-columns: 1fr;
      }

      .track-steps::before {
        display: none;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <main class="track-page">
    <section class="track-card">
      <h1>Track Your Order</h1>
      <p class="track-subtext">
        Your order is moving smoothly through the kitchen and will be on its way soon. Current status is highlighted below.
      </p>

      <div class="track-meta">
        <div class="track-meta-box">
          <h4>Order ID</h4>
          <p id="track-order-id">#--</p>
        </div>
        <div class="track-meta-box">
          <h4>Current Status</h4>
          <p id="track-status-label">Pending</p>
        </div>
        <div class="track-meta-box">
          <h4>Estimated Delivery</h4>
          <p id="track-delivery-time">25-30 mins</p>
        </div>
      </div>

      <div class="track-steps">
        <div class="track-step completed">
          <div class="track-step-dot">1</div>
          <h3>Order Placed</h3>
          <p>Your order has been confirmed and received successfully.</p>
        </div>

        <div class="track-step current">
          <div class="track-step-dot">2</div>
          <h3>Preparing</h3>
          <p>The restaurant is preparing your food fresh for delivery.</p>
        </div>

        <div class="track-step">
          <div class="track-step-dot">3</div>
          <h3>Out for Delivery</h3>
          <p>Your order will be handed to the delivery partner next.</p>
        </div>

        <div class="track-step">
          <div class="track-step-dot">4</div>
          <h3>Delivered</h3>
          <p>Your order will be marked delivered once it reaches you.</p>
        </div>
      </div>

      <div class="track-actions">
        <button type="button" class="track-btn primary" id="mark-delivered-btn" style="display: none;">Mark as Delivered</button>
        <a href="index.php" class="track-btn primary">Back to Home</a>
        <a href="menu.php" class="track-btn secondary">Order Again</a>
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
  <script src="order-api.js"></script>
  <script src="auth-display.js"></script>
  <script src="track.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

