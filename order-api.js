(() => {
  function createRequestId() {
    return 'cibo-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function clearLegacyAuthStorage() {
    localStorage.removeItem('cibo_user');
    localStorage.removeItem('cibo_account');
  }

  async function request(url, options = {}) {
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
      const error = new Error(data.message || 'Request failed.');
      error.ciboAuthError = response.status === 401 || response.status === 403 || /log[\s-]?in|not authenticated/i.test(String(data.message || ''));

      if (error.ciboAuthError) {
        clearLegacyAuthStorage();
      }

      throw error;
    }

    return data;
  }

  window.CiboOrdersApi = {
    create(payload) {
      return request('api/orders.php', {
        method: 'POST',
        body: JSON.stringify(payload)
      });
    },
    listMine() {
      return request('api/orders.php', {
        method: 'GET',
        cache: 'no-store',
        headers: {
          Accept: 'application/json',
          'Cache-Control': 'no-store, no-cache, must-revalidate',
          Pragma: 'no-cache'
        }
      });
    },
    get(orderNumber) {
      const search = orderNumber ? '?order=' + encodeURIComponent(orderNumber) : '';
      return request('api/order.php' + search, {
        method: 'GET',
        cache: 'no-store',
        headers: {
          Accept: 'application/json',
          'Cache-Control': 'no-store, no-cache, must-revalidate',
          Pragma: 'no-cache'
        }
      });
    },
    cancel(orderNumber) {
      return request('api/orders.php', {
        method: 'POST',
        body: JSON.stringify({
          action: 'cancel',
          order_number: orderNumber
        })
      });
    }
  };
})();
