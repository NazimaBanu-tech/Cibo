(() => {
  const MAX_ITEM_QUANTITY = 5;
  const MAX_ORDER_ITEMS = 15;
  const ITEM_LIMIT_MESSAGE = 'Max 5 per item reached';
  const ORDER_LIMIT_MESSAGE = `Order limit reached (Max ${MAX_ORDER_ITEMS} items per order)`;
  const RESTAURANTS_KEY = 'restaurants';
  const MENU_ITEMS_KEY = 'menuItems';
  const cartManager = window.CiboCartManager;
  const cardSelector = '.food-big-card';
  const actionSelector = '.food-action';
  const chipSelector = '.menu-chips .chip';
  const favoritesApi = window.CiboFavorites;
  const heroTitle = document.querySelector('.restaurant-hero-left h1');
  const heroBreadcrumb = document.querySelector('.restaurant-breadcrumb');
  const heroMeta = document.querySelector('.restaurant-meta');
  const heroAddress = document.querySelector('.restaurant-address');
  const heroOffer = document.querySelector('.restaurant-offer');
  const heroImage = document.querySelector('.restaurant-hero-right img');
  const foodGrid = document.querySelector('.food-grid');
  const menuChips = document.querySelector('.menu-chips');
  const heroFallbackImage = heroImage?.dataset.heroFallback || heroImage?.getAttribute('src') || '';

  let cart = readCart();
  let restaurantModal = null;
  let restaurantModalTitle = null;
  let restaurantModalMessage = null;
  let restaurantModalHint = null;
  let restaurantModalCancelButton = null;
  let restaurantModalConfirmButton = null;
  let pendingRestaurantSwap = null;
  let lastModalTrigger = null;

  function readCart() {
    return cartManager ? cartManager.getCart() : {};
  }

  function syncLocalCart() {
    cart = readCart();
    return cart;
  }

  function readJSON(key, fallback) {
    try {
      const rawValue = localStorage.getItem(key);
      return rawValue ? JSON.parse(rawValue) : fallback;
    } catch (error) {
      return fallback;
    }
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

  function formatPrice(value) {
    return '\u20B9' + (Number(value) || 0).toFixed(0);
  }

  function getCartItems(cartState = cart) {
    return Object.values(cartState || {}).filter((item) => Number(item?.quantity) > 0);
  }

  function normalizeCartMutationResult(result) {
    syncLocalCart();

    switch (result?.status) {
      case 'ok':
        return {
          ok: true
        };
      case 'restaurant_conflict':
        return {
          ok: false,
          reason: 'restaurant_conflict',
          conflict: {
            currentRestaurantName: result.currentRestaurant?.name || 'this restaurant',
            nextRestaurantName: result.newRestaurant?.name || 'that restaurant',
            itemData: result.pendingItem || null
          }
        };
      case 'item_limit':
        return {
          ok: false,
          reason: 'item_limit',
          message: result.message || ITEM_LIMIT_MESSAGE
        };
      case 'order_limit':
        return {
          ok: false,
          reason: 'order_limit',
          message: result.message || ORDER_LIMIT_MESSAGE
        };
      default:
        return {
          ok: false,
          reason: 'error',
          message: result?.message || 'Unable to update the cart right now.'
        };
    }
  }

  function getHeroImageCandidates(heroPath, fallbackPath, slug) {
    const candidates = [];
    const normalizedHeroPath = String(heroPath || '').trim().replace(/\\/g, '/');
    const normalizedFallbackPath = String(fallbackPath || '').trim().replace(/\\/g, '/');
    const normalizedSlug = slugify(slug);

    if (
      normalizedHeroPath
      && (
        normalizedHeroPath.includes('images/restaurant-heroes/')
        || normalizedHeroPath.split('/').pop()?.includes('-hero')
      )
    ) {
      candidates.push(normalizedHeroPath);
    }

    const fallbackBase = normalizedFallbackPath.split('/').pop()?.replace(/\.[^.]+$/, '') || '';
    [fallbackBase, normalizedSlug].filter(Boolean).forEach((baseName) => {
      ['jpg', 'jpeg', 'png', 'webp', 'gif'].forEach((extension) => {
        candidates.push(`images/restaurant-heroes/${baseName}-hero.${extension}`);
      });
    });

    if (normalizedHeroPath && normalizedHeroPath !== normalizedFallbackPath) {
      candidates.push(normalizedHeroPath);
    }

    if (normalizedFallbackPath) {
      candidates.push(normalizedFallbackPath);
    }

    return Array.from(new Set(candidates.filter(Boolean)));
  }

  function getHeartIcon() {
    return `
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 21.35 10.55 20.03C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35Z"></path>
      </svg>
    `;
  }

  function getCurrentRestaurantSlug() {
    const params = new URLSearchParams(window.location.search);
    return slugify(params.get('restaurant') || heroTitle?.textContent || document.title.replace(' - Cibo', ''));
  }

  function getDefaultRestaurantData() {
    const name = heroTitle?.textContent.trim() || document.title.replace(' - Cibo', '');
    const defaultHeroImage = heroImage?.getAttribute('src') || '';
    return {
      id: 'restaurant-' + slugify(name),
      slug: slugify(name),
      name,
      location: heroAddress?.textContent.trim() || '',
      category: heroMeta?.lastElementChild?.textContent.trim() || '',
      ratingMeta: heroMeta?.textContent.replace(/\s+/g, ' ').trim() || '',
      offerText: heroOffer?.textContent.trim() || 'Free delivery above \u20B9199',
      image: heroFallbackImage,
      heroImage: defaultHeroImage,
      href: window.location.pathname.split('/').pop() || ('menu.php?restaurant=' + encodeURIComponent(slugify(name)))
    };
  }

  function syncHeroImage(imagePath, fallbackPath, restaurantName) {
    if (!heroImage) {
      return;
    }

    const restaurantSlug = getCurrentRestaurantSlug();
    const candidates = getHeroImageCandidates(imagePath, fallbackPath || heroFallbackImage, restaurantSlug);
    const safeFallback = candidates[candidates.length - 1] || '';
    const tryCandidate = (index) => {
      const nextCandidate = candidates[index] || safeFallback;

      if (!nextCandidate) {
        return;
      }

      heroImage.onerror = () => {
        const nextIndex = index + 1;

        if (nextIndex < candidates.length) {
          tryCandidate(nextIndex);
          return;
        }

        heroImage.onerror = null;
      };

      heroImage.src = nextCandidate;
    };

    if (!candidates.length) {
      return;
    }

    heroImage.alt = `${restaurantName || 'Restaurant'} hero image`;
    tryCandidate(0);
  }

  function getDefaultMenuItems(restaurant) {
    return Array.from(document.querySelectorAll(cardSelector)).map((card, index) => {
      const name = card.querySelector('h3')?.textContent.trim() || ('Item ' + (index + 1));
      const priceText = card.querySelector('.food-price')?.textContent.trim() || '\u20B90';
      const price = Number(priceText.replace(/[^\d.]/g, '')) || 0;
      const image = card.querySelector('.food-image-wrap img');
      const tag = card.querySelector('.food-tag');
      return {
        id: 'menu-item-' + restaurant.slug + '-' + slugify(name),
        restaurantId: restaurant.id,
        restaurantName: restaurant.name,
        restaurantSlug: restaurant.slug,
        restaurantHref: restaurant.href,
        name,
        slug: slugify(name),
        price,
        foodType: tag?.classList.contains('nonveg') ? 'nonveg' : 'veg',
        image: image?.getAttribute('src') || '',
        description: card.querySelector('.food-desc')?.textContent.trim() || '',
        filterTags: String(card.dataset.category || '').split(',').map((value) => slugify(value)).filter(Boolean)
      };
    });
  }

  function ensureLocalStorageData() {
    const restaurantSlug = getCurrentRestaurantSlug();
    const defaultRestaurant = getDefaultRestaurantData();
    const restaurants = readJSON(RESTAURANTS_KEY, []);
    const menuItems = readJSON(MENU_ITEMS_KEY, []);
    const hasRestaurantsKey = localStorage.getItem(RESTAURANTS_KEY) !== null;
    const hasMenuItemsKey = localStorage.getItem(MENU_ITEMS_KEY) !== null;
    const safeRestaurants = Array.isArray(restaurants) ? restaurants : [];
    const safeMenuItems = Array.isArray(menuItems) ? menuItems : [];
    const currentRestaurant = safeRestaurants.find((restaurant) => slugify(restaurant.slug || restaurant.name) === restaurantSlug);
    const effectiveRestaurant = currentRestaurant || defaultRestaurant;
    const hasItemsForRestaurant = safeMenuItems.some((item) => slugify(item.restaurantSlug || item.restaurantName) === effectiveRestaurant.slug);

    if (!hasRestaurantsKey) {
      localStorage.setItem(RESTAURANTS_KEY, JSON.stringify([defaultRestaurant]));
    } else if (!currentRestaurant && !window.location.search) {
      localStorage.setItem(RESTAURANTS_KEY, JSON.stringify([...safeRestaurants, defaultRestaurant]));
    }

    if (!hasMenuItemsKey) {
      localStorage.setItem(MENU_ITEMS_KEY, JSON.stringify(getDefaultMenuItems(effectiveRestaurant)));
      return;
    }

    if (!hasItemsForRestaurant && !window.location.search) {
      localStorage.setItem(MENU_ITEMS_KEY, JSON.stringify([...safeMenuItems, ...getDefaultMenuItems(effectiveRestaurant)]));
    }
  }

  async function fetchCanonicalCatalog() {
    const response = await fetch('api/catalog.php', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json'
      }
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false) {
      throw new Error(data.message || 'Unable to load the latest menu.');
    }

    localStorage.setItem(RESTAURANTS_KEY, JSON.stringify(Array.isArray(data.restaurants) ? data.restaurants : []));
    localStorage.setItem(MENU_ITEMS_KEY, JSON.stringify(Array.isArray(data.menu_items) ? data.menu_items : []));
  }

  function renderDynamicMenu() {
    if (!foodGrid) {
      return;
    }

    ensureLocalStorageData();

    const restaurantSlug = getCurrentRestaurantSlug();
    const restaurants = readJSON(RESTAURANTS_KEY, []);
    const menuItems = readJSON(MENU_ITEMS_KEY, []);
    const safeRestaurants = Array.isArray(restaurants) ? restaurants : [];
    const safeMenuItems = Array.isArray(menuItems) ? menuItems : [];
    const restaurant = safeRestaurants.find((item) => slugify(item.slug || item.name) === restaurantSlug) || getDefaultRestaurantData();
    const items = safeMenuItems.filter((item) => slugify(item.restaurantSlug || item.restaurantName) === restaurantSlug);

    if (heroTitle) {
      heroTitle.textContent = restaurant.name || heroTitle.textContent;
      document.title = `${restaurant.name || 'Menu'} - Cibo`;
    }

    if (heroBreadcrumb) {
      heroBreadcrumb.innerHTML = `<a href="index.php">Home</a> / ${escapeHtml(restaurant.name || 'Menu')}`;
    }

    if (heroMeta) {
      const metaText = restaurant.ratingMeta || `4.3 • 25-30 mins • ${restaurant.category || 'Popular food'}`;
      const parts = metaText.split('•').map((part) => part.trim()).filter(Boolean);
      heroMeta.innerHTML = parts.map((part) => `<span>${escapeHtml(part)}</span>`).join('');
    }

    if (heroAddress) {
      heroAddress.textContent = restaurant.location || '';
    }

    if (heroOffer) {
      heroOffer.textContent = restaurant.offerText || 'Free delivery above \u20B9199';
    }

    syncHeroImage(restaurant.heroImage, restaurant.image, restaurant.name);

    if (!items.length) {
      foodGrid.innerHTML = '<div class="empty-state" style="grid-column: 1 / -1; color: var(--muted);">No items available</div>';

      if (menuChips) {
        menuChips.innerHTML = '<button class="chip active" data-filter="all">All</button>';
      }

      return;
    }

    const chipValues = Array.from(new Set(items.flatMap((item) => {
      const tags = Array.isArray(item.filterTags) && item.filterTags.length ? item.filterTags : [item.foodType || 'veg'];
      return tags.map((tag) => slugify(tag)).filter(Boolean);
    })));

    if (menuChips) {
      const chips = ['all', ...chipValues];
      menuChips.innerHTML = chips.map((chip, index) => `
        <button class="chip ${index === 0 ? 'active' : ''}" data-filter="${escapeHtml(chip)}">${escapeHtml(chip === 'all' ? 'All' : chip.replace(/-/g, ' '))}</button>
      `).join('');
    }

    foodGrid.innerHTML = items.map((item) => {
      const tags = Array.isArray(item.filterTags) && item.filterTags.length ? item.filterTags : [item.foodType || 'veg'];
      const tagMarkup = item.foodType
        ? `<span class="food-tag ${item.foodType === 'nonveg' ? 'nonveg' : 'veg'}">• ${item.foodType === 'nonveg' ? 'Non-Veg' : 'Veg'}</span>`
        : '';
      const favoriteItem = {
        id: item.id,
        restaurantId: item.restaurantId || restaurant.id,
        restaurantName: item.restaurantName || restaurant.name,
        restaurantSlug: item.restaurantSlug || restaurant.slug,
        restaurantHref: item.restaurantHref || restaurant.href || window.location.pathname.split('/').pop(),
        name: item.name,
        price: item.price,
        image: item.image,
        description: item.description || '',
        foodType: item.foodType || '',
        tagText: item.foodType === 'nonveg' ? 'Non-Veg' : (item.foodType === 'veg' ? 'Veg' : '')
      };
      const isFavorite = favoritesApi?.isItemFavorite(favoriteItem);

      return `
        <article
          class="food-big-card"
          data-menu-item-id="${escapeHtml(item.id || '')}"
          data-item-slug="${escapeHtml(item.slug || '')}"
          data-category="${escapeHtml(tags.join(','))}"
          data-favorite-item='${escapeHtml(JSON.stringify(favoriteItem))}'
        >
          <div class="food-image-wrap">
            ${item.image ? `<img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">` : ''}
          </div>
          <div class="food-card-body">
            <div class="food-card-top">
              <div class="food-card-title-wrap">
                ${tagMarkup}
                <h3>${escapeHtml(item.name)}</h3>
              </div>
              <button class="food-favorite-btn ${isFavorite ? 'active' : ''}" type="button" aria-label="${isFavorite ? 'Remove from favorites' : 'Save to favorites'}">
                ${getHeartIcon()}
              </button>
            </div>
            <p class="food-price">${formatPrice(item.price)}</p>
            <p class="food-desc">${escapeHtml(item.description || '')}</p>
            <button class="food-btn">Add</button>
          </div>
        </article>
      `;
    }).join('');
  }

  function getCardData(card) {
    const title = card.querySelector('h3')?.textContent.trim() || 'item';
    const priceText = card.querySelector('.food-price')?.textContent.trim() || '\u20B90';
    const price = Number(priceText.replace(/[^\d.]/g, '')) || 0;
    const restaurant = document.querySelector('.restaurant-hero-left h1')?.textContent.trim() || document.title.replace(' - Cibo', '');
    const restaurantPage = window.location.pathname.split('/').pop() || 'menu.php';
    const restaurantSlug = getCurrentRestaurantSlug();
    const restaurants = readJSON(RESTAURANTS_KEY, []);
    const safeRestaurants = Array.isArray(restaurants) ? restaurants : [];
    const currentRestaurant = safeRestaurants.find((item) => slugify(item.slug || item.name) === restaurantSlug) || getDefaultRestaurantData();
    const description = card.querySelector('.food-desc')?.textContent.trim() || '';
    const image = card.querySelector('.food-image-wrap img');
    const tag = card.querySelector('.food-tag');
    const id = String(card.dataset.menuItemId || `${slugify(restaurant)}-${slugify(title)}-${price}`).trim();
    const itemSlug = String(card.dataset.itemSlug || slugify(title)).trim();

    return {
      id,
      menuItemId: Number(String(id).replace(/[^\d]/g, '')) || 0,
      name: title,
      price,
      quantity: 0,
      restaurant,
      restaurantId: currentRestaurant.id || '',
      restaurantSlug,
      restaurantPage: currentRestaurant.href || restaurantPage,
      slug: itemSlug || slugify(title),
      description,
      image: image?.getAttribute('src') || '',
      imageAlt: image?.getAttribute('alt') || title,
      tagText: tag?.textContent.trim() || '',
      tagClass: tag?.classList.contains('veg') ? 'veg' : tag?.classList.contains('nonveg') ? 'nonveg' : ''
    };
  }

  function getQty(itemId) {
    return Number(cart[itemId]?.quantity || 0);
  }

  function getTotalCartItems(cartState = cart) {
    return Object.values(cartState).reduce((total, item) => total + (Number(item?.quantity) || 0), 0);
  }

  function updateQty(itemData, nextQty) {
    if (!cartManager) {
      return {
        ok: false,
        reason: 'error',
        message: 'Cart is unavailable right now.'
      };
    }

    return normalizeCartMutationResult(cartManager.updateQuantity(itemData.id, nextQty, itemData, {
      source: 'menu-update-quantity'
    }));
  }

  function addToCart(itemData) {
    if (!cartManager) {
      return {
        ok: false,
        reason: 'error',
        message: 'Cart is unavailable right now.'
      };
    }

    return normalizeCartMutationResult(cartManager.addItem(itemData, {
      source: 'menu-add-item'
    }));
  }

  function ensureRestaurantRestrictionModal() {
    if (restaurantModal) {
      return restaurantModal;
    }

    const overlay = document.createElement('div');
    overlay.className = 'cart-guard-modal-overlay';
    overlay.setAttribute('hidden', '');
    overlay.innerHTML = `
      <section class="cart-guard-modal" role="dialog" aria-modal="true" aria-labelledby="cart-guard-modal-title" aria-describedby="cart-guard-modal-message">
        <button class="cart-guard-modal-close" type="button" data-modal-action="cancel" aria-label="Close restaurant restriction dialog">&times;</button>
        <div class="cart-guard-modal-badge">Single restaurant cart</div>
        <h2 class="cart-guard-modal-title" id="cart-guard-modal-title">Different Restaurant Detected</h2>
        <p class="cart-guard-modal-message" id="cart-guard-modal-message"></p>
        <p class="cart-guard-modal-hint"></p>
        <div class="cart-guard-modal-actions">
          <button class="secondary-btn cart-guard-modal-cancel" type="button" data-modal-action="cancel">Cancel</button>
          <button class="primary-btn cart-guard-modal-confirm" type="button" data-modal-action="confirm">Replace Cart</button>
        </div>
      </section>
    `;

    document.body.appendChild(overlay);

    restaurantModal = overlay;
    restaurantModalTitle = overlay.querySelector('.cart-guard-modal-title');
    restaurantModalMessage = overlay.querySelector('.cart-guard-modal-message');
    restaurantModalHint = overlay.querySelector('.cart-guard-modal-hint');
    restaurantModalCancelButton = overlay.querySelector('.cart-guard-modal-cancel');
    restaurantModalConfirmButton = overlay.querySelector('.cart-guard-modal-confirm');

    overlay.addEventListener('click', (event) => {
      if (event.target === overlay) {
        closeRestaurantRestrictionModal();
      }
    });

    overlay.querySelectorAll('[data-modal-action="cancel"]').forEach((button) => {
      button.addEventListener('click', () => {
        closeRestaurantRestrictionModal();
      });
    });

    restaurantModalConfirmButton?.addEventListener('click', () => {
      if (!pendingRestaurantSwap?.itemData) {
        closeRestaurantRestrictionModal();
        return;
      }

      const result = normalizeCartMutationResult(cartManager?.clearAndAdd(pendingRestaurantSwap.itemData, {
        source: 'menu-replace-cart'
      }));
      closeRestaurantRestrictionModal();

      if (result?.ok) {
        refreshCardStates();
        return;
      }

      if (result?.message && pendingRestaurantSwap.card) {
        showCardMessage(pendingRestaurantSwap.card, result.message);
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && restaurantModal && !restaurantModal.hasAttribute('hidden')) {
        closeRestaurantRestrictionModal();
      }
    });

    return overlay;
  }

  function openRestaurantRestrictionModal(details) {
    const overlay = ensureRestaurantRestrictionModal();
    pendingRestaurantSwap = details;
    lastModalTrigger = details?.trigger instanceof HTMLElement ? details.trigger : document.activeElement;

    if (restaurantModalTitle) {
      restaurantModalTitle.textContent = 'Different Restaurant Detected';
    }

    if (restaurantModalMessage) {
      restaurantModalMessage.textContent = `You already have items from ${details.currentRestaurantName} in your cart. You can only order from one restaurant at a time.`;
    }

    if (restaurantModalHint) {
      restaurantModalHint.textContent = `To add items from ${details.nextRestaurantName}, clear your current cart first.`;
    }

    overlay.removeAttribute('hidden');
    window.requestAnimationFrame(() => {
      overlay.classList.add('is-visible');
    });
    document.body.classList.add('cart-guard-modal-open');
    restaurantModalConfirmButton?.focus();
  }

  function closeRestaurantRestrictionModal() {
    if (!restaurantModal) {
      return;
    }

    restaurantModal.classList.remove('is-visible');
    document.body.classList.remove('cart-guard-modal-open');

    const triggerToRestore = lastModalTrigger;
    pendingRestaurantSwap = null;
    lastModalTrigger = null;

    window.setTimeout(() => {
      if (restaurantModal && !restaurantModal.classList.contains('is-visible')) {
        restaurantModal.setAttribute('hidden', '');
      }
    }, 220);

    if (triggerToRestore instanceof HTMLElement) {
      triggerToRestore.focus();
    }
  }

  function createAddButton() {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'food-btn';
    button.dataset.action = 'add';
    button.textContent = 'Add';
    return button;
  }

  function createQtyControl(quantity) {
    const control = document.createElement('div');
    control.className = 'food-qty-control';
    control.innerHTML = `
      <button type="button" class="food-qty-btn" data-action="decrease" aria-label="Decrease quantity">-</button>
      <span class="food-qty-count" aria-live="polite">${quantity}</span>
      <button type="button" class="food-qty-btn" data-action="increase" aria-label="Increase quantity">+</button>
    `;
    return control;
  }

  function showCardMessage(card, messageText) {
    const actionHost = card?.querySelector(actionSelector);

    if (!actionHost || !messageText) {
      return;
    }

    let message = actionHost.querySelector('.cart-limit-message');

    if (!message) {
      message = document.createElement('div');
      message.className = 'cart-limit-message';
      message.setAttribute('role', 'status');
      message.setAttribute('aria-live', 'polite');
      message.style.opacity = '0';
      message.style.transform = 'translateY(-2px)';
      message.style.transition = 'opacity 0.18s ease, transform 0.18s ease';
      actionHost.appendChild(message);
    }

    message.textContent = messageText;
    message.style.opacity = '1';
    message.style.transform = 'translateY(0)';

    if (actionHost._cardMessageTimer) {
      window.clearTimeout(actionHost._cardMessageTimer);
    }

    if (actionHost._cardMessageRemoveTimer) {
      window.clearTimeout(actionHost._cardMessageRemoveTimer);
    }

    actionHost._cardMessageTimer = window.setTimeout(() => {
      message.style.opacity = '0';
      message.style.transform = 'translateY(-2px)';

      actionHost._cardMessageRemoveTimer = window.setTimeout(() => {
        message.remove();
        actionHost._cardMessageTimer = null;
        actionHost._cardMessageRemoveTimer = null;
      }, 180);
    }, 1800);
  }

  function renderCard(card) {
    const itemData = getCardData(card);
    const quantity = getQty(itemData.id);
    const actionHost = card.querySelector(actionSelector);

    if (!actionHost) {
      return;
    }

    actionHost.innerHTML = '';

    if (quantity > 0) {
      actionHost.appendChild(createQtyControl(quantity));
      return;
    }

    actionHost.appendChild(createAddButton());
  }

  function refreshCardStates() {
    document.querySelectorAll(cardSelector).forEach((card) => {
      renderCard(card);
    });
  }

  function upgradeCards() {
    document.querySelectorAll(cardSelector).forEach((card) => {
      const existingButton = card.querySelector('.food-btn');

      if (!existingButton) {
        return;
      }

      if (!existingButton.parentElement.classList.contains('food-action')) {
        const actionHost = document.createElement('div');
        actionHost.className = 'food-action';
        existingButton.replaceWith(actionHost);
      } else {
        existingButton.parentElement.innerHTML = '';
      }

      renderCard(card);
    });
  }

  function normalizeValue(value) {
    return String(value || '')
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function getChipFilter(button) {
    return normalizeValue(button.dataset.filter || button.textContent);
  }

  function getCardCategories(card) {
    return String(card.dataset.category || '')
      .split(',')
      .map((value) => normalizeValue(value))
      .filter(Boolean);
  }

  function setupMenuFilters() {
    const chips = Array.from(document.querySelectorAll(chipSelector));
    const cards = Array.from(document.querySelectorAll(cardSelector));

    if (!chips.length || !cards.length) {
      return;
    }

    let activeFilter = getChipFilter(chips.find((chip) => chip.classList.contains('active')) || chips[0]) || 'all';

    chips.forEach((chip) => {
      if (!chip.dataset.filter) {
        chip.dataset.filter = getChipFilter(chip);
      }
    });

    function setActiveChip(filterValue) {
      chips.forEach((chip) => {
        chip.classList.toggle('active', getChipFilter(chip) === filterValue);
      });
    }

    function cardMatchesFilter(card, filterValue) {
      if (filterValue === 'all') {
        return true;
      }

      return getCardCategories(card).includes(filterValue);
    }

    function applyMenuView() {
      cards.forEach((card, index) => {
        const cardId = card.dataset.menuItemId || `menu-item-${index}`;
        card.dataset.menuItemId = cardId;

        const matchesFilter = cardMatchesFilter(card, activeFilter);
        card.style.display = matchesFilter ? '' : 'none';
      });
    }

    chips.forEach((chip) => {
      chip.addEventListener('click', () => {
        activeFilter = getChipFilter(chip) || 'all';
        setActiveChip(activeFilter);
        applyMenuView();
      });
    });

    setActiveChip(activeFilter);
    applyMenuView();
  }

  document.addEventListener('click', (event) => {
    const favoriteButton = event.target.closest('.food-favorite-btn');

    if (favoriteButton) {
      event.preventDefault();
      event.stopPropagation();

      if (!favoritesApi) {
        return;
      }

      const card = favoriteButton.closest(cardSelector);

      if (!card) {
        return;
      }

      const favoriteData = JSON.parse(card.dataset.favoriteItem || '{}');
      favoritesApi.toggleItem(favoriteData);
      const isFavorite = favoritesApi.isItemFavorite(favoriteData);
      favoriteButton.classList.toggle('active', isFavorite);
      favoriteButton.setAttribute('aria-label', isFavorite ? 'Remove from favorites' : 'Save to favorites');
      return;
    }

    const trigger = event.target.closest('[data-action]');

    if (!trigger) {
      return;
    }

    const card = trigger.closest(cardSelector);

    if (!card) {
      return;
    }

    const itemData = getCardData(card);
    let result = null;

    if (trigger.dataset.action === 'add' || trigger.dataset.action === 'increase') {
      result = addToCart(itemData);
    }

    if (trigger.dataset.action === 'decrease') {
      result = updateQty(itemData, getQty(itemData.id) - 1);
    }

    if (result?.ok) {
      refreshCardStates();
      return;
    }

    if (result?.reason === 'restaurant_conflict' && result.conflict) {
      openRestaurantRestrictionModal({
        ...result.conflict,
        itemData: result.conflict.itemData || itemData,
        card,
        trigger
      });
      return;
    }

    if (result?.message) {
      showCardMessage(card, result.message);
    }
  });

  window.addEventListener('cibo-cart-updated', () => {
    syncLocalCart();
    refreshCardStates();
  });

  window.addEventListener('cibo-favorites-updated', () => {
    document.querySelectorAll(cardSelector).forEach((card) => {
      const favoriteData = JSON.parse(card.dataset.favoriteItem || '{}');
      const favoriteButton = card.querySelector('.food-favorite-btn');

      if (!favoriteButton || !favoriteData.id || !favoritesApi) {
        return;
      }

      const isFavorite = favoritesApi.isItemFavorite(favoriteData);
      favoriteButton.classList.toggle('active', isFavorite);
      favoriteButton.setAttribute('aria-label', isFavorite ? 'Remove from favorites' : 'Save to favorites');
    });
  });

  Promise.resolve()
    .then(fetchCanonicalCatalog)
    .catch(() => null)
    .finally(() => {
      renderDynamicMenu();
      upgradeCards();
      setupMenuFilters();
    });
})();
