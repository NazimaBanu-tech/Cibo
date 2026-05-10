<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Cibo</title>
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
      max-width: 1080px;
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
      padding: 42px 38px;
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
      font-size: 42px;
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -1px;
      color: #171715;
      margin-bottom: 14px;
    }

    .auth-left p {
      font-size: 16px;
      line-height: 1.75;
      color: var(--muted);
      max-width: 420px;
      margin-bottom: 22px;
    }

    .auth-points {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-top: 8px;
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
      padding: 42px 38px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .auth-card {
      width: 100%;
      max-width: 420px;
    }

    .auth-card h2 {
      font-size: 30px;
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

    .form-group input:focus {
      border-color: var(--accent);
      background: #fffdf9;
      box-shadow: 0 0 0 3px rgba(95, 124, 58, 0.15);
    }

    .auth-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: -4px;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: #5f584f;
      font-weight: 600;
    }

    .remember-me input {
      accent-color: var(--accent);
    }

    .forgot-link {
      font-size: 14px;
      font-weight: 700;
      color: var(--accent);
    }

    .forgot-link:hover {
      text-decoration: underline;
    }

    .auth-btn {
      width: 100%;
      height: 52px;
      border: 1px solid var(--accent);
      border-radius: 16px;
      background: linear-gradient(180deg, #89a85c, var(--accent));
      color: white;
      font-size: 16px;
      font-weight: 800;
      cursor: pointer;
      transition: background-color 0.2s ease, border-color 0.2s ease;
      margin-top: 6px;
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

    .forgot-password-panel {
      display: none;
      margin-top: 18px;
      padding: 18px;
      border: 1px solid var(--line);
      border-radius: 18px;
      background: #fbfaf7;
    }

    .forgot-password-panel.is-open {
      display: block;
    }

    .forgot-password-panel h3 {
      margin: 0 0 8px;
      font-size: 18px;
      font-weight: 800;
      color: #171715;
    }

    .forgot-password-panel p {
      margin: 0 0 16px;
      font-size: 14px;
      line-height: 1.6;
      color: var(--muted);
    }

    .reset-actions {
      display: flex;
      gap: 12px;
      margin-top: 6px;
    }

    .reset-actions .secondary-btn,
    .reset-actions .auth-btn {
      margin-top: 0;
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
        font-size: 32px;
      }

      .auth-card h2 {
        font-size: 26px;
      }

      .auth-row {
        flex-direction: column;
        align-items: flex-start;
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
        <span class="auth-badge">Welcome back to Cibo</span>
        <h1>Login and continue your food journey</h1>
        <p>
          Sign in to explore your favourite restaurants, manage your orders,
          and enjoy a smooth food ordering experience with the same cozy Cibo vibe.
        </p>

        <div class="auth-points">
          <div class="auth-point">
            <div class="auth-point-icon">✓</div>
            <span>Fast ordering experience</span>
          </div>

          <div class="auth-point">
            <div class="auth-point-icon">✓</div>
            <span>Track your food journey easily</span>
          </div>

          <div class="auth-point">
            <div class="auth-point-icon">✓</div>
            <span>Save your favourite meals and places</span>
          </div>
        </div>
      </div>

      <div class="auth-right">
        <div class="auth-card">
          <h2>Sign In</h2>
          <p class="auth-subtext">Enter your details to access your Cibo account.</p>

          <form class="auth-form" id="login-form" novalidate>
            <div class="form-group">
              <label for="login-email">Email Address</label>
              <input id="login-email" name="email" type="email" placeholder="Enter your email" autocomplete="email" required>
              <div class="field-error" data-error-for="email" aria-live="polite"></div>
            </div>

            <div class="form-group">
              <label for="login-password">Password</label>
              <div class="password-field">
                <input id="login-password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
                <button type="button" class="password-toggle" data-password-toggle="login-password" aria-label="Show password" aria-pressed="false">
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

            <div class="auth-row">
              <label class="remember-me">
                <input type="checkbox" name="remember_me">
                <span>Remember me</span>
              </label>

              <a href="#" class="forgot-link" id="forgot-password-link">Forgot Password?</a>
            </div>

            <div class="auth-form-message" id="login-form-message" aria-live="polite"></div>

            <button type="submit" class="auth-btn" id="login-submit" disabled>Login</button>
          </form>

          <section class="forgot-password-panel" id="forgot-password-panel">
            <h3>Reset Password</h3>
            <p>Enter your registered email and choose a new password to regain access.</p>
            <form class="auth-form" id="forgot-password-form" novalidate>
              <div class="form-group">
                <label for="reset-email">Email Address</label>
                <input id="reset-email" name="reset_email" type="email" placeholder="Enter your registered email" autocomplete="email" required>
                <div class="field-error" data-error-for="reset_email" aria-live="polite"></div>
              </div>

              <div class="form-group">
                <label for="reset-password">New Password</label>
                <div class="password-field">
                  <input id="reset-password" name="reset_password" type="password" placeholder="Create a new password" autocomplete="new-password" required>
                  <button type="button" class="password-toggle" data-password-toggle="reset-password" aria-label="Show password" aria-pressed="false">
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
                <div class="field-error" data-error-for="reset_password" aria-live="polite"></div>
              </div>

              <div class="form-group">
                <label for="reset-confirm-password">Confirm New Password</label>
                <div class="password-field">
                  <input id="reset-confirm-password" name="reset_confirm_password" type="password" placeholder="Confirm new password" autocomplete="new-password" required>
                  <button type="button" class="password-toggle" data-password-toggle="reset-confirm-password" aria-label="Show password" aria-pressed="false">
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
                <div class="field-error" data-error-for="reset_confirm_password" aria-live="polite"></div>
              </div>

              <div class="auth-form-message" id="forgot-password-message" aria-live="polite"></div>

              <div class="reset-actions">
                <button type="submit" class="auth-btn" id="forgot-password-submit">Update Password</button>
                <button type="button" class="secondary-btn" id="forgot-password-cancel">Cancel</button>
              </div>
            </form>
          </section>

          <div class="auth-divider">or</div>

          <button class="secondary-btn">Continue with Google</button>

          <div class="auth-footer">
            Don’t have an account?
            <a href="signup.php">Create one</a>
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
  <script src="login.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>
