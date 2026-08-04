document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const strengthBar = document.getElementById('strengthBar');
  const togglePasswordBtn = document.querySelector('.toggle-password');
  const submitBtn = document.getElementById('submitBtn');

  const validators = {
    fullName: (value) => value.trim().length >= 2 ? '' : 'Name must be at least 2 characters.',
    email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? '' : 'Enter a valid email address.',
    phone: (value) => /^\+?[0-9\s\-()]{7,15}$/.test(value.trim()) ? '' : 'Enter a valid phone number.',
    password: (value) => value.length >= 8 ? '' : 'Password must be at least 8 characters.',
    confirmPassword: (value) => value === passwordInput.value ? '' : 'Passwords do not match.',
    terms: (checked) => checked ? '' : 'You must accept terms and conditions.',
  };

  function validateField(input) {
    const fieldName = input.name;
    const errorElement = document.getElementById(`${fieldName}Error`);
    if (!errorElement) return true;

    const value = input.type === 'checkbox' ? input.checked : input.value;
    const errorMessage = validators[fieldName] ? validators[fieldName](value) : '';
    const group = input.closest('.form-group');

    if (errorMessage) {
      group.classList.add('error');
      errorElement.textContent = errorMessage;
      return false;
    }

    group.classList.remove('error');
    errorElement.textContent = '';
    return true;
  }

  passwordInput.addEventListener('input', () => {
    const value = passwordInput.value;
    strengthBar.className = 'strength-bar';

    if (!value) {
      return;
    }

    let score = 0;
    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
    if (/[0-9]/.test(value) || /[^A-Za-z0-9]/.test(value)) score++;

    if (score === 1) strengthBar.classList.add('strength-weak');
    else if (score === 2) strengthBar.classList.add('strength-medium');
    else if (score >= 3) strengthBar.classList.add('strength-strong');

    if (confirmPasswordInput.value) {
      validateField(confirmPasswordInput);
    }
  });

  const fields = form.querySelectorAll('input');
  fields.forEach((input) => {
    input.addEventListener('blur', () => validateField(input));
    input.addEventListener('input', () => {
      if (input.closest('.form-group')?.classList.contains('error')) {
        validateField(input);
      }
    });
  });

  togglePasswordBtn.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePasswordBtn.textContent = isPassword ? 'Hide' : 'Show';
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    let isFormValid = true;
    fields.forEach((input) => {
      if (!validateField(input)) {
        isFormValid = false;
      }
    });

    if (!isFormValid) {
      return;
    }

    submitBtn.disabled = true;

    const payload = {
      fullName: document.getElementById('fullName').value.trim(),
      email: document.getElementById('email').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      password: passwordInput.value,
      confirmPassword: confirmPasswordInput.value,
    };

    try {
      const response = await fetch('../backend/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'include',
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Registration failed.');
      }

      alert(data.message || 'Registration successful.');
      window.location.href = '../The.alisha/index.html';
    } catch (error) {
      alert(error.message);
    } finally {
      submitBtn.disabled = false;
    }
  });
});