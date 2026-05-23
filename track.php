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
  <link rel="stylesheet" href="track.css">

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
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }

    .track-meta-box {
      background: white;
      border: 1px solid #e7dfd3;
      border-radius: 20px;
      padding: 18px 16px;
      min-height: 108px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .track-meta-box.is-highlight,
    .track-meta-box:nth-child(2),
    .track-meta-box:nth-child(4) {
      background: linear-gradient(180deg, #f7faf1, #ffffff);
      border-color: #d7e3c4;
    }

    .track-meta-box:nth-child(3) {
      order: 4;
    }

    .track-meta-box:nth-child(4) {
      order: 3;
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
      background: #e8e2d7;
      z-index: 0;
    }

    .track-step {
      position: relative;
      z-index: 1;
      text-align: center;
      transition: opacity 0.28s ease, transform 0.28s ease;
    }

    .track-step-dot {
      width: 46px;
      height: 46px;
      margin: 0 auto 12px;
      border-radius: 50%;
      border: 2px solid #ddd6ca;
      background: #fffdfa;
      color: #948c80;
      font-size: 18px;
      font-weight: 800;
      font-family: Arial, Helvetica, sans-serif;
      font-variant-numeric: lining-nums tabular-nums;
      font-feature-settings: "lnum" 1, "tnum" 1;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 18px rgba(68, 58, 44, 0.04);
      transition: background-color 0.28s ease, border-color 0.28s ease, color 0.28s ease, box-shadow 0.28s ease, transform 0.28s ease;
    }

    .track-step h3 {
      font-size: 16px;
      font-weight: 800;
      color: #23201c;
      margin-bottom: 6px;
      transition: color 0.28s ease;
    }

    .track-step p {
      font-size: 13px;
      line-height: 1.6;
      color: var(--muted);
      max-width: 180px;
      margin: 0 auto;
      transition: color 0.28s ease;
    }

    .track-step.is-upcoming {
      opacity: 0.78;
    }

    .track-step.completed .track-step-dot {
      background: #f5f8ef;
      border-color: #d7e3c4;
      color: #7a8f60;
      box-shadow: 0 10px 20px rgba(105, 128, 77, 0.07);
    }

    .track-step.completed h3 {
      color: #4f6640;
    }

    .track-step.completed p {
      color: #6e7368;
    }

    .track-step.current {
      opacity: 1;
      transform: translateY(-2px);
    }

    .track-step.current .track-step-dot {
      background: linear-gradient(180deg, #f2f8ea, #edf5e3);
      border-color: #bfd39f;
      color: var(--accent);
      box-shadow:
        0 0 0 10px rgba(95, 124, 58, 0.06),
        0 16px 28px rgba(95, 124, 58, 0.11);
    }

    .track-step.current h3 {
      color: var(--accent);
    }

    .track-step.current p {
      color: #5f6657;
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
      transition: background-color 0.22s ease, color 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
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
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(78, 104, 46, 0.18);
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
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(95, 124, 58, 0.12);
    }

    .track-btn:focus-visible {
      outline: 3px solid rgba(95, 124, 58, 0.2);
      outline-offset: 3px;
    }

    .track-btn[disabled] {
      opacity: 0.68;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .track-feedback {
      display: none;
      max-width: 920px;
      margin: 18px auto 0;
      padding: 14px 18px;
      border-radius: 18px;
      border: 1px solid #e7dfd3;
      background: #f6f1e8;
      color: #5f584f;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.6;
    }

    .track-feedback.success {
      color: var(--accent);
      border-color: #d8e4c4;
      background: #eef4e7;
    }

    .track-feedback.error {
      color: #a84747;
      border-color: #e4bdb1;
      background: #fbefea;
    }

    .track-cancel-modal {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: rgba(30, 27, 21, 0.34);
      backdrop-filter: blur(10px);
      z-index: 1200;
    }

    .track-cancel-modal.is-open {
      display: flex;
    }

    .track-cancel-dialog {
      width: min(100%, 420px);
      background: linear-gradient(180deg, #fffdf9, #fbf8f2);
      border: 1px solid #e7dfd3;
      border-radius: 24px;
      box-shadow: 0 28px 54px rgba(55, 45, 31, 0.16);
      padding: 24px 22px 20px;
    }

    .track-cancel-dialog h2 {
      margin: 0 0 10px;
      font-size: 24px;
      font-weight: 800;
      color: #171715;
      letter-spacing: -0.03em;
    }

    .track-cancel-dialog p {
      margin: 0;
      font-size: 14px;
      line-height: 1.7;
      color: #5f584f;
    }

    .track-cancel-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 22px;
    }

    .track-cancel-actions .track-btn {
      min-width: 140px;
      height: 46px;
      border-radius: 14px;
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

    @media (max-width: 560px) {
      .track-cancel-dialog {
        padding: 20px 18px 18px;
        border-radius: 20px;
      }

      .track-cancel-actions {
        flex-direction: column-reverse;
      }

      .track-cancel-actions .track-btn {
        width: 100%;
        min-width: 0;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

<?php
require_once __DIR__ . '/includes/orders.php';

cibo_start_user_session();

$trackOrderNumber = trim((string) ($_GET['order'] ?? ($_SESSION['last_order_number'] ?? '')));
$trackReceiptContext = $trackOrderNumber !== '' ? cibo_fetch_receipt_context($trackOrderNumber) : null;
$trackReceiptViewUrl = (string) ($trackReceiptContext['links']['view'] ?? '');
$trackReceiptDownloadUrl = (string) ($trackReceiptContext['links']['download'] ?? '');
include 'header.php';
?>

  <main class="track-page">
    <section class="track-card">
      <h1>Track Your Order</h1>
      <p class="track-subtext">
        Follow the live order status from the restaurant to your doorstep. The latest backend update is highlighted below.
      </p>

      <div class="track-meta">
        <div class="track-meta-box">
          <h4>Order ID</h4>
          <p id="track-order-id">#--</p>
        </div>
        <div class="track-meta-box">
          <h4>Current Status</h4>
          <p id="track-status-label">--</p>
        </div>
        <div class="track-meta-box">
          <h4>Estimated Delivery</h4>
          <p id="track-delivery-time">Estimated delivery: 25–35 min</p>
        </div>

        <div class="track-meta-box">
          <h4>Payment Status</h4>
          <p id="track-payment-status">--</p>
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
        <a href="index.php" class="track-btn primary">Back to Home</a>
        <a href="menu.php" class="track-btn secondary">Order Again</a>
        <button type="button" class="track-btn secondary" id="track-cancel-button" style="display: none;">Cancel Order</button>
        <?php if ($trackReceiptViewUrl !== '' && $trackReceiptDownloadUrl !== ''): ?>
          <a href="<?= htmlspecialchars($trackReceiptViewUrl, ENT_QUOTES, 'UTF-8') ?>" class="track-btn secondary">View Receipt</a>
          <a href="<?= htmlspecialchars($trackReceiptDownloadUrl, ENT_QUOTES, 'UTF-8') ?>" class="track-btn secondary">Download PDF</a>
        <?php endif; ?>
      </div>
      <div class="track-feedback" id="track-feedback" aria-live="polite"></div>
    </section>
  </main>

  <div class="track-cancel-modal" id="track-cancel-modal" aria-hidden="true">
    <div class="track-cancel-dialog" role="dialog" aria-modal="true" aria-labelledby="track-cancel-title" aria-describedby="track-cancel-copy">
      <h2 id="track-cancel-title">Cancel Order?</h2>
      <p id="track-cancel-copy">This order will stop its delivery journey immediately if it is still newly placed. You can keep the order active if you want the demo flow to continue.</p>
      <div class="track-cancel-actions">
        <button type="button" class="track-btn secondary" id="track-cancel-keep">Keep Order</button>
        <button type="button" class="track-btn primary" id="track-cancel-confirm">Confirm Cancel</button>
      </div>
    </div>
  </div>

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
  <script src="cart-manager.js"></script>
  <script src="track.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>

