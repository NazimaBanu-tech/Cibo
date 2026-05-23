<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-data.php';

cibo_admin_guest_only();

$errorMessage = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $errorMessage = cibo_admin_attempt_login($emailValue, $password) ?? '';

    if ($errorMessage === '') {
        cibo_redirect(CIBO_ADMIN_BASE . '/panel.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cibo Admin Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .password-field {
      position: relative;
      display: flex;
      align-items: center;
    }

    .password-field .field {
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

    .field::-ms-reveal,
    .field::-ms-clear {
      display: none;
    }
  </style>
</head>
<body data-auth-mode="php">
  <main class="login-shell">
    <section class="admin-card login-card">
      <div class="brand">
        <div class="brand-logo-shell">
          <img src="../images/logo.png" alt="Cibo" class="brand-logo">
        </div>
        <div class="brand-copy">
          <h1>Cibo</h1>
          <p>Admin panel access</p>
        </div>
      </div>

      <h2>Welcome back</h2>
      <p>Sign in to manage restaurants, menu items, orders, users, and your admin profile without touching the customer-facing experience.</p>

      <div class="flash error" id="admin-login-error"<?= $errorMessage !== '' ? '' : ' style="display: none;"' ?>><span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></span></div>

      <form class="stack" id="admin-login-form" method="post" novalidate>
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input class="field" id="email" name="email" type="email" placeholder="admin@cibo.local" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="password-field">
            <input class="field" id="password" name="password" type="password" placeholder="Enter your password" required>
            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
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
        </div>

        <button class="button" type="submit">Sign In to Admin</button>
      </form>

      <div class="login-help">
        Demo admin access:
        <strong>admin@cibo.local</strong>
        /
        <strong>cibo123</strong>
      </div>
    </section>
  </main>
  <script src="assets/admin.js"></script>
</body>
</html>
