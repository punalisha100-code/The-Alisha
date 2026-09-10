document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const strengthBar = document.getElementById('strengthBar');
  const togglePasswordBtn = document.querySelector('.toggle-password');

  // Input Rules
  const validators = {
    fullName: (value) => value.trim().length >= 2 ? '' : 'Name must be at least 2 characters.',
    email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? '' : 'Enter a valid email address.',
    phone: (value) => /^\+?[0-9\s\-()]{7,15}$/.test(value.trim()) ? '' : 'Enter a valid phone number.',
    password: (value) => value.length >= 8 ? '' : 'Password must be at least 8 characters.',
    confirmPassword: (value) => value === passwordInput.value ? '' : 'Passwords do not match.',
    terms: (checked) => checked ? '' : 'You must accept terms and conditions.'
  };

  // Real-time Field Validation
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
    } else {
      group.classList.remove('error');
      errorElement.textContent = '';
      return true;
    }
  }

  // Password Strength Meter Logic
  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    strengthBar.className = 'strength-bar';

    if (!val) return;

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val) || /[^A-Za-z0-9]/.test(val)) score++;

    if (score === 1) strengthBar.classList.add('strength-weak');
    else if (score === 2) strengthBar.classList.add('strength-medium');
    else if (score >= 3) strengthBar.classList.add('strength-strong');

    // Re-validate confirm password if modified
    if (confirmPasswordInput.value) {
      validateField(confirmPasswordInput);
    }
  });

  // Attach Blur & Input Event Listeners for Live Validation
  const fields = form.querySelectorAll('input');
  fields.forEach(input => {
    input.addEventListener('blur', () => validateField(input));
    input.addEventListener('input', () => {
      if (input.closest('.form-group').classList.contains('error')) {
        validateField(input);
      }
    });
  });

  // Toggle Password Visibility
  togglePasswordBtn.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePasswordBtn.textContent = isPassword ? 'Hide' : 'Show';
  });

  // Form Submission
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    let isFormValid = true;
    fields.forEach((input) => {
      if (!validateField(input)) {
        isFormValid = false;
      }
    });

    if (!isFormValid) {
      return;
    }

    const payload = {
      fullName: document.getElementById('fullName').value.trim(),
      email: document.getElementById('email').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      password: passwordInput.value,
      confirmPassword: confirmPasswordInput.value,
      terms: document.getElementById('terms').checked,
    };

    try {
      const response = await fetch('../../../server-side/backend/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Registration failed.');
      }

      alert(data.message || 'Registration successful.');
      window.location.href = '../../Login/login.html';
    } catch (error) {
      alert(error.message);
    }
  });
});