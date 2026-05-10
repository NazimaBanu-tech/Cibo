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
      error.ciboAuthError = response.status === 401 || response.status === 403 || /log[\s-]?in|access this account/i.test(String(data.message || ''));

      if (error.ciboAuthError) {
        clearLegacyAuthStorage();
      }

      throw error;
    }

    return data;
  }

  window.CiboAccountApi = {
    getProfile() {
      return request('api/account.php');
    },
    updateProfile(payload) {
      return request('api/account.php', {
        method: 'POST',
        body: JSON.stringify(payload)
      });
    },
    listAddresses() {
      return request('api/addresses.php');
    },
    saveAddress(payload) {
      return request('api/addresses.php', {
        method: 'POST',
        body: JSON.stringify(payload)
      });
    },
    deleteAddress(id) {
      return request('api/addresses.php', {
        method: 'DELETE',
        body: JSON.stringify({ id })
      });
    }
  };
})();
