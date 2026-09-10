document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.auth-form');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    if (!email || !password) {
      alert('Email and password are required.');
      return;
    }

    try {
      const response = await fetch('../../../server-side/backend/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
        credentials: 'include',
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Login failed.');
      }

      alert(data.message || 'Login successful.');
      window.location.href = '../The.alisha/index.html';
    } catch (error) {
      alert(error.message);
    }
  });
});