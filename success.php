<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/orders.php';

cibo_start_user_session();

$successOrderNumber = trim((string) ($_GET['order'] ?? ($_SESSION['last_order_number'] ?? '')));
$successReceiptContext = $successOrderNumber !== '' ? cibo_fetch_receipt_context($successOrderNumber) : null;
$successReceiptViewUrl = (string) ($successReceiptContext['links']['view'] ?? '#');
$successReceiptDownloadUrl = (string) ($successReceiptContext['links']['download'] ?? '#');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Success - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">

  <style>
    .success-page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 34px 48px 60px;
    }

    .success-card {
      max-width: 760px;
      margin: 40px auto 0;
      background: linear-gradient(135deg, #fbf8f3, #fffdf9);
      border: 1px solid var(--line);
      border-radius: 28px;
      box-shadow: var(--shadow);
      padding: 42px 36px;
      text-align: center;
    }

    .success-icon {
      width: 88px;
      height: 88px;
      margin: 0 auto 22px;
      border-radius: 50%;
      background: #eef4e7;
      border: 1px solid #d8e4c4;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .success-icon svg {
      width: 42px;
      height: 42px;
      stroke: var(--accent);
      stroke-width: 2.5;
      fill: none;
    }

    .success-card h1 {
      font-size: 38px;
      font-weight: 800;
      letter-spacing: -1px;
      color: #171715;
      margin-bottom: 12px;
    }

    .success-subtext {
      font-size: 16px;
      line-height: 1.7;
      color: var(--muted);
      max-width: 520px;
      margin: 0 auto 28px;
    }

    .success-info {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .info-box {
      background: white;
      border: 1px solid #e7dfd3;
      border-radius: 20px;
      padding: 18px 14px;
    }

    .info-box h4 {
      font-size: 14px;
      font-weight: 700;
      color: #7a746b;
      margin-bottom: 8px;
    }

    .info-box p {
      font-size: 17px;
      font-weight: 800;
      color: #171715;
    }

    .success-note {
      background: #f6f1e8;
      border: 1px solid #e7dfd3;
      border-radius: 20px;
      padding: 16px 18px;
      font-size: 14px;
      line-height: 1.7;
      color: #5f584f;
      margin-bottom: 28px;
    }

    .success-order-preview {
      display: none;
      text-align: left;
      background: white;
      border: 1px solid #e7dfd3;
      border-radius: 20px;
      padding: 18px;
      margin-bottom: 28px;
    }

    .success-order-preview.active {
      display: block;
    }

    .success-order-preview h3 {
      font-size: 18px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 14px;
    }

    .success-order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid #eee5d9;
    }

    .success-order-item:last-child {
      border-bottom: none;
    }

    .success-order-item-main {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
      flex: 1;
    }

    .success-order-thumb {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      object-fit: cover;
      background: #f6f1e8;
      flex-shrink: 0;
      display: block;
    }

    .success-order-copy strong {
      display: block;
      font-size: 15px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 4px;
    }

    .success-order-copy span {
      font-size: 14px;
      color: #6f685f;
    }

    .success-actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .success-btn {
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

    .success-btn.primary {
      background: var(--accent);
      color: white;
      border: none;
    }

    .success-btn.primary:hover {
      background: #4e682e;
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(78, 104, 46, 0.18);
    }

    .success-btn.secondary {
      background: white;
      color: var(--accent);
      border: 1px solid #d9d0c3;
    }

    .success-btn.secondary:hover {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(95, 124, 58, 0.12);
    }

    .success-btn:focus-visible {
      outline: 3px solid rgba(95, 124, 58, 0.2);
      outline-offset: 3px;
    }

    @media (max-width: 900px) {
      .success-page {
        padding-left: 20px;
        padding-right: 20px;
      }

      .success-info {
        grid-template-columns: 1fr;
      }

      .success-card {
        padding: 34px 22px;
      }
    }

    @media (max-width: 640px) {
      .success-card h1 {
        font-size: 30px;
      }

      .success-subtext {
        font-size: 15px;
      }

      .success-btn {
        width: 100%;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>

  <?php include 'header.php'; ?>

  <main class="success-page">
    <section class="success-card">

      <div class="success-icon">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M20 6L9 17l-5-5"></path>
        </svg>
      </div>

      <p class="success-subtext">&#127881; Thank you for ordering with Cibo!</p>
      <h1>Order Placed Successfully!</h1>
      <p class="success-subtext">
        Your order has been confirmed and is being prepared with care. Sit back and relax, your delicious meal is on the way.
      </p>

      <div class="success-info">
        <div class="info-box">
          <h4>Order ID</h4>
          <p id="success-order-id">#--</p>
        </div>

        <div class="info-box">
          <h4>Payment Method</h4>
          <p id="success-payment-method">--</p>
        </div>

        <div class="info-box">
          <h4>Payment Status</h4>
          <p id="success-order-total">₹0</p>
        </div>
      </div>

      <div class="success-note" id="success-note">
        A confirmation message has been sent to your registered number. You can continue exploring restaurants and order more of your favourites anytime.
      </div>

      <div class="success-order-preview" id="success-order-preview">
        <h3>Order Items</h3>
      </div>

      <div class="success-actions">
        <a href="index.php" class="success-btn primary">Back to Home</a>
        <a href="track.php" class="success-btn secondary" id="track-order-link">Track Order</a>
        <a href="<?= htmlspecialchars($successReceiptViewUrl, ENT_QUOTES, 'UTF-8') ?>" class="success-btn secondary" id="view-receipt-link">View Receipt</a>
        <a href="<?= htmlspecialchars($successReceiptDownloadUrl, ENT_QUOTES, 'UTF-8') ?>" class="success-btn secondary" id="download-receipt-link">Download PDF</a>
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
      <p>&copy; 2026 Cibo. Crafted for better food moments.</p>
    </div>
  </footer>

  <script src="order-api.js"></script>
  <script src="favorites.js"></script>
  <script src="auth-display.js"></script>
  <script>
    (() => {
      const orderIdNode = document.getElementById('success-order-id');
      const paymentMethodNode = document.getElementById('success-payment-method');
      const paymentStatusNode = document.getElementById('success-order-total');
      const noteNode = document.getElementById('success-note');
      const orderPreviewNode = document.getElementById('success-order-preview');
      const trackLink = document.getElementById('track-order-link');
      const viewReceiptLink = document.getElementById('view-receipt-link');
      const downloadReceiptLink = document.getElementById('download-receipt-link');
      const params = new URLSearchParams(window.location.search);
      const orderNumber = params.get('order') || '';

      function formatPrice(amount) {
        return '\u20B9' + (Number(amount) || 0);
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }

      function getPaymentMethodLabel(method) {
        const normalized = String(method || '').trim().toLowerCase();

        if (normalized === 'cod') {
          return 'Cash on Delivery';
        }

        if (normalized === 'upi') {
          return 'UPI Payment';
        }

        if (normalized === 'card') {
          return 'Card Payment';
        }

        return 'Payment';
      }

      if (trackLink && orderNumber) {
        trackLink.href = 'track.php?order=' + encodeURIComponent(orderNumber);
      }

      if (orderIdNode && orderNumber) {
        orderIdNode.textContent = '#' + orderNumber;
      }

      if (!window.CiboOrdersApi) {
        return;
      }

      window.CiboOrdersApi.get(orderNumber)
        .then((response) => {
          const order = response.order || {};
          const paymentMethod = String(order.payment_method || '').trim().toLowerCase();
          const paymentStatus = String(order.payment_status || '').trim();
          const paymentStatusLabel = String(order.payment_status_label || paymentStatus || '--').trim() || '--';
          const totalAmount = Number(order.total_amount) || 0;

          if (viewReceiptLink && order.receipt_view_url) {
            viewReceiptLink.href = String(order.receipt_view_url);
          }

          if (downloadReceiptLink && order.receipt_download_url) {
            downloadReceiptLink.href = String(order.receipt_download_url);
          }

          if (orderIdNode) {
            orderIdNode.textContent = '#' + String(order.order_number || '--');
          }

          if (paymentMethodNode) {
            paymentMethodNode.textContent = getPaymentMethodLabel(paymentMethod);
          }

          if (paymentStatusNode) {
            paymentStatusNode.textContent = paymentStatusLabel;
          }

          const items = Array.isArray(order.items) ? order.items : [];

          if (orderPreviewNode && items.length) {
            orderPreviewNode.classList.add('active');
            orderPreviewNode.innerHTML = `
              <h3>Order Items</h3>
              ${items.map((item) => {
                const quantity = Number(item.quantity) || 1;
                const lineTotal = Number(item.line_total) || ((Number(item.price) || 0) * quantity);

                return `
                  <div class="success-order-item">
                    <div class="success-order-item-main">
                      ${item.image ? `<img class="success-order-thumb" src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name || 'Item')}">` : ''}
                      <div class="success-order-copy">
                        <strong>${escapeHtml(item.name || 'Item')}</strong>
                        <span>Qty: ${quantity}</span>
                      </div>
                    </div>
                    <span>${formatPrice(lineTotal)}</span>
                  </div>
                `;
              }).join('')}
            `;
          }
          if (noteNode) {
            noteNode.textContent = `${getPaymentMethodLabel(paymentMethod)}. Payment Status: ${paymentStatusLabel}. Order Total: ${formatPrice(totalAmount)}.`;
          }
        })
        .catch(() => null);
    })();
  </script>
</body>
</html>
