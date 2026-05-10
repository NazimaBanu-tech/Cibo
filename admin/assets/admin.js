(() => {
  const ADMIN_EMAIL = 'admin@cibo.local';
  const ADMIN_PASSWORD = 'cibo123';
  const AUTH_KEY = 'cibo_admin_auth';
  const RESTAURANTS_KEY = 'restaurants';
  const MENU_ITEMS_KEY = 'menuItems';
  const ORDERS_KEY = 'orders';
  const USERS_KEY = 'users';
  const LEGACY_ORDERS_KEY = 'cibo_orders';
  const USER_ORDERS_PREFIX = 'cibo_orders::';

  const path = window.location.pathname.toLowerCase();
  const isLoginPage = path.endsWith('/admin/login.php') || path.endsWith('\\admin\\login.php');
  const isPanelPage = path.endsWith('/admin/panel.php') || path.endsWith('/admin/index.php') || path.endsWith('\\admin\\panel.php') || path.endsWith('\\admin\\index.php');
  const phpAuthMode = document.body?.dataset.authMode === 'php';

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

  function slugify(value) {
    return String(value || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatPrice(amount) {
    return '\u20B9' + (Number(amount) || 0).toFixed(0);
  }

  function splitTags(value) {
    if (Array.isArray(value)) {
      return Array.from(new Set(value.map((entry) => slugify(entry)).filter(Boolean)));
    }

    return Array.from(new Set(String(value || '')
      .split(',')
      .map((entry) => slugify(entry))
      .filter(Boolean)));
  }

  function formatTagLabel(value) {
    return String(value || '')
      .split('-')
      .filter(Boolean)
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(' ');
  }

  function parseRestaurantMeta(ratingMeta) {
    const parts = String(ratingMeta || '')
      .replace(/\s+/g, ' ')
      .split('•')
      .map((part) => part.trim())
      .filter(Boolean);

    return {
      rating: (parts[0] || '').replace(/[^0-9.]/g, '') || '4.3',
      deliveryTime: parts[1] || '25-30 mins',
      cuisines: parts.slice(2).join(', ')
    };
  }

  function buildRatingMeta(restaurant) {
    const rating = String(restaurant.rating || '4.3').trim() || '4.3';
    const deliveryTime = String(restaurant.deliveryTime || '25-30 mins').trim() || '25-30 mins';
    const cuisines = String(restaurant.cuisines || restaurant.category || '').trim();

    return `★ ${rating} • ${deliveryTime}${cuisines ? ` • ${cuisines}` : ''}`;
  }

  function buildRestaurantHref(restaurant) {
    return `menu.php?restaurant=${encodeURIComponent(restaurant.slug || slugify(restaurant.name))}`;
  }

  function normalizeRestaurant(restaurant) {
    const parsedMeta = parseRestaurantMeta(restaurant?.ratingMeta);
    const name = String(restaurant?.name || 'Restaurant').trim() || 'Restaurant';
    const slug = slugify(restaurant?.slug || name);
    const cuisines = String(restaurant?.cuisines || parsedMeta.cuisines || '').trim();
    const category = slugify(restaurant?.category || cuisines.split(',')[0] || name) || 'food';
    const rating = String(restaurant?.rating || parsedMeta.rating || '4.3').trim() || '4.3';
    const deliveryTime = String(restaurant?.deliveryTime || parsedMeta.deliveryTime || '25-30 mins').trim() || '25-30 mins';
    const image = String(restaurant?.image || '').trim();
    const heroImage = String(restaurant?.heroImage || image).trim();
    const location = String(restaurant?.location || restaurant?.address || '').trim();
    const offerText = String(restaurant?.offerText || 'Free delivery above ₹199').trim() || 'Free delivery above ₹199';

    return {
      ...restaurant,
      id: String(restaurant?.id || ('restaurant-' + slug)).trim(),
      name,
      slug,
      image,
      heroImage,
      location,
      address: location,
      category,
      cuisines,
      rating,
      deliveryTime,
      offerText,
      ratingMeta: buildRatingMeta({ rating, deliveryTime, cuisines, category }),
      href: String(restaurant?.href || buildRestaurantHref({ slug, name })).trim() || buildRestaurantHref({ slug, name })
    };
  }

  function normalizeMenuItem(item, restaurants) {
    const restaurant = restaurants.find((entry) => entry.id === item?.restaurantId)
      || restaurants.find((entry) => entry.slug === slugify(item?.restaurantSlug || item?.restaurantName));
    const foodType = item?.foodType === 'nonveg' ? 'nonveg' : 'veg';
    const filterTags = splitTags(item?.filterTags || item?.menuTags || item?.tags || '');

    if (!filterTags.length) {
      filterTags.push(foodType);
    }

    return {
      ...item,
      id: String(item?.id || ('menu-item-' + Date.now())).trim(),
      restaurantId: String(item?.restaurantId || restaurant?.id || '').trim(),
      restaurantName: String(item?.restaurantName || restaurant?.name || '').trim(),
      restaurantSlug: String(item?.restaurantSlug || restaurant?.slug || slugify(item?.restaurantName || restaurant?.name || '')).trim(),
      name: String(item?.name || 'Item').trim(),
      price: Number(item?.price) || 0,
      foodType,
      image: String(item?.image || '').trim(),
      description: String(item?.description || '').trim(),
      filterTags
    };
  }

  function resolveAssetUrl(path) {
    const value = String(path || '').trim();

    if (!value) {
      return '';
    }

    if (
      /^(?:[a-z]+:)?\/\//i.test(value) ||
      value.startsWith('/') ||
      value.startsWith('../') ||
      value.startsWith('./') ||
      value.startsWith('data:')
    ) {
      return value;
    }

    return '../' + value.replace(/^\/+/, '');
  }

  function readAdminSession() {
    return readJSON(AUTH_KEY, null);
  }

  function saveAdminSession(payload) {
    saveJSON(AUTH_KEY, payload);
  }

  function clearAdminSession() {
    localStorage.removeItem(AUTH_KEY);
  }

  function createRequestId() {
    return 'cibo-admin-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  async function requestJson(url, options = {}) {
    const response = await fetch(url, {
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Cibo-Request-Id': createRequestId(),
        ...(options.headers || {})
      },
      ...options
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false) {
      if (response.status === 401 || response.status === 403) {
        clearAdminSession();

        if (isPanelPage) {
          window.location.href = 'login.php';
          return Promise.reject(new Error('Admin session expired.'));
        }
      }

      throw new Error(data.message || 'Unable to complete the request.');
    }

    return data;
  }

  function mapServerRestaurant(restaurant) {
    return normalizeRestaurant({
      id: restaurant?.id,
      name: restaurant?.name,
      slug: restaurant?.slug,
      cuisines: restaurant?.cuisine,
      category: restaurant?.cuisine,
      rating: restaurant?.rating,
      deliveryTime: restaurant?.delivery_time,
      image: restaurant?.image_path,
      heroImage: restaurant?.hero_image_path,
      location: restaurant?.location,
      address: restaurant?.address,
      offerText: restaurant?.offer_text
    });
  }

  function mapServerMenuItem(item, restaurants) {
    return normalizeMenuItem({
      id: item?.id,
      restaurantId: item?.restaurant_id,
      restaurantName: item?.restaurant_name,
      restaurantSlug: item?.restaurant_slug,
      name: item?.name,
      slug: item?.slug,
      price: item?.price,
      foodType: item?.food_type,
      image: item?.image_path,
      description: item?.description,
      filterTags: [item?.food_type === 'nonveg' ? 'nonveg' : 'veg']
    }, restaurants);
  }

  function mapServerOrder(order) {
    return {
      id: order.order_number || order.id,
      user: order.user_name,
      restaurant: order.restaurant_name,
      items: Array.isArray(order.items) ? order.items : [],
      total: Number(order.total_amount) || 0,
      status: order.order_status_label || 'Pending',
      rawStatus: order.order_status || 'pending',
      date: order.placed_at || order.created_at || new Date().toISOString()
    };
  }

  function mapServerUser(user) {
    return {
      id: String(user?.id || ''),
      name: String(user?.name || 'User'),
      email: String(user?.email || ''),
      phone: String(user?.phone || ''),
      createdAt: user?.created_at || ''
    };
  }

  async function fetchAdminData() {
    return requestJson('api/dashboard.php');
  }

  async function fetchServerOrders() {
    const data = await requestJson('api/orders.php');
    return Array.isArray(data.orders) ? data.orders.map(mapServerOrder) : [];
  }

  async function updateServerOrderStatus(orderNumber, status) {
    const data = await requestJson('api/orders.php', {
      method: 'POST',
      body: JSON.stringify({
        order_number: orderNumber,
        status
      })
    });

    return data.order || null;
  }

  async function saveServerRestaurant(payload) {
    return requestJson('api/restaurants.php', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  }

  async function deleteServerRestaurant(id) {
    return requestJson('api/restaurants.php', {
      method: 'DELETE',
      body: JSON.stringify({ id })
    });
  }

  async function saveServerMenuItem(payload) {
    return requestJson('api/menu-items.php', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  }

  async function deleteServerMenuItem(id) {
    return requestJson('api/menu-items.php', {
      method: 'DELETE',
      body: JSON.stringify({ id })
    });
  }

  function cleanupLegacySharedData() {
    localStorage.removeItem(ORDERS_KEY);
    localStorage.removeItem(LEGACY_ORDERS_KEY);
    localStorage.removeItem('cibo_address');
  }

  function clearAllOrders() {
    const keysToRemove = [];

    for (let index = 0; index < localStorage.length; index += 1) {
      const storageKey = localStorage.key(index);

      if (!storageKey) {
        continue;
      }

      if (
        storageKey === ORDERS_KEY ||
        storageKey === LEGACY_ORDERS_KEY ||
        storageKey === 'orderStatus' ||
        storageKey === 'orderHistory' ||
        storageKey === 'pendingOrders' ||
        storageKey.startsWith(USER_ORDERS_PREFIX)
      ) {
        keysToRemove.push(storageKey);
      }
    }

    keysToRemove.forEach((storageKey) => {
      localStorage.removeItem(storageKey);
    });
  }

  function readScopedOrders() {
    const collected = [];

    for (let index = 0; index < localStorage.length; index += 1) {
      const storageKey = localStorage.key(index);

      if (!storageKey || !storageKey.startsWith(USER_ORDERS_PREFIX)) {
        continue;
      }

      const scopedOrders = readJSON(storageKey, []);

      if (!Array.isArray(scopedOrders)) {
        continue;
      }

      scopedOrders.forEach((order, orderIndex) => {
        if (!order || typeof order !== 'object') {
          return;
        }

        collected.push({
          ...order,
          id: order.id || ('CB' + Date.now() + '-' + orderIndex),
          user: order.user || order.customerName || order.name || 'Customer',
          restaurant: order.restaurant || order.restaurantName || 'Cibo Order',
          items: Array.isArray(order.items) ? order.items : [],
          total: Number(order.total) || 0,
          status: order.status || 'Pending',
          date: order.date || new Date().toISOString(),
          _storageKey: storageKey
        });
      });
    }

    return collected.sort((first, second) => new Date(second.date || 0) - new Date(first.date || 0));
  }

  function showMessage(message, type = 'success') {
    const feedback = document.getElementById('admin-feedback');

    if (!feedback) {
      return;
    }

    feedback.classList.remove('success', 'error');
    feedback.classList.add(type);
    feedback.style.display = 'flex';
    const copy = feedback.querySelector('span') || feedback;
    copy.textContent = message;

    window.clearTimeout(showMessage._timer);
    showMessage._timer = window.setTimeout(() => {
      feedback.style.display = 'none';
    }, 2200);
  }

  function setButtonBusy(button, busy, busyLabel, idleLabel) {
    if (!button) {
      return;
    }

    if (!button.dataset.defaultLabel) {
      button.dataset.defaultLabel = idleLabel || button.textContent.trim();
    }

    button.disabled = busy;
    button.setAttribute('aria-busy', busy ? 'true' : 'false');
    button.textContent = busy ? (busyLabel || 'Working...') : (idleLabel || button.dataset.defaultLabel);
  }

  function readImageAutocompleteData() {
    const source = document.getElementById('admin-image-autocomplete-data');

    if (!source) {
      return {};
    }

    try {
      const parsed = JSON.parse(source.textContent || '{}');
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
      return {};
    }
  }

  function normalizePathValue(value) {
    return String(value || '')
      .trim()
      .replace(/\\/g, '/')
      .toLowerCase();
  }

  function basenameFromPath(path) {
    const normalized = normalizePathValue(path);
    const parts = normalized.split('/');
    return parts[parts.length - 1] || normalized;
  }

  function compactPathValue(value) {
    return normalizePathValue(value).replace(/[^a-z0-9]/g, '');
  }

  function findBestImageSuggestion(query, suggestions) {
    const normalizedQuery = normalizePathValue(query);

    if (!normalizedQuery) {
      return '';
    }

    const exactPrefixMatch = suggestions.find((entry) => normalizePathValue(entry).startsWith(normalizedQuery));
    if (exactPrefixMatch) {
      return exactPrefixMatch;
    }

    const basenamePrefixMatch = suggestions.find((entry) => basenameFromPath(entry).startsWith(normalizedQuery));
    if (basenamePrefixMatch) {
      return basenamePrefixMatch;
    }

    const basenameIncludesMatch = suggestions.find((entry) => basenameFromPath(entry).includes(normalizedQuery));
    if (basenameIncludesMatch) {
      return basenameIncludesMatch;
    }

    const pathIncludesMatch = suggestions.find((entry) => normalizePathValue(entry).includes(normalizedQuery));
    return pathIncludesMatch || '';
  }

  function setupImageAutocomplete() {
    const autocompleteGroups = readImageAutocompleteData();

    document.querySelectorAll('[data-image-autocomplete]').forEach((input) => {
      const groupName = input.dataset.imageAutocomplete || '';
      const suggestions = Array.isArray(autocompleteGroups[groupName]) ? autocompleteGroups[groupName] : [];
      const hint = document.querySelector(`[data-image-autocomplete-hint="${groupName}"]`);
      const select = document.querySelector(`[data-image-select="${groupName}"]`);
      const formGroup = input.closest('.form-group');
      const menu = document.createElement('div');
      menu.className = 'image-autocomplete-menu';
      menu.setAttribute('data-image-autocomplete-menu', groupName);
      formGroup?.appendChild(menu);

      let activeIndex = -1;

      function getScopedSuggestions() {
        if (groupName !== 'menu-item') {
          return suggestions;
        }

        const restaurantSelect = document.getElementById('menu-restaurant-select');
        const selectedOption = restaurantSelect?.selectedOptions?.[0];
        const selectedRestaurant = String(selectedOption?.textContent || '').trim();
        const compactRestaurant = compactPathValue(selectedRestaurant);

        if (!compactRestaurant || !selectedOption?.value) {
          return suggestions;
        }

        const scopedSuggestions = suggestions.filter((entry) => compactPathValue(entry).includes(compactRestaurant));
        return scopedSuggestions.length ? scopedSuggestions : suggestions;
      }

      function refreshSelectOptions() {
        if (!select) {
          return;
        }

        const scopedSuggestions = getScopedSuggestions();
        select.innerHTML = [
          '<option value="">Select a saved image</option>',
          ...scopedSuggestions.map((entry) => `<option value="${escapeHtml(entry)}">${escapeHtml(entry)}</option>`)
        ].join('');
      }

      function updatePlaceholder() {
        const scopedSuggestions = getScopedSuggestions();
        if (groupName === 'menu-item' && scopedSuggestions.length) {
          input.placeholder = scopedSuggestions[0];
        }
      }

      function getMatchingOptions() {
        const currentValue = normalizePathValue(input.value);
        const activeSuggestions = getScopedSuggestions();
        if (!currentValue) {
          return [];
        }

        return activeSuggestions.filter((entry) => {
          const normalizedEntry = normalizePathValue(entry);
          const normalizedBase = basenameFromPath(entry);

          return normalizedEntry.startsWith(currentValue)
            || normalizedBase.startsWith(currentValue)
            || normalizedBase.includes(currentValue)
            || normalizedEntry.includes(currentValue);
        }).slice(0, 8);
      }

      function closeMenu() {
        activeIndex = -1;
        menu.classList.remove('is-open');
        menu.innerHTML = '';
      }

      function syncSelectValue() {
        if (!select) {
          return;
        }

        const normalizedInput = normalizePathValue(input.value);
        const matchedOption = getScopedSuggestions().find((entry) => normalizePathValue(entry) === normalizedInput) || '';
        select.value = matchedOption;
      }

      function applySuggestion(value) {
        if (!value) {
          return;
        }

        input.value = value;
        updateSuggestionState();
        syncSelectValue();
        closeMenu();
      }

      if (select) {
        refreshSelectOptions();
        select.addEventListener('change', () => {
          if (!select.value) {
            return;
          }

          applySuggestion(select.value);
        });
      }

      function updateSuggestionState() {
        updatePlaceholder();
        refreshSelectOptions();
        const activeSuggestions = getScopedSuggestions();
        const bestMatch = findBestImageSuggestion(input.value, activeSuggestions);
        input.dataset.autocompleteSuggestion = bestMatch;
        const matchingOptions = getMatchingOptions();
        syncSelectValue();

        if (!hint) {
          menu.innerHTML = '';
        } else {
          hint.classList.toggle('is-match', Boolean(bestMatch));
          hint.textContent = bestMatch
            ? `Tab to autocomplete: ${bestMatch}`
            : 'Type part of a filename and press Tab to fill the full path.';
        }

        if (!matchingOptions.length) {
          closeMenu();
          return;
        }

        if (activeIndex >= matchingOptions.length) {
          activeIndex = 0;
        }

        menu.innerHTML = matchingOptions.map((entry, index) => `
          <button
            class="image-autocomplete-option${index === activeIndex ? ' is-active' : ''}"
            type="button"
            data-image-option="${escapeHtml(entry)}"
          >${escapeHtml(entry)}</button>
        `).join('');
        menu.classList.add('is-open');

        menu.querySelectorAll('[data-image-option]').forEach((button) => {
          button.addEventListener('mousedown', (event) => {
            event.preventDefault();
            applySuggestion(button.dataset.imageOption || '');
          });
        });
      }

      input.addEventListener('input', updateSuggestionState);
      input.addEventListener('focus', updateSuggestionState);
      input.addEventListener('blur', () => {
        window.setTimeout(() => {
          closeMenu();
        }, 120);
      });

      input.addEventListener('keydown', (event) => {
        const bestMatch = input.dataset.autocompleteSuggestion || '';
        const matchingOptions = getMatchingOptions();

        if (event.key === 'ArrowDown' && matchingOptions.length) {
          event.preventDefault();
          activeIndex = activeIndex < matchingOptions.length - 1 ? activeIndex + 1 : 0;
          updateSuggestionState();
          return;
        }

        if (event.key === 'ArrowUp' && matchingOptions.length) {
          event.preventDefault();
          activeIndex = activeIndex > 0 ? activeIndex - 1 : matchingOptions.length - 1;
          updateSuggestionState();
          return;
        }

        if (event.key === 'Enter' && activeIndex >= 0 && matchingOptions[activeIndex]) {
          event.preventDefault();
          applySuggestion(matchingOptions[activeIndex]);
          return;
        }

        if (event.key !== 'Tab' || !bestMatch) {
          return;
        }

        if (normalizePathValue(input.value) === normalizePathValue(bestMatch)) {
          return;
        }

        event.preventDefault();
        applySuggestion(activeIndex >= 0 && matchingOptions[activeIndex] ? matchingOptions[activeIndex] : bestMatch);
      });

      updateSuggestionState();
    });
  }

  function setupPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
      toggleButton.addEventListener('click', () => {
        const target = document.getElementById(toggleButton.dataset.passwordToggle || '');

        if (!target) {
          return;
        }

        const showing = target.type === 'text';
        target.type = showing ? 'password' : 'text';
        toggleButton.setAttribute('aria-pressed', String(!showing));
        toggleButton.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        toggleButton.classList.toggle('is-visible', !showing);
      });

      if (groupName === 'menu-item') {
        const restaurantSelect = document.getElementById('menu-restaurant-select');
        restaurantSelect?.addEventListener('change', () => {
          updatePlaceholder();
          refreshSelectOptions();
          syncSelectValue();
          updateSuggestionState();
        });
      }

      updatePlaceholder();
      refreshSelectOptions();
    });
  }

  function setupLoginPage() {
    const form = document.getElementById('admin-login-form');
    const errorBox = document.getElementById('admin-login-error');

    if (!form) {
      return;
    }

    if (!phpAuthMode && readAdminSession()) {
      window.location.href = 'panel.php';
      return;
    }

    setupPasswordToggles();

    if (phpAuthMode) {
      return;
    }

    form.addEventListener('submit', (event) => {
      event.preventDefault();

      const email = form.elements.email.value.trim().toLowerCase();
      const password = form.elements.password.value.trim();

      if (email === ADMIN_EMAIL && password === ADMIN_PASSWORD) {
        saveAdminSession({
          name: 'Cibo Admin',
          email: ADMIN_EMAIL,
          signedInAt: new Date().toISOString()
        });
        window.location.href = 'panel.php';
        return;
      }

      if (errorBox) {
        errorBox.style.display = 'flex';
        const copy = errorBox.querySelector('span') || errorBox;
        copy.textContent = 'Invalid admin credentials. Use admin@cibo.local / cibo123';
      }
    });
  }

  function setupPanelPage() {
    const adminSession = phpAuthMode ? null : readAdminSession();

    if (!phpAuthMode && !adminSession) {
      window.location.href = 'login.php';
      return;
    }

    const sessionName = document.getElementById('admin-session-name');
    if (sessionName && adminSession) {
      sessionName.textContent = adminSession.name || 'Cibo Admin';
    }

    const logoutButton = document.getElementById('admin-logout-button');
    if (logoutButton && !phpAuthMode) {
      logoutButton.addEventListener('click', () => {
        clearAdminSession();
        window.location.href = 'login.php';
      });
    }

    const navLinks = Array.from(document.querySelectorAll('[data-section-link]'));
    const sections = Array.from(document.querySelectorAll('[data-section-panel]'));
    const params = new URLSearchParams(window.location.search);
    let activeSection = params.get('section') || 'dashboard';
    let activeDashboardStat = 'orders';
    const statCards = Array.from(document.querySelectorAll('[data-stat-target]'));
    const dashboardDetailTitle = document.getElementById('dashboard-detail-title');
    const dashboardDetailCopy = document.getElementById('dashboard-detail-copy');
    const dashboardDetailBody = document.getElementById('dashboard-detail-body');
    const dashboardDetailAction = document.getElementById('dashboard-detail-action');

    const restaurantForm = document.getElementById('restaurant-form');
    const restaurantFormTitle = document.getElementById('restaurant-form-title');
    const restaurantFormCancel = document.getElementById('restaurant-form-cancel');
    const restaurantGrid = document.getElementById('admin-restaurants-grid');
    const restaurantEmpty = document.getElementById('admin-restaurants-empty');

    const menuForm = document.getElementById('menu-item-form');
    const menuFormTitle = document.getElementById('menu-form-title');
    const menuFormCancel = document.getElementById('menu-form-cancel');
    const menuRestaurantSelect = document.getElementById('menu-restaurant-select');
    const menuGrid = document.getElementById('admin-menu-grid');
    const menuEmpty = document.getElementById('admin-menu-empty');

    const ordersBody = document.getElementById('admin-orders-body');
    const ordersWrap = document.getElementById('admin-orders-wrap');
    const ordersEmpty = document.getElementById('admin-orders-empty');
    const usersBody = document.getElementById('admin-users-body');
    const usersWrap = document.getElementById('admin-users-wrap');
    const usersEmpty = document.getElementById('admin-users-empty');
    const restaurantSubmitButton = restaurantForm?.querySelector('button[type="submit"]');
    const menuSubmitButton = menuForm?.querySelector('button[type="submit"]');

    let restaurants = [];
    let menuItems = [];
    cleanupLegacySharedData();
    let orders = [];
    let users = [];
    let dashboardStats = {
      orders: 0,
      revenue: 0,
      users: 0,
      restaurants: 0
    };
    let statusOptions = {
      pending: 'Pending',
      preparing: 'Preparing',
      out_for_delivery: 'Out for Delivery',
      delivered: 'Delivered'
    };

    setupImageAutocomplete();

    async function loadPanelData() {
      if (restaurantEmpty && !restaurants.length) {
        restaurantEmpty.style.display = 'block';
        restaurantEmpty.textContent = 'Loading restaurants...';
      }

      if (menuEmpty && !menuItems.length) {
        menuEmpty.style.display = 'block';
        menuEmpty.textContent = 'Loading menu items...';
      }

      if (ordersEmpty && !orders.length) {
        ordersWrap.style.display = 'none';
        ordersEmpty.style.display = 'block';
        ordersEmpty.textContent = 'Loading orders...';
      }

      if (usersEmpty && !users.length) {
        usersWrap.style.display = 'none';
        usersEmpty.style.display = 'block';
        usersEmpty.textContent = 'Loading users...';
      }

      const data = await fetchAdminData();
      dashboardStats = data?.stats && typeof data.stats === 'object'
        ? {
            orders: Number(data.stats.orders) || 0,
            revenue: Number(data.stats.revenue) || 0,
            users: Number(data.stats.users) || 0,
            restaurants: Number(data.stats.restaurants) || 0
          }
        : {
            orders: 0,
            revenue: 0,
            users: 0,
            restaurants: 0
          };
      statusOptions = data?.status_options && typeof data.status_options === 'object'
        ? data.status_options
        : statusOptions;
      restaurants = Array.isArray(data.restaurants)
        ? data.restaurants.map(mapServerRestaurant)
        : [];
      menuItems = Array.isArray(data.menu_items)
        ? data.menu_items.map((item) => mapServerMenuItem(item, restaurants))
        : [];
      orders = Array.isArray(data.orders)
        ? data.orders.map(mapServerOrder)
        : [];
      users = Array.isArray(data.users)
        ? data.users.map(mapServerUser)
        : [];
    }

    function labelToStatusValue(label) {
      return String(label || 'Pending')
        .toLowerCase()
        .replace(/\s+/g, '_');
    }

    function statusValueToLabel(value) {
      const normalizedValue = String(value || 'pending').toLowerCase();
      return String(statusOptions[normalizedValue] || 'Pending');
    }

    function activateSection(sectionName) {
      activeSection = sectionName;
      navLinks.forEach((link) => {
        link.classList.toggle('is-active', link.dataset.sectionLink === sectionName);
      });

      sections.forEach((section) => {
        section.classList.toggle('is-active', section.dataset.sectionPanel === sectionName);
      });

      const nextUrl = new URL(window.location.href);
      nextUrl.searchParams.set('section', sectionName);
      window.history.replaceState({}, '', nextUrl);
    }

    function formatDate(value) {
      return new Date(value || Date.now()).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function renderDashboardDetails(statName) {
      if (!dashboardDetailTitle || !dashboardDetailCopy || !dashboardDetailBody || !dashboardDetailAction) {
        return;
      }

      activeDashboardStat = statName;
      statCards.forEach((card) => {
        card.classList.toggle('is-active', card.dataset.statTarget === statName);
      });

      let title = 'Orders Snapshot';
      let copy = 'Recent order activity across the customer flow.';
      let actionLabel = 'Open Orders';
      let actionSection = 'orders';
      let items = [];

      if (statName === 'revenue') {
        const revenue = Number(dashboardStats.revenue) || 0;
        const averageOrder = dashboardStats.orders ? revenue / dashboardStats.orders : 0;
        title = 'Revenue Details';
        copy = 'A quick financial summary based on the database-backed order records.';
        actionLabel = 'View Orders';
        actionSection = 'orders';
        items = [
          {
            title: 'Total Revenue',
            meta: 'Combined order value across all recorded orders.',
            value: formatPrice(revenue)
          },
          {
            title: 'Average Order Value',
            meta: 'Mean basket total from the saved orders.',
            value: formatPrice(averageOrder)
          },
          {
            title: 'Latest Order Total',
            meta: orders[0] ? `${orders[0].user || 'Customer'} • ${orders[0].restaurant || 'Cibo Order'}` : 'No order data yet.',
            value: orders[0] ? formatPrice(orders[0].total) : '₹0'
          }
        ];
      } else if (statName === 'users') {
        title = 'Users Snapshot';
        copy = 'Recently synced users available in this demo environment.';
        actionLabel = 'Open Users';
        actionSection = 'users';
        items = users.slice(0, 5).map((user) => ({
          title: user.name || 'User',
          meta: user.email || 'No email provided',
          value: formatDate(user.createdAt)
        }));
      } else if (statName === 'restaurants') {
        title = 'Restaurants Snapshot';
        copy = 'Current restaurant cards and the details powering the customer experience.';
        actionLabel = 'Open Restaurants';
        actionSection = 'restaurants';
        items = restaurants.map((restaurant) => ({
          title: restaurant.name || 'Restaurant',
          meta: `${restaurant.location || 'Unknown location'} • ${restaurant.category || 'food'}`,
          value: restaurant.ratingMeta || '★ 4.3 • 25-30 mins'
        }));
      } else {
        title = 'Orders Snapshot';
        copy = 'Recent order activity from the database-backed customer flow.';
        actionLabel = 'Open Orders';
        actionSection = 'orders';
        items = orders.slice(0, 5).map((order) => ({
          title: `#${order.id}`,
          meta: `${order.user || 'Customer'} • ${order.restaurant || 'Cibo Order'}`,
          value: `${order.status || 'Pending'} • ${formatPrice(order.total)}`
        }));
      }

      if (!items.length) {
        items = [{
          title: 'No data yet',
          meta: 'Create or place some demo records and this area will fill in automatically.',
          value: 'Ready for data'
        }];
      }

      dashboardDetailTitle.textContent = title;
      dashboardDetailCopy.textContent = copy;
      dashboardDetailAction.textContent = actionLabel;
      dashboardDetailAction.dataset.targetSection = actionSection;
      dashboardDetailBody.innerHTML = items.map((item) => `
        <article class="dashboard-detail-item">
          <div>
            <strong>${escapeHtml(item.title)}</strong>
            <span>${escapeHtml(item.meta)}</span>
          </div>
          <small>${escapeHtml(item.value)}</small>
        </article>
      `).join('');
    }

    function populateRestaurantSelect() {
      if (!menuRestaurantSelect) {
        return;
      }

      const selectedValue = menuRestaurantSelect.value;
      menuRestaurantSelect.innerHTML = '<option value="">Select restaurant</option>' + restaurants
        .map((restaurant) => `<option value="${escapeHtml(restaurant.id)}">${escapeHtml(restaurant.name)}</option>`)
        .join('');

      if (selectedValue) {
        menuRestaurantSelect.value = selectedValue;
      }
    }

    function renderDashboard() {
      const statOrders = document.getElementById('stat-orders');
      const statRevenue = document.getElementById('stat-revenue');
      const statUsers = document.getElementById('stat-users');
      const statRestaurants = document.getElementById('stat-restaurants');

      if (statOrders) statOrders.textContent = String(Number(dashboardStats.orders) || 0);
      if (statRevenue) statRevenue.textContent = formatPrice(Number(dashboardStats.revenue) || 0);
      if (statUsers) statUsers.textContent = String(Number(dashboardStats.users) || 0);
      if (statRestaurants) statRestaurants.textContent = String(Number(dashboardStats.restaurants) || 0);
      renderDashboardDetails(activeDashboardStat);
    }

    function renderRestaurants() {
      populateRestaurantSelect();

      if (!restaurantGrid || !restaurantEmpty) {
        return;
      }

      if (!restaurants.length) {
        restaurantGrid.innerHTML = '';
        restaurantEmpty.style.display = 'block';
        restaurantEmpty.textContent = 'No restaurants yet. Add your first restaurant to populate the customer experience.';
        renderDashboard();
        return;
      }

      restaurantEmpty.style.display = 'none';
      restaurantGrid.innerHTML = restaurants.map((restaurant) => `
        <article class="admin-card restaurant-card">
          ${restaurant.image ? `<img class="card-image" src="${escapeHtml(resolveAssetUrl(restaurant.image))}" alt="${escapeHtml(restaurant.name)}">` : ''}
          <div class="card-body">
            <div class="eyebrow">${escapeHtml(restaurant.location)}</div>
            <h4>${escapeHtml(restaurant.name)}</h4>
            <p class="card-meta">${escapeHtml(restaurant.cuisines || restaurant.category)}</p>
            <p class="card-meta">${escapeHtml(`${restaurant.rating} ★ • ${restaurant.deliveryTime}`)}</p>
            <p class="card-meta">${escapeHtml(`Hero banner: ${restaurant.heroImage || 'Uses card image'}`)}</p>
            <div class="button-row">
              <button class="button-secondary" type="button" data-restaurant-edit="${escapeHtml(restaurant.id)}">Edit</button>
              <button class="button-secondary" type="button" data-restaurant-delete="${escapeHtml(restaurant.id)}">Delete</button>
            </div>
          </div>
        </article>
      `).join('');

      renderDashboard();
    }

    function renderMenuItems() {
      if (!menuGrid || !menuEmpty) {
        return;
      }

      if (!menuItems.length) {
        menuGrid.innerHTML = '';
        menuEmpty.style.display = 'block';
        menuEmpty.textContent = 'No menu items yet. Create one to start building the catalog.';
        renderDashboard();
        return;
      }

      menuEmpty.style.display = 'none';
      menuGrid.innerHTML = menuItems.map((item) => `
        <article class="admin-card menu-card">
          ${item.image ? `<img class="card-image" src="${escapeHtml(resolveAssetUrl(item.image))}" alt="${escapeHtml(item.name)}">` : ''}
          <div class="card-body">
            <div class="eyebrow">${escapeHtml(item.restaurantName)}</div>
            <h4>${escapeHtml(item.name)}</h4>
            <div class="price-row">
              <strong>${formatPrice(item.price)}</strong>
              <span class="badge ${escapeHtml(item.foodType)}">${item.foodType === 'nonveg' ? 'Non-Veg' : 'Veg'}</span>
            </div>
            <p class="card-meta">${escapeHtml(item.filterTags.map(formatTagLabel).join(' • '))}</p>
            <p>${escapeHtml(item.description)}</p>
            <div class="button-row">
              <button class="button-secondary" type="button" data-menu-edit="${escapeHtml(item.id)}">Edit</button>
              <button class="button-secondary" type="button" data-menu-delete="${escapeHtml(item.id)}">Delete</button>
            </div>
          </div>
        </article>
      `).join('');

      renderDashboard();
    }

    function renderOrders() {
      if (!ordersBody || !ordersWrap || !ordersEmpty) {
        return;
      }

      if (!orders.length) {
        ordersBody.innerHTML = '';
        ordersWrap.style.display = 'none';
        ordersEmpty.style.display = 'block';
        ordersEmpty.textContent = 'No orders yet. Customer orders will appear here as they are placed.';
        renderDashboard();
        return;
      }

      ordersWrap.style.display = 'block';
      ordersEmpty.style.display = 'none';
      ordersBody.innerHTML = orders.map((order) => {
        const itemSummary = Array.isArray(order.items) && order.items.length
          ? order.items.map((item) => `${item.name} x${item.quantity}`).join(', ')
          : 'No items available';
        const orderStatusOptionsMarkup = Object.entries(statusOptions)
          .map(([value, label]) => ({ value: String(value || '').toLowerCase(), label: String(label || 'Pending') }))
          .filter((option) => option.value)
          .map((option) => `<option value="${escapeHtml(option.value)}" ${labelToStatusValue(order.status) === option.value ? 'selected' : ''}>${escapeHtml(option.label)}</option>`)
          .join('');

        return `
          <tr>
            <td><strong>#${escapeHtml(order.id)}</strong></td>
            <td>${escapeHtml(order.user || 'Customer')}</td>
            <td>${escapeHtml(itemSummary)}</td>
            <td><strong>${formatPrice(order.total)}</strong></td>
            <td>
              <select class="field-select" data-order-status="${escapeHtml(order.id)}">
                ${orderStatusOptionsMarkup}
              </select>
            </td>
          </tr>
        `;
      }).join('');

      renderDashboard();
    }

    function renderUsers() {
      if (!usersBody || !usersWrap || !usersEmpty) {
        return;
      }

      if (!users.length) {
        usersBody.innerHTML = '';
        usersWrap.style.display = 'none';
        usersEmpty.style.display = 'block';
        usersEmpty.textContent = 'No users yet. New customer signups will appear here automatically.';
        renderDashboard();
        return;
      }

      usersWrap.style.display = 'block';
      usersEmpty.style.display = 'none';
      usersBody.innerHTML = users.map((user) => `
        <tr>
          <td><strong>${escapeHtml(user.name || 'User')}</strong></td>
          <td>${escapeHtml(user.email || 'No email')}</td>
          <td>${escapeHtml(new Date(user.createdAt || Date.now()).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }))}</td>
        </tr>
      `).join('');

      renderDashboard();
    }

    function resetRestaurantForm() {
      if (!restaurantForm) {
        return;
      }

      restaurantForm.reset();
      restaurantForm.elements.id.value = '';
      if (restaurantFormTitle) {
        restaurantFormTitle.textContent = 'Add Restaurant';
      }
      if (restaurantFormCancel) {
        restaurantFormCancel.style.display = 'none';
      }
    }

    function resetMenuForm() {
      if (!menuForm) {
        return;
      }

      menuForm.reset();
      menuForm.elements.id.value = '';
      if (menuFormTitle) {
        menuFormTitle.textContent = 'Add Menu Item';
      }
      if (menuFormCancel) {
        menuFormCancel.style.display = 'none';
      }
    }

    if (restaurantForm) {
      restaurantForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const id = restaurantForm.elements.id.value.trim();
        const name = restaurantForm.elements.name.value.trim();
        const image = restaurantForm.elements.image.value.trim();
        const heroImage = restaurantForm.elements.heroImage.value.trim();
        const location = restaurantForm.elements.location.value.trim();
        const cuisines = restaurantForm.elements.cuisines.value.trim();
        const category = restaurantForm.elements.category.value.trim();
        const rating = restaurantForm.elements.rating.value.trim();
        const deliveryTime = restaurantForm.elements.deliveryTime.value.trim();
        const offerText = restaurantForm.elements.offerText.value.trim();

        if (!name || !location || !cuisines || !category) {
          showMessage('Please fill in restaurant name, location, cuisines, and category.', 'error');
          return;
        }

        const payload = {
          id: id || '',
          name,
          image_path: image,
          hero_image_path: heroImage || image,
          location,
          cuisine: cuisines || category,
          rating: rating || '4.3',
          delivery_time: deliveryTime || '25-30 mins',
          offerText: 'Free delivery above ₹199',
          address: location,
        };

        try {
          setButtonBusy(restaurantSubmitButton, true, id ? 'Saving Restaurant...' : 'Adding Restaurant...');
          await saveServerRestaurant({
            ...payload,
            offer_text: offerText || payload.offerText || payload.offer_text || 'Free delivery above 199'
          });
          await loadPanelData();
          resetRestaurantForm();
          renderRestaurants();
          renderMenuItems();
          renderOrders();
          renderUsers();
          showMessage(id ? 'Restaurant updated successfully.' : 'Restaurant added successfully.');
        } catch (error) {
          showMessage(error.message || 'Unable to save the restaurant.', 'error');
        } finally {
          setButtonBusy(restaurantSubmitButton, false, '', id ? 'Save Restaurant' : 'Add Restaurant');
        }
      });
    }

    if (menuForm) {
      menuForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const id = menuForm.elements.id.value.trim();
        const restaurantId = menuForm.elements.restaurantId.value.trim();
        const name = menuForm.elements.name.value.trim();
        const price = Number(menuForm.elements.price.value || 0);
        const foodType = menuForm.elements.foodType.value.trim();
        const image = menuForm.elements.image.value.trim();
        const description = menuForm.elements.description.value.trim();
        const restaurant = restaurants.find((item) => item.id === restaurantId);

        if (!restaurantId || !restaurant || !name || price <= 0) {
          showMessage('Please select a restaurant and enter a valid item name and price.', 'error');
          return;
        }

        const payload = {
          id: id || '',
          restaurant_id: restaurantId,
          name,
          price,
          food_type: foodType === 'nonveg' ? 'nonveg' : 'veg',
          image_path: image,
          description
        };

        try {
          setButtonBusy(menuSubmitButton, true, id ? 'Saving Item...' : 'Adding Item...');
          await saveServerMenuItem(payload);
          await loadPanelData();
          resetMenuForm();
          renderRestaurants();
          renderMenuItems();
          renderOrders();
          renderUsers();
          showMessage(id ? 'Item updated successfully.' : 'Item added successfully.');
        } catch (error) {
          showMessage(error.message || 'Unable to save the menu item.', 'error');
        } finally {
          setButtonBusy(menuSubmitButton, false, '', id ? 'Save Menu Item' : 'Add Menu Item');
        }
      });
    }

    if (restaurantFormCancel) {
      restaurantFormCancel.addEventListener('click', resetRestaurantForm);
    }

    if (menuFormCancel) {
      menuFormCancel.addEventListener('click', resetMenuForm);
    }

    document.addEventListener('click', (event) => {
      const restaurantEdit = event.target.closest('[data-restaurant-edit]');
      const restaurantDelete = event.target.closest('[data-restaurant-delete]');
      const menuEdit = event.target.closest('[data-menu-edit]');
      const menuDelete = event.target.closest('[data-menu-delete]');

      if (restaurantEdit && restaurantForm) {
        const restaurant = restaurants.find((item) => item.id === restaurantEdit.dataset.restaurantEdit);
        if (!restaurant) {
          return;
        }

        restaurantForm.elements.id.value = restaurant.id;
        restaurantForm.elements.name.value = restaurant.name || '';
        restaurantForm.elements.image.value = restaurant.image || '';
        restaurantForm.elements.heroImage.value = restaurant.heroImage || '';
        restaurantForm.elements.location.value = restaurant.location || '';
        restaurantForm.elements.cuisines.value = restaurant.cuisines || '';
        restaurantForm.elements.category.value = restaurant.category || '';
        restaurantForm.elements.rating.value = restaurant.rating || '';
        restaurantForm.elements.deliveryTime.value = restaurant.deliveryTime || '';
        restaurantForm.elements.offerText.value = restaurant.offerText || '';
        if (restaurantFormTitle) {
          restaurantFormTitle.textContent = 'Edit Restaurant';
        }
        if (restaurantFormCancel) {
          restaurantFormCancel.style.display = 'inline-flex';
        }
        activateSection('restaurants');
        restaurantForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      if (restaurantDelete) {
        const restaurantId = restaurantDelete.dataset.restaurantDelete;
        setButtonBusy(restaurantDelete, true, 'Removing...');
        deleteServerRestaurant(restaurantId)
          .then(loadPanelData)
          .then(() => {
            renderRestaurants();
            renderMenuItems();
            renderOrders();
            renderUsers();
            showMessage('Restaurant removed.');
          })
          .catch((error) => {
            showMessage(error.message || 'Unable to remove the restaurant.', 'error');
          })
          .finally(() => {
            setButtonBusy(restaurantDelete, false, '', 'Delete');
          });
      }

      if (menuEdit && menuForm) {
        const item = menuItems.find((entry) => entry.id === menuEdit.dataset.menuEdit);
        if (!item) {
          return;
        }

        menuForm.elements.id.value = item.id;
        menuForm.elements.restaurantId.value = item.restaurantId || '';
        menuForm.elements.name.value = item.name || '';
        menuForm.elements.price.value = item.price || '';
        menuForm.elements.foodType.value = item.foodType || 'veg';
        menuForm.elements.image.value = item.image || '';
        menuForm.elements.description.value = item.description || '';
        if (menuFormTitle) {
          menuFormTitle.textContent = 'Edit Menu Item';
        }
        if (menuFormCancel) {
          menuFormCancel.style.display = 'inline-flex';
        }
        activateSection('menu-items');
        menuForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      if (menuDelete) {
        setButtonBusy(menuDelete, true, 'Removing...');
        deleteServerMenuItem(menuDelete.dataset.menuDelete)
          .then(loadPanelData)
          .then(() => {
            renderRestaurants();
            renderMenuItems();
            renderOrders();
            renderUsers();
            showMessage('Item removed.');
          })
          .catch((error) => {
            showMessage(error.message || 'Unable to remove the item.', 'error');
          })
          .finally(() => {
            setButtonBusy(menuDelete, false, '', 'Delete');
          });
      }
    });

    document.addEventListener('change', async (event) => {
      const statusSelect = event.target.closest('[data-order-status]');

      if (!statusSelect) {
        return;
      }

      const order = orders.find((item) => item.id === statusSelect.dataset.orderStatus);

      if (!order) {
        return;
      }

      try {
        statusSelect.disabled = true;
        const updatedOrder = await updateServerOrderStatus(statusSelect.dataset.orderStatus, statusSelect.value);
        order.status = statusValueToLabel(updatedOrder?.order_status || statusSelect.value);
        order.rawStatus = updatedOrder?.order_status || statusSelect.value;
        await loadPanelData();
        renderOrders();
        renderDashboard();
        showMessage('Order updated.');
      } catch (error) {
        statusSelect.value = labelToStatusValue(order.status);
        showMessage(error.message || 'Unable to update the order.', 'error');
      } finally {
        statusSelect.disabled = false;
      }
    });

    navLinks.forEach((link) => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        activateSection(link.dataset.sectionLink);
      });
    });

    statCards.forEach((card) => {
      card.addEventListener('click', () => {
        renderDashboardDetails(card.dataset.statTarget || 'orders');
      });
    });

    if (dashboardDetailAction) {
      dashboardDetailAction.addEventListener('click', () => {
        activateSection(dashboardDetailAction.dataset.targetSection || 'orders');
      });
    }

    activateSection(activeSection);
    Promise.resolve()
      .then(loadPanelData)
      .then(() => {
        renderRestaurants();
        renderMenuItems();
        renderOrders();
        renderUsers();
      })
      .catch((error) => {
        showMessage(error.message || 'Unable to load admin data.', 'error');
      });
  }

  if (isLoginPage) {
    setupLoginPage();
  }

  if (isPanelPage) {
    setupPanelPage();
  }
})();
