(() => {
  const steps = Array.from(document.querySelectorAll('.track-step'));
  const statusValue = document.getElementById('track-status-label');
  const orderIdValue = document.getElementById('track-order-id');
  const deliveryTimeValue = document.getElementById('track-delivery-time');
  const markDeliveredButton = document.getElementById('mark-delivered-btn');
  const cartLink = document.querySelector('.nav-right a[href="cart.php"]');
  const continueOrderingLink = document.querySelector('.track-btn.secondary');
  const STORAGE_KEY = 'cart';
  const CART_KEY = 'cibo_cart';
  const CART_COUNT_KEY = 'cartCount';
  const LAST_ORDER_KEY = 'cibo_last_order';
  const ORDER_STATUS_KEY = 'orderStatus';
  const stages = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered'];
  const SIMULATION_STEP_DELAY_MS = 4000;
  const restaurantPages = {
    'Burger King': 'burgerking.php',
    "Domino's": 'dominos.php',
    'Pizza Hut': 'pizza-hut.php',
    Paradise: 'paradise.php',
    Meghana: 'meghana.php',
    Empire: 'empire.php',
    EatFit: 'eatfit.php',
    FreshMenu: 'freshmenu.php',
    'Chinese Wok': 'chinese-wok.php',
    'Mainland China': 'mainland-china.php',
    'Punjab Grill': 'punjab-grill.php',
    Udupi: 'udupi.php',
    Vidyarthi: 'vidyarthi.php',
    'Corner House': 'corner-house.php',
    'Polar Bear': 'polar-bear.php',
    'Hae Kum Gang': 'hae-kum-gang.php'
  };

  if (!steps.length) {
    return;
  }

  let currentStep = 0;
  let simulationTimer = null;

  function getOrderNumberFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('order') || '';
  }

  function readCart() {
    try {
      const savedCart = localStorage.getItem(STORAGE_KEY);
      const parsedCart = savedCart ? JSON.parse(savedCart) : {};
      return parsedCart && typeof parsedCart === 'object' ? parsedCart : {};
    } catch (error) {
      return {};
    }
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

  function updateStatus(step, statusLabel = stages[step] || 'Pending') {
    currentStep = Math.max(0, Math.min(stages.length - 1, Number(step) || 0));
    const completedUntil = currentStep;

    steps.forEach((item, index) => {
      item.classList.remove('completed', 'current');

      if (index < completedUntil) {
        item.classList.add('completed');
      }

      if (index === completedUntil) {
        item.classList.add('current');
      }
    });

    if (statusValue) {
      statusValue.textContent = statusLabel;
    }

    if (deliveryTimeValue) {
      deliveryTimeValue.textContent = currentStep === stages.length - 1 ? 'Delivered' : '25-30 mins';
    }
  }

  function getStepIndexFromStatus(status) {
    const normalizedStatus = String(status || '').trim().toLowerCase();

    switch (normalizedStatus) {
      case 'delivered':
        return 3;
      case 'out_for_delivery':
      case 'out for delivery':
        return 2;
      case 'preparing':
        return 1;
      case 'pending':
      default:
        return 0;
    }
  }

  function clearSimulationTimer() {
    if (simulationTimer) {
      window.clearTimeout(simulationTimer);
      simulationTimer = null;
    }
  }

  function startSimulation(stepIndex) {
    clearSimulationTimer();

    const safeStep = Math.max(0, Math.min(stages.length - 1, Number(stepIndex) || 0));

    if (safeStep >= stages.length - 1) {
      return;
    }

    simulationTimer = window.setTimeout(() => {
      const nextStep = safeStep + 1;
      updateStatus(nextStep, stages[nextStep]);
      startSimulation(nextStep);
    }, SIMULATION_STEP_DELAY_MS);
  }

  function initializeOrderMeta() {
    const orderNumber = getOrderNumberFromUrl();

    if (orderIdValue && orderNumber) {
      orderIdValue.textContent = '#' + orderNumber;
    }

    if (markDeliveredButton) {
      markDeliveredButton.style.display = 'none';
    }
  }

  async function loadOrderStatus() {
    const orderNumber = getOrderNumberFromUrl();

    if (!orderNumber || !window.CiboOrdersApi) {
      updateStatus(0, 'Pending');
      return;
    }

    try {
      const response = await window.CiboOrdersApi.get(orderNumber);
      const order = response?.order || {};
      const orderStatus = String(order.order_status || 'pending');
      const orderStatusLabel = String(order.order_status_label || stages[getStepIndexFromStatus(orderStatus)] || 'Pending');
      const stepIndex = getStepIndexFromStatus(orderStatus);

      updateStatus(stepIndex, orderStatusLabel);
      startSimulation(stepIndex);
    } catch (error) {
      clearSimulationTimer();
      updateStatus(0, 'Pending');
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

  document.addEventListener('DOMContentLoaded', () => {
    initializeOrderMeta();
    loadOrderStatus();
  });
})();
