document.addEventListener('DOMContentLoaded', () => {
  const statusBanner = document.getElementById('statusBanner');
  const params = new URLSearchParams(window.location.search);
  const status = params.get('status');
  const message = params.get('message');

  function showStatusBanner(messageText, type = 'success') {
    if (!statusBanner) return;
    statusBanner.textContent = messageText || 'Action completed.';
    statusBanner.hidden = false;
    statusBanner.classList.remove('success', 'error');
    statusBanner.classList.add(type);
  }

  if (status === 'success' && message) {
    showStatusBanner(decodeURIComponent(message), 'success');
    const cleanUrl = `${window.location.pathname}${window.location.hash}`;
    window.history.replaceState({}, document.title, cleanUrl);
  }

  // Hook product buttons to AlishaCart
  const buttons = document.querySelectorAll('.product button, .product .add-to-cart-btn');
  buttons.forEach((button) => {
    button.addEventListener('click', (e) => {
      if (button.dataset.alishaCartHandled === 'true') return;
      button.dataset.alishaCartHandled = 'true';
      setTimeout(() => {
        delete button.dataset.alishaCartHandled;
      }, 250);

      e.preventDefault();
      const productElement = button.closest('.product');
      if (!productElement) return;

      const pId = productElement.dataset.id || productElement.querySelector('h3')?.textContent?.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-');
      const name = productElement.querySelector('h3, .product-title')?.textContent?.trim() || 'Product';
      const priceText = productElement.querySelector('p, .product-price')?.textContent?.trim() || '0';
      const price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
      const image = productElement.querySelector('img')?.src || '';

      if (window.AlishaCart) {
        window.AlishaCart.add({ productId: pId, name, price, image, size: 'M' });
      }
    });
  });
});

