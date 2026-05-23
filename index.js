(() => {
  const RESTAURANTS_KEY = 'restaurants';
  const categoryButtons = Array.from(document.querySelectorAll('.category'));
  const restaurantsSection = document.querySelector('.restaurants');

  if (!restaurantsSection) {
    return;
  }

  let activeCategory = 'all';
  let currentRestaurants = [];

  function readJSON(key, fallback) {
    try {
      const rawValue = localStorage.getItem(key);
      return rawValue ? JSON.parse(rawValue) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function saveJSON(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function normalize(value) {
    return String(value || '').trim().toLowerCase();
  }

  function slugify(value) {
    return String(value || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function buildRestaurantHref(restaurant) {
    return String(restaurant?.href || '').trim() || 'menu.php?restaurant=' + encodeURIComponent(restaurant.slug || slugify(restaurant.name));
  }

  async function fetchCanonicalRestaurants() {
    const response = await fetch('api/catalog.php', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json'
      }
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false || !Array.isArray(data.restaurants)) {
      throw new Error(data.message || 'Unable to load restaurants right now.');
    }

    saveJSON(RESTAURANTS_KEY, data.restaurants);
    localStorage.setItem('menuItems', JSON.stringify(Array.isArray(data.menu_items) ? data.menu_items : []));
    return data.restaurants.map(normalizeRestaurantRecord);
  }

  function extractRestaurantsFromDom() {
    return Array.from(restaurantsSection.querySelectorAll('.restaurant-card')).map((card) => ({
      name: card.querySelector('h3')?.textContent.trim() || 'Restaurant',
      image: card.querySelector('img')?.getAttribute('src') || '',
      cuisines: card.querySelector('.cuisine')?.textContent.trim() || '',
      location: card.querySelector('.location')?.textContent.trim() || '',
      ratingMeta: card.querySelector('.rating-time')?.textContent.replace(/\s+/g, ' ').trim() || '',
      category: card.dataset.category || '',
      href: card.getAttribute('href') || ''
    }));
  }

  function normalizeRestaurantRecord(restaurant) {
    const name = String(restaurant?.name || 'Restaurant').trim() || 'Restaurant';
    const slug = slugify(restaurant?.slug || name);
    const cuisines = String(restaurant?.cuisines || '').trim();
    const category = normalize(restaurant?.category || cuisines.split(',')[0] || 'food');
    const image = String(restaurant?.image || '').trim();
    const heroImage = String(restaurant?.heroImage || '').trim();

    return {
      id: String(restaurant?.id || `restaurant-${slug}`).trim(),
      slug,
      name,
      image,
      heroImage,
      location: String(restaurant?.location || '').trim(),
      category,
      cuisines,
      ratingMeta: String(restaurant?.ratingMeta || '').trim(),
      href: buildRestaurantHref({ ...restaurant, slug, name })
    };
  }

  function getRestaurantCategories(restaurant) {
    const pieces = [
      ...String(restaurant.category || '')
        .split(',')
        .map((part) => normalize(part)),
      ...String(restaurant.cuisines || '')
        .split(',')
        .map((part) => normalize(part))
    ].filter(Boolean);

    const expandedPieces = pieces.flatMap((piece) => {
      const aliases = [piece];
      if (piece === 'burger') aliases.push('burgers');
      if (piece === 'burgers') aliases.push('burger');
      if (piece === 'dessert') aliases.push('desserts');
      if (piece === 'desserts') aliases.push('dessert');
      if (piece === 'salad') aliases.push('salads');
      if (piece === 'salads') aliases.push('salad');
      return aliases;
    });

    return Array.from(new Set(expandedPieces));
  }

  function ensureRestaurantsStorage() {
    const storedRestaurants = readJSON(RESTAURANTS_KEY, []);
    const domRestaurants = extractRestaurantsFromDom();
    const mergedRestaurants = [...domRestaurants, ...storedRestaurants]
      .map(normalizeRestaurantRecord)
      .reduce((collection, restaurant) => {
        const key = restaurant.slug || slugify(restaurant.name);
        if (!collection.has(key)) {
          collection.set(key, restaurant);
          return collection;
        }

        const existing = collection.get(key);
        collection.set(key, {
          ...existing,
          ...restaurant,
          image: restaurant.image || existing.image,
          heroImage: restaurant.heroImage || existing.heroImage || '',
          location: restaurant.location || existing.location,
          cuisines: restaurant.cuisines || existing.cuisines,
          ratingMeta: restaurant.ratingMeta || existing.ratingMeta,
          category: restaurant.category || existing.category,
          href: restaurant.href || existing.href
        });
        return collection;
      }, new Map());

    const normalizedRestaurants = Array.from(mergedRestaurants.values());
    saveJSON(RESTAURANTS_KEY, normalizedRestaurants);
    return normalizedRestaurants;
  }

  function renderRestaurants(restaurants) {
    currentRestaurants = restaurants.map((restaurant) => ({
      ...restaurant,
      categories: getRestaurantCategories(restaurant)
    }));

    restaurantsSection.innerHTML = currentRestaurants.map((restaurant) => `
      <a href="${escapeHtml(buildRestaurantHref(restaurant))}" class="restaurant-card" data-category="${escapeHtml(restaurant.categories[0] || '')}">
        ${restaurant.image ? `<img src="${escapeHtml(restaurant.image)}" alt="${escapeHtml(restaurant.name)}">` : ''}
        <div class="card-content">
          <h3>${escapeHtml(restaurant.name)}</h3>
          <div class="rating-time">${escapeHtml(restaurant.ratingMeta || '4.3 • 25-30 mins')}</div>
          <div class="cuisine">${escapeHtml(restaurant.cuisines || restaurant.category || '')}</div>
          <div class="location">${escapeHtml(restaurant.location || '')}</div>
        </div>
      </a>
    `).join('');
  }

  function initFromDom() {
    const domRestaurants = extractRestaurantsFromDom();
    if (domRestaurants.length === 0) {
      return;
    }
    currentRestaurants = domRestaurants.map((restaurant) => ({
      ...normalizeRestaurantRecord(restaurant),
      categories: getRestaurantCategories(normalizeRestaurantRecord(restaurant))
    }));
  }

  function applyCategoryView() {
    Array.from(restaurantsSection.querySelectorAll('.restaurant-card')).forEach((card, index) => {
      const restaurant = currentRestaurants[index];
      if (!restaurant) {
        return;
      }
      const visible = activeCategory === 'all' || restaurant.categories.includes(activeCategory);
      card.style.display = visible ? '' : 'none';
    });
  }

  categoryButtons.forEach((categoryButton) => {
    const categoryName = categoryButton.dataset.category || categoryButton.querySelector('p')?.textContent.trim() || '';
    categoryButton.dataset.category = normalize(categoryName);

    categoryButton.addEventListener('click', () => {
      activeCategory = categoryButton.dataset.category || 'all';
      categoryButtons.forEach((button) => {
        button.classList.toggle('active', button === categoryButton);
      });
      applyCategoryView();
    });
  });

  // Step 1: immediately register the existing HTML cards so category filtering
  // works right away without waiting for the API response.
  initFromDom();

  // Step 2: fetch canonical data from the API in the background.
  // Only replace the DOM if the API returns a non-empty list of restaurants.
  // If the API fails or returns nothing, the static HTML cards remain visible.
  fetchCanonicalRestaurants()
    .then((restaurants) => {
      if (Array.isArray(restaurants) && restaurants.length > 0) {
        renderRestaurants(restaurants);
        applyCategoryView();
      }
    })
    .catch(() => {
      // API unavailable — persist what is already in the DOM to localStorage
      // so search.js and other scripts can use it, but do NOT touch the DOM.
      ensureRestaurantsStorage();
    });
})();
