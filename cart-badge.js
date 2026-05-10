(() => {
  const STORAGE_KEY = 'cibo_cart';
  const CART_TEXT = 'cart';
  const ADMIN_HREF = 'admin/login.php';

  function readCart() {
    try {
      const savedCart = localStorage.getItem(STORAGE_KEY);
      const parsedCart = savedCart ? JSON.parse(savedCart) : {};
      return parsedCart && typeof parsedCart === 'object' ? parsedCart : {};
    } catch (error) {
      return {};
    }
  }

  function getCartCount() {
    return Object.values(readCart()).reduce((total, item) => total + (Number(item.quantity) || 0), 0);
  }

  function getCartTargets() {
    return Array.from(document.querySelectorAll('.nav-item')).filter((item) =>
      item.textContent.trim().toLowerCase().includes(CART_TEXT)
    );
  }

  function ensureAdminFooterLink() {
    const footerBottom = document.querySelector('.footer-bottom');

    if (!footerBottom || footerBottom.querySelector('.admin-link')) {
      return;
    }

    const adminLink = document.createElement('a');
    adminLink.href = ADMIN_HREF;
    adminLink.className = 'admin-link';
    adminLink.textContent = 'Admin';
    adminLink.style.color = 'var(--muted)';
    adminLink.style.fontSize = '0.85rem';
    adminLink.style.textDecoration = 'none';
    adminLink.style.transition = 'color 0.25s ease';

    adminLink.addEventListener('mouseenter', () => {
      adminLink.style.color = 'var(--accent)';
    });

    adminLink.addEventListener('mouseleave', () => {
      adminLink.style.color = 'var(--muted)';
    });

    const adminWrap = document.createElement('div');
    adminWrap.style.marginTop = '10px';
    adminWrap.appendChild(adminLink);

    footerBottom.appendChild(adminWrap);
  }

  function getBadge(target) {
    let badge = target.querySelector('.cibo-cart-count');

    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'cibo-cart-count';
      badge.style.display = 'none';
      badge.style.minWidth = '20px';
      badge.style.height = '18px';
      badge.style.padding = '0 5px';
      badge.style.borderRadius = '999px';
      badge.style.background = '#6f8f3a';
      badge.style.color = '#ffffff';
      badge.style.border = 'none';
      badge.style.fontSize = '10px';
      badge.style.fontWeight = '800';
      badge.style.lineHeight = '1';
      badge.style.letterSpacing = '0';
      badge.style.textAlign = 'center';
      badge.style.marginLeft = '2px';
      badge.style.display = 'inline-flex';
      badge.style.alignItems = 'center';
      badge.style.justifyContent = 'center';
      badge.style.flexShrink = '0';
      badge.style.verticalAlign = 'baseline';
      badge.style.position = 'relative';
      badge.style.top = '0';
      badge.style.boxShadow = 'none';
      target.appendChild(badge);
    }

    return badge;
  }

  function renderCartCount() {
    const count = getCartCount();

    getCartTargets().forEach((target) => {
      const badge = getBadge(target);

      if (count > 0) {
        badge.textContent = String(count);
        badge.style.display = 'inline-flex';
      } else {
        badge.textContent = '';
        badge.style.display = 'none';
      }
    });
  }

  window.addEventListener('storage', (event) => {
    if (event.key === STORAGE_KEY) {
      renderCartCount();
    }
  });

  window.addEventListener('cibo-cart-updated', renderCartCount);

  ensureAdminFooterLink();
  renderCartCount();
})();
