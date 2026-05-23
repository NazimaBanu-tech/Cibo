(() => {
  const STORAGE_KEY = 'cibo_cart';
  const LEGACY_KEYS = ['ciboCart', 'cart'];
  const SNAPSHOT_KEY = 'cibo_checkout_snapshot';
  const MAX_ITEM_QUANTITY = 5;
  const MAX_ORDER_ITEMS = 15;
  const ITEM_LIMIT_MESSAGE = 'Max 5 per item reached';
  const ORDER_LIMIT_MESSAGE = `Order limit reached (Max ${MAX_ORDER_ITEMS} items per order)`;
  let cartCache = null;

  function readJSON(storage, key, fallback) {
    try {
      const rawValue = storage.getItem(key);
      return rawValue ? JSON.parse(rawValue) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function writeJSON(storage, key, value) {
    storage.setItem(key, JSON.stringify(value));
  }

  function slugify(value) {
    return String(value || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function clone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  function toRestaurantMeta(item) {
    return {
      id: String(item?.restaurantId || '').trim(),
      slug: slugify(item?.restaurantSlug || item?.restaurantName || item?.restaurant || ''),
      name: String(item?.restaurant || item?.restaurantName || 'Restaurant').trim() || 'Restaurant'
    };
  }

  function isSameRestaurant(source, target) {
    const sourceRestaurant = toRestaurantMeta(source);
    const targetRestaurant = toRestaurantMeta(target);

    if (sourceRestaurant.id && targetRestaurant.id) {
      return sourceRestaurant.id === targetRestaurant.id;
    }

    if (sourceRestaurant.slug && targetRestaurant.slug) {
      return sourceRestaurant.slug === targetRestaurant.slug;
    }

    return sourceRestaurant.name.toLowerCase() === targetRestaurant.name.toLowerCase();
  }

  function normalizeItem(item, fallbackKey) {
    if (!item || typeof item !== 'object') {
      return null;
    }

    const key = String(item.id || item.menuItemId || item.slug || fallbackKey || '').trim();
    const quantity = Number(item.quantity) || 0;

    if (!key) {
      return null;
    }

    return {
      ...item,
      id: key,
      quantity
    };
  }

  function buildCartObject(input) {
    if (Array.isArray(input)) {
      return input.reduce((cart, item, index) => {
        const normalizedItem = normalizeItem(item, `item-${index}`);

        if (!normalizedItem) {
          return cart;
        }

        cart[normalizedItem.id] = normalizedItem;
        return cart;
      }, {});
    }

    if (input && typeof input === 'object') {
      return Object.keys(input).reduce((cart, key) => {
        const normalizedItem = normalizeItem(input[key], key);

        if (!normalizedItem) {
          return cart;
        }

        cart[normalizedItem.id] = normalizedItem;
        return cart;
      }, {});
    }

    return {};
  }

  function sanitizeCart(input) {
    const sourceCart = buildCartObject(input);
    const nextCart = {};
    let totalItems = 0;

    Object.keys(sourceCart).forEach((itemId) => {
      const item = sourceCart[itemId];
      let quantity = Number(item?.quantity) || 0;

      if (quantity <= 0) {
        return;
      }

      if (quantity > MAX_ITEM_QUANTITY) {
        quantity = MAX_ITEM_QUANTITY;
      }

      const remainingSlots = MAX_ORDER_ITEMS - totalItems;

      if (remainingSlots <= 0) {
        return;
      }

      if (quantity > remainingSlots) {
        quantity = remainingSlots;
      }

      totalItems += quantity;
      nextCart[itemId] = {
        ...item,
        id: itemId,
        quantity
      };
    });

    return nextCart;
  }

  function getCartItemsFrom(cartObject) {
    return Object.values(cartObject || {}).filter((item) => Number(item?.quantity) > 0);
  }

  function getConflictDetails(items) {
    const safeItems = Array.isArray(items) ? items : [];
    const firstItem = safeItems[0];

    if (!firstItem) {
      return null;
    }

    const conflictingItem = safeItems.find((item) => !isSameRestaurant(firstItem, item));

    if (!conflictingItem) {
      return null;
    }

    return {
      currentRestaurant: toRestaurantMeta(firstItem),
      newRestaurant: toRestaurantMeta(conflictingItem),
      pendingItem: clone(conflictingItem)
    };
  }

  function dispatchCartUpdated(source) {
    window.dispatchEvent(new CustomEvent('cibo-cart-updated', {
      detail: {
        source: String(source || 'cart-manager'),
        cart: clone(cartCache || {}),
        items: getCartItemsFrom(cartCache || {}),
        count: getCartCount()
      }
    }));
  }

  function persistCart(nextCart, source) {
    cartCache = sanitizeCart(nextCart);
    writeJSON(localStorage, STORAGE_KEY, cartCache);
    dispatchCartUpdated(source);
    return {
      status: 'ok',
      cart: clone(cartCache),
      items: getCartItemsFrom(cartCache),
      count: getCartCount()
    };
  }

  function tryHydrateFromSnapshot() {
    const localSnapshot = readJSON(localStorage, SNAPSHOT_KEY, null);
    const sessionSnapshot = readJSON(sessionStorage, SNAPSHOT_KEY, null);
    const snapshotCart = localSnapshot?.cart && typeof localSnapshot.cart === 'object'
      ? localSnapshot.cart
      : (sessionSnapshot?.cart && typeof sessionSnapshot.cart === 'object' ? sessionSnapshot.cart : null);

    return snapshotCart && typeof snapshotCart === 'object' ? snapshotCart : null;
  }

  function hydrateCart() {
    const storedCart = readJSON(localStorage, STORAGE_KEY, null);

    if (storedCart && typeof storedCart === 'object') {
      cartCache = sanitizeCart(storedCart);
      return cartCache;
    }

    const legacyCart = LEGACY_KEYS
      .map((key) => readJSON(localStorage, key, null))
      .find((value) => value && typeof value === 'object');

    const snapshotCart = tryHydrateFromSnapshot();
    const fallbackCart = legacyCart || snapshotCart;

    if (fallbackCart && typeof fallbackCart === 'object') {
      cartCache = sanitizeCart(fallbackCart);
      writeJSON(localStorage, STORAGE_KEY, cartCache);
      LEGACY_KEYS.forEach((key) => localStorage.removeItem(key));
      return cartCache;
    }

    cartCache = {};
    return cartCache;
  }

  function ensureCart() {
    return cartCache && typeof cartCache === 'object' ? cartCache : hydrateCart();
  }

  function buildConflictResponse(conflict) {
    return {
      status: 'restaurant_conflict',
      currentRestaurant: conflict.currentRestaurant,
      newRestaurant: conflict.newRestaurant,
      pendingItem: conflict.pendingItem
    };
  }

  function getCart() {
    ensureCart();
    return clone(cartCache);
  }

  function getItems() {
    ensureCart();
    return getCartItemsFrom(cartCache).map((item) => ({ ...item }));
  }

  function getCartCount() {
    ensureCart();
    return getCartItemsFrom(cartCache).reduce((total, item) => total + (Number(item?.quantity) || 0), 0);
  }

  function getItem(itemId) {
    ensureCart();
    return cartCache[String(itemId || '').trim()] ? { ...cartCache[String(itemId || '').trim()] } : null;
  }

  function setCart(input, options = {}) {
    ensureCart();
    const nextCart = sanitizeCart(input);
    const conflict = getConflictDetails(getCartItemsFrom(nextCart));

    if (conflict) {
      return buildConflictResponse(conflict);
    }

    return persistCart(nextCart, options.source || 'set-cart');
  }

  function clearCart(options = {}) {
    ensureCart();
    return persistCart({}, options.source || 'clear-cart');
  }

  function removeItem(itemId, options = {}) {
    ensureCart();
    const key = String(itemId || '').trim();

    if (!key || !cartCache[key]) {
      return {
        status: 'ok',
        cart: clone(cartCache),
        items: getCartItemsFrom(cartCache),
        count: getCartCount()
      };
    }

    const nextCart = {
      ...cartCache
    };
    delete nextCart[key];
    return persistCart(nextCart, options.source || 'remove-item');
  }

  function updateQuantity(itemId, nextQuantity, itemData, options = {}) {
    ensureCart();
    const key = String(itemId || itemData?.id || '').trim();
    const existingItem = key ? cartCache[key] : null;
    const candidateItem = normalizeItem({
      ...existingItem,
      ...itemData,
      id: key || itemData?.id || existingItem?.id,
      quantity: Number(nextQuantity) || 0
    }, key);

    if (!candidateItem) {
      return {
        status: 'error',
        message: 'Unable to update the cart item.'
      };
    }

    if (candidateItem.quantity <= 0) {
      return removeItem(candidateItem.id, options);
    }

    if (candidateItem.quantity > MAX_ITEM_QUANTITY) {
      return {
        status: 'item_limit',
        message: ITEM_LIMIT_MESSAGE,
        item: { ...candidateItem, quantity: MAX_ITEM_QUANTITY }
      };
    }

    const currentItems = getCartItemsFrom(cartCache);
    const firstCartItem = currentItems.find((item) => item.id !== candidateItem.id) || currentItems[0] || null;

    if (firstCartItem && !isSameRestaurant(firstCartItem, candidateItem)) {
      return buildConflictResponse({
        currentRestaurant: toRestaurantMeta(firstCartItem),
        newRestaurant: toRestaurantMeta(candidateItem),
        pendingItem: clone(candidateItem)
      });
    }

    const nextCart = {
      ...cartCache,
      [candidateItem.id]: candidateItem
    };

    const nextCount = getCartItemsFrom(nextCart).reduce((total, item) => total + (Number(item?.quantity) || 0), 0);

    if (nextCount > MAX_ORDER_ITEMS) {
      return {
        status: 'order_limit',
        message: ORDER_LIMIT_MESSAGE,
        item: { ...candidateItem }
      };
    }

    return persistCart(nextCart, options.source || 'update-quantity');
  }

  function addItem(itemData, options = {}) {
    ensureCart();
    const normalizedItem = normalizeItem(itemData, itemData?.slug || itemData?.name || 'item');

    if (!normalizedItem) {
      return {
        status: 'error',
        message: 'Unable to add the selected item.'
      };
    }

    const currentQuantity = Number(cartCache[normalizedItem.id]?.quantity || 0);
    return updateQuantity(normalizedItem.id, currentQuantity + 1, normalizedItem, {
      source: options.source || 'add-item'
    });
  }

  function clearAndAdd(itemData, options = {}) {
    ensureCart();
    const clearResult = clearCart({
      source: options.source ? `${options.source}:clear` : 'clear-and-add:clear'
    });

    if (clearResult.status !== 'ok') {
      return clearResult;
    }

    return addItem(itemData, {
      source: options.source ? `${options.source}:add` : 'clear-and-add:add'
    });
  }

  window.addEventListener('storage', (event) => {
    if (![STORAGE_KEY, ...LEGACY_KEYS].includes(event.key || '')) {
      return;
    }

    cartCache = null;
    hydrateCart();
    dispatchCartUpdated('storage');
  });

  hydrateCart();

  window.CiboCartManager = {
    addItem,
    clearAndAdd,
    clearCart,
    getCart,
    getCartCount,
    getItem,
    getItems,
    removeItem,
    setCart,
    updateQuantity
  };
})();
