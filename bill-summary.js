(() => {
  const SUMMARY_KEY = 'cibo_summary';
  const PROMO_KEY = 'cibo_promo';
  const ORDER_CONTEXT_KEY = 'cibo_order_context';
  const RESTAURANTS_KEY = 'restaurants';
  const MENU_ITEMS_KEY = 'menuItems';
  const GST_RATE = 0.05;
  const cartManager = window.CiboCartManager;
  let isFirstTimeCustomerCache = true;

  function readJSON(key, fallback) {
    try {
      const rawValue = localStorage.getItem(key);
      return rawValue ? JSON.parse(rawValue) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function writeJSON(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function slugify(value) {
    return String(value || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function normalizeSummary(summary) {
    return {
      subtotal: Math.round((Number(summary?.subtotal) || 0) * 100) / 100,
      delivery_fee: Math.round((Number(summary?.delivery_fee ?? summary?.delivery) || 0) * 100) / 100,
      tax_amount: Math.round((Number(summary?.tax_amount ?? summary?.tax) || 0) * 100) / 100,
      discount_amount: Math.round((Number(summary?.discount_amount ?? summary?.discount) || 0) * 100) / 100,
      total_amount: Math.round((Number(summary?.total_amount ?? summary?.total) || 0) * 100) / 100,
      discount_type: String(summary?.discount_type ?? summary?.discountType ?? 'none'),
      discount_label: String(summary?.discount_label ?? summary?.discountLabel ?? 'Discount'),
      promo_code: normalizePromoCode(summary?.promo_code ?? summary?.promoCode ?? ''),
      promo_status: String(summary?.promo_status ?? summary?.promoStatus ?? 'none'),
      promo_message: String(summary?.promo_message ?? summary?.promoMessage ?? ''),
      promo_applied: Boolean(summary?.promo_applied ?? summary?.promoApplied),
      is_free_delivery: Boolean(summary?.is_free_delivery ?? summary?.isFreeDelivery),
      tax_label: String(summary?.tax_label ?? summary?.taxLabel ?? 'GST (5%)')
    };
  }

  function normalizePromoCode(code) {
    return String(code || '').trim().toUpperCase();
  }

  function getAutoDiscountRate(subtotal) {
    if (subtotal >= 2000) {
      return 0.10;
    }

    if (subtotal >= 1000) {
      return 0.05;
    }

    return 0;
  }

  function buildAutoDiscount(subtotal) {
    const discountRate = getAutoDiscountRate(subtotal);
    const discountAmount = Math.round(subtotal * discountRate * 100) / 100;

    return {
      discountType: discountAmount > 0 ? 'auto' : 'none',
      discountLabel: discountAmount > 0
        ? `Auto Discount (${Math.round(discountRate * 100)}%)`
        : 'Discount',
      discountAmount,
      promoStatus: 'none',
      promoMessage: '',
      promoApplied: false
    };
  }

  function readPromoState() {
    const promoState = readJSON(PROMO_KEY, null);

    if (!promoState || typeof promoState !== 'object') {
      return {
        code: '',
        status: 'none',
        message: '',
        applied: false
      };
    }

    return {
      code: normalizePromoCode(promoState.code),
      status: String(promoState.status || 'none'),
      message: String(promoState.message || ''),
      applied: Boolean(promoState.applied)
    };
  }

  function isFirstTimeUser() {
    return isFirstTimeCustomerCache;
  }

  function getSummaryRequestPayload(cartItems, promoState) {
    const safeCartItems = Array.isArray(cartItems) ? cartItems : getCartItems();
    const safePromoState = promoState && typeof promoState === 'object'
      ? promoState
      : readPromoState();

    return {
      action: 'summary',
      promo_code: normalizePromoCode(safePromoState.code),
      restaurant: {
        id: safeCartItems[0]?.restaurantId || '',
        name: safeCartItems[0]?.restaurant || 'Cibo Order',
        slug: safeCartItems[0]?.restaurantSlug || '',
        page: safeCartItems[0]?.restaurantPage || ''
      },
      items: safeCartItems.map((item) => ({
        id: item?.id || '',
        name: item?.name || '',
        restaurantId: item?.restaurantId || safeCartItems[0]?.restaurantId || '',
        slug: item?.slug || item?.itemSlug || '',
        price: Number(item?.price) || 0,
        quantity: Number(item?.quantity) || 0,
        restaurant: item?.restaurant || safeCartItems[0]?.restaurant || 'Cibo Order',
        restaurantSlug: item?.restaurantSlug || safeCartItems[0]?.restaurantSlug || '',
        restaurantPage: item?.restaurantPage || safeCartItems[0]?.restaurantPage || ''
      }))
    };
  }

  async function hydrateOrderContext() {
    const cachedContext = readJSON(ORDER_CONTEXT_KEY, null);

    if (cachedContext && typeof cachedContext === 'object' && typeof cachedContext.isFirstTimeUser === 'boolean') {
      isFirstTimeCustomerCache = cachedContext.isFirstTimeUser;
    }

    try {
      const response = await fetch('api/orders.php', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json'
        }
      });
      const data = await response.json().catch(() => ({}));

      if (!response.ok || data.success === false) {
        return;
      }

      const orders = Array.isArray(data.orders) ? data.orders : [];
      const isFirstTimeCustomer = typeof data?.order_context?.is_first_time_customer === 'boolean'
        ? data.order_context.is_first_time_customer
        : orders.length === 0;
      isFirstTimeCustomerCache = isFirstTimeCustomer;
      writeJSON(ORDER_CONTEXT_KEY, {
        isFirstTimeUser: isFirstTimeCustomerCache,
        orderCount: orders.length,
        updatedAt: Date.now()
      });
      window.dispatchEvent(new CustomEvent('cibo-bill-summary-updated', {
        detail: {
          isFirstTimeUser: isFirstTimeCustomerCache,
          orderCount: orders.length
        }
      }));
    } catch (error) {
      // Keep the cached/default context when the session order snapshot cannot be loaded.
    }
  }

  function getCartItems() {
    return cartManager ? cartManager.getItems() : [];
  }

  function saveCartItems(cartItems) {
    if (!cartManager) {
      return [];
    }

    const result = cartManager.setCart(Array.isArray(cartItems) ? cartItems : [], {
      source: 'bill-summary'
    });

    return result.status === 'ok' ? result.items : cartManager.getItems();
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
      throw new Error(data.message || 'Unable to load the latest menu data.');
    }

    writeJSON(RESTAURANTS_KEY, Array.isArray(data.restaurants) ? data.restaurants : []);
    writeJSON(MENU_ITEMS_KEY, Array.isArray(data.menu_items) ? data.menu_items : []);

    return {
      restaurants: Array.isArray(data.restaurants) ? data.restaurants : [],
      menuItems: Array.isArray(data.menu_items) ? data.menu_items : []
    };
  }

  function findCanonicalRestaurant(restaurants, cartItems) {
    const firstItem = Array.isArray(cartItems) ? cartItems[0] : null;
    const restaurantId = Number(firstItem?.restaurantId || 0);
    const restaurantSlug = slugify(firstItem?.restaurantSlug || '');
    const restaurantName = slugify(firstItem?.restaurant || firstItem?.restaurantName || '');

    return restaurants.find((restaurant) => Number(restaurant?.id || 0) === restaurantId)
      || restaurants.find((restaurant) => slugify(restaurant?.slug || restaurant?.name) === restaurantSlug)
      || restaurants.find((restaurant) => slugify(restaurant?.name) === restaurantName)
      || null;
  }

  function reconcileCartItemsWithCatalog(cartItems, catalog) {
    const restaurants = Array.isArray(catalog?.restaurants) ? catalog.restaurants : [];
    const menuItems = Array.isArray(catalog?.menuItems) ? catalog.menuItems : [];
    const restaurant = findCanonicalRestaurant(restaurants, cartItems);

    if (!restaurant) {
      return {
        changed: Array.isArray(cartItems) && cartItems.length > 0,
        removedCount: Array.isArray(cartItems) ? cartItems.length : 0,
        cartItems: []
      };
    }

    const restaurantSlug = slugify(restaurant.slug || restaurant.name);
    const restaurantMenuItems = menuItems.filter((item) => {
      return Number(item?.restaurantId || 0) === Number(restaurant.id || 0)
        || slugify(item?.restaurantSlug || item?.restaurantName) === restaurantSlug;
    });

    let changed = false;
    let removedCount = 0;
    const nextItems = [];

    (Array.isArray(cartItems) ? cartItems : []).forEach((item, index) => {
      const itemSlug = slugify(item?.slug || item?.itemSlug || item?.name || '');
      const itemName = slugify(item?.name || '');
      const menuItemId = Number(item?.menuItemId || String(item?.id || '').replace(/[^\d]/g, '')) || 0;

      const matchedItem = restaurantMenuItems.find((menuItem) => Number(menuItem?.menuItemId || 0) === menuItemId)
        || restaurantMenuItems.find((menuItem) => slugify(menuItem?.slug || menuItem?.name) === itemSlug)
        || restaurantMenuItems.find((menuItem) => slugify(menuItem?.name) === itemName);

      if (!matchedItem) {
        changed = true;
        removedCount += 1;
        return;
      }

      const normalizedItem = {
        ...item,
        ...matchedItem,
        id: String(matchedItem.id || ('menu-item-' + index)),
        restaurant: matchedItem.restaurantName || restaurant.name || item?.restaurant || 'Cibo Order',
        restaurantId: matchedItem.restaurantId || restaurant.id || item?.restaurantId || '',
        restaurantSlug: matchedItem.restaurantSlug || restaurant.slug || item?.restaurantSlug || '',
        restaurantPage: matchedItem.restaurantHref || ('menu.php?restaurant=' + encodeURIComponent(restaurant.slug || restaurant.name)),
        quantity: Number(item?.quantity) || 0,
        imageAlt: matchedItem.name || item?.name || 'Item'
      };

      if (
        Number(normalizedItem.price) !== Number(item?.price || 0)
        || String(normalizedItem.name || '') !== String(item?.name || '')
        || String(normalizedItem.id || '') !== String(item?.id || '')
      ) {
        changed = true;
      }

      nextItems.push(normalizedItem);
    });

    return {
      changed,
      removedCount,
      cartItems: nextItems
    };
  }

  function resolveDiscount(subtotal, promoCode) {
    const normalizedPromoCode = normalizePromoCode(promoCode);
    const autoDiscount = buildAutoDiscount(subtotal);

    if (normalizedPromoCode) {
      if (normalizedPromoCode === 'CIBO50') {
        if (!isFirstTimeUser()) {
          return {
            ...autoDiscount,
            promoStatus: 'ineligible',
            promoMessage: 'Only for first-time users',
            promoApplied: false
          };
        }

        return {
          discountType: 'promo',
          discountLabel: 'Promo Discount',
          discountAmount: Math.min(50, subtotal),
          promoStatus: 'applied',
          promoMessage: 'Promo Applied: CIBO50',
          promoApplied: true
        };
      }

      if (normalizedPromoCode === 'CIBO100') {
        if (subtotal <= 500) {
          return {
            ...autoDiscount,
            promoStatus: 'ineligible',
            promoMessage: 'Order must be above Rs500',
            promoApplied: false
          };
        }

        return {
          discountType: 'promo',
          discountLabel: 'Promo Discount',
          discountAmount: Math.min(100, subtotal),
          promoStatus: 'applied',
          promoMessage: 'Promo Applied: CIBO100',
          promoApplied: true
        };
      }

      if (normalizedPromoCode === 'CIBO5') {
        if (subtotal <= 1000) {
          return {
            ...autoDiscount,
            promoStatus: 'ineligible',
            promoMessage: 'Order must be above Rs1000',
            promoApplied: false
          };
        }

        return {
          discountType: 'promo',
          discountLabel: 'Promo Discount',
          discountAmount: Math.round(subtotal * 0.05 * 100) / 100,
          promoStatus: 'applied',
          promoMessage: 'Promo Applied: CIBO5',
          promoApplied: true
        };
      }

      if (normalizedPromoCode === 'CIBO10') {
        if (subtotal <= 2000) {
          return {
            ...autoDiscount,
            promoStatus: 'ineligible',
            promoMessage: 'Order must be above Rs2000',
            promoApplied: false
          };
        }

        return {
          discountType: 'promo',
          discountLabel: 'Promo Discount',
          discountAmount: Math.round(subtotal * 0.1 * 100) / 100,
          promoStatus: 'applied',
          promoMessage: 'Promo Applied: CIBO10',
          promoApplied: true
        };
      }

      return {
        ...autoDiscount,
        promoStatus: 'invalid',
        promoMessage: 'Invalid promo code',
        promoApplied: false
      };
    }

    return autoDiscount;
  }

  function persistSummary(summary) {
    writeJSON(SUMMARY_KEY, {
      subtotal: summary.subtotal,
      delivery: summary.delivery_fee,
      delivery_fee: summary.delivery_fee,
      discount: summary.discount_amount,
      discount_amount: summary.discount_amount,
      total: summary.total_amount,
      total_amount: summary.total_amount,
      tax: summary.tax_amount,
      tax_amount: summary.tax_amount,
      taxLabel: summary.tax_label,
      charges: 0,
      discountType: summary.discount_type,
      discountLabel: summary.discount_label,
      promoCode: summary.promo_code,
      promoStatus: summary.promo_status,
      promoMessage: summary.promo_message,
      promoApplied: summary.promo_applied,
      isFreeDelivery: summary.is_free_delivery
    });

    if (summary.promo_code) {
      writeJSON(PROMO_KEY, {
        code: summary.promo_code,
        status: summary.promo_status,
        message: summary.promo_message,
        applied: summary.promo_applied
      });
      return;
    }

    localStorage.removeItem(PROMO_KEY);
  }

  async function refreshBillSummary(options = {}) {
    const cartItems = Array.isArray(options.cartItems) ? options.cartItems : getCartItems();
    const promoState = options.promoState && typeof options.promoState === 'object'
      ? options.promoState
      : readPromoState();

    if (!cartItems.length) {
      const emptySummary = calculateBillSummary({
        cartItems,
        promoState,
        persist: options.persist !== false
      });
      return emptySummary;
    }

    try {
      const response = await fetch('api/orders.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify(getSummaryRequestPayload(cartItems, promoState))
      });
      const data = await response.json().catch(() => ({}));

      if (!response.ok || data.success === false || !data.summary || typeof data.summary !== 'object') {
        throw new Error(data.message || 'Unable to refresh the latest order summary.');
      }

      if (Array.isArray(data.cart_items) && data.cart_items.length) {
        saveCartItems(data.cart_items);
      }

      const summary = normalizeSummary(data.summary);

      if (options.persist !== false) {
        persistSummary(summary);
      }

      window.dispatchEvent(new CustomEvent('cibo-bill-summary-updated', {
        detail: {
          source: 'backend-summary',
          summary
        }
      }));

      return summary;
    } catch (error) {
      try {
        const catalog = await fetchCanonicalCatalog();
        const reconciliation = reconcileCartItemsWithCatalog(cartItems, catalog);

        if (reconciliation.changed) {
          const updatedItems = saveCartItems(reconciliation.cartItems);

          window.dispatchEvent(new CustomEvent('cibo-cart-reconciled', {
            detail: {
              removedCount: reconciliation.removedCount,
              itemCount: updatedItems.length
            }
          }));

          if (!updatedItems.length) {
            throw new Error('Your cart was updated because some menu items are no longer available.');
          }

          return refreshBillSummary({
            ...options,
            cartItems: updatedItems,
            promoState
          });
        }
      } catch (reconciliationError) {
        window.dispatchEvent(new CustomEvent('cibo-bill-summary-error', {
          detail: {
            message: reconciliationError instanceof Error
              ? reconciliationError.message
              : (error instanceof Error ? error.message : 'Unable to refresh the latest order summary.')
          }
        }));
        throw reconciliationError;
      }

      window.dispatchEvent(new CustomEvent('cibo-bill-summary-error', {
        detail: {
          message: error instanceof Error ? error.message : 'Unable to refresh the latest order summary.'
        }
      }));
      throw error;
    }
  }

  function calculateBillSummary(options = {}) {
    const cartItems = Array.isArray(options.cartItems) ? options.cartItems : getCartItems();
    const promoState = options.promoState && typeof options.promoState === 'object'
      ? options.promoState
      : readPromoState();
    const promoCode = normalizePromoCode(promoState.code);

    const subtotal = cartItems.reduce((total, item) => {
      const price = Number(item?.price) || 0;
      const quantity = Number(item?.quantity) || 0;
      return total + (price * quantity);
    }, 0);

    const deliveryFee = subtotal <= 0 ? 0 : (subtotal >= 199 ? 0 : 40);
    const discount = resolveDiscount(subtotal, promoCode);
    const taxableAmount = Math.max(0, subtotal - discount.discountAmount);
    const taxAmount = Math.round(taxableAmount * GST_RATE * 100) / 100;
    const totalAmount = Math.max(0, taxableAmount + taxAmount + deliveryFee);

    const summary = {
      subtotal: Math.round(subtotal * 100) / 100,
      delivery_fee: Math.round(deliveryFee * 100) / 100,
      tax_amount: taxAmount,
      discount_amount: Math.round(discount.discountAmount * 100) / 100,
      total_amount: Math.round(totalAmount * 100) / 100,
      discount_type: discount.discountType,
      discount_label: discount.discountLabel,
      promo_code: promoCode,
      promo_status: discount.promoStatus,
      promo_message: discount.promoMessage,
      promo_applied: discount.promoApplied,
      is_free_delivery: deliveryFee === 0 && subtotal > 0,
      tax_label: 'GST (5%)'
    };

    if (options.persist !== false) {
      persistSummary(summary);
    }

    return summary;
  }

  const ready = hydrateOrderContext();

  window.CiboBillSummary = {
    calculateBillSummary,
    refreshBillSummary,
    getCartItems,
    normalizePromoCode,
    readPromoState,
    ready
  };
})();
