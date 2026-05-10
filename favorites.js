(() => {
  const FAVORITES_PREFIX = 'cibo_favorites::';
  const AUTH_EVENT = 'cibo-auth-state-updated';
  let currentUserKey = 'guest-user';

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

  function getStorageKey() {
    return FAVORITES_PREFIX + currentUserKey;
  }

  function emitUpdate() {
    const favorites = api.readFavorites();
    window.dispatchEvent(new CustomEvent('cibo-favorites-updated', {
      detail: favorites
    }));
  }

  async function resolveSessionUserKey() {
    try {
      const response = await fetch('api/account.php', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json'
        }
      });

      if (response.status === 401) {
        currentUserKey = 'guest-user';
        window.dispatchEvent(new CustomEvent(AUTH_EVENT, {
          detail: {
            authenticated: false,
            userKey: currentUserKey
          }
        }));
        emitUpdate();
        return currentUserKey;
      }

      const data = await response.json().catch(() => ({}));

      if (!response.ok || data.success === false || !data.user || typeof data.user !== 'object') {
        throw new Error(data.message || 'Unable to load the current session.');
      }

      const userId = String(data.user.id || '').trim();
      currentUserKey = userId || 'guest-user';
      window.dispatchEvent(new CustomEvent(AUTH_EVENT, {
        detail: {
          authenticated: currentUserKey !== 'guest-user',
          userKey: currentUserKey
        }
      }));
      emitUpdate();
      return currentUserKey;
    } catch (error) {
      currentUserKey = 'guest-user';
      window.dispatchEvent(new CustomEvent(AUTH_EVENT, {
        detail: {
          authenticated: false,
          userKey: currentUserKey
        }
      }));
      emitUpdate();
      return currentUserKey;
    }
  }

  function normalizeItem(entry) {
    const restaurantSlug = slugify(entry?.restaurantSlug || entry?.restaurantName || '');
    const itemId = entry?.id || ('menu-item-' + restaurantSlug + '-' + slugify(entry?.name || 'item'));
    return {
      id: itemId,
      restaurantId: entry?.restaurantId || ('restaurant-' + restaurantSlug),
      restaurantName: entry?.restaurantName || 'Restaurant',
      restaurantSlug,
      restaurantHref: entry?.restaurantHref || ('menu.php?restaurant=' + encodeURIComponent(restaurantSlug)),
      name: entry?.name || 'Menu Item',
      price: Number(entry?.price) || 0,
      image: entry?.image || '',
      description: entry?.description || '',
      foodType: entry?.foodType || '',
      tagText: entry?.tagText || ''
    };
  }

  function dedupe(entries, normalizeEntry) {
    const map = new Map();
    (Array.isArray(entries) ? entries : []).forEach((entry) => {
      const normalized = normalizeEntry(entry);
      map.set(normalized.id, normalized);
    });
    return Array.from(map.values());
  }

  const api = {
    readFavorites() {
      const rawFavorites = readJSON(getStorageKey(), { items: [] });
      return {
        restaurants: [],
        items: dedupe(rawFavorites?.items, normalizeItem)
      };
    },

    saveFavorites(nextFavorites) {
      const payload = {
        restaurants: [],
        items: dedupe(nextFavorites?.items, normalizeItem)
      };
      localStorage.setItem(getStorageKey(), JSON.stringify(payload));
      emitUpdate();
      return payload;
    },

    isItemFavorite(entry) {
      const id = typeof entry === 'string' ? entry : normalizeItem(entry).id;
      return api.readFavorites().items.some((item) => item.id === id);
    },

    toggleItem(entry) {
      const favorite = normalizeItem(entry);
      const favorites = api.readFavorites();
      const exists = favorites.items.some((item) => item.id === favorite.id);
      return api.saveFavorites({
        ...favorites,
        items: exists
          ? favorites.items.filter((item) => item.id !== favorite.id)
          : [...favorites.items, favorite]
      });
    },

    removeItem(entry) {
      const id = typeof entry === 'string' ? entry : normalizeItem(entry).id;
      const favorites = api.readFavorites();
      return api.saveFavorites({
        ...favorites,
        items: favorites.items.filter((item) => item.id !== id)
      });
    }
  };

  window.addEventListener('storage', (event) => {
    if (event.key === getStorageKey()) {
      emitUpdate();
    }
  });

  const ready = resolveSessionUserKey();

  window.CiboFavorites = api;
  window.CiboFavorites.ready = ready;
})();
