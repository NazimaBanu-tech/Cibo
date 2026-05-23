<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/user-auth.php';

if (!cibo_current_user()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Cibo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu.css">
  <style>
    .account-page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 34px 48px 60px;
    }

    .account-shell {
      background: linear-gradient(135deg, #f8f4ed, #fffdf9);
      border: 1px solid var(--line);
      border-radius: 28px;
      box-shadow: var(--shadow);
      padding: 28px;
    }

    .account-topbar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      margin-bottom: 24px;
    }

    .account-topbar h1 {
      font-size: 38px;
      font-weight: 800;
      color: #171715;
      letter-spacing: -1px;
      margin-bottom: 8px;
    }

    .account-topbar p {
      font-size: 16px;
      line-height: 1.7;
      color: var(--muted);
      max-width: 620px;
    }

    .account-layout {
      display: grid;
      grid-template-columns: 280px minmax(0, 1fr);
      gap: 26px;
      align-items: start;
    }

    .account-sidebar,
    .account-main,
    .order-card,
    .address-card,
    .form-card,
    .empty-state,
    .detail-box {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: var(--shadow);
    }

    .account-sidebar {
      padding: 18px;
      position: sticky;
      top: 110px;
    }

    .sidebar-title {
      font-size: 16px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 14px;
      padding: 0 8px;
    }

    .sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .sidebar-link {
      width: 100%;
      min-height: 54px;
      border: 1px solid transparent;
      border-radius: 18px;
      background: transparent;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 16px;
      font-family: 'Manrope', sans-serif;
      font-size: 16px;
      font-weight: 800;
      color: #2a2723;
      cursor: pointer;
      text-align: left;
      transition: background-color 0.22s ease, color 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
    }

    .sidebar-link:hover {
      background: #f6f1e8;
      border-color: #e7dfd3;
    }

    .sidebar-link.active {
      background: #eef4e7;
      border-color: #d8e4c4;
      color: var(--accent);
    }

    .sidebar-link svg {
      width: 20px;
      height: 20px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
      flex-shrink: 0;
    }

    .account-main {
      padding: 26px;
      min-height: 560px;
    }

    .section-panel {
      display: none;
    }

    .section-panel.active {
      display: block;
    }

    .section-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 22px;
    }

    .section-head h2 {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -0.8px;
      color: #171715;
      margin-bottom: 6px;
    }

    .section-head p {
      font-size: 15px;
      line-height: 1.7;
      color: var(--muted);
      max-width: 600px;
    }

    .account-btn {
      min-width: 150px;
      height: 48px;
      border-radius: 16px;
      border: 1px solid #d9d0c3;
      background: white;
      color: var(--accent);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-family: 'Manrope', sans-serif;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      transition: 0.2s ease;
      text-decoration: none;
      padding: 0 18px;
    }

    .account-btn:hover {
      background: var(--accent);
      border-color: var(--accent);
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(95, 124, 58, 0.12);
    }

    .account-btn.primary {
      background: var(--accent);
      border-color: var(--accent);
      color: white;
    }

    .account-btn.primary:hover {
      background: #4e682e;
      border-color: #4e682e;
      box-shadow: 0 14px 26px rgba(78, 104, 46, 0.18);
    }

    .account-btn:focus-visible,
    .sidebar-link:focus-visible {
      outline: 3px solid rgba(95, 124, 58, 0.2);
      outline-offset: 3px;
    }

    .orders-list,
    .addresses-grid,
    .favorites-grid {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .order-card,
    .address-card,
    .favorite-card,
    .form-card,
    .empty-state {
      padding: 22px;
    }

    .order-header,
    .address-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 14px;
    }

    .order-card h3,
    .address-card h3,
    .favorite-card h3,
    .form-card h3 {
      font-size: 24px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 6px;
    }

    .order-summary,
    .address-copy,
    .muted-copy {
      font-size: 15px;
      line-height: 1.7;
      color: var(--muted);
    }

    .meta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 10px;
    }

    .meta-pill,
    .status-badge,
    .address-tag {
      display: inline-flex;
      align-items: center;
      min-height: 36px;
      border-radius: 999px;
      padding: 8px 14px;
      background: #fbf8f3;
      border: 1px solid #e1d7ca;
      font-size: 13px;
      font-weight: 800;
      color: #4b463f;
    }

    .status-badge {
      background: #eef4e7;
      border-color: #d8e4c4;
      color: var(--accent);
      white-space: nowrap;
    }

    .address-tag {
      color: var(--accent);
    }

    .order-actions,
    .address-actions,
    .favorite-actions,
    .form-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 18px;
    }

    .favorites-section h3 {
      font-size: 22px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 6px;
    }

    .favorites-section .muted-copy {
      margin-bottom: 14px;
    }

    .favorite-card {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .favorite-media {
      width: 92px;
      height: 92px;
      border-radius: 22px;
      overflow: hidden;
      background: #f6f1e8;
      flex-shrink: 0;
    }

    .favorite-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .favorite-copy {
      flex: 1;
      min-width: 0;
    }

    .favorite-copy h3 {
      margin-bottom: 4px;
    }

    .favorite-pill {
      display: inline-flex;
      align-items: center;
      min-height: 32px;
      border-radius: 999px;
      padding: 7px 12px;
      background: #eef4e7;
      border: 1px solid #d8e4c4;
      font-size: 12px;
      font-weight: 800;
      color: var(--accent);
      margin-bottom: 10px;
    }

    .detail-box {
      display: none;
      margin-top: 18px;
      padding: 18px;
    }

    .detail-box.active {
      display: block;
    }

    .detail-box h4 {
      font-size: 18px;
      font-weight: 800;
      color: #171715;
      margin-bottom: 14px;
    }

    .detail-row,
    .detail-total {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      padding: 12px 0;
      border-bottom: 1px solid #eee5d9;
      font-size: 15px;
      color: #4b463f;
    }

    .detail-total {
      border-bottom: none;
      font-size: 18px;
      font-weight: 800;
      color: #171715;
    }

    .detail-row strong {
      display: block;
      color: #171715;
      margin-bottom: 4px;
    }

    .detail-item-main {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
      flex: 1;
    }

    .detail-item-thumb {
      width: 54px;
      height: 54px;
      border-radius: 16px;
      object-fit: cover;
      background: #f6f1e8;
      flex-shrink: 0;
      display: block;
    }

    .empty-state {
      background: #f6f1e8;
      border-color: #e7dfd3;
      color: #5f584f;
      font-size: 15px;
      line-height: 1.7;
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

    .form-group textarea {
      min-height: 110px;
      resize: vertical;
    }

    .section-note {
      margin-top: 14px;
      font-size: 14px;
      font-weight: 700;
      color: var(--accent);
    }

    .section-note.error {
      color: #a84747;
    }

    .section-note.success {
      color: var(--accent);
    }

    .status-badge.is-cancelled {
      background: #fbefea;
      border-color: #e4bdb1;
      color: #b05a44;
    }

    @media (max-width: 980px) {
      .account-page {
        padding-left: 20px;
        padding-right: 20px;
      }

      .account-shell {
        padding: 20px;
      }

      .account-layout {
        grid-template-columns: 1fr;
      }

      .account-sidebar {
        position: static;
      }

      .sidebar-nav {
        flex-direction: row;
        flex-wrap: wrap;
      }

      .sidebar-link {
        flex: 1 1 180px;
      }
    }

    @media (max-width: 640px) {
      .account-topbar {
        flex-direction: column;
      }

      .account-topbar h1,
      .section-head h2 {
        font-size: 29px;
      }

      .account-main,
      .order-card,
      .address-card,
      .form-card,
      .empty-state {
        padding: 18px;
      }

      .order-header,
      .address-header,
      .section-head {
        flex-direction: column;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }

      .form-group.full {
        grid-column: span 1;
      }

      .account-btn {
        width: 100%;
      }

      .favorite-card {
        flex-direction: column;
        align-items: flex-start;
      }

      .favorite-media {
        width: 100%;
        height: 200px;
      }
    }
  </style>
  <link rel="stylesheet" href="global.css">
</head>
<body>
  <?php include 'header.php'; ?>

  <main class="account-page">
    <section class="account-shell">
      <div class="account-topbar">
        <div>
          <h1>My Account</h1>
          <p>Keep track of your orders, manage delivery addresses, and update your Cibo account details from one clean place.</p>
        </div>
        <a href="index.php" class="account-btn">Continue Ordering</a>
      </div>

      <div class="account-layout">
        <aside class="account-sidebar">
          <div class="sidebar-title">Profile</div>
          <nav class="sidebar-nav" aria-label="Account sections">
            <button class="sidebar-link active" type="button" data-view="orders">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 6h13"></path>
                <path d="M8 12h13"></path>
                <path d="M8 18h13"></path>
                <path d="M3 6h.01"></path>
                <path d="M3 12h.01"></path>
                <path d="M3 18h.01"></path>
              </svg>
              <span>Orders</span>
            </button>
            <button class="sidebar-link" type="button" data-view="favorites">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 21s-6.7-4.35-9.18-8.18C.7 9.55 2 5.75 5.58 4.67c2.07-.62 4.13.08 5.42 1.77 1.29-1.69 3.35-2.39 5.42-1.77 3.58 1.08 4.88 4.88 2.76 8.15C18.7 16.65 12 21 12 21z"></path>
              </svg>
              <span>Favorites</span>
            </button>
            <button class="sidebar-link" type="button" data-view="addresses">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"></path>
                <circle cx="12" cy="10" r="2.5"></circle>
              </svg>
              <span>Addresses</span>
            </button>
            <button class="sidebar-link" type="button" data-view="settings">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8.91 4.6H9A1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.24.5.87 1 1.51 1H21a2 2 0 1 1 0 4h-.09c-.64 0-1.27.5-1.51 1z"></path>
              </svg>
              <span>Account Settings</span>
            </button>
          </nav>
        </aside>

        <section class="account-main">
          <div class="section-panel active" id="panel-orders">
          <div class="section-head">
            <div>
              <h2>Past Orders</h2>
              <p>See your recent orders, open the item breakdown, and reorder the same meal back into your cart.</p>
            </div>
          </div>
          <p class="section-note" id="orders-note" style="display: none;"></p>
          <div class="orders-list" id="orders-list"></div>
        </div>

          <div class="section-panel" id="panel-favorites">
            <div class="section-head">
              <div>
                <h2>Favorites</h2>
                <p>Keep your saved dishes in one place so it is easy to come back to what you love.</p>
              </div>
            </div>
            <div class="favorites-section">
              <h3>Saved Dishes</h3>
              <p class="muted-copy">See the meals you have bookmarked for your next order.</p>
              <div class="favorites-grid" id="favorite-items-list"></div>
            </div>
          </div>

          <div class="section-panel" id="panel-addresses">
            <div class="section-head">
              <div>
                <h2>Saved Addresses</h2>
                <p>Add multiple delivery addresses and quickly edit or delete them whenever something changes.</p>
              </div>
              <button class="account-btn primary" type="button" id="add-address-button">Add Address</button>
            </div>
            <div class="addresses-grid" id="addresses-list"></div>
            <div class="form-card" id="address-form-card" style="display: none; margin-top: 18px;">
              <h3 id="address-form-title">Add Address</h3>
              <p class="muted-copy" style="margin-bottom: 16px;">Save an address with a simple label so it is easier to choose during checkout.</p>
              <form id="address-form">
                <input type="hidden" name="editIndex" value="">
                <input type="hidden" name="editLocalId" value="">
                <div class="form-grid">
                  <div class="form-group">
                    <label for="address-type">Address Label</label>
                    <select id="address-type" name="type">
                      <option value="Home">Home</option>
                      <option value="Work">Work</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="address-name">Name</label>
                    <input id="address-name" name="name" type="text" placeholder="Enter your name">
                  </div>
                  <div class="form-group">
                    <label for="address-phone">Phone</label>
                    <input id="address-phone" name="phone" type="text" placeholder="Enter your phone number">
                  </div>
                  <div class="form-group full">
                    <label for="address-line">Address</label>
                    <textarea id="address-line" name="address" placeholder="Enter the full delivery address"></textarea>
                  </div>
                  <div class="form-group full">
                    <label for="address-landmark">Landmark</label>
                    <input id="address-landmark" name="landmark" type="text" placeholder="Nearby landmark">
                  </div>
                  <div class="form-group">
                    <label for="address-city">City</label>
                    <input id="address-city" name="city" type="text" placeholder="Enter your city">
                  </div>
                  <div class="form-group">
                    <label for="address-pincode">Pincode</label>
                    <input id="address-pincode" name="pincode" type="text" inputmode="numeric" maxlength="6" placeholder="Enter your pincode">
                  </div>
                </div>
                <div class="form-actions">
                  <button class="account-btn primary" type="submit">Save Address</button>
                  <button class="account-btn" type="button" id="cancel-address-button">Cancel</button>
                </div>
                <p class="section-note" id="address-note" style="display: none;"></p>
              </form>
            </div>
          </div>

          <div class="section-panel" id="panel-settings">
            <div class="section-head">
              <div>
                <h2>Account Settings</h2>
                <p>Keep the important basics here so your profile name, email, and phone stay up to date across Cibo.</p>
              </div>
            </div>
            <div class="form-card">
              <h3>Profile Details</h3>
              <p class="muted-copy" style="margin-bottom: 16px;">These details are stored locally for your current Cibo experience.</p>
              <form id="account-form">
                <div class="form-grid">
                  <div class="form-group">
                    <label for="account-name">Full Name</label>
                    <input id="account-name" name="name" type="text" placeholder="Enter your full name">
                  </div>
                  <div class="form-group">
                    <label for="account-phone">Phone</label>
                    <input id="account-phone" name="phone" type="text" placeholder="Enter your phone number">
                  </div>
                  <div class="form-group full">
                    <label for="account-email">Email</label>
                    <input id="account-email" name="email" type="email" placeholder="Enter your email">
                  </div>
                </div>
                <div class="form-actions">
                  <button class="account-btn primary" type="submit">Save Changes</button>
                </div>
                <p class="section-note" id="account-note" style="display: none;"></p>
              </form>
            </div>
          </div>
        </section>
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

  <script src="favorites.js"></script>
  <script src="order-api.js"></script>
  <script src="account-api.js"></script>
  <script src="cart-manager.js"></script>
  <script>
    (() => {
      const ACCOUNT_VIEW_KEY = 'cibo_myaccount_view';
      const favoritesApi = window.CiboFavorites;
      const cartManager = window.CiboCartManager;

      const sidebarLinks = Array.from(document.querySelectorAll('.sidebar-link'));
      const panels = {
        orders: document.getElementById('panel-orders'),
        favorites: document.getElementById('panel-favorites'),
        addresses: document.getElementById('panel-addresses'),
        settings: document.getElementById('panel-settings')
      };
      const ordersList = document.getElementById('orders-list');
      const ordersNote = document.getElementById('orders-note');
      const favoriteItemsList = document.getElementById('favorite-items-list');
      const addressesList = document.getElementById('addresses-list');
      const addressFormCard = document.getElementById('address-form-card');
      const addressForm = document.getElementById('address-form');
      const addressFormTitle = document.getElementById('address-form-title');
      const addAddressButton = document.getElementById('add-address-button');
      const cancelAddressButton = document.getElementById('cancel-address-button');
      const addressNote = document.getElementById('address-note');
      const accountForm = document.getElementById('account-form');
      const accountNote = document.getElementById('account-note');
      const addressSubmitButton = addressForm?.querySelector('button[type="submit"]');
      const accountSubmitButton = accountForm?.querySelector('button[type="submit"]');
      let serverOrdersCache = null;
      let serverAddressesCache = null;
      let serverAccountCache = null;
      let isAuthenticated = false;
      let isAddressSaving = false;
      let isAccountSaving = false;
      let isOrdersRefreshing = false;
      let isOrderCancelling = false;
      let lastOrdersRefreshAt = 0;

      function readJSON(key, fallback) {
        try {
          const rawValue = localStorage.getItem(key);
          if (!rawValue) {
            return fallback;
          }
          const parsedValue = JSON.parse(rawValue);
          return parsedValue === null ? fallback : parsedValue;
        } catch (error) {
          return fallback;
        }
      }

      function saveJSON(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
      }

      function clearLegacyAuthStorage() {
        localStorage.removeItem('cibo_user');
        localStorage.removeItem('cibo_account');
      }

      function cleanupLegacySharedData() {
        localStorage.removeItem('orders');
        localStorage.removeItem('cibo_orders');
        localStorage.removeItem('cibo_address');
      }

      function handleUnauthorized() {
        isAuthenticated = false;
        serverAccountCache = null;
        serverAddressesCache = [];
        serverOrdersCache = [];
        clearLegacyAuthStorage();
        window.location.href = 'login.php';
      }

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }

      function formatPrice(amount) {
        return '\u20B9' + (Number(amount) || 0);
      }

      function renderLoadingState(target, message) {
        if (!target) {
          return;
        }

        target.innerHTML = `<div class="empty-state">${escapeHtml(message)}</div>`;
      }

      function setSectionNote(node, message, type = '') {
        if (!node) {
          return;
        }

        node.textContent = message;
        node.style.display = message ? 'block' : 'none';
        node.className = 'section-note' + (type ? ` ${type}` : '');
      }

      function updateAddressSubmitState() {
        if (!addressSubmitButton) {
          return;
        }

        addressSubmitButton.disabled = isAddressSaving;
        addressSubmitButton.toggleAttribute('aria-busy', isAddressSaving);
        addressSubmitButton.textContent = isAddressSaving ? 'Saving Address...' : 'Save Address';
      }

      function updateAccountSubmitState() {
        if (!accountSubmitButton) {
          return;
        }

        accountSubmitButton.disabled = isAccountSaving;
        accountSubmitButton.toggleAttribute('aria-busy', isAccountSaving);
        accountSubmitButton.textContent = isAccountSaving ? 'Saving Changes...' : 'Save Changes';
      }

      function getOrderCancellationMessage(error) {
        const safeMessage = String(error?.message || '').trim();

        if (/only newly placed orders can be cancelled/i.test(safeMessage)) {
          return 'This order has already progressed and can no longer be cancelled.';
        }

        if (/unable to find the order/i.test(safeMessage) || /order not found/i.test(safeMessage)) {
          return 'We could not find that order anymore. Please refresh your order history.';
        }

        if (!navigator.onLine) {
          return 'Network connection lost. Please check your internet and try again.';
        }

        return safeMessage || 'Unable to cancel the order right now. Please try again.';
      }

      function normalizeItems(items) {
        if (Array.isArray(items)) {
          return items.filter((item) => item && typeof item === 'object');
        }

        if (items && typeof items === 'object') {
          return Object.values(items).filter((item) => item && typeof item === 'object');
        }

        return [];
      }

      function readOrders() {
        return Array.isArray(serverOrdersCache) ? serverOrdersCache : [];
      }

      async function loadOrders() {
        if (!window.CiboOrdersApi) {
          return;
        }

        try {
          const response = await window.CiboOrdersApi.listMine();
          const serverOrders = Array.isArray(response.orders) ? response.orders : [];

          const normalizedOrders = serverOrders.map((order) => {
            const items = normalizeItems(order.items || []);
            const rawDate = order.placed_at || order.created_at;
            const parsedDate = rawDate ? new Date(rawDate) : null;
            const displayDate = parsedDate && !Number.isNaN(parsedDate.getTime())
              ? parsedDate.toLocaleString('en-IN', {
                  day: 'numeric',
                  month: 'short',
                  year: 'numeric',
                  hour: 'numeric',
                  minute: '2-digit'
                })
              : (rawDate || 'Date unavailable');
            const firstItem = items[0];
            const summary = !items.length
              ? 'No items available'
              : ((Number(firstItem.quantity) || 1) + ' ' + (firstItem.name || 'Item') + (items.length > 1 ? ' + ' + (items.length - 1) + ' more item' + (items.length === 2 ? '' : 's') : ''));

            return {
              id: order.order_number || order.id,
              orderNumber: order.order_number || order.id,
              restaurant: order.restaurant_name || 'Cibo Order',
              total: Number(order.total_amount) || 0,
              summary,
              items,
              deliveryAddress: String(order.delivery_address || '').trim(),
              date: displayDate,
              rawStatus: String(order.order_status || '').trim().toLowerCase(),
              status: order.order_status_label || order.order_status || '--',
              paymentStatus: order.payment_status_label || order.payment_status || '--',
              receiptViewUrl: String(order.receipt_view_url || '').trim(),
              receiptDownloadUrl: String(order.receipt_download_url || '').trim(),
              canCancel: String(order.order_status || '').trim().toLowerCase() === 'placed'
            };
          });

          serverOrdersCache = normalizedOrders.length ? normalizedOrders : null;
        } catch (error) {
          serverOrdersCache = null;
          throw error;
        }
      }

      async function refreshOrdersAndRender(options = {}) {
        if (isOrdersRefreshing) {
          return;
        }

        const now = Date.now();
        const force = options.force === true;

        if (!force && now - lastOrdersRefreshAt < 1200) {
          return;
        }

        try {
          isOrdersRefreshing = true;
          await loadOrders();
          lastOrdersRefreshAt = Date.now();
          renderOrders();
        } finally {
          isOrdersRefreshing = false;
        }
      }

      async function loadAccount() {
        if (!window.CiboAccountApi) {
          return;
        }

        try {
          const response = await window.CiboAccountApi.getProfile();
          serverAccountCache = response?.user && typeof response.user === 'object' ? response.user : null;

          if (!serverAccountCache) {
            throw new Error('Unable to load the current account.');
          }

          isAuthenticated = true;
        } catch (error) {
          if (error?.ciboAuthError) {
            handleUnauthorized();
            throw error;
          }

          serverAccountCache = null;
          isAuthenticated = false;
        }
      }

      async function loadAddresses() {
        if (!window.CiboAccountApi || !isAuthenticated) {
          return;
        }

        try {
          const response = await window.CiboAccountApi.listAddresses();
          serverAddressesCache = Array.isArray(response.addresses) ? response.addresses : [];
        } catch (error) {
          if (error?.ciboAuthError) {
            handleUnauthorized();
            throw error;
          }

          serverAddressesCache = null;
        }
      }

      function renderOrders() {
        const orders = readOrders();

        if (!orders.length) {
          ordersList.innerHTML = '<div class="empty-state">No orders yet. Your Cibo order history will appear here after your first completed checkout.</div>';
          return;
        }

        ordersList.innerHTML = orders.map((order, index) => {
          const detailMarkup = order.items.length
            ? order.items.map((item) => {
                const quantity = Number(item.quantity) || 1;
                const lineTotal = (Number(item.price) || 0) * quantity;
                return `
                  <div class="detail-row">
                    <div class="detail-item-main">
                      ${item.image ? `<img class="detail-item-thumb" src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name || 'Item')}">` : ''}
                      <div>
                      <strong>${escapeHtml(item.name || 'Item')}</strong>
                      <span>Qty: ${quantity}</span>
                      </div>
                    </div>
                    <span>${formatPrice(lineTotal)}</span>
                  </div>
                `;
              }).join('')
            : '<div class="empty-state">Order item details are not available for this entry yet.</div>';

          return `
            <article class="order-card">
              <div class="order-header">
                <div>
                  <h3>${escapeHtml(order.restaurant)}</h3>
                  <p class="order-summary">${escapeHtml(order.summary)}</p>
                  <div class="meta-row">
                    <span class="meta-pill">#${escapeHtml(order.orderNumber || order.id || '--')}</span>
                    <span class="meta-pill">${escapeHtml(order.date)}</span>
                    <span class="meta-pill">${escapeHtml(order.paymentStatus || '--')}</span>
                    <span class="meta-pill">${formatPrice(order.total)}</span>
                  </div>
                </div>
                <span class="status-badge ${order.rawStatus === 'cancelled' ? 'is-cancelled' : ''}">${escapeHtml(order.status)}</span>
              </div>
              <div class="order-actions">
                <button class="account-btn" type="button" data-action="toggle-order" data-index="${index}">View Details</button>
                ${order.receiptViewUrl ? `<a class="account-btn" href="${escapeHtml(order.receiptViewUrl)}">View Receipt</a>` : ''}
                ${order.receiptDownloadUrl ? `<a class="account-btn" href="${escapeHtml(order.receiptDownloadUrl)}">Download PDF</a>` : ''}
                ${order.canCancel ? `<button class="account-btn" type="button" data-action="cancel-order" data-index="${index}">Cancel Order</button>` : ''}
                <button class="account-btn primary" type="button" data-action="reorder" data-index="${index}">Reorder</button>
              </div>
              <div class="detail-box" id="order-detail-${index}">
                <h4>Order Details</h4>
                ${detailMarkup}
                ${order.deliveryAddress ? `
                  <div class="detail-row">
                    <div>
                      <strong>Delivery Address</strong>
                      <span>${escapeHtml(order.deliveryAddress)}</span>
                    </div>
                    <span></span>
                  </div>
                ` : ''}
                <div class="detail-total">
                  <span>Total</span>
                  <span>${formatPrice(order.total)}</span>
                </div>
              </div>
            </article>
          `;
        }).join('');
      }

      function renderFavorites() {
        const favorites = favoritesApi?.readFavorites?.() || { items: [] };

        if (!favorites.items.length) {
          favoriteItemsList.innerHTML = '<div class="empty-state">No saved favorites yet. Add a dish from any menu to keep it close for your next order.</div>';
          return;
        }

        favoriteItemsList.innerHTML = favorites.items.map((item) => `
          <article class="favorite-card">
            <div class="favorite-media">
              ${item.image ? `<img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">` : ''}
            </div>
            <div class="favorite-copy">
              <span class="favorite-pill">${escapeHtml(item.tagText || 'Saved dish')}</span>
              <h3>${escapeHtml(item.name)}</h3>
              <p class="muted-copy">${escapeHtml(item.description || item.restaurantName || 'Saved dish')}</p>
              <p class="muted-copy">${escapeHtml(item.restaurantName || '')}${item.price ? ' • ' + formatPrice(item.price) : ''}</p>
              <div class="favorite-actions">
                <a class="account-btn primary" href="${escapeHtml(item.restaurantHref || ('menu.php?restaurant=' + encodeURIComponent(item.restaurantSlug || '')))}">View Restaurant</a>
                <button class="account-btn" type="button" data-action="remove-favorite-item" data-id="${escapeHtml(item.id)}">Remove</button>
              </div>
            </div>
          </article>
        `).join('');
      }

      function buildCartFromOrder(order) {
        return order.items.reduce((cart, item, index) => {
          const key = item.id || item.cartId || item.name || ('item-' + index);
          cart[key] = {
            id: item.id || key,
            name: item.name || 'Item',
            price: Number(item.price) || 0,
            quantity: Number(item.quantity) || 1,
            description: item.description || '',
            image: item.image || '',
            imageAlt: item.imageAlt || item.name || 'Food item',
            tagText: item.tagText || item.tag || '',
            tagClass: item.tagClass || '',
            restaurant: item.restaurant || order.restaurant
          };
          return cart;
        }, {});
      }

      function normalizeAddresses() {
        if (!isAuthenticated) {
          return [];
        }

        if (Array.isArray(serverAddressesCache)) {
          return serverAddressesCache.filter((item) => item && typeof item === 'object').map((item) => ({
            id: item.id || 0,
            type: item.type || item.label || 'Home',
            name: item.name || '',
            phone: item.phone || '',
            address: item.address || item.full_address || '',
            landmark: item.landmark || '',
            city: item.city || '',
            state: item.state || '',
              pincode: item.pincode || item.postal_code || ''
          }));
        }

        return [];
      }

      function renderAddresses() {
        const addresses = normalizeAddresses();

        if (!addresses.length) {
          addressesList.innerHTML = '<div class="empty-state">No saved addresses yet. Add your delivery details here to make checkout faster next time.</div>';
          return;
        }

        addressesList.innerHTML = addresses.map((address, index) => `
          <article class="address-card">
            <div class="address-header">
              <div>
                <div class="meta-row" style="margin-top: 0; margin-bottom: 10px;">
                  <span class="address-tag">${escapeHtml(address.type)}</span>
                </div>
                <h3>${escapeHtml(address.name || 'Saved Address')}</h3>
                <p class="address-copy">${escapeHtml(address.address || 'Address not available')}</p>
                <p class="address-copy">${escapeHtml(address.landmark ? 'Landmark: ' + address.landmark : 'Landmark not added')}</p>
                <p class="address-copy">${escapeHtml(address.city ? 'City: ' + address.city : 'City not added')}${address.pincode ? escapeHtml(' | Pincode: ' + address.pincode) : ''}</p>
                <p class="address-copy">${escapeHtml(address.phone ? 'Phone: ' + address.phone : 'Phone not added')}</p>
              </div>
            </div>
            <div class="address-actions">
              <button class="account-btn" type="button" data-action="edit-address" data-index="${index}">Edit</button>
              <button class="account-btn" type="button" data-action="delete-address" data-index="${index}">Delete</button>
            </div>
          </article>
        `).join('');
      }

      function openAddressForm(index) {
        const addresses = normalizeAddresses();
        const address = typeof index === 'number' ? addresses[index] : null;

        addressForm.elements.editIndex.value = address?.id ? String(address.id) : '';
        addressForm.elements.editLocalId.value = '';
        addressForm.elements.type.value = address?.type || 'Home';
        addressForm.elements.name.value = address?.name || '';
        addressForm.elements.phone.value = address?.phone || '';
        addressForm.elements.address.value = address?.address || '';
        addressForm.elements.landmark.value = address?.landmark || '';
        addressForm.elements.city.value = address?.city || '';
        addressForm.elements.pincode.value = address?.pincode || '';
        addressFormTitle.textContent = address?.id ? 'Edit Address' : 'Add Address';
        setSectionNote(addressNote, '');
        addressFormCard.style.display = 'block';
      }

      function closeAddressForm() {
        addressForm.reset();
        addressForm.elements.editIndex.value = '';
        addressForm.elements.editLocalId.value = '';
        addressForm.elements.type.value = 'Home';
        addressFormCard.style.display = 'none';
        setSectionNote(addressNote, '');
      }

      function readAccount() {
        if (serverAccountCache && typeof serverAccountCache === 'object') {
          return {
            id: serverAccountCache.id || '',
            name: serverAccountCache.name || '',
            phone: serverAccountCache.phone || '',
            email: serverAccountCache.email || '',
            createdAt: serverAccountCache.created_at || serverAccountCache.createdAt || ''
          };
        }

        return {
          id: '',
          name: '',
          phone: '',
          email: '',
          createdAt: ''
        };
      }

      function fillAccountForm() {
        const account = readAccount();
        accountForm.elements.name.value = account.name;
        accountForm.elements.phone.value = account.phone;
        accountForm.elements.email.value = account.email;
      }

      function normalizeView(view) {
        const normalizedView = String(view || '').trim().toLowerCase();
        return Object.prototype.hasOwnProperty.call(panels, normalizedView) ? normalizedView : 'orders';
      }

      function readInitialView() {
        const hashView = normalizeView(window.location.hash.replace(/^#/, ''));

        if (hashView !== 'orders' || window.location.hash.replace(/^#/, '').toLowerCase() === 'orders') {
          return hashView;
        }

        try {
          return normalizeView(sessionStorage.getItem(ACCOUNT_VIEW_KEY) || 'orders');
        } catch (error) {
          return 'orders';
        }
      }

      function persistActiveView(view) {
        const safeView = normalizeView(view);

        try {
          sessionStorage.setItem(ACCOUNT_VIEW_KEY, safeView);
        } catch (error) {
          // Ignore storage failures and continue with hash-only persistence.
        }

        const nextHash = '#' + safeView;

        if (window.location.hash !== nextHash) {
          window.history.replaceState(null, '', nextHash);
        }
      }

      function setActiveView(view, options = {}) {
        const safeView = normalizeView(view);

        Object.keys(panels).forEach((key) => {
          panels[key].classList.toggle('active', key === safeView);
        });

        sidebarLinks.forEach((link) => {
          link.classList.toggle('active', link.dataset.view === safeView);
        });

        if (options.persist !== false) {
          persistActiveView(safeView);
        }
      }

      sidebarLinks.forEach((link) => {
        link.addEventListener('click', () => {
          setActiveView(link.dataset.view);
        });
      });

      addAddressButton.addEventListener('click', () => {
        openAddressForm();
      });

      cancelAddressButton.addEventListener('click', () => {
        closeAddressForm();
      });

      addressForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (isAddressSaving) {
          return;
        }

        const payload = {
          id: Number(addressForm.elements.editIndex.value || 0) || 0,
          localId: addressForm.elements.editLocalId.value || addressForm.elements.editIndex.value || ('address-' + Date.now()),
          type: addressForm.elements.type.value.trim() || 'Home',
          name: addressForm.elements.name.value.trim(),
          phone: addressForm.elements.phone.value.trim(),
          address: addressForm.elements.address.value.trim(),
          landmark: addressForm.elements.landmark.value.trim(),
          city: addressForm.elements.city.value.trim(),
          pincode: addressForm.elements.pincode.value.trim()
        };

        try {
          isAddressSaving = true;
          updateAddressSubmitState();
          let savedAddress = payload;

          if (window.CiboAccountApi) {
            const response = await window.CiboAccountApi.saveAddress(payload);
            savedAddress = response?.address && typeof response.address === 'object' ? response.address : payload;
            await loadAddresses();
          }
          renderAddresses();
          closeAddressForm();
          setSectionNote(addressNote, 'Address saved successfully.', 'success');
        } catch (error) {
          if (error?.ciboAuthError) {
            handleUnauthorized();
            return;
          }

          setSectionNote(addressNote, error.message || 'Unable to save the address right now. Please try again.', 'error');
        } finally {
          isAddressSaving = false;
          updateAddressSubmitState();
        }
      });

      accountForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (isAccountSaving) {
          return;
        }

        const existingAccount = readAccount();
        const payload = {
          id: existingAccount.id || '',
          name: accountForm.elements.name.value.trim(),
          phone: accountForm.elements.phone.value.trim(),
          email: accountForm.elements.email.value.trim(),
          createdAt: existingAccount.createdAt || new Date().toISOString()
        };

        try {
          isAccountSaving = true;
          updateAccountSubmitState();
          if (window.CiboAccountApi) {
            const response = await window.CiboAccountApi.updateProfile(payload);
            serverAccountCache = response?.user && typeof response.user === 'object' ? response.user : payload;
          }
          setSectionNote(accountNote, 'Account settings updated successfully.', 'success');
        } catch (error) {
          if (error?.ciboAuthError) {
            handleUnauthorized();
            return;
          }

          setSectionNote(accountNote, error.message || 'Unable to update the account right now. Please try again.', 'error');
        } finally {
          isAccountSaving = false;
          updateAccountSubmitState();
        }
      });

      document.addEventListener('click', (event) => {
        const actionTarget = event.target.closest('[data-action]');

        if (!actionTarget) {
          return;
        }

        const action = actionTarget.dataset.action;
        const index = Number(actionTarget.dataset.index);

        if (action === 'toggle-order') {
          const detailBox = document.getElementById('order-detail-' + index);
          const isOpen = detailBox.classList.contains('active');

          document.querySelectorAll('.detail-box.active').forEach((box) => {
            box.classList.remove('active');
          });

          document.querySelectorAll('[data-action="toggle-order"]').forEach((button) => {
            button.textContent = 'View Details';
          });

          detailBox.classList.toggle('active', !isOpen);
          actionTarget.textContent = isOpen ? 'View Details' : 'Hide Details';
        }

        if (action === 'reorder') {
          const orders = readOrders();
          const order = orders[index];

          if (!order) {
            return;
          }

          cartManager?.setCart(buildCartFromOrder(order), {
            source: 'myaccount-reorder'
          });
          actionTarget.textContent = 'Added to Cart';

          window.setTimeout(() => {
            actionTarget.textContent = 'Reorder';
          }, 1400);
        }

        if (action === 'cancel-order') {
          const orders = readOrders();
          const order = orders[index];

          if (!order || !order.canCancel || !window.CiboOrdersApi || isOrderCancelling) {
            return;
          }

          const shouldCancel = window.confirm('Are you sure you want to cancel this order?');

          if (!shouldCancel) {
            return;
          }

          isOrderCancelling = true;
          setSectionNote(ordersNote, '');
          actionTarget.disabled = true;
          actionTarget.textContent = 'Cancelling...';

          window.CiboOrdersApi.cancel(order.orderNumber || order.id)
            .then(() => refreshOrdersAndRender({ force: true }))
            .then(() => {
              setSectionNote(ordersNote, 'Order cancelled successfully.', 'success');
            })
            .catch((error) => {
              if (error?.ciboAuthError) {
                handleUnauthorized();
                return;
              }

              setSectionNote(ordersNote, getOrderCancellationMessage(error), 'error');
            })
            .finally(() => {
              isOrderCancelling = false;
              actionTarget.disabled = false;
              if (actionTarget.textContent === 'Cancelling...') {
                actionTarget.textContent = 'Cancel Order';
              }
            });
        }

        if (action === 'edit-address') {
          openAddressForm(index);
        }

        if (action === 'delete-address') {
          const addresses = normalizeAddresses();
          const address = addresses[index];

          if (!address) {
            return;
          }

          Promise.resolve()
            .then(() => window.CiboAccountApi ? window.CiboAccountApi.deleteAddress(address.id) : null)
            .then(loadAddresses)
            .then(() => {
              renderAddresses();
              closeAddressForm();
              setSectionNote(addressNote, 'Address removed successfully.', 'success');
            })
            .catch((error) => {
              if (error?.ciboAuthError) {
                handleUnauthorized();
                return;
              }

              setSectionNote(addressNote, error.message || 'Unable to delete the address right now. Please try again.', 'error');
            });
        }

        if (action === 'remove-favorite-item' && favoritesApi) {
          favoritesApi.removeItem(actionTarget.dataset.id || '');
          renderFavorites();
        }
      });

      cleanupLegacySharedData();
      setActiveView(readInitialView());
      renderLoadingState(ordersList, 'Loading your recent orders...');
      renderLoadingState(favoriteItemsList, 'Loading your saved dishes...');
      renderLoadingState(addressesList, 'Loading your saved addresses...');
      updateAddressSubmitState();
      updateAccountSubmitState();

      Promise.resolve()
        .then(loadAccount)
        .then(loadAddresses)
        .then(loadOrders)
        .then(() => {
          renderOrders();
          renderFavorites();
          renderAddresses();
          fillAccountForm();
        })
        .catch((error) => {
          if (error?.ciboAuthError) {
            return;
          }

          renderOrders();
          renderFavorites();
          renderAddresses();
          fillAccountForm();
        });

      window.addEventListener('cibo-favorites-updated', () => {
        renderFavorites();
      });

      window.addEventListener('hashchange', () => {
        setActiveView(window.location.hash.replace(/^#/, ''), { persist: false });
      });

      window.addEventListener('pageshow', () => {
        if (normalizeView(window.location.hash.replace(/^#/, '')) === 'orders') {
          refreshOrdersAndRender({ force: true }).catch((error) => {
            if (error?.ciboAuthError) {
              handleUnauthorized();
            }
          });
        }
      });

      window.addEventListener('focus', () => {
        if (normalizeView(window.location.hash.replace(/^#/, '')) === 'orders') {
          refreshOrdersAndRender().catch(() => null);
        }
      });

      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && normalizeView(window.location.hash.replace(/^#/, '')) === 'orders') {
          refreshOrdersAndRender().catch(() => null);
        }
      });
    })();
  </script>
  <script src="auth-display.js"></script>
  <script src="cart-badge.js"></script>
</body>
</html>
