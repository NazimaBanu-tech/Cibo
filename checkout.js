(async () => {
  const STORAGE_KEY = 'cibo_cart';
  const LEGACY_STORAGE_KEY = 'cart';
  const SUMMARY_KEY = 'cibo_summary';
  const PROMO_KEY = 'cibo_promo';
  const CHECKOUT_SNAPSHOT_KEY = 'cibo_checkout_snapshot';
  const CHECKOUT_INTENT_KEY = 'cibo_checkout_intent';
  const CHECKOUT_DRAFT_KEY = 'cibo_checkout_draft';
  const CART_COUNT_KEY = 'cartCount';
  const LAST_ORDER_KEY = 'cibo_last_order';

  const form = document.getElementById('checkout-form');
  const orderSummaryCard = document.querySelector('.order-summary-card');
  const paymentOptions = document.querySelector('.payment-options');
  const paymentInputs = Array.from(document.querySelectorAll('input[name="payment"]'));
  const formMessage = document.getElementById('checkout-form-message');
  const initialPlaceOrderButton = document.getElementById('place-order-btn');
  const UPI_ID = 'naz@cibo';
  const UPI_PAYEE_NAME = 'Cibo';
  const UPI_URI = `upi://pay?pa=${UPI_ID}&pn=${UPI_PAYEE_NAME}`;
  const UPI_QR_URL = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(UPI_URI)}`;
  const BUTTON_LABELS = {
    cod: 'Place Order',
    card: 'Pay Securely',
    upi: 'Complete Payment',
    cardProcessing: 'Processing Payment...',
    cardSuccess: 'Payment Successful \u2713',
    upiProcessing: 'Verifying Payment...',
    upiSuccess: 'Payment Successful \u2713'
  };

  if (!form || !orderSummaryCard || !paymentOptions || !paymentInputs.length || !formMessage || !initialPlaceOrderButton) {
    return;
  }

  const nameInput = document.getElementById('checkout-name');
  const phoneInput = document.getElementById('checkout-phone');
  const addressInput = document.getElementById('checkout-address');
  const cityInput = document.getElementById('checkout-city');
  const pincodeInput = document.getElementById('checkout-pincode');
  let upiSimulationStatus = 'idle';
  let upiVerificationTimer = null;
  let upiSimCard = null;
  let upiQrButton = null;
  let upiPayableAmountNode = null;
  let upiIdInput = null;
  let upiIdErrorNode = null;
  let upiStatusNode = null;
  let upiQrScanned = false;
  let cardSimulationStatus = 'idle';
  let cardVerificationTimer = null;
  let cardStatusNode = null;
  let selectedAddressId = 0;
  let isAuthenticated = false;
  let availableAddresses = [];
  let selectedPaymentMethod = paymentInputs.find((input) => input.checked)?.value || '';
  let orderSubmissionInFlight = false;

  function createRequestId() {
    return 'cibo-order-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function debugCheckout(message, details) {
    if (typeof console === 'undefined' || typeof console.debug !== 'function') {
      return;
    }

    if (typeof details === 'undefined') {
      console.debug('[Cibo Checkout]', message);
      return;
    }

    console.debug('[Cibo Checkout]', message, details);
  }

  const fields = {
    name: {
      element: nameInput,
      errorNode: form.querySelector('[data-error-for="name"]'),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Full name is required';
        }

        if (!/^[a-zA-Z][a-zA-Z\s'.-]{1,}$/.test(normalized)) {
          return 'Enter a valid full name';
        }

        return '';
      }
    },
    phone: {
      element: phoneInput,
      errorNode: form.querySelector('[data-error-for="phone"]'),
      sanitize: (value) => value.replace(/\D/g, '').slice(0, 10),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Phone number is required';
        }

        if (!/^\d{10}$/.test(normalized)) {
          return 'Phone number must be 10 digits';
        }

        return '';
      }
    },
    address: {
      element: addressInput,
      errorNode: form.querySelector('[data-error-for="address"]'),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Address is required';
        }

        if (normalized.length < 10) {
          return 'Enter a complete delivery address';
        }

        return '';
      }
    },
    city: {
      element: cityInput,
      errorNode: form.querySelector('[data-error-for="city"]'),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'City is required';
        }

        if (!/^[a-zA-Z][a-zA-Z\s.-]{1,}$/.test(normalized)) {
          return 'Enter a valid city name';
        }

        return '';
      }
    },
    pincode: {
      element: pincodeInput,
      errorNode: form.querySelector('[data-error-for="pincode"]'),
      sanitize: (value) => value.replace(/\D/g, '').slice(0, 6),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Pincode is required';
        }

        if (!/^\d{6}$/.test(normalized)) {
          return 'Pincode must be 6 digits';
        }

        return '';
      }
    }
  };

  const paymentFields = [];

  function readJSON(key, fallback) {
    try {
      const rawValue = localStorage.getItem(key);
      return rawValue ? JSON.parse(rawValue) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function readSessionJSON(key, fallback) {
    try {
      const rawValue = sessionStorage.getItem(key);
      return rawValue ? JSON.parse(rawValue) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function writeJSON(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function readCheckoutDraft() {
    try {
      const rawValue = sessionStorage.getItem(CHECKOUT_DRAFT_KEY);
      const parsedValue = rawValue ? JSON.parse(rawValue) : null;
      return parsedValue && typeof parsedValue === 'object' ? parsedValue : null;
    } catch (error) {
      return null;
    }
  }

  function persistCheckoutDraft() {
    const draft = {
      name: String(nameInput?.value || '').trim(),
      phone: String(phoneInput?.value || '').trim(),
      address: String(addressInput?.value || '').trim(),
      city: String(cityInput?.value || '').trim(),
      pincode: String(pincodeInput?.value || '').trim(),
      payment_method: getSelectedPaymentType(),
      updatedAt: Date.now()
    };

    sessionStorage.setItem(CHECKOUT_DRAFT_KEY, JSON.stringify(draft));
  }

  function restoreCheckoutDraft() {
    const draft = readCheckoutDraft();

    if (!draft) {
      return;
    }

    if (!nameInput.value && draft.name) {
      nameInput.value = String(draft.name);
    }

    if (!phoneInput.value && draft.phone) {
      phoneInput.value = String(draft.phone);
    }

    if (!addressInput.value && draft.address) {
      addressInput.value = String(draft.address);
    }

    if (!cityInput.value && draft.city) {
      cityInput.value = String(draft.city);
    }

    if (!pincodeInput.value && draft.pincode) {
      pincodeInput.value = String(draft.pincode);
    }

    const draftPaymentMethod = String(draft.payment_method || '').trim().toLowerCase();

    if (draftPaymentMethod) {
      const matchingPaymentInput = paymentInputs.find((input) => String(input.value || '').trim().toLowerCase() === draftPaymentMethod);

      if (matchingPaymentInput) {
        matchingPaymentInput.checked = true;
        selectedPaymentMethod = matchingPaymentInput.value || selectedPaymentMethod;
      }
    }
  }

  function clearCheckoutDraft() {
    sessionStorage.removeItem(CHECKOUT_DRAFT_KEY);
  }

  function normalizePromoCode(code) {
    return String(code || '').trim().toUpperCase();
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

  async function createServerOrder(payload) {
    debugCheckout('Creating backend order payload', {
      restaurant_id: payload?.restaurant?.id || payload?.restaurant_id || '',
      payment_method: payload?.payment_method || '',
      address_id: payload?.address_id || 0,
      items_count: Array.isArray(payload?.items) ? payload.items.length : 0
    });

    const response = await fetch('api/orders.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Cibo-Request-Id': createRequestId()
      },
      body: JSON.stringify(payload)
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false) {
      throw new Error(data.message || 'Unable to place the order right now.');
    }

    return data.order || null;
  }

  function clearPlacedOrderState() {
    localStorage.removeItem(STORAGE_KEY);
    localStorage.removeItem(LEGACY_STORAGE_KEY);
    localStorage.removeItem(SUMMARY_KEY);
    localStorage.removeItem(PROMO_KEY);
    localStorage.removeItem(CHECKOUT_SNAPSHOT_KEY);
    localStorage.removeItem(CART_COUNT_KEY);
    sessionStorage.removeItem(CHECKOUT_SNAPSHOT_KEY);
    sessionStorage.removeItem(CHECKOUT_INTENT_KEY);
    clearCheckoutDraft();
    window.dispatchEvent(new Event('cibo-cart-updated'));
  }

  function formatPrice(amount) {
    return '\u20B9' + amount;
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function getCartItems() {
    const cart = readJSON(STORAGE_KEY, null);
    const legacyCart = readJSON(LEGACY_STORAGE_KEY, null);
    const checkoutSnapshot = readJSON(CHECKOUT_SNAPSHOT_KEY, null) || readSessionJSON(CHECKOUT_SNAPSHOT_KEY, null);
    const snapshotCart = checkoutSnapshot?.cart && typeof checkoutSnapshot.cart === 'object'
      ? checkoutSnapshot.cart
      : null;
    const effectiveCart = cart && typeof cart === 'object'
      ? cart
      : (legacyCart && typeof legacyCart === 'object'
        ? legacyCart
        : (snapshotCart && typeof snapshotCart === 'object' ? snapshotCart : {}));

    if (!localStorage.getItem(STORAGE_KEY) && legacyCart && typeof legacyCart === 'object') {
      writeJSON(STORAGE_KEY, legacyCart);
    }

    if (!localStorage.getItem(STORAGE_KEY) && snapshotCart && typeof snapshotCart === 'object') {
      writeJSON(STORAGE_KEY, snapshotCart);
    }

    return Object.values(effectiveCart).filter((item) => Number(item?.quantity) > 0);
  }

  function normalizedAddressValue(value) {
    return String(value || '')
      .toLowerCase()
      .replace(/[.,-]+/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function isAddressMatch(savedAddress, typedAddress, typedCity, typedPincode) {
    const addressLine = normalizedAddressValue(savedAddress?.address || savedAddress?.full_address || '');
    const city = normalizedAddressValue(savedAddress?.city || '');
    const pincode = String(savedAddress?.pincode || savedAddress?.postal_code || '').replace(/\D/g, '').trim();

    if (typedCity === '' || typedPincode === '' || city !== typedCity || pincode !== typedPincode) {
      return false;
    }

    if (typedAddress === '' || addressLine === '') {
      return false;
    }

    return addressLine === typedAddress
      || addressLine.includes(typedAddress)
      || typedAddress.includes(addressLine);
  }

  function resolveSelectedAddressId() {
    const typedAddress = normalizedAddressValue(addressInput?.value);
    const typedCity = normalizedAddressValue(cityInput?.value);
    const typedPincode = String(pincodeInput?.value || '').replace(/\D/g, '').trim();

    const matchedAddress = availableAddresses.find((address) => {
      return isAddressMatch(address, typedAddress, typedCity, typedPincode);
    }) || null;

    const nextAddressId = Number(matchedAddress?.id || 0) || 0;
    selectedAddressId = nextAddressId;

    debugCheckout('Selected address resolved', {
      selectedAddressId,
      typedAddress,
      typedCity,
      typedPincode,
      matchedAddressId: Number(matchedAddress?.id || 0) || 0
    });

    return selectedAddressId;
  }

  function hasValidSelectedAddress() {
    const addressId = Number(selectedAddressId || 0) || 0;

    if (addressId <= 0) {
      return false;
    }

    return availableAddresses.some((address) => Number(address?.id || 0) === addressId);
  }

  function readSummary() {
    const summary = readJSON(SUMMARY_KEY, null);
    return summary && typeof summary === 'object' ? summary : null;
  }

  function normalizeSummary(summary) {
    return {
      subtotal: Number(summary?.subtotal) || 0,
      delivery: Number(summary?.delivery_fee ?? summary?.delivery) || 0,
      tax: Number(summary?.tax_amount ?? summary?.tax) || 0,
      charges: 0,
      discount: Number(summary?.discount_amount ?? summary?.discount) || 0,
      total: Number(summary?.total_amount ?? summary?.total) || 0,
      discountType: String(summary?.discount_type ?? summary?.discountType ?? 'none'),
      discountLabel: String(summary?.discount_label ?? summary?.discountLabel ?? 'Discount'),
      taxLabel: String(summary?.tax_label ?? summary?.taxLabel ?? 'GST (5%)'),
      promoCode: normalizePromoCode(summary?.promo_code ?? summary?.promoCode ?? ''),
      promoStatus: String(summary?.promo_status ?? summary?.promoStatus ?? 'none'),
      promoMessage: String(summary?.promo_message ?? summary?.promoMessage ?? ''),
      promoApplied: Boolean(summary?.promo_applied ?? summary?.promoApplied),
      isFreeDelivery: Boolean(summary?.is_free_delivery ?? summary?.isFreeDelivery)
    };
  }

  async function loadSummary(items) {
    if (!window.CiboBillSummary) {
      throw new Error('Bill summary service is unavailable.');
    }

    await Promise.resolve(window.CiboBillSummary.ready).catch(() => null);

    if (typeof window.CiboBillSummary.refreshBillSummary === 'function') {
      return normalizeSummary(await window.CiboBillSummary.refreshBillSummary({
        cartItems: items,
        promoState: readPromoState(),
        persist: true
      }));
    }

    return normalizeSummary(window.CiboBillSummary.calculateBillSummary({
      cartItems: items,
      promoState: readPromoState(),
      persist: true
    }));
  }

  function getSelectedPaymentType() {
    const checkedValue = paymentInputs.find((input) => input.checked)?.value || '';

    if (checkedValue) {
      selectedPaymentMethod = checkedValue;
    }

    return selectedPaymentMethod || 'cod';
  }

  function getUpiStatusMessage() {
    if (upiSimulationStatus === 'processing') {
      return 'Verifying Payment...';
    }

    if (upiSimulationStatus === 'success') {
      return 'Payment Successful \u2713';
    }

    return '';
  }

  function getCardStatusMessage() {
    if (cardSimulationStatus === 'processing') {
      return 'Processing Payment...';
    }

    if (cardSimulationStatus === 'success') {
      return 'Payment Successful \u2713';
    }

    return '';
  }

  function sanitizeUpiId(value) {
    return String(value || '').trim().toLowerCase().replace(/\s+/g, '');
  }

  function isValidUpiId(value) {
    return /^[a-z0-9._-]{2,}@[a-z][a-z0-9.-]{1,}$/i.test(value);
  }

  function getUpiIdMessage() {
    if (!upiIdInput) {
      return '';
    }

    const normalized = sanitizeUpiId(upiIdInput.value);

    if (!normalized) {
      return upiQrScanned ? '' : 'Scan QR or enter UPI ID';
    }

    if (!isValidUpiId(normalized)) {
      return 'Enter a valid UPI ID';
    }

    return '';
  }

  function setUpiIdState(forceRequirement = false) {
    if (!upiIdInput || !upiIdErrorNode) {
      return true;
    }

    const normalized = sanitizeUpiId(upiIdInput.value);
    let message = '';

    if (normalized && !isValidUpiId(normalized)) {
      message = 'Enter a valid UPI ID';
    } else if (forceRequirement && !normalized && !upiQrScanned) {
      message = 'Scan QR or enter UPI ID';
    }

    upiIdErrorNode.textContent = message;
    upiIdInput.classList.remove('is-invalid', 'is-valid');

    if (message) {
      upiIdInput.classList.add('is-invalid');
      return false;
    }

    if (normalized) {
      upiIdInput.classList.add('is-valid');
    }

    return true;
  }

  function updatePlaceOrderButton() {
    const button = getPlaceOrderButton();

    if (!button) {
      return;
    }

    const paymentType = getSelectedPaymentType();
    let label = BUTTON_LABELS[paymentType] || BUTTON_LABELS.cod;

    if (paymentType === 'upi') {
      if (upiSimulationStatus === 'processing') {
        label = BUTTON_LABELS.upiProcessing;
      } else if (upiSimulationStatus === 'success') {
        label = BUTTON_LABELS.upiSuccess;
      }
    } else if (paymentType === 'card') {
      if (cardSimulationStatus === 'processing') {
        label = BUTTON_LABELS.cardProcessing;
      } else if (cardSimulationStatus === 'success') {
        label = BUTTON_LABELS.cardSuccess;
      }
    }

    button.textContent = label;
  }

  function setFormMessage(message, type = '') {
    formMessage.textContent = message;
    formMessage.className = 'checkout-form-message' + (type ? ` ${type}` : '');
  }

  window.addEventListener('cibo-cart-reconciled', (event) => {
    const removedCount = Number(event?.detail?.removedCount || 0);
    setFormMessage(
      removedCount > 0
        ? 'Your cart was updated to match the latest menu before checkout.'
        : 'Your checkout totals were refreshed with the latest prices.',
      'success'
    );
  });

  window.addEventListener('cibo-bill-summary-error', (event) => {
    const message = String(event?.detail?.message || '').trim();

    if (message) {
      setFormMessage(message, 'error');
    }
  });

  function setFieldState(field, message) {
    if (field.errorNode) {
      field.errorNode.textContent = message;
    }

    field.element.classList.remove('is-invalid', 'is-valid');

    if (message) {
      field.element.classList.add('is-invalid');
      return false;
    }

    if (field.element.value.trim()) {
      field.element.classList.add('is-valid');
    }

    return true;
  }

  function validateField(field) {
    return setFieldState(field, field.validate(field.element.value));
  }

  function getFieldMessage(field) {
    return field.validate(field.element.value);
  }

  function createPaymentFields() {
    const paymentFieldsWrap = document.createElement('div');
    paymentFieldsWrap.className = 'form-grid';
    paymentFieldsWrap.style.marginTop = '18px';
    paymentFieldsWrap.style.display = 'none';

    const upiGroup = document.createElement('div');
    upiGroup.className = 'form-group full';
    upiGroup.style.display = 'none';
    upiGroup.innerHTML = `
      <div class="upi-sim-card" id="upi-sim-card">
        <div class="upi-sim-top">
          <div class="upi-sim-copy">
            <h4>Pay with UPI</h4>
            <p>Scan using any UPI app</p>
          </div>
          <button type="button" class="upi-qr-button" id="upi-qr-button" aria-label="Scan the UPI QR code">
            <div class="upi-qr">
              <img src="${UPI_QR_URL}" alt="UPI QR code for ${UPI_ID}">
            </div>
          </button>
        </div>
        <div class="upi-sim-meta">
          <div class="upi-sim-row">
            <span>UPI ID</span>
            <strong>${UPI_ID}</strong>
          </div>
          <div class="upi-sim-row">
            <span>Total payable</span>
            <strong id="upi-payable-amount">₹0</strong>
          </div>
        </div>
        <div class="form-group full upi-manual-group">
          <label for="checkout-upi-id">Enter your UPI ID</label>
          <input id="checkout-upi-id" name="upi_id" type="text" placeholder="user@oksbi" inputmode="email" autocomplete="off">
          <div class="field-error" data-error-for="upi_id" aria-live="polite"></div>
        </div>
        <div class="upi-sim-status" id="upi-sim-status" aria-live="polite"></div>
      </div>
    `;

    const cardGrid = document.createElement('div');
    cardGrid.className = 'form-grid';
    cardGrid.style.display = 'none';
    cardGrid.style.gridColumn = 'span 2';
    cardGrid.innerHTML = `
      <div class="form-group full">
        <label for="checkout-card-number">Card Number</label>
        <input id="checkout-card-number" name="card_number" type="text" inputmode="numeric" placeholder="1234 5678 9012 3456" autocomplete="cc-number">
        <div class="field-error" data-error-for="card_number" aria-live="polite"></div>
      </div>
      <div class="form-group full">
        <label for="checkout-card-holder">Card Holder Name</label>
        <input id="checkout-card-holder" name="card_holder" type="text" placeholder="" autocomplete="cc-name">
        <div class="field-error" data-error-for="card_holder" aria-live="polite"></div>
      </div>
      <div class="form-group">
        <label for="checkout-card-expiry">Expiry Date</label>
        <input id="checkout-card-expiry" name="card_expiry" type="text" inputmode="numeric" placeholder="MM/YY" autocomplete="cc-exp">
        <div class="field-error" data-error-for="card_expiry" aria-live="polite"></div>
      </div>
      <div class="form-group">
        <label for="checkout-card-cvv">CVV</label>
        <div class="password-field">
          <input id="checkout-card-cvv" name="card_cvv" type="password" inputmode="numeric" placeholder="CVV" autocomplete="cc-csc" maxlength="3">
          <button type="button" class="password-toggle" data-password-toggle="checkout-card-cvv" aria-label="Show CVV" aria-pressed="false">
            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M3 3l18 18"></path>
              <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
              <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.2 17.2 0 0 1-4.1 4.8"></path>
              <path d="M6.2 6.2A17.6 17.6 0 0 0 2 12s3.5 7 10 7a10.9 10.9 0 0 0 5.1-1.2"></path>
            </svg>
          </button>
        </div>
        <div class="field-error" data-error-for="card_cvv" aria-live="polite"></div>
      </div>
      <div class="card-sim-status full" id="card-sim-status" aria-live="polite"></div>
    `;

    paymentFieldsWrap.appendChild(upiGroup);
    paymentFieldsWrap.appendChild(cardGrid);
    paymentOptions.appendChild(paymentFieldsWrap);

    const cardNumberInput = paymentFieldsWrap.querySelector('#checkout-card-number');
    const cardHolderInput = paymentFieldsWrap.querySelector('#checkout-card-holder');
    const expiryInput = paymentFieldsWrap.querySelector('#checkout-card-expiry');
    const cvvInput = paymentFieldsWrap.querySelector('#checkout-card-cvv');
    upiSimCard = paymentFieldsWrap.querySelector('#upi-sim-card');
    upiQrButton = paymentFieldsWrap.querySelector('#upi-qr-button');
    upiPayableAmountNode = paymentFieldsWrap.querySelector('#upi-payable-amount');
    upiIdInput = paymentFieldsWrap.querySelector('#checkout-upi-id');
    upiIdErrorNode = paymentFieldsWrap.querySelector('[data-error-for="upi_id"]');
    upiStatusNode = paymentFieldsWrap.querySelector('#upi-sim-status');
    cardStatusNode = paymentFieldsWrap.querySelector('#card-sim-status');

    paymentFields.push(
      {
        type: 'card',
        element: cardNumberInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="card_number"]'),
        sanitize: (value) => value.replace(/\D/g, '').slice(0, 16),
        format: (value) => value.replace(/(\d{4})(?=\d)/g, '$1 ').trim(),
        validate: (value) => {
          const digits = String(value || '').replace(/\D/g, '');

          if (!digits) {
            return 'Enter valid card number';
          }

          if (digits.length !== 16) {
            return 'Enter valid card number';
          }

          return '';
        }
      },
      {
        type: 'card',
        element: cardHolderInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="card_holder"]'),
        validate: (value) => {
          const normalized = String(value || '').trim();

          if (!normalized || normalized.length < 2 || !/^[a-zA-Z][a-zA-Z\s'.-]+$/.test(normalized)) {
            return 'Enter card holder name';
          }

          return '';
        }
      },
      {
        type: 'card',
        element: expiryInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="card_expiry"]'),
        sanitize: (value) => {
          const digits = value.replace(/\D/g, '').slice(0, 4);
          return digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
        },
        validate: (value) => {
          const normalized = String(value || '').trim();

          if (!normalized) {
            return 'Invalid expiry date';
          }

          if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(normalized)) {
            return 'Invalid expiry date';
          }

          const [monthText, yearText] = normalized.split('/');
          const month = Number(monthText);
          const year = 2000 + Number(yearText);
          const now = new Date();
          const currentMonth = now.getMonth() + 1;
          const currentYear = now.getFullYear();

          if (year < currentYear || (year === currentYear && month < currentMonth)) {
            return 'Invalid expiry date';
          }

          return '';
        }
      },
      {
        type: 'card',
        element: cvvInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="card_cvv"]'),
        sanitize: (value) => value.replace(/\D/g, '').slice(0, 3),
        validate: (value) => {
          const normalized = String(value || '').trim();

          if (!normalized) {
            return 'Enter valid CVV';
          }

          if (!/^\d{3}$/.test(normalized)) {
            return 'Enter valid CVV';
          }

          return '';
        }
      }
    );

    paymentFieldsWrap._upiGroup = upiGroup;
    paymentFieldsWrap._cardGrid = cardGrid;

    if (upiQrButton) {
      upiQrButton.addEventListener('click', () => {
        upiQrScanned = true;
        upiQrButton.classList.add('is-selected');
        setUpiIdState(false);
        setFormMessage('');

        if (upiStatusNode && upiSimulationStatus === 'idle') {
          upiStatusNode.textContent = 'QR selected for payment.';
          upiStatusNode.className = 'upi-sim-status';
        }
      });
    }

    if (upiIdInput) {
      upiIdInput.addEventListener('input', () => {
        upiIdInput.value = sanitizeUpiId(upiIdInput.value);
        setUpiIdState(false);
        setFormMessage('');
        updateSubmitState();
      });

      upiIdInput.addEventListener('blur', () => {
        setUpiIdState(false);
        updateSubmitState();
      });
    }

    /*

        if (upiStatusNode) {
          upiStatusNode.textContent = 'Payment Successful ✓';
          upiStatusNode.className = 'upi-sim-status is-success';
        }

        if (upiPaidButton) {
          upiPaidButton.textContent = 'Paid Successfully';
          upiPaidButton.classList.add('is-valid');
        }

        updateSubmitState();
        form.requestSubmit();
      });
    }

    */
    return paymentFieldsWrap;
  }

  const paymentFieldsWrap = createPaymentFields();
  restoreCheckoutDraft();

  function updateUpiPayableAmount(amount = 0) {
    if (upiPayableAmountNode) {
      upiPayableAmountNode.textContent = formatPrice(Number(amount) || 0);
    }
  }

  function resetCardSimulation() {
    cardSimulationStatus = 'idle';

    if (cardVerificationTimer) {
      window.clearTimeout(cardVerificationTimer);
      cardVerificationTimer = null;
    }

    if (cardStatusNode) {
      cardStatusNode.textContent = getCardStatusMessage();
      cardStatusNode.className = 'card-sim-status full';
    }

    updatePlaceOrderButton();
  }

  function resetUpiSimulation() {
    upiSimulationStatus = 'idle';
    upiQrScanned = false;

    if (upiVerificationTimer) {
      window.clearTimeout(upiVerificationTimer);
      upiVerificationTimer = null;
    }

    if (upiStatusNode) {
      upiStatusNode.textContent = getUpiStatusMessage();
      upiStatusNode.className = 'upi-sim-status';
    }

    if (upiQrButton) {
      upiQrButton.classList.remove('is-selected');
    }

    if (upiIdInput) {
      upiIdInput.value = '';
      upiIdInput.classList.remove('is-invalid', 'is-valid');
    }

    if (upiIdErrorNode) {
      upiIdErrorNode.textContent = '';
    }

    updatePlaceOrderButton();
  }

  function clearPaymentField(field) {
    if ('value' in field.element) {
      field.element.value = '';
    }

    if (field.type === 'upi') {
      resetUpiSimulation();
    } else if (field.type === 'card') {
      resetCardSimulation();
    }

    setFieldState(field, '');
  }

  function syncPaymentFields() {
    const paymentType = getSelectedPaymentType();

    paymentFieldsWrap.style.display = paymentType === 'cod' ? 'none' : 'grid';
    paymentFieldsWrap._upiGroup.style.display = paymentType === 'upi' ? '' : 'none';
    paymentFieldsWrap._cardGrid.style.display = paymentType === 'card' ? 'grid' : 'none';

    if (upiSimCard) {
      upiSimCard.classList.toggle('is-visible', paymentType === 'upi');
    }

    paymentFields.forEach((field) => {
      if (field.type !== paymentType) {
        clearPaymentField(field);
      }
    });

    if (paymentType !== 'upi') {
      resetUpiSimulation();
    } else if (upiStatusNode) {
      upiStatusNode.textContent = getUpiStatusMessage();
      upiStatusNode.className = `upi-sim-status${upiSimulationStatus === 'processing' ? ' is-processing' : ''}${upiSimulationStatus === 'success' ? ' is-success' : ''}`;
    }

    if (paymentType !== 'card') {
      resetCardSimulation();
    } else if (cardStatusNode) {
      cardStatusNode.textContent = getCardStatusMessage();
      cardStatusNode.className = `card-sim-status full${cardSimulationStatus === 'processing' ? ' is-processing' : ''}${cardSimulationStatus === 'success' ? ' is-success' : ''}`;
    }

    updatePlaceOrderButton();
  }

  function validateForm(options = {}) {
    const requireUpiSuccess = Boolean(options.requireUpiSuccess);
    const baseValid = Object.values(fields).every(validateField);
    const paymentType = getSelectedPaymentType();
    const activePaymentFields = paymentFields.filter((field) => field.type === paymentType && field.type !== 'upi');
    const paymentValid = activePaymentFields.every(validateField);
    const upiValid = paymentType !== 'upi' || !requireUpiSuccess || upiSimulationStatus === 'success';

    return baseValid && paymentValid && upiValid;
  }

  function getPlaceOrderButton() {
    return document.getElementById('place-order-btn');
  }

  function updateSubmitState() {
    const button = getPlaceOrderButton();

    if (button) {
      const paymentType = getSelectedPaymentType();
      const hasCartItems = getCartItems().length > 0;
      const hasPaymentMethod = paymentInputs.some((input) => input.checked) && Boolean(paymentType);
      const isProcessing = paymentType === 'upi'
        ? upiSimulationStatus === 'processing'
        : paymentType === 'card'
          ? cardSimulationStatus === 'processing'
          : false;
      const shouldDisable = !hasPaymentMethod || !hasCartItems || isProcessing || orderSubmissionInFlight;
      button.disabled = shouldDisable;

      debugCheckout('Place Order button state updated', {
        disabled: button.disabled,
        paymentType,
        isAuthenticated,
        selectedAddressId,
        hasValidAddress: hasValidSelectedAddress(),
        hasPaymentMethod,
        hasCartItems,
        isProcessing,
        orderSubmissionInFlight,
        upiSimulationStatus,
        cardSimulationStatus
      });
    }

    updatePlaceOrderButton();
  }

  async function populateFromAccount() {
    if (!window.CiboAccountApi) {
      isAuthenticated = false;
      selectedAddressId = 0;
      availableAddresses = [];
      updateSubmitState();
      return;
    }

    let account = null;
    let addresses = [];

    try {
      const [accountResponse, addressesResponse] = await Promise.all([
        window.CiboAccountApi.getProfile(),
        window.CiboAccountApi.listAddresses()
      ]);

      if (accountResponse?.user && typeof accountResponse.user === 'object') {
        account = accountResponse.user;
      }

      addresses = Array.isArray(addressesResponse?.addresses) ? addressesResponse.addresses : [];
      availableAddresses = addresses.filter((address) => address && typeof address === 'object');
      isAuthenticated = Boolean(account && account.id);
      debugCheckout('Account and address data loaded', {
        isAuthenticated,
        addressesCount: availableAddresses.length,
        selectedPaymentMethod: getSelectedPaymentType()
      });
    } catch (error) {
      isAuthenticated = false;
      selectedAddressId = 0;
      availableAddresses = [];

      if (error?.ciboAuthError) {
        updateSubmitState();
        return;
      }

      setFormMessage(error.message || 'Unable to load saved account details right now. You can still continue as a guest.', 'error');
      updateSubmitState();
      return;
    }

    if (account && typeof account === 'object') {
      if (!nameInput.value && account.name) {
        nameInput.value = String(account.name);
      }

      if (!phoneInput.value && account.phone) {
        phoneInput.value = String(account.phone).replace(/\D/g, '').slice(0, 10);
      }
    }

    const primaryAddress = availableAddresses[0];
    selectedAddressId = Number(primaryAddress?.id || 0) || 0;

    if (primaryAddress && typeof primaryAddress === 'object') {
      if (!addressInput.value && primaryAddress.address) {
        addressInput.value = String(primaryAddress.address);
      }

      if (!cityInput.value && primaryAddress.city) {
        cityInput.value = String(primaryAddress.city);
      }

      if (!pincodeInput.value && (primaryAddress.pincode || primaryAddress.postal_code)) {
        pincodeInput.value = String(primaryAddress.pincode || primaryAddress.postal_code);
      }
    }

    resolveSelectedAddressId();

    persistCheckoutDraft();
    updateSubmitState();
  }

  function setupFieldListeners(fieldMap) {
    Object.values(fieldMap).forEach((field) => {
      field.element.addEventListener('input', () => {
        if (typeof field.sanitize === 'function') {
          const sanitized = field.sanitize(field.element.value);
          field.element.value = typeof field.format === 'function' ? field.format(sanitized) : sanitized;
        }

        if (field.type === 'card') {
          resetCardSimulation();
        }

        if (field.element === addressInput || field.element === cityInput || field.element === pincodeInput) {
          resolveSelectedAddressId();
        }

        validateField(field);
        setFormMessage('');
        persistCheckoutDraft();
        updateSubmitState();
      });

      field.element.addEventListener('blur', () => {
        if (field.type === 'card') {
          resetCardSimulation();
        }

        if (field.element === addressInput || field.element === cityInput || field.element === pincodeInput) {
          resolveSelectedAddressId();
        }

        validateField(field);
        persistCheckoutDraft();
        updateSubmitState();
      });
    });
  }

  async function renderCheckoutLegacy() {
    let items = getCartItems();
    const hasCheckoutIntent = sessionStorage.getItem(CHECKOUT_INTENT_KEY) === '1';

    if (!items.length) {
      if (!hasCheckoutIntent) {
        window.location.href = 'cart.php';
        return;
      }

      return;
    }

    if (hasCheckoutIntent) {
      sessionStorage.removeItem(CHECKOUT_INTENT_KEY);
    }

    const safeSummary = await loadSummary(items);
    items = getCartItems();
    const offerMessage = Number(safeSummary.discount) > 0
      ? `🎉 Offer applied: You saved ${formatPrice(Number(safeSummary.discount) || 0)} on this order`
      : '';

    orderSummaryCard.innerHTML = `
      <h3>Order Summary</h3>
      ${items.map((item) => {
        const quantity = Number(item.quantity) || 0;
        const itemTotal = (Number(item.price) || 0) * quantity;

        return `
          <div class="order-item">
            <span>${escapeHtml(item.name)} x ${quantity}</span>
            <span>${formatPrice(itemTotal)}</span>
          </div>
        `;
      }).join('')}
      <div class="summary-row">
        <span>Subtotal</span>
        <span>${formatPrice(Number(safeSummary.subtotal) || 0)}</span>
      </div>
      <div class="summary-row">
        <span>Delivery Fee</span>
        <span>${formatPrice(Number(safeSummary.delivery) || 0)}</span>
      </div>
      <div class="summary-row">
        <span>Taxes</span>
        <span>${formatPrice((Number(safeSummary.tax) || 0) + (Number(safeSummary.charges) || 0))}</span>
      </div>
      <div class="summary-row">
        <span>Discount</span>
        <span>${Number(safeSummary.discount) > 0 ? '- ' : ''}${formatPrice(Number(safeSummary.discount) || 0)}</span>
      </div>
      ${offerMessage ? `
        <div class="order-item">
          <span>${escapeHtml(offerMessage)}</span>
          <span></span>
        </div>
      ` : ''}
      <div class="summary-total">
        <span>Total</span>
        <span>${formatPrice(Number(safeSummary.total) || 0)}</span>
      </div>
      <button type="submit" class="place-order-btn primary-btn" id="place-order-btn" disabled>Place Order</button>
    `;
  }

  async function renderCheckout() {
    let items = getCartItems();
    const hasCheckoutIntent = sessionStorage.getItem(CHECKOUT_INTENT_KEY) === '1';

    if (!items.length) {
      if (!hasCheckoutIntent) {
        window.location.href = 'cart.php';
        return;
      }

      return;
    }

    if (hasCheckoutIntent) {
      sessionStorage.removeItem(CHECKOUT_INTENT_KEY);
    }

    const safeSummary = await loadSummary(items);
    items = getCartItems();
    updateUpiPayableAmount(Number(safeSummary.total) || 0);
    let offerMessage = '';

    if (safeSummary.promoApplied && Number(safeSummary.discount) > 0) {
      offerMessage = `${safeSummary.promoCode} applied. You saved ${formatPrice(Number(safeSummary.discount) || 0)} on this order.`;
    } else if (safeSummary.discountType === 'auto' && Number(safeSummary.discount) > 0) {
      offerMessage = `${safeSummary.discountLabel} applied. You saved ${formatPrice(Number(safeSummary.discount) || 0)} on this order.`;
    } else if (Number(safeSummary.delivery) === 0 && Number(safeSummary.subtotal) >= 199) {
      offerMessage = 'Free delivery has been applied to this order.';
    }

    orderSummaryCard.innerHTML = `
      <h3>Order Summary</h3>
      ${items.map((item) => {
        const quantity = Number(item.quantity) || 0;
        const itemTotal = (Number(item.price) || 0) * quantity;

        return `
          <div class="order-item">
            <span>${escapeHtml(item.name)} x ${quantity}</span>
            <span>${formatPrice(itemTotal)}</span>
          </div>
        `;
      }).join('')}
      <div class="summary-row">
        <span>Subtotal</span>
        <span>${formatPrice(Number(safeSummary.subtotal) || 0)}</span>
      </div>
      <div class="summary-row">
        <span>Delivery</span>
        <span>${Number(safeSummary.delivery) === 0 ? 'FREE' : formatPrice(Number(safeSummary.delivery) || 0)}</span>
      </div>
      <div class="summary-row">
        <span>${escapeHtml(safeSummary.discountLabel || 'Discount')}</span>
        <span>${Number(safeSummary.discount) > 0 ? '- ' : ''}${formatPrice(Number(safeSummary.discount) || 0)}</span>
      </div>
      <div class="summary-row">
        <span>${escapeHtml(safeSummary.taxLabel || 'GST (5%)')}</span>
        <span>${formatPrice(Number(safeSummary.tax) || 0)}</span>
      </div>
      ${offerMessage ? `
        <div class="order-item order-summary-note">
          <span>${escapeHtml(offerMessage)}</span>
          <span></span>
        </div>
      ` : ''}
      <div class="summary-total">
        <span>Total</span>
        <span>${formatPrice(Number(safeSummary.total) || 0)}</span>
      </div>
      <button type="submit" class="place-order-btn primary-btn" id="place-order-btn" disabled>Place Order</button>
    `;
  }

  function persistOrderUiState() {
    const items = getCartItems();
    const cartSnapshot = items.reduce((cart, item, index) => {
      const key = item.id || item.name || ('item-' + index);
      cart[key] = {
        ...item,
        id: item.id || key,
        quantity: Number(item.quantity) || 0
      };
      return cart;
    }, {});

    writeJSON(LAST_ORDER_KEY, cartSnapshot);
  }

  async function placeOrder(payload) {
    const serverOrder = await createServerOrder(payload);
    const orderNumber = String(serverOrder?.order_number || '');

    if (!orderNumber) {
      throw new Error('Order was created, but the confirmation number was missing.');
    }

    persistOrderUiState();
    clearPlacedOrderState();
    setFormMessage('Order placed successfully. Redirecting...', 'success');
    window.location.href = 'success.php?order=' + encodeURIComponent(orderNumber);
  }

  async function runUpiVerification(payload) {
    if (upiSimulationStatus === 'processing') {
      return;
    }

    if (!setUpiIdState(true)) {
      if (upiStatusNode) {
        upiStatusNode.textContent = 'Scan QR or enter UPI ID';
        upiStatusNode.className = 'upi-sim-status';
      }

      updateSubmitState();
      return;
    }

    upiSimulationStatus = 'processing';

    if (upiStatusNode) {
      upiStatusNode.textContent = getUpiStatusMessage();
      upiStatusNode.className = 'upi-sim-status is-processing';
    }

    updateSubmitState();

    await new Promise((resolve) => {
      upiVerificationTimer = window.setTimeout(resolve, 1500);
    });

    upiVerificationTimer = null;
    upiSimulationStatus = 'success';

    if (upiStatusNode) {
      upiStatusNode.textContent = getUpiStatusMessage();
      upiStatusNode.className = 'upi-sim-status is-success';
    }

    updateSubmitState();
    await new Promise((resolve) => window.setTimeout(resolve, 450));
    await placeOrder(payload);
  }

  async function runCardVerification(payload) {
    if (cardSimulationStatus === 'processing') {
      return;
    }

    cardSimulationStatus = 'processing';

    if (cardStatusNode) {
      cardStatusNode.textContent = getCardStatusMessage();
      cardStatusNode.className = 'card-sim-status full is-processing';
    }

    updateSubmitState();

    await new Promise((resolve) => {
      cardVerificationTimer = window.setTimeout(resolve, 1600);
    });

    cardVerificationTimer = null;
    cardSimulationStatus = 'success';

    if (cardStatusNode) {
      cardStatusNode.textContent = getCardStatusMessage();
      cardStatusNode.className = 'card-sim-status full is-success';
    }

    updateSubmitState();
    await new Promise((resolve) => window.setTimeout(resolve, 450));
    await placeOrder(payload);
  }

  let refreshedPlaceOrderButton = null;

  try {
    await renderCheckout();
    refreshedPlaceOrderButton = document.getElementById('place-order-btn');
  } catch (error) {
    setFormMessage(error instanceof Error ? error.message : 'Unable to load the latest order summary.', 'error');
  }

  if (!refreshedPlaceOrderButton) {
    return;
  }

  await populateFromAccount();
  setupFieldListeners(fields);
  setupFieldListeners(paymentFields.reduce((accumulator, field, index) => {
    accumulator[index] = field;
    return accumulator;
  }, {}));

    paymentInputs.forEach((input) => {
      input.addEventListener('change', () => {
        selectedPaymentMethod = input.value || '';
        debugCheckout('Payment method changed', {
          selectedPaymentMethod: selectedPaymentMethod || getSelectedPaymentType()
        });
        syncPaymentFields();
        setFormMessage('');
        persistCheckoutDraft();
        updateSubmitState();
      });
    });

  document.addEventListener('click', (event) => {
    const toggleButton = event.target.closest('[data-password-toggle]');

    if (!toggleButton) {
      return;
    }

    const target = document.getElementById(toggleButton.dataset.passwordToggle || '');

    if (!target) {
      return;
    }

    const showing = target.type === 'text';
    target.type = showing ? 'password' : 'text';
    toggleButton.setAttribute('aria-pressed', String(!showing));
    toggleButton.setAttribute('aria-label', showing ? 'Show CVV' : 'Hide CVV');
    toggleButton.classList.toggle('is-visible', !showing);
  });

  syncPaymentFields();
  persistCheckoutDraft();

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    setFormMessage('');

    if (orderSubmissionInFlight) {
      return;
    }

    const paymentType = getSelectedPaymentType();
    resolveSelectedAddressId();
    debugCheckout('Checkout submit triggered', {
      selectedPaymentMethod: paymentType,
      selectedAddressId,
      isAuthenticated,
      hasValidAddress: hasValidSelectedAddress(),
      itemsCount: getCartItems().length
    });

    if (!validateForm({ requireUpiSuccess: false })) {
      updateSubmitState();
      setFormMessage(paymentType === 'upi'
        ? 'Please correct the highlighted fields before completing payment.'
        : paymentType === 'card'
          ? 'Please correct the highlighted fields before paying securely.'
          : 'Please correct the highlighted fields before placing your order.', 'error');
      return;
    }

    const items = getCartItems();
    const promoState = readPromoState();

    const payload = {
      address_id: isAuthenticated ? selectedAddressId : 0,
      restaurant: {
        id: items[0]?.restaurantId || '',
        name: items[0]?.restaurant || 'Cibo Order',
        slug: items[0]?.restaurantSlug || '',
        page: items[0]?.restaurantPage || ''
      },
      promo_code: promoState.code,
      payment_method: getSelectedPaymentType(),
      customer: {
        address_id: isAuthenticated ? selectedAddressId : 0,
        name: nameInput.value.trim(),
        phone: phoneInput.value.trim(),
        address: addressInput.value.trim(),
        city: cityInput.value.trim(),
        pincode: pincodeInput.value.trim()
      },
      items: items.map((item) => ({
        id: item.id,
        name: item.name,
        restaurantId: item.restaurantId || items[0]?.restaurantId || '',
        slug: item.slug || item.itemSlug || '',
        price: Number(item.price) || 0,
        quantity: Number(item.quantity) || 0,
        restaurant: item.restaurant || items[0]?.restaurant || 'Cibo Order',
        restaurantSlug: item.restaurantSlug || items[0]?.restaurantSlug || '',
        restaurantPage: item.restaurantPage || items[0]?.restaurantPage || ''
      }))
    };

    try {
      orderSubmissionInFlight = true;
      updateSubmitState();

      if (paymentType === 'upi' && upiSimulationStatus !== 'success') {
        await runUpiVerification(payload);
        return;
      }

      if (paymentType === 'card' && cardSimulationStatus !== 'success') {
        await runCardVerification(payload);
        return;
      }

      await placeOrder(payload);
    } catch (error) {
      setFormMessage(error instanceof Error ? error.message : 'Unable to place the order right now.', 'error');
    } finally {
      orderSubmissionInFlight = false;
      updateSubmitState();
    }
  });

  updateSubmitState();
})();
