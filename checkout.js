(async () => {
  const SUMMARY_KEY = 'cibo_summary';
  const PROMO_KEY = 'cibo_promo';
  const CHECKOUT_SNAPSHOT_KEY = 'cibo_checkout_snapshot';
  const CHECKOUT_INTENT_KEY = 'cibo_checkout_intent';
  const CHECKOUT_DRAFT_KEY = 'cibo_checkout_draft';
  const LAST_ORDER_KEY = 'cibo_last_order';
  const cartManager = window.CiboCartManager;

  const form = document.getElementById('checkout-form');
  const orderSummaryCard = document.querySelector('.order-summary-card');
  const paymentOptions = document.querySelector('.payment-options');
  const paymentInputs = Array.from(document.querySelectorAll('input[name="payment"]'));
  const formMessage = document.getElementById('checkout-form-message');
  const initialPlaceOrderButton = document.getElementById('place-order-btn');
  const UPI_ID = 'naz@cibo';
  const UPI_PAYEE_NAME = 'Cibo';
  const BUTTON_LABELS = {
    cod: 'Place Order',
    card: 'Pay Securely',
    upi: 'Complete Payment',
    cardProcessing: 'Processing Payment...',
    upiProcessing: 'Processing Payment...'
  };
  const CHECKOUT_DEBUG_ENABLED = window.CIBO_CHECKOUT_DEBUG === true;

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
  let upiQrStateBadgeNode = null;
  let upiQrHintNode = null;
  let upiProcessingPanel = null;
  let upiProcessingTitleNode = null;
  let upiProcessingStepNodes = [];
  let upiSuccessPanel = null;
  let upiSuccessAmountNode = null;
  let upiSuccessReferenceNode = null;
  let upiSuccessTimeNode = null;
  let upiQrScanned = false;
  let cardSimulationStatus = 'idle';
  let cardVerificationTimer = null;
  let cardStatusNode = null;
  let cardHolderInput = null;
  let cardNumberInput = null;
  let cardExpiryInput = null;
  let cardCvvInput = null;
  let cardPreviewNode = null;
  let cardAmountNode = null;
  let cardProcessingPanel = null;
  let cardProcessingTitleNode = null;
  let cardProcessingStepNodes = [];
  let cardOtpPanel = null;
  let cardOtpInput = null;
  let cardOtpErrorNode = null;
  let cardOtpVerifyButton = null;
  let cardSuccessPanel = null;
  let cardSuccessAmountNode = null;
  let cardSuccessMaskedNode = null;
  let cardSuccessReferenceNode = null;
  let cardSuccessTimeNode = null;
  let selectedAddressId = 0;
  let isAuthenticated = false;
  let availableAddresses = [];
  let currentUserEmail = '';
  let selectedPaymentMethod = paymentInputs.find((input) => input.checked)?.value || '';
  let orderSubmissionInFlight = false;

  function createRequestId() {
    return 'cibo-order-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function wait(ms) {
    return new Promise((resolve) => {
      window.setTimeout(resolve, ms);
    });
  }

  function debugCheckout(message, details) {
    if (!CHECKOUT_DEBUG_ENABLED || typeof console === 'undefined' || typeof console.debug !== 'function') {
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
    cartManager?.clearCart({
      source: 'checkout-order-complete'
    });
    localStorage.removeItem(SUMMARY_KEY);
    localStorage.removeItem(PROMO_KEY);
    localStorage.removeItem(CHECKOUT_SNAPSHOT_KEY);
    sessionStorage.removeItem(CHECKOUT_SNAPSHOT_KEY);
    sessionStorage.removeItem(CHECKOUT_INTENT_KEY);
    clearCheckoutDraft();
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
    return cartManager ? cartManager.getItems() : [];
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
    if (upiSimulationStatus === 'scanning') {
      return 'Scanning QR... Hold steady while we prepare the UPI request.';
    }

    if (upiSimulationStatus === 'processing') {
      return 'Waiting for approval in your UPI app...';
    }

    if (upiSimulationStatus === 'success') {
      return 'UPI payment approved. Finalizing your order...';
    }

    return 'Scan the QR or enter a valid UPI ID to continue with your UPI payment.';
  }

  function getCardStatusMessage() {
    if (cardSimulationStatus === 'processing') {
      return 'Processing your card payment...';
    }

    return 'Your card selection will continue through the normal checkout flow.';
  }

  function setUpiVisualState(state, options = {}) {
    upiSimulationStatus = state;

    if (upiSimCard) {
      upiSimCard.dataset.upiState = state;
    }

    if (upiQrButton) {
      upiQrButton.classList.toggle('is-selected', state !== 'idle');
    }

    if (upiQrStateBadgeNode) {
      const badgeLabel = options.badge || (
        state === 'scanning'
          ? 'Scanning QR'
          : state === 'processing'
            ? 'Waiting for approval'
            : state === 'success'
              ? 'Paid'
              : 'Ready'
      );
      upiQrStateBadgeNode.textContent = badgeLabel;
    }

    if (upiQrHintNode) {
      const hintLabel = options.hint || (
        state === 'scanning'
          ? 'Preparing the UPI QR for scan.'
          : state === 'processing'
            ? 'Collect request sent. Approve the payment to continue.'
            : state === 'success'
              ? 'Payment approved successfully.'
              : 'Scan using any UPI app or enter your UPI ID below.'
      );
      upiQrHintNode.textContent = hintLabel;
    }

    if (upiStatusNode) {
      upiStatusNode.textContent = options.status || getUpiStatusMessage();
      upiStatusNode.className = 'upi-sim-status'
        + (state === 'scanning' || state === 'processing' ? ' is-processing' : '')
        + (state === 'success' ? ' is-success' : '');
    }

    updatePlaceOrderButton();
  }

  function sanitizeUpiId(value) {
    return String(value || '').trim().toLowerCase().replace(/\s+/g, '');
  }

  function isValidUpiId(value) {
    return /^[a-z0-9._-]{2,}@[a-z][a-z0-9.-]{1,}$/i.test(value);
  }

  function sanitizeCardholderName(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().slice(0, 60);
  }

  function sanitizeCardNumber(value) {
    return String(value || '').replace(/\D/g, '').slice(0, 16);
  }

  function formatCardNumber(value) {
    return sanitizeCardNumber(value).replace(/(\d{4})(?=\d)/g, '$1 ').trim();
  }

  function sanitizeExpiry(value) {
    const digits = String(value || '').replace(/\D/g, '').slice(0, 4);

    if (digits.length <= 2) {
      return digits;
    }

    return digits.slice(0, 2) + '/' + digits.slice(2);
  }

  function sanitizeCvv(value) {
    return String(value || '').replace(/\D/g, '').slice(0, 3);
  }

  function detectCardType(cardNumber) {
    const digits = sanitizeCardNumber(cardNumber);

    if (/^4/.test(digits)) {
      return 'Visa';
    }

    if (/^(5[1-5]|2[2-7])/.test(digits)) {
      return 'Mastercard';
    }

    if (/^6/.test(digits)) {
      return 'RuPay';
    }

    return 'Card';
  }

  function cardPreviewLabel(cardNumber) {
    const digits = sanitizeCardNumber(cardNumber);

    if (digits.length < 4) {
      return 'Enter your card details to preview the masked payment method.';
    }

    return `${detectCardType(digits)} ending in ${digits.slice(-4)}`;
  }

  function isValidExpiry(value) {
    const normalized = sanitizeExpiry(value);

    if (!/^\d{2}\/\d{2}$/.test(normalized)) {
      return false;
    }

    const [monthText, yearText] = normalized.split('/');
    const month = Number(monthText);
    const year = Number(yearText);

    if (month < 1 || month > 12) {
      return false;
    }

    const now = new Date();
    const currentYear = now.getFullYear() % 100;
    const currentMonth = now.getMonth() + 1;

    if (year < currentYear) {
      return false;
    }

    if (year === currentYear && month < currentMonth) {
      return false;
    }

    return true;
  }

  function createSimulatedCardReference() {
    return 'CARDTXN' + Math.floor(100000 + Math.random() * 900000);
  }

  function getUpiIdMessage() {
    if (!upiIdInput) {
      return '';
    }

    const normalized = sanitizeUpiId(upiIdInput.value);

    if (!normalized) {
      return 'UPI ID is required';
    }

    if (!isValidUpiId(normalized)) {
      return 'Enter a valid UPI ID like name@bank';
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
      message = 'Enter a valid UPI ID like name@bank';
    } else if (forceRequirement && !normalized) {
      message = 'UPI ID is required';
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
      }
    } else if (paymentType === 'card') {
      if (cardSimulationStatus === 'processing') {
        label = BUTTON_LABELS.cardProcessing;
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
            <p>Choose your UPI app or enter your UPI ID to continue with a secure UPI payment.</p>
          </div>
          <div class="upi-qr-button" id="upi-qr-button" aria-label="UPI QR code" role="img">
            <div class="upi-qr-shell">
              <div class="upi-qr upi-qr-visual" aria-hidden="true">
                <img class="upi-qr-static" src="images/payments/upi-qr-static.svg" alt="UPI QR code">
              </div>
            </div>
          </div>
        </div>
        <div class="upi-app-badges" aria-label="Supported UPI apps">
          <span class="upi-app-badge">Google Pay</span>
          <span class="upi-app-badge">PhonePe</span>
          <span class="upi-app-badge">Paytm</span>
          <span class="upi-app-badge">BHIM</span>
        </div>
        <div class="upi-manual-group">
          <label for="upi-id-input">UPI ID</label>
          <input id="upi-id-input" name="upi_id" type="text" inputmode="email" placeholder="name@bank" autocomplete="off">
          <div class="field-error" id="upi-id-error" aria-live="polite"></div>
        </div>
        <div class="upi-sim-meta">
          <div class="upi-sim-row">
            <span>Payment Method</span>
            <strong>UPI Gateway</strong>
          </div>
          <div class="upi-sim-row">
            <span>Payee</span>
            <strong>${UPI_PAYEE_NAME}</strong>
          </div>
          <div class="upi-sim-row">
            <span>Total payable</span>
            <strong id="upi-payable-amount">₹0</strong>
          </div>
        </div>
        <div class="upi-sim-status" id="upi-sim-status" aria-live="polite"></div>
        <div class="upi-processing-panel" id="upi-processing-panel" hidden>
          <div class="upi-processing-heading">
            <h5 id="upi-processing-title">Preparing payment</h5>
            <p>Please keep this page open while your UPI payment is being processed.</p>
          </div>
          <div class="upi-processing-steps">
            <div class="upi-processing-step" data-step="0">Displaying QR for scan...</div>
            <div class="upi-processing-step" data-step="1">Scanning UPI QR...</div>
            <div class="upi-processing-step" data-step="2">Connecting to UPI app...</div>
            <div class="upi-processing-step" data-step="3">Confirming payment...</div>
          </div>
        </div>
        <div class="upi-success-panel" id="upi-success-panel" hidden>
          <h5>Payment Successful</h5>
          <div class="upi-success-row">
            <span>Amount paid</span>
            <strong id="upi-success-amount">₹0</strong>
          </div>
          <div class="upi-success-row">
            <span>UPI transaction reference</span>
            <strong id="upi-success-reference">CIBOUPI000000</strong>
          </div>
          <div class="upi-success-row">
            <span>Paid at</span>
            <strong id="upi-success-time">--</strong>
          </div>
        </div>
      </div>
    `;

    const cardGrid = document.createElement('div');
    cardGrid.className = 'form-grid';
    cardGrid.style.display = 'none';
    cardGrid.style.gridColumn = 'span 2';
    cardGrid.innerHTML = `
        <div class="card-sim-card">
        <div class="card-sim-header">
          <div>
            <h4>Card Payment</h4>
            <p>Enter your card details to continue with payment.</p>
          </div>
          <div class="card-brand-badges" aria-label="Supported cards">
            <span class="card-brand-badge">Visa</span>
            <span class="card-brand-badge">Mastercard</span>
            <span class="card-brand-badge">RuPay</span>
          </div>
        </div>
        <div class="card-sim-meta">
          <div class="upi-sim-row">
            <span>Payment Processor</span>
            <strong>Card Payment</strong>
          </div>
          <div class="upi-sim-row">
            <span>Security</span>
            <strong>Secure Payment</strong>
          </div>
          <div class="upi-sim-row">
            <span>Total payable</span>
            <strong id="card-payable-amount">₹0</strong>
          </div>
        </div>
        <div class="form-grid card-form-grid">
          <div class="form-group full">
            <label for="card-holder-name">Cardholder Name</label>
            <input id="card-holder-name" type="text" autocomplete="cc-name" placeholder="Name on card">
            <div class="field-error" data-error-for="cardholder"></div>
          </div>
          <div class="form-group full">
            <label for="card-number">Card Number</label>
            <input id="card-number" type="text" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456">
            <div class="field-error" data-error-for="cardnumber"></div>
          </div>
          <div class="form-group">
            <label for="card-expiry">Expiry Date</label>
            <input id="card-expiry" type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY">
            <div class="field-error" data-error-for="cardexpiry"></div>
          </div>
          <div class="form-group">
            <label for="card-cvv">CVV</label>
            <div class="password-field">
              <input id="card-cvv" type="password" inputmode="numeric" autocomplete="off" placeholder="123" maxlength="3">
              <button
                type="button"
                class="password-toggle"
                data-password-toggle="card-cvv"
                aria-label="Show CVV"
                aria-pressed="false"
              >
                <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58"></path><path d="M9.88 5.09A9.77 9.77 0 0112 5c5 0 9.27 3.11 11 7- .08.19-.08.41 0 .6a12.72 12.72 0 01-4.24 5.11"></path><path d="M6.61 6.61A12.24 12.24 0 001 12c1.73 3.89 6 7 11 7a10.8 10.8 0 005.39-1.39"></path></svg>
                <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
            <div class="field-error" data-error-for="cardcvv"></div>
          </div>
        </div>
        <div class="card-preview-row">
          <span>Masked payment preview</span>
          <strong id="card-preview-label">Enter your card details to preview the masked payment method.</strong>
        </div>
        <div class="card-sim-status full" id="card-sim-status" aria-live="polite"></div>
        <div class="card-processing-panel" id="card-processing-panel" hidden>
          <div class="upi-processing-heading">
            <h5 id="card-processing-title">Preparing payment</h5>
            <p>Please keep this page open while your payment is being processed.</p>
          </div>
          <div class="upi-processing-steps">
            <div class="card-processing-step" data-card-step="0">Verifying card details...</div>
            <div class="card-processing-step" data-card-step="1">Processing payment...</div>
            <div class="card-processing-step" data-card-step="2">Confirming transaction...</div>
            <div class="card-processing-step" data-card-step="3">Completing payment...</div>
          </div>
        </div>
        <div class="card-otp-panel" id="card-otp-panel" hidden>
          <h5>OTP Verification</h5>
          <p>Enter OTP sent to your registered mobile number.</p>
          <input id="card-otp-input" type="password" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" maxlength="6">
          <div class="field-error" id="card-otp-error" aria-live="polite"></div>
          <button type="button" class="otp-verify-btn" id="card-otp-verify-btn">Verify OTP</button>
        </div>
        <div class="card-success-panel" id="card-success-panel" hidden>
          <h5>Payment Successful</h5>
          <div class="upi-success-row">
            <span>Amount paid</span>
            <strong id="card-success-amount">₹0</strong>
          </div>
          <div class="upi-success-row">
            <span>Paid using</span>
            <strong id="card-success-masked">Visa ending in 0000</strong>
          </div>
          <div class="upi-success-row">
            <span>Transaction reference</span>
            <strong id="card-success-reference">CARDTXN000000</strong>
          </div>
          <div class="upi-success-row">
            <span>Paid at</span>
            <strong id="card-success-time">--</strong>
          </div>
        </div>
      </div>
    `;

    paymentFieldsWrap.appendChild(upiGroup);
    paymentFieldsWrap.appendChild(cardGrid);
    paymentOptions.appendChild(paymentFieldsWrap);

    upiSimCard = paymentFieldsWrap.querySelector('#upi-sim-card');
    upiQrButton = paymentFieldsWrap.querySelector('#upi-qr-button');
    upiPayableAmountNode = paymentFieldsWrap.querySelector('#upi-payable-amount');
    upiIdInput = paymentFieldsWrap.querySelector('#upi-id-input');
    upiIdErrorNode = paymentFieldsWrap.querySelector('#upi-id-error');
    upiStatusNode = paymentFieldsWrap.querySelector('#upi-sim-status');
    upiQrStateBadgeNode = paymentFieldsWrap.querySelector('#upi-qr-state-badge');
    upiQrHintNode = paymentFieldsWrap.querySelector('#upi-qr-hint');
    upiProcessingPanel = paymentFieldsWrap.querySelector('#upi-processing-panel');
    upiProcessingTitleNode = paymentFieldsWrap.querySelector('#upi-processing-title');
    upiProcessingStepNodes = Array.from(paymentFieldsWrap.querySelectorAll('.upi-processing-step'));
    upiSuccessPanel = paymentFieldsWrap.querySelector('#upi-success-panel');
    upiSuccessAmountNode = paymentFieldsWrap.querySelector('#upi-success-amount');
    upiSuccessReferenceNode = paymentFieldsWrap.querySelector('#upi-success-reference');
    upiSuccessTimeNode = paymentFieldsWrap.querySelector('#upi-success-time');
    cardStatusNode = paymentFieldsWrap.querySelector('#card-sim-status');
    cardHolderInput = paymentFieldsWrap.querySelector('#card-holder-name');
    cardNumberInput = paymentFieldsWrap.querySelector('#card-number');
    cardExpiryInput = paymentFieldsWrap.querySelector('#card-expiry');
    cardCvvInput = paymentFieldsWrap.querySelector('#card-cvv');
    cardPreviewNode = paymentFieldsWrap.querySelector('#card-preview-label');
    cardAmountNode = paymentFieldsWrap.querySelector('#card-payable-amount');
    cardProcessingPanel = paymentFieldsWrap.querySelector('#card-processing-panel');
    cardProcessingTitleNode = paymentFieldsWrap.querySelector('#card-processing-title');
    cardProcessingStepNodes = Array.from(paymentFieldsWrap.querySelectorAll('.card-processing-step'));
    cardOtpPanel = paymentFieldsWrap.querySelector('#card-otp-panel');
    cardOtpInput = paymentFieldsWrap.querySelector('#card-otp-input');
    cardOtpErrorNode = paymentFieldsWrap.querySelector('#card-otp-error');
    cardOtpVerifyButton = paymentFieldsWrap.querySelector('#card-otp-verify-btn');
    cardSuccessPanel = paymentFieldsWrap.querySelector('#card-success-panel');
    cardSuccessAmountNode = paymentFieldsWrap.querySelector('#card-success-amount');
    cardSuccessMaskedNode = paymentFieldsWrap.querySelector('#card-success-masked');
    cardSuccessReferenceNode = paymentFieldsWrap.querySelector('#card-success-reference');
    cardSuccessTimeNode = paymentFieldsWrap.querySelector('#card-success-time');

    paymentFieldsWrap._upiGroup = upiGroup;
    paymentFieldsWrap._cardGrid = cardGrid;

    paymentFields.push(
      {
        type: 'card',
        element: cardHolderInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="cardholder"]'),
        sanitize: sanitizeCardholderName,
        validate: (value) => {
          const normalized = sanitizeCardholderName(value);

          if (!normalized) {
            return 'Cardholder name is required';
          }

          if (normalized.length < 3) {
            return 'Enter the full name shown on the card';
          }

          return '';
        }
      },
      {
        type: 'card',
        element: cardNumberInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="cardnumber"]'),
        sanitize: formatCardNumber,
        validate: (value) => {
          const normalized = sanitizeCardNumber(value);

          if (!normalized) {
            return 'Card number is required';
          }

          if (normalized.length < 16) {
            return 'Card number must be 16 digits';
          }

          return '';
        }
      },
      {
        type: 'card',
        element: cardExpiryInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="cardexpiry"]'),
        sanitize: sanitizeExpiry,
        validate: (value) => {
          const normalized = sanitizeExpiry(value);

          if (!normalized) {
            return 'Expiry date is required';
          }

          if (!isValidExpiry(normalized)) {
            return 'Enter a valid future expiry date';
          }

          return '';
        }
      },
      {
        type: 'card',
        element: cardCvvInput,
        errorNode: paymentFieldsWrap.querySelector('[data-error-for="cardcvv"]'),
        sanitize: sanitizeCvv,
        validate: (value) => {
          const normalized = sanitizeCvv(value);

          if (!normalized) {
            return 'CVV is required';
          }

          if (normalized.length !== 3) {
            return 'CVV must be 3 digits';
          }

          return '';
        }
      }
    );

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

  function updateCardPayableAmount(amount = 0) {
    if (cardAmountNode) {
      cardAmountNode.textContent = formatPrice(Number(amount) || 0);
    }
  }

  function hideCardProcessPanels() {
    if (cardProcessingPanel) {
      cardProcessingPanel.hidden = true;
    }

    if (cardOtpPanel) {
      cardOtpPanel.hidden = true;
    }

    if (cardSuccessPanel) {
      cardSuccessPanel.hidden = true;
    }
  }

  function setCardProcessingStage(activeStep, title) {
    if (cardProcessingTitleNode && title) {
      cardProcessingTitleNode.textContent = title;
    }

    cardProcessingStepNodes.forEach((node, index) => {
      node.classList.remove('is-active', 'is-complete');

      if (index < activeStep) {
        node.classList.add('is-complete');
      } else if (index === activeStep) {
        node.classList.add('is-active');
      }
    });
  }

  function updateCardPreview() {
    if (cardPreviewNode) {
      cardPreviewNode.textContent = cardPreviewLabel(cardNumberInput?.value || '');
    }
  }

  function clearCardOtpState() {
    if (cardOtpInput) {
      cardOtpInput.value = '';
    }

    if (cardOtpErrorNode) {
      cardOtpErrorNode.textContent = '';
    }
  }

  function storeSimulatedCardPaymentResult(details) {
    sessionStorage.setItem('cibo_simulated_payment', JSON.stringify({
      method: 'card',
      reference: String(details?.reference || ''),
      amount: Number(details?.amount || 0),
      paid_at: String(details?.paidAt || ''),
      masked_card: String(details?.maskedCard || '')
    }));
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

    hideCardProcessPanels();
    clearCardOtpState();
    updateCardPreview();

    updatePlaceOrderButton();
  }

  function hideUpiProcessPanels() {
    if (upiProcessingPanel) {
      upiProcessingPanel.hidden = true;
    }

    if (upiSuccessPanel) {
      upiSuccessPanel.hidden = true;
    }
  }

  function setUpiProcessingStage(activeStep, title) {
    if (upiProcessingTitleNode && title) {
      upiProcessingTitleNode.textContent = title;
    }

    upiProcessingStepNodes.forEach((node, index) => {
      node.classList.remove('is-active', 'is-complete');

      if (index < activeStep) {
        node.classList.add('is-complete');
      } else if (index === activeStep) {
        node.classList.add('is-active');
      }
    });
  }

  function createSimulatedUpiReference() {
    return 'CIBOUPI' + Math.floor(100000 + Math.random() * 900000);
  }

  function formatSimulatedPaymentTimestamp() {
    return new Date().toLocaleString('en-IN', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit'
    });
  }

  function storeSimulatedPaymentResult(details) {
    sessionStorage.setItem('cibo_simulated_payment', JSON.stringify({
      method: 'upi',
      reference: String(details?.reference || ''),
      amount: Number(details?.amount || 0),
      paid_at: String(details?.paidAt || '')
    }));
  }

  function setCheckoutInteractionDisabled(disabled) {
    const controls = Array.from(form.querySelectorAll('input, textarea, select, button'));
    form.classList.toggle('is-busy', disabled);

    controls.forEach((control) => {
      if (!(control instanceof HTMLElement)) {
        return;
      }

      if (disabled) {
        control.dataset.wasDisabled = control.disabled ? '1' : '0';
        control.disabled = true;
        return;
      }

      const wasDisabled = control.dataset.wasDisabled === '1';
      delete control.dataset.wasDisabled;
      control.disabled = wasDisabled;
    });
  }

  function resetUpiSimulation() {
    upiQrScanned = false;

    if (upiVerificationTimer) {
      window.clearTimeout(upiVerificationTimer);
      upiVerificationTimer = null;
    }

    hideUpiProcessPanels();

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

    setUpiVisualState('idle');
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
    } else {
      setUpiVisualState(upiSimulationStatus === 'success' ? 'success' : 'idle');
    }

    if (paymentType !== 'card') {
      resetCardSimulation();
    } else if (cardStatusNode) {
      updateCardPreview();
      cardStatusNode.textContent = getCardStatusMessage();
      cardStatusNode.className = `card-sim-status full${cardSimulationStatus === 'processing' ? ' is-processing' : ''}`;
    }

    updatePlaceOrderButton();
  }

  function validateForm() {
    const baseValid = Object.values(fields).every(validateField);
    const paymentType = getSelectedPaymentType();
    const activePaymentFields = paymentFields.filter((field) => field.type === paymentType && field.type !== 'upi');
    const paymentValid = activePaymentFields.every(validateField);
    const upiValid = paymentType === 'upi' ? setUpiIdState(true) : true;

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
      currentUserEmail = String(account.email || '').trim();

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
            <div class="order-item-main">
              ${item.image ? `<img class="order-item-thumb" src="${escapeHtml(item.image)}" alt="${escapeHtml(item.imageAlt || item.name)}">` : ''}
              <span>${escapeHtml(item.name)} x ${quantity}</span>
            </div>
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
    updateCardPayableAmount(Number(safeSummary.total) || 0);
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

    return completeSuccessfulOrder(serverOrder);
  }

  function completeSuccessfulOrder(order) {
    const orderNumber = String(order?.order_number || '');

    if (!orderNumber) {
      throw new Error('Order was created, but the confirmation number was missing.');
    }

    persistOrderUiState();
    clearPlacedOrderState();
    setFormMessage('Order placed successfully. Redirecting...', 'success');
    window.location.href = 'success.php?order=' + encodeURIComponent(orderNumber);

    return order;
  }

  async function runPrepaidCheckout(payload, paymentType) {
    if (paymentType === 'upi') {
      upiSimulationStatus = 'processing';

      if (upiStatusNode) {
        upiStatusNode.textContent = getUpiStatusMessage();
        upiStatusNode.className = 'upi-sim-status is-processing';
      }
    } else {
      cardSimulationStatus = 'processing';

      if (cardStatusNode) {
        cardStatusNode.textContent = getCardStatusMessage();
        cardStatusNode.className = 'card-sim-status full is-processing';
      }
    }

    updateSubmitState();

    try {
      await placeOrder(payload);
    } catch (error) {
      if (paymentType === 'upi' && upiStatusNode) {
        upiStatusNode.textContent = 'UPI payment was not completed. Your cart is still ready.';
        upiStatusNode.className = 'upi-sim-status';
      }

      if (paymentType === 'card' && cardStatusNode) {
      cardStatusNode.textContent = 'Card payment was not completed. Your cart is still ready.';
        cardStatusNode.className = 'card-sim-status full';
      }

      throw error;
    } finally {
      if (paymentType === 'upi') {
        upiSimulationStatus = 'idle';
      } else {
        cardSimulationStatus = 'idle';
      }

      updateSubmitState();
    }
  }

  async function runUpiVerification(payload) {
    if (upiSimulationStatus === 'processing') {
      return;
    }

    if (!setUpiIdState(true)) {
      setFormMessage('Please enter a valid UPI ID before continuing.', 'error');
      return;
    }

    const orderSummary = readSummary();
    const totalAmount = Number(orderSummary?.total_amount ?? orderSummary?.total ?? 0) || 0;

      setUpiVisualState('scanning', {
        badge: 'Displaying QR',
        hint: 'Opening the scan experience for your selected UPI app.',
        status: 'Showing your UPI QR...'
      });
    hideUpiProcessPanels();
    setCheckoutInteractionDisabled(true);

    if (upiProcessingPanel) {
      upiProcessingPanel.hidden = false;
    }

    updateSubmitState();

    try {
      setUpiProcessingStage(0, 'Displaying QR');
      await wait(2000);

      setUpiVisualState('scanning', {
        badge: 'Scanning QR',
        hint: 'Scanning the QR just like a UPI app camera would.',
        status: 'Scanning UPI QR...'
      });
      setUpiProcessingStage(1, 'Scanning UPI QR');
      await wait(2500);

      setUpiVisualState('processing', {
        badge: 'Opening app',
        hint: 'Connecting the QR request to your selected UPI app.',
        status: 'Connecting to UPI app...'
      });
      setUpiProcessingStage(2, 'Connecting to UPI app');
      await wait(2500);

      setUpiVisualState('processing', {
        badge: 'Awaiting approval',
        hint: 'Approve the collect request in your UPI app.',
        status: 'Waiting for approval...'
      });
      setUpiProcessingStage(3, 'Waiting for approval');
      await wait(5000);

      const simulatedPayment = {
        reference: createSimulatedUpiReference(),
        amount: totalAmount,
        paidAt: formatSimulatedPaymentTimestamp()
      };

      const serverOrder = await createServerOrder(payload);
      storeSimulatedPaymentResult(simulatedPayment);

      hideUpiProcessPanels();

      if (upiSuccessPanel) {
        upiSuccessPanel.hidden = false;
      }

      if (upiSuccessAmountNode) {
        upiSuccessAmountNode.textContent = formatPrice(simulatedPayment.amount);
      }

      if (upiSuccessReferenceNode) {
        upiSuccessReferenceNode.textContent = simulatedPayment.reference;
      }

      if (upiSuccessTimeNode) {
        upiSuccessTimeNode.textContent = simulatedPayment.paidAt;
      }

      setUpiVisualState('success', {
        badge: 'Paid',
        hint: 'Payment approved successfully. Completing your order now.',
        status: 'UPI payment approved. Redirecting to your order confirmation...'
      });

      await wait(1600);
      completeSuccessfulOrder(serverOrder);
    } catch (error) {
      setUpiVisualState('idle', {
        status: 'UPI payment was not completed. Your cart is still ready.'
      });
      hideUpiProcessPanels();
      throw error;
    } finally {
      upiSimulationStatus = 'idle';
      setCheckoutInteractionDisabled(false);
      updateSubmitState();
    }
  }

  async function runCardVerification(payload) {
    if (cardSimulationStatus === 'processing') {
      return;
    }

    const cardNumber = sanitizeCardNumber(cardNumberInput?.value || '');
    const maskedCard = `${detectCardType(cardNumber)} ending in ${cardNumber.slice(-4)}`;
    const orderSummary = readSummary();
    const totalAmount = Number(orderSummary?.total_amount ?? orderSummary?.total ?? 0) || 0;

    cardSimulationStatus = 'processing';
    hideCardProcessPanels();
    clearCardOtpState();
    setCheckoutInteractionDisabled(true);

    if (cardProcessingPanel) {
      cardProcessingPanel.hidden = false;
    }

    if (cardStatusNode) {
      cardStatusNode.textContent = 'Processing your card payment...';
      cardStatusNode.className = 'card-sim-status full is-processing';
    }

    updateSubmitState();

    try {
      setCardProcessingStage(0, 'Verifying card details');
      await wait(2000);
      setCardProcessingStage(1, 'Processing payment');
      await wait(2500);
      setCardProcessingStage(2, 'Confirming transaction');
      await wait(3500);

      if (cardProcessingPanel) {
        cardProcessingPanel.hidden = true;
      }

      if (cardOtpPanel) {
        cardOtpPanel.hidden = false;
      }

      setCheckoutInteractionDisabled(false);
      updateSubmitState();

      if (cardOtpInput) {
        cardOtpInput.focus();
      }

      const otpValid = await new Promise((resolve) => {
        const attemptVerify = () => {
          const otp = String(cardOtpInput?.value || '').trim();

          if (otp !== '123456') {
            if (cardOtpErrorNode) {
              cardOtpErrorNode.textContent = otp === '' ? 'OTP is required' : 'Incorrect OTP. Use demo OTP 123456.';
            }
            resolve(false);
            return;
          }

          if (cardOtpErrorNode) {
            cardOtpErrorNode.textContent = '';
          }

          resolve(true);
        };

        if (!cardOtpInput) {
          resolve(false);
          return;
        }

        const handleKeydown = (event) => {
          if (event.key === 'Enter') {
            event.preventDefault();
            cleanup();
            attemptVerify();
          }
        };
        const handleClick = () => {
          cleanup();
          attemptVerify();
        };

        const cleanup = () => {
          cardOtpInput.removeEventListener('keydown', handleKeydown);
          cardOtpVerifyButton?.removeEventListener('click', handleClick);
        };

        cardOtpInput.addEventListener('keydown', handleKeydown);
        cardOtpVerifyButton?.addEventListener('click', handleClick);
      });

      if (!otpValid) {
        throw new Error('Card payment could not be completed with the provided OTP.');
      }

      setCheckoutInteractionDisabled(true);

      if (cardOtpPanel) {
        cardOtpPanel.hidden = true;
      }

      if (cardProcessingPanel) {
        cardProcessingPanel.hidden = false;
      }

      setCardProcessingStage(3, 'Completing payment');
      await wait(1600);

      const simulatedPayment = {
        reference: createSimulatedCardReference(),
        amount: totalAmount,
        paidAt: formatSimulatedPaymentTimestamp(),
        maskedCard
      };

      const serverOrder = await createServerOrder(payload);
      storeSimulatedCardPaymentResult(simulatedPayment);

      hideCardProcessPanels();

      if (cardSuccessPanel) {
        cardSuccessPanel.hidden = false;
      }

      if (cardSuccessAmountNode) {
        cardSuccessAmountNode.textContent = formatPrice(simulatedPayment.amount);
      }

      if (cardSuccessMaskedNode) {
        cardSuccessMaskedNode.textContent = simulatedPayment.maskedCard;
      }

      if (cardSuccessReferenceNode) {
        cardSuccessReferenceNode.textContent = simulatedPayment.reference;
      }

      if (cardSuccessTimeNode) {
        cardSuccessTimeNode.textContent = simulatedPayment.paidAt;
      }

      if (cardStatusNode) {
        cardStatusNode.textContent = 'Simulated card payment approved. Redirecting to your order confirmation...';
        cardStatusNode.className = 'card-sim-status full is-success';
      }

      if (cardCvvInput) {
        cardCvvInput.value = '';
      }

      await wait(1600);
      completeSuccessfulOrder(serverOrder);
    } catch (error) {
      if (cardStatusNode) {
        cardStatusNode.textContent = error instanceof Error ? error.message : 'Card payment was not completed. Your cart is still ready.';
        cardStatusNode.className = 'card-sim-status full';
      }

      hideCardProcessPanels();
      throw error;
    } finally {
      cardSimulationStatus = 'idle';
      clearCardOtpState();
      setCheckoutInteractionDisabled(false);
      updateSubmitState();
    }
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

  if (upiIdInput) {
    upiIdInput.addEventListener('input', () => {
      setUpiIdState(false);
      if (getSelectedPaymentType() === 'upi' && upiSimulationStatus !== 'processing' && upiSimulationStatus !== 'scanning') {
        setUpiVisualState('idle');
      }
      persistCheckoutDraft();
      updateSubmitState();
    });

    upiIdInput.addEventListener('blur', () => {
      setUpiIdState(Boolean(upiIdInput.value.trim()));
      persistCheckoutDraft();
      updateSubmitState();
    });
  }

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

    if (!validateForm()) {
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
        image: item.image || '',
        restaurant: item.restaurant || items[0]?.restaurant || 'Cibo Order',
        restaurantSlug: item.restaurantSlug || items[0]?.restaurantSlug || '',
        restaurantPage: item.restaurantPage || items[0]?.restaurantPage || ''
      }))
    };

    try {
      orderSubmissionInFlight = true;
      updateSubmitState();

      if (paymentType === 'upi') {
        await runUpiVerification(payload);
        return;
      }

      if (paymentType === 'card') {
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
