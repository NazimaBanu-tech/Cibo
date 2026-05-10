(() => {
  const STORAGE_KEY = 'cibo_cart';
  const CART_NOTICE_KEY = 'cibo_cart_notice';
  const SUMMARY_KEY = 'cibo_summary';
  const PROMO_KEY = 'cibo_promo';
  const CHECKOUT_SNAPSHOT_KEY = 'cibo_checkout_snapshot';
  const CHECKOUT_INTENT_KEY = 'cibo_checkout_intent';
  const MAX_ITEM_QUANTITY = 5;
  const MAX_ORDER_ITEMS = 15;
  const ORDER_LIMIT_MESSAGE = `Order limit reached (Max ${MAX_ORDER_ITEMS} items per order)`;
  const DEFAULT_SUMMARY_NOTE = 'Your order is eligible for secure checkout and will be delivered with care.';
  const PROMO_SUGGESTIONS = [
    { code: 'CIBO50', description: '₹50 OFF for first-time users' },
    { code: 'CIBO100', description: '₹100 OFF for orders above ₹500' },
    { code: 'CIBO5', description: '5% OFF for orders above ₹1000' },
    { code: 'CIBO10', description: '10% OFF for orders above ₹2000' }
  ];

  const cartMainCard = document.querySelector('.cart-main-card');
  const cartLeft = document.querySelector('.cart-left');
  const cartHeaderTitle = document.querySelector('.cart-header h1');
  const cartHeaderText = document.querySelector('.cart-header p');
  const summaryRows = Array.from(document.querySelectorAll('.summary-row'));
  const summaryTotal = document.querySelector('.summary-total span:last-child');
  const summaryPromo = document.querySelector('.summary-promo');
  const summaryNote = document.querySelector('.summary-delivery-note');
  const clearCartModal = document.getElementById('clear-cart-modal');
  const cancelClearCartButton = document.getElementById('cancel-clear-cart');
  const confirmClearCartButton = document.getElementById('confirm-clear-cart');

  let cartNoticeTimer = null;
  let promoInput = null;
  let promoSuggestions = null;
  let promoFeedback = null;

  if (!cartMainCard || !cartLeft || !window.CiboBillSummary) {
    return;
  }

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

  function sanitizeCart(cart) {
    const safeCart = cart && typeof cart === 'object' ? cart : {};
    let didChange = false;
    let totalItems = 0;

    Object.keys(safeCart).forEach((itemId) => {
      const item = safeCart[itemId];
      let quantity = Number(item?.quantity) || 0;

      if (quantity <= 0) {
        delete safeCart[itemId];
        didChange = true;
        return;
      }

      if (quantity > MAX_ITEM_QUANTITY) {
        quantity = MAX_ITEM_QUANTITY;
        safeCart[itemId] = {
          ...item,
          quantity
        };
        didChange = true;
      }

      const remainingSlots = MAX_ORDER_ITEMS - totalItems;

      if (remainingSlots <= 0) {
        delete safeCart[itemId];
        didChange = true;
        return;
      }

      if (quantity > remainingSlots) {
        quantity = remainingSlots;
        safeCart[itemId] = {
          ...safeCart[itemId],
          quantity
        };
        didChange = true;
      }

      totalItems += quantity;
    });

    return {
      cart: safeCart,
      didChange
    };
  }

  function readCart() {
    const parsedCart = readJSON(STORAGE_KEY, {});
    const { cart, didChange } = sanitizeCart(parsedCart);

    if (didChange) {
      writeJSON(STORAGE_KEY, cart);
    }

    return cart;
  }

  function saveCart(cart) {
    writeJSON(STORAGE_KEY, sanitizeCart(cart).cart);
    window.dispatchEvent(new Event('cibo-cart-updated'));
  }

  function getCartItems() {
    return window.CiboBillSummary.getCartItems();
  }

  function getTotalCartItems(cart) {
    return Object.values(cart).reduce((total, item) => total + (Number(item?.quantity) || 0), 0);
  }

  function readPromoState() {
    return window.CiboBillSummary.readPromoState();
  }

  function savePromoState(state) {
    const code = window.CiboBillSummary.normalizePromoCode(state?.code);

    if (!code) {
      localStorage.removeItem(PROMO_KEY);
      return;
    }

    writeJSON(PROMO_KEY, {
      code,
      status: String(state?.status || 'pending'),
      message: String(state?.message || ''),
      applied: Boolean(state?.applied)
    });
  }

  function clearPromoState() {
    localStorage.removeItem(PROMO_KEY);
  }

  function formatPrice(amount) {
    return '₹' + (Number(amount) || 0);
  }

  function formatDelivery(amount) {
    return Number(amount) === 0 ? 'FREE' : formatPrice(amount);
  }

  function formatDiscount(amount) {
    return Number(amount) > 0 ? `- ${formatPrice(amount)}` : '₹0';
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function readCartNotice() {
    const notice = readJSON(CART_NOTICE_KEY, null);
    return notice && typeof notice.message === 'string' ? notice : null;
  }

  function setCartNotice(messageText) {
    if (!messageText) {
      return;
    }

    writeJSON(CART_NOTICE_KEY, {
      message: messageText,
      createdAt: Date.now()
    });
    window.dispatchEvent(new Event('cibo-cart-notice-updated'));
  }

  function clearCartNotice() {
    localStorage.removeItem(CART_NOTICE_KEY);
    window.dispatchEvent(new Event('cibo-cart-notice-updated'));
  }

  function scheduleCartNoticeClear() {
    if (cartNoticeTimer) {
      window.clearTimeout(cartNoticeTimer);
    }

    cartNoticeTimer = window.setTimeout(() => {
      clearCartNotice();
      cartNoticeTimer = null;
    }, 2200);
  }

  function setEmptyState() {
    saveCart({});
    localStorage.removeItem(SUMMARY_KEY);
    clearPromoState();
    window.location.href = 'empty-cart.php';
  }

  function toggleClearCartModal(open) {
    if (!clearCartModal) {
      return;
    }

    clearCartModal.classList.toggle('is-open', open);
    clearCartModal.setAttribute('aria-hidden', open ? 'false' : 'true');
  }

  function getPromoSuggestions(query = '') {
    const normalizedQuery = window.CiboBillSummary.normalizePromoCode(query);

    if (!normalizedQuery || normalizedQuery.startsWith('C')) {
      return PROMO_SUGGESTIONS.filter((suggestion) => suggestion.code.includes(normalizedQuery));
    }

    return [];
  }

  function togglePromoSuggestions(visible) {
    if (!promoSuggestions) {
      return;
    }

    promoSuggestions.classList.toggle('is-visible', visible);
  }

  function renderPromoSuggestions(query = '') {
    if (!promoSuggestions) {
      return;
    }

    const suggestions = getPromoSuggestions(query);

    if (!suggestions.length) {
      promoSuggestions.innerHTML = '';
      togglePromoSuggestions(false);
      return;
    }

    promoSuggestions.innerHTML = suggestions.map((suggestion) => `
      <button class="summary-promo-option" type="button" data-promo-code="${suggestion.code}">
        <strong>${suggestion.code}</strong>
        <span>${suggestion.description}</span>
      </button>
    `).join('');

    togglePromoSuggestions(true);
  }

  function updatePromoFeedback(message = '', type = '') {
    if (!promoFeedback) {
      return;
    }

    promoFeedback.textContent = message;
    promoFeedback.className = 'summary-promo-feedback' + (type ? ` is-${type}` : '');
  }

  function initializePromoBox() {
    if (!summaryPromo || summaryPromo.dataset.ready === 'true') {
      return;
    }

    summaryPromo.innerHTML = `
      <label class="summary-promo-label" for="summary-promo-input">Promo Code</label>
      <div class="summary-promo-input">
        <input id="summary-promo-input" type="text" placeholder="Enter promo code" autocomplete="off" spellcheck="false" maxlength="12">
        <button class="apply-btn" type="button" data-promo-apply>Apply</button>
      </div>
      <div class="summary-promo-suggestions" data-promo-suggestions></div>
      <div class="summary-promo-feedback" data-promo-feedback aria-live="polite"></div>
    `;

    promoInput = summaryPromo.querySelector('#summary-promo-input');
    promoSuggestions = summaryPromo.querySelector('[data-promo-suggestions]');
    promoFeedback = summaryPromo.querySelector('[data-promo-feedback]');
    summaryPromo.dataset.ready = 'true';

    promoInput.value = readPromoState().code;

    promoInput.addEventListener('focus', () => {
      renderPromoSuggestions(promoInput.value);
    });

    promoInput.addEventListener('input', () => {
      promoInput.value = window.CiboBillSummary.normalizePromoCode(promoInput.value);
      renderPromoSuggestions(promoInput.value);
      updatePromoFeedback('');
    });

    promoInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        applyPromoCode(promoInput.value);
      }
    });

    summaryPromo.querySelector('[data-promo-apply]')?.addEventListener('click', () => {
      applyPromoCode(promoInput?.value || '');
    });

    promoSuggestions.addEventListener('click', (event) => {
      const button = event.target.closest('[data-promo-code]');

      if (!button) {
        return;
      }

      promoInput.value = window.CiboBillSummary.normalizePromoCode(button.dataset.promoCode);
      applyPromoCode(promoInput.value);
    });

    document.addEventListener('click', (event) => {
      if (!summaryPromo.contains(event.target)) {
        togglePromoSuggestions(false);
      }
    });
  }

  function calculateBillSummary() {
    const summary = window.CiboBillSummary.calculateBillSummary({
      cartItems: getCartItems(),
      promoState: readPromoState(),
      persist: true
    });

    const subtotalRowValue = summaryRows[0]?.querySelector('strong');
    const deliveryRowValue = summaryRows[1]?.querySelector('strong');
    const taxRowLabel = summaryRows[2]?.querySelector('span:first-child');
    const taxRowValue = summaryRows[2]?.querySelector('strong');
    const discountRowLabel = summaryRows[3]?.querySelector('span:first-child');
    const discountRowValue = summaryRows[3]?.querySelector('strong');

    if (subtotalRowValue) {
      subtotalRowValue.textContent = formatPrice(summary.subtotal);
    }

    if (deliveryRowValue) {
      deliveryRowValue.textContent = formatDelivery(summary.delivery_fee);
    }

    if (taxRowLabel) {
      taxRowLabel.textContent = summary.tax_label || 'GST (5%)';
    }

    if (taxRowValue) {
      taxRowValue.textContent = formatPrice(summary.tax_amount);
    }

    if (discountRowLabel) {
      discountRowLabel.textContent = summary.discount_label || 'Discount';
    }

    if (discountRowValue) {
      discountRowValue.textContent = formatDiscount(summary.discount_amount);
    }

    if (summaryTotal) {
      summaryTotal.textContent = formatPrice(summary.total_amount);
    }

    if (promoInput) {
      promoInput.value = summary.promo_code || '';
    }

    if (summary.promo_code) {
      updatePromoFeedback(
        summary.promo_message,
        summary.promo_applied ? 'success' : (summary.promo_message ? 'error' : '')
      );
    } else {
      updatePromoFeedback('');
    }

    if (summaryNote) {
      if (summary.promo_applied && summary.discount_amount > 0) {
        summaryNote.textContent = `${summary.promo_code} applied. You saved ${formatPrice(summary.discount_amount)} on this order.`;
      } else if (summary.discount_type === 'auto' && summary.discount_amount > 0) {
        summaryNote.textContent = `${summary.discount_label} applied. You saved ${formatPrice(summary.discount_amount)} on this order.`;
      } else if (summary.is_free_delivery) {
        summaryNote.textContent = 'Free delivery has been applied to this order.';
      } else {
        summaryNote.textContent = DEFAULT_SUMMARY_NOTE;
      }
    }

    return summary;
  }

  function syncBillSummary() {
    if (!window.CiboBillSummary || typeof window.CiboBillSummary.refreshBillSummary !== 'function') {
      calculateBillSummary();
      return Promise.resolve();
    }

    return Promise.resolve(window.CiboBillSummary.refreshBillSummary({
      cartItems: getCartItems(),
      promoState: readPromoState(),
      persist: true
    }))
      .catch(() => null)
      .finally(() => {
        if (getCartItems().length) {
          calculateBillSummary();
        }
      });
  }

  function applyPromoCode(code) {
    const normalizedCode = window.CiboBillSummary.normalizePromoCode(code);

    if (!normalizedCode) {
      clearPromoState();
      syncBillSummary();
      togglePromoSuggestions(false);
      return;
    }

    savePromoState({
      code: normalizedCode,
      status: 'pending',
      message: '',
      applied: false
    });

    syncBillSummary();
    togglePromoSuggestions(false);
  }

  function saveCheckoutSnapshot() {
    const summary = calculateBillSummary();
    const snapshot = {
      cart: readCart(),
      summary,
      savedAt: Date.now()
    };

    writeJSON(CHECKOUT_SNAPSHOT_KEY, snapshot);
    sessionStorage.setItem(CHECKOUT_SNAPSHOT_KEY, JSON.stringify(snapshot));
  }

  function renderCart() {
    const items = getCartItems();

    if (!items.length) {
      setEmptyState();
      return;
    }

    const restaurantName = items[0].restaurant || 'Cibo Order';
    const itemCount = items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
    const cartNotice = readCartNotice();
    const cartNoticeMarkup = cartNotice
      ? `
        <div class="cart-limit-message" role="status" aria-live="polite" style="margin: 0 0 14px; padding: 10px 12px; border-radius: 14px; background: #fff4e8; color: #b85b13; font-size: 13px; font-weight: 600; line-height: 1.4;">
          ${escapeHtml(cartNotice.message)}
        </div>
      `
      : '';

    if (cartHeaderTitle) {
      cartHeaderTitle.textContent = 'Your Cart';
    }

    if (cartHeaderText) {
      cartHeaderText.textContent = 'Almost there. Review your items and continue to checkout for a smooth Cibo order experience.';
    }

    cartMainCard.innerHTML = `
      ${cartNoticeMarkup}
      <div class="cart-restaurant">
        <div>
          <h3>${escapeHtml(restaurantName)}</h3>
          <p>${itemCount} item${itemCount === 1 ? '' : 's'}  -  Estimated delivery in 25-30 mins</p>
        </div>
        <div class="cart-restaurant-meta">
          <span class="cart-badge">Free delivery above ₹199</span>
          <button class="clear-cart-link" type="button" data-action="clear-cart">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16"></path>
              <path d="M10 11v6"></path>
              <path d="M14 11v6"></path>
              <path d="M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12"></path>
              <path d="M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"></path>
            </svg>
            <span>Clear cart</span>
          </button>
        </div>
      </div>
      ${items.map((item) => {
        const quantity = Number(item.quantity) || 0;
        const price = Number(item.price) || 0;
        const itemTotal = price * quantity;
        const tagClass = item.tagClass ? ` ${escapeHtml(item.tagClass)}` : '';
        const tagMarkup = item.tagText ? `<span class="cart-item-tag${tagClass}">${escapeHtml(item.tagText)}</span>` : '';
        const imageMarkup = item.image
          ? `<img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.imageAlt || item.name)}">`
          : '';

        return `
          <article class="cart-item" data-id="${escapeHtml(item.id)}">
            <div class="cart-item-image">${imageMarkup}</div>
            <div class="cart-item-info">
              ${tagMarkup}
              <h4>${escapeHtml(item.name)}</h4>
              <p>${escapeHtml(item.description || 'Freshly prepared and added to your Cibo cart.')}</p>
              <div class="cart-item-actions">
                <div class="qty-box">
                  <button class="qty-btn" data-action="decrease" type="button">-</button>
                  <span class="qty-value">${quantity}</span>
                  <button class="qty-btn" data-action="increase" type="button">+</button>
                </div>
                <a href="#" class="remove-link" data-action="remove">Remove</a>
              </div>
            </div>
            <div class="cart-item-price">
              <div class="price">${formatPrice(itemTotal)}</div>
              <div class="line-total">Item total</div>
            </div>
          </article>
        `;
      }).join('')}
    `;

    if (cartNotice) {
      scheduleCartNoticeClear();
    }

    calculateBillSummary();
  }

  document.addEventListener('click', (event) => {
    const actionTrigger = event.target.closest('[data-action]');

    if (actionTrigger?.dataset.action === 'clear-cart') {
      event.preventDefault();
      toggleClearCartModal(true);
      return;
    }

    const checkoutButton = event.target.closest('.checkout-btn');

    if (checkoutButton) {
      event.preventDefault();
      sessionStorage.setItem(CHECKOUT_INTENT_KEY, '1');
      saveCheckoutSnapshot();
      window.location.href = 'checkout.php';
      return;
    }

    if (!actionTrigger) {
      return;
    }

    const cartItem = actionTrigger.closest('.cart-item');

    if (!cartItem) {
      return;
    }

    event.preventDefault();

    const cart = readCart();
    const item = cart[cartItem.dataset.id];

    if (!item) {
      renderCart();
      return;
    }

    if (actionTrigger.dataset.action === 'increase') {
      if (Number(item.quantity || 0) >= MAX_ITEM_QUANTITY) {
        setCartNotice('Max 5 per item allowed');
        renderCart();
        return;
      }

      if (getTotalCartItems(cart) >= MAX_ORDER_ITEMS) {
        setCartNotice(ORDER_LIMIT_MESSAGE);
        renderCart();
        return;
      }

      item.quantity = Number(item.quantity || 0) + 1;
    }

    if (actionTrigger.dataset.action === 'decrease') {
      item.quantity = Number(item.quantity || 0) - 1;
    }

    if (actionTrigger.dataset.action === 'remove' || item.quantity <= 0) {
      delete cart[cartItem.dataset.id];
    } else {
      cart[cartItem.dataset.id] = item;
    }

    saveCart(cart);
    renderCart();
  });

  window.addEventListener('cibo-cart-notice-updated', renderCart);
  window.addEventListener('cibo-cart-updated', renderCart);
  window.addEventListener('cibo-bill-summary-updated', () => {
    if (getCartItems().length) {
      calculateBillSummary();
    }
  });
  window.addEventListener('cibo-cart-reconciled', (event) => {
    const removedCount = Number(event?.detail?.removedCount || 0);
    setCartNotice(removedCount > 0
      ? 'Your cart was updated to match the latest menu.'
      : 'Your cart prices were refreshed.');
    renderCart();
  });
  window.addEventListener('cibo-bill-summary-error', (event) => {
    const message = String(event?.detail?.message || '').trim();

    if (message) {
      setCartNotice(message);
      renderCart();
    }
  });

  if (cancelClearCartButton) {
    cancelClearCartButton.addEventListener('click', () => {
      toggleClearCartModal(false);
    });
  }

  if (confirmClearCartButton) {
    confirmClearCartButton.addEventListener('click', () => {
      toggleClearCartModal(false);
      setEmptyState();
    });
  }

  if (clearCartModal) {
    clearCartModal.addEventListener('click', (event) => {
      if (event.target === clearCartModal) {
        toggleClearCartModal(false);
      }
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && clearCartModal?.classList.contains('is-open')) {
      toggleClearCartModal(false);
    }
  });

  initializePromoBox();

  Promise.resolve(window.CiboBillSummary.ready)
    .catch(() => null)
    .finally(() => {
      renderCart();
      syncBillSummary();
    });
})();
