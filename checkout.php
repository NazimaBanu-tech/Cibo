<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">

  <style>
    .checkout-page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 34px 48px 60px;
    }

    .checkout-title {
      font-size: 34px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 28px;
      letter-spacing: -0.8px;
    }

    .checkout-layout {
      display: grid;
      grid-template-columns: 1.5fr 0.9fr;
      gap: 28px;
      align-items: start;
    }

    .checkout-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: var(--shadow);
      padding: 26px;
      margin-bottom: 24px;
    }

    .checkout-card h3 {
      font-size: 22px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 18px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group.full {
      grid-column: span 2;
    }

    .form-group label {
      font-size: 14px;
      font-weight: 700;
      color: #4b463f;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      border: 1.5px solid #ddd4c8;
      background: #fbfaf7;
      border-radius: 16px;
      padding: 14px 16px;
      font-size: 15px;
      color: var(--text);
      outline: none;
      transition: 0.2s ease;
      font-family: 'Manrope', sans-serif;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      border-color: var(--accent);
      background: #fffdf9;
      box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.15);
    }

    .form-group input.is-invalid,
    .form-group textarea.is-invalid,
    .form-group select.is-invalid {
      border-color: #c54b4b;
      box-shadow: 0 0 0 3px rgba(197, 75, 75, 0.12);
    }

    .form-group input.is-valid,
    .form-group textarea.is-valid,
    .form-group select.is-valid {
      border-color: var(--accent);
    }

    .form-group textarea {
      min-height: 110px;
      resize: vertical;
    }

    .form-group input::-ms-reveal,
    .form-group input::-ms-clear {
      display: none;
    }

    .password-field {
      position: relative;
      display: flex;
      align-items: center;
    }

    .password-field input {
      padding-right: 54px;
    }

    .password-toggle {
      position: absolute;
      right: 12px;
      width: 34px;
      height: 34px;
      border: none;
      background: transparent;
      color: var(--accent);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      padding: 0;
      border-radius: 50%;
    }

    .password-toggle svg {
      width: 20px;
      height: 20px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      pointer-events: none;
    }

    .password-toggle .icon-eye-off {
      display: block;
    }

    .password-toggle .icon-eye {
      display: none;
    }

    .password-toggle.is-visible .icon-eye {
      display: block;
    }

    .password-toggle.is-visible .icon-eye-off {
      display: none;
    }

    .field-error {
      min-height: 18px;
      font-size: 13px;
      font-weight: 600;
      color: #c54b4b;
      line-height: 1.4;
    }

    .field-error:empty {
      display: none;
    }

    .checkout-form-message {
      margin-top: 16px;
      min-height: 20px;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.5;
      color: var(--muted);
    }

    .checkout-form-message.error {
      color: #c54b4b;
    }

    .checkout-form-message.success {
      color: var(--accent);
    }

    .payment-options {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .payment-option {
      border: 1px solid #ddd4c8;
      border-radius: 18px;
      padding: 16px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
      background: #fbfaf7;
      transition: 0.2s ease;
    }

    .payment-option:hover {
      border-color: var(--accent);
      background: #fffdf9;
    }

    .payment-option input {
      accent-color: var(--accent);
      transform: scale(1.1);
    }

    .payment-option span {
      font-size: 15px;
      font-weight: 700;
      color: #2a2723;
    }

    .upi-sim-card {
      display: none;
      margin-top: 18px;
      padding: 18px;
      border: 1px solid #e3d9cd;
      border-radius: 20px;
      background: linear-gradient(180deg, #fffdf9, #f8f3eb);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
    }

    .upi-sim-card.is-visible {
      display: block;
    }

    .upi-sim-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .upi-sim-copy h4 {
      font-size: 17px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 6px;
    }

    .upi-sim-copy p {
      font-size: 14px;
      line-height: 1.6;
      color: var(--muted);
    }

    .upi-qr-button {
      border: none;
      background: transparent;
      padding: 0;
      border-radius: 22px;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .upi-qr-button:hover {
      transform: translateY(-1px);
    }

    .upi-qr-button:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.16);
    }

    .upi-qr-button.is-selected .upi-qr {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.14);
    }

    .upi-qr {
      width: 140px;
      height: 140px;
      border-radius: 18px;
      border: 1px solid #e1d7ca;
      background: #fff;
      padding: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .upi-qr img {
      display: block;
      width: 100%;
      height: 100%;
      border-radius: 12px;
      object-fit: cover;
    }

    .upi-sim-meta {
      display: grid;
      gap: 10px;
      margin-bottom: 16px;
    }

    .upi-sim-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      font-size: 14px;
      color: #4b463f;
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.7);
      border: 1px solid #ece2d6;
    }

    .upi-sim-row strong {
      color: #171715;
      font-size: 15px;
      font-weight: 800;
    }

    .upi-manual-group {
      margin-top: 2px;
    }

    .upi-manual-group input {
      background: rgba(255, 255, 255, 0.86);
    }

    .upi-sim-status {
      min-height: 20px;
      margin-top: 14px;
      font-size: 13px;
      font-weight: 700;
      line-height: 1.5;
      color: var(--muted);
    }

    .upi-sim-status.is-processing {
      color: #b45309;
    }

    .upi-sim-status.is-success {
      color: var(--accent);
    }

    .card-sim-status {
      min-height: 20px;
      margin-top: 4px;
      font-size: 13px;
      font-weight: 700;
      line-height: 1.5;
      color: var(--muted);
    }

    .card-sim-status.is-processing {
      color: #b45309;
    }

    .card-sim-status.is-success {
      color: var(--accent);
    }

    .cart-note-card {
      margin-top: 20px;
      padding: 20px 22px;
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: var(--shadow);
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

    .order-summary-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: var(--shadow);
      padding: 26px;
      position: sticky;
      top: 110px;
    }

    .order-summary-card h3 {
      font-size: 22px;
      font-weight: 800;
      margin-bottom: 18px;
      color: #171715;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 0;
      border-bottom: 1px solid #eee5d9;
      font-size: 15px;
      color: #4b463f;
    }

    .order-item:last-of-type {
      border-bottom: none;
    }

    .order-summary-note {
      font-size: 13px;
      line-height: 1.6;
      color: #5f584f;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-top: 14px;
      font-size: 15px;
      color: #5f584f;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;
      margin-top: 18px;
      padding-top: 18px;
      border-top: 1px solid #e7dfd3;
      font-size: 20px;
      font-weight: 800;
      color: #171715;
    }

    .place-order-btn {
      width: 100%;
      height: 52px;
      font-size: 16px;
      margin-top: 22px;
      border: 1px solid var(--accent);
      background: linear-gradient(180deg, #89a85c, var(--accent));
      box-shadow: none;
    }

    .place-order-btn:disabled {
      background: linear-gradient(180deg, #89a85c, var(--accent));
      color: #fff;
      border-color: var(--accent);
      box-shadow: none;
      opacity: 1;
      cursor: not-allowed;
    }

    @media (max-width: 980px) {
      .checkout-page {
        padding-left: 20px;
        padding-right: 20px;
      }

      .checkout-layout {
        grid-template-columns: 1fr;
      }

      .order-summary-card {
        position: static;
      }
    }

    @media (max-width: 640px) {
      .form-grid {
        grid-template-columns: 1fr;
      }

      .upi-sim-top {
        align-items: flex-start;
      }

      .form-group.full {
        grid-column: span 1;
      }

      .checkout-title {
        font-size: 28px;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <main class="checkout-page">
    <h1 class="checkout-title">Checkout</h1>

    <form class="checkout-layout" id="checkout-form" novalidate>

      <div class="checkout-left">

        <section class="checkout-card">
          <h3>Delivery Details</h3>

          <div class="form-grid">
            <div class="form-group">
              <label for="checkout-name">Full Name</label>
              <input id="checkout-name" name="name" type="text" placeholder="Enter your name" autocomplete="name" required>
              <div class="field-error" data-error-for="name" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="checkout-phone">Phone Number</label>
              <input id="checkout-phone" name="phone" type="tel" inputmode="numeric" placeholder="Enter your phone number" autocomplete="tel" maxlength="10" required>
              <div class="field-error" data-error-for="phone" aria-live="polite"></div>
            </div>

            <div class="form-group full">
              <label for="checkout-address">Address</label>
              <textarea id="checkout-address" name="address" placeholder="House number, street, area" autocomplete="street-address" required></textarea>
              <div class="field-error" data-error-for="address" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="checkout-city">City</label>
              <input id="checkout-city" name="city" type="text" placeholder="Bangalore" autocomplete="address-level2" required>
              <div class="field-error" data-error-for="city" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="checkout-pincode">Pincode</label>
              <input id="checkout-pincode" name="pincode" type="text" inputmode="numeric" placeholder="560001" autocomplete="postal-code" maxlength="6" required>
              <div class="field-error" data-error-for="pincode" aria-live="polite"></div>
            </div>
          </div>
        </section>

        <section class="checkout-card">
          <h3>Payment Method</h3>

          <div class="payment-options">
            <label class="payment-option">
              <input type="radio" name="payment" value="cod" checked>
              <span>Cash on Delivery</span>
            </label>

            <label class="payment-option">
              <input type="radio" name="payment" value="upi">
              <span>UPI</span>
            </label>

            <label class="payment-option">
              <input type="radio" name="payment" value="card">
              <span>Credit / Debit Card</span>
            </label>
          </div>

          <div class="checkout-form-message" id="checkout-form-message" aria-live="polite"></div>
        </section>

        <section class="cart-note-card">
          <h4>Delivery Note</h4>
          <p>Please check your address and phone number before placing the order. You can continue shopping or proceed to checkout when ready.</p>
        </section>

      </div>

      <aside class="order-summary-card">
        <h3>Order Summary</h3>

        <div class="order-item">
          <span>Your cart items will appear here</span>
          <span>₹0</span>
        </div>

        <div class="order-item">
          <span>Order summary loads after checkout data is ready</span>
          <span>₹0</span>
        </div>

        <div class="summary-row">
          <span>Subtotal</span>
          <span>₹0</span>
        </div>

        <div class="summary-row">
          <span>Delivery</span>
          <span>₹0</span>
        </div>

        <div class="summary-row">
          <span>Discount</span>
          <span>₹0</span>
        </div>

        <div class="summary-row">
          <span>GST (5%)</span>
          <span>â‚¹0</span>
        </div>

        <div class="summary-total">
          <span>Total</span>
          <span>₹0</span>
        </div>

        <button type="submit" class="place-order-btn primary-btn" id="place-order-btn" disabled>Place Order</button>
      </aside>

    </form>
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
  <script src="account-api.js"></script>
  <script src="bill-summary.js?v=20260508"></script>
  <script src="checkout.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

