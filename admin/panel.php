<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-data.php';

cibo_admin_require_login();

$section = trim((string) ($_GET['section'] ?? 'dashboard'));
$allowedSections = ['dashboard', 'restaurants', 'menu-items', 'orders', 'users', 'admin-profile'];

if (!in_array($section, $allowedSections, true)) {
    $section = 'dashboard';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectSection = trim((string) ($_POST['redirect_section'] ?? $section));

    try {
        $action = trim((string) ($_POST['admin_action'] ?? ''));

        switch ($action) {
            case 'update_admin_profile':
                cibo_admin_update_profile($_POST);
                cibo_admin_flash('success', 'Profile updated successfully.');
                $redirectSection = 'admin-profile';
                break;

            case 'change_admin_password':
                cibo_admin_change_password($_POST);
                cibo_admin_flash('success', 'Password changed successfully.');
                $redirectSection = 'admin-profile';
                break;

            default:
                cibo_admin_flash('error', 'Unknown admin action.');
                break;
        }
    } catch (Throwable $exception) {
        cibo_admin_flash('error', $exception->getMessage());
    }

    cibo_redirect(CIBO_ADMIN_BASE . '/panel.php?section=' . urlencode($redirectSection));
}

$sessionAdmin = cibo_admin_user();
$flash = cibo_admin_pull_flash();
$adminProfile = cibo_admin_profile();

function cibo_admin_panel_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cibo_admin_panel_date(?string $value): string
{
    if (!$value) {
        return 'Not available';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return 'Not available';
    }

    return date('d M Y', $timestamp);
}

function cibo_admin_panel_date_input(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d', $timestamp);
}

function cibo_admin_collect_image_paths(string $relativeDirectory, bool $recursive = true): array
{
    $baseDirectory = realpath(__DIR__ . '/../' . trim($relativeDirectory, '/\\'));

    if ($baseDirectory === false || !is_dir($baseDirectory)) {
        return [];
    }

    $paths = [];

    if ($recursive) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDirectory, FilesystemIterator::SKIP_DOTS)
        );
    } else {
        $iterator = new IteratorIterator(new DirectoryIterator($baseDirectory));
    }

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            continue;
        }

        $fullPath = $file->getPathname();
        $relativePath = substr($fullPath, strlen(realpath(__DIR__ . '/..')) + 1);
        $paths[] = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
    }

    sort($paths, SORT_NATURAL | SORT_FLAG_CASE);
    return array_values(array_unique($paths));
}

$restaurantCardSuggestions = array_values(array_unique(array_merge(
    cibo_admin_collect_image_paths('images/restaurants', false),
    cibo_admin_collect_image_paths('images/food-items', true)
)));
$restaurantHeroSuggestions = cibo_admin_collect_image_paths('images/restaurant-heroes', false);
$menuImageSuggestions = cibo_admin_collect_image_paths('images/food-items', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cibo Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .profile-shell {
      display: grid;
      gap: 22px;
    }

    .profile-grid {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 16px;
      align-items: stretch;
    }

    .profile-card {
      padding: 24px;
      height: 100%;
      max-width: 1080px;
    }

    .profile-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 0;
    }

    .profile-identity {
      display: flex;
      align-items: center;
      gap: 18px;
      min-width: 0;
    }

    .profile-main {
      display: grid;
      gap: 16px;
    }

    .profile-hero {
      position: relative;
      overflow: hidden;
      padding: 18px 20px;
      border-radius: 22px;
      border: 1px solid color-mix(in srgb, var(--accent) 16%, var(--line) 84%);
      background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--accent-soft) 18%, transparent 82%), transparent 36%),
        linear-gradient(135deg, color-mix(in srgb, var(--card) 94%, var(--bg) 6%), color-mix(in srgb, var(--card) 88%, var(--accent) 12%));
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
    }

    .profile-hero::after {
      content: "";
      position: absolute;
      inset: auto -40px -60px auto;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: color-mix(in srgb, var(--accent) 8%, transparent 92%);
      pointer-events: none;
    }

    .profile-avatar {
      width: 70px;
      height: 70px;
      border-radius: 20px;
      display: grid;
      place-items: center;
      background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) 44%, var(--card) 56%), color-mix(in srgb, var(--accent) 30%, var(--card) 70%));
      color: var(--accent);
      box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--accent) 20%, var(--line) 80%), 0 12px 24px rgba(95, 124, 58, 0.12);
      font-size: 28px;
      font-weight: 800;
      flex-shrink: 0;
    }

    .profile-title {
      display: grid;
      gap: 6px;
      min-width: 0;
    }

    .profile-kicker {
      display: inline-flex;
      align-items: center;
      width: fit-content;
      padding: 6px 11px;
      border-radius: 999px;
      background: rgba(255, 253, 249, 0.78);
      border: 1px solid color-mix(in srgb, var(--accent) 20%, var(--line) 80%);
      color: var(--accent);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .profile-title h3 {
      margin: 0;
      font-size: clamp(20px, 2.6vw, 24px);
      letter-spacing: -0.04em;
      line-height: 1.12;
    }

    .profile-title p {
      margin: 0;
      color: var(--muted);
      line-height: 1.5;
      font-size: 14px;
      word-break: break-word;
    }

    .profile-facts {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .profile-fact {
      padding: 14px 16px;
      border-radius: 18px;
      border: 1px solid color-mix(in srgb, var(--accent) 12%, var(--line) 88%);
      background: linear-gradient(180deg, color-mix(in srgb, var(--card) 88%, var(--bg) 12%), var(--card));
      min-height: 84px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .profile-fact:hover {
      transform: translateY(-2px);
      border-color: color-mix(in srgb, var(--accent) 24%, var(--line) 76%);
      box-shadow: 0 12px 24px rgba(31, 31, 27, 0.08);
    }

    .profile-fact span {
      display: block;
      color: var(--muted);
      font-size: 11px;
      font-weight: 700;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .profile-fact strong {
      display: block;
      font-size: clamp(14px, 1.6vw, 17px);
      line-height: 1.4;
      word-break: break-word;
    }

    .profile-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      padding-top: 2px;
    }

    .profile-form-card {
      display: none;
      gap: 16px;
    }

    .profile-form-card.is-open {
      display: grid;
    }

    .profile-form-card .toolbar {
      align-items: flex-start;
    }

    .profile-form-card .toolbar h3 {
      font-size: 22px;
    }

    .profile-form-card form {
      gap: 14px;
    }

    .profile-form-card .button-row {
      margin-top: 2px;
    }

    .profile-form-card .toolbar p {
      font-size: 14px;
      line-height: 1.6;
    }

    .profile-form-card .form-label {
      font-size: 13px;
    }

    .profile-form-card .field,
    .profile-form-card .field-select {
      padding: 12px 14px;
      border-radius: 16px;
    }

    .profile-form-card .button,
    .profile-form-card .button-secondary {
      min-height: 40px;
      padding: 0 18px;
      font-size: 14px;
    }

    .profile-form-note {
      margin: -2px 0 0;
      color: var(--muted);
      font-size: 13px;
      line-height: 1.6;
    }

    .profile-actions .button.is-active,
    .profile-actions .button-secondary.is-active {
      background: var(--accent);
      border-color: var(--accent);
      color: #fff;
    }

    @media (max-width: 980px) {
      .profile-grid {
        grid-template-columns: 1fr;
      }

      .profile-head,
      .profile-identity {
        flex-direction: column;
        align-items: flex-start;
      }

      .profile-facts {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .profile-card {
        padding: 18px;
      }
    }
  </style>
</head>
<body data-auth-mode="php">
  <div class="admin-shell">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-logo-shell">
          <img src="../images/logo.png" alt="Cibo" class="brand-logo">
        </div>
        <div class="brand-copy">
          <h1>Cibo</h1>
          <p>Premium admin panel</p>
          <a href="/food-app/index.php" class="brand-switch">View site</a>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a href="?section=dashboard" class="sidebar-link" data-section-link="dashboard">Dashboard</a>
        <a href="?section=restaurants" class="sidebar-link" data-section-link="restaurants">Restaurants</a>
        <a href="?section=menu-items" class="sidebar-link" data-section-link="menu-items">Menu Items</a>
        <a href="?section=orders" class="sidebar-link" data-section-link="orders">Orders</a>
        <a href="?section=users" class="sidebar-link" data-section-link="users">Users</a>
        <a href="?section=admin-profile" class="sidebar-link" data-section-link="admin-profile">Admin Profile</a>
      </nav>

      <div class="sidebar-meta">
        <p>Signed in as <strong id="admin-session-name"><?= cibo_admin_panel_h((string) ($sessionAdmin['name'] ?? 'Cibo Admin')) ?></strong>.</p>
        <a href="logout.php" class="button-secondary" id="admin-logout-button">Logout</a>
      </div>
    </aside>

    <main class="content">
      <div class="flash <?= $flash ? cibo_admin_panel_h((string) ($flash['type'] ?? 'success')) : 'success' ?>" id="admin-feedback"<?= $flash ? '' : ' style="display: none;"' ?>><span><?= $flash ? cibo_admin_panel_h((string) ($flash['message'] ?? '')) : '' ?></span></div>

      <section class="section" data-section-panel="dashboard">
        <div class="content-head">
          <div>
            <h2>Dashboard</h2>
            <p>Track the health of Cibo at a glance with quick numbers for orders, revenue, users, and restaurant coverage.</p>
          </div>
        </div>

        <div class="stat-grid">
          <button class="admin-card stat-card" type="button" data-stat-target="orders">
            <span>Total Orders</span>
            <strong id="stat-orders">0</strong>
            <small>All recorded customer orders</small>
          </button>
          <button class="admin-card stat-card" type="button" data-stat-target="revenue">
            <span>Total Revenue</span>
            <strong id="stat-revenue">₹0</strong>
            <small>Order totals processed so far</small>
          </button>
          <button class="admin-card stat-card" type="button" data-stat-target="users">
            <span>Total Users</span>
            <strong id="stat-users">0</strong>
            <small>Registered customers in Cibo</small>
          </button>
          <button class="admin-card stat-card" type="button" data-stat-target="restaurants">
            <span>Total Restaurants</span>
            <strong id="stat-restaurants">0</strong>
            <small>Active restaurant records</small>
          </button>
        </div>

        <div class="panel-card dashboard-detail-card">
          <div class="toolbar">
            <div>
              <h3 id="dashboard-detail-title">Orders Snapshot</h3>
              <p id="dashboard-detail-copy">Click a dashboard card to explore the matching details.</p>
            </div>
            <button class="button-secondary" type="button" id="dashboard-detail-action">Open Orders</button>
          </div>
          <div class="dashboard-detail-body" id="dashboard-detail-body"></div>
        </div>
      </section>

      <section class="section" data-section-panel="restaurants">
        <div class="content-head">
          <div>
            <h2>Restaurants</h2>
            <p>Add, edit, or remove restaurant listings while keeping the same visual language as the customer-facing cards.</p>
          </div>
        </div>

        <div class="stack">
          <div class="panel-card">
            <div class="toolbar">
              <div>
                <h3 id="restaurant-form-title">Add Restaurant</h3>
                <p>Create a new listing with the same essentials used across the Cibo homepage experience.</p>
              </div>
            </div>

            <form class="stack" id="restaurant-form">
              <input type="hidden" name="id" value="">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Name</label>
                  <input class="field" type="text" name="name" placeholder="Domino's" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Card Image</label>
                  <input class="field" type="text" name="image" placeholder="images/restaurants/dominos.jpg" data-image-autocomplete="restaurant-card">
                  <select class="field-select" data-image-select="restaurant-card">
                    <option value="">Select a saved image</option>
                  </select>
                  <div class="image-path-hint" data-image-autocomplete-hint="restaurant-card">Type part of a filename and press Tab to fill the full path.</div>
                </div>
                <div class="form-group">
                  <label class="form-label">Hero Banner</label>
                  <input class="field" type="text" name="heroImage" placeholder="images/restaurant-heroes/dominos-hero.jpg" data-image-autocomplete="restaurant-hero">
                  <select class="field-select" data-image-select="restaurant-hero">
                    <option value="">Select a saved image</option>
                  </select>
                  <div class="image-path-hint" data-image-autocomplete-hint="restaurant-hero">Type part of a filename and press Tab to fill the full path.</div>
                </div>
                <div class="form-group">
                  <label class="form-label">Location</label>
                  <input class="field" type="text" name="location" placeholder="Jayanagar" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Cuisines</label>
                  <input class="field" type="text" name="cuisines" placeholder="Pizza, Italian" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Homepage Category</label>
                  <input class="field" type="text" name="category" placeholder="pizza" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Rating</label>
                  <input class="field" type="text" name="rating" placeholder="4.2">
                </div>
                <div class="form-group">
                  <label class="form-label">Delivery Time</label>
                  <input class="field" type="text" name="deliveryTime" placeholder="25-30 mins">
                </div>
                <div class="form-group full">
                  <label class="form-label">Offer Text</label>
                  <input class="field" type="text" name="offerText" placeholder="Free delivery above ₹199">
                </div>
              </div>
              <div class="button-row">
                <button class="button" type="submit">Save Restaurant</button>
                <button class="button-secondary" type="button" id="restaurant-form-cancel" style="display: none;">Cancel</button>
              </div>
            </form>
          </div>

          <div class="grid" id="admin-restaurants-grid"></div>
          <div class="panel-card empty-state" id="admin-restaurants-empty" style="display: none;">No restaurants available.</div>
        </div>
      </section>

      <section class="section" data-section-panel="menu-items">
        <div class="content-head">
          <div>
            <h2>Menu Items</h2>
            <p>Manage menu content with the same premium food card presentation used across restaurant pages.</p>
          </div>
        </div>

        <div class="stack">
          <div class="panel-card">
            <div class="toolbar">
              <div>
                <h3 id="menu-form-title">Add Menu Item</h3>
                <p>Create new food items and assign them to a restaurant in a clean, reusable format.</p>
              </div>
            </div>

            <form class="stack" id="menu-item-form">
              <input type="hidden" name="id" value="">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Restaurant</label>
                  <select class="field-select" name="restaurantId" id="menu-restaurant-select" required>
                    <option value="">Select restaurant</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Item Name</label>
                  <input class="field" type="text" name="name" placeholder="Tandoori Paneer Pizza" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Price</label>
                  <input class="field" type="number" min="1" step="0.01" name="price" placeholder="329" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Food Type</label>
                  <select class="field-select" name="foodType" required>
                    <option value="veg">Veg</option>
                    <option value="nonveg">Non-Veg</option>
                  </select>
                </div>
                <div class="form-group full">
                  <label class="form-label">Menu Chips / Tags</label>
                  <input class="field" type="text" name="filterTags" placeholder="pizza, garlic bread, sides">
                </div>
                <div class="form-group full">
                  <label class="form-label">Image</label>
                  <input class="field" type="text" name="image" placeholder="images/food-items/dominos/margherita.jpg" data-image-autocomplete="menu-item">
                  <select class="field-select" data-image-select="menu-item">
                    <option value="">Select a saved image</option>
                  </select>
                  <div class="image-path-hint" data-image-autocomplete-hint="menu-item">Type part of a filename and press Tab to fill the full path.</div>
                </div>
                <div class="form-group full">
                  <label class="form-label">Description</label>
                  <textarea class="field-textarea" name="description" placeholder="Paneer chunks with smoky tandoori flavour, onions and cheesy topping."></textarea>
                </div>
              </div>
              <div class="button-row">
                <button class="button" type="submit">Save Menu Item</button>
                <button class="button-secondary" type="button" id="menu-form-cancel" style="display: none;">Cancel</button>
              </div>
            </form>
          </div>

          <div class="grid" id="admin-menu-grid"></div>
          <div class="panel-card empty-state" id="admin-menu-empty" style="display: none;">No items available.</div>
        </div>
      </section>

      <section class="section" data-section-panel="orders">
        <div class="content-head">
          <div>
            <h2>Orders</h2>
            <p>Monitor incoming orders and update their current status from one calm, readable table.</p>
          </div>
        </div>

        <div class="panel-card">
          <div class="table-wrap" id="admin-orders-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>User</th>
                  <th>Items</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="admin-orders-body"></tbody>
            </table>
          </div>
          <div class="empty-state" id="admin-orders-empty" style="display: none;">No orders available.</div>
        </div>
      </section>

      <section class="section" data-section-panel="users">
        <div class="content-head">
          <div>
            <h2>Users</h2>
            <p>See a simple customer list with the essentials you need for account oversight.</p>
          </div>
        </div>

        <div class="panel-card">
          <div class="table-wrap" id="admin-users-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Joined</th>
                </tr>
              </thead>
              <tbody id="admin-users-body"></tbody>
            </table>
          </div>
          <div class="empty-state" id="admin-users-empty" style="display: none;">No users available.</div>
        </div>
      </section>

      <section class="section" data-section-panel="admin-profile">
        <div class="content-head">
          <div>
            <h2>Admin Profile</h2>
            <p>Manage your personal admin details and password without leaving the panel.</p>
          </div>
        </div>

        <div class="profile-shell">
          <div class="profile-grid">
            <article class="admin-card profile-card">
              <div class="profile-main">
                <div class="profile-hero">
                  <div class="profile-head">
                    <div class="profile-identity">
                      <div class="profile-avatar"><?= cibo_admin_panel_h(strtoupper(substr((string) ($adminProfile['name'] ?? 'A'), 0, 1))) ?></div>
                      <div class="profile-title">
                        <span class="profile-kicker">Primary Admin Account</span>
                        <h3><?= cibo_admin_panel_h((string) ($adminProfile['name'] ?? 'Admin User')) ?></h3>
                        <p><?= cibo_admin_panel_h((string) ($adminProfile['email'] ?? 'admin@cibo.local')) ?></p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="profile-facts">
                  <div class="profile-fact">
                    <span>Admin Name</span>
                    <strong><?= cibo_admin_panel_h((string) ($adminProfile['name'] ?? 'Admin User')) ?></strong>
                  </div>
                  <div class="profile-fact">
                    <span>Email</span>
                    <strong><?= cibo_admin_panel_h((string) ($adminProfile['email'] ?? 'admin@cibo.local')) ?></strong>
                  </div>
                  <div class="profile-fact">
                    <span>Role</span>
                    <strong><?= cibo_admin_panel_h((string) ($adminProfile['role'] ?? 'Admin')) ?></strong>
                  </div>
                  <div class="profile-fact">
                    <span>Joined Date</span>
                    <strong><?= cibo_admin_panel_h(cibo_admin_panel_date($adminProfile['created_at'] ?? null)) ?></strong>
                  </div>
                </div>

                <div class="profile-actions">
                  <button class="button" type="button" data-profile-toggle="admin-profile-edit">Edit Profile</button>
                  <button class="button-secondary" type="button" data-profile-toggle="admin-change-password">Change Password</button>
                </div>
              </div>
            </article>
          </div>

          <div class="profile-grid">
            <article class="panel-card profile-card profile-form-card" id="admin-profile-edit">
              <div class="toolbar">
                <div>
                  <h3>Edit Profile</h3>
                  <p>Update all main account details from one place.</p>
                </div>
              </div>

              <form method="post" class="stack">
                <input type="hidden" name="admin_action" value="update_admin_profile">
                <input type="hidden" name="redirect_section" value="admin-profile">
                <div class="form-grid">
                  <div class="form-group">
                    <label class="form-label" for="admin-profile-name">Admin Name</label>
                    <input class="field" id="admin-profile-name" name="name" type="text" value="<?= cibo_admin_panel_h((string) ($adminProfile['name'] ?? '')) ?>" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="admin-profile-email">Email</label>
                    <input class="field" id="admin-profile-email" name="email" type="email" value="<?= cibo_admin_panel_h((string) ($adminProfile['email'] ?? '')) ?>" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="admin-profile-role">Role</label>
                    <input class="field" id="admin-profile-role" name="role" type="text" value="<?= cibo_admin_panel_h((string) ($adminProfile['role'] ?? 'Admin')) ?>">
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="admin-profile-joined">Joined Date</label>
                    <input class="field" id="admin-profile-joined" name="created_at" type="date" value="<?= cibo_admin_panel_h(cibo_admin_panel_date_input($adminProfile['created_at'] ?? null)) ?>">
                  </div>
                </div>
                <p class="profile-form-note">You can now edit name, email, role, and joined date here. If joined date is empty, the current saved value will be kept.</p>
                <div class="button-row">
                  <button class="button" type="submit">Save Profile</button>
                </div>
              </form>
            </article>

            <article class="panel-card profile-card profile-form-card" id="admin-change-password">
              <div class="toolbar">
                <div>
                  <h3>Change Password</h3>
                  <p>Confirm your current password and set a new one using secure hashing.</p>
                </div>
              </div>

              <form method="post" class="stack">
                <input type="hidden" name="admin_action" value="change_admin_password">
                <input type="hidden" name="redirect_section" value="admin-profile">
                <div class="form-group">
                  <label class="form-label" for="admin-current-password">Current Password</label>
                  <input class="field" id="admin-current-password" name="current_password" type="password" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-new-password">New Password</label>
                  <input class="field" id="admin-new-password" name="new_password" type="password" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-confirm-password">Confirm Password</label>
                  <input class="field" id="admin-confirm-password" name="confirm_password" type="password" required>
                </div>
                <div class="button-row">
                  <button class="button" type="submit">Update Password</button>
                </div>
              </form>
            </article>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script id="admin-image-autocomplete-data" type="application/json"><?= json_encode([
      'restaurant-card' => $restaurantCardSuggestions,
      'restaurant-hero' => $restaurantHeroSuggestions,
      'menu-item' => $menuImageSuggestions,
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <script src="assets/admin.js"></script>
  <script>
    (() => {
      const toggleButtons = Array.from(document.querySelectorAll('[data-profile-toggle]'));
      const cards = Array.from(document.querySelectorAll('.profile-form-card'));

      if (!toggleButtons.length || !cards.length) {
        return;
      }

      function openCard(cardId) {
        cards.forEach((card) => {
          card.classList.toggle('is-open', card.id === cardId);
        });

        toggleButtons.forEach((button) => {
          button.classList.toggle('is-active', button.dataset.profileToggle === cardId);
        });

        const activeCard = document.getElementById(cardId);

        if (activeCard) {
          activeCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }

      toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const targetId = button.dataset.profileToggle || '';
          const targetCard = document.getElementById(targetId);

          if (!targetCard) {
            return;
          }

          if (targetCard.classList.contains('is-open')) {
            targetCard.classList.remove('is-open');
            button.classList.remove('is-active');
            return;
          }

          openCard(targetId);
        });
      });
    })();
  </script>
</body>
</html>
