<?php header('Location: panel.php'); exit; ?>

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-data.php';

cibo_admin_require_login();

$admin = cibo_admin_user();
$section = trim((string) ($_GET['section'] ?? 'dashboard'));
$allowedSections = ['dashboard', 'restaurants', 'menu-items', 'orders', 'users'];

if (!in_array($section, $allowedSections, true)) {
    $section = 'dashboard';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectSection = trim((string) ($_POST['redirect_section'] ?? $section));

    try {
        $action = trim((string) ($_POST['admin_action'] ?? ''));

        switch ($action) {
            case 'save_restaurant':
                cibo_admin_save_restaurant($_POST);
                cibo_admin_flash('success', 'Restaurant details saved successfully.');
                $redirectSection = 'restaurants';
                break;

            case 'delete_restaurant':
                cibo_admin_delete_restaurant((int) ($_POST['id'] ?? 0));
                cibo_admin_flash('success', 'Restaurant removed.');
                $redirectSection = 'restaurants';
                break;

            case 'save_menu_item':
                cibo_admin_save_menu_item($_POST);
                cibo_admin_flash('success', 'Menu item saved successfully.');
                $redirectSection = 'menu-items';
                break;

            case 'delete_menu_item':
                cibo_admin_delete_menu_item((int) ($_POST['id'] ?? 0));
                cibo_admin_flash('success', 'Menu item removed.');
                $redirectSection = 'menu-items';
                break;

            case 'update_order_status':
                cibo_admin_update_order_status((int) ($_POST['id'] ?? 0), trim((string) ($_POST['order_status'] ?? '')));
                cibo_admin_flash('success', 'Order status updated.');
                $redirectSection = 'orders';
                break;

            default:
                cibo_admin_flash('error', 'Unknown admin action.');
                break;
        }
    } catch (Throwable $exception) {
        cibo_admin_flash('error', $exception->getMessage());
    }

    cibo_redirect(CIBO_ADMIN_BASE . '/index.php?section=' . urlencode($redirectSection));
}

$flash = cibo_admin_pull_flash();
$dbReady = cibo_db_ready();
$stats = cibo_admin_dashboard_stats();
$restaurants = cibo_admin_fetch_restaurants();
$restaurantOptions = cibo_admin_fetch_restaurant_options();
$menuItems = cibo_admin_fetch_menu_items();
$orders = cibo_admin_fetch_orders();
$users = cibo_admin_fetch_users();
$orderStatuses = cibo_admin_order_status_options();

function cibo_admin_money(float $amount): string
{
    return '₹' . number_format($amount, 0);
}
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
</head>
<body>
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
      </nav>

      <div class="sidebar-meta">
        <p>Signed in as <strong><?= cibo_admin_h((string) ($admin['name'] ?? 'Admin')) ?></strong>.</p>
        <a href="logout.php" class="button-secondary">Logout</a>
      </div>
    </aside>

    <main class="content">
      <?php if ($flash): ?>
        <div class="flash <?= cibo_admin_h((string) $flash['type']) ?>">
          <span><?= cibo_admin_h((string) $flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!$dbReady): ?>
        <div class="flash error">
          <span>Database not connected yet. Import <strong>database/schema.sql</strong> into MySQL to enable live admin data and CRUD actions.</span>
        </div>
      <?php endif; ?>

      <section class="section" data-section-panel="dashboard">
        <div class="content-head">
          <div>
            <h2>Dashboard</h2>
            <p>Track the health of Cibo at a glance with quick numbers for orders, revenue, users, and restaurant coverage.</p>
          </div>
        </div>

        <div class="stat-grid">
          <article class="admin-card stat-card">
            <span>Total Orders</span>
            <strong><?= cibo_admin_h((string) $stats['orders']) ?></strong>
            <small>All recorded customer orders</small>
          </article>
          <article class="admin-card stat-card">
            <span>Total Revenue</span>
            <strong><?= cibo_admin_h(cibo_admin_money((float) $stats['revenue'])) ?></strong>
            <small>Order totals processed so far</small>
          </article>
          <article class="admin-card stat-card">
            <span>Total Users</span>
            <strong><?= cibo_admin_h((string) $stats['users']) ?></strong>
            <small>Registered customers in Cibo</small>
          </article>
          <article class="admin-card stat-card">
            <span>Total Restaurants</span>
            <strong><?= cibo_admin_h((string) $stats['restaurants']) ?></strong>
            <small>Active restaurant records</small>
          </article>
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
                <h3>Add Restaurant</h3>
                <p>Create a new listing with the same essentials used across the Cibo homepage experience.</p>
              </div>
            </div>

            <form method="post" class="stack">
              <input type="hidden" name="admin_action" value="save_restaurant">
              <input type="hidden" name="redirect_section" value="restaurants">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Name</label>
                  <input class="field" type="text" name="name" placeholder="Domino's" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Image</label>
                  <input class="field" type="text" name="image_path" placeholder="images/restaurants/dominos.jpg">
                </div>
                <div class="form-group">
                  <label class="form-label">Location</label>
                  <input class="field" type="text" name="location" placeholder="Jayanagar" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Category</label>
                  <input class="field" type="text" name="cuisine" placeholder="Pizza, Italian" required>
                </div>
              </div>
              <div class="button-row">
                <button class="button" type="submit">Add Restaurant</button>
              </div>
            </form>
          </div>

          <?php if ($restaurants): ?>
            <div class="grid">
              <?php foreach ($restaurants as $restaurant): ?>
                <?php $deleteId = 'delete-restaurant-' . (int) $restaurant['id']; ?>
                <article class="admin-card restaurant-card">
                  <?php if (!empty($restaurant['image_path'])): ?>
                    <img class="card-image" src="<?= cibo_admin_h(cibo_admin_asset_url((string) $restaurant['image_path'])) ?>" alt="<?= cibo_admin_h((string) $restaurant['name']) ?>">
                  <?php endif; ?>
                  <div class="card-body">
                    <div class="eyebrow"><?= cibo_admin_h((string) $restaurant['location']) ?></div>
                    <h4><?= cibo_admin_h((string) $restaurant['name']) ?></h4>
                    <p class="card-meta"><?= cibo_admin_h((string) $restaurant['cuisine']) ?></p>

                    <form method="post" class="stack">
                      <input type="hidden" name="admin_action" value="save_restaurant">
                      <input type="hidden" name="redirect_section" value="restaurants">
                      <input type="hidden" name="id" value="<?= (int) $restaurant['id'] ?>">
                      <div class="form-grid">
                        <div class="form-group full">
                          <label class="form-label">Name</label>
                          <input class="field" type="text" name="name" value="<?= cibo_admin_h((string) $restaurant['name']) ?>" required>
                        </div>
                        <div class="form-group full">
                          <label class="form-label">Image</label>
                          <input class="field" type="text" name="image_path" value="<?= cibo_admin_h((string) ($restaurant['image_path'] ?? '')) ?>">
                        </div>
                        <div class="form-group">
                          <label class="form-label">Location</label>
                          <input class="field" type="text" name="location" value="<?= cibo_admin_h((string) $restaurant['location']) ?>" required>
                        </div>
                        <div class="form-group">
                          <label class="form-label">Category</label>
                          <input class="field" type="text" name="cuisine" value="<?= cibo_admin_h((string) $restaurant['cuisine']) ?>" required>
                        </div>
                      </div>
                      <div class="button-row">
                        <button class="button" type="submit">Save Changes</button>
                        <button class="button-secondary" type="submit" form="<?= $deleteId ?>">Delete</button>
                      </div>
                    </form>

                    <form method="post" id="<?= $deleteId ?>">
                      <input type="hidden" name="admin_action" value="delete_restaurant">
                      <input type="hidden" name="redirect_section" value="restaurants">
                      <input type="hidden" name="id" value="<?= (int) $restaurant['id'] ?>">
                    </form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="panel-card empty-state">No restaurants yet. Add your first restaurant to start populating the admin panel.</div>
          <?php endif; ?>
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
                <h3>Add Menu Item</h3>
                <p>Create new food items and assign them to a restaurant in a clean, reusable format.</p>
              </div>
            </div>

            <form method="post" class="stack">
              <input type="hidden" name="admin_action" value="save_menu_item">
              <input type="hidden" name="redirect_section" value="menu-items">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Restaurant</label>
                  <select class="field-select" name="restaurant_id" required>
                    <option value="">Select restaurant</option>
                    <?php foreach ($restaurantOptions as $option): ?>
                      <option value="<?= (int) $option['id'] ?>"><?= cibo_admin_h((string) $option['name']) ?></option>
                    <?php endforeach; ?>
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
                  <label class="form-label">Category</label>
                  <select class="field-select" name="food_type" required>
                    <option value="veg">Veg</option>
                    <option value="nonveg">Non-Veg</option>
                  </select>
                </div>
                <div class="form-group full">
                  <label class="form-label">Image</label>
                  <input class="field" type="text" name="image_path" placeholder="images/menu/pizza.jpg">
                </div>
                <div class="form-group full">
                  <label class="form-label">Description</label>
                  <textarea class="field-textarea" name="description" placeholder="Paneer chunks with smoky tandoori flavour, onions and cheesy topping."></textarea>
                </div>
              </div>
              <div class="button-row">
                <button class="button" type="submit">Add Menu Item</button>
              </div>
            </form>
          </div>

          <?php if ($menuItems): ?>
            <div class="grid">
              <?php foreach ($menuItems as $item): ?>
                <?php
                $deleteId = 'delete-menu-item-' . (int) $item['id'];
                $foodType = (string) $item['food_type'];
                $foodLabel = $foodType === 'nonveg' ? 'Non-Veg' : 'Veg';
                ?>
                <article class="admin-card menu-card">
                  <?php if (!empty($item['image_path'])): ?>
                    <img class="card-image" src="<?= cibo_admin_h(cibo_admin_asset_url((string) $item['image_path'])) ?>" alt="<?= cibo_admin_h((string) $item['name']) ?>">
                  <?php endif; ?>
                  <div class="card-body">
                    <div class="eyebrow"><?= cibo_admin_h((string) $item['restaurant_name']) ?></div>
                    <h4><?= cibo_admin_h((string) $item['name']) ?></h4>
                    <div class="price-row">
                      <strong><?= cibo_admin_h(cibo_admin_money((float) $item['price'])) ?></strong>
                      <span class="badge <?= cibo_admin_h($foodType) ?>"><?= cibo_admin_h($foodLabel) ?></span>
                    </div>
                    <p><?= cibo_admin_h((string) ($item['description'] ?? '')) ?></p>

                    <form method="post" class="stack">
                      <input type="hidden" name="admin_action" value="save_menu_item">
                      <input type="hidden" name="redirect_section" value="menu-items">
                      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                      <div class="form-grid">
                        <div class="form-group full">
                          <label class="form-label">Restaurant</label>
                          <select class="field-select" name="restaurant_id" required>
                            <?php foreach ($restaurantOptions as $option): ?>
                              <option value="<?= (int) $option['id'] ?>" <?= (int) $option['id'] === (int) $item['restaurant_id'] ? 'selected' : '' ?>>
                                <?= cibo_admin_h((string) $option['name']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="form-group full">
                          <label class="form-label">Item Name</label>
                          <input class="field" type="text" name="name" value="<?= cibo_admin_h((string) $item['name']) ?>" required>
                        </div>
                        <div class="form-group">
                          <label class="form-label">Price</label>
                          <input class="field" type="number" min="1" step="0.01" name="price" value="<?= cibo_admin_h((string) $item['price']) ?>" required>
                        </div>
                        <div class="form-group">
                          <label class="form-label">Category</label>
                          <select class="field-select" name="food_type" required>
                            <option value="veg" <?= $foodType === 'veg' ? 'selected' : '' ?>>Veg</option>
                            <option value="nonveg" <?= $foodType === 'nonveg' ? 'selected' : '' ?>>Non-Veg</option>
                          </select>
                        </div>
                        <div class="form-group full">
                          <label class="form-label">Image</label>
                          <input class="field" type="text" name="image_path" value="<?= cibo_admin_h((string) ($item['image_path'] ?? '')) ?>">
                        </div>
                        <div class="form-group full">
                          <label class="form-label">Description</label>
                          <textarea class="field-textarea" name="description"><?= cibo_admin_h((string) ($item['description'] ?? '')) ?></textarea>
                        </div>
                      </div>
                      <div class="button-row">
                        <button class="button" type="submit">Save Changes</button>
                        <button class="button-secondary" type="submit" form="<?= $deleteId ?>">Delete</button>
                      </div>
                    </form>

                    <form method="post" id="<?= $deleteId ?>">
                      <input type="hidden" name="admin_action" value="delete_menu_item">
                      <input type="hidden" name="redirect_section" value="menu-items">
                      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                    </form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="panel-card empty-state">No menu items yet. Add a restaurant first, then create menu items for it here.</div>
          <?php endif; ?>
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
          <?php if ($orders): ?>
            <div class="table-wrap">
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
                <tbody>
                  <?php foreach ($orders as $order): ?>
                    <tr>
                      <td><strong>#<?= cibo_admin_h((string) $order['order_number']) ?></strong></td>
                      <td><?= cibo_admin_h((string) $order['user_name']) ?></td>
                      <td><?= cibo_admin_h((string) ($order['items'] ?? 'No items listed')) ?></td>
                      <td><strong><?= cibo_admin_h(cibo_admin_money((float) $order['total_amount'])) ?></strong></td>
                      <td>
                        <form method="post" class="button-row">
                          <input type="hidden" name="admin_action" value="update_order_status">
                          <input type="hidden" name="redirect_section" value="orders">
                          <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                          <select class="field-select" name="order_status">
                            <?php foreach ($orderStatuses as $value => $label): ?>
                              <option value="<?= cibo_admin_h($value) ?>" <?= $value === (string) $order['order_status'] ? 'selected' : '' ?>>
                                <?= cibo_admin_h($label) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <button class="button-secondary" type="submit">Update</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state">No orders available in the database yet.</div>
          <?php endif; ?>
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
          <?php if ($users): ?>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($users as $user): ?>
                    <tr>
                      <td><strong><?= cibo_admin_h((string) $user['name']) ?></strong></td>
                      <td><?= cibo_admin_h((string) $user['email']) ?></td>
                      <td><?= cibo_admin_h(date('d M Y', strtotime((string) $user['created_at']))) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state">No users are available yet.</div>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>

  <script src="assets/admin.js"></script>
</body>
</html>
