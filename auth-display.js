(() => {
  const USER_KEY = 'cibo_user';
  const ACCOUNT_KEY = 'cibo_account';
  const ACCOUNT_URL = 'myaccount.php';
  const LOGIN_URL = 'login.php';
  const authItem = document.querySelector('.nav-right .nav-item');

  if (!authItem) {
    return;
  }

  function clearLegacyAuthStorage() {
    localStorage.removeItem(USER_KEY);
    localStorage.removeItem(ACCOUNT_KEY);
  }

  async function fetchCurrentUser() {
    const response = await fetch('api/account.php', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json'
      }
    });

    const data = await response.json().catch(() => ({}));

    if (response.status === 401) {
      clearLegacyAuthStorage();
      return null;
    }

    if (!response.ok || data.success === false || !data.user || typeof data.user !== 'object') {
      throw new Error(data.message || 'Unable to load account state right now.');
    }

    return data.user;
  }

  function renderLoggedIn(user) {
    const authIcon = authItem.querySelector('svg');
    const authText = authItem.querySelector('span');
    const label = String(user?.name || '').trim() || 'User';
    const dropdown = document.createElement('div');
    const profileLink = document.createElement('a');
    const logoutButton = document.createElement('button');

    authItem.href = '#';
    authItem.style.position = 'relative';
    authItem.setAttribute('aria-label', 'Profile menu');
    authItem.removeAttribute('title');

    if (authIcon) {
      authIcon.style.display = 'none';
    }

    if (authText) {
      authText.style.display = '';
      authText.textContent = 'Hi, ' + label;
    }

    dropdown.style.position = 'absolute';
    dropdown.style.top = 'calc(100% + 18px)';
    dropdown.style.right = '0';
    dropdown.style.minWidth = '190px';
    dropdown.style.padding = '10px';
    dropdown.style.background = '#fffdf9';
    dropdown.style.border = '1px solid #e7dfd3';
    dropdown.style.borderRadius = '18px';
    dropdown.style.boxShadow = '0 18px 34px rgba(0, 0, 0, 0.12)';
    dropdown.style.display = 'none';
    dropdown.style.flexDirection = 'column';
    dropdown.style.gap = '6px';
    dropdown.style.zIndex = '1005';

    profileLink.href = ACCOUNT_URL;
    profileLink.textContent = 'Profile';
    profileLink.style.display = 'flex';
    profileLink.style.alignItems = 'center';
    profileLink.style.minHeight = '44px';
    profileLink.style.padding = '0 14px';
    profileLink.style.border = 'none';
    profileLink.style.borderRadius = '14px';
    profileLink.style.background = 'transparent';
    profileLink.style.textDecoration = 'none';
    profileLink.style.fontFamily = 'Manrope, sans-serif';
    profileLink.style.fontSize = '15px';
    profileLink.style.fontWeight = '800';
    profileLink.style.color = '#1f1f1b';

    logoutButton.type = 'button';
    logoutButton.textContent = 'Logout';
    logoutButton.style.minHeight = '44px';
    logoutButton.style.padding = '0 14px';
    logoutButton.style.border = 'none';
    logoutButton.style.borderRadius = '14px';
    logoutButton.style.background = 'transparent';
    logoutButton.style.textAlign = 'left';
    logoutButton.style.fontFamily = 'Manrope, sans-serif';
    logoutButton.style.fontSize = '15px';
    logoutButton.style.fontWeight = '800';
    logoutButton.style.color = '#1f1f1b';
    logoutButton.style.cursor = 'pointer';

    [profileLink, logoutButton].forEach((item) => {
      item.addEventListener('mouseenter', () => {
        item.style.background = '#f6f1e8';
        item.style.color = '#5f7c3a';
      });

      item.addEventListener('mouseleave', () => {
        item.style.background = 'transparent';
        item.style.color = '#1f1f1b';
      });
    });

    logoutButton.addEventListener('click', (event) => {
      event.preventDefault();
      fetch('api/user-logout.php', {
        method: 'POST',
        credentials: 'same-origin'
      }).catch(() => null).finally(() => {
        clearLegacyAuthStorage();
        window.location.href = 'index.php';
      });
    });

    authItem.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
    });

    dropdown.addEventListener('click', (event) => {
      event.stopPropagation();
    });

    document.addEventListener('click', () => {
      dropdown.style.display = 'none';
    });

    dropdown.appendChild(profileLink);
    dropdown.appendChild(logoutButton);
    authItem.appendChild(dropdown);
  }

  function renderLoggedOut() {
    const authIcon = authItem.querySelector('svg');
    const authText = authItem.querySelector('span');

    if (authIcon) {
      authIcon.style.display = '';
    }

    if (authText) {
      authText.textContent = 'Sign In';
      authText.style.display = '';
    }

    authItem.setAttribute('aria-label', 'Login');
    authItem.removeAttribute('title');

    if (authItem.tagName !== 'A') {
      authItem.addEventListener('click', () => {
        window.location.href = LOGIN_URL;
      });

      authItem.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          window.location.href = LOGIN_URL;
        }
      });

      authItem.tabIndex = 0;
    }
  }

  fetchCurrentUser()
    .then((user) => {
      if (user) {
        renderLoggedIn(user);
        return;
      }

      renderLoggedOut();
    })
    .catch(() => {
      renderLoggedOut();
    });
})();
