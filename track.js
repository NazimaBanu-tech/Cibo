(() => {
  const steps = Array.from(document.querySelectorAll('.track-step'));
  const statusValue = document.getElementById('track-status-label');
  const orderIdValue = document.getElementById('track-order-id');
  const deliveryTimeValue = document.getElementById('track-delivery-time');
  const paymentStatusValue = document.getElementById('track-payment-status');
  const cancelOrderButton = document.getElementById('track-cancel-button');
  const feedbackNode = document.getElementById('track-feedback');
  const cancelModal = document.getElementById('track-cancel-modal');
  const keepOrderButton = document.getElementById('track-cancel-keep');
  const confirmCancelButton = document.getElementById('track-cancel-confirm');
  const cartLink = document.querySelector('.nav-right a[href="cart.php"]');
  const continueOrderingLink = document.querySelector('.track-btn.secondary');
  const stepsContainer = document.querySelector('.track-steps');
  const trackCard = document.querySelector('.track-card');
  const LAST_ORDER_KEY = 'cibo_last_order';
  const STATIC_ETA_LABEL = 'Estimated delivery: 25-35 min';
  const TRACK_REFRESH_MS = 4000;
  const cartManager = window.CiboCartManager;
  const UI_STATUS_CONFIG = {
    placed: {
      label: 'Order Received',
      step: 0,
      emotion: 'Your food is being prepared fresh',
      stepTitle: 'Order Received',
      stepCopy: 'Your kitchen ticket is in and the Cibo team is getting started.'
    },
    preparing: {
      label: 'Chef is Preparing Your Food',
      step: 1,
      emotion: 'Chef is adding final touch',
      stepTitle: 'Chef at Work',
      stepCopy: 'Ingredients are coming together fresh and warm for your table.'
    },
    out_for_delivery: {
      label: 'On the Way to You',
      step: 2,
      emotion: 'Packed safely for delivery',
      stepTitle: 'On the Way',
      stepCopy: 'Your order is packed, protected, and heading to your doorstep.'
    },
    delivered: {
      label: 'Delivered with Care',
      step: 3,
      emotion: 'Your Cibo moment has arrived',
      stepTitle: 'Delivered',
      stepCopy: 'Everything has arrived and is ready to enjoy.'
    },
    cancelled: {
      label: 'Order Cancelled',
      step: 0,
      emotion: 'This order is no longer active',
      stepTitle: 'Order Cancelled',
      stepCopy: 'This order was cancelled before it could continue through the journey.'
    }
  };
  function slugify(value) {
    return String(value || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function buildRestaurantHref(name) {
    return `menu.php?restaurant=${encodeURIComponent(slugify(name))}`;
  }

  const restaurantPages = {
    "McDonald's": buildRestaurantHref("McDonald's"),
    'Burger King': buildRestaurantHref('Burger King'),
    "Domino's": buildRestaurantHref("Domino's"),
    'Pizza Hut': buildRestaurantHref('Pizza Hut'),
    Paradise: buildRestaurantHref('Paradise'),
    Meghana: buildRestaurantHref('Meghana'),
    'Meghana Foods': buildRestaurantHref('Meghana Foods'),
    Empire: buildRestaurantHref('Empire'),
    EatFit: buildRestaurantHref('EatFit'),
    FreshMenu: buildRestaurantHref('FreshMenu'),
    'Chinese Wok': buildRestaurantHref('Chinese Wok'),
    'Mainland China': buildRestaurantHref('Mainland China'),
    'Punjab Grill': buildRestaurantHref('Punjab Grill'),
    Udupi: buildRestaurantHref('Udupi'),
    Vidyarthi: buildRestaurantHref('Vidyarthi'),
    'Vidyarthi Bhavan': buildRestaurantHref('Vidyarthi Bhavan'),
    'Corner House': buildRestaurantHref('Corner House'),
    'Polar Bear': buildRestaurantHref('Polar Bear'),
    'Hae Kum Gang': buildRestaurantHref('Hae Kum Gang')
  };

  if (!steps.length) {
    return;
  }

  let progressFill = null;
  let emotionNode = null;
  let activeOrder = null;
  let isCancelling = false;
  let isLoadingOrder = false;
  let refreshTimerId = 0;
  let lastFocusedElement = null;

  function getOrderNumberFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('order') || '';
  }

  function normalizeStatus(status) {
    const normalizedStatus = String(status || '').trim().toLowerCase();

    if (normalizedStatus === 'out for delivery') {
      return 'out_for_delivery';
    }

    if (normalizedStatus === 'pending') {
      return 'placed';
    }

    return normalizedStatus || 'placed';
  }

  function getStatusConfig(status, backendLabel = '') {
    const normalizedStatus = normalizeStatus(status);
    const config = UI_STATUS_CONFIG[normalizedStatus];

    if (config) {
      return config;
    }

    return {
      label: String(backendLabel || normalizedStatus || '--').trim() || '--',
      step: 0,
      emotion: 'Your order is being reviewed by the Cibo team',
      stepTitle: 'Order Update',
      stepCopy: 'We are reflecting the latest status shared by the backend.'
    };
  }

  function readCart() {
    return cartManager ? cartManager.getCart() : {};
  }

  function readLastOrder() {
    try {
      const savedOrder = localStorage.getItem(LAST_ORDER_KEY);
      const parsedOrder = savedOrder ? JSON.parse(savedOrder) : {};
      return parsedOrder && typeof parsedOrder === 'object' ? parsedOrder : {};
    } catch (error) {
      return {};
    }
  }

  function hasCartItems() {
    return Object.values(readCart()).some((item) => Number(item.quantity) > 0);
  }

  function ensureEmotionNode() {
    if (emotionNode || !statusValue) {
      return;
    }

    emotionNode = document.createElement('div');
    emotionNode.className = 'track-status-emotion';
    emotionNode.textContent = 'We are syncing the latest kitchen update for you.';
    statusValue.insertAdjacentElement('afterend', emotionNode);
  }

  function ensureProgressFill() {
    if (progressFill || !stepsContainer) {
      return;
    }

    progressFill = document.createElement('div');
    progressFill.className = 'track-progress-fill';
    stepsContainer.appendChild(progressFill);
  }

  function setFeedback(message, type = '') {
    if (!feedbackNode) {
      return;
    }

    feedbackNode.textContent = String(message || '').trim();
    feedbackNode.className = 'track-feedback' + (type ? ` ${type}` : '');
    feedbackNode.style.display = message ? 'block' : 'none';
  }

  function canCancelOrder(order) {
    return normalizeStatus(order?.order_status || '') === 'placed';
  }

  function isCancelModalOpen() {
    return Boolean(cancelModal?.classList.contains('is-open'));
  }

  function closeCancelModal() {
    if (!cancelModal) {
      return;
    }

    cancelModal.classList.remove('is-open');
    cancelModal.setAttribute('aria-hidden', 'true');

    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  }

  function openCancelModal() {
    if (!cancelModal || !canCancelOrder(activeOrder) || isCancelling) {
      return;
    }

    lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    cancelModal.classList.add('is-open');
    cancelModal.setAttribute('aria-hidden', 'false');
    window.setTimeout(() => {
      if (keepOrderButton instanceof HTMLElement) {
        keepOrderButton.focus();
      }
    }, 0);
  }

  function clearRefreshTimer() {
    if (!refreshTimerId) {
      return;
    }

    window.clearTimeout(refreshTimerId);
    refreshTimerId = 0;
  }

  function shouldKeepRefreshing(order) {
    const normalizedStatus = normalizeStatus(order?.order_status || '');
    return normalizedStatus !== 'cancelled' && normalizedStatus !== 'delivered';
  }

  function scheduleStatusRefresh() {
    clearRefreshTimer();

    if (!shouldKeepRefreshing(activeOrder)) {
      return;
    }

    refreshTimerId = window.setTimeout(() => {
      if (document.visibilityState === 'hidden') {
        scheduleStatusRefresh();
        return;
      }

      loadOrderStatus();
    }, TRACK_REFRESH_MS);
  }

  function syncCancelButton(order) {
    if (!cancelOrderButton) {
      return;
    }

    const shouldShow = canCancelOrder(order);
    cancelOrderButton.style.display = shouldShow ? 'inline-flex' : 'none';
    cancelOrderButton.disabled = isCancelling;
    cancelOrderButton.textContent = isCancelling ? 'Cancelling...' : 'Cancel Order';

    if (!shouldShow && isCancelModalOpen()) {
      closeCancelModal();
    }
  }

  function getCancellationErrorMessage(error) {
    const safeMessage = String(error?.message || '').trim();

    if (/only newly placed orders can be cancelled/i.test(safeMessage)) {
      return 'This order has already progressed and can no longer be cancelled.';
    }

    if (/unable to find the order/i.test(safeMessage) || /order not found/i.test(safeMessage)) {
      return 'We could not find that order anymore. Please refresh the tracking page.';
    }

    if (!navigator.onLine) {
      return 'Network connection lost. Please check your internet and try again.';
    }

    return safeMessage || 'Unable to cancel the order right now. Please try again.';
  }

  function setProgressFill(step) {
    if (!progressFill) {
      return;
    }

    const normalizedStep = Math.max(0, Math.min(steps.length - 1, Number(step) || 0));
    const percent = steps.length <= 1 ? 0 : (normalizedStep / (steps.length - 1)) * 76;
    progressFill.style.width = `${percent}%`;
  }

  function updateStepCopy(normalizedStatus) {
    const config = getStatusConfig(normalizedStatus);

    steps.forEach((stepNode, index) => {
      const titleNode = stepNode.querySelector('h3');
      const copyNode = stepNode.querySelector('p');

      if (!titleNode || !copyNode) {
        return;
      }

      if (index === config.step) {
        titleNode.textContent = config.stepTitle;
        copyNode.textContent = config.stepCopy;
        return;
      }

      if (index === 0) {
        titleNode.textContent = 'Order Placed';
        copyNode.textContent = 'Your order has been confirmed and received successfully.';
      } else if (index === 1) {
        titleNode.textContent = 'Preparing';
        copyNode.textContent = 'The restaurant is preparing your food fresh for delivery.';
      } else if (index === 2) {
        titleNode.textContent = 'Out for Delivery';
        copyNode.textContent = 'Your order will be handed to the delivery partner next.';
      } else if (index === 3) {
        titleNode.textContent = 'Delivered';
        copyNode.textContent = 'Your order will be marked delivered once it reaches you.';
      }
    });
  }

  function updateStatus(status, backendLabel = '') {
    const normalizedStatus = normalizeStatus(status);
    const config = getStatusConfig(normalizedStatus, backendLabel);
    const currentStep = Math.max(0, Math.min(steps.length - 1, Number(config.step) || 0));
    const isCancelled = normalizedStatus === 'cancelled';

    if (trackCard) {
      trackCard.classList.toggle('is-cancelled', isCancelled);
    }

    steps.forEach((item, index) => {
      item.classList.remove('completed', 'current', 'is-upcoming');

      if (!isCancelled && index < currentStep) {
        item.classList.add('completed');
      } else if (index === currentStep) {
        item.classList.add('current');
      } else {
        item.classList.add('is-upcoming');
      }
    });

    updateStepCopy(normalizedStatus);
    setProgressFill(currentStep);

    if (statusValue) {
      statusValue.textContent = config.label;
    }

    if (emotionNode) {
      emotionNode.textContent = config.emotion;
    }

    if (deliveryTimeValue) {
      deliveryTimeValue.textContent = isCancelled ? 'Order Cancelled' : STATIC_ETA_LABEL;
    }

    syncCancelButton(activeOrder);
  }

  function initializeOrderMeta() {
    const orderNumber = getOrderNumberFromUrl();

    ensureEmotionNode();
    ensureProgressFill();

    if (trackCard) {
      trackCard.classList.add('is-cinematic');
    }

    if (orderIdValue && orderNumber) {
      orderIdValue.textContent = '#' + orderNumber;
    }

    if (deliveryTimeValue) {
      deliveryTimeValue.textContent = STATIC_ETA_LABEL;
    }
  }

  async function loadOrderStatus() {
    const orderNumber = getOrderNumberFromUrl();

    if (!orderNumber || !window.CiboOrdersApi || isLoadingOrder) {
      updateStatus('placed', '--');
      return;
    }

    try {
      isLoadingOrder = true;
      const response = await window.CiboOrdersApi.get(orderNumber);
      const order = response?.order || {};
      activeOrder = order;
      const orderStatus = String(order.order_status || 'placed');
      const orderStatusLabel = String(order.order_status_label || orderStatus || '--').trim() || '--';
      const paymentStatusLabel = String(order.payment_status_label || order.payment_status || '--').trim() || '--';

      updateStatus(orderStatus, orderStatusLabel);
      if (paymentStatusValue) {
        paymentStatusValue.textContent = paymentStatusLabel;
      }
      scheduleStatusRefresh();
    } catch (error) {
      activeOrder = null;
      clearRefreshTimer();
      updateStatus('placed', '--');
      syncCancelButton(null);
    } finally {
      isLoadingOrder = false;
    }
  }

  if (cartLink) {
    cartLink.addEventListener('click', (event) => {
      event.preventDefault();
      window.location.href = hasCartItems() ? 'cart.php' : 'empty.php';
    });
  }

  if (continueOrderingLink) {
    continueOrderingLink.addEventListener('click', (event) => {
      event.preventDefault();

      const lastOrder = readLastOrder();
      const firstItem = Object.values(lastOrder)[0];
      const restaurantPage = String(firstItem?.restaurantPage || restaurantPages[firstItem?.restaurant] || '').trim();

      window.location.href = restaurantPage || 'index.php';
    });
  }

  if (cancelOrderButton) {
    cancelOrderButton.addEventListener('click', () => {
      openCancelModal();
    });
  }

  if (keepOrderButton) {
    keepOrderButton.addEventListener('click', () => {
      closeCancelModal();
    });
  }

  if (cancelModal) {
    cancelModal.addEventListener('click', (event) => {
      if (event.target === cancelModal) {
        closeCancelModal();
      }
    });
  }

  if (confirmCancelButton) {
    confirmCancelButton.addEventListener('click', async () => {
      const orderNumber = String(activeOrder?.order_number || getOrderNumberFromUrl() || '').trim();

      if (!orderNumber || !canCancelOrder(activeOrder) || !window.CiboOrdersApi || isCancelling) {
        return;
      }

      try {
        isCancelling = true;
        setFeedback('', '');
        syncCancelButton(activeOrder);
        if (confirmCancelButton instanceof HTMLButtonElement) {
          confirmCancelButton.disabled = true;
          confirmCancelButton.textContent = 'Cancelling...';
        }
        if (keepOrderButton instanceof HTMLButtonElement) {
          keepOrderButton.disabled = true;
        }
        await window.CiboOrdersApi.cancel(orderNumber);
        closeCancelModal();
        await loadOrderStatus();
        setFeedback('Order cancelled successfully.', 'success');
      } catch (error) {
        setFeedback(getCancellationErrorMessage(error), 'error');
      } finally {
        isCancelling = false;
        if (confirmCancelButton instanceof HTMLButtonElement) {
          confirmCancelButton.disabled = false;
          confirmCancelButton.textContent = 'Confirm Cancel';
        }
        if (keepOrderButton instanceof HTMLButtonElement) {
          keepOrderButton.disabled = false;
        }
        syncCancelButton(activeOrder);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initializeOrderMeta();
    syncCancelButton(activeOrder);
    loadOrderStatus();
  });

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && shouldKeepRefreshing(activeOrder)) {
      loadOrderStatus();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isCancelModalOpen() && !isCancelling) {
      closeCancelModal();
    }
  });
})();
