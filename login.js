(() => {
  const form = document.getElementById('login-form');
  const submitButton = document.getElementById('login-submit');
  const googleButton = document.querySelector('.secondary-btn');
  const formMessage = document.getElementById('login-form-message');
  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');
  const forgotPasswordLink = document.getElementById('forgot-password-link');
  const forgotPasswordPanel = document.getElementById('forgot-password-panel');
  const forgotPasswordForm = document.getElementById('forgot-password-form');
  const forgotPasswordMessage = document.getElementById('forgot-password-message');
  const forgotPasswordCancel = document.getElementById('forgot-password-cancel');
  const resetEmailInput = document.getElementById('reset-email');
  const resetPasswordInput = document.getElementById('reset-password');
  const resetConfirmPasswordInput = document.getElementById('reset-confirm-password');

  if (!form || !submitButton || !formMessage || !emailInput || !passwordInput) {
    return;
  }

  const defaultSubmitLabel = submitButton.textContent.trim() || 'Sign In';
  let isSubmitting = false;

  const fields = {
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
    }
  };

  function setFormMessage(message, type = '') {
    formMessage.textContent = message;
    formMessage.className = 'auth-form-message' + (type ? ` ${type}` : '');
  }

  function setResetMessage(message, type = '') {
    if (!forgotPasswordMessage) {
      return;
    }

    forgotPasswordMessage.textContent = message;
    forgotPasswordMessage.className = 'auth-form-message' + (type ? ` ${type}` : '');
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
    const hasErrors = Object.keys(fields).some((fieldName) => Boolean(getFieldMessage(fieldName)));
    submitButton.disabled = hasErrors || isSubmitting;
    submitButton.toggleAttribute('aria-busy', isSubmitting);
    submitButton.textContent = isSubmitting ? 'Signing In...' : defaultSubmitLabel;
  }

  async function attemptServerLogin(email, password) {
    const response = await fetch('api/user-login.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false) {
      throw new Error(data.message || 'Unable to sign in right now.');
    }

    return data.user || null;
  }

  Object.entries(fields).forEach(([fieldName, field]) => {
    field.element.addEventListener('input', () => {
      if (fieldName === 'email') {
        field.element.value = field.element.value.trimStart();
      }

      validateField(fieldName);
      setFormMessage('');
      updateSubmitState();
    });

    field.element.addEventListener('blur', () => {
      validateField(fieldName);
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

  if (forgotPasswordLink && forgotPasswordPanel && forgotPasswordForm && resetEmailInput && resetPasswordInput && resetConfirmPasswordInput) {
    const resetFields = {
      reset_email: {
        element: resetEmailInput,
        errorNode: forgotPasswordForm.querySelector('[data-error-for="reset_email"]'),
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
      reset_password: {
        element: resetPasswordInput,
        errorNode: forgotPasswordForm.querySelector('[data-error-for="reset_password"]'),
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
      reset_confirm_password: {
        element: resetConfirmPasswordInput,
        errorNode: forgotPasswordForm.querySelector('[data-error-for="reset_confirm_password"]'),
        validate: (value) => {
          const normalized = String(value || '').trim();

          if (!normalized) {
            return 'Confirm password is required';
          }

          if (normalized !== resetPasswordInput.value.trim()) {
            return 'Passwords do not match';
          }

          return '';
        }
      }
    };

    function setResetFieldState(fieldName, message) {
      const field = resetFields[fieldName];

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

    function validateResetField(fieldName) {
      const field = resetFields[fieldName];
      return setResetFieldState(fieldName, field.validate(field.element.value));
    }

    function toggleForgotPassword(open) {
      forgotPasswordPanel.classList.toggle('is-open', open);

      if (open) {
        resetEmailInput.value = emailInput.value.trim();
        setResetMessage('');
      }
    }

    Object.entries(resetFields).forEach(([fieldName, field]) => {
      field.element.addEventListener('input', () => {
        validateResetField(fieldName);

        if (fieldName === 'reset_password' && resetConfirmPasswordInput.value) {
          validateResetField('reset_confirm_password');
        }

        setResetMessage('');
      });
    });

    forgotPasswordLink.addEventListener('click', (event) => {
      event.preventDefault();
      toggleForgotPassword(!forgotPasswordPanel.classList.contains('is-open'));
    });

    forgotPasswordCancel.addEventListener('click', () => {
      toggleForgotPassword(false);
    });

    forgotPasswordForm.addEventListener('submit', (event) => {
      event.preventDefault();
      setResetMessage('');

      const isValid = Object.keys(resetFields).every(validateResetField);

      if (!isValid) {
        setResetMessage('Please correct the highlighted fields before updating your password.', 'error');
        return;
      }

      const email = resetEmailInput.value.trim().toLowerCase();

      if (email === 'admin@cibo.local') {
        setResetFieldState('reset_email', 'Admin password cannot be reset here');
        setResetMessage('Use the default admin password for the local admin account.', 'error');
        return;
      }

      setResetMessage('Password reset is not available here yet. Please use your existing password.', 'error');
    });
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    setFormMessage('');

    if (isSubmitting) {
      return;
    }

    if (!validateForm()) {
      updateSubmitState();
      setFormMessage('Please correct the highlighted fields before continuing.', 'error');
      return;
    }

    const email = emailInput.value.trim().toLowerCase();
    const password = passwordInput.value.trim();

    if (email === 'admin@cibo.local') {
      setFormMessage('Admin access is handled in the admin portal. Redirecting...', 'success');
      window.location.href = 'admin/login.php';
      return;
    }

    try {
      isSubmitting = true;
      updateSubmitState();
      await attemptServerLogin(email, password);
      setFormMessage('Login successful. Redirecting...', 'success');
      window.location.href = 'index.php';
    } catch (error) {
      setFormMessage(error.message || 'Unable to sign in right now.', 'error');
    } finally {
      isSubmitting = false;
      updateSubmitState();
    }
  });

  if (googleButton) {
    googleButton.addEventListener('click', (event) => {
      event.preventDefault();
      setFormMessage('Google sign in is not available right now. Please use your email and password.', 'error');
    });
  }

  updateSubmitState();
})();
