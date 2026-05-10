(() => {
  const form = document.getElementById('signup-form');
  const submitButton = document.getElementById('signup-submit');
  const googleButton = document.querySelector('.secondary-btn');
  const formMessage = document.getElementById('signup-form-message');

  const nameInput = document.getElementById('signup-name');
  const emailInput = document.getElementById('signup-email');
  const phoneInput = document.getElementById('signup-phone');
  const passwordInput = document.getElementById('signup-password');
  const confirmPasswordInput = document.getElementById('signup-confirm-password');

  if (!form || !submitButton || !formMessage || !nameInput || !emailInput || !phoneInput || !passwordInput || !confirmPasswordInput) {
    return;
  }

  const defaultSubmitLabel = submitButton.textContent.trim() || 'Create Account';
  let isSubmitting = false;

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
    email: {
      element: emailInput,
      errorNode: form.querySelector('[data-error-for="email"]'),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Email is required';
        }

        if (!/^[a-zA-Z0-9._%+-]+@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/.test(normalized)) {
          return 'Enter a valid email address';
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
    password: {
      element: passwordInput,
      errorNode: form.querySelector('[data-error-for="password"]'),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Password is required';
        }

        if (normalized.length < 6) {
          return 'Password must be at least 6 characters';
        }

        return '';
      }
    },
    confirm_password: {
      element: confirmPasswordInput,
      errorNode: form.querySelector('[data-error-for="confirm_password"]'),
      validate: (value) => {
        const normalized = String(value || '').trim();

        if (!normalized) {
          return 'Confirm your password';
        }

        if (normalized !== passwordInput.value.trim()) {
          return 'Passwords do not match';
        }

        return '';
      }
    }
  };

  async function createServerUser(userRecord) {
    const response = await fetch('api/user-register.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(userRecord)
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false) {
      throw new Error(data.message || 'Unable to create the account right now.');
    }

    return data.user || null;
  }

  function setFormMessage(message, type = '') {
    formMessage.textContent = message;
    formMessage.className = 'auth-form-message' + (type ? ` ${type}` : '');
  }

  function setFieldState(fieldName, message) {
    const field = fields[fieldName];

    if (!field) {
      return false;
    }

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

  function getFieldMessage(fieldName) {
    const field = fields[fieldName];
    return field.validate(field.element.value);
  }

  function validateField(fieldName) {
    return setFieldState(fieldName, getFieldMessage(fieldName));
  }

  function validateForm() {
    return Object.keys(fields).every(validateField);
  }

  function updateSubmitState() {
    submitButton.disabled = isSubmitting;
    submitButton.toggleAttribute('aria-busy', isSubmitting);
    submitButton.textContent = isSubmitting ? 'Creating Account...' : defaultSubmitLabel;
  }

  Object.entries(fields).forEach(([fieldName, field]) => {
    field.element.addEventListener('input', () => {
      if (typeof field.sanitize === 'function') {
        field.element.value = field.sanitize(field.element.value);
      } else if (fieldName === 'email') {
        field.element.value = field.element.value.trimStart();
      }

      validateField(fieldName);

      if (fieldName === 'password' && confirmPasswordInput.value) {
        validateField('confirm_password');
      }

      setFormMessage('');
      updateSubmitState();
    });

    field.element.addEventListener('blur', () => {
      validateField(fieldName);

      if (fieldName === 'password' && confirmPasswordInput.value) {
        validateField('confirm_password');
      }

      updateSubmitState();
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
    toggleButton.addEventListener('click', () => {
      const target = document.getElementById(toggleButton.dataset.passwordToggle || '');

      if (!target) {
        return;
      }

      const showing = target.type === 'text';
      target.type = showing ? 'password' : 'text';
      toggleButton.setAttribute('aria-pressed', String(!showing));
      toggleButton.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
      toggleButton.classList.toggle('is-visible', !showing);
    });
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    setFormMessage('');

    if (isSubmitting) {
      return;
    }

    if (!validateForm()) {
      updateSubmitState();
      const passwordMessage = getFieldMessage('password');
      const confirmPasswordMessage = getFieldMessage('confirm_password');

      if (passwordMessage) {
        setFormMessage(passwordMessage, 'error');
        return;
      }

      if (confirmPasswordMessage) {
        setFormMessage(confirmPasswordMessage, 'error');
        return;
      }

      setFormMessage('Please correct the highlighted fields before creating your account.', 'error');
      return;
    }

    const email = emailInput.value.trim().toLowerCase();

    if (email === 'admin@cibo.local') {
      setFieldState('email', 'This email is reserved for admin access');
      setFormMessage('Use a different email for customer accounts.', 'error');
      updateSubmitState();
      return;
    }

    const userRecord = {
      name: nameInput.value.trim(),
      email,
      phone: phoneInput.value.trim(),
      password: passwordInput.value.trim()
    };

    try {
      isSubmitting = true;
      updateSubmitState();
      await createServerUser(userRecord);
      setFormMessage('Account created successfully. Redirecting...', 'success');
      window.location.href = 'index.php';
    } catch (error) {
      setFormMessage(error.message || 'Unable to create the account right now.', 'error');
    } finally {
      isSubmitting = false;
      updateSubmitState();
    }
  });

  if (googleButton) {
    googleButton.addEventListener('click', (event) => {
      event.preventDefault();
      setFormMessage('Google sign up is not available right now. Please use email and password.', 'error');
    });
  }

  updateSubmitState();
})();
