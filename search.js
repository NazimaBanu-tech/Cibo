document.addEventListener('DOMContentLoaded', () => {
  const RESTAURANTS_KEY = 'restaurants';
  const searchBox = document.querySelector('.search-box');
  const searchInput = document.querySelector('.search-box input');

  if (!searchBox || !searchInput) {
    return;
  }

  const DEFAULT_RESTAURANTS = [
    { name: "McDonald's", href: 'menu.php?restaurant=mcdonalds', category: 'burgers', cuisines: 'Burgers, Fast Food', location: 'Rajajinagar' },
    { name: 'Burger King', href: 'menu.php?restaurant=burger-king', category: 'burgers', cuisines: 'Burgers, Fast Food', location: 'Koramangala' },
    { name: "Domino's", href: 'menu.php?restaurant=dominos', category: 'pizza', cuisines: 'Pizza, Fast Food', location: 'BTM Layout' },
    { name: 'Pizza Hut', href: 'menu.php?restaurant=pizza-hut', category: 'pizza', cuisines: 'Pizza, Italian', location: 'Jayanagar' },
    { name: 'Meghana Foods', href: 'menu.php?restaurant=meghana', category: 'biryani', cuisines: 'Biryani, Andhra', location: 'Indiranagar' },
    { name: 'Paradise', href: 'menu.php?restaurant=paradise', category: 'biryani', cuisines: 'Biryani, Mughlai', location: 'MG Road' },
    { name: 'Chinese Wok', href: 'menu.php?restaurant=chinese-wok', category: 'chinese', cuisines: 'Chinese, Asian', location: 'HSR Layout' },
    { name: 'Mainland China', href: 'menu.php?restaurant=mainland-china', category: 'chinese', cuisines: 'Chinese, Asian', location: 'Church Street' },
    { name: 'Empire', href: 'menu.php?restaurant=empire', category: 'north indian', cuisines: 'North Indian, Kebabs', location: 'Kammanahalli' },
    { name: 'Punjab Grill', href: 'menu.php?restaurant=punjab-grill', category: 'north indian', cuisines: 'North Indian, Punjabi', location: 'Whitefield' },
    { name: 'Udupi', href: 'menu.php?restaurant=udupi', category: 'south indian', cuisines: 'South Indian, Breakfast', location: 'Basavanagudi' },
    { name: 'Vidyarthi Bhavan', href: 'menu.php?restaurant=vidyarthi', category: 'south indian', cuisines: 'South Indian, Dosa', location: 'Basavanagudi' },
    { name: 'Polar Bear', href: 'menu.php?restaurant=polar-bear', category: 'desserts', cuisines: 'Desserts, Ice Cream', location: 'Malleshwaram' },
    { name: 'Corner House', href: 'menu.php?restaurant=corner-house', category: 'desserts', cuisines: 'Desserts, Sundaes', location: 'Indiranagar' },
    { name: 'FreshMenu', href: 'menu.php?restaurant=freshmenu', category: 'salad', cuisines: 'Healthy, Salads', location: 'Bellandur' },
    { name: 'EatFit', href: 'menu.php?restaurant=eatfit', category: 'salad', cuisines: 'Healthy, Salads', location: 'Koramangala' },
    { name: 'Hae Kum Gang', href: 'menu.php?restaurant=hae-kum-gang', category: 'korean', cuisines: 'Korean, Asian', location: 'Residency Road' }
  ];

  const searchResults = document.createElement('div');
  searchResults.className = 'cibo-search-results';
  searchResults.style.display = 'none';
  searchResults.style.position = 'absolute';
  searchResults.style.top = 'calc(100% + 10px)';
  searchResults.style.left = '0';
  searchResults.style.right = '0';
  searchResults.style.background = '#fffdf9';
  searchResults.style.border = '1.5px solid #ddd4c8';
  searchResults.style.borderRadius = '18px';
  searchResults.style.boxShadow = '0 18px 34px rgba(0, 0, 0, 0.08)';
  searchResults.style.padding = '10px';
  searchResults.style.maxHeight = '420px';
  searchResults.style.overflowY = 'auto';
  searchResults.style.zIndex = '1002';

  searchBox.style.position = 'relative';
  searchBox.style.overflow = 'visible';
  searchBox.appendChild(searchResults);

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

  function buildRestaurantHref(restaurant) {
    const explicitHref = String(restaurant?.href || '').trim();

    if (explicitHref !== '' && !/^[a-z0-9-]+\.php$/i.test(explicitHref)) {
      return explicitHref;
    }

    const slug = slugify(restaurant?.slug || restaurant?.name || explicitHref.replace(/\.php$/i, ''));
    return `menu.php?restaurant=${encodeURIComponent(slug)}`;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function toTitleCase(value) {
    return String(value || '')
      .split(' ')
      .filter(Boolean)
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(' ');
  }

  function normalizeRestaurantRecord(restaurant) {
    const name = String(restaurant?.name || 'Restaurant').trim() || 'Restaurant';
    const slug = slugify(restaurant?.slug || restaurant?.name || restaurant?.href || name);
    const cuisines = String(restaurant?.cuisines || '').trim();
    const category = normalize(restaurant?.category || cuisines.split(',')[0] || 'food');
    const href = buildRestaurantHref({ ...restaurant, slug, name });
    const image = String(restaurant?.image || '').trim();
    const heroImage = String(restaurant?.heroImage || '').trim();

    return {
      id: String(restaurant?.id || `restaurant-${slug}`).trim(),
      slug,
      name,
      href,
      image,
      heroImage,
      location: String(restaurant?.location || restaurant?.address || '').trim(),
      category,
      cuisines
    };
  }

  function extractRestaurantsFromHomeDom() {
    return Array.from(document.querySelectorAll('.restaurant-card')).map((card) => ({
      name: card.querySelector('h3')?.textContent.trim() || '',
      href: card.getAttribute('href') || '',
      image: card.querySelector('img')?.getAttribute('src') || '',
      cuisines: card.querySelector('.cuisine')?.textContent.trim() || '',
      location: card.querySelector('.location')?.textContent.trim() || '',
      category: card.dataset.category || ''
    }));
  }

  function extractCurrentRestaurant() {
    const heroTitle = document.querySelector('.restaurant-hero-left h1');

    if (!heroTitle) {
      return null;
    }

    const metaParts = Array.from(document.querySelectorAll('.restaurant-meta span')).map((item) => item.textContent.trim());
    const cuisines = metaParts.find((part) => /[a-z]/i.test(part) && !/\d/.test(part.replace(/[^\d]/g, ''))) || '';
    const pathname = window.location.pathname.split('/').pop() || '';
    const query = window.location.search || '';

    return {
      name: heroTitle.textContent.trim(),
      href: pathname + query,
      image: '',
      heroImage: document.querySelector('.restaurant-hero-right img')?.getAttribute('src') || '',
      cuisines,
      location: document.querySelector('.restaurant-address')?.textContent.trim() || '',
      category: normalize((cuisines.split(',')[0] || '').trim())
    };
  }

  function ensureRestaurantsStorage() {
    const storedRestaurants = readJSON(RESTAURANTS_KEY, []);
    const mergedRestaurants = [...DEFAULT_RESTAURANTS, ...extractRestaurantsFromHomeDom()];
    const currentRestaurant = extractCurrentRestaurant();

    if (currentRestaurant) {
      mergedRestaurants.push(currentRestaurant);
    }

    mergedRestaurants.push(...storedRestaurants);

    const uniqueRestaurants = mergedRestaurants
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
          category: restaurant.category || existing.category,
          href: restaurant.href || existing.href
        });
        return collection;
      }, new Map());

    const normalizedRestaurants = Array.from(uniqueRestaurants.values());
    saveJSON(RESTAURANTS_KEY, normalizedRestaurants);
    return normalizedRestaurants;
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

  function getCategoryMatches(restaurants, query) {
    const categoryMap = new Map();

    restaurants.forEach((restaurant) => {
      getRestaurantCategories(restaurant).forEach((category) => {
        if (!category || !category.includes(query) || categoryMap.has(category)) {
          return;
        }

        categoryMap.set(category, {
          name: toTitleCase(category),
          type: 'Category',
          href: restaurant.href
        });
      });
    });

    return Array.from(categoryMap.values());
  }

  function renderSearchResults(items) {
    searchResults.innerHTML = '';

    if (!items.length) {
      const noResult = document.createElement('div');
      noResult.textContent = 'No results found';
      noResult.style.padding = '16px 18px';
      noResult.style.fontSize = '16px';
      noResult.style.fontWeight = '700';
      noResult.style.color = '#6f685f';
      searchResults.appendChild(noResult);
      searchResults.style.display = 'block';
      return;
    }

    items.forEach((item) => {
      const resultLink = document.createElement('a');
      resultLink.href = item.href;
      resultLink.style.display = 'block';
      resultLink.style.padding = '14px 16px';
      resultLink.style.borderRadius = '14px';
      resultLink.style.textDecoration = 'none';
      resultLink.style.color = 'inherit';
      resultLink.style.transition = 'background 0.2s ease';
      resultLink.innerHTML = `
        <div style="font-size:18px;font-weight:700;color:#171715;margin-bottom:4px;">${escapeHtml(item.name)}</div>
        <div style="font-size:14px;color:#6f685f;">${escapeHtml(item.type)}</div>
      `;

      resultLink.addEventListener('mouseenter', () => {
        resultLink.style.background = '#f6f1e8';
      });

      resultLink.addEventListener('mouseleave', () => {
        resultLink.style.background = 'transparent';
      });

      searchResults.appendChild(resultLink);
    });

    searchResults.style.display = 'block';
  }

  function hideSearchResults() {
    searchResults.style.display = 'none';
  }

  function showSearchResults(query) {
    const restaurants = ensureRestaurantsStorage();
    const restaurantMatches = restaurants
      .filter((restaurant) => {
        const name = normalize(restaurant.name);
        const location = normalize(restaurant.location);
        const cuisine = normalize(restaurant.cuisines || '');
        const categories = normalize(getRestaurantCategories(restaurant).join(' | '));
        return name.includes(query) || location.includes(query) || cuisine.includes(query) || categories.includes(query);
      })
      .map((restaurant) => ({
        name: restaurant.name,
        type: 'Restaurant',
        href: restaurant.href
      }));

    const uniqueRestaurantMatches = restaurantMatches.reduce((collection, item) => {
      if (!collection.some((existing) => existing.name === item.name && existing.href === item.href)) {
        collection.push(item);
      }
      return collection;
    }, []);

    renderSearchResults([...uniqueRestaurantMatches, ...getCategoryMatches(restaurants, query)]);
  }

  function applySearch() {
    const query = normalize(searchInput.value);
    if (!query) {
      hideSearchResults();
      return;
    }

    showSearchResults(query);
  }

  searchInput.addEventListener('input', applySearch);
  searchInput.addEventListener('focus', applySearch);
  searchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      hideSearchResults();
    }
  });

  document.addEventListener('click', (event) => {
    if (!searchBox.contains(event.target)) {
      hideSearchResults();
    }
  });

  ensureRestaurantsStorage();
});
