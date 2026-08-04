document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('.product button');

  function parseProduct(productElement) {
    const name = productElement.querySelector('h3')?.textContent?.trim() || 'Unknown product';
    const priceText = productElement.querySelector('p')?.textContent?.trim() || '0';
    const price = parseFloat(priceText.replace(/[^0-9.]/g, '').replace(/[Rs₹,]/g, '')) || 0;
    const image = productElement.querySelector('img')?.src || '';
    const productId = `${name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-${price}`;
    return { productId, name, price, image };
  }

  async function addToCart(product) {
    try {
      const response = await fetch('../backend/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          productId: product.productId,
          name: product.name,
          price: product.price,
          quantity: 1,
        }),
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Failed to add product to cart.');
      }
      alert('Added to cart: ' + product.name);
    } catch (error) {
      if (error.message.includes('Authentication required')) {
        window.location.href = '../Login/login.html';
        return;
      }
      alert(error.message);
    }
  }

  buttons.forEach((button) => {
    button.addEventListener('click', async () => {
      const productElement = button.closest('.product');
      if (!productElement) {
        return;
      }
      const product = parseProduct(productElement);
      await addToCart(product);
    });
  });
});
