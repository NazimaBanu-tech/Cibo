<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">

  <style>
    .cart-page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 34px 48px 60px;
    }

    .cart-toast-region {
      position: fixed;
      top: calc(84px + env(safe-area-inset-top, 0px));
      left: 50%;
      transform: translateX(-50%);
      width: min(100% - 32px, 440px);
      display: grid;
      gap: 12px;
      z-index: 1100;
      pointer-events: none;
    }

    .cart-toast {
      display: grid;
      grid-template-columns: auto minmax(0, 1fr) auto;
      align-items: start;
      gap: 12px;
      padding: 14px 16px;
      border-radius: 18px;
      border: 1px solid #e6dccf;
      background: rgba(255, 253, 249, 0.96);
      box-shadow: 0 18px 34px rgba(31, 31, 27, 0.14);
      backdrop-filter: blur(12px);
      color: #4b463f;
      pointer-events: auto;
      opacity: 0;
      transform: translateY(-10px) scale(0.98);
      transition: opacity 0.22s ease, transform 0.22s ease;
    }

    .cart-toast.is-visible {
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .cart-toast[data-type="info"] {
      border-color: #dde6d2;
      background: rgba(248, 252, 243, 0.97);
    }

    .cart-toast[data-type="success"] {
      border-color: #d4e7cb;
      background: rgba(243, 251, 239, 0.97);
    }

    .cart-toast[data-type="warning"] {
      border-color: #ecd7bf;
      background: rgba(255, 247, 236, 0.97);
    }

    .cart-toast[data-type="error"] {
      border-color: #ebc9c9;
      background: rgba(255, 245, 245, 0.98);
    }

    .cart-toast-icon {
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      font-weight: 800;
      flex-shrink: 0;
      background: #f6f1e8;
      color: #8a5d2f;
    }

    .cart-toast[data-type="info"] .cart-toast-icon {
      background: #eef4e7;
      color: #5f7c3a;
    }

    .cart-toast[data-type="success"] .cart-toast-icon {
      background: #e7f4df;
      color: #3f7a2c;
    }

    .cart-toast[data-type="warning"] .cart-toast-icon {
      background: #fff1df;
      color: #b56a17;
    }

    .cart-toast[data-type="error"] .cart-toast-icon {
      background: #fce8e8;
      color: #b14a4a;
    }

    .cart-toast-copy {
      min-width: 0;
      padding-top: 1px;
    }

    .cart-toast-title {
      display: block;
      margin-bottom: 3px;
      font-size: 13px;
      font-weight: 800;
      color: #171715;
      letter-spacing: 0.01em;
      text-transform: uppercase;
    }

    .cart-toast-message {
      margin: 0;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.5;
      color: #4b463f;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .cart-toast-dismiss {
      width: 34px;
      height: 34px;
      border: none;
      border-radius: 12px;
      background: transparent;
      color: #6f685f;
      font-size: 20px;
      line-height: 1;
      cursor: pointer;
      transition: background-color 0.2s ease, color 0.2s ease;
      flex-shrink: 0;
    }

    .cart-toast-dismiss:hover,
    .cart-toast-dismiss:focus-visible {
      background: rgba(31, 31, 27, 0.06);
      color: #171715;
      outline: none;
    }

    .cart-header {
      margin-bottom: 28px;
    }

    .cart-header h1 {
      font-size: 38px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 8px;
      letter-spacing: -1px;
    }

    .cart-header p {
      font-size: 16px;
      color: var(--muted);
      line-height: 1.7;
    }

    .cart-layout {
      display: grid;
      grid-template-columns: 1.6fr 0.9fr;
      gap: 28px;
      align-items: start;
    }

    .cart-main-card,
    .cart-summary-card,
    .cart-note-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: var(--shadow);
    }

    .cart-main-card {
      padding: 24px;
    }

    .cart-restaurant {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding-bottom: 18px;
      border-bottom: 1px solid #eee5d9;
      margin-bottom: 22px;
    }

    .cart-restaurant h3 {
      font-size: 22px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 4px;
    }

    .cart-restaurant p {
      font-size: 14px;
      color: var(--muted);
    }

    .cart-badge {
      display: inline-block;
      background: #eef4e7;
      color: var(--accent);
      border: 1px solid #d8e4c4;
      padding: 10px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 800;
      white-space: nowrap;
    }

    .cart-restaurant-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .clear-cart-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 38px;
      padding: 0 14px;
      border-radius: 999px;
      border: 1px solid #e3d9cd;
      background: linear-gradient(180deg, #fffdf9, #f8f3eb);
      color: #6f685f;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: color 0.2s ease, border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }

    .clear-cart-link:hover {
      color: #b14a4a;
      border-color: #d6c2c2;
      background: linear-gradient(180deg, #fffaf9, #f7efee);
      transform: translateY(-1px);
    }

    .clear-cart-link svg {
      width: 15px;
      height: 15px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      flex-shrink: 0;
    }

    .cart-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(31, 31, 27, 0.24);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      z-index: 1200;
    }

    .cart-modal-overlay.is-open {
      display: flex;
    }

    .cart-modal {
      width: min(440px, 100%);
      background: linear-gradient(135deg, #fbf8f3, #fffdf9);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: 0 20px 44px rgba(31, 31, 27, 0.16);
      padding: 24px;
    }

    .cart-modal h3 {
      font-size: 24px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 10px;
      letter-spacing: -0.03em;
    }

    .cart-modal p {
      font-size: 15px;
      line-height: 1.7;
      color: var(--muted);
      margin-bottom: 18px;
    }

    .cart-modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      flex-wrap: wrap;
    }

    .cart-modal-btn {
      min-width: 118px;
      height: 44px;
      border-radius: 14px;
      border: 1px solid #ddd4c8;
      background: #fffdf9;
      color: #4b463f;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      transition: 0.2s ease;
      font-family: 'Manrope', sans-serif;
    }

    .cart-modal-btn:hover {
      border-color: #cfc3b4;
      background: #f8f3eb;
    }

    .cart-modal-btn.danger {
      border-color: #d7c2c2;
      background: #b14a4a;
      color: #fff;
    }

    .cart-modal-btn.danger:hover {
      border-color: #a94444;
      background: #9f3f3f;
    }

    .cart-item {
      display: grid;
      grid-template-columns: 110px 1fr auto;
      gap: 18px;
      align-items: center;
      padding: 18px 0;
      border-bottom: 1px solid #eee5d9;
      border-radius: 24px;
      transition: none;
    }

    .cart-item:last-child {
      border-bottom: none;
      padding-bottom: 4px;
    }

    .cart-item-image {
      width: 110px;
      height: 95px;
      border-radius: 18px;
      overflow: hidden;
      flex-shrink: 0;
    }

    .cart-item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .cart-item-info {
      min-width: 0;
    }

    .cart-item-tag {
      display: inline-block;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .cart-item-tag.veg {
      color: #1d8a45;
    }

    .cart-item-tag.nonveg {
      color: #c54b4b;
    }

    .cart-item-info h4 {
      font-size: 22px;
      font-weight: 700;
      color: #171715;
      margin-bottom: 6px;
      line-height: 1.25;
    }

    .cart-item-info p {
      font-size: 14px;
      color: var(--muted);
      line-height: 1.7;
      margin-bottom: 14px;
      max-width: 500px;
    }

    .cart-item-actions {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .qty-box {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0;
      min-width: 152px;
      border: 1px solid color-mix(in srgb, var(--accent) 14%, var(--line) 86%);
      border-radius: 999px;
      background: var(--card);
      padding: 2px;
      box-shadow: 0 6px 16px rgba(31, 31, 27, 0.05);
      overflow: hidden;
    }

    .qty-btn {
      width: 44px;
      height: 40px;
      border: none;
      border-radius: 999px;
      background: transparent;
      font-size: 24px;
      font-weight: 800;
      color: var(--accent);
      cursor: pointer;
      line-height: 1;
      box-shadow: none;
      transition: color 0.25s ease, background-color 0.25s ease;
    }

    .qty-btn:hover {
      background: color-mix(in srgb, var(--accent) 8%, var(--card) 92%);
      color: var(--accent);
    }

    .qty-value {
      min-width: 52px;
      margin: 0;
      text-align: center;
      font-size: 18px;
      font-weight: 800;
      color: #171715;
      letter-spacing: -0.02em;
      position: relative;
    }

    .qty-value::before,
    .qty-value::after {
      content: "";
      position: absolute;
      top: 50%;
      width: 1px;
      height: 18px;
      background: color-mix(in srgb, var(--accent) 12%, var(--line) 88%);
      transform: translateY(-50%);
    }

    .qty-value::before {
      left: 0;
    }

    .qty-value::after {
      right: 0;
    }

    .remove-link {
      font-size: 14px;
      font-weight: 700;
      color: #b14a4a;
      text-decoration: none;
    }

    .remove-link:hover {
      text-decoration: underline;
    }

    .cart-item-price {
      text-align: right;
      min-width: 90px;
    }

    .cart-item-price .price {
      font-size: 24px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 8px;
    }

    .cart-item-price .line-total {
      font-size: 13px;
      color: var(--muted);
    }

    .cart-note-card {
      margin-top: 20px;
      padding: 20px 22px;
    }

    .cart-note-card h4 {
      font-size: 17px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 8px;
    }

    .cart-note-card p {
      font-size: 14px;
      color: var(--muted);
      line-height: 1.7;
    }

    .cart-summary-card {
      padding: 24px;
      position: sticky;
      top: 110px;
    }

    .cart-summary-card h3 {
      font-size: 24px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 20px;
    }

    .summary-promo {
      position: relative;
      margin-bottom: 20px;
    }

    .summary-promo-label {
      display: block;
      margin-bottom: 10px;
      font-size: 13px;
      font-weight: 800;
      color: #4b463f;
      letter-spacing: 0.01em;
      text-transform: uppercase;
    }

    .summary-promo-input {
      display: flex;
      gap: 10px;
    }

    .summary-promo input {
      flex: 1;
      height: 46px;
      border: 1.5px solid #ddd4c8;
      background: #fbfaf7;
      border-radius: 14px;
      padding: 0 14px;
      font-size: 14px;
      outline: none;
      font-family: 'Manrope', sans-serif;
    }

    .summary-promo input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.15);
    }

    .apply-btn {
      min-width: 92px;
      height: 46px;
    }

    .summary-promo-suggestions {
      display: none;
      margin-top: 10px;
      padding: 10px;
      background: #fffdf9;
      border: 1px solid #e7dfd3;
      border-radius: 18px;
      box-shadow: 0 14px 28px rgba(31, 31, 27, 0.08);
    }

    .summary-promo-suggestions.is-visible {
      display: grid;
      gap: 10px;
    }

    .summary-promo-option {
      width: 100%;
      border: 1px solid #ebe2d6;
      background: linear-gradient(180deg, #fffdf9, #f8f3eb);
      border-radius: 16px;
      padding: 12px 14px;
      text-align: left;
      cursor: pointer;
      transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
      font-family: 'Manrope', sans-serif;
    }

    .summary-promo-option:hover {
      border-color: #cfc3b4;
      background: #fffaf4;
      transform: translateY(-1px);
    }

    .summary-promo-option strong {
      display: block;
      font-size: 14px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 4px;
    }

    .summary-promo-option span {
      display: block;
      font-size: 13px;
      line-height: 1.5;
      color: #6f685f;
    }

    .summary-promo-feedback {
      min-height: 18px;
      margin-top: 10px;
      font-size: 13px;
      font-weight: 700;
      line-height: 1.5;
      color: #6f685f;
    }

    .summary-promo-feedback.is-success {
      color: var(--accent);
    }

    .summary-promo-feedback.is-error {
      color: #c54b4b;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
      font-size: 15px;
      color: #5f584f;
    }

    .summary-row strong {
      color: #171715;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 18px;
      padding-top: 18px;
      border-top: 1px solid #e7dfd3;
      font-size: 22px;
      font-weight: 800;
      color: #171715;
    }

    .summary-delivery-note {
      margin-top: 18px;
      background: #f6f1e8;
      border: 1px solid #e7dfd3;
      border-radius: 18px;
      padding: 14px 16px;
      font-size: 13px;
      line-height: 1.7;
      color: #5f584f;
    }

    .checkout-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 52px;
      margin-top: 22px;
      font-size: 16px;
      text-decoration: none;
    }

    .continue-link {
      display: inline-block;
      margin-top: 14px;
      font-size: 14px;
      font-weight: 700;
      color: var(--accent);
      text-decoration: none;
    }

    .continue-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 980px) {
      .cart-page {
        padding-left: 20px;
        padding-right: 20px;
      }

      .cart-layout {
        grid-template-columns: 1fr;
      }

      .cart-summary-card {
        position: static;
      }

      .cart-item {
        grid-template-columns: 95px 1fr;
      }

      .cart-restaurant {
        align-items: flex-start;
      }

      .cart-restaurant-meta {
        width: 100%;
        justify-content: flex-start;
      }

      .cart-item-price {
        grid-column: 2;
        text-align: left;
        margin-top: 6px;
      }
    }

    @media (max-width: 640px) {
      .cart-toast-region {
        top: calc(74px + env(safe-area-inset-top, 0px));
        width: calc(100% - 24px);
      }

      .cart-toast {
        grid-template-columns: auto minmax(0, 1fr);
        gap: 10px 12px;
        padding: 13px 14px;
      }

      .cart-toast-dismiss {
        grid-column: 2;
        justify-self: end;
        margin-top: -2px;
      }

      .cart-header h1 {
        font-size: 30px;
      }

      .cart-item {
        grid-template-columns: 1fr;
      }

      .cart-item-image {
        width: 100%;
        height: 210px;
      }

      .cart-item-price {
        grid-column: auto;
      }

      .summary-promo {
        margin-bottom: 18px;
      }

      .summary-promo-input {
        flex-direction: column;
      }

      .apply-btn {
        width: 100%;
      }

      .cart-modal-actions {
        flex-direction: column-reverse;
      }

      .cart-modal-btn {
        width: 100%;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <div class="cart-toast-region" aria-live="polite" aria-atomic="true" data-cart-toast-region></div>

  <main class="cart-page">
    <div class="cart-header">
      <h1>Your Cart</h1>
      <p>Almost there. Review your items and continue to checkout for a smooth Cibo order experience.</p>
    </div>

    <div class="cart-layout">

      <div class="cart-left">
        <section class="cart-main-card">

          <div class="cart-restaurant">
            <div>
              <h3>Your Selected Restaurant</h3>
              <p>Your selected items will appear here as soon as your current cart is ready.</p>
            </div>
            <span class="cart-badge">Offers apply when your cart is ready</span>
          </div>

          <article class="cart-item">
            <div class="cart-item-image">
              <img src="images/food-items/default-food.jpg" alt="Cart item placeholder">
            </div>

            <div class="cart-item-info">
              <span class="cart-item-tag veg">Placeholder</span>
              <h4>Your selected items will appear here</h4>
              <p>This placeholder is replaced automatically once the actual cart data is loaded.</p>

              <div class="cart-item-actions">
                <div class="qty-box">
                  <button class="qty-btn" type="button">-</button>
                  <span class="qty-value">0</span>
                  <button class="qty-btn">+</button>
                </div>

                <a href="#" class="remove-link">Remove</a>
              </div>
            </div>

            <div class="cart-item-price">
              <div class="price">₹0</div>
              <div class="line-total">Waiting for cart data</div>
            </div>
          </article>

          <article class="cart-item">
            <div class="cart-item-image">
              <img src="images/food-items/default-food.jpg" alt="Cart item placeholder">
            </div>

            <div class="cart-item-info">
              <span class="cart-item-tag veg">● Veg</span>
              <h4>More items will load here</h4>
              <p>Real cart details replace this fallback as soon as the saved cart is available.</p>

              <div class="cart-item-actions">
                <div class="qty-box">
                  <button class="qty-btn" type="button">-</button>
                  <span class="qty-value">0</span>
                  <button class="qty-btn">+</button>
                </div>

                <a href="#" class="remove-link">Remove</a>
              </div>
            </div>

            <div class="cart-item-price">
              <div class="price">₹0</div>
              <div class="line-total">Waiting for cart data</div>
            </div>
          </article>

        </section>

      </div>

      <aside class="cart-summary-card">
        <h3>Bill Summary</h3>

        <div class="summary-promo">
        </div>

        <div class="summary-row">
          <span>Subtotal</span>
          <strong>₹238</strong>
        </div>

        <div class="summary-row">
          <span>Delivery</span>
          <strong>₹30</strong>
        </div>

        <div class="summary-row">
          <span>GST (5%)</span>
          <strong>₹12</strong>
        </div>

        <div class="summary-row">
          <span>Discount</span>
          <strong>₹0</strong>
        </div>

        <div class="summary-total">
          <span>Total</span>
          <span>₹280</span>
        </div>

        <div class="summary-delivery-note">
          Your order is eligible for secure checkout and will be delivered with care.
        </div>

        <a href="checkout.php" class="checkout-btn primary-btn">Proceed to Checkout</a>

        <a href="index.php" class="continue-link">← Continue exploring restaurants</a>
      </aside>

    </div>
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
  <div class="cart-modal-overlay" id="clear-cart-modal" aria-hidden="true">
    <div class="cart-modal" role="dialog" aria-modal="true" aria-labelledby="clear-cart-title">
      <h3 id="clear-cart-title">Clear your cart?</h3>
      <p>All items from this restaurant will be removed. You can still add them again anytime while browsing Cibo.</p>
      <div class="cart-modal-actions">
        <button class="cart-modal-btn" type="button" id="cancel-clear-cart">Keep items</button>
        <button class="cart-modal-btn danger" type="button" id="confirm-clear-cart">Clear cart</button>
      </div>
    </div>
  </div>
  <script src="auth-display.js"></script>
  <script src="cart-manager.js"></script>
  <script src="bill-summary.js?v=20260508"></script>
  <script src="cart.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

