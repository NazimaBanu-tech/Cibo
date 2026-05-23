<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">

  <style>
    .auth-page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 34px 48px 60px;
    }

    .auth-wrapper {
      max-width: 1120px;
      margin: 24px auto 0;
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: linear-gradient(135deg, #fbf8f3, #fffdf9);
      border: 1px solid var(--line);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: var(--shadow);
    }

    .auth-left {
      padding: 48px 42px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: linear-gradient(160deg, #f7f2e9 0%, #fdfaf5 100%);
      border-right: 1px solid var(--line);
    }

    .auth-badge {
      display: inline-block;
      width: fit-content;
      padding: 10px 16px;
      border-radius: 999px;
      background: #eef4e7;
      border: 1px solid #d8e4c4;
      color: var(--accent);
      font-size: 13px;
      font-weight: 800;
      margin-bottom: 18px;
    }

    .auth-left h1 {
      font-size: 44px;
      line-height: 1.12;
      font-weight: 800;
      color: #171715;
      letter-spacing: -1px;
      margin-bottom: 16px;
    }

    .auth-left p {
      font-size: 16px;
      color: var(--muted);
      line-height: 1.8;
      max-width: 430px;
      margin-bottom: 26px;
    }

    .auth-points {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-top: 4px;
    }

    .auth-point {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 15px;
      font-weight: 700;
      color: #4b463f;
    }

    .auth-point-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: white;
      border: 1px solid #e7dfd3;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent);
      font-size: 16px;
      font-weight: 800;
      flex-shrink: 0;
    }

    .auth-right {
      padding: 44px 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .auth-card {
      width: 100%;
      max-width: 440px;
    }

    .auth-card h2 {
      font-size: 32px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 8px;
      letter-spacing: -0.8px;
    }

    .auth-subtext {
      font-size: 15px;
      color: var(--muted);
      margin-bottom: 24px;
      line-height: 1.7;
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-size: 14px;
      font-weight: 700;
      color: #4b463f;
    }

    .form-group input {
      width: 100%;
      padding: 15px 16px;
      border-radius: 16px;
      border: 1.5px solid #ddd4c8;
      background: #fbfaf7;
      outline: none;
      font-size: 15px;
      color: var(--text);
      font-family: 'Manrope', sans-serif;
      transition: 0.2s ease;
    }

    .form-group input:focus {
      border-color: var(--accent);
      background: #fffdf9;
      box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.15);
    }

    .auth-btn {
      width: 100%;
      height: 52px;
      background: linear-gradient(180deg, #89a85c, var(--accent));
      color: white;
      border: 1px solid var(--accent);
      border-radius: 16px;
      font-size: 16px;
      font-weight: 800;
      margin-top: 8px;
      cursor: pointer;
      transition: background-color 0.2s ease, border-color 0.2s ease;
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
      padding-right: 88px;
    }

    .password-toggle {
      position: absolute;
      right: 14px;
      border: none;
      background: transparent;
      color: var(--accent);
      width: 34px;
      height: 34px;
      cursor: pointer;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
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
      display: none;
    }

    .password-toggle.is-visible .icon-eye-off {
      display: none;
    }

    .password-toggle.is-visible .icon-eye {
      display: block;
    }

    .password-toggle:not(.is-visible) .icon-eye {
      display: none;
    }

    .password-toggle:not(.is-visible) .icon-eye-off {
      display: block;
    }

    .auth-btn:disabled {
      background: linear-gradient(180deg, #89a85c, var(--accent));
      color: #fff;
      border-color: var(--accent);
      opacity: 1;
      cursor: not-allowed;
    }

    .auth-btn:hover {
      background: linear-gradient(180deg, #89a85c, var(--accent));
    }

    .auth-btn:disabled:hover {
      background: linear-gradient(180deg, #89a85c, var(--accent));
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

    .field-error:not(:empty) {
      display: block;
    }

    .auth-form .form-group input.is-invalid {
      border-color: #c54b4b;
      box-shadow: 0 0 0 3px rgba(197, 75, 75, 0.12);
    }

    .auth-form .form-group input.is-valid {
      border-color: var(--accent);
    }

    .auth-form-message {
      min-height: 20px;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.5;
      color: var(--muted);
    }

    .auth-form-message.error {
      color: #c54b4b;
    }

    .auth-form-message.success {
      color: var(--accent);
    }

    .auth-divider {
      position: relative;
      text-align: center;
      margin: 18px 0;
      font-size: 13px;
      font-weight: 700;
      color: #8a8175;
    }

    .auth-divider::before,
    .auth-divider::after {
      content: "";
      position: absolute;
      top: 50%;
      width: 38%;
      height: 1px;
      background: #e7dfd3;
    }

    .auth-divider::before {
      left: 0;
    }

    .auth-divider::after {
      right: 0;
    }

    .secondary-btn {
      width: 100%;
      height: 50px;
      border: 1px solid #d9d0c3;
      border-radius: 16px;
      background: white;
      color: var(--accent);
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
    }

    .secondary-btn:hover {
      background: white;
      color: var(--accent);
      border-color: #d9d0c3;
    }

    .auth-footer {
      text-align: center;
      margin-top: 22px;
      font-size: 15px;
      color: #5f584f;
    }

    .auth-footer a {
      color: var(--accent);
      font-weight: 800;
    }

    .auth-footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 980px) {
      .auth-page {
        padding-left: 20px;
        padding-right: 20px;
      }

      .auth-wrapper {
        grid-template-columns: 1fr;
      }

      .auth-left {
        border-right: none;
        border-bottom: 1px solid var(--line);
      }
    }

    @media (max-width: 640px) {
      .auth-left,
      .auth-right {
        padding: 28px 20px;
      }

      .auth-left h1 {
        font-size: 34px;
      }

      .auth-card h2 {
        font-size: 28px;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <main class="auth-page">
    <section class="auth-wrapper">

      <div class="auth-left">
        <span class="auth-badge">Join the Cibo experience</span>

        <h1>Create your Cibo account</h1>

        <p>
          Sign up to explore top restaurants, save your favourites, and enjoy a smooth food ordering experience with the warm and premium Cibo feel.
        </p>

        <div class="auth-points">
          <div class="auth-point">
            <div class="auth-point-icon">✓</div>
            <span>Discover restaurants you’ll love</span>
          </div>

          <div class="auth-point">
            <div class="auth-point-icon">✓</div>
            <span>Order faster with a clean checkout flow</span>
          </div>

          <div class="auth-point">
            <div class="auth-point-icon">✓</div>
            <span>Enjoy a cozy and modern food experience</span>
          </div>
        </div>
      </div>

      <div class="auth-right">
        <div class="auth-card">

          <h2>Sign Up</h2>
          <p class="auth-subtext">Fill in your details to get started with Cibo.</p>

          <form class="auth-form" id="signup-form" novalidate>
            <div class="form-group">
              <label for="signup-name">Full Name</label>
              <input id="signup-name" name="name" type="text" placeholder="Enter your name" autocomplete="name" required>
              <div class="field-error" data-error-for="name" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="signup-email">Email</label>
              <input id="signup-email" name="email" type="email" placeholder="Enter your email" autocomplete="email" required>
              <div class="field-error" data-error-for="email" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="signup-phone">Phone Number</label>
              <input id="signup-phone" name="phone" type="tel" inputmode="numeric" placeholder="Enter your 10-digit phone number" autocomplete="tel" maxlength="10" required>
              <div class="field-error" data-error-for="phone" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="signup-password">Password</label>
              <div class="password-field">
                <input id="signup-password" name="password" type="password" placeholder="Create password" autocomplete="new-password" required>
                <button type="button" class="password-toggle" data-password-toggle="signup-password" aria-label="Show password" aria-pressed="false">
                  <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 3l18 18"></path>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                    <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.2 17.2 0 0 1-4.1 4.8"></path>
                    <path d="M6.2 6.2A17.6 17.6 0 0 0 2 12s3.5 7 10 7a10.9 10.9 0 0 0 5.1-1.2"></path>
                  </svg>
                </button>
              </div>
              <div class="field-error" data-error-for="password" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="signup-confirm-password">Confirm Password</label>
              <div class="password-field">
                <input id="signup-confirm-password" name="confirm_password" type="password" placeholder="Confirm password" autocomplete="new-password" required>
                <button type="button" class="password-toggle" data-password-toggle="signup-confirm-password" aria-label="Show password" aria-pressed="false">
                  <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 3l18 18"></path>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                    <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.2 17.2 0 0 1-4.1 4.8"></path>
                    <path d="M6.2 6.2A17.6 17.6 0 0 0 2 12s3.5 7 10 7a10.9 10.9 0 0 0 5.1-1.2"></path>
                  </svg>
                </button>
              </div>
              <div class="field-error" data-error-for="confirm_password" aria-live="polite"></div>
            </div>

            <div class="auth-form-message" id="signup-form-message" aria-live="polite"></div>

            <button type="submit" class="auth-btn" id="signup-submit" disabled>Create Account</button>
          </form>

          <div class="auth-divider">or</div>

          <button class="secondary-btn">Continue with Google</button>

          <div class="auth-footer">
            Already have an account?
            <a href="login.php">Login</a>
          </div>

        </div>
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
  <script src="cart-manager.js"></script>
  <script src="signup.js?v=non-otp-rollback-1"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

